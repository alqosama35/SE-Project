<?php

namespace App\Models;

class DonationProgram {
    private string $id;
    private string $name;
    private string $description;
    private float $goalAmount;
    private bool $active;

    public function startProgram(): void {
        // Implementation
    }

    public function endProgram(): void {
        // Implementation
    }

    public function updateGoal(float $amount): void {
        // Implementation
    }

    public function isActive(): bool {
        // Implementation
        return true;
    }
} 