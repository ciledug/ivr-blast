<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('templates', function (Blueprint $table) {
            // $table->engine = 'MyISAM';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->string('name', 50);
            $table->string('reference_table', 40)->unique();
            $table->text('voice_text')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('t_defaul_0000001', function (Blueprint $table) {
            // $table->engine = 'MyISAM';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->integer('campaign_id')->unsigned();
            $table->integer('contact_id')->unsigned();
            $table->string('account_id', 10)->unique();
            $table->string('name', 30);
            $table->string('phone', 15)->unique();
            $table->date('bill_date');
            $table->date('due_date');
            $table->integer('nominal')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('t_demo_0000002', function (Blueprint $table) {
            // $table->engine = 'MyISAM';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->integer('campaign_id')->unsigned();
            $table->integer('contact_id')->unsigned();
            $table->string('product_name', 100);
            $table->string('agreement_no', 100);
            $table->date('due_date');
            $table->integer('nominal')->unsigned();
            $table->string('phone', 15)->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('t_demo_0000003', function (Blueprint $table) {
            // $table->engine = 'MyISAM';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->integer('campaign_id')->unsigned();
            $table->integer('contact_id')->unsigned();
            $table->string('phone', 15)->unique();
            $table->string('product_name', 100);
            $table->string('agreement_no', 100)->unique();
            $table->integer('nominal')->unsigned();
            $table->date('due_date');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('t_demo_0000004', function (Blueprint $table) {
            // $table->engine = 'MyISAM';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->integer('campaign_id')->unsigned();
            $table->integer('contact_id')->unsigned();
            $table->string('phone', 15)->unique();
            $table->string('product_name', 100);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('t_demo_0000005', function (Blueprint $table) {
            // $table->engine = 'MyISAM';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->integer('campaign_id')->unsigned();
            $table->integer('contact_id')->unsigned();
            $table->string('phone', 15)->unique();
            $table->string('product_name', 100);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('t_demo_0000006', function (Blueprint $table) {
            // $table->engine = 'MyISAM';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id');
            $table->integer('campaign_id')->unsigned();
            $table->integer('contact_id')->unsigned();
            $table->string('phone', 15)->unique();
            $table->string('product_name', 100);
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
        Schema::dropIfExists('templates');
        Schema::dropIfExists('t_defaul_0000001');

        for ($i=2; $i<=6; $i++) {
            Schema::dropIfExists('t_demo_000000' . $i);
        }
    }
}
