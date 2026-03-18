<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * @deprecated All section methods have been moved to App\Http\Controllers\Admin\Landing\*
 *
 * - ProgramController  – program items (store, update, destroy, toggle)
 * - ServiceController  � services
 * - FeatureController  � features
 * - SliderController   � sliders
 * - TeamController     � team

 */
class LandingController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.landing.program');
    }
}
