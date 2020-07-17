<?php


namespace App\Telegram\Helpers;


class ConvertDate
{

    static function ConvertToWordMonth($date,$lang)
    {
        if ($lang=='uk'){
            $lang="ua";
        }
        $months = [
            "ua" => ["Січня", "Лютого", "Березня", "Квітня", "Травня", "Червня", "Липня", "Серпня", "Вересня", "Листопада", "Груденя"],
            "en"=> ["January", "February", "March", "April", "May", "June", "July", "August", "September", "November", "December"]
        ];
        $day=date("d",strtotime($date));
        $month=$months["$lang"][date("n",strtotime($date))-1];
        $year=date("Y",strtotime($date));

        return "$day $month $year";
    }
}
