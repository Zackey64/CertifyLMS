<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class AdminDashboardCacheService
{
    public function forget(): void
    {
        Cache::forget(config('dashboard.admin_kpi_cache_key'));
        Cache::forget(config('dashboard.admin_completion_rate_cache_key'));
    }
}
