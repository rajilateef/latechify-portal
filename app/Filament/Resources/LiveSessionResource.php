<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LiveSessionResource\Pages;
use App\Filament\Resources\LiveSessionResource\RelationManagers\AttendanceRelationManager;
use App\Models\LiveSession;
use App\Models\TrainingCourse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LiveSessionResource extends Resource
{
    protected static ?string $model = LiveSession::class;

    protected static ?string $navigationGroup = 'Training Portal';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Live sessions';

    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()->upcoming()->count() ?: null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\Select::make('training_program_id')
                    ->label('Program (cohort)')
                    ->relationship('program', 'name')
                    ->searchable()->preload()->required()->live()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('training_course_id', null)),
                Forms\Components\Select::make('training_course_id')
                    ->label('Course (optional)')
                    ->options(fn (Forms\Get $get) => $get('training_program_id')
                        ? TrainingCourse::where('training_program_id', $get('training_program_id'))->orderBy('sort_order')->pluck('name', 'id')
                        : [])
                    ->searchable()->placeholder('General session'),
                Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
                Forms\Components\DateTimePicker::make('starts_at')->required()->native(false)->seconds(false),
                Forms\Components\DateTimePicker::make('ends_at')->native(false)->seconds(false)->after('starts_at'),
                Forms\Components\TextInput::make('join_url')->label('Join link (Zoom / Meet)')->url()->prefixIcon('heroicon-o-video-camera'),
                Forms\Components\TextInput::make('location')->placeholder('Online or physical venue'),
                Forms\Components\Toggle::make('is_cancelled')->label('Cancelled'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->wrap()->description(fn (LiveSession $r) => $r->course?->name),
                Tables\Columns\TextColumn::make('program.name')->label('Program')->badge()->color('info'),
                Tables\Columns\TextColumn::make('starts_at')->dateTime('D, M j · g:ia')->sortable()
                    ->description(fn (LiveSession $r) => $r->starts_at->isFuture() ? $r->starts_at->diffForHumans() : null),
                Tables\Columns\TextColumn::make('attendances_count')->counts('attendances')->label('Attendance')->badge(),
                Tables\Columns\IconColumn::make('is_cancelled')->boolean()->label('Cancelled')
                    ->trueIcon('heroicon-o-x-circle')->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')->falseColor('success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('training_program_id')->label('Program')->relationship('program', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [AttendanceRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLiveSessions::route('/'),
            'create' => Pages\CreateLiveSession::route('/create'),
            'edit'   => Pages\EditLiveSession::route('/{record}/edit'),
        ];
    }
}
