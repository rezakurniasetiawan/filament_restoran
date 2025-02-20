<?php

namespace App\Filament\Resources\OrderResource\Pages;

use Filament\Actions;
use pxlrbt\FilamentExcel\Columns\Column;
use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ListRecords;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ExportAction::make()->exports([
                ExcelExport::make()->withColumns([
                    Column::make('customer.name')->heading('Customer Name'),
                    Column::make('customer.telephone')->heading('Customer Telephone'),
                    Column::make('menu.name')->heading('Menu Name'),
                    Column::make('quantity')->heading('Quantity'),
                    Column::make('total')->heading('Total')->formatStateUsing(fn(string $state, $record): string => 'Rp ' . number_format((float) ($record->menu->harga * $record->quantity), 0, ',', '.')),

                ]),
            ])
        ];
    }
}
