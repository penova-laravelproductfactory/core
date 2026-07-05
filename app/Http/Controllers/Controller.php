<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Base controller for the whole application — Core modules and product
 * Modules alike extend this class.
 */
abstract class Controller
{
    use AuthorizesRequests;
}
