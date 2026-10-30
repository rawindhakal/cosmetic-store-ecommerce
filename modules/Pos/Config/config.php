<?php

/** Default POS settings; override in admin (POS -> Settings) stored as "pos_<key>". */
return [
    'store_name' => '',               // blank = store name
    'address' => 'Thamel, Kathmandu, Nepal',
    'phone' => '',
    'tax_id' => '',                   // PAN / VAT number printed on the bill
    'tax_mode' => 'inclusive',        // inclusive | exclusive | none
    'tax_rate' => 13,
    'tax_label' => 'VAT',
    'receipt_title' => 'TAX INVOICE',
    'receipt_footer' => 'Thank you for shopping with us!',
    'return_policy' => 'Exchange within 7 days with bill. No cash refund.',
    'show_logo' => true,
    'show_barcode' => true,
    'show_savings' => true,
    'paper_width' => 80,              // mm
    'font_size' => 12,                // px
    'auto_print' => true,
    'round_total' => true,
    'low_stock' => 5,
    'invoice_prefix' => 'INV-',
    'require_register' => true,
];
