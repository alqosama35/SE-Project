<?php

namespace App\Models;

class Loan extends Model {
    protected static string $table = 'loans';
    
    protected array $fillable = [
        'id',
        'museum_object_id',
        'borrowing_institution',
        'start_date',
        'end_date',
        'status',
        'notes'
    ];
    
    protected array $relationships = [
        'museumObject' => MuseumObject::class
    ];

    private const VALID_STATUSES = ['PENDING', 'ACTIVE', 'COMPLETED', 'CANCELLED'];

    public function getMuseumObject(): ?MuseumObject {
        try {
            return MuseumObject::find($this->getAttribute('museum_object_id'));
        } catch (\Exception $e) {
            error_log("Error getting museum object for loan {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function setMuseumObject(MuseumObject $museumObject): void {
        try {
            if (!$museumObject->getId()) {
                throw new \InvalidArgumentException("Museum object must have an ID");
            }
            $this->setAttribute('museum_object_id', $museumObject->getId());
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting museum object for loan {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getBorrowingInstitution(): string {
        return $this->getAttribute('borrowing_institution');
    }

    public function setBorrowingInstitution(string $institution): void {
        try {
            if (empty(trim($institution))) {
                throw new \InvalidArgumentException("Borrowing institution cannot be empty");
            }
            $this->setAttribute('borrowing_institution', $institution);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting borrowing institution for loan {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getStartDate(): string {
        return $this->getAttribute('start_date');
    }

    public function setStartDate(string $startDate): void {
        try {
            $date = new \DateTime($startDate);
            if ($date < new \DateTime()) {
                throw new \InvalidArgumentException("Start date cannot be in the past");
            }
            $this->setAttribute('start_date', $startDate);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting start date for loan {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getEndDate(): ?string {
        return $this->getAttribute('end_date');
    }

    public function setEndDate(?string $endDate): void {
        try {
            if ($endDate !== null) {
                $end = new \DateTime($endDate);
                $start = new \DateTime($this->getStartDate());
                if ($end <= $start) {
                    throw new \InvalidArgumentException("End date must be after start date");
                }
            }
            $this->setAttribute('end_date', $endDate);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting end date for loan {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getStatus(): string {
        return $this->getAttribute('status');
    }

    public function setStatus(string $status): void {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid loan status");
            }
            $this->setAttribute('status', $status);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting status for loan {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getNotes(): ?string {
        return $this->getAttribute('notes');
    }

    public function setNotes(?string $notes): void {
        try {
            $this->setAttribute('notes', $notes);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting notes for loan {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function isActive(): bool {
        try {
            return $this->getAttribute('status') === 'ACTIVE';
        } catch (\Exception $e) {
            error_log("Error checking loan active status {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function isPending(): bool {
        try {
            return $this->getAttribute('status') === 'PENDING';
        } catch (\Exception $e) {
            error_log("Error checking loan pending status {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function isCompleted(): bool {
        try {
            return $this->getAttribute('status') === 'COMPLETED';
        } catch (\Exception $e) {
            error_log("Error checking loan completed status {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function isCancelled(): bool {
        try {
            return $this->getAttribute('status') === 'CANCELLED';
        } catch (\Exception $e) {
            error_log("Error checking loan cancelled status {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByStatus(string $status): array {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid loan status");
            }
            return self::where('status', '=', $status)->get();
        } catch (\Exception $e) {
            error_log("Error finding loans by status {$status}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByInstitution(string $institution): array {
        try {
            if (empty(trim($institution))) {
                throw new \InvalidArgumentException("Institution cannot be empty");
            }
            return self::where('borrowing_institution', '=', $institution)->get();
        } catch (\Exception $e) {
            error_log("Error finding loans by institution {$institution}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findActive(): array {
        try {
            return self::where('status', '=', 'ACTIVE')->get();
        } catch (\Exception $e) {
            error_log("Error finding active loans: " . $e->getMessage());
            throw $e;
        }
    }
} 