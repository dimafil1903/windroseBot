<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class FlightTracking extends Model
{
    protected $table = "flight_tracking";
    /**
     * @var string[]
     */
    protected $fillable = [
        "flight_number",
        "date",
        "chat_id",
        "person_id",
        "page",
        "departure_date",
        "arrival_date",
        "departure_date_utc",
        "arrival_date_utc",
        'status',
        'delay_send',
        'carrier',
        'fromJSON',
        'toJSON',
        'delay',
        'expired_at',
        'expired_at_utc',

    ];
}
