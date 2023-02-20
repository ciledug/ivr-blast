<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCallLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('call_logs', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->collation = 'utf8mb4_unicode_ci';
            
            $table->increments('id');
            $table->integer('contact_id')->unsigned();
            $table->datetime('call_dial')->default(null)->nullable();
            $table->datetime('call_connect')->default(null)->nullable();
            $table->datetime('call_disconnect')->default(null)->nullable();
            $table->datetime('call_duration')->default(null)->nullable();
            $table->string('call_response', 10)->default(null)->nullable()->comment('answered, no_answer, busy, failed');
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
        Schema::dropIfExists('call_logs');
    }
}
