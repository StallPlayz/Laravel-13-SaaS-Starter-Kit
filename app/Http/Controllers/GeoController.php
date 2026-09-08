<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeoController extends Controller
{
    public function countries(): JsonResponse
    {
        return response()->json($this->fetchGeoDbAll('/countries', [], 'geo_countries'));
    }

    public function regions(string $countryId): JsonResponse
    {
        return response()->json($this->fetchGeoDbAll("/countries/{$countryId}/regions", [], "geo_regions_{$countryId}"));
    }

    public function cities(Request $request): JsonResponse
    {
        $countryId = (string) $request->query('countryIds');
        $adminCode = (string) $request->query('adminCode');

        return response()->json($this->fetchGeoDbAll('/cities', [
            'countryIds' => $countryId,
            'adminCode' => $adminCode,
            'types' => 'CITY',
            'sort' => '-population',
            'minPopulation' => 10000,
        ], "geo_cities_{$countryId}_{$adminCode}"));
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function fetchGeoDbAll(string $endpoint, array $params = [], ?string $cacheKey = null): array
    {
        if ($cacheKey && Cache::has($cacheKey)) {
            /** @var array<string, mixed> $cachedData */
            $cachedData = Cache::get($cacheKey);

            return $cachedData;
        }

        $allData = [];
        $offset = 0;
        $limit = 10;
        $maxPages = 25;
        $pageCount = 0;

        do {
            try {
                $response = Http::timeout(10)->withHeaders([
                    'x-rapidapi-key' => config('services.rapidapi.key'),
                    'x-rapidapi-host' => config('services.rapidapi.host'),
                ])->get("https://wft-geo-db.p.rapidapi.com/v1/geo{$endpoint}", [
                    ...$params,
                    'limit' => $limit,
                    'offset' => $offset,
                ]);

                if ($response->failed()) {
                    break;
                }

                $json = $response->json();

                if (! \is_array($json)) {
                    break;
                }

                $data = isset($json['data']) && \is_array($json['data']) ? $json['data'] : [];

                if (empty($data)) {
                    break;
                }

                $allData = [...$allData, ...$data];

                $metadata = isset($json['metadata']) && \is_array($json['metadata']) ? $json['metadata'] : [];
                $totalCount = (int) ($metadata['totalCount'] ?? 0);

                $offset += $limit;
                $pageCount++;

                if ($offset < $totalCount && $pageCount < $maxPages) {
                    sleep(1);
                }
            } catch (Exception $e) {
                break;
            }

        } while ($offset < $totalCount && $pageCount < $maxPages);

        $result = ['data' => $allData];

        if ($cacheKey && ! empty($allData)) {
            Cache::put($cacheKey, $result, now()->addDays(30));
        }

        return $result;
    }
}
