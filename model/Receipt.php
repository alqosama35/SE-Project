<?php

namespace App\Models;

use DateTime;

class Receipt extends Model {
    protected static string $table = 'receipts';
    
    protected array $fillable = [
        'id',
        'amount',
        'payment_date',
        'method',
        'payment_id'
    ];
    
    protected array $relationships = [
        'payment' => Payment::class
    ];

    private const VALID_METHODS = ['credit_card', 'debit_card', 'paypal', 'bank_transfer'];

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
    }

    public function getAmount(): float {
        return (float)$this->getAttribute('amount');
    }

    public function setAmount(float $amount): void {
        try {
            if ($amount <= 0) {
                throw new \InvalidArgumentException("Amount must be greater than 0");
            }
            $this->setAttribute('amount', $amount);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting amount for receipt {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPaymentDate(): DateTime {
        return new DateTime($this->getAttribute('payment_date'));
    }

    public function setPaymentDate(DateTime $date): void {
        try {
            if ($date > new DateTime()) {
                throw new \InvalidArgumentException("Payment date cannot be in the future");
            }
            $this->setAttribute('payment_date', $date->format('Y-m-d H:i:s'));
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting payment date for receipt {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getMethod(): string {
        return $this->getAttribute('method');
    }

    public function setMethod(string $method): void {
        try {
            if (!in_array($method, self::VALID_METHODS)) {
                throw new \InvalidArgumentException("Invalid payment method");
            }
            $this->setAttribute('method', $method);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting method for receipt {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPayment(): ?Payment {
        try {
            return Payment::find($this->getAttribute('payment_id'));
        } catch (\Exception $e) {
            error_log("Error getting payment for receipt {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function setPayment(Payment $payment): void {
        try {
            $this->setAttribute('payment_id', $payment->getId());
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting payment for receipt {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByDateRange(DateTime $start, DateTime $end): array {
        try {
            if ($start > $end) {
                throw new \InvalidArgumentException("Start date must be before end date");
            }
            return self::where('payment_date', '>=', $start->format('Y-m-d H:i:s'))
                ->where('payment_date', '<=', $end->format('Y-m-d H:i:s'))
                ->get();
        } catch (\Exception $e) {
            error_log("Error finding receipts by date range: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByMethod(string $method): array {
        try {
            if (!in_array($method, self::VALID_METHODS)) {
                throw new \InvalidArgumentException("Invalid payment method");
            }
            return self::where('method', '=', $method)->get();
        } catch (\Exception $e) {
            error_log("Error finding receipts by method {$method}: " . $e->getMessage());
            throw $e;
        }
    }
} 