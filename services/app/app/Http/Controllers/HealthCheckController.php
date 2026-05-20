<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthCheckController extends Controller
{
    public function check(): JsonResponse
    {
        $checks = [];
        $healthy = true;

        try {
            DB::select('SELECT 1');
            $checks['database'] = 'ok';
        } catch (\Throwable) {
            $checks['database'] = 'error';
            $healthy = false;
        }

        try {
            Cache::store('redis')->put('_health', '1', 5);
            $checks['cache'] = 'ok';
        } catch (\Throwable) {
            $checks['cache'] = 'error';
            $healthy = false;
        }

        $checks['version'] = config('app.version', 'unknown');

        return response()->json(
            ['status' => $healthy ? 'ok' : 'degraded', 'checks' => $checks],
            $healthy ? 200 : 503,
        );
    }
}
