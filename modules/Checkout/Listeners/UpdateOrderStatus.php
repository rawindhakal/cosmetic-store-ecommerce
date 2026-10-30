<?php

namespace Modules\Checkout\Listeners;

use Modules\Order\Entities\Order;
use Modules\Checkout\Events\OrderPlaced;

class UpdateOrderStatus
{
    /**
     * Handle the event.
     *
     * @param OrderPlaced $event
     *
     * @return void
     */
    public function handle($event)
    {
        // Manually verified methods stay "pending payment" until an admin confirms the money arrived.
        $manual = in_array($event->order->getRawOriginal('payment_method'), ['qr_payment', 'bank_transfer'], true);

        $event->order->update(['status' => $manual ? Order::PENDING_PAYMENT : Order::PENDING]);
    }
}
