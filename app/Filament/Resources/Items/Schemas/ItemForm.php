<?php

namespace App\Filament\Resources\Items\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('name')
                    ->label('Nama Item')
                    ->required(),
                TextInput::make('price')
                    ->label('Harga')
                    ->numeric()
                    ->required(),
                RichEditor::make('description')
                    ->label('Deskripsi')
                    ->nullable()
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->label('Gambar')
                    ->directory('items')
                    ->visibility('public')
                    ->disk('public')
                    ->image()
                    ->nullable()
                    ->columnSpanFull(),
            ]);
    }
}
