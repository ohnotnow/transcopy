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

        Blade::directive('svg', function ($arguments) {
            list($path, $class) = array_pad(explode(',', trim($arguments, "() ")), 2, '');
            $path = trim($path, "' ");
            $class = trim($class, "' ");

            $svg = new \DOMDocument();
            $svg->load(public_path($path));
            $svg->documentElement->setAttribute("class", $class);
            $output = $svg->saveXML($svg->documentElement);

            return $output;
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
