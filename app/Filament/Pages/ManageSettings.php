<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Site Settings';

    protected static ?string $title = 'Site Settings';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    /** key => group */
    protected array $map = [
        // general
        'site_name' => 'general', 'site_tagline' => 'general', 'logo' => 'general', 'logo_white' => 'general',
        'brand_badge_title' => 'general', 'brand_badge_sub' => 'general', 'footer_about' => 'general', 'cta_label' => 'general',
        // contact
        'contact_address' => 'contact', 'topbar_address' => 'contact', 'contact_phone' => 'contact', 'contact_phone2' => 'contact',
        'contact_email' => 'contact', 'whatsapp_number' => 'contact', 'notification_email' => 'contact', 'business_hours' => 'contact',
        'map_embed_url' => 'contact', 'directions_url' => 'contact',
        // social
        'instagram_url' => 'social', 'facebook_url' => 'social', 'twitter_url' => 'social', 'linkedin_url' => 'social',
        // payment
        'currency' => 'payment',
        'monnify_api_key' => 'payment', 'monnify_secret_key' => 'payment', 'monnify_contract_code' => 'payment', 'monnify_live' => 'payment',
        'bank_name' => 'payment', 'bank_account_name' => 'payment', 'bank_account_number' => 'payment',
        'enterprise_base_price' => 'payment', 'enterprise_price_per_student' => 'payment',
        // summer camp
        'camp_title' => 'camp', 'camp_subtitle' => 'camp', 'camp_badge' => 'camp', 'camp_dates' => 'camp',
        'camp_duration' => 'camp', 'camp_location' => 'camp', 'camp_fee_physical' => 'camp', 'camp_fee_virtual' => 'camp', 'camp_image' => 'camp',
        'camp_highlights' => 'camp', 'camp_tracks' => 'camp',
        // home
        'about_heading' => 'home', 'about_body_1' => 'home', 'about_body_2' => 'home', 'about_image' => 'home',
        'services_heading' => 'home', 'services_sub' => 'home', 'cohort_name' => 'home', 'cohort_status' => 'home',
        'cohort_image' => 'home', 'hero_background' => 'home', 'testimonial_card_heading' => 'home',
        'promo_heading' => 'home', 'promo_sub' => 'home',
        // section headings (home page sections)
        'services_eyebrow' => 'sections', 'services_statement' => 'sections',
        'benefits_eyebrow' => 'sections', 'benefits_heading' => 'sections', 'benefits_sub' => 'sections',
        'tech_eyebrow' => 'sections', 'tech_heading' => 'sections', 'tech_sub' => 'sections',
        'testimonials_eyebrow' => 'sections', 'testimonials_heading' => 'sections', 'testimonials_sub' => 'sections',
        'blog_eyebrow' => 'sections', 'blog_heading' => 'sections', 'blog_sub' => 'sections',
        'cohort_heading' => 'sections', 'cohort_sub' => 'sections',
        'faq_heading' => 'sections', 'faq_sub' => 'sections',
        'partners_eyebrow' => 'sections', 'partners_heading' => 'sections', 'partners_sub' => 'sections',
        'contact_cta_heading' => 'sections', 'contact_cta_sub' => 'sections',
        // receipts
        'receipt_prefix' => 'receipts', 'receipt_title' => 'receipts', 'receipt_accent_color' => 'receipts',
        'receipt_signatory' => 'receipts', 'receipt_signature' => 'receipts', 'receipt_footer' => 'receipts',
        // seo
        'meta_title' => 'seo', 'meta_description' => 'seo',
    ];

    public function mount(): void
    {
        $values = Setting::pluck('value', 'key')->toArray();
        $this->form->fill(collect(array_keys($this->map))->mapWithKeys(fn ($k) => [$k => $values[$k] ?? null])->toArray());
    }

    public function form(Form $form): Form
    {
        return $form->statePath('data')->schema([
            Forms\Components\Tabs::make()->columnSpanFull()->persistTabInQueryString()->tabs([
                Forms\Components\Tabs\Tab::make('General')->schema([
                    Forms\Components\TextInput::make('site_name'),
                    Forms\Components\TextInput::make('site_tagline'),
                    \App\Filament\Forms\Components\MediaPicker::make('logo')->label('Logo'),
                    \App\Filament\Forms\Components\MediaPicker::make('logo_white')->label('Logo (white)'),
                    Forms\Components\TextInput::make('brand_badge_title'),
                    Forms\Components\TextInput::make('brand_badge_sub'),
                    Forms\Components\TextInput::make('cta_label')->label('Header CTA label'),
                    Forms\Components\Textarea::make('footer_about')->rows(3)->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('Contact')->schema([
                    Forms\Components\TextInput::make('contact_email')->email(),
                    Forms\Components\TextInput::make('notification_email')->label('Notifications inbox')->email()->helperText('New applications & messages are emailed here.'),
                    Forms\Components\TextInput::make('contact_phone'),
                    Forms\Components\TextInput::make('contact_phone2')->label('Secondary phone'),
                    Forms\Components\TextInput::make('whatsapp_number')->helperText('International format, no +, e.g. 2348064539275'),
                    Forms\Components\TextInput::make('topbar_address')->label('Top-bar address'),
                    Forms\Components\Textarea::make('contact_address')->rows(2)->columnSpanFull(),
                    Forms\Components\TextInput::make('business_hours')->helperText('Separate lines with a | e.g. Mon-Fri: 9-5|Sat: 12-5|Sun: Closed')->columnSpanFull(),
                    Forms\Components\Textarea::make('map_embed_url')->label('Google Maps embed URL')->rows(2)->columnSpanFull(),
                    Forms\Components\TextInput::make('directions_url')->label('Directions URL')->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('Social')->schema([
                    Forms\Components\TextInput::make('instagram_url')->url(),
                    Forms\Components\TextInput::make('facebook_url')->url(),
                    Forms\Components\TextInput::make('twitter_url')->url(),
                    Forms\Components\TextInput::make('linkedin_url')->url(),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('Payment')->schema([
                    Forms\Components\TextInput::make('currency')->default('NGN'),
                    Forms\Components\Placeholder::make('_monnify')->label('Monnify (Checkout)')
                        ->content(function () {
                            $monnify = app(\App\Services\Monnify::class);

                            $status = $monnify->isConfigured()
                                ? '✅ Online payment is LIVE on the website checkout ('.($monnify->isLive() ? 'live mode' : 'sandbox / test mode').').'
                                : '⚠️ Online payment is OFF — all three fields below are required before "Pay online" appears at checkout. Visitors currently see bank transfer only.';

                            return new \Illuminate\Support\HtmlString(
                                '<strong>'.e($status).'</strong><br>'
                                .'Powers the website Checkout, portal fee payments and Summer Camp registration. '
                                .'Every reference we send is LATECHIFY-prefixed.'
                            );
                        })
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('monnify_api_key')->label('Monnify API key'),
                    Forms\Components\TextInput::make('monnify_secret_key')->label('Monnify secret key')->password()->revealable()->helperText('Stored privately.'),
                    Forms\Components\TextInput::make('monnify_contract_code')->label('Monnify contract code'),
                    Forms\Components\Toggle::make('monnify_live')->label('Live mode')->helperText('Off = sandbox (test). On = live payments.'),
                    Forms\Components\Placeholder::make('_monnify_webhook')->label('Webhook URL')
                        ->content(fn () => new \Illuminate\Support\HtmlString(
                            'Paste this into Monnify → Settings → API Keys &amp; Webhooks as the <strong>Transaction Completion</strong> URL:<br>'
                            .'<code class="select-all font-mono text-primary-600">'.e(url('/webhooks/monnify')).'</code><br>'
                            .'<span class="text-xs">One URL covers checkout, portal fee payments and camp registrations. '
                            .'Deliveries are listed under Training Portal → Payment webhooks.</span>'
                        ))
                        ->columnSpanFull(),
                    Forms\Components\Placeholder::make('_bank')->label('Bank transfer details')->content('Shown to applicants who pay by transfer.')->columnSpanFull(),
                    Forms\Components\TextInput::make('bank_name'),
                    Forms\Components\TextInput::make('bank_account_name'),
                    Forms\Components\TextInput::make('bank_account_number'),
                    Forms\Components\TextInput::make('enterprise_base_price')->numeric()->prefix('₦'),
                    Forms\Components\TextInput::make('enterprise_price_per_student')->numeric()->prefix('₦'),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('Home page')->schema([
                    Forms\Components\TextInput::make('about_heading'),
                    \App\Filament\Forms\Components\MediaPicker::make('about_image')->label('About image'),
                    Forms\Components\Textarea::make('about_body_1')->rows(3)->columnSpanFull(),
                    Forms\Components\Textarea::make('about_body_2')->rows(3)->columnSpanFull(),
                    Forms\Components\TextInput::make('services_heading'),
                    Forms\Components\Textarea::make('services_sub')->rows(2),
                    Forms\Components\TextInput::make('cohort_name'),
                    Forms\Components\TextInput::make('cohort_status'),
                    \App\Filament\Forms\Components\MediaPicker::make('cohort_image')->label('Cohort image'),
                    \App\Filament\Forms\Components\MediaPicker::make('hero_background')->label('Hero background'),
                    Forms\Components\TextInput::make('testimonial_card_heading')->columnSpanFull(),
                    Forms\Components\TextInput::make('promo_heading')->label('Promotions section heading'),
                    Forms\Components\Textarea::make('promo_sub')->rows(2),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('Summer Camp')->schema([
                    Forms\Components\TextInput::make('camp_title')->columnSpanFull(),
                    Forms\Components\Textarea::make('camp_subtitle')->rows(2)->columnSpanFull(),
                    Forms\Components\TextInput::make('camp_badge')->label('Hero badge')->helperText('e.g. "Registration Open"'),
                    Forms\Components\TextInput::make('camp_fee_physical')->label('Physical fee')->numeric()->prefix('₦')->helperText('On-site attendance fee (0 = free).'),
                    Forms\Components\TextInput::make('camp_fee_virtual')->label('Virtual fee')->numeric()->prefix('₦')->helperText('Online attendance fee (0 = free).'),
                    Forms\Components\TextInput::make('camp_dates')->label('Dates')->helperText('e.g. "Aug 4 – Aug 29, 2025"'),
                    Forms\Components\TextInput::make('camp_duration')->label('Duration')->helperText('e.g. "4 weeks · Mon–Fri"'),
                    Forms\Components\TextInput::make('camp_location')->label('Location'),
                    \App\Filament\Forms\Components\MediaPicker::make('camp_image')->label('Camp image'),
                    Forms\Components\Textarea::make('camp_highlights')->rows(5)->label("What's included")->helperText('One highlight per line.')->columnSpanFull(),
                    Forms\Components\Textarea::make('camp_tracks')->rows(4)->label('Tracks')->helperText('One track per line — these fill the registration form dropdown.')->columnSpanFull(),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('Section headings')->schema([
                    Forms\Components\Fieldset::make('Services')->schema([
                        Forms\Components\TextInput::make('services_eyebrow')->label('Eyebrow'),
                        Forms\Components\Textarea::make('services_statement')->label('Statement')->rows(2)->columnSpanFull(),
                    ]),
                    Forms\Components\Fieldset::make('Why Choose Us')->schema([
                        Forms\Components\TextInput::make('benefits_eyebrow')->label('Eyebrow'),
                        Forms\Components\TextInput::make('benefits_heading')->label('Heading'),
                        Forms\Components\Textarea::make('benefits_sub')->label('Subtitle')->rows(2)->columnSpanFull(),
                    ]),
                    Forms\Components\Fieldset::make('Tech Stack')->schema([
                        Forms\Components\TextInput::make('tech_eyebrow')->label('Eyebrow'),
                        Forms\Components\TextInput::make('tech_heading')->label('Heading'),
                        Forms\Components\Textarea::make('tech_sub')->label('Subtitle')->rows(2)->columnSpanFull(),
                    ]),
                    Forms\Components\Fieldset::make('Testimonials')->schema([
                        Forms\Components\TextInput::make('testimonials_eyebrow')->label('Eyebrow'),
                        Forms\Components\TextInput::make('testimonials_heading')->label('Heading'),
                        Forms\Components\Textarea::make('testimonials_sub')->label('Subtitle')->rows(2)->columnSpanFull(),
                    ]),
                    Forms\Components\Fieldset::make('Blog')->schema([
                        Forms\Components\TextInput::make('blog_eyebrow')->label('Eyebrow'),
                        Forms\Components\TextInput::make('blog_heading')->label('Heading'),
                        Forms\Components\Textarea::make('blog_sub')->label('Subtitle')->rows(2)->columnSpanFull(),
                    ]),
                    Forms\Components\Fieldset::make('Cohort')->schema([
                        Forms\Components\TextInput::make('cohort_heading')->label('Heading'),
                        Forms\Components\Textarea::make('cohort_sub')->label('Subtitle')->rows(2)->columnSpanFull(),
                    ]),
                    Forms\Components\Fieldset::make('FAQ')->schema([
                        Forms\Components\TextInput::make('faq_heading')->label('Heading'),
                        Forms\Components\Textarea::make('faq_sub')->label('Subtitle')->rows(2)->columnSpanFull(),
                    ]),
                    Forms\Components\Fieldset::make('Partners & Recognition')->schema([
                        Forms\Components\TextInput::make('partners_eyebrow')->label('Eyebrow'),
                        Forms\Components\TextInput::make('partners_heading')->label('Heading'),
                        Forms\Components\Textarea::make('partners_sub')->label('Subtitle')->rows(2)->columnSpanFull(),
                    ]),
                    Forms\Components\Fieldset::make('Contact call-to-action')->schema([
                        Forms\Components\TextInput::make('contact_cta_heading')->label('Heading'),
                        Forms\Components\Textarea::make('contact_cta_sub')->label('Subtitle')->rows(2)->columnSpanFull(),
                    ]),
                ]),

                Forms\Components\Tabs\Tab::make('Receipts')->schema([
                    Forms\Components\Placeholder::make('_receipt_info')->label('Payment receipts')
                        ->content('Customise the look of the PDF receipts issued to trainees. Business name, address, phone, email and logo are taken from the General & Contact tabs.')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('receipt_title')->label('Document title')->placeholder('RECEIPT')
                        ->helperText('Heading shown top-right, e.g. RECEIPT or PAYMENT RECEIPT.'),
                    Forms\Components\TextInput::make('receipt_prefix')->label('Receipt number prefix')->placeholder('RCP')
                        ->helperText('Numbers look like PREFIX-YEAR-0001. Changing this only affects new receipts.'),
                    Forms\Components\ColorPicker::make('receipt_accent_color')->label('Accent colour')
                        ->helperText('Used for the header, table and totals. Defaults to your brand blue.'),
                    Forms\Components\TextInput::make('receipt_signatory')->label('Signatory name / title')
                        ->helperText('Printed under the signature line, e.g. "Accounts Department".'),
                    \App\Filament\Forms\Components\MediaPicker::make('receipt_signature')->label('Signature image')
                        ->helperText('Upload a signature (PNG with a transparent background works best). Appears above the signatory on every receipt.')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('receipt_footer')->label('Footer note')->rows(3)->columnSpanFull()
                        ->helperText('Small print at the bottom of the receipt.'),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('SEO')->schema([
                    Forms\Components\TextInput::make('meta_title')->columnSpanFull(),
                    Forms\Components\Textarea::make('meta_description')->rows(2)->columnSpanFull(),
                ]),
            ]),
        ]);
    }

    /**
     * Prove the Monnify credentials actually work. Auth can succeed while the
     * contract code belongs to another account, so this exercises the same call
     * checkout makes and reports Monnify's own message.
     */
    public function testMonnify(): void
    {
        [$ok, $message] = app(\App\Services\Monnify::class)->testConnection();

        Notification::make()
            ->title($ok ? 'Monnify connected' : 'Monnify is not working yet')
            ->body($message)
            ->{$ok ? 'success' : 'danger'}()
            ->persistent()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('testMonnify')
                ->label('Test Monnify connection')
                ->icon('heroicon-o-signal')
                ->color('gray')
                ->action('testMonnify'),
        ];
    }

    public function save(): void
    {
        foreach ($this->form->getState() as $key => $value) {
            if (! array_key_exists($key, $this->map)) {
                continue;
            }
            Setting::put($key, is_array($value) ? ($value[0] ?? null) : $value, $this->map[$key]);
        }

        Notification::make()->title('Settings saved')->success()->send();
    }
}
