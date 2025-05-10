<?php

namespace App\Models;

class Notification {
    private string $id;
    private string $email;
    private \DateTime $subscribedAt;

    public function subscribe(): void {
        // Implementation
    }

    public function unsubscribe(): void {
        // Implementation
    }

    public function sendConfirmationEmail(): void {
        // Implementation
    }
} 