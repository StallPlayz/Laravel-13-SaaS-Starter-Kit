<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;

class PlatformHealthController extends Controller
{
    public function dashboard(): Response
    {
        $vitals = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => is_writable(storage_path()),
        ];

        $chartData = [
            'labels' => [],
            'errors' => [],
            'warnings' => [],
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateString = $date->format('Y-m-d');

            $chartData['labels'][] = $date->format('D');

            $logPath = storage_path("logs/laravel-{$dateString}.log");
            $errorCount = 0;
            $warningCount = 0;

            if (File::exists($logPath)) {
                $content = file_get_contents($logPath);

                $errorCount = substr_count($content, '.ERROR:')
                            + substr_count($content, '.CRITICAL:')
                            + substr_count($content, '.EMERGENCY:');

                $warningCount = substr_count($content, '.WARNING:');
            }

            $chartData['errors'][] = $errorCount;
            $chartData['warnings'][] = $warningCount;
        }

        return Inertia::render('admin/PlatformHealth', [
            'vitals' => $vitals,
            'telemetry' => $chartData,
        ]);
    }

    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkCache(): bool
    {
        try {
            Cache::store('redis')->set('system_health_ping', true, 10);

            return Cache::store('redis')->get('system_health_ping') === true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
