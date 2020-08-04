<?php

namespace App\Console;

use App\Chat;
use App\CheckedByUser;
use App\Console\Commands\MakeUser;
use App\Console\Commands\SendAndDeleteArrived;
use App\Console\Commands\SendInFlight;
use App\Console\Commands\TrackingFlights;
use App\DistributionType;

use App\TelegramDistribution;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Longman\TelegramBot\Request;
use PhpTelegramBot\Laravel\PhpTelegramBotContract;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\TestCron::class,
        MakeUser::class,
        TrackingFlights::class,
        SendAndDeleteArrived::class,
        SendInFlight::class,
        Commands\Viber\getLastMessageOnline::class,
    ];
    public $distribution;


    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule)
    {
        $schedule->command('get:data')->dailyAt("01:00");
        $schedule->command('get:firstTen')->everyFiveMinutes();
        $schedule->command("track:changeDelay")->everyTenMinutes();
        $schedule->command('track:sendArrived')->everyFiveMinutes();
        $schedule->command('track:Flight')->everyMinute();
        $schedule->command("viber:getOnline")->everyFiveMinutes();
    }


    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
