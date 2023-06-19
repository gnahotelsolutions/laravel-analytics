<?php

namespace GNAHotelSolutions\Analytics\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \GNAHotelSolutions\Analytics\Analytics
 */
class Analytics extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-analytics';
    }
}
