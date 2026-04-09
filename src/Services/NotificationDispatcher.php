<?php

namespace ClarionApp\Backend\Services;

class NotificationDispatcher
{
    /** @var array<callable(string $userId, string $category, string $title, string $message): void> */
    private array $handlers = [];

    public function registerHandler(callable $handler): void
    {
        $this->handlers[] = $handler;
    }

    public function dispatch(string $userId, string $category, string $title, string $message): void
    {
        foreach ($this->handlers as $handler) {
            try {
                $handler($userId, $category, $title, $message);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning(
                    "Notification handler failed for category '{$category}': " . $e->getMessage()
                );
            }
        }
    }
}
