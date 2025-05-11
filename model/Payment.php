<?php

namespace App\Models;

use DateTime;
use App\Models\Model;

class Payment extends Model {
    protected static string $table = 'payments';
    
    protected array $fillable = [
        'id',
        'amount',
        'method',
        'payment_date',
        'status',
        'receipt_id',
        'payable_id',
        'payable_type'
    ];
    
    protected array $relationships = [
        'receipt' => Receipt::class,
        'payable' => Payable::class
    ];

    private const VALID_METHODS = ['credit_card', 'debit_card', 'paypal', 'bank_transfer'];
    private const VALID_STATUSES = ['pending', 'completed', 'refunded', 'failed'];

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
        if (!isset($this->attributes['payment_date'])) {
            $this->attributes['payment_date'] = (new DateTime())->format('Y-m-d H:i:s');
        }
        if (!isset($this->attributes['status'])) {
            $this->attributes['status'] = 'pending';
        }
    }

    public function process(): Receipt {
        try {
            if (!$this->validate()) {
                throw new \Exception('Payment validation failed');
            }

            if ($this->getStatus() !== 'pending') {
                throw new \Exception('Payment is not in pending status');
            }

            $this->setAttribute('status', 'completed');
            
            $receipt = new Receipt([
                'id' => uniqid('receipt_'),
                'amount' => $this->getAmount(),
                'payment_date' => $this->getPaymentDate()->format('Y-m-d H:i:s'),
                'method' => $this->getMethod()
            ]);
            
            $receipt->save();
            $this->setAttribute('receipt_id', $receipt->getId());
            $this->save();

            return $receipt;
        } catch (\Exception $e) {
            error_log("Error processing payment {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function refund(): bool {
        try {
            if ($this->getStatus() !== 'completed') {
                throw new \Exception('Only completed payments can be refunded');
            }

            $this->setAttribute('status', 'refunded');
            return $this->save();
        } catch (\Exception $e) {
            error_log("Error refunding payment {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function validate(): bool {
        try {
            if ($this->getAmount() <= 0) {
                throw new \InvalidArgumentException('Payment amount must be greater than 0');
            }

            if (!in_array($this->getMethod(), self::VALID_METHODS)) {
                throw new \InvalidArgumentException('Invalid payment method');
            }

            if (!in_array($this->getStatus(), self::VALID_STATUSES)) {
                throw new \InvalidArgumentException('Invalid payment status');
            }

            if ($this->getStatus() === 'refunded') {
                throw new \InvalidArgumentException('Refunded payments cannot be processed');
            }

            return true;
        } catch (\Exception $e) {
            error_log("Payment validation error: " . $e->getMessage());
            return false;
        }
    }

    public function setPayable(Payable $payable): void {
        try {
            if (!$payable->getId()) {
                throw new \InvalidArgumentException('Payable must have an ID');
            }
            $this->setAttribute('payable_id', $payable->getId());
            $this->setAttribute('payable_type', get_class($payable));
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting payable for payment {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPayable(): ?Payable {
        try {
            return $this->getAttribute('payable_id') ? 
                $this->getAttribute('payable_type')::find($this->getAttribute('payable_id')) : 
                null;
        } catch (\Exception $e) {
            error_log("Error getting payable for payment {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getReceipt(): ?Receipt {
        try {
            return $this->getAttribute('receipt_id') ? 
                Receipt::find($this->getAttribute('receipt_id')) : 
                null;
        } catch (\Exception $e) {
            error_log("Error getting receipt for payment {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAmount(): float {
        return (float)$this->getAttribute('amount');
    }

    public function setAmount(float $amount): void {
        try {
            if ($amount <= 0) {
                throw new \InvalidArgumentException('Payment amount must be greater than 0');
            }
            $this->setAttribute('amount', $amount);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting amount for payment {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPaymentDate(): DateTime {
        return new DateTime($this->getAttribute('payment_date'));
    }

    public function setPaymentDate(DateTime $date): void {
        try {
            if ($date > new DateTime()) {
                throw new \InvalidArgumentException('Payment date cannot be in the future');
            }
            $this->setAttribute('payment_date', $date->format('Y-m-d H:i:s'));
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting payment date for payment {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getMethod(): string {
        return $this->getAttribute('method');
    }

    public function setMethod(string $method): void {
        try {
            if (!in_array($method, self::VALID_METHODS)) {
                throw new \InvalidArgumentException('Invalid payment method');
            }
            $this->setAttribute('method', $method);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting method for payment {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getStatus(): string {
        return $this->getAttribute('status');
    }

    public function setStatus(string $status): void {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException('Invalid payment status');
            }
            $this->setAttribute('status', $status);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting status for payment {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByStatus(string $status): array {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException('Invalid payment status');
            }
            return self::where('status', '=', $status)->get();
        } catch (\Exception $e) {
            error_log("Error finding payments by status {$status}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByMethod(string $method): array {
        try {
            if (!in_array($method, self::VALID_METHODS)) {
                throw new \InvalidArgumentException('Invalid payment method');
            }
            return self::where('method', '=', $method)->get();
        } catch (\Exception $e) {
            error_log("Error finding payments by method {$method}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByDateRange(DateTime $start, DateTime $end): array {
        try {
            if ($start > $end) {
                throw new \InvalidArgumentException('Start date must be before end date');
            }
            return self::where('payment_date', '>=', $start->format('Y-m-d H:i:s'))
                ->where('payment_date', '<=', $end->format('Y-m-d H:i:s'))
                ->get();
        } catch (\Exception $e) {
            error_log("Error finding payments by date range: " . $e->getMessage());
            throw $e;
        }
    }
} 