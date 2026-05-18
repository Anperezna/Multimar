<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SupersetDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $internalBaseUrl = rtrim((string) config('services.superset.internal_base_url', ''), '/');
        $publicBaseUrl = rtrim((string) config('services.superset.public_base_url', $internalBaseUrl), '/');

        if ($internalBaseUrl === '') {
            return response()->json([
                'message' => 'Superset no esta configurado.',
                'dashboards' => [],
            ], 500);
        }

        try {
            $accessToken = Cache::remember(
                'superset_access_token',
                now()->addMinutes(10),
                function () use ($internalBaseUrl) {
                    $loginResponse = Http::baseUrl($internalBaseUrl)
                        ->acceptJson()
                        ->post('/api/v1/security/login', [
                            'username' => (string) config('services.superset.username', ''),
                            'password' => (string) config('services.superset.password', ''),
                            'provider' => 'db',
                            'refresh' => true,
                        ]);

                    $loginResponse->throw();

                    return (string) $loginResponse->json('access_token', '');
                }
            );

            if ($accessToken === '') {
                return response()->json([
                    'message' => 'No se pudo obtener el token de Superset.',
                    'dashboards' => [],
                ], 500);
            }

            $dashboardsResponse = Http::baseUrl($internalBaseUrl)
                ->acceptJson()
                ->withToken($accessToken)
                ->get('/api/v1/dashboard/', [
                    'q' => json_encode([
                        'page' => 0,
                        'page_size' => 100,
                        'order_column' => 'changed_on',
                        'order_direction' => 'desc',
                    ], JSON_THROW_ON_ERROR),
                ]);

            $dashboardsResponse->throw();

            $results = $dashboardsResponse->json('result')
                ?? $dashboardsResponse->json('results')
                ?? [];

            $dashboards = collect($results)
                ->map(function ($dashboard) use ($publicBaseUrl) {
                    $id = data_get($dashboard, 'id');
                    $title = data_get($dashboard, 'dashboard_title')
                        ?: data_get($dashboard, 'title')
                        ?: data_get($dashboard, 'slice_name')
                        ?: 'Dashboard';

                    if ($id === null) {
                        return null;
                    }

                    return [
                        'id' => $id,
                        'title' => $title,
                        'url' => $this->buildDashboardUrl($publicBaseUrl, $id),
                    ];
                })
                ->filter()
                ->values();

            return response()->json([
                'dashboards' => $dashboards,
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'No se pudieron cargar los dashboards de Superset.',
                'dashboards' => [],
            ], 500);
        }
    }

    private function buildDashboardUrl(string $baseUrl, int|string $dashboardId): string
    {
        $embedParam = trim((string) config('services.superset.dashboard_embed_param', 'standalone=1'));
        $queryString = $embedParam !== '' ? ('?' . $embedParam) : '';

        return sprintf('%s/superset/dashboard/%s/%s', $baseUrl, $dashboardId, $queryString);
    }
}
