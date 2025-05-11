<?php

namespace App\Models;

class Tour extends Model {
    protected static string $table = 'tours';
    
    protected array $fillable = [
        'id',
        'name',
        'description',
        'start_time',
        'end_time',
        'max_participants',
        'current_participants',
        'guide_id',
        'status'
    ];
    
    protected array $relationships = [
        'guide' => TourGuide::class
    ];

    private const VALID_STATUSES = ['SCHEDULED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'];

    public function getName(): string {
        return $this->getAttribute('name');
    }

    public function setName(string $name): void {
        try {
            if (empty(trim($name))) {
                throw new \InvalidArgumentException("Tour name cannot be empty");
            }
            $this->setAttribute('name', $name);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting name for tour {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getDescription(): string {
        return $this->getAttribute('description');
    }

    public function setDescription(string $description): void {
        try {
            if (empty(trim($description))) {
                throw new \InvalidArgumentException("Tour description cannot be empty");
            }
            $this->setAttribute('description', $description);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting description for tour {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getStartTime(): \DateTime {
        return new \DateTime($this->getAttribute('start_time'));
    }

    public function setStartTime(\DateTime $startTime): void {
        try {
            if ($startTime < new \DateTime()) {
                throw new \InvalidArgumentException("Start time cannot be in the past");
            }
            $this->setAttribute('start_time', $startTime->format('Y-m-d H:i:s'));
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting start time for tour {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getEndTime(): \DateTime {
        return new \DateTime($this->getAttribute('end_time'));
    }

    public function setEndTime(\DateTime $endTime): void {
        try {
            if ($endTime <= $this->getStartTime()) {
                throw new \InvalidArgumentException("End time must be after start time");
            }
            $this->setAttribute('end_time', $endTime->format('Y-m-d H:i:s'));
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting end time for tour {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getMaxParticipants(): int {
        return (int)$this->getAttribute('max_participants');
    }

    public function setMaxParticipants(int $maxParticipants): void {
        try {
            if ($maxParticipants <= 0) {
                throw new \InvalidArgumentException("Maximum participants must be greater than 0");
            }
            if ($maxParticipants < $this->getCurrentParticipants()) {
                throw new \InvalidArgumentException("Maximum participants cannot be less than current participants");
            }
            $this->setAttribute('max_participants', $maxParticipants);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting max participants for tour {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getCurrentParticipants(): int {
        return (int)$this->getAttribute('current_participants');
    }

    public function setCurrentParticipants(int $currentParticipants): void {
        try {
            if ($currentParticipants < 0) {
                throw new \InvalidArgumentException("Current participants cannot be negative");
            }
            if ($currentParticipants > $this->getMaxParticipants()) {
                throw new \InvalidArgumentException("Current participants cannot exceed maximum participants");
            }
            $this->setAttribute('current_participants', $currentParticipants);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting current participants for tour {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getGuide(): ?TourGuide {
        try {
            return TourGuide::find($this->getAttribute('guide_id'));
        } catch (\Exception $e) {
            error_log("Error getting guide for tour {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function setGuide(TourGuide $guide): void {
        try {
            $this->setAttribute('guide_id', $guide->getId());
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting guide for tour {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getStatus(): string {
        return $this->getAttribute('status');
    }

    public function setStatus(string $status): void {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid tour status");
            }
            $this->setAttribute('status', $status);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting status for tour {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function isFull(): bool {
        return $this->getCurrentParticipants() >= $this->getMaxParticipants();
    }

    public function hasSpace(): bool {
        return $this->getCurrentParticipants() < $this->getMaxParticipants();
    }

    public function getDuration(): \DateInterval {
        return $this->getStartTime()->diff($this->getEndTime());
    }

    public function isInProgress(): bool {
        $now = new \DateTime();
        return $this->getStatus() === 'IN_PROGRESS' &&
               $now >= $this->getStartTime() &&
               $now <= $this->getEndTime();
    }

    public function isCompleted(): bool {
        return $this->getStatus() === 'COMPLETED';
    }

    public function isCancelled(): bool {
        return $this->getStatus() === 'CANCELLED';
    }

    public static function findByStatus(string $status): array {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid tour status");
            }
            return self::where('status', '=', $status)->get();
        } catch (\Exception $e) {
            error_log("Error finding tours by status {$status}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByGuide(int $guideId): array {
        try {
            return self::where('guide_id', '=', $guideId)->get();
        } catch (\Exception $e) {
            error_log("Error finding tours by guide {$guideId}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findUpcoming(): array {
        try {
            $now = new \DateTime();
            return self::where('start_time', '>', $now->format('Y-m-d H:i:s'))
                ->where('status', '=', 'SCHEDULED')
                ->get();
        } catch (\Exception $e) {
            error_log("Error finding upcoming tours: " . $e->getMessage());
            throw $e;
        }
    }
} 