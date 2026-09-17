<?php

namespace App\Filament\Admin\Resources\TrackerEvents\Pages;

use App\Filament\Admin\Resources\TrackerEvents\TrackerEventResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTrackerEvent extends ViewRecord
{
    protected static string $resource = TrackerEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
