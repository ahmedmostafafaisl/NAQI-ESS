<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Talks to Firestore's REST API directly, with no google/cloud-firestore
 * or gRPC dependency — just the service account JSON already configured
 * for Firebase (config/firebase.php), a hand-signed JWT, and plain HTTP.
 *
 * Why REST instead of the Kreait Firestore contract: Kreait's Firestore
 * support is a thin wrapper around Google's official SDK
 * (google/cloud-firestore), which isn't installed by default and pulls in
 * gRPC — a heavier dependency than this one small write actually needs.
 *
 * STILL genuinely unverified against a real project — same caveat as
 * before, just for a different reason now:
 *   1. Firestore must be enabled for the Firebase project in
 *      FIREBASE_CREDENTIALS (separate toggle from FCM being enabled).
 *   2. That service account needs Firestore IAM permissions
 *      (roles/datastore.user or similar) — having FCM push work already
 *      doesn't guarantee this.
 */
class FirestoreService
{
    protected const TOKEN_SCOPE = 'https://www.googleapis.com/auth/datastore';
    protected const TOKEN_CACHE_KEY = 'firestore_rest_access_token';

    /**
     * Upserts {email, device_id} into the configured collection, keyed by
     * email (so re-registering the same email's device updates the
     * existing document rather than creating duplicates).
     *
     * Failures are logged and swallowed, not thrown — this is a
     * best-effort side sync; a login should never fail just because this
     * external write failed.
     */
    public function saveDeviceRecord(string $email, string $deviceId): bool
    {
        try {
            $token = $this->getAccessToken();
            $projectId = $this->credentials()['project_id'] ?? null;

            if (! $projectId) {
                throw new \RuntimeException('No project_id found in the Firebase service account file.');
            }

            $collection = config('services.firestore.device_collection', 'dynamics_device_registrations');
            $documentId = $this->documentIdFor($email);
            $url = "https://firestore.googleapis.com/v1/projects/{$projectId}/databases/(default)/documents/{$collection}/{$documentId}";

            // PATCH on a specific document path creates it if missing, or
            // overwrites the given fields if it already exists — an upsert.
            $response = Http::withToken($token)->patch($url, [
                'fields' => [
                    'email' => ['stringValue' => $email],
                    'device_id' => ['stringValue' => $deviceId],
                    'updated_at' => ['stringValue' => now()->toIso8601String()],
                ],
            ]);

            if ($response->failed()) {
                Log::error('Firestore: REST write failed', [
                    'email' => $email,
                    'device_id' => $deviceId,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Firestore: exception while saving device record', [
                'email' => $email,
                'device_id' => $deviceId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Google OAuth2 access token via the JWT Bearer flow (RFC 7523) — hand
     * signs a JWT with the service account's private key, exchanges it for
     * a real access token, and caches it for the token's real lifetime
     * (minus a safety buffer), same caching approach already used for the
     * Dynamics 365 access token.
     */
    protected function getAccessToken(): string
    {
        $cached = Cache::get(self::TOKEN_CACHE_KEY);

        if ($cached) {
            return $cached;
        }

        $credentials = $this->credentials();
        $now = time();

        $jwt = $this->signJwt([
            'iss' => $credentials['client_email'],
            'scope' => self::TOKEN_SCOPE,
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ], $credentials['private_key']);

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Could not obtain a Google OAuth2 token: ' . $response->body());
        }

        $body = $response->json();
        $ttl = max(60, ($body['expires_in'] ?? 3600) - 60);

        Cache::put(self::TOKEN_CACHE_KEY, $body['access_token'], $ttl);

        return $body['access_token'];
    }

    /** Signs an RS256 JWT using openssl directly — no JWT library needed for one signing call. */
    protected function signJwt(array $claims, string $privateKeyPem): string
    {
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->base64UrlEncode(json_encode($claims));
        $signingInput = "{$header}.{$payload}";

        $signed = openssl_sign($signingInput, $signature, $privateKeyPem, 'sha256WithRSAEncryption');

        if (! $signed) {
            throw new \RuntimeException('Could not sign the JWT — check the private_key in the Firebase credentials file.');
        }

        return "{$signingInput}." . $this->base64UrlEncode($signature);
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /** The same Firebase service account JSON already used for FCM push — read fresh each time, not injected, since it's a tiny local file read. */
    protected function credentials(): array
    {
        $path = config('firebase.projects.' . config('firebase.default', 'app') . '.credentials.file');

        // .env commonly sets this as a relative path (e.g.
        // "storage/app/firebase/firebase_credentials.json", per
        // .env.example) — that only resolves correctly if PHP's current
        // working directory happens to be the project root, which isn't
        // guaranteed (e.g. under some web server configs). Resolve it
        // against the Laravel base path explicitly rather than assuming.
        if ($path && ! $this->isAbsolutePath($path)) {
            $path = base_path($path);
        }

        if (! $path || ! is_readable($path)) {
            throw new \RuntimeException("Firebase credentials file not found or unreadable at: {$path}");
        }

        $credentials = json_decode(file_get_contents($path), true);

        if (! is_array($credentials) || empty($credentials['client_email']) || empty($credentials['private_key'])) {
            throw new \RuntimeException('Firebase credentials file is missing client_email or private_key.');
        }

        return $credentials;
    }

    protected function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/') || (bool) preg_match('/^[A-Za-z]:[\\\\\/]/', $path);
    }

    /**
     * Firestore document IDs can't contain '/' — using the raw email
     * (with that one substitution) keeps documents human-readable for
     * debugging in the Firebase console.
     */
    protected function documentIdFor(string $email): string
    {
        return str_replace('/', '_', $email);
    }
}
