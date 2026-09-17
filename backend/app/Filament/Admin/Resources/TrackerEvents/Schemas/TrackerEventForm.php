<?php

namespace App\Filament\Admin\Resources\TrackerEvents\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TrackerEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('event_id')
                    ->required(),
                TextInput::make('event_name')
                    ->required(),
                TextInput::make('anonymous_id'),
                TextInput::make('session_id'),
                TextInput::make('sequence')
                    ->numeric(),
                Textarea::make('url')
                    ->columnSpanFull(),
                Textarea::make('path')
                    ->columnSpanFull(),
                TextInput::make('title'),
                Textarea::make('previous_url')
                    ->columnSpanFull(),
                Textarea::make('referrer')
                    ->columnSpanFull(),
                DateTimePicker::make('occurred_at'),
                Textarea::make('payload')
                    ->required()
                    ->columnSpanFull(),
                Select::make('site_id')
                    ->relationship('site', 'name'),
            ]);
    }
}
