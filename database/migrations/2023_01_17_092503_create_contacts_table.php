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
            $table->string('name', 50)->nullable();
            $table->string('phone', 15);
            $table->date('bill_date')->nullable();
            $table->date('due_date')->nullable();
            $table->integer('nominal')->unsigned();
			$table->integer('extension')->nullable();
			$table->string('callerid')->nullable();
            $table->datetime('call_dial')->nullable();
            $table->string('call_response', 15)->index()->nullable()->comment('answered, no_answer, busy, failed');
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
