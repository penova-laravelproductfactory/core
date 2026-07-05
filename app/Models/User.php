<?php

namespace App\Models;

use App\Core\Users\Models\User as CoreUser;

/**
 * Framework-facing user binding.
 *
 * Laravel conventions (config/auth.php, UserFactory, "user()" helpers,
 * third-party packages) expect App\Models\User to exist — so it stays,
 * but as a thin subclass. All shared behaviour belongs in
 * App\Core\Users\Models\User; product-specific additions belong in a
 * Module (e.g. a Booking module adding a "bookings" relation via trait).
 */
class User extends CoreUser
{
    //
}
