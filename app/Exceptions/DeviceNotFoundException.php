<?php

namespace App\Exceptions;

use Exception;

class DeviceNotFoundException extends Exception
{
    protected $message = 'Device not found';
    protected $code = 404;
}