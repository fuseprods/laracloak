<?php

namespace Tests\Feature;

use App\Models\Credential;
use App\Services\UpstreamService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UpstreamJwtTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_jwt_token_when_configured()
    {
        $secret = '12345678901234567890123456789012'; // 32 chars for HS256
        $credential = Credential::create([
            'name' => 'JWT Service',
            'type' => 'jwt',
            'auth_key' => 'unused',
            'auth_value' => $secret,
            'allowed_domains' => ['example.com'],
            'settings' => [
                'mode' => 'generation',
                'alg' => 'HS256',
                'claims' => [
                    'role' => 'admin',
                ],
            ],
        ]);

        Http::fake([
            'https://example.com/*' => Http::response('ok', 200),
        ]);

        $service = new UpstreamService();
        $service->call('https://example.com/api/resource', 'GET', [], $credential);

        Http::assertSent(function ($request) use ($secret) {
            if (!$request->hasHeader('Authorization')) {
                return false;
            }

            $token = str_replace('Bearer ', '', $request->header('Authorization')[0]);

            try {
                $decoded = JWT::decode($token, new Key($secret, 'HS256'));

                return $decoded->iss === config('app.url') &&
                    $decoded->role === 'admin' &&
                    isset($decoded->exp) &&
                    isset($decoded->iat);
            } catch (\Exception $e) {
                return false;
            }
        });
    }

    public function test_it_uses_static_token_when_mode_is_not_generation()
    {
        $staticToken = 'static.jwt.token';
        $credential = Credential::create([
            'name' => 'Static JWT',
            'type' => 'jwt',
            'auth_key' => 'unused',
            'auth_value' => $staticToken,
            'allowed_domains' => ['example.com'],
            'settings' => [], // No mode or mode != generation
        ]);

        Http::fake([
            'https://example.com/*' => Http::response('ok', 200),
        ]);

        $service = new UpstreamService();
        $service->call('https://example.com/api/resource', 'GET', [], $credential);

        Http::assertSent(function ($request) use ($staticToken) {
            return $request->hasHeader('Authorization', 'Bearer ' . $staticToken);
        });
    }
}
