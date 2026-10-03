<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssignmentResource\Pages;
use App\Filament\Resources\AssignmentResource\RelationManagers\SubmissionsRelationManager;
use App\Models\Assignment;
use App\Models\TrainingClass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static ?string $navigationGroup = 'Training Portal';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Assignments';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        return (string) \App\Models\AssignmentSubmission::where('status', 'submitted')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\Select::make('training_course_id')
                    ->label('Course')
                    ->relationship('course', 'name')
                    ->searchable()->preload()->required()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set) => $set('training_class_id', null)),
                Forms\Components\Select::make('training_class_id')
                    ->label('Class (optional)')
                    ->options(fn (Forms\Get $get) => $get('training_course_id')
                        ? TrainingClass::where('training_course_id', $get('training_course_id'))->orderBy('sort_order')->pluck('title', 'id')
                        : [])
                    ->searchable()->placeholder('Whole course'),
                Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                Forms\Components\Textarea::make('instructions')->rows(5)->columnSpanFull(),
                Forms\Components\DateTimePicker::make('due_at')->label('Due date'),
                Forms\Components\TextInput::make('max_score')->numeric()->default(100)->required()->suffix('points'),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\Toggle::make('is_published')->default(true)->helperText('Visible to trainees when on.'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('due_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->wrap()->description(fn (Assignment $r) => $r->course?->name),
                Tables\Columns\TextColumn::make('course.program.name')->label('Program')->badge()->color('info')->toggleable(),
                Tables\Columns\TextColumn::make('due_at')->dateTime('M j, Y')->placeholder('No due date')->sortable(),
                Tables\Columns\TextColumn::make('submissions_count')->counts('submissions')->label('Submissions')->badge(),
                Tables\Columns\TextColumn::make('ungraded')
                    ->label('To grade')->badge()->color('warning')
                    ->state(fn (Assignment $r) => $r->submissions()->where('status', 'submitted')->count() ?: '—'),
                Tables\Columns\IconColumn::make('is_published')->boolean()->label('Published'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('training_course_id')->label('Course')->relationship('course', 'name'),
                Tables\Filters\TernaryFilter::make('is_published')->label('Published'),
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
        return [SubmissionsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAssignments::route('/'),
            'create' => Pages\CreateAssignment::route('/create'),
            'edit'   => Pages\EditAssignment::route('/{record}/edit'),
        ];
    }
}
