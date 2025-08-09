<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GameResource\Pages;
use App\Filament\Resources\GameResource\RelationManagers;
use App\Models\Game;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GameResource extends Resource
{
    protected static ?string $model = Game::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('slug')
                    ->required(),
                Forms\Components\TextInput::make('provider')
                    ->required(),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\TextInput::make('type')
                    ->required(),
                Forms\Components\TextInput::make('min_bet')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('max_bet')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('reels')
                    ->required()
                    ->numeric()
                    ->default(5),
                Forms\Components\TextInput::make('rows')
                    ->required()
                    ->numeric()
                    ->default(3),
                Forms\Components\TextInput::make('paylines')
                    ->required()
                    ->numeric()
                    ->default(25),
                Forms\Components\TextInput::make('rtp')
                    ->required()
                    ->numeric()
                    ->default(95),
                Forms\Components\Textarea::make('configuration')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('provider')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('min_bet')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_bet')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reels')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rows')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paylines')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rtp')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListGames::route('/'),
            'create' => Pages\CreateGame::route('/create'),
            'edit' => Pages\EditGame::route('/{record}/edit'),
        ];
    }
}
