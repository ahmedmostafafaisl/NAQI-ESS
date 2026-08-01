<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:settings.view')->only('index', 'edit');
        $this->middleware('permission:settings.manage')->only('create', 'store', 'update', 'destroy');
    }

    public function index(Request $request): View
    {
        $settings = Setting::query()
            ->when($request->search, fn($q) => $q->where('key', 'like', "%{$request->search}%"))
            ->orderBy('key')
            ->paginate(20)
            ->withQueryString();

        return view('settings.index', compact('settings'));
    }

    public function create(): View
    {
        return view('settings.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:255', 'unique:settings,key', 'regex:/^[a-z0-9_.]+$/'],
            'value' => ['nullable', 'string'],
            'type' => ['required', 'in:string,integer,boolean,json'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_public' => ['sometimes', 'boolean'],
        ]);

        if ($data['type'] === 'json') {
            $this->validateJson($request, 'value');
        }

        Setting::create([
            ...$data,
            'is_public' => $request->boolean('is_public'),
        ]);

        return redirect()->route('admin.settings.index')->with('success', __('admin.settings.created_success'));
    }

    public function edit(Setting $setting): View
    {
        return view('settings.edit', compact('setting'));
    }

    public function update(Request $request, Setting $setting): RedirectResponse
    {
        $data = $request->validate([
            'value' => ['nullable', 'string'],
            'type' => ['required', 'in:string,integer,boolean,json'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_public' => ['sometimes', 'boolean'],
        ]);

        if ($data['type'] === 'json') {
            $this->validateJson($request, 'value');
        }

        $setting->update([
            ...$data,
            'is_public' => $request->boolean('is_public'),
        ]);

        return redirect()->route('admin.settings.index')->with('success', __('admin.settings.updated_success'));
    }

    public function destroy(Setting $setting): RedirectResponse
    {
        $setting->delete();

        return back()->with('success', __('admin.settings.deleted_success'));
    }

    protected function validateJson(Request $request, string $field): void
    {
        $value = $request->input($field);

        if ($value !== null && $value !== '' && json_decode($value) === null && json_last_error() !== JSON_ERROR_NONE) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                $field => __('admin.settings.invalid_json'),
            ]);
        }
    }
}
