<?php

/**
 * Concern -> product search keywords (matched against product name/description) and tips.
 * Keywords are ingredient / product-type words, so the recommendations follow your real catalogue.
 */
return [
    'keywords' => [
        'acne' => ['tea tree', 'neem', 'charcoal', 'salicylic', 'niacinamide', 'face wash', 'toner', 'clay'],
        'redness' => ['aloe', 'green tea', 'soothing', 'calming', 'honey', 'sandalwood'],
        'spots' => ['vitamin c', 'niacinamide', 'kumkumadi', 'saffron', 'papaya', 'turmeric', 'brightening'],
        'tone' => ['vitamin c', 'niacinamide', 'saffron', 'kumkumadi', 'brightening', 'papaya'],
        'darkcircles' => ['eye cream', 'under eye', 'vitamin c', 'almond', 'saffron', 'retinol'],
        'lines' => ['retinol', 'collagen', 'hyaluronic', 'eye cream', 'night cream', 'anti-aging'],
        'pores' => ['niacinamide', 'charcoal', 'clay', 'toner', 'retinol', 'face wash'],
        'oil' => ['charcoal', 'tea tree', 'niacinamide', 'face wash', 'clay', 'toner'],
        'dry' => ['hyaluronic', 'shea', 'honey', 'aloe', 'moisturizer', 'almond', 'cream', 'collagen'],
    ],
    'always' => ['sunscreen', 'spf'],
];
