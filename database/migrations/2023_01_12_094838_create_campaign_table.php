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
            // $table->engine = 'MyISAM';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            
            $table->increments('id');
            $table->string('unique_key', 20)->unique();
            $table->string('name', 50);
            $table->integer('total_data')->unsigned()->default(0)->nullable();
            $table->tinyinteger('status')->unsigned()->default(0)->nullable()->comment('0=ready, 1=running, 2=paused, 3=finished');
            $table->tinyInteger('created_by')->unsigned();
            $table->tinyInteger('template_id')->unsigned();
            $table->string('reference_table', 100);
            $table->text('text_voice')->nullable();
            $table->string('voice_gender')->nullable()->comment('male_normal, male_strong, female_normal, female_strong');
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
