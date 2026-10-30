@extends('storefront::public.layout')

@section('title', trans('storefront::checkout.checkout'))

@section('content')
    <section
        x-data="
            Checkout({
                customerEmail: '{{ auth()->user()->email ?? null }}',
                customerPhone: '{{ auth()->user()->phone ?? null }}',
                addresses: {{ $addresses }},
                defaultAddress: {{ $defaultAddress }},
                gateways: {{ $gateways }},
                countries: {{ json_encode($countries) }}
            })
        "
        class="checkout-wrap"
    >
        <div class="container">
            @include('storefront::public.cart.index.steps')

            <form class="checkout-form" @input="errors.clear($event.target.name)">
                <div class="checkout-inner">
                    <div class="checkout-left">
                        @include('storefront::public.checkout.create.form.account_details')
                        @include('storefront::public.checkout.create.form.billing_details')
                        @include('storefront::public.checkout.create.form.shipping_details')
                        @include('storefront::public.checkout.create.form.order_note')
                    </div>

                    <div class="checkout-right">
                        @include('storefront::public.checkout.create.payment')
                        @include('storefront::public.checkout.create.shipping')
                    </div>
                </div>

                @include('storefront::public.checkout.create.order_summary')
            </form>


        </div>
    </section>
@endsection

@push('globals')
    <script>
        FleetCart.langs['storefront::checkout.payment_for_order'] = '{{ trans("storefront::checkout.payment_for_order") }}';
        FleetCart.langs['storefront::checkout.remember_about_your_order'] = '{{ trans("storefront::checkout.remember_about_your_order") }}';
    </script>

    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/checkout/create/main.scss',
        'modules/Storefront/Resources/assets/public/js/pages/checkout/create/main.js',
    ])
@endpush
