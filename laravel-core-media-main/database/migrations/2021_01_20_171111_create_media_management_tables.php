<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaManagementTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->bigInteger('user_id')->unsigned()->nullable()->default(NULL);
            $table->text('original_name');
            $table->text('name');
            $table->text('path');
            $table->string('ext');
            $table->string('disk')->default('public');
            $table->string('model_id')->nullable()->default(NULL);
            $table->string('model_type')->nullable()->default(NULL);
            $table->string('media_type_in_model')->nullable()->default(NULL);
            $table->unsignedInteger('order')->default(1);
            $table->boolean('is_mocked')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['model_id', 'model_type', 'media_type_in_model']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media');
    }
}
