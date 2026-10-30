<?php

namespace Modules\Payment\Gateways;

use Illuminate\Http\Request;
use Modules\Order\Entities\Order;
use Modules\Payment\GatewayInterface;
use Modules\Payment\Responses\NullResponse;

class QrPayment implements GatewayInterface
{
    public $label;
    public $description;
    public $instructions;


    public function __construct()
    {
        $this->label = setting('qr_payment_label');
        $this->description = setting('qr_payment_description');
        $this->instructions = self::buildInstructions();
    }


    public static function buildInstructions(): string
    {
        $html = '';
        $file = setting('qr_payment_image') ? \Modules\Media\Entities\File::find(setting('qr_payment_image')) : null;

        if ($file && $file->path) {
            $html .= '<img src="' . e($file->path) . '" alt="QR" style="max-width:240px;width:100%;display:block;margin:8px 0;border:1px solid #eee;border-radius:8px;padding:6px">';
        }

        return $html . nl2br(e((string) setting('qr_payment_instructions')));
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
