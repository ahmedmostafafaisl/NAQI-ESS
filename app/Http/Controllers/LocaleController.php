<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    protected array $supported = ['en', 'ar'];

    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (in_array($locale, $this->supported, true)) {
            session(['locale' => $locale]);
        }

        return back();
    }
}
