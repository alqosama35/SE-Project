<?php

namespace App\Models;

class Shopping implements Payable {
    private string $id;
    private string $itemName;
    private float $price;
    private int $quantity;

    public function addToCart(Visitor $visitor): void {
        // Implementation
    }

    public function removeFromCart(Visitor $visitor): void {
        // Implementation
    }

    public function purchase(Visitor $visitor): Receipt {
        // Implementation
        return new Receipt();
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