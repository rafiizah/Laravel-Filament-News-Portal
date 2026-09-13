<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                Select::make('role')
                    ->options(['admin' => 'Admin', 'author' => 'Author'])
                    ->required(),
                TextInput::make('password')
                    ->label('Password')
                    ->minLength(8)
                    ->dehydrated(fn($state) => filled($state))
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->password()
                    ->required(),
                Toggle::make('email_verified_at')
                    ->label('Email verified')
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($component, $state) {
                        $component->state($state !== null);
                    })
                    ->dehydrateStateUsing(function ($state) {
                        return $state ? now() : null;
                    }),
            ]);
    }
}
