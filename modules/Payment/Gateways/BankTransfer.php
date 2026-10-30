<?php

namespace Modules\Payment\Gateways;

use Illuminate\Http\Request;
use Modules\Order\Entities\Order;
use Modules\Payment\GatewayInterface;
use Modules\Payment\Responses\NullResponse;

class BankTransfer implements GatewayInterface
{
    public $label;
    public $description;
    public $instructions;


    public function __construct()
    {
        $this->label = setting('bank_transfer_label');
        $this->description = setting('bank_transfer_description');
        $this->instructions = nl2br(e((string) setting('bank_transfer_instructions')));
    }


    /** Manual verification: the order stays "pending payment" until an admin confirms the money arrived. */
    public function purchase(Order $order, Request $request)
    {
        return new NullResponse($order);
    }


    public function complete(Order $order)
    {
        return new NullResponse($order);
    }
}
