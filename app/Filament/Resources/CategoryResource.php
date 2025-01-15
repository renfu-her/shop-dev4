<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = '商品管理';

    protected static ?string $navigationLabel = '商品分類';

    protected static ?string $modelLabel = '分類';

    protected static ?string $pluralModelLabel = '分類';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('parent_id')
                    ->label('上層分類')
                    ->relationship(
                        name: 'parent',
                        titleAttribute: 'name'
                    )
                    ->searchable()
                    ->preload()
                    ->placeholder('選擇上層分類')
                    ->getSearchResultsUsing(function (string $search) {
                        $categories = Category::query()
                            ->where('name', 'like', "%{$search}%")
                            ->get()
                            ->map(function ($category) {
                                // 手動計算深度
                                $depth = 0;
                                $parent_id = $category->parent_id;

                                while ($parent_id) {
                                    $depth++;
                                    $parent = DB::table('categories')
                                        ->where('id', $parent_id)
                                        ->first();
                                    if (!$parent) break;
                                    $parent_id = $parent->parent_id;
                                }

                                $prefix = str_repeat('　', $depth);
                                return [
                                    'id' => $category->id,
                                    'name' => $prefix . $category->name,
                                ];
                            })
                            ->pluck('name', 'id')
                            ->toArray();

                        return $categories;
                    })
                    ->getOptionLabelUsing(function ($value) {
                        $category = Category::find($value);
                        if (!$category) {
                            return null;
                        }

                        // 手動計算深度
                        $depth = 0;
                        $parent_id = $category->parent_id;

                        while ($parent_id) {
                            $depth++;
                            $parent = DB::table('categories')
                                ->where('id', $parent_id)
                                ->first();
                            if (!$parent) break;
                            $parent_id = $parent->parent_id;
                        }

                        $prefix = str_repeat('　', $depth);
                        return $prefix . $category->name;
                    }),
                Forms\Components\TextInput::make('name')
                    ->label('分類名稱')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('sort')
                    ->label('排序')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->label('啟用狀態')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('分類名稱')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(function (Category $record): string {
                        // 手動查詢父級分類來確定深度
                        $depth = 0;
                        $parent_id = $record->parent_id;

                        while ($parent_id) {
                            $depth++;
                            $parent = DB::table('categories')
                                ->where('id', $parent_id)
                                ->first();
                            if (!$parent) break;
                            $parent_id = $parent->parent_id;
                        }

                        $prefix = str_repeat('　', $depth);
                        return $prefix . $record->name;
                    }),
                Tables\Columns\TextColumn::make('sort')
                    ->label('排序')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('啟用狀態')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('sort')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('啟用狀態'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('編輯'),
                Tables\Actions\DeleteAction::make()
                    ->label('刪除'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('刪除所選'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
