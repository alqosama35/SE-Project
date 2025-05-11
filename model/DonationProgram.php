<?php

namespace App\Models;

class DonationProgram extends Model {
    protected static string $table = 'donation_programs';
    
    protected array $fillable = [
        'id',
        'name',
        'description',
        'goalAmount',
        'currentAmount',
        'startDate',
        'endDate',
        'active'
    ];

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $this->validateGoalAmount($this->getAttribute('goalAmount'));
        $this->validateDates(
            new \DateTime($this->getAttribute('startDate')),
            new \DateTime($this->getAttribute('endDate'))
        );
    }

    private function validateGoalAmount(float $amount): void {
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Goal amount must be greater than 0");
        }
    }

    private function validateDates(\DateTime $start, \DateTime $end): void {
        if ($start >= $end) {
            throw new \InvalidArgumentException("Start date must be before end date");
        }
    }

    public function startProgram(): void {
        if ($this->getAttribute('active')) {
            throw new \RuntimeException("Program is already active");
        }
        $this->setAttribute('active', true);
        $this->setAttribute('startDate', (new \DateTime())->format('Y-m-d H:i:s'));
        $this->save();
    }

    public function endProgram(): void {
        if (!$this->getAttribute('active')) {
            throw new \RuntimeException("Program is already inactive");
        }
        $this->setAttribute('active', false);
        $this->setAttribute('endDate', (new \DateTime())->format('Y-m-d H:i:s'));
        $this->save();
    }

    public function updateGoal(float $newGoal): void {
        $this->validateGoalAmount($newGoal);
        $this->setAttribute('goalAmount', $newGoal);
        $this->save();
    }

    public function isActive(): bool {
        $now = new \DateTime();
        return $this->getAttribute('active') && 
               $now >= new \DateTime($this->getAttribute('startDate')) && 
               $now <= new \DateTime($this->getAttribute('endDate'));
    }

    public function addDonation(float $amount): void {
        try {
            if ($amount <= 0) {
                throw new \InvalidArgumentException("Donation amount must be greater than 0");
            }
            $currentAmount = (float)$this->getAttribute('current_amount');
            $this->setAttribute('current_amount', $currentAmount + $amount);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error adding donation to program {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getProgress(): float {
        $goalAmount = $this->getAttribute('goalAmount');
        if ($goalAmount === 0) {
            return 0;
        }
        return ($this->getAttribute('currentAmount') / $goalAmount) * 100;
    }
} 