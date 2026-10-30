<?php

namespace Modules\Setting\Admin;

use Modules\Admin\Ui\Tab;
use Modules\Admin\Ui\Tabs;
use Modules\Support\Locale;
use Modules\Support\Country;
use Modules\Support\TimeZone;
use Modules\Currency\Currency;
use Modules\User\Entities\Role;
use Modules\Media\Entities\File;
use Illuminate\Support\Facades\Cache;

class SettingTabs extends Tabs
{
    /**
     * Make new tabs with groups.
     *
     * @return void
     */
    public function make()
    {
        $this->group('general_settings', trans('setting::settings.tabs.group.general_settings'))
            ->active()
            ->add($this->general())
            ->add($this->logo())
            ->add($this->maintenance())
            ->add($this->store())
            ->add($this->PWA())
            ->add($this->currency())
            ->add($this->sms())
            ->add($this->mail())
            ->add($this->newsletter())
            ->add($this->googleRecaptcha())
            ->add($this->customCssJs());

        $this->group('social_logins', trans('setting::settings.tabs.group.social_logins'))
            ->add($this->facebook())
            ->add($this->google());

        $this->group('shipping_methods', trans('setting::settings.tabs.group.shipping_methods'))
            ->add($this->freeShipping())
            ->add($this->localPickup())
            ->add($this->flatRate());

        $this->group('payment_methods', trans('setting::settings.tabs.group.payment_methods'))
            ->add($this->cod())
            ->add($this->qrPayment())
            ->add($this->bankTransfer());
    }


    private function general()
    {
        return tap(new Tab('general', trans('setting::settings.tabs.general')), function (Tab $tab) {
            $tab->active();
            $tab->weight(5);

            $tab->fields(['supported_countries.*', 'default_country', 'default_timezone', 'customer_role']);

            $tab->view('setting::admin.settings.tabs.general', [
                'countries' => Country::all(),
                'timeZones' => TimeZone::all(),
                'roles' => Role::list(),
            ]);
        });
    }

    private function logo()
    {
        return tap(new Tab('logo', trans('setting::settings.tabs.logo')), function (Tab $tab) {
            $tab->weight(10);
            $tab->view('setting::admin.settings.tabs.logo', [
                'logo' => $this->getMedia(setting('admin_logo')),
                'shortLogo' => $this->getMedia(setting('admin_small_logo')),
            ]);
        });
    }


    private function maintenance()
    {
        return tap(new Tab('maintenance', trans('setting::settings.tabs.maintenance')), function (Tab $tab) {
            $tab->weight(7);

            $tab->view('setting::admin.settings.tabs.maintenance');
        });
    }


    private function store()
    {
        return tap(new Tab('store', trans('setting::settings.tabs.store')), function (Tab $tab) {
            $tab->weight(10);

            $tab->fields(['translatable.store_name', 'translatable.store_tagline', 'store_phone', 'store_email', 'store_address_1', 'store_address_2', 'store_city', 'store_country', 'store_state', 'store_zip']);

            $tab->view('setting::admin.settings.tabs.store', [
                'countries' => Country::all(),
            ]);
        });
    }


    private function PWA()
    {
        return tap(new Tab('pwa', trans('setting::settings.tabs.pwa')), function (Tab $tab) {
            $tab->weight(15);

            $tab->fields([
                'pwa_icon',
                'pwa_theme_color',
                'pwa_background_color',
                'pwa_status_bar',
                'pwa_display',
                'pwa_orientation',
                'translatable.pwa_direction',
            ]);

            $directions = [
                'ltr' => trans('setting::settings.form.pwa_directions.ltr'),
                'rtl' => trans('setting::settings.form.pwa_directions.rtl'),
                'auto' => trans('setting::settings.form.pwa_directions.auto'),
            ];

            $orientations = [
                'any' => trans('setting::settings.form.pwa_orientations.any'),
                'natural' => trans('setting::settings.form.pwa_orientations.natural'),
                'landscape' => trans('setting::settings.form.pwa_orientations.landscape'),
                'portrait' => trans('setting::settings.form.pwa_orientations.portrait'),
                'portrait-primary' => trans('setting::settings.form.pwa_orientations.portrait-primary'),
                'portrait-secondary' => trans('setting::settings.form.pwa_orientations.portrait-secondary'),
                'landscape-primary' => trans('setting::settings.form.pwa_orientations.landscape-primary'),
                'landscape-secondary' => trans('setting::settings.form.pwa_orientations.landscape-secondary'),
            ];

            $displays = [
                'fullscreen' => trans('setting::settings.form.pwa_displays.fullscreen'),
                'standalone' => trans('setting::settings.form.pwa_displays.standalone'),
                'minimal-ui' => trans('setting::settings.form.pwa_displays.minimal-ui'),
                'browser' => trans('setting::settings.form.pwa_displays.browser'),
            ];

            $tab->view('setting::admin.settings.tabs.pwa', [
                'icon' => $this->getMedia(setting('pwa_icon')),
                'directions' => $directions,
                'orientations' => $orientations,
                'displays' => $displays,
            ]);
        });
    }


    private function getMedia($fileId)
    {
        return Cache::rememberForever(md5("files.{$fileId}"), function () use ($fileId) {
            return File::findOrNew($fileId);
        });
    }


    private function currency()
    {
        return tap(new Tab('currency', trans('setting::settings.tabs.currency')), function (Tab $tab) {
            $tab->weight(20);

            $tab->fields(['supported_currencies.*', 'default_currency', 'currency_rate_exchange_service', 'fixer_access_key', 'forge_api_key', 'currency_data_feed_api_key', 'auto_refresh_currency_rates', 'auto_refresh_currency_rate_frequency']);

            $tab->view('setting::admin.settings.tabs.currency', [
                'currencies' => Currency::names(),
                'currencyRateExchangeServices' => $this->getCurrencyRateExchangeServices(),
            ]);
        });
    }


    private function getCurrencyRateExchangeServices()
    {
        $currencyRateExchangeServices = ['' => trans('setting::settings.form.select_service')];

        $currencyRateExchangeServices += trans('currency::services');

        return $currencyRateExchangeServices;
    }


    private function sms()
    {
        return tap(new Tab('sms', trans('setting::settings.tabs.sms')), function (Tab $tab) {
            $tab->weight(25);

            $tab->fields(['sms_service', 'vonage_key', 'vonage_secret', 'twilio_sid', 'twilio_token', 'sms_order_statuses']);

            $tab->view('setting::admin.settings.tabs.sms', [
                'smsServices' => $this->getSmsServices(),
                'orderStatuses' => trans('order::statuses'),
            ]);
        });
    }


    private function getSmsServices()
    {
        $smsServices = ['' => trans('setting::settings.form.select_service')];

        return $smsServices += trans('sms::services');
    }


    private function mail()
    {
        return tap(new Tab('mail', trans('setting::settings.tabs.mail')), function (Tab $tab) {
            $tab->weight(30);

            $tab->fields(['mail_from_address']);

            $tab->view('setting::admin.settings.tabs.mail', [
                'encryptionProtocols' => $this->getMailEncryptionProtocols(),
                'orderStatuses' => trans('order::statuses'),
            ]);
        });
    }


    private function getMailEncryptionProtocols()
    {
        return ['' => trans('admin::admin.form.please_select')] + trans('setting::settings.form.mail_encryption_protocols');
    }


    private function newsletter()
    {
        return tap(new Tab('newsletter', trans('setting::settings.tabs.newsletter')), function (Tab $tab) {
            $tab->weight(32);

            $tab->fields(['newsletter_enabled', 'mailchimp_api_key', 'mailchimp_list_id']);

            $tab->view('setting::admin.settings.tabs.newsletter');
        });
    }


    private function googleRecaptcha()
    {
        return tap(new Tab('google_recaptcha', trans('setting::settings.tabs.google_recaptcha')), function (Tab $tab) {
            $tab->weight(35);

            $tab->fields(['google_recaptcha_enabled', 'google_recaptcha_site_key', 'google_recaptcha_secret_key']);

            $tab->view('setting::admin.settings.tabs.google_recaptcha');
        });
    }


    private function customCssJs()
    {
        return tap(new Tab('custom_css_js', trans('setting::settings.tabs.custom_css_js')), function (Tab $tab) {
            $tab->weight(40);

            $tab->view('setting::admin.settings.tabs.custom_css_js');
        });
    }


    private function facebook()
    {
        return tap(new Tab('facebook', trans('setting::settings.tabs.facebook')), function (Tab $tab) {
            $tab->weight(41);

            $tab->fields(['facebook_login_enabled', 'translatable.facebook_login_label', 'facebook_login_app_id', 'facebook_login_app_secret']);

            $tab->view('setting::admin.settings.tabs.facebook');
        });
    }


    private function google()
    {
        return tap(new Tab('google', trans('setting::settings.tabs.google')), function (Tab $tab) {
            $tab->weight(42);

            $tab->fields(['google_login_enabled', 'translatable.google_login_label', 'google_login_client_id', 'google_login_client_secret']);

            $tab->view('setting::admin.settings.tabs.google');
        });
    }


    private function freeShipping()
    {
        return tap(new Tab('free_shipping', trans('setting::settings.tabs.free_shipping')), function (Tab $tab) {
            $tab->weight(50);

            $tab->fields(['free_shipping_enabled', 'translatable.free_shipping_label']);

            $tab->view('setting::admin.settings.tabs.free_shipping');
        });
    }


    private function localPickup()
    {
        return tap(new Tab('local_pickup', trans('setting::settings.tabs.local_pickup')), function (Tab $tab) {
            $tab->weight(55);

            $tab->fields(['local_pickup_enabled', 'translatable.local_pickup_label', 'local_pickup_cost']);

            $tab->view('setting::admin.settings.tabs.local_pickup');
        });
    }


    private function flatRate()
    {
        return tap(new Tab('flat_rate', trans('setting::settings.tabs.flat_rate')), function (Tab $tab) {
            $tab->weight(60);

            $tab->fields(['flat_rate_enabled', 'translatable.flat_rate_label', 'flat_rate_cost']);

            $tab->view('setting::admin.settings.tabs.flat_rate');
        });
    }















    private function cod()
    {
        return tap(new Tab('cod', trans('setting::settings.tabs.cod')), function (Tab $tab) {
            $tab->weight(72);

            $tab->fields(['cod_enabled', 'translatable.cod_label', 'translatable.cod_description']);

            $tab->view('setting::admin.settings.tabs.cod');
        });
    }


    private function qrPayment()
    {
        return tap(new Tab('qr_payment', 'QR Payment'), function (Tab $tab) {
            $tab->weight(73);

            $tab->fields(['qr_payment_enabled', 'translatable.qr_payment_label', 'translatable.qr_payment_description', 'translatable.qr_payment_instructions']);

            $tab->view('setting::admin.settings.tabs.qr_payment', [
                'qrImage' => $this->getMedia(setting('qr_payment_image')),
            ]);
        });
    }


    private function bankTransfer()
    {
        return tap(new Tab('bank_transfer', 'Bank Transfer'), function (Tab $tab) {
            $tab->weight(74);

            $tab->fields(['bank_transfer_enabled', 'translatable.bank_transfer_label', 'translatable.bank_transfer_description', 'translatable.bank_transfer_instructions']);

            $tab->view('setting::admin.settings.tabs.bank_transfer');
        });
    }
}
