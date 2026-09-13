<?php

namespace App\Filament\Pages;

use App\Models\Author;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Events\Registered;
use Filament\Auth\Http\Responses\RegistrationResponse;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class Register extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('username')
                    ->label('Username')
                    ->unique(Author::class)
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(User::class)
                    ->maxLength(255),

                TextInput::make('password')
                    ->password()
                    ->required()
                    ->minLength(8)
                    ->same('passwordConfirmation'),
                TextInput::make('passwordConfirmation')
                    ->password()
                    ->required()
                    ->minLength(8)
                    ->dehydrated(false),
                FileUpload::make('avatar')
                    ->label('Avatar')
                    ->image()
                    ->required()
                    ->disk('public'),
                Textarea::make('bio')
                    ->label('Bio')
                    ->required()
                    ->maxLength(1000)
                    ->rows(3),
            ])->statePath('data');
    }

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title('Too many registration attempts. Please try again in a few minutes.')
                ->body('You have exceeded the maximum number of registration attempts. Please wait a few minutes before trying again.')
                ->danger()
                ->send();
        }

        $data = $this->form->getState();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'author',
        ]);

        $author = Author::create([
            'user_id' => $user->id,
            'username' => $data['username'],
            'avatar' => $data['avatar'],
            'bio' => $data['bio'],
        ]);

        event(new Registered($user));

        $this->form->fill();

        Notification::make()
            ->title('Registration successful')
            ->success()
            ->send();

        return app(RegistrationResponse::class);
    }
}
