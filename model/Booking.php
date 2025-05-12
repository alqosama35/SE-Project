<?php

namespace App\Models;

class Booking extends Model {
    protected static string $table = 'bookings';
    
    protected array $fillable = [
        'id',
        'member_id',
        'event_id',
        'participants',
        'status',
        'created_at',
        'updated_at'
    ];

    private const VALID_STATUSES = ['PENDING', 'CONFIRMED', 'CANCELLED'];

    private string $id;
    private \DateTime $bookingDate;
    private string $status;
    private int $participants;
    private float $pricePerPerson;
    private ?Payment $payment = null;

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $this->id = $attributes['id'] ?? uniqid('booking_');
        $this->bookingDate = $attributes['bookingDate'] ?? new \DateTime();
        $this->status = $attributes['status'] ?? 'PENDING';
        $this->participants = $attributes['participants'] ?? 1;
        $this->pricePerPerson = $attributes['pricePerPerson'] ?? 0.0;

        // Validate initial values
        $this->validateParticipants($this->participants);
        $this->validatePrice($this->pricePerPerson);
    }

    private function validateParticipants(int $count): void {
        if ($count < 1) {
            throw new \InvalidArgumentException("Number of participants must be at least 1");
        }
    }

    private function validatePrice(float $price): void {
        if ($price < 0) {
            throw new \InvalidArgumentException("Price per person cannot be negative");
        }
    }

    public function confirm(): void {
        if ($this->status === 'PENDING') {
            $this->status = 'CONFIRMED';
        } else {
            throw new \RuntimeException("Only pending bookings can be confirmed");
        }
    }

    public function cancel(): void {
        if ($this->status === 'CANCELLED') {
            throw new \RuntimeException("Booking is already cancelled");
        }
        
        $this->status = 'CANCELLED';
        if ($this->payment) {
            try {
                $this->refundPayment($this->payment);
            } catch (\Exception $e) {
                error_log("Error refunding payment for booking {$this->id}: " . $e->getMessage());
                throw $e;
            }
        }
    }

    public function modifyParticipants(int $count): void {
        $this->validateParticipants($count);
        
        if ($this->status === 'CANCELLED') {
            throw new \RuntimeException("Cannot modify cancelled booking");
        }
        
        $this->participants = $count;
    }

    public function getTotal(): float {
        return $this->participants * $this->pricePerPerson;
    }

    public function getDescription(): string {
        return "Booking for {$this->participants} participants on " . $this->bookingDate->format('Y-m-d H:i:s');
    }

    // Payable interface methods
    public function getId(): ?int {
        return (int)substr($this->id, 8); // Remove 'booking_' prefix
    }

    public function getAmount(): float {
        return $this->getTotal();
    }

    public function processPayment(Payment $payment): void {
        if ($this->status !== 'PENDING') {
            throw new \RuntimeException("Payment can only be processed for pending bookings");
        }

        try {
            $payment->setAmount($this->getAmount());
            $payment->process();
            $this->payment = $payment;
            $this->confirm();
        } catch (\Exception $e) {
            error_log("Error processing payment for booking {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function refundPayment(Payment $payment): void {
        if (!$this->payment) {
            throw new \RuntimeException("No payment associated with this booking");
        }

        if ($this->payment->getId() !== $payment->getId()) {
            throw new \RuntimeException("Payment ID mismatch");
        }

        try {
            $payment->refund();
            $this->status = 'CANCELLED';
        } catch (\Exception $e) {
            error_log("Error refunding payment for booking {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getStatus(): string {
        return $this->getAttribute('status');
    }

    public function getBookingDate(): \DateTime {
        return $this->bookingDate;
    }

    public function getParticipants(): int {
        return $this->participants;
    }

    public function getPricePerPerson(): float {
        return $this->pricePerPerson;
    }

    public function getPayment(): ?Payment {
        return $this->payment;
    }

    public function setStatus(string $status): void {
        if (!in_array($status, self::VALID_STATUSES)) {
            throw new \InvalidArgumentException("Invalid booking status");
        }
        $this->setAttribute('status', $status);
        $this->save();
    }
} 