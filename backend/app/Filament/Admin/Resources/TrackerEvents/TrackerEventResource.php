<?php

namespace App\Filament\Admin\Resources\TrackerEvents;

use App\Filament\Admin\Resources\TrackerEvents\Pages\ListTrackerEvents;
use App\Filament\Admin\Resources\TrackerEvents\Pages\ViewTrackerEvent;
use App\Filament\Admin\Resources\TrackerEvents\Schemas\TrackerEventForm;
use App\Filament\Admin\Resources\TrackerEvents\Schemas\TrackerEventInfolist;
use App\Filament\Admin\Resources\TrackerEvents\Tables\TrackerEventsTable;
use App\Models\TrackerEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TrackerEventResource extends Resource
{
    protected static ?string $model = TrackerEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Events';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return TrackerEventForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TrackerEventInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrackerEventsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrackerEvents::route('/'),
            'view' => ViewTrackerEvent::route('/{record}'),
        ];
    }
}
