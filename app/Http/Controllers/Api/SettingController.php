<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\IndexSettingRequest;
use App\Http\Requests\Setting\StoreSettingRequest;
use App\Http\Requests\Setting\UpdateSettingRequest;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use App\Services\SettingService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function __construct(protected SettingService $settings) {}

    /**
     * Unauthenticated: returns only settings flagged is_public as a flat
     * key => cast-value map. For things like app version, maintenance mode,
     * support phone number — anything the mobile app needs before login.
     */
    public function publicIndex(): JsonResponse
    {
        $settings = $this->settings->publicSettings();

        return ApiResponse::success(
            $settings->mapWithKeys(fn(Setting $s) => [$s->key => $s->cast_value])
        );
    }

    /** Authenticated + permission-gated (route middleware): full list, all fields. */
    public function index(IndexSettingRequest $request): JsonResponse
    {
        $settings = $this->settings->paginate(
            search: $request->validated('search'),
            perPage: ApiResponse::perPage($request),
            page: (int) $request->input(ApiResponse::PAGE_NAME, 1),
            pageName: ApiResponse::PAGE_NAME,
        );

        return ApiResponse::paginated($settings->through(fn(Setting $s) => new SettingResource($s)));
    }

    public function show(string $key): JsonResponse
    {
        return ApiResponse::success(new SettingResource($this->settings->find($key)));
    }

    public function store(StoreSettingRequest $request): JsonResponse
    {
        $setting = $this->settings->create($request->payload());

        return ApiResponse::success(new SettingResource($setting), 'Setting created successfully.', 201);
    }

    public function update(UpdateSettingRequest $request, string $key): JsonResponse
    {
        $setting = $this->settings->update($key, $request->payload());

        return ApiResponse::success(new SettingResource($setting), 'Setting updated successfully.');
    }

    public function destroy(string $key): JsonResponse
    {
        $this->settings->delete($key);

        return ApiResponse::success([], 'Setting deleted successfully.');
    }
}
