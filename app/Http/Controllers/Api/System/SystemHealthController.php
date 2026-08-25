<?php

namespace App\Http\Controllers\Api\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SystemHealthController extends Controller
{
    /**
     * GET /admin/system/config-health
     *
     * Reports which required configuration keys are set, WITHOUT ever
     * exposing their actual values — only whether each is present and
     * its length (useful for spotting an accidentally-empty string).
     * Restricted to super_admin: even knowing which keys are missing is
     * information worth protecting.
     */
    public function configHealth(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->hasRole('super-admin')) {
            return response()->json(['status' => false, 'message' => 'Forbidden.'], 403);
        }

        $checks = [
            'dynamics365.client_id'      => config('services.dynamics365.client_id'),
            'dynamics365.client_secret'  => config('services.dynamics365.client_secret'),
            'dynamics365.tenant_id'     => config('services.dynamics365.tenant_id'),
            'dynamics365.resource'     => config('services.dynamics365.resource'),
            'dynamics365.api_version'  => config('services.dynamics365.api_version'),
            'taqnyat.bearer_token' => config('services.taqnyat.bearer_token'),
            'taqnyat.sender_name'       => config('services.taqnyat.sender_name'),
            'taqnyat.base_url'  => config('services.taqnyat.base_url'),
            'taqnyat.timeout'  => config('services.taqnyat.timeout'),

        ];

        $results = collect($checks)->map(function ($value) {
            return [
                'set'    => !empty($value),
                // Length only, never the value itself.
                'length' => $value ? strlen((string) $value) : 0,
            ];
        });

        $missing = $results->filter(fn($r) => !$r['set'])->keys()->values();

        return response()->json([
            'status'  => $missing->isEmpty(),
            'missing' => $missing,
            'checks'  => $results,
        ]);
    }
}
