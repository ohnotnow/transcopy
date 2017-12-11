<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsCopyingFlagToTorrentEntries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('torrent_entries', function (Blueprint $table) {
            $table->boolean('is_copying')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('torrent_entries', function (Blueprint $table) {
            $table->dropColumn('is_copying');
        });
    }
}
