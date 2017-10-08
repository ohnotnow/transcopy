<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTorrentEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('torrent_entries', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('torrent_id');
            $table->string('name');
            $table->bigInteger('size');
            $table->float('percent');
            $table->string('path');
            $table->integer('eta');
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
        Schema::dropIfExists('torrent_entries');
    }
}
