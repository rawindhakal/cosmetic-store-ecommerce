<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('skin_analyses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->unsignedTinyInteger('overall_score');
            $table->string('skin_type', 30)->nullable();
            $table->string('tone_shade', 40)->nullable();
            $table->string('undertone', 20)->nullable();
            $table->json('concerns');   // [{key, score}] - no photo is ever stored
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('skin_analyses');
    }
};
