<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers\EnrollmentsRelationManager;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Training Portal';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Trainees';

    protected static ?string $modelLabel = 'trainee';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_student', true);
    }

    /** Badge = trainees still awaiting activation. */
    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('is_student', true)->where('is_active', false)->count() ?: null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Trainee account')->schema([
                Forms\Components\Hidden::make('is_student')->default(true),
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('phone')->tel(),
                Forms\Components\TextInput::make('password')
                    ->password()->revealable()
                    ->required(fn (string $context) => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->helperText('Leave blank when editing to keep the current password.'),
                Forms\Components\FileUpload::make('avatar_url')->label('Profile photo')->avatar()
                    ->image()->disk('public')->directory('avatars')->imageEditor(),
                Forms\Components\Textarea::make('bio')->rows(2)->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active (can sign in to the portal)')
                    ->helperText('Trainees cannot access the portal until this is on.')
                    ->default(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('phone')->toggleable(),
                Tables\Columns\TextColumn::make('enrollments_count')->counts('enrollments')->label('Programs')->badge(),
                Tables\Columns\TextColumn::make('outstanding')->label('Owing')->money('NGN')
                    ->state(fn (User $record) => $record->totalOutstanding())
                    ->color(fn (User $record) => $record->totalOutstanding() > 0 ? 'danger' : 'success')->toggleable(),
                Tables\Columns\ToggleColumn::make('is_active')->label('Active'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->label('Added'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
            ])
            ->actions([
                Tables\Actions\Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-check-circle')->color('success')
                    ->visible(fn (User $record) => ! $record->is_active)
                    ->action(fn (User $record) => $record->update(['is_active' => true])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')->label('Activate')->icon('heroicon-o-check-circle')->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [EnrollmentsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit'   => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
