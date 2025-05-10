<?php

namespace App\Models;

class Contact {
    private string $id;
    private Visitor $visitor;
    private string $name;
    private string $email;
    private string $subject;
    private string $message;
    private \DateTime $sentAt;
    private string $status;

    public function send(): void {
        // Implementation
    }

    public function respond(string $response): void {
        // Implementation
    }

    public function close(): void {
        // Implementation
    }
} 