<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\StudentResource;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingActivationsWidget extends BaseWidget
{
    protected static ?string $heading = 'Trainees awaiting activation';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()->where('is_student', true)->where('is_active', false)->latest()
            )
            ->emptyStateHeading('No trainees awaiting activation')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->copyable()->icon('heroicon-m-envelope'),
                Tables\Columns\TextColumn::make('enrollments_count')->counts('enrollments')->label('Programs')->badge(),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Registered')->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('activate')
                    ->label('Activate')->icon('heroicon-o-check-circle')->color('success')
                    ->requiresConfirmation()
                    ->action(fn (User $record) => $record->update(['is_active' => true])),
                Tables\Actions\Action::make('view')
                    ->label('Open')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')
                    ->url(fn (User $record) => StudentResource::getUrl('edit', ['record' => $record])),
            ]);
    }

    protected function getTableQuery(): ?Builder
    {
        return User::query()->where('is_student', true)->where('is_active', false)->latest();
    }
}
