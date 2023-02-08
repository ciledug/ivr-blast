<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_logs', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->dateTime('last_login')->default(null)->nullable();
            $table->string('last_ip_address')->default(null)->nullable();
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
        Schema::dropIfExists('user_logs');
    }
}
