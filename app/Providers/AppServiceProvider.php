<?php

namespace App\Providers;

use DB;
use App\Mail\CopyFailed;
use Transmission\Client;
use App\Mail\CopySucceeded;
use Transmission\Transmission;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Blade;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobProcessed;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->tryEnablingSqliteWal();

        $this->app->bind(Transmission::class, function ($app) {
            $client = new Client();
            if (config('transcopy.username')) {
                $client->authenticate(config('transcopy.username'), config('transcopy.password'));
            }
            $transmission = new Transmission(config('transcopy.host', '127.0.0.1'), config('transcopy.port', 9091));
            $transmission->setClient($client);
            return $transmission;
        });

        Queue::failing(function (JobFailed $event) {
            $this->notifyFailure($event);
        });

        Queue::after(function (JobProcessed $event) {
            $this->notifySuccess($event);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function notifyFailure($event)
    {
        if (! config('transcopy.send_failure_notifications')) {
            return;
        }

        Mail::to(config('transcopy.notification_address'))->send(
            new CopyFailed($this->getJobFilename($event))
        );
    }

    protected function notifySuccess($event)
    {
        if (! config('transcopy.send_success_notifications')) {
            return;
        }

        if ($this->torrentStillDownloading($event)) {
            // as laravel no longer lets us silently 'requeue' a job, we
            // have to dispatch a new copy for torrents which are still
            // downloading - which unfortunatly still fires the job
            // event for the original, so we have to bail out here
            // in the handler. See app/Jobs/CopyFile.php
            return;
        }

        Mail::to(config('transcopy.notification_address'))->send(
            new CopySucceeded($this->getJobFilename($event))
        );
    }

    protected function getJobFilename($event)
    {
        $job = $this->getJobFromEvent($event);
        return $job->torrent->getBasename();
    }

    protected function torrentStillDownloading($event)
    {
        $job = $this->getJobFromEvent($event);
        return $job->torrent->isStillDownloading();
    }

    protected function getJobFromEvent($event)
    {
        $job = $event->job->payload();
        return unserialize($job['data']['command']);
    }

    /**
     * Enable sqlite's WAL journal mode to help concurrency.
     * Disabled while running phpunit as it breaks in-memory db's.
     * See :
     * https://www.sqlite.org/wal.html
     *
     * @return void
     */
    protected function tryEnablingSqliteWal(Type $var = null)
    {
        if (app('env') === 'testing') {
            return;
        }

        if (DB::connection() instanceof \Illuminate\Database\SQLiteConnection) {
            DB::statement(DB::raw('PRAGMA journal_mode = wal;'));
        }
    }
}
