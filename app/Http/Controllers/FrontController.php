<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Page;
use App\Services\UpstreamService;
use Illuminate\Support\Facades\Gate;

class FrontController extends Controller
{
    protected UpstreamService $upstream;

    public function __construct(UpstreamService $upstream)
    {
        $this->upstream = $upstream;
    }

    public function show(string $slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();

        // Authorization: Default Deny handled by Policy
        Gate::authorize('view', $page);

        return view('front.page', ['page' => $page]);
    }

    public function proxy(Request $request, string $slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();

        // Authorization: Check view permission first
        Gate::authorize('view', $page);

        // TODO: Validate Input based on Page Config

        try {
            $response = $this->upstream->call(
                $page->destination_url,
                $page->upstream_method,
                $request->all(),
                $page->credential
            );

            $contentType = $response->header('Content-Type');
            $body = $response->body();

            // Sanitize JSON responses to prevent data leaks (headers, internal URLs)
            if (str_contains($contentType, 'application/json')) {
                $json = json_decode($body, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $json = $this->sanitizeResponse($json, $page->response_filters ?? []);
                    $body = json_encode($json);
                }
            }

            // Passthrough Content-Type and Body
            $finalContentType = $contentType;
            if (str_contains($contentType, 'application/json')) {
                $finalContentType = 'application/json';
            }

            $resp = response($body, $response->status())
                ->header('Content-Type', $finalContentType);

            if ($page->success_message) {
                $resp->header('X-Laracloak-Success', bin2hex($page->success_message)); // Hex encode to avoid header issues with non-ascii
            }
            if ($page->redirect_url) {
                $resp->header('X-Laracloak-Redirect', $page->redirect_url);
            }

            return $resp;

        } catch (\Exception $e) {
            // Return JSON error even if expected binary, to allow frontend handling
            return response()->json(['error' => $e->getMessage(), 'code' => '503'], 503);
        }
    }

    /**
     * Recursively remove sensitive keys from the response.
     */
    private function sanitizeResponse(array $data, array $customFilters = []): array
    {
        $sensitiveKeys = array_merge(
            ['headers', 'webhookUrl', 'executionMode', 'stack', 'debug', 'request'],
            $customFilters
        );

        foreach ($data as $key => $value) {
            if (in_array($key, $sensitiveKeys, true)) {
                unset($data[$key]);
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->sanitizeResponse($value, $customFilters);
            }
        }

        return $data;
    }
}
