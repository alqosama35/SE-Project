<?php

namespace App\Models;

class Event {
    private string $id;
    private string $title;
    private string $description;
    private \DateTime $startDate;
    private \DateTime $endDate;
    private string $location;
    private int $capacity;
    private int $registeredParticipants;
    private float $price;
    private string $status;

    public function __construct(array $attributes = []) {
        $this->id = $attributes['id'] ?? uniqid('event_');
        $this->title = $attributes['title'] ?? '';
        $this->description = $attributes['description'] ?? '';
        $this->startDate = $attributes['startDate'] ?? new \DateTime();
        $this->endDate = $attributes['endDate'] ?? (new \DateTime())->modify('+2 hours');
        $this->location = $attributes['location'] ?? '';
        $this->capacity = $attributes['capacity'] ?? 50;
        $this->registeredParticipants = $attributes['registeredParticipants'] ?? 0;
        $this->price = $attributes['price'] ?? 0.0;
        $this->status = $attributes['status'] ?? 'SCHEDULED';

        // Validate initial values
        $this->validateCapacity($this->capacity);
        $this->validateDates($this->startDate, $this->endDate);
        $this->validatePrice($this->price);
    }

    private function validateCapacity(int $capacity): void {
        if ($capacity < 1) {
            throw new \InvalidArgumentException("Capacity must be at least 1");
        }
    }

    private function validateDates(\DateTime $start, \DateTime $end): void {
        if ($start >= $end) {
            throw new \InvalidArgumentException("Start date must be before end date");
        }
    }

    private function validatePrice(float $price): void {
        if ($price < 0) {
            throw new \InvalidArgumentException("Price cannot be negative");
        }
    }

    public function checkAvailability(): bool {
        try {
            $now = new \DateTime();
            return $this->status === 'SCHEDULED' && 
                   $this->registeredParticipants < $this->capacity &&
                   $now < $this->startDate;
        } catch (\Exception $e) {
            error_log("Error checking availability for event {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function registerParticipant(): void {
        try {
            if (!$this->checkAvailability()) {
                throw new \RuntimeException("Event is not available for registration");
            }
            $this->registeredParticipants++;
            if ($this->registeredParticipants >= $this->capacity) {
                $this->status = 'FULL';
            }
        } catch (\Exception $e) {
            error_log("Error registering participant for event {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function cancelParticipant(): void {
        try {
            if ($this->registeredParticipants <= 0) {
                throw new \RuntimeException("No participants to cancel");
            }
            $this->registeredParticipants--;
            if ($this->status === 'FULL') {
                $this->status = 'SCHEDULED';
            }
        } catch (\Exception $e) {
            error_log("Error cancelling participant for event {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPrice(): float {
        return $this->price;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getStartDate(): \DateTime {
        return $this->startDate;
    }

    public function getEndDate(): \DateTime {
        return $this->endDate;
    }

    public function getLocation(): string {
        return $this->location;
    }

    public function getCapacity(): int {
        return $this->capacity;
    }

    public function getRegisteredParticipants(): int {
        return $this->registeredParticipants;
    }

    public function getStatus(): string {
        return $this->status;
    }
} 