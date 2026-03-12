<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Compatibility stub — the app uses BkAccount / SiswaAccount / AdminAccount.
 * This file exists only to satisfy composer's optimized classmap reference.
 * Nothing in the app should instantiate this class directly.
 */
class User extends Authenticatable
{
    protected $table = 'bk_account';
}
