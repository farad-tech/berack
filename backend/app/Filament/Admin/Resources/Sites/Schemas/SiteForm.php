<?php

namespace App\Filament\Admin\Resources\Sites\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('domain'),
                TextInput::make('api_key')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Generated automatically'),
            ]);
    }
}
