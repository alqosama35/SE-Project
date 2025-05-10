<?php

namespace App\Models;

use DateTime;

class Payment {
    private string $id;
    private float $amount;
    private DateTime $paymentDate;
    private string $method;
    private string $status;
    private ?Receipt $receipt = null;
    private ?Payable $payable = null;

    public function __construct(string $id, float $amount, string $method) {
        $this->id = $id;
        $this->amount = $amount;
        $this->method = $method;
        $this->paymentDate = new DateTime();
        $this->status = 'pending';
    }

    public function process(): Receipt {
        if (!$this->validate()) {
            throw new \Exception('Payment validation failed');
        }

        $this->status = 'completed';
        $this->receipt = new Receipt(
            uniqid('receipt_'),
            $this->amount,
            $this->paymentDate,
            $this->method
        );

        return $this->receipt;
    }

    public function refund(): bool {
        if ($this->status !== 'completed') {
            return false;
        }

        $this->status = 'refunded';
        return true;
    }

    public function validate(): bool {
        // Basic validation rules
        if ($this->amount <= 0) {
            return false;
        }

        if (!in_array($this->method, ['credit_card', 'debit_card', 'paypal', 'bank_transfer'])) {
            return false;
        }

        if ($this->status === 'refunded') {
            return false;
        }

        return true;
    }

    public function setPayable(Payable $payable): void {
        $this->payable = $payable;
    }

    public function getPayable(): ?Payable {
        return $this->payable;
    }

    public function getReceipt(): ?Receipt {
        return $this->receipt;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getAmount(): float {
        return $this->amount;
    }

    public function getPaymentDate(): DateTime {
        return $this->paymentDate;
    }

    public function getMethod(): string {
        return $this->method;
    }

    public function getStatus(): string {
        return $this->status;
    }
} 