<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemLogController extends Controller
{
    public function index(Request $request): Response
    {
        $today = now()->format('Y-m-d');

        $dropdownDate = $request->query('date', $today);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dropdownDate)) {
            $dropdownDate = $today; 
        }

        $availableDates = [];
        for ($i = 0; $i < 7; $i++) {
            $date = now()->subDays($i)->format('Y-m-d');
            if (File::exists(storage_path("logs/laravel-{$date}.log"))) {
                $availableDates[] = $date;
            }
        }

        $rawSearch = $request->query('search') ?? '';
        $tokens = [];
        $generalSearch = $rawSearch;

        if (preg_match_all('/(\w+):(".*?"|\S+)/', $rawSearch, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $key = strtolower($match[1]);
                $value = strtolower(trim($match[2], '"'));

                if (!isset($tokens[$key])) {
                    $tokens[$key] = [];
                }
                $tokens[$key][] = $value;
                
                $generalSearch = str_replace($match[0], '', $generalSearch);
            }
        }
        $generalSearch = strtolower(trim($generalSearch));

        $datesToRead = [];
        if (isset($tokens['date'])) {
            $datesToRead = $tokens['date']; 
        } elseif ($rawSearch !== '') {
            $datesToRead = $availableDates;
        } else {
            $datesToRead = [$dropdownDate];
        }

        $parsedLogs = [];
        $pattern = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.([A-Z]+): (.*)/';

        foreach ($datesToRead as $date) {
            $logPath = storage_path("logs/laravel-{$date}.log");
            if (!File::exists($logPath)) continue;

            $fileLines = array_reverse(file($logPath));

            foreach ($fileLines as $line) {
                if (preg_match($pattern, $line, $matches)) {
                    $timestamp = $matches[1];
                    $environment = $matches[2];
                    $level = $matches[3];
                    $message = $matches[4];

                    if ($generalSearch !== '') {
                        if (!str_contains(strtolower($message), $generalSearch) && !str_contains(strtolower($level), $generalSearch)) {
                            continue;
                        }
                    }

                    if (isset($tokens['severity'])) {
                        $severityMatch = false;
                        foreach ($tokens['severity'] as $sevToken) {
                            if (str_contains(strtolower($level), $sevToken)) {
                                $severityMatch = true;
                                break;
                            }
                        }
                        if (!$severityMatch) continue; 
                    }

                    $parsedLogs[] = [
                        'timestamp' => $timestamp,
                        'environment' => $environment,
                        'level' => $level,
                        'message' => $message,
                    ];
                }
            }
        }

        $page = $request->query('page', 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $pagedData = array_slice($parsedLogs, $offset, $perPage);
        
        $paginator = new LengthAwarePaginator(
            $pagedData,
            count($parsedLogs),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Inertia::render('admin/SystemLog', [
            'logs' => $paginator,
            'filters' => [
                'search' => $rawSearch,
                'date' => $dropdownDate
            ],
            'availableDownloads' => $availableDates,
            'today' => $today
        ]);
    }
    public function download(string $date): StreamedResponse
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            abort(400, 'Invalid date format.');
        }

        $logPath = storage_path("logs/laravel-{$date}.log");

        if (!File::exists($logPath)) {
            abort(404, 'Log file not found.');
        }

        $fileName = "syncdesk-logs-{$date}.csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($logPath) {
            $file = fopen($logPath, 'r');
            $output = fopen('php://output', 'w');
            
            fputcsv($output, ['Timestamp', 'Environment', 'Severity', 'Message']);

            $pattern = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.([A-Z]+): (.*)/';

            while (($line = fgets($file)) !== false) {
                if (preg_match($pattern, $line, $matches)) {
                    fputcsv($output, [
                        $matches[1], 
                        $matches[2], 
                        $matches[3], 
                        $matches[4]  
                    ]);
                }
            }

            fclose($file);
            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }
}