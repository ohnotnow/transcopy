<?php

namespace App\Providers;

use DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Transmission\Client;
use Transmission\Transmission;
use App\Mail\CopyFailed;
use App\Mail\CopySucceeded;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
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
            $job = $event->job->payload();
            $jobObj = unserialize($job['data']['command']);
            $filename = $jobObj->file->getBasename();
            if (config('transcopy.send_failure_notifications')) {
                Mail::to(config('transcopy.notification_address'))->send(new CopyFailed($filename));
            }
        });
        Queue::after(function (JobProcessed $event) {
            $job = $event->job->payload();
            $jobObj = unserialize($job['data']['command']);
            $filename = $jobObj->file->getBasename();
            if (config('transcopy.send_success_notifications')) {
                Mail::to(config('transcopy.notification_address'))->send(new CopySucceeded($filename));
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
}
