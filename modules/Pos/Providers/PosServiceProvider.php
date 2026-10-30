<?php

namespace Modules\Pos\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Modules\Pos\Services\Code128;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;

class PosServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (!config('app.installed')) {
            return;
        }

        // Every new product / variant automatically gets a unique in-store EAN-13 barcode.
        Product::created(fn ($p) => $this->assign('products', $p->id));
        ProductVariant::created(fn ($v) => $this->assign('product_variants', $v->id));
    }

    private function assign(string $table, int $id): void
    {
        if (DB::table($table)->where('id', $id)->whereNotNull('barcode')->exists()) {
            return;
        }

        do {
            $code = Code128::generateEan13(random_int(100000000, 999999999));
        } while (DB::table('products')->where('barcode', $code)->exists() || DB::table('product_variants')->where('barcode', $code)->exists());

        DB::table($table)->where('id', $id)->update(['barcode' => $code]);
    }
}
