<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Courses & Services';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make()->columnSpanFull()->tabs([
                Forms\Components\Tabs\Tab::make('Details')->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, Forms\Set $set, string $operation) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                    Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
                    Forms\Components\Select::make('category')
                        ->options(['Software Engineering' => 'Software Engineering', 'Other Tech' => 'Other Tech'])
                        ->default('Software Engineering')
                        ->native(false)
                        ->required(),
                    Forms\Components\TextInput::make('subtitle')->columnSpanFull(),
                    Forms\Components\Textarea::make('description')->label('Short description (catalog card)')->rows(2)->columnSpanFull(),
                    Forms\Components\RichEditor::make('long_description')->label('About this course')->columnSpanFull(),
                    Forms\Components\TextInput::make('icon')->helperText('Lucide icon name, e.g. Layout, Database, Code')->default('Code'),
                    Forms\Components\TextInput::make('level'),
                    Forms\Components\TextInput::make('duration')->placeholder('e.g. 12 weeks'),
                    Forms\Components\TextInput::make('schedule'),
                    Forms\Components\TextInput::make('start_date')->placeholder('e.g. June 15, 2025'),
                    Forms\Components\TextInput::make('rating')->numeric()->step(0.1)->minValue(0)->maxValue(5),
                    Forms\Components\TagsInput::make('tags')->columnSpanFull(),
                    \App\Filament\Forms\Components\MediaPicker::make('image')->label('Image')->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('Pricing & Flags')->schema([
                    Forms\Components\TextInput::make('price_physical')->numeric()->prefix('₦')->default(0)->label('Physical price'),
                    Forms\Components\TextInput::make('price_online')->numeric()->prefix('₦')->default(0)->label('Online price'),
                    Forms\Components\TextInput::make('popular_feature')->label('Highlight line (pricing card)')->columnSpanFull(),
                    Forms\Components\Toggle::make('popular')->label('Popular'),
                    Forms\Components\Toggle::make('featured')->label('Featured on home'),
                    Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
                    Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('Highlights')->schema([
                    Forms\Components\Repeater::make('highlights')
                        ->relationship()
                        ->schema([Forms\Components\TextInput::make('text')->required()])
                        ->orderColumn('sort_order')
                        ->collapsible()
                        ->itemLabel(fn (array $state) => $state['text'] ?? null)
                        ->addActionLabel('Add highlight'),
                ]),

                Forms\Components\Tabs\Tab::make('Curriculum')->schema([
                    Forms\Components\Repeater::make('modules')
                        ->relationship()
                        ->schema([
                            Forms\Components\TextInput::make('week')->required()->placeholder('Week 1-2'),
                            Forms\Components\TextInput::make('title')->required(),
                            Forms\Components\TextInput::make('estimated_hours')->numeric()->default(12),
                            Forms\Components\Toggle::make('is_detailed')->helperText('Show topic descriptions & resources'),
                            Forms\Components\Repeater::make('topics')
                                ->relationship()
                                ->schema([
                                    Forms\Components\TextInput::make('title')->required()->columnSpanFull(),
                                    Forms\Components\Textarea::make('description')->rows(2)->columnSpanFull(),
                                    Forms\Components\TextInput::make('duration')->placeholder('e.g. 3 hours'),
                                    Forms\Components\Repeater::make('resources')
                                        ->relationship()
                                        ->schema([
                                            Forms\Components\TextInput::make('title')->required(),
                                            Forms\Components\Select::make('type')->options([
                                                'video' => 'Video', 'document' => 'Document', 'quiz' => 'Quiz', 'exercise' => 'Exercise',
                                            ])->default('document'),
                                            Forms\Components\TextInput::make('url')->url(),
                                        ])->orderColumn('sort_order')->columns(3)->collapsed()->addActionLabel('Add resource')->columnSpanFull(),
                                ])->orderColumn('sort_order')->columns(2)->collapsed()
                                ->itemLabel(fn (array $state) => $state['title'] ?? null)
                                ->addActionLabel('Add topic')->columnSpanFull(),
                        ])
                        ->orderColumn('sort_order')
                        ->collapsed()
                        ->itemLabel(fn (array $state) => trim(($state['week'] ?? '').' — '.($state['title'] ?? ''), ' —'))
                        ->addActionLabel('Add module'),
                ]),

                Forms\Components\Tabs\Tab::make('FAQs')->schema([
                    Forms\Components\Repeater::make('faqs')
                        ->relationship()
                        ->schema([
                            Forms\Components\TextInput::make('question')->required()->columnSpanFull(),
                            Forms\Components\Textarea::make('answer')->required()->rows(3)->columnSpanFull(),
                        ])->orderColumn('sort_order')->collapsed()
                        ->itemLabel(fn (array $state) => $state['question'] ?? null)
                        ->addActionLabel('Add FAQ'),
                ]),

                Forms\Components\Tabs\Tab::make('Pricing features')->schema([
                    Forms\Components\Repeater::make('features')
                        ->relationship()
                        ->schema([
                            Forms\Components\TextInput::make('name')->required(),
                            Forms\Components\Toggle::make('included')->default(true)->helperText('Included in this plan?'),
                        ])->orderColumn('sort_order')->columns(2)
                        ->itemLabel(fn (array $state) => $state['name'] ?? null)
                        ->addActionLabel('Add feature'),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('image')->getStateUsing(fn ($record) => media_url($record->image))->height(40),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable()->weight('bold'),
                Tables\Columns\TextColumn::make('category')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('level')->toggleable(),
                Tables\Columns\TextColumn::make('duration')->toggleable(),
                Tables\Columns\TextColumn::make('price_physical')->money('NGN')->label('Physical')->sortable(),
                Tables\Columns\TextColumn::make('price_online')->money('NGN')->label('Online')->sortable(),
                Tables\Columns\IconColumn::make('popular')->boolean(),
                Tables\Columns\ToggleColumn::make('featured'),
                Tables\Columns\ToggleColumn::make('is_active')->label('Active'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('popular'),
                Tables\Filters\TernaryFilter::make('featured'),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit'   => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
