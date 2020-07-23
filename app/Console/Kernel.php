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
        $schedule->command("track:changeDelay")->everyTenMinutes();
        $schedule->command('track:sendArrived')->everyFiveMinutes();
        $schedule->command('track:inFlight')->everyFiveMinutes();
//         $schedule->command('test:cron')->


        // $schedule->command('test:cron')->everyMinute();


//        $title_text = 'Ежедневная рассылка ' .

//            $a='a';
//        $this->distribution='21:07';
//            $schedule->call(function (PhpTelegramBotContract $telegram_bot) {
//                for ($i = 0; $i < 3; $i++) {
//                    $this->distribution='21:06';
//
//                    Request::sendMessage(['chat_id' => '481629579', 'text' => $i]);
//                }
//            }) // ->everyMinute();
//
//                ->timezone('Europe/Kiev')
//            ->at($this->distribution);
//        date_default_timezone_set("Europe/Kiev");
//        $time= date("H:i");
//        $distributions = TelegramDistribution::where('type_id', '2')->where('time',$time)->where('switch','0')->get();
//        foreach ($distributions as $distribution) {
//           // Request::sendMessage(['chat_id' => '481629579', 'text' => $distribution->chat_id]);
//                 TelegramDistribution::updateOrInsert(
//                ['chat_id' => $distribution->chat_id, 'type_id' => $distribution->type_id],
//                ['switch' => '1']
//            );
//
//            $schedule->call(function (PhpTelegramBotContract $telegram_bot ) {
//
//                date_default_timezone_set("Europe/Kiev");
//                $time= date("H:i");
//                $dists= TelegramDistribution::where('type_id','2')->where('switch','1')->get();
//           //     $chat=Chat::where('id',$dist->chat_id);
//
//                foreach ($dists as $dist){
//                $all_checked_products = CheckedByUser::where('chat_id', $dist->chat_id)->get();
//                $checked_products_id = [];
//                foreach ($all_checked_products as $checked_product) {
//                    array_push($checked_products_id, $checked_product->product_id);
//                }
//                $product = ShopProduct::
//                whereNotIn('id', $checked_products_id)
//                    ->first();
//
//                if ($product) {
//                    $newkeyboard = new NewProductsInlineKeyboard($dist->chat_id);
//                    $newkeyboard->show_product($product->id);
//                    CheckedByUser::updateOrInsert(
//                        ['chat_id' => $dist->chat_id, 'product_id' => $product->id]
//                    );
//
//                }
//                TelegramDistribution::updateOrInsert(
//                    ['id'=>$dist->id],
//                    ['switch' => '0']
//                );
//                }
//
//
//           })//->everyMinute();
//                ->timezone('Europe/Kiev')
//                ->at($distribution->time);
//
//        }
//
//
//       // }
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
