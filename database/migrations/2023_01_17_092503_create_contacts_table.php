<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->integer('campaign_id')->unsigned();
            $table->string('account_id', 50);
            $table->string('name', 50);
            $table->string('phone', 15);
            $table->date('bill_date')->default(null)->nullable();
            $table->date('due_date')->default(null)->nullable();
            $table->integer('nominal')->unsigned();
            $table->datetime('call_dial')->default(null)->nullable();
            $table->datetime('call_connect')->default(null)->nullable();
            $table->datetime('call_disconnect')->default(null)->nullable();
            $table->datetime('call_duration')->default(null)->nullable();
            $table->tinyInteger('call_response')->unsigned()->default(null)->nullable()->comment('0:answered, 1:no_answer, 2:busy, 3:failed');
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
        Schema::dropIfExists('contacts');
    }
}
