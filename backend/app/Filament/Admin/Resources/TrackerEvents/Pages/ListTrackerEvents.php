<?php

namespace App\Filament\Admin\Resources\TrackerEvents\Pages;

use App\Filament\Admin\Resources\TrackerEvents\TrackerEventResource;
use Filament\Resources\Pages\ListRecords;

class ListTrackerEvents extends ListRecords
{
    protected static string $resource = TrackerEventResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
