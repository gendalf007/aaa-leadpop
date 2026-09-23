<?php

namespace App\Services\DrivePort;

use RuntimeException;

class DrivePortException extends RuntimeException
{
    /**
     * @param bool $retryable false — повтор не поможет (400 неверное тело, 403 секрет)
     */
    public function __construct(string $message, public readonly bool $retryable = true)
    {
        parent::__construct($message);
    }
}
