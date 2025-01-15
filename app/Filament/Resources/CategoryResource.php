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
                    ->options(function () {
                        // 獲取所有分類並組織成階層結構
                        $categories = Category::all();
                        $options = [];

                        // 先找出頂層分類
                        $topCategories = $categories->whereNull('parent_id');

                        // 遞迴函數來建立階層結構
                        $buildOptions = function ($items, $depth = 0) use (&$buildOptions, $categories) {
                            $options = [];
                            foreach ($items as $category) {
                                $prefix = str_repeat('　', $depth);
                                $options[$category->id] = $prefix . $category->name;

                                // 找出此分類的子分類
                                $children = $categories->where('parent_id', $category->id);
                                if ($children->count() > 0) {
                                    $options += $buildOptions($children, $depth + 1);
                                }
                            }
                            return $options;
                        };

                        return $buildOptions($topCategories);
                    })
                    ->searchable()
                    ->placeholder('選擇上層分類'),
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
                    ->label('排序'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('啟用狀態')
                    ->boolean(),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                // 先獲取所有分類
                $categories = Category::all();

                // 遞迴函數來建立排序順序
                $buildOrder = function ($parentId = null) use ($categories, &$buildOrder) {
                    $ids = [];
                    $items = $categories->where('parent_id', $parentId)->sortBy('sort');

                    foreach ($items as $item) {
                        $ids[] = $item->id;
                        $ids = array_merge($ids, $buildOrder($item->id));
                    }

                    return $ids;
                };

                // 獲取排序後的 ID 列表
                $orderedIds = $buildOrder();

                if (!empty($orderedIds)) {
                    // 建立 CASE 語句
                    $cases = [];
                    foreach ($orderedIds as $index => $id) {
                        $cases[] = "WHEN id = {$id} THEN {$index}";
                    }
                    $orderByCase = "CASE " . implode(' ', $cases) . " END";

                    return $query->orderByRaw($orderByCase);
                }

                return $query;
            })
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
            ])
            ->paginated(false);
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
