<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->collation = 'utf8mb4_unicode_ci';
            
            $table->increments('id');
            $table->string('unique_key', 20)->unique();
            $table->string('name', 50);
            //$table->string('excel_name', 100)->default(null)->nullable();
            $table->integer('total_data')->unsigned()->default(0);
            //$table->smallInteger('total_calls')->unsigned()->default(0)->nullable();
            $table->integer('status')->default(0)->comment('0=ready, 1=running, 2=paused, 3=finished');
            $table->integer('created_by');
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
        Schema::dropIfExists('campaigns');
    }
}
