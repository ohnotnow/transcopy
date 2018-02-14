<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShouldCopyFlagToTorrentEntries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('torrent_entries', function (Blueprint $table) {
            $table->boolean('should_copy')->default(false);
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
            $table->dropColumn('should_copy');
        });
    }
}
