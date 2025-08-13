<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Contracts\AttendanceServiceInterface;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Services\Implementations\AttendanceService;
use App\Repositories\Implementations\AttendanceRepository;

class AttendanceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AttendanceServiceInterface::class, AttendanceService::class);
        $this->app->bind(AttendanceRepositoryInterface::class, AttendanceRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
