<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = '商品圖片';

    protected static ?string $recordTitleAttribute = 'image';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image')
                    ->label('圖片')
                    ->image()
                    ->imageEditor()
                    ->directory('product-images')
                    ->required()
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
                        $filename = Str::uuid()->toString() . '.webp';

                        // 轉換並保存為 WebP
                        $image->toWebp(80)->save(storage_path('app/public/product-images/' . $filename));

                        return 'product-images/' . $filename;
                    }),
                Forms\Components\TextInput::make('sort')
                    ->label('排序')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('商品圖片管理')
            ->description('可以上傳多張商品圖片')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('新增圖片'),
            ])
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('圖片'),
                Tables\Columns\TextColumn::make('sort')
                    ->label('排序')
                    ->sortable(),
            ])
            ->defaultSort('sort')
            ->reorderable('sort')
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
            ->emptyStateHeading('尚無其他圖片')
            ->emptyStateDescription('新增更多商品圖片');
    }
}
