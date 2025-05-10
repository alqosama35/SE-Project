<?php

namespace App\Models;

class Booking implements Payable {
    private string $id;
    private \DateTime $bookingDate;
    private string $status;
    private int $participants;

    public function confirm(): void {
        // Implementation
    }

    public function cancel(): void {
        // Implementation
    }

    public function modifyParticipants(int $count): void {
        // Implementation
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