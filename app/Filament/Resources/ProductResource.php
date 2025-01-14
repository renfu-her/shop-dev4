<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\FileUpload;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = '商品管理';

    protected static ?string $navigationLabel = '商品管理';

    protected static ?string $modelLabel = '商品';

    protected static ?string $pluralModelLabel = '商品';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('image')
                    ->label('主圖片')
                    ->image()
                    ->imageEditor()
                    ->directory('products')
                    ->columnSpanFull()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->imageResizeMode('cover')
                    ->imageResizeTargetWidth('1024')
                    ->imageResizeTargetHeight('1024')
                    ->saveUploadedFileUsing(function ($file) {
                        $manager = new ImageManager(new Driver());

                        $image = $manager->read($file);

                        // 調整圖片大小
                        $image->cover(1024, 1024);

                        // 生成唯一的檔案名
                        $filename = Str::uuid() . '.webp';

                        // 轉換並保存為 WebP
                        $image->toWebp(80)->save(storage_path('app/public/products/' . $filename));

                        return 'products/' . $filename;
                    }),
                Forms\Components\TextInput::make('name')
                    ->label('商品名稱')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('商品描述')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('price')
                    ->label('價格')
                    ->required()
                    ->numeric()
                    ->prefix('NT$'),
                Forms\Components\TextInput::make('stock')
                    ->label('庫存')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                Forms\Components\Toggle::make('is_active')
                    ->label('啟用狀態')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('主圖片'),
                Tables\Columns\TextColumn::make('name')
                    ->label('商品名稱')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('價格')
                    ->money('TWD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('庫存')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('啟用狀態')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('建立時間')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('商品狀態')
                    ->placeholder('全部商品')
                    ->trueLabel('已啟用')
                    ->falseLabel('已停用'),
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
            ->emptyStateHeading('尚無商品')
            ->emptyStateDescription('開始建立您的第一個商品')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('新增商品'),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('搜尋商品')
            ->filtersTriggerAction(
                fn($action) => $action
                    ->label('篩選')
            );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
