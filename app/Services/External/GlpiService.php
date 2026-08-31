<?php

namespace App\Services\External;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GlpiService
{
    protected $apiUrl;
    protected $appToken;
    protected $username;
    protected $password;
    protected $defaultEntityId;
    protected $verifySsl;

    public function __construct()
    {
        $this->apiUrl = rtrim(config('glpi.api_url') ?? '', '/');
        $this->appToken = config('glpi.app_token');
        $this->username = config('glpi.username');
        $this->password = config('glpi.password');
        $this->defaultEntityId = config('glpi.default_entity_id');
        $this->verifySsl = config('glpi.verify_ssl', false);
    }

    /**
     * Get HTTP client with proper SSL configuration.
     *
     * @return \Illuminate\Http\Client\PendingRequest
     */
    protected function http(): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::timeout(30);

        if (!$this->verifySsl) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    /**
     * Initialize GLPI session and get session token (with caching).
     * GLPI uses session-based authentication.
     */
    protected function initSession(): string
    {
        Log::info('GlpiService::initSession called', [
            'api_url' => $this->apiUrl,
            'has_username' => !empty($this->username),
            'has_password' => !empty($this->password),
            'has_app_token' => !empty($this->appToken),
            'verify_ssl' => $this->verifySsl,
        ]);

        $cacheFile = 'glpi_session.json';

        // Check cache with robust error handling
        try {
            if (Storage::exists($cacheFile)) {
                $cachedContent = Storage::get($cacheFile);
                $data = json_decode($cachedContent, true);

                // Validate cached data structure
                if (is_array($data) &&
                    !empty($data['session_token']) &&
                    isset($data['expires_at']) &&
                    is_numeric($data['expires_at']) &&
                    $data['expires_at'] > time() + 60) {
                    Log::info('GLPI session token found in cache');
                    return $data['session_token'];
                }

                Log::debug('GLPI session cache expired or invalid', [
                    'expires_at' => $data['expires_at'] ?? 'missing',
                    'current_time' => time(),
                ]);
            } else {
                Log::info('No cached GLPI session found');
            }
        } catch (\Exception $e) {
            Log::warning('GLPI session cache read failed', [
                'error' => $e->getMessage(),
                'file' => $cacheFile,
            ]);
        }

        // Initialize new session using Basic Auth
        try {
            $basicAuth = base64_encode("{$this->username}:{$this->password}");

            Log::info('GLPI: Attempting to initialize new session', [
                'url' => "{$this->apiUrl}/initSession",
                'username' => $this->username,
            ]);

            $response = $this->http()->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . $basicAuth,
                'App-Token' => $this->appToken,
            ])->get("{$this->apiUrl}/initSession");

            Log::info('GLPI initSession response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->body(),
            ]);

            if (!$response->successful()) {
                Log::error('GLPI session init failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Failed to initialize GLPI session: ' . $response->body());
            }

            $sessionToken = $response->json('session_token');

            if (empty($sessionToken)) {
                throw new \Exception('GLPI session token is empty');
            }

            // Cache session token (GLPI sessions typically expire after 1 hour)
            $expiresAt = time() + 3600; // 1 hour

            try {
                Storage::put($cacheFile, json_encode([
                    'session_token' => $sessionToken,
                    'expires_at' => $expiresAt,
                    'created_at' => time(),
                ]));
            } catch (\Exception $e) {
                Log::warning('Failed to cache GLPI session token', [
                    'error' => $e->getMessage(),
                ]);
            }

            Log::info('GLPI session initialized successfully');

            return $sessionToken;

        } catch (\Exception $e) {
            Log::error('GLPI session initialization error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Kill GLPI session (logout).
     */
    protected function killSession(string $sessionToken): void
    {
        try {
            $this->http()->withHeaders([
                'Session-Token' => $sessionToken,
                'App-Token' => $this->appToken,
            ])->get("{$this->apiUrl}/killSession");

            // Remove cached session
            Storage::delete('glpi_session.json');

            Log::debug('GLPI session killed');
        } catch (\Exception $e) {
            Log::warning('Failed to kill GLPI session', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create a project in GLPI.
     *
     * @param array $projectData Project data from Laravel
     * @return array GLPI response with project ID
     * @throws \Exception
     */
    public function createProject(array $projectData): array
    {
        $sessionToken = $this->initSession();

        try {
            // Map Laravel project data to GLPI project structure
            $glpiData = [
                'input' => [
                    'name' => $projectData['nom_projet'],
                    'content' => $projectData['objectif_projet'] ?? '',
                    'code' => $projectData['code_projet'],
                    'entities_id' => $this->defaultEntityId,
                    'projectstates_id' => config('glpi.default_project_state_id', 1),
                    'projecttypes_id' => config('glpi.default_project_type_id', 1),
                    'plan_start_date' => $projectData['date_demarrage'] ?? null,
                    'plan_end_date' => $projectData['date_fin'] ?? null,
                    'comment' => $this->buildComment($projectData),
                ]
            ];

            // Remove null values
            $glpiData['input'] = array_filter($glpiData['input'], fn($value) => $value !== null);

            Log::info('Creating GLPI project', [
                'project_code' => $projectData['code_projet'],
                'glpi_data' => $glpiData,
            ]);

            $response = $this->http()->withHeaders([
                'Session-Token' => $sessionToken,
                'App-Token' => $this->appToken,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/Project", $glpiData);

            if (!$response->successful()) {
                Log::error('GLPI project creation failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'project_code' => $projectData['code_projet'],
                ]);
                throw new \Exception('Failed to create GLPI project: ' . $response->body());
            }

            $result = $response->json();

            Log::info('GLPI project created successfully', [
                'project_code' => $projectData['code_projet'],
                'glpi_project_id' => $result['id'] ?? null,
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('GLPI project creation error', [
                'error' => $e->getMessage(),
                'project_code' => $projectData['code_projet'] ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Update a project in GLPI.
     *
     * @param string $glpiProjectId GLPI project ID
     * @param array $projectData Project data from Laravel
     * @return array GLPI response
     * @throws \Exception
     */
    public function updateProject(string $glpiProjectId, array $projectData): array
    {
        $sessionToken = $this->initSession();

        try {
            // Map Laravel project data to GLPI project structure
            $glpiData = [
                'input' => [
                    'id' => $glpiProjectId,
                    'name' => $projectData['nom_projet'],
                    'content' => $projectData['objectif_projet'] ?? '',
                    'code' => $projectData['code_projet'],
                    'plan_start_date' => $projectData['date_demarrage'] ?? null,
                    'plan_end_date' => $projectData['date_fin'] ?? null,
                    'comment' => $this->buildComment($projectData),
                ]
            ];

            // Remove null values
            $glpiData['input'] = array_filter($glpiData['input'], fn($value) => $value !== null);

            Log::info('Updating GLPI project', [
                'glpi_project_id' => $glpiProjectId,
                'project_code' => $projectData['code_projet'],
            ]);

            $response = $this->http()->withHeaders([
                'Session-Token' => $sessionToken,
                'App-Token' => $this->appToken,
                'Content-Type' => 'application/json',
            ])->put("{$this->apiUrl}/Project/{$glpiProjectId}", $glpiData);

            if (!$response->successful()) {
                Log::error('GLPI project update failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'glpi_project_id' => $glpiProjectId,
                ]);
                throw new \Exception('Failed to update GLPI project: ' . $response->body());
            }

            $result = $response->json();

            Log::info('GLPI project updated successfully', [
                'glpi_project_id' => $glpiProjectId,
                'project_code' => $projectData['code_projet'],
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('GLPI project update error', [
                'error' => $e->getMessage(),
                'glpi_project_id' => $glpiProjectId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get a project from GLPI by ID.
     *
     * @param string $glpiProjectId GLPI project ID
     * @return array|null GLPI project data or null if not found
     */
    public function getProject(string $glpiProjectId): ?array
    {
        $sessionToken = $this->initSession();

        try {
            $response = $this->http()->withHeaders([
                'Session-Token' => $sessionToken,
                'App-Token' => $this->appToken,
            ])->get("{$this->apiUrl}/Project/{$glpiProjectId}");

            if ($response->status() === 404) {
                return null;
            }

            if (!$response->successful()) {
                Log::warning('GLPI project retrieval failed', [
                    'status' => $response->status(),
                    'glpi_project_id' => $glpiProjectId,
                ]);
                return null;
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('GLPI project retrieval error', [
                'error' => $e->getMessage(),
                'glpi_project_id' => $glpiProjectId,
            ]);
            return null;
        }
    }

    /**
     * Build comment field with additional project information.
     *
     * @param array $projectData
     * @return string
     */
    protected function buildComment(array $projectData): string
    {
        $parts = [];

        if (!empty($projectData['budget_initial'])) {
            $parts[] = "Budget: " . number_format($projectData['budget_initial'], 0, ',', ' ') . " FCFA";
        }

        if (!empty($projectData['type_projet'])) {
            $parts[] = "Type: " . $projectData['type_projet'];
        }

        if (!empty($projectData['statut_initial'])) {
            $parts[] = "Statut: " . $projectData['statut_initial'];
        }

        if (!empty($projectData['notes'])) {
            $parts[] = "\n\nNotes:\n" . $projectData['notes'];
        }

        return implode(" | ", $parts);
    }

    /**
     * Test connection to GLPI API.
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $sessionToken = $this->initSession();
            $this->killSession($sessionToken);
            return true;
        } catch (\Exception $e) {
            Log::error('GLPI connection test failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
