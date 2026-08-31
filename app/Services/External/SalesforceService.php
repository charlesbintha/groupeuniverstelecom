<?php

namespace App\Services\External;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SalesforceService
{
    protected $tokenUrl;
    protected $clientId;
    protected $clientSecret;
    protected $apiBase;
    protected $apiVersion;

    public function __construct()
    {
        $this->tokenUrl = config('salesforce.token_url');
        $this->clientId = config('salesforce.client_id');
        $this->clientSecret = config('salesforce.client_secret');
        $this->apiBase = rtrim(config('salesforce.api_base'), '/');
        $this->apiVersion = config('salesforce.api_version', 'v64.0');
    }

    /**
     * Get Salesforce access token (with caching).
     */
    protected function getAccessToken(): string
    {
        $cacheFile = 'salesforce_token.json';

        // Check cache
        if (Storage::exists($cacheFile)) {
            $data = json_decode(Storage::get($cacheFile), true);

            if (!empty($data['access_token']) && $data['expires_at'] > time() + 60) {
                return $data['access_token'];
            }
        }

        // Request new token
        try {
            $http = Http::asForm();

            // Disable SSL verification in local development only
            if (app()->environment('local')) {
                $http = $http->withOptions(['verify' => false]);
            }

            $response = $http->post($this->tokenUrl, [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if (!$response->successful()) {
                throw new \RuntimeException('Salesforce token request failed: ' . $response->body());
            }

            $data = $response->json();

            if (empty($data['access_token'])) {
                throw new \RuntimeException('Access token missing in response');
            }

            // Cache token
            $cacheData = [
                'access_token' => $data['access_token'],
                'expires_at' => time() + ($data['expires_in'] ?? 3600) - 30, // 30s safety margin
            ];

            Storage::put($cacheFile, json_encode($cacheData));

            return $data['access_token'];

        } catch (\Exception $e) {
            Log::error('Salesforce token error: ' . $e->getMessage());
            throw new \RuntimeException('Failed to obtain Salesforce access token: ' . $e->getMessage());
        }
    }

    /**
     * Search Salesforce opportunities.
     *
     * @param string $query Search term
     * @param int $limit Result limit (1-200)
     * @param string|null $cursor Pagination cursor (nextRecordsUrl)
     * @return array
     */
    public function searchOpportunities(string $query, int $limit = 100, ?string $cursor = null): array
    {
        $token = $this->getAccessToken();
        $limit = min(max($limit, 1), 200); // Clamp 1-200
        $closedWonStages = config('salesforce.closed_won_stages', ['Closed Won']);
        // Build SOQL stage filter (e.g. StageName IN ('Closed Won','Gagné'))
        $stageFilter = "StageName IN (" . implode(',', array_map(function ($v) {
                return "'" . str_replace("'", "\\'", $v) . "'";
            }, $closedWonStages)) . ")";

        // Build URL
        if (!empty($cursor)) {
            // Pagination: cursor is the full nextRecordsUrl path
            $url = $this->apiBase . $cursor;
        } else {
            // New search
            if (empty($query) || mb_strlen($query) < 2) {
                return [
                    'ok' => true,
                    'items' => [],
                    'message' => 'Tapez au moins 2 caractères.',
                ];
            }

            // Sanitize SOQL
            $needle = str_replace("'", "\\'", $query);

            // SOQL query
            $soql = "SELECT Id, Name, StageName, Amount, LastModifiedDate
                 FROM Opportunity

                 WHERE
                     (Name LIKE '%{$needle}%')

                 ORDER BY LastModifiedDate DESC
                 LIMIT {$limit}";

            $url = $this->apiBase . "/services/data/{$this->apiVersion}/query?q=" . rawurlencode($soql);
        }

        // Execute query
        try {
            $http = Http::withToken($token)->timeout(30);

            // Disable SSL verification in local development only
            if (app()->environment('local')) {
                $http = $http->withOptions(['verify' => false]);
            }

            $response = $http->get($url);

            if (!$response->successful()) {
                throw new \RuntimeException('Salesforce query failed: ' . $response->body());
            }

            $data = $response->json();

            if (!isset($data['records'])) {
                throw new \RuntimeException('Invalid response format');
            }

            // Transform records
            $items = [];
            foreach ($data['records'] as $record) {
                $items[] = [
                    'id' => $record['Id'] ?? null,
                    'name' => $record['Name'] ?? '',
                    'stage' => $record['StageName'] ?? '',
                    'amount' => isset($record['Amount']) ? (float) $record['Amount'] : null,
                    'lmd' => $record['LastModifiedDate'] ?? null,
                ];
            }

            return [
                'ok' => true,
                'items' => $items,
                'next_cursor' => $data['nextRecordsUrl'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Salesforce search error: ' . $e->getMessage());

            return [
                'ok' => false,
                'error' => 'Erreur lors de la recherche Salesforce: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get opportunity details by ID.
     *
     * @param string $opportunityId
     * @return array|null
     */
    public function getOpportunity(string $opportunityId): ?array
    {
        $token = $this->getAccessToken();

        try {
            $http = Http::withToken($token)->timeout(30);

            // Disable SSL verification in local development only
            if (app()->environment('local')) {
                $http = $http->withOptions(['verify' => false]);
            }

            // Use SOQL query to get Account.Name relationship field
            $soql = "SELECT Id, Name, StageName, Amount, CloseDate, Description, Account.Name FROM Opportunity WHERE Id = '{$opportunityId}'";
            $url = $this->apiBase . "/services/data/{$this->apiVersion}/query?q=" . rawurlencode($soql);

            $response = $http->get($url);

            if (!$response->successful()) {
                Log::warning('Salesforce get opportunity failed', [
                    'opportunity_id' => $opportunityId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();

            // Check if we got a record
            if (empty($data['records']) || !isset($data['records'][0])) {
                Log::warning('Salesforce opportunity not found', [
                    'opportunity_id' => $opportunityId,
                ]);
                return null;
            }

            $record = $data['records'][0];

            return [
                'id' => $record['Id'] ?? null,
                'name' => $record['Name'] ?? '',
                'stage' => $record['StageName'] ?? '',
                'amount' => isset($record['Amount']) ? (float) $record['Amount'] : null,
                'account_name' => $record['Account']['Name'] ?? null,
                'close_date' => $record['CloseDate'] ?? null,
                'description' => $record['Description'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Salesforce get opportunity error: ' . $e->getMessage(), [
                'opportunity_id' => $opportunityId,
                'exception' => get_class($e),
            ]);
            return null;
        }
    }
}
