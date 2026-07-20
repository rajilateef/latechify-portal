<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CertificateResource\Pages;
use App\Models\Certificate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CertificateResource extends Resource
{
    protected static ?string $model = Certificate::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationGroup = 'About & Content';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('certificate_id')->required()->unique(ignoreRecord: true)
                ->helperText('The ID students enter to verify, e.g. LAT-2025-0001'),
            Forms\Components\TextInput::make('student_name')->required(),
            Forms\Components\TextInput::make('course_name')->required(),
            Forms\Components\DatePicker::make('issue_date'),
            Forms\Components\TextInput::make('grade'),
            Forms\Components\Select::make('status')->options(['valid' => 'Valid', 'revoked' => 'Revoked'])->default('valid')->required(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('certificate_id')->searchable()->copyable()->weight('bold'),
                Tables\Columns\TextColumn::make('student_name')->searchable(),
                Tables\Columns\TextColumn::make('course_name')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('issue_date')->date('M j, Y'),
                Tables\Columns\TextColumn::make('grade')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()->colors(['success' => 'valid', 'danger' => 'revoked']),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['valid' => 'Valid', 'revoked' => 'Revoked']),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCertificates::route('/'),
            'create' => Pages\CreateCertificate::route('/create'),
            'edit'   => Pages\EditCertificate::route('/{record}/edit'),
        ];
    }
}
