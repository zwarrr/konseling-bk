<?php

namespace App\Providers;

use App\Models\Classroom;
use App\Models\Kelas;
use App\Models\SiswaAccount;
use App\Observers\ClassroomObserver;
use App\Observers\KelasObserver;
use App\Observers\SiswaAccountObserver;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale('id');
        \Illuminate\Support\Facades\Date::setLocale('id');

        Kelas::observe(KelasObserver::class);
        Classroom::observe(ClassroomObserver::class);
        SiswaAccount::observe(SiswaAccountObserver::class);
    }
}
