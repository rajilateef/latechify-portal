<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrainingProgramResource\Pages;
use App\Filament\Resources\TrainingProgramResource\RelationManagers\CoursesRelationManager;
use App\Models\TrainingProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class TrainingProgramResource extends Resource
{
    protected static ?string $model = TrainingProgram::class;

    protected static ?string $navigationGroup = 'Training Portal';

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Programs';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
                Forms\Components\TextInput::make('duration_weeks')->numeric()->default(12)->suffix('weeks')->required()
                    ->helperText('Used to compute each trainee’s time spent / remaining.'),
                Forms\Components\TextInput::make('fee')->label('Full-time fee')->numeric()->default(0)->prefix('₦')
                    ->helperText('Default fee for full-time trainees (editable per enrolment).'),
                Forms\Components\TextInput::make('siwes_fee')->label('IT / SIWES fee')->numeric()->default(0)->prefix('₦')
                    ->helperText('Default fee for IT/SIWES trainees. Leave 0 to use the full-time fee.'),
                Forms\Components\TextInput::make('discount_fee')->label('Discounted full-time fee')->numeric()->prefix('₦')
                    ->placeholder('No discount')
                    ->lt('fee')
                    ->helperText('This is what checkout charges. Leave blank for no discount.'),
                Forms\Components\TextInput::make('discount_siwes_fee')->label('Discounted IT / SIWES fee')->numeric()->prefix('₦')
                    ->placeholder('No discount')
                    ->helperText('Leave blank to inherit the full-time discount when no separate SIWES fee is set.'),
                \App\Filament\Forms\Components\MediaPicker::make('image')->label('Cover image'),
                Forms\Components\Toggle::make('is_active')->default(true),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\Select::make('components')
                    ->label('Included programs (bundle)')
                    ->relationship(
                        name: 'components',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query, ?TrainingProgram $record) => $query->whereKeyNot($record?->id ?? 0),
                    )
                    ->multiple()->searchable()->preload()
                    ->helperText('Optional. Trainees enrolled in THIS program also get access to the courses of the programs you select here — e.g. Fullstack = Frontend + Backend.')
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('courses_count')->counts('courses')->label('Courses')->badge(),
                Tables\Columns\TextColumn::make('components.name')->label('Includes')->badge()->color('warning')->placeholder('—'),
                Tables\Columns\TextColumn::make('duration_weeks')->suffix(' wks')->label('Duration'),
                Tables\Columns\TextColumn::make('fee')->label('Full-time')->money('NGN')->sortable()
                    ->description(fn ($record) => $record->hasDiscountForType('full_time')
                        ? 'now ₦'.number_format($record->feeForType('full_time')).' (-'.$record->discountPercentForType('full_time').'%)'
                        : null),
                Tables\Columns\TextColumn::make('siwes_fee')->label('IT/SIWES')->money('NGN')
                    ->formatStateUsing(fn ($state, $record) => '₦'.number_format($record->siwes_fee ?: $record->fee))->toggleable(),
                Tables\Columns\TextColumn::make('enrollments_count')->counts('enrollments')->label('Trainees')->badge()->color('info'),
                Tables\Columns\ToggleColumn::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()]),
            ]);
    }

    public static function getRelations(): array
    {
        return [CoursesRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTrainingPrograms::route('/'),
            'create' => Pages\CreateTrainingProgram::route('/create'),
            'edit'   => Pages\EditTrainingProgram::route('/{record}/edit'),
        ];
    }
}
