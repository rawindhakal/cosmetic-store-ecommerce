<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('glow_point_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_id')->index();
            $table->unsignedInteger('order_id')->nullable()->index();
            $table->string('type', 20)->index(); // earn, redeem, refund, reverse, expire, bonus, adjust
            $table->integer('points');
            $table->integer('balance_after')->default(0);
            $table->string('description')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->integer('glow_points_redeemed')->default(0);
            $table->decimal('glow_points_discount', 18, 4)->default(0);
            $table->integer('glow_points_earned')->default(0);
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['glow_points_redeemed', 'glow_points_discount', 'glow_points_earned']);
        });

        Schema::dropIfExists('glow_point_transactions');
    }
};
