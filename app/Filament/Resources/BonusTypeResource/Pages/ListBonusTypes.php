<?php

namespace App\Filament\Resources\BonusTypeResource\Pages;

use App\Filament\Resources\BonusTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBonusTypes extends ListRecords
{
    protected static string $resource = BonusTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
