<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('Payment result') }} — {{ config('app.name') }}</title>
    <style>
        :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        body { min-height: 100vh; margin: 0; display: grid; place-items: center; background: #faf7f2; color: #292524; }
        main { width: min(92%, 34rem); padding: 2rem; border: 1px solid #e7e5e4; border-radius: 1rem; background: white; box-shadow: 0 1rem 3rem rgb(41 37 36 / 8%); text-align: center; }
        .icon { width: 4rem; height: 4rem; margin: 0 auto 1rem; display: grid; place-items: center; border-radius: 999px; font-size: 2rem; }
        .success { background: #dcfce7; color: #166534; }
        .pending { background: #fef3c7; color: #92400e; }
        .failed { background: #fee2e2; color: #991b1b; }
        h1 { margin: 0 0 .75rem; font-size: 1.6rem; }
        p { margin: .5rem 0; color: #57534e; line-height: 1.6; }
        .reference { margin-top: 1.25rem; padding: .75rem; border-radius: .5rem; background: #f5f5f4; font-family: ui-monospace, monospace; }
    </style>
</head>
<body>
    <main>
        @if ($successful)
            <div class="icon success" aria-hidden="true">✓</div>
            <h1>{{ __('Payment completed successfully') }}</h1>
            <p>{{ __('Your order has been received and is being processed.') }}</p>
        @elseif ($pending)
            <div class="icon pending" aria-hidden="true">…</div>
            <h1>{{ __('Payment is being processed') }}</h1>
            <p>{{ __('Please check your order again shortly.') }}</p>
        @else
            <div class="icon failed" aria-hidden="true">!</div>
            <h1>{{ $verified ? __('Payment was not completed') : __('Invalid payment response') }}</h1>
            <p>{{ $verified ? __('Your order remains unpaid.') : __('The payment response signature could not be verified.') }}</p>
        @endif

        @if ($order)
            <p class="reference">{{ __('Order') }}: {{ $order->order_number }}</p>
        @endif
    </main>
</body>
</html>
