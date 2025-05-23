<?php

namespace App\Models;

class Donation extends Model {
    protected static string $table = 'donations';
    
    protected array $fillable = [
        'id',
        'visitor_id',
        'program_id',
        'amount',
        'status',
        'created_at'
    ];

    protected array $attributes = [];

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
    }

    public function setAttribute(string $key, $value): void {
        $this->attributes[$key] = $value;
    }

    public function getAttribute($key) {
        return $this->attributes[$key] ?? null;
    }

    public function save(): bool {
        try {
            if ($this->exists) {
                return $this->update();
            }
            return $this->insert();
        } catch (\Exception $e) {
            error_log("Error saving donation: " . $e->getMessage());
            throw $e;
        }
    }

    protected function insert(): bool {
        $attributes = $this->getDirty();
        if (empty($attributes)) {
            return true;
        }

        $columns = implode(', ', array_keys($attributes));
        $values = implode(', ', array_fill(0, count($attributes), '?'));
        
        $sql = "INSERT INTO " . static::$table . " ($columns) VALUES ($values)";
        
        $result = Database::getInstance()->query($sql, array_values($attributes));
        
        if ($result) {
            $this->exists = true;
            $this->original = $this->attributes;
            return true;
        }
        
        return false;
    }

    protected function update(): bool {
        $attributes = $this->getDirty();
        if (empty($attributes)) {
            return true;
        }

        $set = [];
        foreach (array_keys($attributes) as $column) {
            $set[] = "$column = ?";
        }
        
        $sql = "UPDATE " . static::$table . " SET " . implode(', ', $set) . 
               " WHERE id = ?";
        
        $values = array_values($attributes);
        $values[] = $this->getAttribute('id');
        
        $result = Database::getInstance()->query($sql, $values);
        
        if ($result) {
            $this->original = $this->attributes;
            return true;
        }
        
        return false;
    }

    private function validateAmount(float $amount): void {
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Donation amount must be greater than 0");
        }
    }

    private function validateProgramId(string $programId): void {
        if (empty(trim($programId))) {
            throw new \InvalidArgumentException("Program ID cannot be empty");
        }
        $program = DonationProgram::find($programId);
        if (!$program) {
            throw new \InvalidArgumentException("Invalid program ID");
        }
        if (!$this->isProgramActive($program)) {
            throw new \RuntimeException("Donation program is not active");
        }
    }

    private function isProgramActive(DonationProgram $program): bool {
        try {
            $now = new \DateTime();
            $startDate = new \DateTime($program->getAttribute('startDate'));
            $endDate = new \DateTime($program->getAttribute('endDate'));
            return $program->getAttribute('active') && 
                   $now >= $startDate && 
                   $now <= $endDate;
        } catch (\Exception $e) {
            error_log("Error checking program status: " . $e->getMessage());
            throw $e;
        }
    }

    public function getVisitor(): ?Visitor {
        try {
            return Visitor::find($this->getAttribute('visitor_id'));
        } catch (\Exception $e) {
            error_log("Error getting visitor for donation {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function setVisitor(Visitor $visitor): void {
        try {
            $this->setAttribute('visitor_id', $visitor->getId());
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting visitor for donation {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAmount(): float {
        return (float)$this->getAttribute('amount');
    }

    public function setAmount(float $amount): void {
        try {
            $this->validateAmount($amount);
            $this->setAttribute('amount', $amount);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting amount for donation {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getProgramId(): string {
        return $this->getAttribute('program_id');
    }

    public function setProgramId(string $programId): void {
        try {
            $this->validateProgramId($programId);
            $this->setAttribute('program_id', $programId);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting program ID for donation {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPayment(): ?Payment {
        try {
            return Payment::find($this->getAttribute('payment_id'));
        } catch (\Exception $e) {
            error_log("Error getting payment for donation {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function setPayment(Payment $payment): void {
        try {
            $this->setAttribute('payment_id', $payment->getId());
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting payment for donation {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getDonationDate(): string {
        return $this->getAttribute('donation_date');
    }

    public function setDonationDate(string $donationDate): void {
        try {
            $date = new \DateTime($donationDate);
            $this->setAttribute('donation_date', $date->format('Y-m-d H:i:s'));
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting donation date for donation {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getStatus(): string {
        return $this->getAttribute('status');
    }

    public function setStatus(string $status): void {
        try {
            if (!in_array($status, ['PENDING', 'COMPLETED', 'CANCELLED'])) {
                throw new \InvalidArgumentException("Invalid status: $status");
            }
            $this->setAttribute('status', $status);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting status for donation {$this->getAttribute('id')}: " . $e->getMessage());
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
            error_log("Error setting notes for donation {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function isPending(): bool {
        return $this->getAttribute('status') === 'PENDING';
    }

    public function isCompleted(): bool {
        return $this->getAttribute('status') === 'COMPLETED';
    }

    public function isCancelled(): bool {
        return $this->getAttribute('status') === 'CANCELLED';
    }

    public function processPayment(Payment $payment): void {
        try {
            if (!$this->isPending()) {
                throw new \RuntimeException("Payment can only be processed for pending donations");
            }
            $payment->process();
            $this->setPayment($payment);
            $this->setStatus('COMPLETED');
            
            // Update program's current amount
            $program = DonationProgram::find($this->getProgramId());
            if ($program) {
                $currentAmount = (float)$program->getAttribute('current_amount');
                $program->setAttribute('current_amount', $currentAmount + $this->getAmount());
                $program->save();
            }
        } catch (\Exception $e) {
            error_log("Error processing payment for donation {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function cancel(): void {
        try {
            if ($this->isCompleted()) {
                $payment = $this->getPayment();
                if ($payment) {
                    $payment->refund();
                }
            }
            $this->setStatus('CANCELLED');
        } catch (\Exception $e) {
            error_log("Error cancelling donation {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByVisitor(int $visitorId): array {
        try {
            return self::where('visitor_id', '=', $visitorId)->get();
        } catch (\Exception $e) {
            error_log("Error finding donations by visitor {$visitorId}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByStatus(string $status): array {
        try {
            if (!in_array($status, ['PENDING', 'COMPLETED', 'CANCELLED'])) {
                throw new \InvalidArgumentException("Invalid status: $status");
            }
            return self::where('status', '=', $status)->get();
        } catch (\Exception $e) {
            error_log("Error finding donations by status {$status}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByProgram(string $programId): array {
        try {
            return self::where('program_id', '=', $programId)->get();
        } catch (\Exception $e) {
            error_log("Error finding donations by program {$programId}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findPending(): array {
        try {
            return self::where('status', '=', 'PENDING')->get();
        } catch (\Exception $e) {
            error_log("Error finding pending donations: " . $e->getMessage());
            throw $e;
        }
    }
}
