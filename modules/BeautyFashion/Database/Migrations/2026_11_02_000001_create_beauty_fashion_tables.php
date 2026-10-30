<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('size_charts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('note')->nullable();
            $table->json('headers');   // ["Size","Chest (in)", ...]
            $table->json('rows');      // [["S","34-36"], ...]
            $table->timestamps();
        });

        Schema::create('product_details', function (Blueprint $table) {
            $table->unsignedInteger('product_id')->primary();
            $table->text('ingredients')->nullable();
            $table->text('how_to_use')->nullable();
            $table->text('benefits')->nullable();
            $table->unsignedSmallInteger('pao_months')->nullable();         // period after opening
            $table->unsignedSmallInteger('shelf_life_months')->nullable();
            $table->string('net_content', 60)->nullable();                   // 50 ml / 30 g
            $table->string('country_of_origin', 80)->nullable();
            $table->string('material', 190)->nullable();                     // 100% cotton
            $table->text('care_instructions')->nullable();
            $table->string('model_info', 190)->nullable();
            $table->unsignedInteger('size_chart_id')->nullable();
            $table->timestamps();
        });

        Schema::create('product_batches', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id')->index();
            $table->unsignedInteger('variant_id')->nullable();
            $table->string('batch_no', 60);
            $table->date('mfg_date')->nullable();
            $table->date('expiry_date')->index();
            $table->integer('qty')->default(0);
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        foreach (['product_batches', 'product_details', 'size_charts'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
