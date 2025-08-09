<?php

namespace App\Filament\Resources\BonusTypeResource\Pages;

use App\Filament\Resources\BonusTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBonusType extends EditRecord
{
    protected static string $resource = BonusTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
