<?php

namespace App\Filament\Admin\Resources\Sites\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SiteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('name'),
                TextEntry::make('domain')
                    ->placeholder('-'),
                TextEntry::make('api_key')
                    ->copyable(),
                TextEntry::make('sdkUrl')
                    ->label('SDK URL')
                    ->state(fn ($record): string => $record->sdkUrl())
                    ->copyable()
                    ->columnSpanFull(),
                TextEntry::make('trackerEvents_count')
                    ->label('Events')
                    ->state(fn ($record): int => $record->trackerEvents()->count()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
