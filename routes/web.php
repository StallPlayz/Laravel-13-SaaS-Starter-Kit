<?php

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Laravel\Fortify\Http\Responses\RegisterResponse;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

route::post('/register', function (RegisterRequest $request, CreateNewUser $creator){
    event(new Registered($user = $creator->create($request->all())));
    Auth::login($user);
    return app(RegisterResponse::class);
})->middleware(['guest', HandlePrecognitiveRequests::class])->name('register.store');

if (!function_exists('fetchGeoDbAll')) {
    function fetchGeoDbAll($endpoint, $params = [], $cacheKey = null) {
        if ($cacheKey && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $allData = [];
        $offset = 0;
        $limit = 10;
        $maxPages = 25;
        $pageCount = 0;

        do {
            try {
                $response = Http::timeout(10)->withHeaders([
                    'x-rapidapi-key' => env('RAPIDAPI_KEY'),
                    'x-rapidapi-host' => env('RAPIDAPI_HOST')
                ])->get("https://wft-geo-db.p.rapidapi.com/v1/geo" . $endpoint, array_merge($params, [
                    'limit' => $limit,
                    'offset' => $offset
                ]));

                if ($response->failed()) {
                    break;
                }

                $json = $response->json();
                $data = $json['data'] ?? [];
                
                if (empty($data)) {
                    break;
                }

                $allData = array_merge($allData, $data);
                
                $totalCount = $json['metadata']['totalCount'] ?? 0;
                $offset += $limit;
                $pageCount++;

                if ($offset < $totalCount && $pageCount < $maxPages) {
                    sleep(1); 
                }
            } catch (\Exception $e) {
                break;
            }
            
        } while ($offset < $totalCount && $pageCount < $maxPages);

        $result = ['data' => $allData];
        
        if ($cacheKey && !empty($allData)) {
            Cache::put($cacheKey, $result, now()->addDays(30));
        }

        return $result;
    }
}

Route::get('/api/geo/countries', function () {
    return fetchGeoDbAll('/countries', [], 'geo_countries');
});

Route::get('/api/geo/countries/{countryId}/regions', function ($countryId) {
    return fetchGeoDbAll("/countries/{$countryId}/regions", [], "geo_regions_{$countryId}");
});

Route::get('/api/geo/cities', function () {
    $countryId = request('countryIds');
    $adminCode = request('adminCode');
    
    return fetchGeoDbAll('/cities', [
        'countryIds' => $countryId,
        'adminCode' => $adminCode,
        'types' => 'CITY',
        'sort' => '-population',
        'minPopulation' => 10000
    ], "geo_cities_{$countryId}_{$adminCode}");
});

require __DIR__.'/settings.php';
