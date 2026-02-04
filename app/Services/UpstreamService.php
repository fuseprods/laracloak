<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\Credential;

class UpstreamService
{
    protected string $apiKey;
    protected string $authHeader;

    public function __construct()
    {
        $this->apiKey = config('services.upstream.api_key', '');
        $this->authHeader = config('services.upstream.auth_header', 'X-UPSTREAM-KEY');
    }

    /**
     * Execute a request to the upstream service.
     *
     * @param string $url Full destination URL
     * @param string $method HTTP Method (GET, POST)
     * @param array $data Payload for the request
     * @param Credential|null $credential Optional authentication credential
     * @return \Illuminate\Http\Client\Response
     * @throws Exception
     */
    public function call(string $url, string $method = 'POST', array $data = [], ?Credential $credential = null): \Illuminate\Http\Client\Response
    {
        // 1. Validate URL Scheme
        if (!filter_var($url, FILTER_VALIDATE_URL) || !in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'])) {
            Log::warning('Security: Invalid upstream URL', ['url' => $url]);
            throw new Exception("Invalid destination configuration");
        }

        // 2. Validate Credential Allowed Domains
        if ($credential && !$credential->isDomainAllowed($url)) {
            Log::alert('Security: Domain not allowed by credential policy', [
                'url' => $url,
                'credential_id' => $credential->id,
                'credential_name' => $credential->name
            ]);
            throw new Exception("Security: Destination URL not allowed by selected credential");
        }

        try {
            // 3. Prepare Request
            $request = Http::withHeaders([
                'User-Agent' => 'Laravel-Secure-Proxy',
                'Accept' => '*/*', // Accept anything
            ])->timeout(30); // increased timeout for media

            // 4. Apply Authentication
            if ($credential) {
                switch ($credential->type) {
                    case 'basic':
                        $request->withBasicAuth($credential->auth_key, $credential->auth_value);
                        break;
                    case 'header':
                        // key is header name, value is header value
                        $request->withHeaders([$credential->auth_key => $credential->auth_value]);
                        break;
                    case 'jwt':
                        $request->withToken($credential->auth_value);
                        break;
                }
            } elseif (!empty($this->apiKey)) {
                // Fallback to legacy global key
                $request->withHeaders([$this->authHeader => $this->apiKey]);
            }

            // 5. Execute
            $response = match (strtoupper($method)) {
                'GET' => $request->get($url, $data),
                'POST' => $request->post($url, $data),
                default => throw new Exception("Method not allowed"),
            };

            // 6. Handle Upstream Errors
            if ($response->failed()) {
                Log::error('Upstream Error', [
                    'status' => $response->status(),
                    'destination' => $url,
                ]);

                throw new Exception("Upstream service unavailable");
            }

            // 7. Return Full Response
            return $response;

        } catch (Exception $e) {
            Log::error('Upstream Exception', ['message' => $e->getMessage(), 'url' => $url]);
            throw new Exception($e->getMessage()); // Pass original message if possible
        }
    }
}
