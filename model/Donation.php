<?php

namespace App\Models;

class Donation implements Payable {
    private string $id;
    private float $amount;
    private \DateTime $donatedAt;
    private string $paymentMethod;

    public function processDonation(): Receipt {
        // Implementation
        return new Receipt();
    }

    public function refund(): bool {
        // Implementation
        return true;
    }

    public function getDonorDetails(): Visitor {
        // Implementation
        return new Visitor();
    }

    public function getTotal(): float {
        // Implementation
        return 0.0;
    }

    public function getDescription(): string {
        // Implementation
        return '';
    }
} 