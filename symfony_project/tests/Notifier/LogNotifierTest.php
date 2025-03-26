<?php

namespace App\Tests\Notifier;

use App\Notifier\LogNotifier;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LogNotifierTest extends TestCase
{
    public function testNotifyLogsMessage(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $notifier = new LogNotifier($logger);

        $logger->expects($this->once())
            ->method('info')
            ->with('Testowa wiadomość');

        $notifier->notify('Testowa wiadomość');
    }
}
