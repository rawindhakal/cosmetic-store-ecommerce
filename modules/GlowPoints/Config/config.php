<?php

/**
 * Default GlowPoints settings. Every key can be overridden in the admin
 * (GlowPoints -> Settings), which stores it as "glowpoints_<key>".
 */
return [
    'enabled' => true,
    'name' => 'GlowPoints',
    'earn_points' => 1,            // points earned ...
    'earn_per_amount' => 100,      // ... for every this much spent (default currency)
    'earn_base' => 'subtotal',     // subtotal (after discounts) | total
    'earn_statuses' => ['completed'],
    'redeem_value' => 1,           // value of 1 point in default currency
    'min_redeem' => 100,           // minimum points per redemption
    'max_redeem_percent' => 50,    // max % of the order subtotal payable with points
    'signup_bonus' => 100,
    'first_order_bonus' => 0,
    'expiry_days' => 365,          // 0 = never expire
];
