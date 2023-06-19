<?php

namespace GNAHotelSolutions\Analytics\Tests\TestSupport;

use Orchestra\Testbench\TestCase as Orchestra;
use GNAHotelSolutions\Analytics\AnalyticsServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            AnalyticsServiceProvider::class,
        ];
    }
}
