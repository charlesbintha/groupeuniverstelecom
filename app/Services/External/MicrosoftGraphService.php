<?php

namespace App\Services\External;

use App\Exceptions\MicrosoftGraphException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MicrosoftGraphService
{
    protected $tenantId;
    protected $clientId;
    protected $clientSecret;
    protected $scope;
    protected $defaultGroupId;

    public function __construct()
    {
        $this->tenantId = config('microsoft-graph.tenant_id');
        $this->clientId = config('microsoft-graph.client_id');
        $this->clientSecret = config('microsoft-graph.client_secret');
        $this->scope = config('microsoft-graph.scope', 'https://graph.microsoft.com/.default');
        $this->defaultGroupId = config('microsoft-graph.default_group_id');
    }

    /**
     * Get Microsoft Graph access token (with caching).
     * Includes robust error handling and storage permission checks.
     */
    public function getAccessToken(): string
    {
        $cacheFile = 'msgraph_token.json';

        // Check cache with robust error handling
        try {
            if (Storage::exists($cacheFile)) {
                $cachedContent = Storage::get($cacheFile);
                $data = json_decode($cachedContent, true);

                // Validate cached data structure
                if (is_array($data) &&
                    !empty($data['access_token']) &&
                    isset($data['expires_at']) &&
                    is_numeric($data['expires_at']) &&
                    $data['expires_at'] > time() + 60) {
                    return $data['access_token'];
                }

                Log::debug('MS Graph token cache expired or invalid', [
                    'expires_at' => $data['expires_at'] ?? 'missing',
                    'time' => time(),
                ]);
            }
        } catch (\Exception $e) {
            // Cache read failed - not critical, just log and continue
            Log::warning('MS Graph token cache read failed', [
                'error' => $e->getMessage(),
                'file' => $cacheFile,
            ]);
        }

        // Request new token
        $tokenUrl = "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token";

        try {
            $http = Http::asForm();

            // Disable SSL verification in local development only
            if (app()->environment('local')) {
                $http = $http->withOptions(['verify' => false]);
            }

            $response = $http->post($tokenUrl, [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => $this->scope,
                'grant_type' => 'client_credentials',
            ]);

            if (!$response->successful()) {
                throw new \RuntimeException('MS Graph token request failed: ' . $response->body());
            }

            $data = $response->json();

            if (empty($data['access_token'])) {
                throw new \RuntimeException('Access token missing in response');
            }

            // Cache token with error handling
            try {
                // Verify storage is writable
                if (!is_writable(storage_path('app'))) {
                    Log::warning('Storage directory not writable, skipping cache', [
                        'path' => storage_path('app'),
                    ]);
                } else {
                    $cacheData = [
                        'access_token' => $data['access_token'],
                        'expires_at' => time() + ($data['expires_in'] ?? 3600) - 30,
                    ];

                    Storage::put($cacheFile, json_encode($cacheData));

                    Log::debug('MS Graph token cached successfully', [
                        'expires_in' => $data['expires_in'] ?? 3600,
                    ]);
                }
            } catch (\Exception $e) {
                // Cache write failed - not critical, just log and continue
                Log::warning('MS Graph token cache write failed', [
                    'error' => $e->getMessage(),
                    'file' => $cacheFile,
                ]);
            }

            return $data['access_token'];

        } catch (\Exception $e) {
            Log::error('MS Graph token error: ' . $e->getMessage());
            throw new \RuntimeException('Failed to obtain MS Graph access token: ' . $e->getMessage());
        }
    }

    /**
     * Make a Microsoft Graph API call.
     * Includes retry logic for network errors and improved timeout handling.
     *
     * @param string $method HTTP method (GET, POST, PATCH, DELETE)
     * @param string $endpoint API endpoint (e.g., '/planner/plans')
     * @param array|null $body Request body
     * @param array $extraHeaders Additional headers
     * @param bool $returnHeaders Whether to return headers in response (for etag extraction)
     * @return array
     */
    protected function call(string $method, string $endpoint, ?array $body = null, array $extraHeaders = [], bool $returnHeaders = false): array
    {
        $token = $this->getAccessToken();
        $url = 'https://graph.microsoft.com/v1.0' . $endpoint;

        $request = Http::withToken($token)
            ->timeout(30)
            ->connectTimeout(10) // Separate connection timeout
            ->retry(
                3,
                fn (int $attempt) => $attempt * 250,
                function (Throwable $exception) {
                    if ($exception instanceof ConnectionException) {
                        return true;
                    }

                    if (!$exception instanceof RequestException) {
                        return false;
                    }

                    $status = $exception->response->status();

                    return $status === 429 || $status >= 500;
                },
                throw: false,
            )
            ->acceptJson();

        // Disable SSL verification in local development only
        if (app()->environment('local')) {
            $request = $request->withOptions(['verify' => false]);
        }

        // Add extra headers (must reassign $request for headers to be applied)
        if (!empty($extraHeaders)) {
            $request = $request->withHeaders($extraHeaders);
        }

        // Execute request
        try {
            Log::debug("MS Graph API call starting", [
                'method' => $method,
                'endpoint' => $endpoint,
                'url' => $url,
            ]);

            $response = match(strtoupper($method)) {
                'GET' => $request->get($url),
                'POST' => $request->post($url, $body ?? []),
                'PATCH' => $request->patch($url, $body ?? []),
                'PUT' => $request->put($url, $body ?? []),
                'DELETE' => $request->delete($url),
                default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}")
            };

            Log::debug("MS Graph API response received", [
                'method' => $method,
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'successful' => $response->successful(),
            ]);

            if (!$response->successful()) {
                $statusCode = $response->status();
                $errorBody = $response->body();

                // Try to parse JSON error, with fallback
                $errorData = null;
                try {
                    $errorData = $response->json();
                } catch (\Exception $jsonError) {
                    Log::warning("Failed to parse error response as JSON", [
                        'json_error' => $jsonError->getMessage(),
                        'raw_body' => substr($errorBody, 0, 500),
                    ]);
                }

                // Extract detailed error message from MS Graph response
                $detailedError = $errorData['error']['message'] ?? $errorBody ?? 'No error message';
                $errorCode = $errorData['error']['code'] ?? 'Unknown';

                $logContext = [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'status' => $statusCode,
                    'errorCode' => $errorCode,
                    'detailedError' => mb_substr((string) $detailedError, 0, 4000),
                    'rawBody' => mb_substr($errorBody, 0, 10000),
                    'headers' => $response->headers(),
                ];

                if ($statusCode >= 500) {
                    Log::error('MS Graph API server error', $logContext);
                } else {
                    Log::warning('MS Graph API client error', $logContext);
                }

                throw new MicrosoftGraphException(
                    $method,
                    $endpoint,
                    $statusCode,
                    (string) $errorCode,
                    (string) $detailedError,
                );
            }

            $result = $response->json() ?? [];

            // If caller needs headers (for etag extraction), include them
            if ($returnHeaders) {
                $result['_headers'] = $response->headers();
            }

            return $result;

        } catch (MicrosoftGraphException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("MS Graph API transport error [{$method} {$endpoint}]", [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Create a Microsoft Planner Plan.
     *
     * @param string $title Plan title
     * @param string|null $groupId Owner group ID (uses default if null)
     * @return array ['id' => string, 'title' => string]
     */
    public function createPlan(string $title, ?string $groupId = null): array
    {
        $owner = $groupId ?: $this->defaultGroupId;

        if (empty($owner)) {
            throw new \RuntimeException('Group ID is required to create a plan');
        }

        $result = $this->call('POST', '/planner/plans', [
            'owner' => $owner,
            'title' => $title,
        ]);

        return [
            'id' => $result['id'] ?? null,
            'title' => $result['title'] ?? $title,
        ];
    }

    /**
     * Get a Planner plan, or null when it no longer exists.
     */
    public function getPlan(string $planId): ?array
    {
        try {
            return $this->call('GET', "/planner/plans/{$planId}");
        } catch (MicrosoftGraphException $e) {
            if ($e->isNotFound()) {
                return null;
            }

            throw $e;
        }
    }

    /**
     * Create a Planner Bucket.
     *
     * @param string $planId Plan ID
     * @param string $name Bucket name
     * @param string|null $orderHint Order hint (e.g., ' !' for first)
     * @return array ['id' => string, 'name' => string]
     */
    public function createBucket(string $planId, string $name, ?string $orderHint = null): array
    {
        $body = [
            'name' => $name,
            'planId' => $planId,
        ];

        if ($orderHint !== null) {
            $body['orderHint'] = $orderHint;
        }

        $result = $this->call('POST', '/planner/buckets', $body);

        return [
            'id' => $result['id'] ?? null,
            'name' => $result['name'] ?? $name,
        ];
    }

    /**
     * Create a Planner Task.
     *
     * @param string $planId Plan ID
     * @param string $bucketId Bucket ID
     * @param string $title Task title
     * @param array $options Optional: dueDateTime, percentComplete, assignments, etc.
     * @return array ['id' => string, 'title' => string, 'etag' => string|null]
     */
    public function createTask(string $planId, string $bucketId, string $title, array $options = []): array
    {
        $body = array_merge([
            'planId' => $planId,
            'bucketId' => $bucketId,
            'title' => $title,
        ], $options);

        $result = $this->call('POST', '/planner/tasks', $body);

        return [
            'id' => $result['id'] ?? null,
            'title' => $result['title'] ?? $title,
            'etag' => $result['@odata.etag'] ?? null,
        ];
    }

    /**
     * Get a Planner Task by ID (with etag for updates).
     *
     * @param string $taskId Task ID
     * @return array|null Task data with etag, or null if not found
     */
    public function getTask(string $taskId): ?array
    {
        try {
            $result = $this->call('GET', "/planner/tasks/{$taskId}");

            return [
                'id' => $result['id'] ?? null,
                'title' => $result['title'] ?? '',
                'planId' => $result['planId'] ?? null,
                'bucketId' => $result['bucketId'] ?? null,
                'percentComplete' => $result['percentComplete'] ?? 0,
                'startDateTime' => $result['startDateTime'] ?? null,
                'dueDateTime' => $result['dueDateTime'] ?? null,
                'assignments' => $result['assignments'] ?? null,
                'etag' => $result['@odata.etag'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::debug("Task not found: {$taskId}", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Update a Planner Task.
     * Requires etag for optimistic concurrency control.
     *
     * @param string $taskId Task ID
     * @param string $etag Current etag (from getTask or previous operation)
     * @param array $updates Fields to update (title, percentComplete, dueDateTime, etc.)
     * @return array ['success' => bool, 'etag' => string|null, 'error' => string|null]
     */
    public function updateTask(string $taskId, string $etag, array $updates): array
    {
        try {
            // Request headers to extract ETag from HTTP response headers
            $result = $this->call('PATCH', "/planner/tasks/{$taskId}", $updates, [
                'If-Match' => $etag,
            ], true);

            // Extract ETag from HTTP headers (MS Graph returns it in the ETag header, not in body)
            $newEtag = null;
            if (!empty($result['_headers']['ETag'])) {
                $etagValue = $result['_headers']['ETag'];
                // Headers can be arrays, extract first value
                $newEtag = is_array($etagValue) ? $etagValue[0] : $etagValue;
            } elseif (!empty($result['_headers']['etag'])) {
                // Try lowercase variant
                $etagValue = $result['_headers']['etag'];
                $newEtag = is_array($etagValue) ? $etagValue[0] : $etagValue;
            }

            return [
                'success' => true,
                'etag' => $newEtag,
                'error' => null,
            ];
        } catch (\Exception $e) {
            Log::warning("Failed to update task {$taskId}: " . $e->getMessage());

            return [
                'success' => false,
                'etag' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a Planner Task.
     * Requires etag for optimistic concurrency control.
     *
     * @param string $taskId Task ID
     * @param string $etag Current etag
     * @return bool True if deleted successfully
     */
    public function deleteTask(string $taskId, string $etag): bool
    {
        try {
            $this->call('DELETE', "/planner/tasks/{$taskId}", null, [
                'If-Match' => $etag,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::warning("Failed to delete task {$taskId}: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Create a Microsoft 365 Unified Group.
     *
     * @param string $displayName Group display name
     * @param string $mailNickname Mail nickname (unique)
     * @param string $description Group description
     * @return array ['id' => string, 'displayName' => string]
     */
    public function createM365Group(string $displayName, string $mailNickname, string $description = ''): array
    {
        // Validate and sanitize inputs
        $displayName = trim($displayName);
        $mailNickname = trim($mailNickname);
        $description = trim($description);

        // MS Graph limits
        if (strlen($displayName) > 256) {
            $displayName = substr($displayName, 0, 256);
            Log::warning('displayName truncated to 256 chars');
        }
        if (strlen($description) > 1024) {
            $description = substr($description, 0, 1024);
            Log::warning('description truncated to 1024 chars');
        }
        if (strlen($mailNickname) > 64) {
            $mailNickname = substr($mailNickname, 0, 64);
            Log::warning('mailNickname truncated to 64 chars');
        }

        $requestBody = [
            'displayName' => $displayName,
            'mailNickname' => $mailNickname,
            'description' => $description,
            'mailEnabled' => true,
            'securityEnabled' => false,
            'groupTypes' => ['Unified'],
            'visibility' => 'Private'
        ];

        Log::info('🚀 Creating M365 group - REQUEST BODY', [
            'displayName' => $displayName,
            'displayName_length' => strlen($displayName),
            'mailNickname' => $mailNickname,
            'mailNickname_length' => strlen($mailNickname),
            'mailNickname_valid' => preg_match('/^[a-z0-9]+$/', $mailNickname),
            'description' => substr($description, 0, 100),
            'description_length' => strlen($description),
            'full_request_body' => json_encode($requestBody),
        ]);

        $result = $this->call('POST', '/groups', $requestBody);

        Log::info('M365 group created', [
            'group_id' => $result['id'] ?? null,
            'displayName' => $result['displayName'] ?? $displayName,
        ]);

        return [
            'id' => $result['id'] ?? null,
            'displayName' => $result['displayName'] ?? $displayName,
        ];
    }

    /**
     * Add a user as a member of a group.
     *
     * @param string $groupId Group ID
     * @param string $userId User AAD ID
     * @return void
     */
    public function addGroupMember(string $groupId, string $userId): void
    {
        $this->call('POST', "/groups/{$groupId}/members/\$ref", [
            '@odata.id' => "https://graph.microsoft.com/v1.0/users/{$userId}"
        ]);
    }

    /**
     * Add a user as an owner of a group.
     *
     * @param string $groupId Group ID
     * @param string $userId User AAD ID
     * @return void
     */
    public function addGroupOwner(string $groupId, string $userId): void
    {
        $this->call('POST', "/groups/{$groupId}/owners/\$ref", [
            '@odata.id' => "https://graph.microsoft.com/v1.0/users/{$userId}"
        ]);
    }

    /**
     * Check if a user is already an owner of a group.
     */
    public function isGroupOwner(string $groupId, string $userId): bool
    {
        $result = $this->call('GET', "/groups/{$groupId}/owners?\$select=id");

        foreach ($result['value'] ?? [] as $owner) {
            if (($owner['id'] ?? null) === $userId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user is allowed (internal domain, not guest).
     *
     * @param array $user User data from Graph API
     * @return bool
     */
    protected function isAllowedUser(array $user): bool
    {
        // Exclude guests
        if (strtolower($user['userType'] ?? '') !== 'member') {
            return false;
        }

        $allowedDomains = config('microsoft-graph.allowed_domains', []);
        $upn = strtolower($user['userPrincipalName'] ?? '');
        $mail = strtolower($user['mail'] ?? '');

        foreach ($allowedDomains as $domain) {
            $suffix = '@' . strtolower($domain);
            if (str_ends_with($upn, $suffix) || str_ends_with($mail, $suffix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve internal user by email (with fallback to filter search).
     * Following original implementation pattern.
     *
     * @param string $email User email
     * @return array|null User data or null if not found/not allowed
     */
    protected function resolveInternalUser(string $email): ?array
    {
        $email = strtolower(trim($email));

        // Try direct GET first
        try {
            $user = $this->call('GET', '/users/' . urlencode($email) . '?$select=id,userPrincipalName,mail,userType');
            if (!empty($user['id']) && $this->isAllowedUser($user)) {
                return $user;
            }
        } catch (\Exception $e) {
            Log::debug("User not found by direct GET: {$email}");
        }

        // Fallback to filter search
        try {
            $filter = "mail eq '{$email}' or userPrincipalName eq '{$email}' or otherMails/any(c:c eq '{$email}')";
            $result = $this->call('GET', '/users?$select=id,userPrincipalName,mail,userType&$filter=' . urlencode($filter));

            foreach ($result['value'] ?? [] as $user) {
                if (!empty($user['id']) && $this->isAllowedUser($user)) {
                    return $user;
                }
            }
        } catch (\Exception $e) {
            Log::debug("User not found by filter: {$email}");
        }

        return null;
    }

    /**
     * Get user from Azure AD by email (wrapper for resolveInternalUser).
     *
     * @param string $email User email
     * @return array|null User data or null if not found
     */
    public function getUserByEmail(string $email): ?array
    {
        return $this->resolveInternalUser($email);
    }

    /**
     * Get user from Azure AD by AAD ID.
     *
     * @param string $aadId Azure AD user ID
     * @return array|null User data or null if not found/not allowed
     */
    public function getUserByAadId(string $aadId): ?array
    {
        try {
            $user = $this->call('GET', "/users/{$aadId}?\$select=id,userPrincipalName,mail,userType");
            if (!empty($user['id']) && $this->isAllowedUser($user)) {
                return $user;
            }
            return null;
        } catch (\Exception $e) {
            Log::debug("User not found by AAD ID: {$aadId}", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Check if user is already a member of a group.
     *
     * @param string $groupId Group ID
     * @param string $userId User AAD ID
     * @return bool
     */
    public function isGroupMember(string $groupId, string $userId): bool
    {
        try {
            $result = $this->call('GET', "/groups/{$groupId}/members");
            $members = $result['value'] ?? [];

            foreach ($members as $member) {
                if ($member['id'] === $userId) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::debug("Error checking group membership", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Ensure user is a member of the group (adds if not already member).
     *
     * @param string $groupId Group ID
     * @param string $userId User AAD ID
     * @return bool True if added, false if already member
     */
    public function ensureGroupMember(string $groupId, string $userId): bool
    {
        if ($this->isGroupMember($groupId, $userId)) {
            return false; // Already member
        }

        try {
            $this->addGroupMember($groupId, $userId);
            return true; // Successfully added
        } catch (\Exception $e) {
            Log::warning("Failed to add group member", [
                'group_id' => $groupId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Ensure user is an owner of the group (adds if not already owner).
     *
     * @param string $groupId Group ID
     * @param string $userId User AAD ID
     * @return bool True if added, false if already owner
     */
    public function ensureGroupOwner(string $groupId, string $userId): bool
    {
        if ($this->isGroupOwner($groupId, $userId)) {
            return false;
        }

        try {
            $this->addGroupOwner($groupId, $userId);

            return true;
        } catch (MicrosoftGraphException $e) {
            // Another sync may have added the owner between the GET and POST.
            if ($e->isDuplicateReference()) {
                Log::debug('Group owner already exists', [
                    'group_id' => $groupId,
                    'user_id' => $userId,
                ]);

                return false;
            }

            Log::warning('Failed to add group owner', [
                'group_id' => $groupId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Add project members (stakeholders + chef) to the M365 group.
     * Following original implementation pattern (lines 381-502).
     *
     * @param \App\Models\Project $project
     * @param string $groupId M365 Group ID
     * @param string $planId Plan ID
     * @param string $bucketId Bucket ID
     * @return array ['added_members' => int, 'added_owners' => int, 'updated_aad' => int, 'chef_aad_id' => string|null, 'errors' => array]
     */
    protected function addProjectMembersToGroup($project, string $groupId, string $planId, string $bucketId): array
    {
        $addedMembers = 0;
        $addedOwners = 0;
        $updatedAad = 0;
        $errors = [];
        $chefAadId = null;

        // --------- 1) Add stakeholders as members (not assigned to tasks) ----------
        $stakeholders = $project->stakeholders()
            ->leftJoin('employes', 'employes.id', '=', 'project_stakeholders.employe_id')
            ->select(
                'project_stakeholders.id as sid',
                'project_stakeholders.role',
                'project_stakeholders.email as stakeholder_email',
                'project_stakeholders.aad_id as stakeholder_aad_id',
                'project_stakeholders.employe_id',
                'employes.email as employe_email',
                'employes.aad_id as employe_aad_id'
            )
            ->get();

        foreach ($stakeholders as $s) {
            // COALESCE logic: prefer stakeholder fields, fallback to employe fields
            $email = strtolower(trim($s->stakeholder_email ?: $s->employe_email ?: ''));
            $aadId = trim($s->stakeholder_aad_id ?: $s->employe_aad_id ?: '');
            $employeId = $s->employe_id;

            if (empty($email)) {
                continue; // Skip if no email
            }

            $user = null;

            // Try by AAD ID first
            if (!empty($aadId)) {
                $user = $this->getUserByAadId($aadId);
            }

            // Fallback to email resolution
            if (!$user) {
                $user = $this->resolveInternalUser($email);

                // Update AAD ID in database if found
                if ($user && !empty($user['id'])) {
                    try {
                        if ($employeId) {
                            \App\Models\Employee::where('id', $employeId)->update(['aad_id' => $user['id']]);
                        }
                        \App\Models\ProjectStakeholder::where('id', $s->sid)->update(['aad_id' => $user['id']]);
                        $updatedAad++;
                    } catch (\Exception $e) {
                        Log::debug("Could not update AAD ID", ['error' => $e->getMessage()]);
                    }
                }
            }

            if (!$user) {
                Log::info("Stakeholder ignored (not found or not allowed)", [
                    'email' => $email,
                    'role' => $s->role
                ]);
                continue;
            }

            // Add as member
            try {
                if ($this->ensureGroupMember($groupId, $user['id'])) {
                    $addedMembers++;
                    Log::info("Added stakeholder to group", [
                        'email' => $email,
                        'role' => $s->role,
                        'user_id' => $user['id']
                    ]);
                }
            } catch (\Exception $e) {
                $errors[] = "Ajout membre ({$email}) : " . $e->getMessage();
            }
        }

        // --------- 2) Add owner_executant as owner + member + prepare assignments ----------
        $ownerNom = $project->owner_executant;

        if (!empty($ownerNom)) {
            $ownerEmployee = \App\Models\Employee::where('prenom_nom', $ownerNom)->first();

            $ownerMail = '';
            $ownerAad = '';
            $ownerId = null;

            if ($ownerEmployee) {
                $ownerId = $ownerEmployee->id;
                $ownerMail = strtolower(trim($ownerEmployee->email ?? ''));
                $ownerAad = trim($ownerEmployee->aad_id ?? '');
            }

            $ownerUser = null;

            // Try by AAD ID first
            if (!empty($ownerAad)) {
                $ownerUser = $this->getUserByAadId($ownerAad);
            }

            // Fallback to email
            if (!$ownerUser && !empty($ownerMail)) {
                $ownerUser = $this->resolveInternalUser($ownerMail);
            }

            if ($ownerUser) {
                $chefAadId = $ownerUser['id'];

                // Update employee AAD ID if needed
                if ($ownerId && empty($ownerAad)) {
                    try {
                        \App\Models\Employee::where('id', $ownerId)->update(['aad_id' => $chefAadId]);
                    } catch (\Exception $e) {
                        Log::debug("Could not update owner_executant AAD ID", ['error' => $e->getMessage()]);
                    }
                }

                // Add as member
                try {
                    if ($this->ensureGroupMember($groupId, $chefAadId)) {
                        $addedMembers++;
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to add owner_executant as member", ['error' => $e->getMessage()]);
                }

                // Add as owner
                try {
                    if ($this->ensureGroupOwner($groupId, $chefAadId)) {
                        $addedOwners++;
                        Log::info("Added owner_executant as group owner", [
                            'owner' => $ownerNom,
                            'email' => $ownerMail,
                            'user_id' => $chefAadId
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to add owner_executant as owner", ['error' => $e->getMessage()]);
                }
            } else {
                $errors[] = "Owner exécutant introuvable ou non autorisé : " . ($ownerMail ?: $ownerNom);
                Log::info("Owner executant not found in Azure AD", ['owner' => $ownerNom, 'email' => $ownerMail]);
            }
        }

        return [
            'added_members' => $addedMembers,
            'added_owners' => $addedOwners,
            'updated_aad' => $updatedAad,
            'chef_aad_id' => $chefAadId,
            'errors' => $errors,
        ];
    }

    /**
     * Create a new task in Planner for an action.
     *
     * @param \App\Models\ProjectAction $action
     * @param string $planId
     * @param string $bucketId
     * @param \App\Models\Project $project
     * @param array|null $assignments
     * @param array &$errors
     * @param int &$tasksCreated
     * @return void
     */
    protected function syncCreateTask($action, string $planId, string $bucketId, $project, ?array $assignments, array &$errors, int &$tasksCreated): void
    {
        $title = trim($action->libelle);

        $options = [];

        // Keep Planner dates aligned with project dates.
        if ($project->date_demarrage) {
            $options['startDateTime'] = $project->date_demarrage->format('Y-m-d\TH:i:s\Z');
        }

        // Add due date if project has end date
        if ($project->date_fin) {
            $options['dueDateTime'] = $project->date_fin->format('Y-m-d\TH:i:s\Z');
        }

        // Assign to chef if available
        if ($assignments) {
            $options['assignments'] = $assignments;
        }

        $taskResult = $this->createTask($planId, $bucketId, $title, $options);

        // Only count as created if we got an ID AND saved it successfully
        if (empty($taskResult['id'])) {
            $errors[] = "Tâche « {$title} » : ID manquant dans la réponse";
            Log::error("Task created but no ID returned", [
                'action_id' => $action->id,
                'title' => $title,
            ]);
            return;
        }

        // Save task ID and etag in database for future updates
        try {
            \App\Models\ProjectAction::where('id', $action->id)->update([
                'ms_task_id' => $taskResult['id'],
                'ms_task_etag' => $taskResult['etag'],
            ]);

            // Only increment counter after successful save
            $tasksCreated++;

            Log::info("Created and saved new Planner task for action", [
                'action_id' => $action->id,
                'ms_task_id' => $taskResult['id'],
                'title' => $title,
            ]);
        } catch (\Exception $e) {
            $errors[] = "Tâche « {$title} » créée mais non enregistrée en base : " . $e->getMessage();
            Log::error("Failed to save task ID for action {$action->id}: " . $e->getMessage(), [
                'ms_task_id' => $taskResult['id'],
                'action_id' => $action->id,
            ]);
        }
    }

    /**
     * Update an existing task in Planner for an action.
     *
     * @param \App\Models\ProjectAction $action
     * @param \App\Models\Project $project
     * @param array|null $assignments
     * @param array &$errors
     * @param int &$tasksUpdated
     * @return void
     */
    protected function syncUpdateTask($action, $project, ?array $assignments, array &$errors, int &$tasksUpdated): void
    {
        $title = trim($action->libelle);

        // First, get current task to retrieve fresh etag
        $currentTask = $this->getTask($action->ms_task_id);

        if (!$currentTask) {
            // Task was deleted in Planner - recreate it or clear the link
            Log::warning("Task not found in Planner, clearing ms_task_id", [
                'action_id' => $action->id,
                'ms_task_id' => $action->ms_task_id,
            ]);

            \App\Models\ProjectAction::where('id', $action->id)->update([
                'ms_task_id' => null,
                'ms_task_etag' => null,
            ]);

            $errors[] = "Tâche « {$title} » supprimée dans Planner - lien retiré";
            return;
        }

        // Prepare updates
        $updates = [];

        // Update title if changed
        if ($currentTask['title'] !== $title) {
            $updates['title'] = $title;
        }

        // Update both dates together so Planner never receives an inconsistent range.
        $newStartDate = $project->date_demarrage ? $project->date_demarrage->format('Y-m-d\TH:i:s\Z') : null;
        $newDueDate = $project->date_fin ? $project->date_fin->format('Y-m-d\TH:i:s\Z') : null;

        if ($currentTask['startDateTime'] !== $newStartDate) {
            $updates['startDateTime'] = $newStartDate;
        }

        if ($currentTask['dueDateTime'] !== $newDueDate) {
            $updates['dueDateTime'] = $newDueDate;
        }

        // Update assignments if needed (only if we have chef assigned)
        if ($assignments && $currentTask['assignments'] !== $assignments) {
            $updates['assignments'] = $assignments;
        }

        // Only update if there are changes
        if (!empty($updates)) {
            $result = $this->updateTask($action->ms_task_id, $currentTask['etag'], $updates);

            if ($result['success']) {
                // Verify we got a new etag from the PATCH response
                if (empty($result['etag'])) {
                    $errors[] = "Mise à jour tâche « {$title} » : etag manquant dans la réponse";
                    Log::warning("Task updated but no etag returned", [
                        'action_id' => $action->id,
                        'ms_task_id' => $action->ms_task_id,
                    ]);
                    return;
                }

                // Update etag in database
                try {
                    \App\Models\ProjectAction::where('id', $action->id)->update([
                        'ms_task_etag' => $result['etag'],
                    ]);

                    // Only increment counter after successful save
                    $tasksUpdated++;

                    Log::info("Updated Planner task for action", [
                        'action_id' => $action->id,
                        'ms_task_id' => $action->ms_task_id,
                        'updates' => array_keys($updates),
                        'new_etag' => substr($result['etag'], 0, 20) . '...',
                    ]);
                } catch (\Exception $e) {
                    $errors[] = "Tâche « {$title} » mise à jour mais etag non enregistré : " . $e->getMessage();
                    Log::error("Failed to save updated etag for action {$action->id}: " . $e->getMessage(), [
                        'ms_task_id' => $action->ms_task_id,
                        'action_id' => $action->id,
                    ]);
                }
            } else {
                $errors[] = "Mise à jour tâche « {$title} » : " . ($result['error'] ?? 'Erreur inconnue');
                Log::warning("Failed to update task for action {$action->id}: " . ($result['error'] ?? 'Unknown error'));
            }
        } else {
            Log::debug("No changes for task, skipping update", [
                'action_id' => $action->id,
                'ms_task_id' => $action->ms_task_id,
            ]);
        }
    }

    /**
     * Sync project to MS Planner (create or update M365 Group, Plan, Bucket, and Tasks from actions).
     * - If project has ms_group_id: reuses existing group (UPDATE mode)
     * - If no ms_group_id: creates new M365 group (CREATE mode)
     *
     * @param \App\Models\Project $project
     * @param string|null $groupId Ignored - uses $project->ms_group_id if available
     * @return array ['success', 'ms_plan_id', 'ms_bucket_id', 'ms_group_id', 'tasks_created', 'tasks_updated', 'errors']
     */
    public function syncProjectToPlanner($project, ?string $groupId = null): array
    {
        $errors = [];
        $groupId = null;
        $planId = null;
        $bucketId = null;
        $isUpdate = false;

        // --------- 1) Get or Create M365 Unified Group ----------
        // If project already has a group ID, use it (UPDATE mode)
        if (!empty($project->ms_group_id)) {
            $groupId = $project->ms_group_id;
            $isUpdate = true;

            Log::info('MS Planner sync: using existing M365 group', [
                'project_id' => $project->id,
                'group_id' => $groupId,
                'mode' => 'UPDATE'
            ]);

        } else {
            // CREATE mode: create new group
            try {
                $displayName = '[' . ($project->code_projet ?? "PRJ-{$project->id}") . '] ' . ($project->nom_projet ?: "Projet #{$project->id}");

                // Generate unique mailNickname (no accents/spaces)
                $mailNickname = strtolower(preg_replace('/[^a-z0-9]/', '', 'proj-' . $project->id . '-' . $project->code_projet));
                if (strlen($mailNickname) < 3) {
                    $mailNickname = 'proj' . $project->id;
                }
                $mailNickname = substr($mailNickname, 0, 60);

                $description = 'Projet ' . $project->code_projet . ' — ' . ($project->objectif_projet ?: '');


                try {
                    Log::info('Creating M365 group for project', [
                        'project_id' => $project->id,
                        'displayName' => $displayName,
                        'mailNickname' => $mailNickname,
                        'mode' => 'CREATE'
                    ]);

                    $group = $this->createM365Group($displayName, $mailNickname, $description);
                    $groupId = $group['id'];

                    if (!$groupId) {
                        throw new \RuntimeException('Group ID missing in response');
                    }

                } catch (\Exception $e) {
                    // Retry with suffix if mailNickname collision (400 Bad Request)
                    $errorMsg = $e->getMessage();
                    $isMailNicknameConflict = str_contains($errorMsg, '400') && (
                        str_contains($errorMsg, 'mailNickname') ||
                        str_contains($errorMsg, 'Another object with the same value') ||
                        str_contains($errorMsg, 'already exists')
                    );

                    if ($isMailNicknameConflict) {
                        // Add timestamp suffix to ensure uniqueness
                        $suffix = substr(md5(time() . $project->id), 0, 8);
                        $mailNickname2 = substr($mailNickname . '-' . $suffix, 0, 64);

                        Log::warning('M365 group creation failed (mailNickname conflict), retrying with unique suffix', [
                            'project_id' => $project->id,
                            'original_mailNickname' => $mailNickname,
                            'new_mailNickname' => $mailNickname2,
                            'error' => substr($errorMsg, 0, 200),
                        ]);

                        try {
                            $group = $this->createM365Group($displayName, $mailNickname2, $description);
                            $groupId = $group['id'];

                            if (!$groupId) {
                                throw new \RuntimeException('Group ID missing in retry response');
                            }

                            Log::info('M365 group created successfully with alternative mailNickname', [
                                'project_id' => $project->id,
                                'group_id' => $groupId,
                                'mailNickname' => $mailNickname2,
                            ]);
                        } catch (\Exception $retryError) {
                            Log::error('M365 group creation retry also failed', [
                                'project_id' => $project->id,
                                'retry_error' => $retryError->getMessage(),
                            ]);
                            throw $retryError;
                        }
                    } else {
                        throw $e;
                    }
                }

                // Update project with group ID immediately (use DB::table to avoid triggering observer)
                \Illuminate\Support\Facades\DB::table('projects')
                    ->where('id', $project->id)
                    ->update(['ms_group_id' => $groupId]);

                Log::info('M365 group created successfully', [
                    'project_id' => $project->id,
                    'group_id' => $groupId
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to create M365 group: ' . $e->getMessage());

                return [
                    'success' => false,
                    'error' => 'Création du groupe: ' . $e->getMessage(),
                    'errors' => $errors,
                ];
            }
        }

        // --------- 2) Get or Create Plan + Bucket in this group ----------
        if ($groupId) {
            $reuseExistingPlan = $isUpdate
                && !empty($project->ms_plan_id)
                && !empty($project->ms_bucket_id);

            if ($reuseExistingPlan) {
                try {
                    $existingPlan = $this->getPlan($project->ms_plan_id);
                } catch (\Exception $e) {
                    Log::error('Failed to validate existing Planner plan', [
                        'project_id' => $project->id,
                        'plan_id' => $project->ms_plan_id,
                        'error' => $e->getMessage(),
                    ]);

                    return [
                        'success' => false,
                        'error' => 'Validation du plan Planner: ' . $e->getMessage(),
                        'ms_group_id' => $groupId,
                        'errors' => $errors,
                    ];
                }

                if (!$existingPlan) {
                    Log::warning('Stored Planner plan no longer exists; recreating it', [
                        'project_id' => $project->id,
                        'missing_plan_id' => $project->ms_plan_id,
                        'stale_bucket_id' => $project->ms_bucket_id,
                    ]);

                    \Illuminate\Support\Facades\DB::transaction(function () use ($project) {
                        \Illuminate\Support\Facades\DB::table('projects')
                            ->where('id', $project->id)
                            ->update([
                                'ms_plan_id' => null,
                                'ms_bucket_id' => null,
                            ]);

                        \App\Models\ProjectAction::where('project_id', $project->id)
                            ->update([
                                'ms_task_id' => null,
                                'ms_task_etag' => null,
                            ]);
                    });

                    $project->ms_plan_id = null;
                    $project->ms_bucket_id = null;
                    $reuseExistingPlan = false;
                }
            }

            // If UPDATE mode and plan/bucket already exist, use them
            if ($reuseExistingPlan) {
                $planId = $project->ms_plan_id;
                $bucketId = $project->ms_bucket_id;

                Log::info('MS Planner sync: using existing plan and bucket', [
                    'project_id' => $project->id,
                    'plan_id' => $planId,
                    'bucket_id' => $bucketId,
                    'mode' => 'UPDATE'
                ]);

            } else {
                // CREATE mode: create new plan and bucket
                try {
                    $planTitle = ($project->nom_projet ?: "Projet #{$project->id}") . ' — PMO';

                    Log::info('Creating plan in group', [
                        'project_id' => $project->id,
                        'group_id' => $groupId,
                        'plan_title' => $planTitle,
                        'mode' => $isUpdate ? 'UPDATE' : 'CREATE'
                    ]);

                    $plan = $this->createPlan($planTitle, $groupId);
                    $planId = $plan['id'];

                    if (!$planId) {
                        throw new \RuntimeException('Plan ID missing in response');
                    }

                    $bucket = $this->createBucket($planId, 'Actions', ' !');
                    $bucketId = $bucket['id'];

                    if (!$bucketId) {
                        throw new \RuntimeException('Bucket ID missing in response');
                    }

                    // Update project with Plan and Bucket IDs (use DB::table to avoid triggering observer)
                    \Illuminate\Support\Facades\DB::table('projects')
                        ->where('id', $project->id)
                        ->update([
                            'ms_plan_id' => $planId,
                            'ms_bucket_id' => $bucketId,
                        ]);

                    Log::info('Plan and bucket created successfully', [
                        'project_id' => $project->id,
                        'plan_id' => $planId,
                        'bucket_id' => $bucketId
                    ]);

                } catch (\Exception $e) {
                    Log::error('Failed to create Plan/Bucket: ' . $e->getMessage());

                    return [
                        'success' => false,
                        'error' => 'Création Plan/Bucket: ' . $e->getMessage(),
                        'ms_group_id' => $groupId,
                        'errors' => $errors,
                    ];
                }
            }
        }

        // --------- 3) Add members (stakeholders + chef) to group ----------
        $addedMembers = 0;
        $addedOwners = 0;
        $updatedAad = 0;
        $chefAadId = null;

        if ($groupId && $planId && $bucketId) {
            try {
                Log::info('Adding project members to group', [
                    'project_id' => $project->id,
                    'group_id' => $groupId
                ]);

                $memberResult = $this->addProjectMembersToGroup($project, $groupId, $planId, $bucketId);
                $addedMembers = $memberResult['added_members'] ?? 0;
                $addedOwners = $memberResult['added_owners'] ?? 0;
                $updatedAad = $memberResult['updated_aad'] ?? 0;
                $chefAadId = $memberResult['chef_aad_id'] ?? null;
                $errors = array_merge($errors, $memberResult['errors'] ?? []);

                Log::info('Members added to group', [
                    'project_id' => $project->id,
                    'added_members' => $addedMembers,
                    'added_owners' => $addedOwners,
                    'updated_aad' => $updatedAad,
                    'chef_assigned' => !empty($chefAadId)
                ]);

            } catch (\Exception $e) {
                // Don't fail the whole sync if member addition fails
                Log::warning('Failed to add some members to group: ' . $e->getMessage());
                $errors[] = 'Erreur lors de l\'ajout des membres : ' . $e->getMessage();
            }
        }

        // --------- 4) Synchronize Tasks from actions ----------
        $tasksCreated = 0;
        $tasksUpdated = 0;

        // In both CREATE and UPDATE modes, synchronize tasks
        if ($groupId && $planId && $bucketId) {
            $actions = $project->actions()->orderBy('ordre')->get();

            Log::info('Loaded actions for sync', [
                'project_id' => $project->id,
                'total_actions' => $actions->count(),
                'actions_with_ms_task_id' => $actions->filter(fn($a) => !empty($a->ms_task_id))->count(),
                'actions_without_ms_task_id' => $actions->filter(fn($a) => empty($a->ms_task_id))->count(),
            ]);

            // Prepare assignments for chef (if available)
            $assignments = null;
            if (!empty($chefAadId)) {
                $assignments = [
                    $chefAadId => [
                        '@odata.type' => 'microsoft.graph.plannerAssignment',
                        'orderHint' => ' !'
                    ]
                ];
            }

            foreach ($actions as $action) {
                $title = trim($action->libelle);

                if (empty($title)) {
                    continue;
                }

                try {
                    // Check if action is already synced to Planner
                    if (!empty($action->ms_task_id)) {
                        Log::info('Syncing existing task (UPDATE)', [
                            'action_id' => $action->id,
                            'ms_task_id' => $action->ms_task_id,
                            'title' => $title,
                        ]);
                        // UPDATE existing task
                        $this->syncUpdateTask($action, $project, $assignments, $errors, $tasksUpdated);
                    } else {
                        Log::info('Creating new task (CREATE)', [
                            'action_id' => $action->id,
                            'title' => $title,
                        ]);
                        // CREATE new task
                        $this->syncCreateTask($action, $planId, $bucketId, $project, $assignments, $errors, $tasksCreated);
                    }

                } catch (\Exception $e) {
                    $errors[] = "Tâche « {$title} » : " . $e->getMessage();
                    Log::warning("Failed to sync task for action {$action->id}: " . $e->getMessage());
                }
            }

            Log::info('Tasks synchronized', [
                'project_id' => $project->id,
                'tasks_created' => $tasksCreated,
                'tasks_updated' => $tasksUpdated,
                'mode' => $isUpdate ? 'UPDATE' : 'CREATE',
            ]);
        }

        return [
            'success' => true,
            'ms_plan_id' => $planId,
            'ms_bucket_id' => $bucketId,
            'ms_group_id' => $groupId,
            'tasks_created' => $tasksCreated,
            'tasks_updated' => $tasksUpdated,
            'added_members' => $addedMembers,
            'added_owners' => $addedOwners,
            'updated_aad' => $updatedAad,
            'tasks_assigned_to_chef' => !empty($chefAadId) && $tasksCreated > 0,
            'errors' => $errors,
        ];
    }
}
