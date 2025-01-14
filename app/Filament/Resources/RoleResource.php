<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = '系統管理';

    protected static ?string $navigationLabel = '角色管理';

    protected static ?string $modelLabel = '角色';

    protected static ?string $pluralModelLabel = '角色';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('角色名稱')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('description')
                    ->label('描述')
                    ->maxLength(255),
                Forms\Components\Select::make('permissions')
                    ->label('權限')
                    ->multiple()
                    ->relationship('permissions', 'name')
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('角色名稱')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('描述'),
                Tables\Columns\TextColumn::make('permissions.name')
                    ->label('權限')
                    ->badge(),
            ])
            ->filters([
                //
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
            ->emptyStateHeading('尚無角色')
            ->emptyStateDescription('建立角色來管理使用者權限')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('新增角色'),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('搜尋角色')
            ->filtersTriggerAction(
                fn($action) => $action
                    ->label('篩選')
            );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
