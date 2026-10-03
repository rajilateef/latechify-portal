<?php

namespace App\Filament\Resources\AssignmentResource\RelationManagers;

use App\Models\AssignmentSubmission;
use App\Notifications\PortalAlert;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'submissions';

    protected static ?string $title = 'Submissions & grading';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('score')
                ->numeric()->minValue(0)
                ->maxValue(fn () => $this->getOwnerRecord()->max_score)
                ->suffix('/ '.$this->getOwnerRecord()->max_score),
            Forms\Components\Textarea::make('feedback')->rows(4)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Trainee')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('submitted_at')->dateTime('M j, g:ia')->label('Submitted')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'graded' => 'success', 'returned' => 'danger', default => 'warning',
                }),
                Tables\Columns\TextColumn::make('score')
                    ->badge()->color('info')
                    ->formatStateUsing(fn ($state, $record) => $state === null ? '—' : $state.' / '.$record->assignment->max_score),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'submitted' => 'Awaiting grade', 'graded' => 'Graded', 'returned' => 'Returned',
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('View work')
                    ->infolist([
                        \Filament\Infolists\Components\TextEntry::make('content')->label('Response')->placeholder('—')->columnSpanFull(),
                        \Filament\Infolists\Components\TextEntry::make('link')->label('Link')->url(fn ($record) => $record->link)->openUrlInNewTab()->placeholder('—'),
                        \Filament\Infolists\Components\TextEntry::make('file_path')->label('File')
                            ->formatStateUsing(fn ($state) => $state ? 'Download' : '—')
                            ->url(fn ($record) => $record->file_path ? media_url($record->file_path) : null)->openUrlInNewTab(),
                    ]),
                Tables\Actions\Action::make('grade')
                    ->icon('heroicon-o-check-badge')->color('success')
                    ->form(fn (Form $form) => $this->form($form))
                    ->fillForm(fn (AssignmentSubmission $record) => [
                        'score' => $record->score, 'feedback' => $record->feedback,
                    ])
                    ->action(function (AssignmentSubmission $record, array $data) {
                        $record->update([
                            'score'     => $data['score'],
                            'feedback'  => $data['feedback'] ?? null,
                            'status'    => 'graded',
                            'graded_at' => now(),
                            'graded_by' => auth()->id(),
                        ]);

                        $record->user?->notify(new PortalAlert(
                            title: 'Assignment graded: '.$record->assignment->title,
                            body: 'You scored '.$data['score'].' / '.$record->assignment->max_score.'.',
                            url: route('portal.assignments.show', $record->assignment_id),
                            icon: 'ClipboardCheck',
                            color: 'success',
                        ));
                    }),
            ]);
    }
}
