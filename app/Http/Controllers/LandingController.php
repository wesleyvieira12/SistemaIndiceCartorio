<?php

namespace App\Http\Controllers;

class LandingController extends Controller
{
    /**
     * Página pública institucional da serventia.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('landing');
    }
}
