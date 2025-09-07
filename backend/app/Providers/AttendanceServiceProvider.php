<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Contracts\AttendanceServiceInterface;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Services\Implementations\AttendanceService;
use App\Repositories\Implementations\AttendanceRepository;

use App\Services\Contracts\Admin\AttendanceServiceInterface as AdminAttendanceServiceInterface;
use App\Repositories\Contracts\Admin\AttendanceRepositoryInterface as AdminAttendanceRepositoryInterface;
use App\Services\Implementations\Admin\AttendanceService as AdminAttendanceService;
use App\Repositories\Implementations\Admin\AttendanceRepository as AdminAttendanceRepository;

class AttendanceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // 一般ユーザー
        $this->app->bind(AttendanceServiceInterface::class, AttendanceService::class);
        $this->app->bind(AttendanceRepositoryInterface::class, AttendanceRepository::class);

        // 管理者
        $this->app->bind(AdminAttendanceServiceInterface::class, AdminAttendanceService::class);
        $this->app->bind(AdminAttendanceRepositoryInterface::class, AdminAttendanceRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
