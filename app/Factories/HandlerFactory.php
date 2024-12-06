<?php

namespace App\Factories;

use App\Handlers\LineHandler;
use App\Handlers\WhatsAppHandler;
use App\Services\MessageService;
use InvalidArgumentException;

class HandlerFactory
{
    public static function createHandler(string $platform, MessageService $messageService)
    {
        return match ($platform) {
            'Facebook' => '',
            'Line' => new LineHandler($messageService),
            'WhatsApp' => new WhatsAppHandler($messageService),
            'Instagram' => '',
            'Tiktok' => '',
            default => throw new InvalidArgumentException("Unsupported platform: $platform"),
        };
    }
}
