<?php

namespace App\Filament\Admin\Resources\TrackerEvents\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TrackerEventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('event_id'),
                TextEntry::make('event_name'),
                TextEntry::make('anonymous_id')
                    ->placeholder('-'),
                TextEntry::make('session_id')
                    ->placeholder('-'),
                TextEntry::make('sequence')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('url')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('path')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('title')
                    ->placeholder('-'),
                TextEntry::make('previous_url')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('referrer')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('occurred_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('payload')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('site.name')
                    ->label('Site')
                    ->placeholder('-'),
            ]);
    }
}
