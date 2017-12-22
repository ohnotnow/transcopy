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
            if (config('transcopy.send_failure_notifications')) {
                Mail::to(config('transcopy.notification_address'))->send(
                    new CopyFailed($this->getJobFilename($event))
                );
            }
        });
        Queue::after(function (JobProcessed $event) {
            if (config('transcopy.send_success_notifications')) {
                Mail::to(config('transcopy.notification_address'))->send(
                    new CopySucceeded($this->getJobFilename($event))
                );
            }
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

    protected function getJobFilename($event)
    {
        $job = $event->job->payload();
        $jobObj = unserialize($job['data']['command']);
        return $jobObj->torrent->getBasename();
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
