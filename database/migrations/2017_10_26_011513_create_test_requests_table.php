<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("test_requests", function (Blueprint $table) {
            $table->increments("id");
            $table->string("clientIP");
            $table->string("type");
            $table->longText("result");
            $table->integer("done");
            $table->string("destination");
            $table->string("key");
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
        Schema::dropIfExists("test_requests");
    }
}
