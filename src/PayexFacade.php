<?php

namespace Laraditz\Payex;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Laraditz\Payex\Skeleton\SkeletonClass
 */
class PayexFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'payex';
    }
}
