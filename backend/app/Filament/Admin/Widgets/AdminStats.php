<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Site;
use App\Models\TrackerEvent;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Users', User::count()),
            Stat::make('Sites', Site::count()),
            Stat::make('Events today', TrackerEvent::whereDate('occurred_at', today())->count()),
        ];
    }
}
