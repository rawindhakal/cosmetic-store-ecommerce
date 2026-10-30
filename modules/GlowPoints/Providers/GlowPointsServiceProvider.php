<?php

namespace Modules\GlowPoints\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Order\Entities\Order;
use Modules\Order\Events\OrderStatusChanged;
use Modules\User\Entities\User;
use Modules\GlowPoints\Services\GlowPoints;

class GlowPointsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (!config('app.installed')) {
            return;
        }

        Event::listen(OrderStatusChanged::class, function (OrderStatusChanged $event) {
            $order = $event->order->fresh();

            if (in_array($order->status, [Order::CANCELED, Order::REFUNDED], true)) {
                GlowPoints::reverseForOrder($order);
            } else {
                GlowPoints::reinstateForOrder($order);
                GlowPoints::earnForOrder($order);
            }
        });

        // Welcome bonus for customers who register on the storefront.
        User::created(function (User $user) {
            if (!request()->is('admin*')) {
                GlowPoints::signupBonus($user->id);
            }
        });

        view()->composer('storefront::public.account.layout', function ($view) {
            $view->with('glowPointsName', GlowPoints::name())->with('glowPointsEnabled', GlowPoints::enabled());
        });
    }
}
