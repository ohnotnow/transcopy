<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\TorrentEntry;

class QueuePendingTorrentCopies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transcopy:queuepending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add newly downloaded torrents to the copy queue';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        TorrentEntry::shouldBeQueued()->get()->each->queueCopy();
    }
}
