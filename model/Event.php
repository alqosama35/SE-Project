<?php

namespace App\Models;

class Event {
    private string $id;
    private string $title;
    private \DateTime $dateTime;
    private string $location;
    private int $capacity;

    public function checkAvailability(): bool {
        // Implementation
        return true;
    }
} 