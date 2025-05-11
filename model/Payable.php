<?php

namespace App\Models;

interface Payable {
    public function getId(): ?int;
    public function getAmount(): float;
    public function processPayment(Payment $payment): void;
    public function refundPayment(Payment $payment): void;
} 