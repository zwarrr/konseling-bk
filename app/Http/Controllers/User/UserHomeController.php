<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BkAccount;

class UserHomeController extends Controller
{
    public function index()
    {
        // Pass BK gurus for the chat quick-access section on home
        $gurus = BkAccount::orderBy('name')->get();

        return view('users.sections.home', compact('gurus'));
    }
}
