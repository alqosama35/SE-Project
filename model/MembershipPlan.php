<?php

namespace App\Models;

class MembershipPlan {
    private string $id;
    private string $name;
    private string $benefits;
    private float $price;
    private int $duration; // in months
    private bool $active;
    private array $features;

    public function __construct(array $attributes = []) {
        try {
            $this->id = $attributes['id'] ?? uniqid('plan_');
            $this->name = $attributes['name'] ?? '';
            $this->benefits = $attributes['benefits'] ?? '';
            $this->price = $attributes['price'] ?? 0.0;
            $this->duration = $attributes['duration'] ?? 12;
            $this->active = $attributes['active'] ?? true;
            $this->features = $attributes['features'] ?? [];

            if (empty(trim($this->name))) {
                throw new \InvalidArgumentException("Plan name cannot be empty");
            }
            if ($this->price < 0) {
                throw new \InvalidArgumentException("Plan price cannot be negative");
            }
            if ($this->duration <= 0) {
                throw new \InvalidArgumentException("Plan duration must be positive");
            }
        } catch (\Exception $e) {
            error_log("Error creating membership plan: " . $e->getMessage());
            throw $e;
        }
    }

    public function isActive(): bool {
        try {
            return $this->active;
        } catch (\Exception $e) {
            error_log("Error checking plan active status {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function calculatePrice(int $duration): float {
        try {
            if ($duration <= 0) {
                throw new \InvalidArgumentException("Duration must be positive");
            }
            
            // Calculate price based on duration and apply any discounts
            $basePrice = $this->price * ($duration / $this->duration);
            
            // Apply discounts for longer durations
            if ($duration >= 24) {
                return $basePrice * 0.8; // 20% discount for 2+ years
            } elseif ($duration >= 12) {
                return $basePrice * 0.9; // 10% discount for 1+ year
            }
            
            return $basePrice;
        } catch (\Exception $e) {
            error_log("Error calculating plan price {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function listBenefits(): array {
        try {
            return array_merge(
                [$this->benefits],
                $this->features
            );
        } catch (\Exception $e) {
            error_log("Error listing plan benefits {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function addFeature(string $feature): void {
        try {
            if (empty(trim($feature))) {
                throw new \InvalidArgumentException("Feature cannot be empty");
            }
            if (!in_array($feature, $this->features)) {
                $this->features[] = $feature;
            }
        } catch (\Exception $e) {
            error_log("Error adding feature to plan {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function removeFeature(string $feature): void {
        try {
            if (empty(trim($feature))) {
                throw new \InvalidArgumentException("Feature cannot be empty");
            }
            $this->features = array_filter($this->features, fn($f) => $f !== $feature);
        } catch (\Exception $e) {
            error_log("Error removing feature from plan {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getId(): string {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getPrice(): float {
        return $this->price;
    }

    public function getDuration(): int {
        return $this->duration;
    }

    public function getFeatures(): array {
        return $this->features;
    }
} 