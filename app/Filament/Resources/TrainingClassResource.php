<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrainingClassResource\Pages;
use App\Filament\Resources\TrainingClassResource\RelationManagers\ResourcesRelationManager;
use App\Models\ClassCompletion;
use App\Models\TrainingClass;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class TrainingClassResource extends Resource
{
    protected static ?string $model = TrainingClass::class;

    protected static ?string $navigationGroup = 'Training Portal';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('training_course_id')->relationship('course', 'name')->required()->label('Course'),
            Forms\Components\TextInput::make('title')->required()->columnSpanFull(),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            Forms\Components\Textarea::make('description')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('course.name')->badge(),
                Tables\Columns\TextColumn::make('course.program.name')->label('Program')->badge()->color('info'),
                Tables\Columns\TextColumn::make('completions_count')->counts('completions')->label('Completed by')->badge()->color('success'),
            ])
            ->actions([
                // Mark THIS class completed for one or more trainees in its program.
                Tables\Actions\Action::make('markCompleted')
                    ->label('Mark completed')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->form(fn (TrainingClass $record) => [
                        Forms\Components\Select::make('students')
                            ->label('Trainees')
                            ->multiple()
                            ->options(static::programStudents($record))
                            ->required()
                            ->helperText('Only trainees enrolled in this class’s program are listed.'),
                    ])
                    ->action(fn (TrainingClass $record, array $data) => static::complete([$record->id], $data['students'])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Mark SEVERAL classes completed for a group of trainees at once.
                    Tables\Actions\BulkAction::make('markCompleted')
                        ->label('Mark completed for trainees')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->form([
                            Forms\Components\Select::make('students')
                                ->label('Trainees')
                                ->multiple()
                                ->options(fn () => User::where('is_student', true)->orderBy('name')->pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(fn (Collection $records, array $data) => static::complete($records->pluck('id')->all(), $data['students']))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function programStudents(TrainingClass $class): array
    {
        $program = $class->course->program;

        // Students enrolled directly in this class's program OR in a composite program that includes it.
        $programIds = array_merge([$program->id], $program->partOf()->pluck('training_programs.id')->all());

        return User::where('is_student', true)
            ->whereHas('activeEnrollments', fn ($q) => $q->whereIn('training_program_id', $programIds))
            ->orderBy('name')->pluck('name', 'id')->toArray();
    }

    protected static function complete(array $classIds, array $userIds): void
    {
        foreach ($classIds as $classId) {
            foreach ($userIds as $userId) {
                $completion = ClassCompletion::updateOrCreate(
                    ['user_id' => $userId, 'training_class_id' => $classId],
                    ['completed_at' => now(), 'marked_by' => auth()->id()],
                );

                if ($completion->wasRecentlyCreated) {
                    $class = TrainingClass::with('course.program')->find($classId);
                    User::find($userId)?->notify(new \App\Notifications\PortalAlert(
                        title: 'Class completed: '.$class?->title,
                        body: 'Marked complete in '.$class?->course?->name.'. Keep it up!',
                        url: route('portal.class', $classId),
                        icon: 'CircleCheck',
                        color: 'success',
                    ));
                }
            }
        }

        \Filament\Notifications\Notification::make()
            ->title('Classes marked completed')
            ->body(count($classIds).' class(es) × '.count($userIds).' trainee(s).')
            ->success()->send();
    }

    public static function getRelations(): array
    {
        return [ResourcesRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTrainingClasses::route('/'),
            'create' => Pages\CreateTrainingClass::route('/create'),
            'edit'   => Pages\EditTrainingClass::route('/{record}/edit'),
        ];
    }
}
