<?php

namespace App\Notifier;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class LogNotifier implements NotifierInterface
{
    private LoggerInterface $logger;

    public function __construct(
        #[Autowire(service: 'monolog.logger.product_notifications')]
        LoggerInterface $logger,
    ) {
        $this->logger = $logger;
    }

    public function notify(string $message): void
    {
        $this->logger->info($message);
    }
}
