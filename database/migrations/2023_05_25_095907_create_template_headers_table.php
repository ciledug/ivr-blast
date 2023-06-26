<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplateHeadersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_headers', function (Blueprint $table) {
            // $table->engine = 'MyISAM';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            
            $table->increments('id');
            $table->integer('template_id')->unsigned()->nullable();
            $table->string('name', 30);
            $table->string('column_type', 10)->comment('string, datetime, date, time, numeric, handphone');
            $table->boolean('is_mandatory')->nullable();
            $table->boolean('is_unique')->nullable();
            $table->boolean('is_voice')->nullable();
            $table->tinyInteger('voice_position')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('template_headers');
    }
}
