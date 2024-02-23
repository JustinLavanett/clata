<?php

namespace TicketKitten\Helper;

use Carbon\Carbon;

class DateHelper
{
    public static function convertToUTC(string $eventDate, string $userTimezone): string
    {
        return Carbon::parse($eventDate, $userTimezone)
            ->setTimezone('UTC')
            ->toString();
    }
}
