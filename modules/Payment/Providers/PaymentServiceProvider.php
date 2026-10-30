<?php

namespace Modules\Payment\Providers;

use Modules\Payment\Gateways\COD;
use Modules\Payment\Gateways\QrPayment;
use Modules\Payment\Gateways\BankTransfer;
use Modules\Payment\Facades\Gateway;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Cash on delivery, QR payment and direct bank transfer (the last two are verified manually by an admin).
     */
    public function boot()
    {
        if (!config('app.installed')) {
            return;
        }

        if (app('inAdminPanel') || setting('cod_enabled')) {
            Gateway::register('cod', new COD());
        }

        if (app('inAdminPanel') || setting('qr_payment_enabled')) {
            Gateway::register('qr_payment', new QrPayment());
        }

        if (app('inAdminPanel') || setting('bank_transfer_enabled')) {
            Gateway::register('bank_transfer', new BankTransfer());
        }
    }


    public function register()
    {
    }
}
