<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
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
        Queue::failing(function (JobFailed $event) {
            if (config('transcopy.send_failure_notifications')) {
                Mail::to(config('transcopy.notification_address'))->send(new CopyFailed($event->job));
            }
        });
        Queue::after(function (JobProcessed $event) {
            if (config('transcopy.send_success_notifications')) {
                Mail::to(config('transcopy.notification_address'))->send(new CopySucceeded($event->job));
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
