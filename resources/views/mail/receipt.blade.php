<x-mail::message>
# Payment receipt

Hello {{ $receipt->payer_name ?: 'there' }},

We've received your payment and attached your official receipt.

**Receipt no:** {{ $receipt->receipt_number }}
**Amount paid:** ₦{{ number_format($receipt->amount) }} ({{ $receipt->methodLabel() }})
**Program:** {{ $receipt->program_name ?? '—' }}
**Date:** {{ $receipt->paid_at?->format('M j, Y') }}

@if ($receipt->balance > 0)
Your outstanding balance is **₦{{ number_format($receipt->balance) }}**.
@else
Your fees are **fully paid** — thank you!
@endif

The full receipt is attached as a PDF. You can also view it anytime from your **Fees & payments** page in the portal.

<x-mail::button :url="route('portal.fees')">
View my fees
</x-mail::button>

Thanks,<br>
{{ setting('site_name', 'Latechify Digital Hub') }}
</x-mail::message>
