<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnrollmentResource\Pages;
use App\Filament\Resources\EnrollmentResource\RelationManagers\PaymentsRelationManager;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Notifications\PortalAlert;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static ?string $navigationGroup = 'Training Portal';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Enrolments';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')->label('Trainee')
                ->options(fn () => User::where('is_student', true)->orderBy('name')->pluck('name', 'id'))
                ->searchable()->required(),
            Forms\Components\Select::make('training_program_id')->label('Program')->relationship('program', 'name')->required()->live(),
            Forms\Components\Select::make('type')->label('Trainee type')->options([
                'full_time' => 'Full-time', 'it_siwes' => 'IT / SIWES',
            ])->default('full_time')->required()->live()
                ->helperText('IT/SIWES trainees can be billed a different default fee.'),
            Forms\Components\Select::make('status')->options([
                'pending' => 'Pending', 'active' => 'Active', 'completed' => 'Completed', 'cancelled' => 'Cancelled',
            ])->default('active')->required(),
            Forms\Components\TextInput::make('fee_amount')->label('Fee (₦)')->numeric()->prefix('₦')
                ->helperText('Set a custom amount for this trainee (e.g. a relative/discount), or leave blank to use the program default for the selected type.')
                ->placeholder(fn (Forms\Get $get) => ($p = TrainingProgram::find($get('training_program_id'))) ? number_format($p->feeForType($get('type'))) : null),
            Forms\Components\TextInput::make('fee_note')->label('Fee note (optional)')->placeholder('e.g. family discount, staff rate'),
            Forms\Components\DateTimePicker::make('started_at')->native(false),
            Forms\Components\DateTimePicker::make('ends_at')->native(false),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Trainee')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('program.name')->label('Program')->badge()->color('info'),
                Tables\Columns\TextColumn::make('type')->badge()
                    ->formatStateUsing(fn (Enrollment $r) => $r->typeLabel())
                    ->color(fn ($state) => $state === 'it_siwes' ? 'warning' : 'gray'),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'active' => 'success', 'pending' => 'warning', 'completed' => 'primary', 'cancelled' => 'danger', default => 'gray',
                }),
                Tables\Columns\TextColumn::make('fee')->label('Fee')->money('NGN')->state(fn (Enrollment $r) => $r->feeAmount()),
                Tables\Columns\TextColumn::make('paid')->label('Paid')->money('NGN')->state(fn (Enrollment $r) => $r->amountPaid())->color('success'),
                Tables\Columns\TextColumn::make('outstanding')->label('Outstanding')->money('NGN')
                    ->state(fn (Enrollment $r) => $r->outstanding())
                    ->color(fn (Enrollment $r) => $r->outstanding() > 0 ? 'danger' : 'success')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('started_at')->date()->placeholder('—')->toggleable(),
                Tables\Columns\IconColumn::make('certificate')->label('Cert')
                    ->state(fn (Enrollment $r) => $r->certificate()->exists())->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending' => 'Pending', 'active' => 'Active', 'completed' => 'Completed', 'cancelled' => 'Cancelled',
                ]),
                Tables\Filters\SelectFilter::make('training_program_id')->label('Program')->relationship('program', 'name'),
                Tables\Filters\SelectFilter::make('type')->label('Trainee type')->options([
                    'full_time' => 'Full-time', 'it_siwes' => 'IT / SIWES',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')->icon('heroicon-o-check-circle')->color('success')
                    ->visible(fn (Enrollment $record) => $record->status === 'pending')
                    ->action(fn (Enrollment $record) => static::approve($record)),
                Tables\Actions\Action::make('issueCertificate')
                    ->label('Issue certificate')->icon('heroicon-o-academic-cap')->color('primary')
                    ->visible(fn (Enrollment $record) => $record->isComplete() && ! $record->certificate()->exists())
                    ->requiresConfirmation()
                    ->modalDescription('This issues a verifiable certificate for a fully-completed program.')
                    ->action(fn (Enrollment $record) => static::issueCertificate($record)),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')->label('Approve')->icon('heroicon-o-check-circle')->color('success')
                        ->action(fn ($records) => $records->each(fn (Enrollment $e) => static::approve($e)))->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function approve(Enrollment $enrollment): void
    {
        $program = TrainingProgram::find($enrollment->training_program_id);
        $enrollment->update([
            'status'      => 'active',
            'fee_amount'  => $enrollment->fee_amount ?? $program?->feeForType($enrollment->type),
            'started_at'  => $enrollment->started_at ?? now(),
            'ends_at'     => $enrollment->ends_at ?? now()->addWeeks($program?->duration_weeks ?? 12),
            'approved_at' => now(),
        ]);

        $enrollment->user?->notify(new PortalAlert(
            title: 'Enrolment approved',
            body: 'You now have access to '.$program?->name.'. Happy learning!',
            url: route('portal.program', $program),
            icon: 'CheckCircle',
            color: 'success',
        ));
    }

    protected static function issueCertificate(Enrollment $enrollment): void
    {
        $program = $enrollment->program;

        $certificate = Certificate::create([
            'user_id'             => $enrollment->user_id,
            'training_program_id' => $enrollment->training_program_id,
            'enrollment_id'       => $enrollment->id,
            'certificate_id'      => 'LTC-'.strtoupper(Str::random(8)),
            'student_name'        => $enrollment->user?->displayName(),
            'course_name'         => $program?->name,
            'issue_date'          => now(),
            'grade'               => 'Completed',
            'status'              => 'valid',
        ]);

        $enrollment->update(['status' => 'completed']);

        $enrollment->user?->notify(new PortalAlert(
            title: 'Certificate issued 🎓',
            body: 'Your certificate for '.$program?->name.' is ready to download.',
            url: route('portal.certificates'),
            icon: 'Award',
            color: 'success',
        ));

        \Filament\Notifications\Notification::make()
            ->title('Certificate '.$certificate->certificate_id.' issued')->success()->send();
    }

    public static function getRelations(): array
    {
        return [PaymentsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEnrollments::route('/'),
            'create' => Pages\CreateEnrollment::route('/create'),
            'edit'   => Pages\EditEnrollment::route('/{record}/edit'),
        ];
    }
}
