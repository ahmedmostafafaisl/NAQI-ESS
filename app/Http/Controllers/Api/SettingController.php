<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SettingController extends Controller
{
    /**
     * Unauthenticated: returns only settings flagged is_public as a flat
     * key => value map. For things like app version, maintenance mode,
     * support phone number — anything the mobile app needs before login.
     */
    public function publicIndex(): JsonResponse
    {
        return response()->json(
            Setting::where('is_public', true)->pluck('value', 'key')
        );
    }

    /** Authenticated + permission-gated: full list, all fields. */
    public function index(Request $request): JsonResponse
    {
        $settings = Setting::query()
            ->when($request->search, fn($q) => $q->where('key', 'like', "%{$request->search}%"))
            ->orderBy('key')
            ->paginate(
                perPage: ApiResponse::perPage($request),
                pageName: ApiResponse::PAGE_NAME,
            );

        return ApiResponse::paginated($settings);
    }

    public function show(string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)->firstOrFail();

        return ApiResponse::success($setting);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateSetting($request, isCreate: true);

        $setting = Setting::create($data);

        return ApiResponse::success($setting, 'Setting created successfully.', 201);
    }

    public function update(Request $request, string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)->firstOrFail();

        $data = $this->validateSetting($request, isCreate: false);

        $setting->update($data);

        return ApiResponse::success($setting, 'Setting updated successfully.');
    }

    public function destroy(string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)->firstOrFail();
        $setting->delete();

        return ApiResponse::success([], 'Setting deleted successfully.');
    }

    protected function validateSetting(Request $request, bool $isCreate): array
    {
        $rules = [
            'value' => ['nullable', 'string'],
            'type' => ['required', 'in:string,integer,boolean,json'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_public' => ['sometimes', 'boolean'],
        ];

        if ($isCreate) {
            $rules['key'] = ['required', 'string', 'max:255', 'unique:settings,key', 'regex:/^[a-z0-9_.]+$/'];
        }

        $data = $request->validate($rules);

        if (($data['type'] ?? null) === 'json' && ! empty($data['value'])) {
            json_decode($data['value']);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages(['value' => 'The value must be valid JSON.']);
            }
        }

        $data['is_public'] = $request->boolean('is_public');

        return $data;
    }
}
