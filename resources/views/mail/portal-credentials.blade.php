<x-mail::message>
# Welcome aboard, {{ $student->displayName() }} 🎉

Your payment for **{{ $order->itemName() }}** has been confirmed by our team, and your trainee
portal is now active.

<x-mail::panel>
**Course:** {{ $order->itemName() }} ({{ $order->formatLabel() }})
**Program:** {{ $order->programName() }}
**Amount paid:** ₦{{ number_format($order->amount) }}
**Payment reference:** {{ $order->payment_reference ?: '—' }}
</x-mail::panel>

## Signing in

**Portal URL:** {{ $loginUrl }}
**Email:** {{ $student->email }}
@if ($password)
**Temporary password:** {{ $password }}

Please change this password from **Profile → Password** right after your first sign-in.
@else
Use the password you already have for your account. Forgot it? Reply to this email and we'll reset it.
@endif

<x-mail::button :url="$loginUrl">
Open my portal
</x-mail::button>

Inside the portal you'll find your class slides and materials, schedule, assignments,
fee statement and receipts.

Thanks,<br>
{{ setting('site_name', 'Latechify') }}
</x-mail::message>
