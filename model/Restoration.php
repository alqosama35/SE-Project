<?php

namespace App\Models;

class Restoration extends Model {
    protected static string $table = 'restorations';
    
    protected array $fillable = [
        'id',
        'object_id',
        'start_date',
        'end_date',
        'status',
        'description',
        'restorer_id'
    ];
    
    protected array $relationships = [
        'object' => MuseumObject::class,
        'restorer' => Staff::class
    ];

    private const VALID_STATUSES = ['PENDING', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'];

    public function getObject(): ?MuseumObject {
        try {
            return MuseumObject::find($this->getAttribute('object_id'));
        } catch (\Exception $e) {
            error_log("Error getting museum object for restoration {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function setObject(MuseumObject $object): void {
        try {
            $this->setAttribute('object_id', $object->getId());
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting museum object for restoration {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getStartDate(): \DateTime {
        return new \DateTime($this->getAttribute('start_date'));
    }

    public function setStartDate(\DateTime $startDate): void {
        try {
            if ($startDate > new \DateTime()) {
                throw new \InvalidArgumentException("Start date cannot be in the future");
            }
            $this->setAttribute('start_date', $startDate->format('Y-m-d H:i:s'));
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting start date for restoration {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getEndDate(): ?\DateTime {
        $endDate = $this->getAttribute('end_date');
        return $endDate ? new \DateTime($endDate) : null;
    }

    public function setEndDate(?\DateTime $endDate): void {
        try {
            if ($endDate && $endDate < $this->getStartDate()) {
                throw new \InvalidArgumentException("End date cannot be before start date");
            }
            $this->setAttribute('end_date', $endDate ? $endDate->format('Y-m-d H:i:s') : null);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting end date for restoration {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getStatus(): string {
        return $this->getAttribute('status');
    }

    public function setStatus(string $status): void {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid restoration status");
            }
            $this->setAttribute('status', $status);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting status for restoration {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getDescription(): string {
        return $this->getAttribute('description');
    }

    public function setDescription(string $description): void {
        try {
            if (empty(trim($description))) {
                throw new \InvalidArgumentException("Description cannot be empty");
            }
            $this->setAttribute('description', $description);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting description for restoration {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getRestorer(): ?Staff {
        try {
            return Staff::find($this->getAttribute('restorer_id'));
        } catch (\Exception $e) {
            error_log("Error getting restorer for restoration {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function setRestorer(Staff $restorer): void {
        try {
            $this->setAttribute('restorer_id', $restorer->getId());
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting restorer for restoration {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function isCompleted(): bool {
        return $this->getAttribute('status') === 'COMPLETED';
    }

    public function isInProgress(): bool {
        return $this->getAttribute('status') === 'IN_PROGRESS';
    }

    public function getDuration(): ?\DateInterval {
        try {
            $startDate = $this->getStartDate();
            $endDate = $this->getEndDate();
            
            if (!$endDate) {
                return null;
            }
            
            return $startDate->diff($endDate);
        } catch (\Exception $e) {
            error_log("Error calculating duration for restoration {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByStatus(string $status): array {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid restoration status");
            }
            return self::where('status', '=', $status)->get();
        } catch (\Exception $e) {
            error_log("Error finding restorations by status {$status}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByRestorer(int $restorerId): array {
        try {
            return self::where('restorer_id', '=', $restorerId)->get();
        } catch (\Exception $e) {
            error_log("Error finding restorations by restorer {$restorerId}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByObject(int $objectId): array {
        try {
            return self::where('object_id', '=', $objectId)->get();
        } catch (\Exception $e) {
            error_log("Error finding restorations by object {$objectId}: " . $e->getMessage());
            throw $e;
        }
    }
} 