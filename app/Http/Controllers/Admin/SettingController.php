<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\IndexSettingRequest;
use App\Http\Requests\Setting\StoreSettingRequest;
use App\Http\Requests\Setting\UpdateSettingRequest;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(protected SettingService $settings)
    {
        $this->middleware('permission:settings.view')->only('index', 'edit');
        $this->middleware('permission:settings.manage')->only('create', 'store', 'update', 'destroy');
    }

    public function index(IndexSettingRequest $request): View
    {
        $settings = $this->settings->paginate(
            search: $request->validated('search'),
            perPage: 20,
            page: (int) $request->input('page', 1),
            pageName: 'page',
        )->withQueryString();

        return view('settings.index', compact('settings'));
    }

    public function create(): View
    {
        return view('settings.create');
    }

    public function store(StoreSettingRequest $request): RedirectResponse
    {
        $this->settings->create($request->payload());

        return redirect()->route('admin.settings.index')->with('success', __('admin.settings.created_success'));
    }

    /** Route-model-bound by numeric ID (unchanged from before) — the service itself works by key. */
    public function edit(Setting $setting): View
    {
        return view('settings.edit', compact('setting'));
    }

    public function update(UpdateSettingRequest $request, Setting $setting): RedirectResponse
    {
        $this->settings->update($setting->key, $request->payload());

        return redirect()->route('admin.settings.index')->with('success', __('admin.settings.updated_success'));
    }

    public function destroy(Setting $setting): RedirectResponse
    {
        $this->settings->delete($setting->key);

        return back()->with('success', __('admin.settings.deleted_success'));
    }
}
