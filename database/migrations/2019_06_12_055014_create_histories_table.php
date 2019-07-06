<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('cdnType');
            $table->string('accountName');
            $table->string('estimatedSeconds');
            $table->string('progressUri');
            $table->string('purgeId');
            $table->string('supportId');
            $table->string('httpStatus');
            $table->string('detail');
            $table->integer('pingAfterSeconds');
            $table->integer('done');
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
        Schema::dropIfExists('histories');
    }
}
