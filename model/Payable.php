<?php

namespace App\Models;

interface Payable {
    public function getTotal(): float;
    public function getDescription(): string;
} 