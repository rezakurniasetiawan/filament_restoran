<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Menu;
use Filament\Tables;
use App\Models\Order;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\OrderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OrderResource\RelationManagers;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('id_customer')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('total_order')
                    ->label('Total Order')
                    ->numeric()
                    ->default(0)
                    ->readOnly()
                    ->dehydrated()
                    ->reactive(),

                Repeater::make('menu_items')
                    ->relationship()
                    ->label('Menu Items')
                    ->schema([
                        Select::make('id_menu')
                            ->label('Menu')
                            ->relationship('menu', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $menu = Menu::find($state);
                                $price = $menu?->price ?? 0;
                                $quantity = $get('quantity') ?? 0;
                                $total = $price * $quantity;
                                $set('total', $total);

                                // Update total order
                                self::updateTotalOrder($state, $get, $set);
                            }),

                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->required()
                            ->default(1)
                            // ->lazy()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $menuId = $get('id_menu');
                                $menu = Menu::find($menuId);
                                $price = $menu?->price ?? 0;
                                $total = $price * ($state ?? 0);
                                $set('total', $total);

                                self::updateTotalOrder($state, $get, $set);
                            }),

                        TextInput::make('total')
                            ->label('Item Total')
                            ->numeric()
                            ->readOnly(),
                    ])
                    ->columnSpanFull()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        self::updateTotalOrder($state, $get, $set);
                    }),
            ]);
    }

    private static function updateTotalOrder($state, callable $get, callable $set)
    {
        $menuItems = $get('menu_items') ?? [];
        $orderTotal = collect($menuItems)->sum(fn($item) => ($item['total'] ?? 0));
        $set('total_order', $orderTotal);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                // phone 
                TextColumn::make('customer.telephone')
                    ->label('Phone')
                    ->searchable()
                    ->sortable(),

                // count total dari menuitem where id_order = order.id 
                TextColumn::make('total_order')
                    ->label('Total')
                    ->searchable()
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // // infolist 
    // public static function infolist(Infolist $infolist): Infolist
    // {
    //     return $infolist
    //         ->schema([

    //             Section::make('Order Information')
    //                 ->schema([
    //                     TextEntry::make('customer.name')
    //                         ->label('Customer'),

    //                     TextEntry::make('customer.telephone')
    //                         ->label('Phone'),
    //                 ])->columns(2),
    //         ]);
    // }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
