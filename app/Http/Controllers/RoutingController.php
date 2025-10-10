<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class RoutingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        } else {
            return redirect()->route('login');
        }
    }

    /**
     * Display a view based on first route param
     *
     * @return \Illuminate\Http\Response
     */
    public function root(Request $request, $first)
    {
        return view($first);
    }

    /**
     * second level route
     */
    public function secondLevel(Request $request, $first, $second)
    {
        return view($first . '.' . $second);
    }

    /**
     * third level route
     */
    public function thirdLevel(Request $request, $first, $second, $third)
    {
        return view($first . '.' . $second . '.' . $third);
    }
}

