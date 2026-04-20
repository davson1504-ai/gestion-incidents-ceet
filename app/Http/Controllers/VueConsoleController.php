<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class VueConsoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'permission:incidents.view']);
    }

    public function __invoke(): View
    {
        return view('incidents.vue-console');
    }
}
