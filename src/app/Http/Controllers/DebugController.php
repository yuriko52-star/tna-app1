<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;

class DebugController extends Controller
{
        public function show($viewName)					
    {					
        // ビューが存在するか確認					
        if (View::exists($viewName)) {					
            return view($viewName);					
        }					
        return response("View '{$viewName}' not found.", 404);					
    }					

}
