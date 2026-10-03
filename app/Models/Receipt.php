<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'paid_at'           => 'datetime',
        'issued_at'         => 'datetime',
        'emailed_at'        => 'datetime',
        'amount'            => 'integer',
        'fee_total'         => 'integer',
        'amount_paid_total' => 'integer',
        'balance'           => 'integer',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(FeePayment::class, 'fee_payment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function isVoid(): bool
    {
        return $this->status === 'void';
    }

    public function getRouteKeyName(): string
    {
        return 'receipt_number';
    }

    /* ── Issuing ── */

    /** Create (or return the existing) receipt for a recorded payment, snapshotting all details. */
    public static function issueFor(FeePayment $payment): self
    {
        if ($payment->relationLoaded('receipt') && $payment->receipt) {
            return $payment->receipt;
        }
        if ($existing = static::where('fee_payment_id', $payment->id)->first()) {
            return $existing;
        }

        $enrollment = $payment->enrollment()->with('program', 'user')->first();
        $program = $enrollment?->program;

        return static::create([
            'receipt_number'    => static::nextNumber(),
            'fee_payment_id'    => $payment->id,
            'user_id'           => $payment->user_id,
            'enrollment_id'     => $payment->enrollment_id,
            'payer_name'        => $enrollment?->user?->displayName(),
            'payer_email'       => $enrollment?->user?->email,
            'program_name'      => $program?->name,
            'description'       => 'Training fee'.($program ? ' — '.$program->name : '').($enrollment?->type === 'it_siwes' ? ' (IT/SIWES)' : ''),
            'amount'            => $payment->amount,
            'currency'          => 'NGN',
            'method'            => $payment->method,
            'reference'         => $payment->reference,
            'paid_at'           => $payment->paid_at ?? $payment->created_at,
            'fee_total'         => $enrollment?->feeAmount() ?? 0,
            'amount_paid_total' => $enrollment?->amountPaid() ?? $payment->amount,
            'balance'           => $enrollment?->outstanding() ?? 0,
            'issued_by'         => auth()->id(),
            'issued_at'         => now(),
            'status'            => 'issued',
        ]);
    }

    /** Sequential, human-readable receipt number, e.g. RCP-2026-0007. */
    public static function nextNumber(): string
    {
        $prefix = strtoupper(setting('receipt_prefix', 'RCP'));
        $year = (int) now()->format('Y');
        $seq = static::whereYear('created_at', $year)->count() + 1;

        do {
            $number = sprintf('%s-%d-%04d', $prefix, $year, $seq);
            $seq++;
        } while (static::where('receipt_number', $number)->exists());

        return $number;
    }

    /* ── Presentation helpers ── */

    public function amountInWords(): string
    {
        return static::numberToWords($this->amount).' '.($this->currency === 'NGN' ? 'naira' : $this->currency).' only';
    }

    public function methodLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->method));
    }

    /** Business/branding block pulled from site + receipt settings. */
    public function business(): array
    {
        return [
            'name'      => setting('site_name', 'Latechify Digital Hub'),
            'address'   => setting('topbar_address'),
            'phone'     => setting('contact_phone'),
            'email'     => setting('contact_email'),
            'logo'      => static::logoDataUri(),
            'signature' => static::signatureDataUri(),
            'signatory' => setting('receipt_signatory'),
            'accent'    => setting('receipt_accent_color') ?: '#031273',
            'title'     => setting('receipt_title') ?: 'RECEIPT',
        ];
    }

    public static function logoDataUri(): ?string
    {
        return static::fileToDataUri(setting('logo'));
    }

    /** The uploaded signature image (Settings → Receipts), reusable across the app. */
    public static function signatureDataUri(): ?string
    {
        return static::fileToDataUri(setting('receipt_signature'));
    }

    /** Resolve a stored image path/URL to a base64 data URI (dompdf-safe, no external fetch). */
    protected static function fileToDataUri(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $candidates = [
            public_path($value),
            public_path('storage/'.ltrim($value, '/')),
            public_path(ltrim(parse_url($value, PHP_URL_PATH) ?? '', '/')),
        ];

        foreach ($candidates as $path) {
            if ($path && is_file($path)) {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION)) ?: 'png';
                $mime = $ext === 'svg' ? 'image/svg+xml' : ($ext === 'jpg' ? 'image/jpeg' : 'image/'.$ext);

                return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
            }
        }

        return null;
    }

    /** Integer → English words (supports up to trillions). */
    public static function numberToWords(int $number): string
    {
        if ($number === 0) {
            return 'zero';
        }

        $ones = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten',
            'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
        $tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
        $scales = ['', ' thousand', ' million', ' billion', ' trillion'];

        $chunkToWords = function (int $n) use ($ones, $tens): string {
            $words = '';
            if ($n >= 100) {
                $words .= $ones[intdiv($n, 100)].' hundred';
                $n %= 100;
                if ($n) {
                    $words .= ' and ';
                }
            }
            if ($n >= 20) {
                $words .= $tens[intdiv($n, 10)];
                if ($n % 10) {
                    $words .= '-'.$ones[$n % 10];
                }
            } elseif ($n > 0) {
                $words .= $ones[$n];
            }

            return $words;
        };

        $parts = [];
        $scaleIndex = 0;
        while ($number > 0) {
            $chunk = $number % 1000;
            if ($chunk > 0) {
                $parts[] = $chunkToWords($chunk).$scales[$scaleIndex];
            }
            $number = intdiv($number, 1000);
            $scaleIndex++;
        }

        return ucfirst(trim(implode(', ', array_reverse($parts))));
    }
}
