<?php

namespace zero\facade;

use zero\Facade;

class Log extends Facade
{
    protected static function getFacadeClass()
    {
        return 'zero\Log';
    }
}