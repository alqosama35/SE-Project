<?php

namespace App\Models;

class Loan {
    private string $id;
    private Visitor $borrower;
    private MuseumObject $item;
    private \DateTime $loanDate;
    private \DateTime $dueDate;
    private \DateTime $returnDate;
    private string $status;

    public function request(Visitor $borrower, MuseumObject $item): bool {
        // Implementation
        return true;
    }

    public function approve(): void {
        // Implementation
    }

    public function renew(\DateTime $newDueDate): bool {
        // Implementation
        return true;
    }

    public function returnItem(): void {
        // Implementation
    }

    public function isOverdue(): bool {
        // Implementation
        return false;
    }
} 