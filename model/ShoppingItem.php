<?php

namespace App\Models;

class ShoppingItem extends Model {
    protected static string $table = 'shopping_items';
    
    protected array $fillable = [
        'id',
        'name',
        'description',
        'price',
        'quantity',
        'visitor_id',
        'status'
    ];
    
    protected array $relationships = [
        'visitor' => Visitor::class
    ];

    private const VALID_STATUSES = ['IN_CART', 'PURCHASED', 'REMOVED'];

    public function getName(): string {
        return $this->getAttribute('name');
    }

    public function setName(string $name): void {
        try {
            if (empty(trim($name))) {
                throw new \InvalidArgumentException("Item name cannot be empty");
            }
            $this->setAttribute('name', $name);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting name for shopping item {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getDescription(): string {
        return $this->getAttribute('description');
    }

    public function setDescription(string $description): void {
        try {
            if (empty(trim($description))) {
                throw new \InvalidArgumentException("Item description cannot be empty");
            }
            $this->setAttribute('description', $description);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting description for shopping item {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPrice(): float {
        return (float)$this->getAttribute('price');
    }

    public function setPrice(float $price): void {
        try {
            if ($price < 0) {
                throw new \InvalidArgumentException("Price cannot be negative");
            }
            $this->setAttribute('price', $price);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting price for shopping item {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getQuantity(): int {
        return (int)$this->getAttribute('quantity');
    }

    public function setQuantity(int $quantity): void {
        try {
            if ($quantity <= 0) {
                throw new \InvalidArgumentException("Quantity must be greater than 0");
            }
            $this->setAttribute('quantity', $quantity);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting quantity for shopping item {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getVisitor(): ?Visitor {
        try {
            return Visitor::find($this->getAttribute('visitor_id'));
        } catch (\Exception $e) {
            error_log("Error getting visitor for shopping item {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function setVisitor(Visitor $visitor): void {
        try {
            $this->setAttribute('visitor_id', $visitor->getId());
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting visitor for shopping item {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getStatus(): string {
        return $this->getAttribute('status');
    }

    public function setStatus(string $status): void {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid shopping item status");
            }
            $this->setAttribute('status', $status);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting status for shopping item {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function isInCart(): bool {
        return $this->getAttribute('status') === 'IN_CART';
    }

    public function isPurchased(): bool {
        return $this->getAttribute('status') === 'PURCHASED';
    }

    public function getTotalPrice(): float {
        return $this->getPrice() * $this->getQuantity();
    }

    public static function findByVisitor(int $visitorId): array {
        try {
            return self::where('visitor_id', '=', $visitorId)->get();
        } catch (\Exception $e) {
            error_log("Error finding shopping items by visitor {$visitorId}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByStatus(string $status): array {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid shopping item status");
            }
            return self::where('status', '=', $status)->get();
        } catch (\Exception $e) {
            error_log("Error finding shopping items by status {$status}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findInCart(int $visitorId): array {
        try {
            return self::where('visitor_id', '=', $visitorId)
                ->where('status', '=', 'IN_CART')
                ->get();
        } catch (\Exception $e) {
            error_log("Error finding shopping items in cart for visitor {$visitorId}: " . $e->getMessage());
            throw $e;
        }
    }
} 