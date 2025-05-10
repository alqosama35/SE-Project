<?php

namespace App\Models;

class Receipt {
    private string $id;
    private \DateTime $issuedAt;
    private float $amount;
    private string $paymentMethod;

    public function generateReceipt(): string {
        // Implementation
        return '';
    }

    public function sendEmail(): void {
        // Implementation
    }
} 