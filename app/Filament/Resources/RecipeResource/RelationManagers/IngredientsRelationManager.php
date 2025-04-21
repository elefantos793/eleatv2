<?php

namespace App\Filament\Resources\RecipeResource\RelationManagers;

use App\Models\Ingredient;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IngredientsRelationManager extends RelationManager
{
    protected static string $relationship = 'ingredients';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('amount'),
                //TODO fix the edit action to not throw a sql error even when working
//                Select::make('unit_id')
//                    ->relationship('unit', 'name')
//                    ->preload()
//                    ->searchable()
//                ,
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Ingredient'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount'),

                Tables\Columns\TextColumn::make('unit.abbreviation')
                    ->label('Unit'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->iconButton()
                    ->icon('heroicon-s-plus')
                    ->slideOver()
                    ->form(fn(AttachAction $action): array
                        => [
                        $action
                            ->getRecordSelect()
                            ->required()
                            ->createOptionUsing(function (array $data) {
                                return Ingredient::create([
                                    'name' => $data['name'],
                                ])->getKey();
                            })
                            ->createOptionForm([
                                TextInput::make('name')->required(),
                            ])
                        ,
                        Forms\Components\TextInput::make('amount'),
                        Select::make('unit_id')
                            ->relationship('unit', 'name')
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                TextInput::make('name')->required(),
                                TextInput::make('abbreviation')->required(),
                            ])
                        ,
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DetachAction::make()->iconButton(),
//                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
//                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\DetachBulkAction::make()->iconButton(),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
