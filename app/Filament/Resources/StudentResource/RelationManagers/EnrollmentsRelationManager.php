<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use App\Models\Enrollment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    protected static ?string $title = 'Program enrolments';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('training_program_id')->relationship('program', 'name')->required()->label('Program')->live(),
            Forms\Components\Select::make('type')->label('Trainee type')->options([
                'full_time' => 'Full-time', 'it_siwes' => 'IT / SIWES',
            ])->default('full_time')->required()->live(),
            Forms\Components\Select::make('status')->options([
                'pending' => 'Pending', 'active' => 'Active', 'completed' => 'Completed', 'cancelled' => 'Cancelled',
            ])->default('active')->required(),
            Forms\Components\TextInput::make('fee_amount')->label('Fee (₦)')->numeric()->prefix('₦')
                ->helperText('Leave blank for the program default for the selected type.')
                ->placeholder(fn (Forms\Get $get) => ($p = \App\Models\TrainingProgram::find($get('training_program_id'))) ? number_format($p->feeForType($get('type'))) : null),
            Forms\Components\TextInput::make('fee_note')->label('Fee note')->placeholder('e.g. family discount')->columnSpanFull(),
            Forms\Components\DateTimePicker::make('started_at'),
            Forms\Components\DateTimePicker::make('ends_at'),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('program.name')->label('Program')->badge()->color('info'),
                Tables\Columns\TextColumn::make('type')->badge()
                    ->formatStateUsing(fn (Enrollment $r) => $r->typeLabel())
                    ->color(fn ($state) => $state === 'it_siwes' ? 'warning' : 'gray'),
                Tables\Columns\TextColumn::make('fee')->label('Fee')->money('NGN')->state(fn (Enrollment $r) => $r->feeAmount())
                    ->description(fn (Enrollment $r) => $r->fee_note),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'active' => 'success', 'pending' => 'warning', 'cancelled' => 'danger', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('started_at')->date()->toggleable(),
                Tables\Columns\TextColumn::make('ends_at')->date()->toggleable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->mutateFormDataUsing(function (array $data) {
                    return static::withSchedule($data);
                }),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')->icon('heroicon-o-check-circle')->color('success')
                    ->visible(fn (Enrollment $record) => $record->status === 'pending')
                    ->action(function (Enrollment $record) {
                        $record->update(static::withSchedule(['training_program_id' => $record->training_program_id, 'status' => 'active']));
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    /** Fill start/end dates from the program duration when activating. */
    protected static function withSchedule(array $data): array
    {
        if (($data['status'] ?? null) === 'active') {
            $program = \App\Models\TrainingProgram::find($data['training_program_id'] ?? null);
            $data['started_at'] ??= now();
            $data['ends_at'] ??= now()->addWeeks($program?->duration_weeks ?? 12);
            $data['fee_amount'] ??= $program?->feeForType($data['type'] ?? 'full_time');
            $data['approved_at'] = now();
        }

        return $data;
    }
}
