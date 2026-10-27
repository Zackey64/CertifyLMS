<?php

declare(strict_types=1);

return [
    'admin_kpi_cache_key' => 'admin_dashboard.kpi',
    'admin_completion_rate_cache_key' => 'admin_dashboard.completion_rates',
    'admin_cache_ttl' => (int) env('ADMIN_DASHBOARD_CACHE_TTL', 300),
];
