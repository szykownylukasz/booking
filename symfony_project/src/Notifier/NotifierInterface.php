<?php

namespace App\Notifier;

interface NotifierInterface
{
    public function notify(string $message): void;
}
