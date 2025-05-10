<?php

namespace App\Models;

class MembershipPlan {
    private string $id;
    private string $name;
    private string $benefits;
    private float $price;

    public function isActive(): bool {
        // Implementation
        return true;
    }

    public function calculatePrice(int $duration): float {
        // Implementation
        return 0.0;
    }

    public function listBenefits(): array {
        // Implementation
        return [];
    }
} 