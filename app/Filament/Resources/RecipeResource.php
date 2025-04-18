<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecipeResource\Pages;
use App\Filament\Resources\RecipeResource\RelationManagers\IngredientsRelationManager;
use App\Models\Recipe;
use Filament\Forms\Components\Grid as FormGrid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid as InfolistGrid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Guava\FilamentModalRelationManagers\Actions\Table\RelationManagerAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RecipeResource extends Resource
{
    protected static ?string $model = Recipe::class;

    protected static ?string $slug = 'recipes';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Recipe')
                ->tabs([
                    Tabs\Tab::make('Generals')
                        ->schema([
                            TextInput::make('title')
                                ->required(),

                            MarkdownEditor::make('description')
                                ->columnSpanFull()
                                ->required(),

                            FormGrid::make(['default' => 3])->schema([
                                TextInput::make('duration_in_minutes')
                                    ->integer(),

                                TextInput::make('rating')
                                    ->numeric(),

                                TextInput::make('difficulty')
                                    ->numeric(),
                            ]),
                        ]),
                    Tabs\Tab::make('Instructions')
                        ->schema([
                            MarkdownEditor::make('instructions')
                        ]),
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description'),

                TextColumn::make('rating'),

                TextColumn::make('duration_in_minutes'),

                TextColumn::make('difficulty'),

                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                RelationManagerAction::make('ingredients')->relationManager(IngredientsRelationManager::class),
                ViewAction::make()->iconButton(),
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
                RestoreAction::make()->iconButton(),
                ForceDeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('title')
                            ->label('')
                            ->columnSpanFull()
                            ->size(TextEntry\TextEntrySize::Large),
                        TextEntry::make('description')
                            ->label('')
                            ->html()
                            ->columnSpanFull(),
                        InfolistGrid::make(['default' => 3])->schema([
                            TextEntry::make('duration_in_minutes')
                                ->label('')
                                ->suffix(' min')
                                ->size(TextEntry\TextEntrySize::ExtraSmall),
                            TextEntry::make('rating')
                                ->label('')
                                ->size(TextEntry\TextEntrySize::ExtraSmall)
                                ->icon('heroicon-s-star'),
                            TextEntry::make('difficulty')
                                ->label('')
                                ->size(TextEntry\TextEntrySize::ExtraSmall)
                                ->icon('heroicon-s-arrow-trending-up'),
                        ]),
                    ])
            ]);
    }

    public static function getRelations(): array
    {
        return[
            IngredientsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecipes::route('/'),
            'create' => Pages\CreateRecipe::route('/create'),
            'view' => Pages\ViewRecipe::route('/{record}'),
            'edit' => Pages\EditRecipe::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['user']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'user.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [];

        if ($record->user) {
            $details['User'] = $record->user->name;
        }

        return $details;
    }
}
