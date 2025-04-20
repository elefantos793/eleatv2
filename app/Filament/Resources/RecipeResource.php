<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecipeResource\Pages;
use App\Filament\Resources\RecipeResource\RelationManagers\IngredientsRelationManager;
use App\Models\Recipe;
use Filament\Forms\Components\Grid as FormGrid;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Tabs as TableTabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Grid as InfolistGrid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ActionGroup;
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

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TableTabs::make('Recipe')
                ->columnSpanFull()
                ->tabs([
                    TableTabs\Tab::make('Generals')
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
                    TableTabs\Tab::make('Instructions')
                        ->schema([
                            MarkdownEditor::make('instructions')
                                ->default(static fn(): string => file_get_contents(resource_path('markdown/recipe-instruction-template.md')))
                        ]),
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn(Model $record): string => Pages\ViewRecipe::getUrl([$record->getKey()]))
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->width('full')
                    ->icon(fn(Model $record) => $record->user->id === auth()->id() ? 'heroicon-s-user' : '')
                    ->iconPosition('before')
                ,

                TextColumn::make('description')
                    ->visibleFrom('md')
                    ->limit(25),

                TextColumn::make('rating')
                    ->icon('heroicon-s-star')
                    ->label('')
                    ->sortable(),

                TextColumn::make('duration_in_minutes')
                    ->label('')
                    ->formatStateUsing(fn($record): string => "{$record->duration_in_minutes} min")
                    ->sortable(),

                TextColumn::make('difficulty')
                    ->label('')
                    ->icon('heroicon-s-arrow-trending-up')
                    ->sortable(),

            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                RelationManagerAction::make('ingredients')->relationManager(IngredientsRelationManager::class),
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()->visible(fn(Model $record): bool => $record->user_id === auth()->id() || auth()->user()->hasRole('admin')),
                    DeleteAction::make()->visible(fn(Model $record): bool => $record->user_id === auth()->id() || auth()->user()->hasRole('admin')),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ])
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
                Tabs::make('Recipe')
                    ->columnSpanFull()
                    ->tabs([
                    Tabs\Tab::make('Generals')
                    ->schema([
                        TextEntry::make('title')
                            ->label('')
                            ->columnSpanFull()
                            ->size(TextEntry\TextEntrySize::Large),
                        TextEntry::make('description')
                            ->label('')
                            ->markdown()
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
                    ]),
                    Tabs\Tab::make('Instructions')->schema([
                        TextEntry::make('instructions')->markdown()->label('')
                    ]),
                    Tabs\Tab::make('Ingredients')->schema([
                        RepeatableEntry::make('ingredients')
                            ->label('')
                            ->schema([
                                Grid::make(['default' => 3])->schema([
                                    TextEntry::make('name')->label(''),
                                    TextEntry::make('pivot.amount')
                                        ->label(''),
                                    TextEntry::make('pivot.unit.abbreviation')
                                        ->label('')
                                ])
                            ])
                            ->contained(false)
                    ])
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
