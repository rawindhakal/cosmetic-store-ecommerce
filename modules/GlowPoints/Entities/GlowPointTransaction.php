<?php

namespace Modules\GlowPoints\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;
use Modules\Order\Entities\Order;

class GlowPointTransaction extends Model
{
    protected $guarded = [];

    protected $casts = ['expires_at' => 'datetime'];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
