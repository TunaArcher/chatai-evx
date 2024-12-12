<?php

namespace App\Factories;

use App\Handlers\InstagramHandler;
use App\Handlers\FacebookHandler;
use App\Handlers\LineHandler;
use App\Handlers\TiktokHandler;
use App\Handlers\WhatsAppHandler;
use App\Services\MessageService;
use InvalidArgumentException;

class HandlerFactory
{
    public static function createHandler(string $platform, MessageService $messageService)
    {
        return match ($platform) {
            'Facebook' => new FacebookHandler($messageService),
            'Line' => new LineHandler($messageService),
            'WhatsApp' => new WhatsAppHandler($messageService),
            'Instagram' => new InstagramHandler($messageService),
            'Tiktok' => new TiktokHandler($messageService),
            default => throw new InvalidArgumentException("Unsupported platform: $platform"),
        };
    }
}
