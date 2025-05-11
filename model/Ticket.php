<?php

namespace App\Models;

class Ticket extends Model {
    protected static string $table = 'tickets';
    
    protected array $fillable = [
        'id',
        'visitor_id',
        'visit_id',
        'price',
        'purchase_date',
        'status',
        'type'
    ];
    
    protected array $relationships = [
        'visitor' => Visitor::class,
        'visit' => Visit::class
    ];

    private const VALID_STATUSES = ['PURCHASED', 'USED', 'CANCELLED', 'REFUNDED'];
    private const VALID_TYPES = ['REGULAR', 'STUDENT', 'SENIOR', 'CHILD', 'GROUP'];

    public function getVisitor(): ?Visitor {
        try {
            return Visitor::find($this->getAttribute('visitor_id'));
        } catch (\Exception $e) {
            error_log("Error getting visitor for ticket {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function setVisitor(Visitor $visitor): void {
        try {
            $this->setAttribute('visitor_id', $visitor->getId());
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting visitor for ticket {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getVisit(): ?Visit {
        try {
            return Visit::find($this->getAttribute('visit_id'));
        } catch (\Exception $e) {
            error_log("Error getting visit for ticket {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function setVisit(Visit $visit): void {
        try {
            $this->setAttribute('visit_id', $visit->getId());
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting visit for ticket {$this->getAttribute('id')}: " . $e->getMessage());
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
            error_log("Error setting price for ticket {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPurchaseDate(): \DateTime {
        return new \DateTime($this->getAttribute('purchase_date'));
    }

    public function setPurchaseDate(\DateTime $purchaseDate): void {
        try {
            if ($purchaseDate > new \DateTime()) {
                throw new \InvalidArgumentException("Purchase date cannot be in the future");
            }
            $this->setAttribute('purchase_date', $purchaseDate->format('Y-m-d H:i:s'));
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting purchase date for ticket {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getStatus(): string {
        return $this->getAttribute('status');
    }

    public function setStatus(string $status): void {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid ticket status");
            }
            $this->setAttribute('status', $status);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting status for ticket {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getType(): string {
        return $this->getAttribute('type');
    }

    public function setType(string $type): void {
        try {
            if (!in_array($type, self::VALID_TYPES)) {
                throw new \InvalidArgumentException("Invalid ticket type");
            }
            $this->setAttribute('type', $type);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting type for ticket {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function isUsed(): bool {
        return $this->getAttribute('status') === 'USED';
    }

    public function isCancelled(): bool {
        return $this->getAttribute('status') === 'CANCELLED';
    }

    public function isRefunded(): bool {
        return $this->getAttribute('status') === 'REFUNDED';
    }

    public function isValid(): bool {
        return !$this->isUsed() && !$this->isCancelled() && !$this->isRefunded();
    }

    public static function findByStatus(string $status): array {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid ticket status");
            }
            return self::where('status', '=', $status)->get();
        } catch (\Exception $e) {
            error_log("Error finding tickets by status {$status}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByType(string $type): array {
        try {
            if (!in_array($type, self::VALID_TYPES)) {
                throw new \InvalidArgumentException("Invalid ticket type");
            }
            return self::where('type', '=', $type)->get();
        } catch (\Exception $e) {
            error_log("Error finding tickets by type {$type}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByVisitor(int $visitorId): array {
        try {
            return self::where('visitor_id', '=', $visitorId)->get();
        } catch (\Exception $e) {
            error_log("Error finding tickets by visitor {$visitorId}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByVisit(int $visitId): array {
        try {
            return self::where('visit_id', '=', $visitId)->get();
        } catch (\Exception $e) {
            error_log("Error finding tickets by visit {$visitId}: " . $e->getMessage());
            throw $e;
        }
    }
} 