<?php

namespace App\Models;

class Membership implements Payable {
    private string $id;
    private \DateTime $startDate;
    private \DateTime $endDate;

    public function activate(): void {
        // Implementation
    }

    public function renew(int $period): void {
        // Implementation
    }

    public function cancel(): void {
        // Implementation
    }

    public function isValid(): bool {
        // Implementation
        return true;
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