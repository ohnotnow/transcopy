<?php

namespace App\Providers;

use DB;
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
        //DB::statement(DB::raw('PRAGMA journal_mode=WAL'));

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
            //$filename = $jobObj->file->getBasename();
            $filename = 'fred';
//            dd($jobObj);
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
