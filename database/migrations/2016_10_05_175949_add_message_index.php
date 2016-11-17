<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMessageIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
                Schema::table('messages', function (Blueprint $table){
                    $table->index(['sender','recipient']);
                });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
                Schema::table('messages', function (Blueprint $table){
                    $table->dropIndex(['sender','recipient']);
                });
    }
}
