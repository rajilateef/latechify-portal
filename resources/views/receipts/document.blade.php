@php
    $b = $receipt->business();
    $preview = $preview ?? false;
    $accent = $b['accent'];
    $title = $b['title'];
    $sig = $b['signature'];
    $signatory = $b['signatory'] ?: ($receipt->issuedBy?->name ?: $b['name']);
    $fmt = fn ($n) => '₦'.number_format((int) $n);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Receipt {{ $receipt->receipt_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "DejaVu Sans", sans-serif; color: #1f2937; font-size: 12px; margin: 0; padding: 0; }
        .sheet { padding: 34px 40px; }
        .muted { color: #6b7280; }
        .right { text-align: right; }
        table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: top; }
        .brand-name { font-size: 20px; font-weight: bold; color: {{ $accent }}; }
        .brand-meta { font-size: 10.5px; color: #6b7280; line-height: 1.5; margin-top: 3px; }
        .doc-title { font-size: 22px; font-weight: bold; letter-spacing: 1px; color: {{ $accent }}; }
        .rcp-meta { font-size: 11px; color: #374151; line-height: 1.7; margin-top: 4px; }
        .rcp-meta b { color: #111827; }
        .rule { border: none; border-top: 2px solid {{ $accent }}; margin: 18px 0; }
        .panel { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px 14px; }
        .label { font-size: 9.5px; text-transform: uppercase; letter-spacing: .6px; color: #6b7280; margin-bottom: 3px; }
        .items { margin-top: 16px; }
        .items th { background: {{ $accent }}; color: #fff; text-align: left; padding: 9px 12px; font-size: 11px; }
        .items th.amt, .items td.amt { text-align: right; }
        .items td { padding: 11px 12px; border-bottom: 1px solid #e5e7eb; }
        .items tr.total td { font-weight: bold; font-size: 13px; border-top: 2px solid {{ $accent }}; border-bottom: none; padding-top: 12px; }
        .words { margin-top: 10px; font-size: 11px; }
        .words .val { font-style: italic; color: #111827; }
        .summary td { padding: 6px 0; font-size: 11.5px; }
        .summary .k { color: #6b7280; }
        .summary .v { text-align: right; font-weight: bold; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 10px; font-weight: bold; }
        .badge-paid { background: #dcfce7; color: #166534; }
        .badge-due { background: #fee2e2; color: #991b1b; }
        .foot { margin-top: 26px; font-size: 10.5px; color: #6b7280; }
        .sign { margin-top: 30px; }
        .sign .line { border-top: 1px solid #9ca3af; width: 200px; padding-top: 4px; font-size: 10.5px; color: #374151; }
        .void { position: fixed; top: 40%; left: 0; right: 0; text-align: center; font-size: 120px; font-weight: bold;
                color: rgba(220,38,38,.12); transform: rotate(-24deg); letter-spacing: 8px; }
        .bar { background: {{ $accent }}; color: #fff; padding: 10px 16px; text-align: center; font-size: 13px; }
        .bar a { color: #fff; text-decoration: none; background: rgba(255,255,255,.15); padding: 7px 14px; border-radius: 6px; margin: 0 4px; }
        @media print { .bar { display: none; } }
    </style>
</head>
<body>
    @if ($preview)
        <div class="bar">
            <a href="{{ route('receipts.download', $receipt) }}">⬇ Download PDF</a>
            <a href="#" onclick="window.print();return false;">🖨 Print</a>
        </div>
    @endif

    @if ($receipt->isVoid())<div class="void">VOID</div>@endif

    <div class="sheet">
        <table class="header">
            <tr>
                <td style="width:60%;">
                    @if ($b['logo'])<img src="{{ $b['logo'] }}" alt="" style="height:46px; margin-bottom:6px;">@endif
                    <div class="brand-name">{{ $b['name'] }}</div>
                    <div class="brand-meta">
                        @if ($b['address']){{ $b['address'] }}<br>@endif
                        @if ($b['phone'])Tel: {{ $b['phone'] }}@endif @if ($b['email']) · {{ $b['email'] }}@endif
                    </div>
                </td>
                <td class="right" style="width:40%;">
                    <div class="doc-title">{{ $title }}</div>
                    <div class="rcp-meta">
                        <b>No:</b> {{ $receipt->receipt_number }}<br>
                        <b>Date:</b> {{ $receipt->issued_at?->format('M j, Y') }}<br>
                        @if ($receipt->isVoid())<span class="badge badge-due">VOID</span>
                        @elseif ($receipt->balance <= 0)<span class="badge badge-paid">PAID IN FULL</span>
                        @else <span class="badge badge-due">BALANCE {{ $fmt($receipt->balance) }}</span>@endif
                    </div>
                </td>
            </tr>
        </table>

        <hr class="rule">

        <table>
            <tr>
                <td style="width:55%; vertical-align:top;">
                    <div class="label">Received from</div>
                    <div style="font-size:14px; font-weight:bold;">{{ $receipt->payer_name ?: '—' }}</div>
                    @if ($receipt->payer_email)<div class="muted">{{ $receipt->payer_email }}</div>@endif
                    @if ($receipt->program_name)<div class="muted" style="margin-top:4px;">Program: {{ $receipt->program_name }}</div>@endif
                </td>
                <td style="width:45%; vertical-align:top;">
                    <div class="panel">
                        <table>
                            <tr><td class="muted">Payment date</td><td class="right"><b>{{ $receipt->paid_at?->format('M j, Y') }}</b></td></tr>
                            <tr><td class="muted">Method</td><td class="right"><b>{{ $receipt->methodLabel() }}</b></td></tr>
                            @if ($receipt->reference)<tr><td class="muted">Reference</td><td class="right"><b>{{ $receipt->reference }}</b></td></tr>@endif
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr><th>Description</th><th class="amt">Amount</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $receipt->description }}</td>
                    <td class="amt">{{ $fmt($receipt->amount) }}</td>
                </tr>
                <tr class="total">
                    <td>Total paid (this receipt)</td>
                    <td class="amt">{{ $fmt($receipt->amount) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="words">
            <span class="muted">Amount in words:</span> <span class="val">{{ $receipt->amountInWords() }}</span>
        </div>

        <table style="margin-top:22px;">
            <tr>
                <td style="width:58%;"></td>
                <td style="width:42%;">
                    <table class="summary">
                        <tr><td class="k">Program fee</td><td class="v">{{ $fmt($receipt->fee_total) }}</td></tr>
                        <tr><td class="k">Total paid to date</td><td class="v" style="color:#166534;">{{ $fmt($receipt->amount_paid_total) }}</td></tr>
                        <tr><td class="k">Outstanding balance</td><td class="v" style="color:{{ $receipt->balance > 0 ? '#991b1b' : '#166534' }};">{{ $fmt($receipt->balance) }}</td></tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="sign">
            <tr>
                <td style="width:60%; vertical-align:bottom;">
                    <div class="foot">
                        {{ setting('receipt_footer', 'Thank you for your payment. This is a computer-generated receipt and is valid without a physical signature.') }}
                    </div>
                </td>
                <td style="width:40%; vertical-align:bottom;" class="right">
                    <div style="display:inline-block; width:210px; text-align:center;">
                        @if ($sig)<img src="{{ $sig }}" alt="signature" style="max-height:48px; margin-bottom:2px;">@endif
                        <div class="line" style="width:210px; text-align:left;">{{ $signatory }}</div>
                        <div class="muted" style="font-size:9.5px; text-align:left;">Issued by / Authorised signatory</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
