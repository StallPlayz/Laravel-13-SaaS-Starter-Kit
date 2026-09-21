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
            'emergency' => [],
            'alert' => [],
            'critical' => [],
            'error' => [],
            'warning' => [],
            'notice' => [],
            'info' => [],
            'debug' => [],
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateString = $date->format('Y-m-d');

            $chartData['labels'][] = $date->format('D');

            $logPath = storage_path("logs/laravel-{$dateString}.log");
            
            $counts = [
                'emergency' => 0,
                'alert' => 0,
                'critical' => 0,
                'error' => 0,
                'warning' => 0,
                'notice' => 0,
                'info' => 0,
                'debug' => 0,
            ];

            if (File::exists($logPath)) {
                $content = file_get_contents($logPath);

                $counts['emergency'] = substr_count($content, '.EMERGENCY:');
                $counts['alert'] = substr_count($content, '.ALERT:');
                $counts['critical'] = substr_count($content, '.CRITICAL:');
                $counts['error'] = substr_count($content, '.ERROR:');
                $counts['warning'] = substr_count($content, '.WARNING:');
                $counts['notice'] = substr_count($content, '.NOTICE:');
                $counts['info'] = substr_count($content, '.INFO:');
                $counts['debug'] = substr_count($content, '.DEBUG:');
            }

            foreach ($counts as $level => $count) {
                $chartData[$level][] = $count;
            }
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
