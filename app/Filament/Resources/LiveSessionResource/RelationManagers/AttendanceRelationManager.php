<?php

namespace App\Filament\Resources\LiveSessionResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AttendanceRelationManager extends RelationManager
{
    protected static string $relationship = 'attendances';

    protected static ?string $title = 'Attendance';

    /** Trainees enrolled in this session's program. */
    protected function roster(): array
    {
        $programId = $this->getOwnerRecord()->training_program_id;

        return User::where('is_student', true)
            ->whereHas('activeEnrollments', fn ($q) => $q->where('training_program_id', $programId))
            ->orderBy('name')->pluck('name', 'id')->all();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')->label('Trainee')
                ->options(fn () => $this->roster())->searchable()->required(),
            Forms\Components\Select::make('status')->options([
                'present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'excused' => 'Excused',
            ])->default('present')->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Trainee')->searchable()->sortable(),
                Tables\Columns\SelectColumn::make('status')->options([
                    'present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'excused' => 'Excused',
                ])->selectablePlaceholder(false),
                Tables\Columns\TextColumn::make('updated_at')->since()->label('Marked'),
            ])
            ->headerActions([
                // Add every enrolled trainee (not already listed) as present in one click.
                Tables\Actions\Action::make('loadRoster')
                    ->label('Load roster (all present)')
                    ->icon('heroicon-o-user-group')->color('gray')
                    ->requiresConfirmation()
                    ->action(function () {
                        $session = $this->getOwnerRecord();
                        $existing = $session->attendances()->pluck('user_id')->all();
                        foreach ($this->roster() as $userId => $name) {
                            if (! in_array($userId, $existing)) {
                                $session->attendances()->create([
                                    'user_id' => $userId, 'status' => 'present', 'marked_by' => auth()->id(),
                                ]);
                            }
                        }
                    }),
                Tables\Actions\CreateAction::make()
                    ->label('Add trainee')
                    ->mutateFormDataUsing(function (array $data) {
                        $data['marked_by'] = auth()->id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
