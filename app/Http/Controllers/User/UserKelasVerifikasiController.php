<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ProgramBooking;
use Illuminate\View\View;

class UserKelasVerifikasiController extends Controller
{
    /** Guard: BK (guru) only. */
    private function assertGuru(): void
    {
        abort_unless(auth()->user()?->role === 'guru', 403);
    }

    /**
        * Halaman ACC booking program (tatap muka).
        * GET /bk/program/acc
     */
    public function index(): View
    {
        $this->assertGuru();

        // Program bookings (owned programs only)
        $pendingBookings = ProgramBooking::with(['program', 'user'])
            ->whereHas('program', fn($q) => $q->where('added_by', auth()->id()))
            ->where('status', 'pending')
            ->latest('id')
            ->get();

        $bookingHistory = ProgramBooking::with(['program', 'user.classroom', 'respondedBy'])
            ->whereHas('program', fn($q) => $q->where('added_by', auth()->id()))
            ->whereIn('status', ['approved', 'rejected'])
            ->latest('responded_at')
            ->latest('id')
            ->limit(30)
            ->get();

        return view('users.pages.acc-program-booking', compact('pendingBookings', 'bookingHistory'));
    }
}

