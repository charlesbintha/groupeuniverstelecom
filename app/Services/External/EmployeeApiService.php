<?php

namespace App\Services\External;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmployeeApiService
{
    protected string $url;
    protected string $headerName;
    protected string $headerValue;
    protected int $timeout;
    protected bool $verifySsl;

    public function __construct()
    {
        $this->url = trim((string) config('employee-api.url', ''));
        $this->headerName = trim((string) config('employee-api.header_name', ''));
        $this->headerValue = (string) config('employee-api.header_value', '');
        $this->timeout = (int) config('employee-api.timeout', 15);
        $this->verifySsl = (bool) config('employee-api.verify_ssl', true);
    }

    /**
     * Fetch employees from the external API and normalize the output.
     *
     * @return array{ok: bool, items?: array<int, array<string, mixed>>, error?: string}
     */
    public function listEmployees(): array
    {
        if ($this->url === '') {
            return [
                'ok' => false,
                'error' => 'Employee API URL is not configured.',
            ];
        }

        try {
            $http = Http::timeout($this->timeout);

            if (!$this->verifySsl) {
                $http = $http->withOptions(['verify' => false]);
            }

            if ($this->headerName !== '' && $this->headerValue !== '') {
                $http = $http->withHeaders([$this->headerName => $this->headerValue]);
            }

            $response = $http->get($this->url);

            if (!$response->successful()) {
                Log::error('Employee API request failed: ' . $response->body());

                return [
                    'ok' => false,
                    'error' => 'Employee API request failed.',
                ];
            }

            $payload = $response->json();
            $items = $this->extractEmployeeItems($payload);
            $normalized = [];

            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $normalizedItem = $this->normalizeEmployee($item);

                if ($normalizedItem['id'] === null) {
                    continue;
                }

                $normalized[] = $normalizedItem;
            }

            return [
                'ok' => true,
                'items' => $normalized,
            ];
        } catch (\Exception $e) {
            Log::error('Employee API error: ' . $e->getMessage());

            return [
                'ok' => false,
                'error' => 'Employee API request failed.',
            ];
        }
    }

    /**
     * Extract the employee list from a flexible API response.
     *
     * @param mixed $payload
     * @return array<int, mixed>
     */
    protected function extractEmployeeItems($payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        if ($this->isList($payload)) {
            return $payload;
        }

        foreach (['data', 'employes', 'employees', 'items', 'results'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                return $payload[$key];
            }
        }

        return [];
    }

    /**
     * Normalize a single employee payload.
     *
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    protected function normalizeEmployee(array $item): array
    {
        $id = $item['id'] ?? $item['employe_id'] ?? $item['employee_id'] ?? null;
        if (is_string($id) && ctype_digit($id)) {
            $id = (int) $id;
        }

        $fullName = $item['prenom_nom']
            ?? $item['full_name']
            ?? $item['fullname']
            ?? null;

        if ($fullName === null) {
            $first = $item['prenom'] ?? $item['first_name'] ?? '';
            $last = $item['nom'] ?? $item['last_name'] ?? '';
            $fullName = trim($first . ' ' . $last);
            $fullName = $fullName === '' ? null : $fullName;
        }

        $email = $item['email'] ?? $item['mail'] ?? $item['email_pro'] ?? null;

        $filiale = $item['filiale'] ?? $item['filiale_libelle'] ?? null;
        $direction = $item['direction'] ?? $item['direction_libelle'] ?? null;
        $aadId = $item['aad_id'] ?? null;

        $actif = null;
        if (array_key_exists('actif', $item)) {
            $actif = (bool) $item['actif'];
        } elseif (array_key_exists('active', $item)) {
            $actif = (bool) $item['active'];
        } elseif (array_key_exists('is_active', $item)) {
            $actif = (bool) $item['is_active'];
        }

        return [
            'id' => $id,
            'prenom_nom' => $fullName,
            'email' => $email,
            'filiale' => $filiale,
            'direction' => $direction,
            'aad_id' => $aadId,
            'actif' => $actif,
        ];
    }

    /**
     * Determine if the payload is a list of items.
     *
     * @param array<mixed> $payload
     */
    protected function isList(array $payload): bool
    {
        if ($payload === []) {
            return true;
        }

        return array_keys($payload) === range(0, count($payload) - 1);
    }
}
