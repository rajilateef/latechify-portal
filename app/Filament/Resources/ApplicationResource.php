<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationResource\Pages;
use App\Filament\Resources\ApplicationResource\RelationManagers;
use App\Models\Application;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationGroup = 'Submissions';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Applicant')->schema([
                Forms\Components\TextInput::make('full_name')->required(),
                Forms\Components\TextInput::make('email')->email()->required(),
                Forms\Components\TextInput::make('phone')->required(),
                Forms\Components\TextInput::make('heard_about')->label('Heard about us'),
                Forms\Components\TextInput::make('education'),
                Forms\Components\TextInput::make('experience'),
                Forms\Components\Textarea::make('motivation')->rows(3)->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Course & Payment')->schema([
                Forms\Components\Select::make('course_id')
                    ->label('Course')
                    ->options(Course::pluck('title', 'id'))
                    ->searchable(),
                Forms\Components\TextInput::make('package'),
                Forms\Components\TextInput::make('price')->numeric()->prefix('₦'),
                Forms\Components\Select::make('class_format')->options(['online' => 'Online', 'physical' => 'Physical'])->default('online'),
                Forms\Components\Select::make('payment_method')->options(['paystack' => 'Paystack', 'transfer' => 'Bank Transfer'])->default('paystack'),
                Forms\Components\Select::make('status')->options([
                    'pending' => 'Pending', 'reviewing' => 'Reviewing', 'accepted' => 'Accepted', 'rejected' => 'Rejected', 'paid' => 'Paid',
                ])->default('pending')->required(),
                Forms\Components\TextInput::make('reference')->label('Payment reference'),
                Forms\Components\DateTimePicker::make('paid_at'),
                Forms\Components\Textarea::make('notes')->rows(2)->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('full_name')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('email')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('phone')->toggleable(),
                Tables\Columns\TextColumn::make('course.title')->label('Course')->toggleable(),
                Tables\Columns\TextColumn::make('price')->money('NGN')->sortable(),
                Tables\Columns\TextColumn::make('class_format')->badge()->colors(['info' => 'online', 'gray' => 'physical']),
                Tables\Columns\TextColumn::make('payment_method')->badge(),
                Tables\Columns\TextColumn::make('status')->badge()->colors([
                    'warning' => 'pending', 'info' => 'reviewing', 'primary' => 'accepted', 'danger' => 'rejected', 'success' => 'paid',
                ]),
                Tables\Columns\TextColumn::make('created_at')->dateTime('M j, Y')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending' => 'Pending', 'reviewing' => 'Reviewing', 'accepted' => 'Accepted', 'rejected' => 'Rejected', 'paid' => 'Paid',
                ]),
                Tables\Filters\SelectFilter::make('payment_method')->options(['paystack' => 'Paystack', 'transfer' => 'Bank Transfer']),
                Tables\Filters\SelectFilter::make('class_format')->options(['online' => 'Online', 'physical' => 'Physical']),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
        return [
            RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'edit'   => Pages\EditApplication::route('/{record}/edit'),
        ];
    }
}
