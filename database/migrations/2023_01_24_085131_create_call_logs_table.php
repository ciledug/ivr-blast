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
            // $table->engine = 'MyISAM';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            
            $table->increments('id');
            $table->integer('campaign_id')->unsigned();
            $table->integer('contact_id')->unsigned();
            $table->datetime('call_dial')->nullable();
            $table->datetime('call_connect')->nullable();
            $table->datetime('call_disconnect')->nullable();
            $table->integer('call_duration')->nullable();
            $table->string('call_recording', 255)->nullable();
            $table->string('call_response', 15)->index()->nullable()->comment('answered, no_answer, busy, failed');
            $table->timestamps();
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
