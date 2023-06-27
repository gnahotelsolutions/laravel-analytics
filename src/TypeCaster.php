<?php

namespace GNAHotelSolutions\Analytics;

use Illuminate\Support\Carbon;

class TypeCaster
{
    public function castValue(string $key, string $value)
    {
        switch ($key) {
            case 'date':
                return Carbon::createFromFormat('Ymd', $value);
            case 'visitors':
            case 'pageViews':
            case 'activeUsers':
            case 'newUsers':
            case 'screenPageViews':
            case 'active1DayUsers':
            case 'active7DayUsers':
            case 'active28DayUsers':
                return (int) $value;
            default:
                return $value;
        }
    }
}
