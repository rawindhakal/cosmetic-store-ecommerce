<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('barcode', 64)->nullable()->index();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('barcode', 64)->nullable()->index();
        });

        Schema::create('pos_registers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->decimal('opening_cash', 18, 4)->default(0);
            $table->decimal('closing_cash', 18, 4)->nullable();
            $table->decimal('expected_cash', 18, 4)->nullable();
            $table->text('note')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pos_sales', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_id')->unique();
            $table->unsignedInteger('register_id')->nullable()->index();
            $table->unsignedInteger('cashier_id')->nullable();
            $table->string('invoice_no')->index();
            $table->json('payments');           // [{method, amount}]
            $table->decimal('tendered', 18, 4)->default(0);
            $table->decimal('change_due', 18, 4)->default(0);
            $table->decimal('tax_amount', 18, 4)->default(0);
            $table->decimal('rounding', 18, 4)->default(0);
            $table->string('discount_label')->nullable();
            $table->json('lines');              // snapshot for the receipt
            $table->timestamps();
        });

        Schema::create('pos_holds', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('label')->nullable();
            $table->json('payload');
            $table->timestamps();
        });

        Schema::create('pos_returns', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_id')->index();
            $table->unsignedInteger('user_id');
            $table->string('return_no');
            $table->json('items');
            $table->decimal('amount', 18, 4);
            $table->string('refund_method')->default('cash');
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('pos_cash_movements', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('register_id')->index();
            $table->unsignedInteger('user_id');
            $table->string('type', 10); // in | out
            $table->decimal('amount', 18, 4);
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        foreach (['pos_cash_movements', 'pos_returns', 'pos_holds', 'pos_sales', 'pos_registers'] as $t) {
            Schema::dropIfExists($t);
        }
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('barcode'));
        Schema::table('product_variants', fn (Blueprint $t) => $t->dropColumn('barcode'));
    }
};
