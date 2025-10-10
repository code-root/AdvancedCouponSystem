<?php

namespace App\Http\Controllers;

use App\Traits\HasUserScope;

abstract class BaseController extends Controller
{
    use HasUserScope;
}

