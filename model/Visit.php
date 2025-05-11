<?php

namespace App\Models;

class Visit extends Model {
    protected static string $table = 'visits';
    
    protected array $fillable = [
        'id',
        'visitor_id',
        'ticket_id',
        'check_in_time',
        'check_out_time',
        'status',
        'purpose'
    ];
    
    protected array $relationships = [
        'visitor' => Visitor::class,
        'ticket' => Ticket::class
    ];

    private const VALID_STATUSES = ['SCHEDULED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'];
    private const VALID_PURPOSES = ['GENERAL', 'RESEARCH', 'EDUCATION', 'GROUP_TOUR', 'EVENT'];

    public function getVisitor(): ?Visitor {
        try {
            return Visitor::find($this->getAttribute('visitor_id'));
        } catch (\Exception $e) {
            error_log("Error getting visitor for visit {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function setVisitor(Visitor $visitor): void {
        try {
            $this->setAttribute('visitor_id', $visitor->getId());
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting visitor for visit {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getTicket(): ?Ticket {
        try {
            return Ticket::find($this->getAttribute('ticket_id'));
        } catch (\Exception $e) {
            error_log("Error getting ticket for visit {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function setTicket(Ticket $ticket): void {
        try {
            $this->setAttribute('ticket_id', $ticket->getId());
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting ticket for visit {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getCheckInTime(): ?\DateTime {
        $checkInTime = $this->getAttribute('check_in_time');
        return $checkInTime ? new \DateTime($checkInTime) : null;
    }

    public function setCheckInTime(?\DateTime $checkInTime): void {
        try {
            if ($checkInTime && $checkInTime > new \DateTime()) {
                throw new \InvalidArgumentException("Check-in time cannot be in the future");
            }
            $this->setAttribute('check_in_time', $checkInTime ? $checkInTime->format('Y-m-d H:i:s') : null);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting check-in time for visit {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getCheckOutTime(): ?\DateTime {
        $checkOutTime = $this->getAttribute('check_out_time');
        return $checkOutTime ? new \DateTime($checkOutTime) : null;
    }

    public function setCheckOutTime(?\DateTime $checkOutTime): void {
        try {
            if ($checkOutTime) {
                if ($checkOutTime > new \DateTime()) {
                    throw new \InvalidArgumentException("Check-out time cannot be in the future");
                }
                $checkInTime = $this->getCheckInTime();
                if ($checkInTime && $checkOutTime <= $checkInTime) {
                    throw new \InvalidArgumentException("Check-out time must be after check-in time");
                }
            }
            $this->setAttribute('check_out_time', $checkOutTime ? $checkOutTime->format('Y-m-d H:i:s') : null);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting check-out time for visit {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getStatus(): string {
        return $this->getAttribute('status');
    }

    public function setStatus(string $status): void {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid visit status");
            }
            $this->setAttribute('status', $status);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting status for visit {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPurpose(): string {
        return $this->getAttribute('purpose');
    }

    public function setPurpose(string $purpose): void {
        try {
            if (!in_array($purpose, self::VALID_PURPOSES)) {
                throw new \InvalidArgumentException("Invalid visit purpose");
            }
            $this->setAttribute('purpose', $purpose);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting purpose for visit {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function isInProgress(): bool {
        return $this->getStatus() === 'IN_PROGRESS';
    }

    public function isCompleted(): bool {
        return $this->getStatus() === 'COMPLETED';
    }

    public function isCancelled(): bool {
        return $this->getStatus() === 'CANCELLED';
    }

    public function getDuration(): ?\DateInterval {
        try {
            $checkInTime = $this->getCheckInTime();
            $checkOutTime = $this->getCheckOutTime();
            
            if (!$checkInTime || !$checkOutTime) {
                return null;
            }
            
            return $checkInTime->diff($checkOutTime);
        } catch (\Exception $e) {
            error_log("Error calculating duration for visit {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function checkIn(): void {
        try {
            if ($this->getCheckInTime()) {
                throw new \InvalidArgumentException("Visit already checked in");
            }
            $this->setCheckInTime(new \DateTime());
            $this->setStatus('IN_PROGRESS');
        } catch (\Exception $e) {
            error_log("Error checking in visit {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function checkOut(): void {
        try {
            if (!$this->getCheckInTime()) {
                throw new \InvalidArgumentException("Visit not checked in");
            }
            if ($this->getCheckOutTime()) {
                throw new \InvalidArgumentException("Visit already checked out");
            }
            $this->setCheckOutTime(new \DateTime());
            $this->setStatus('COMPLETED');
        } catch (\Exception $e) {
            error_log("Error checking out visit {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByStatus(string $status): array {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid visit status");
            }
            return self::where('status', '=', $status)->get();
        } catch (\Exception $e) {
            error_log("Error finding visits by status {$status}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByPurpose(string $purpose): array {
        try {
            if (!in_array($purpose, self::VALID_PURPOSES)) {
                throw new \InvalidArgumentException("Invalid visit purpose");
            }
            return self::where('purpose', '=', $purpose)->get();
        } catch (\Exception $e) {
            error_log("Error finding visits by purpose {$purpose}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByVisitor(int $visitorId): array {
        try {
            return self::where('visitor_id', '=', $visitorId)->get();
        } catch (\Exception $e) {
            error_log("Error finding visits by visitor {$visitorId}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findInProgress(): array {
        try {
            return self::where('status', '=', 'IN_PROGRESS')->get();
        } catch (\Exception $e) {
            error_log("Error finding in-progress visits: " . $e->getMessage());
            throw $e;
        }
    }
} 