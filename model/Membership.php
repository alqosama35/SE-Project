<?php

namespace App\Models;

class Membership implements Payable {
    private string $id;
    private \DateTime $startDate;
    private \DateTime $endDate;
    private MembershipPlan $plan;
    private string $status;
    private ?Payment $payment = null;

    private const VALID_STATUSES = ['PENDING', 'ACTIVE', 'CANCELLED', 'EXPIRED'];

    public function __construct(array $attributes = []) {
        try {
            $this->id = $attributes['id'] ?? uniqid('membership_');
            $this->startDate = $attributes['startDate'] ?? new \DateTime();
            $this->endDate = $attributes['endDate'] ?? (new \DateTime())->modify('+1 year');
            $this->plan = $attributes['plan'] ?? new MembershipPlan();
            $this->status = $attributes['status'] ?? 'PENDING';

            if (!$this->plan instanceof MembershipPlan) {
                throw new \InvalidArgumentException("Invalid membership plan");
            }
            if (!in_array($this->status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid membership status");
            }
        } catch (\Exception $e) {
            error_log("Error creating membership: " . $e->getMessage());
            throw $e;
        }
    }

    public function activate(): void {
        try {
            if ($this->status !== 'PENDING') {
                throw new \RuntimeException("Only pending memberships can be activated");
            }
            $this->status = 'ACTIVE';
            $this->startDate = new \DateTime();
            $this->endDate = (new \DateTime())->modify('+' . $this->plan->getDuration() . ' months');
        } catch (\Exception $e) {
            error_log("Error activating membership {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function renew(int $period): void {
        try {
            if (!$this->isValid()) {
                throw new \RuntimeException("Cannot renew an invalid membership");
            }
            if ($period <= 0) {
                throw new \InvalidArgumentException("Renewal period must be positive");
            }
            $this->endDate = (new \DateTime($this->endDate->format('Y-m-d')))
                ->modify('+' . $period . ' months');
        } catch (\Exception $e) {
            error_log("Error renewing membership {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function cancel(): void {
        try {
            if ($this->status !== 'ACTIVE') {
                throw new \RuntimeException("Only active memberships can be cancelled");
            }
            $this->status = 'CANCELLED';
            if ($this->payment) {
                $this->refundPayment($this->payment);
            }
        } catch (\Exception $e) {
            error_log("Error cancelling membership {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function isValid(): bool {
        try {
            return $this->status === 'ACTIVE' && $this->endDate > new \DateTime();
        } catch (\Exception $e) {
            error_log("Error checking membership validity {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getTotal(): float {
        try {
            return $this->plan->getPrice();
        } catch (\Exception $e) {
            error_log("Error getting membership total {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getDescription(): string {
        try {
            return "Membership: {$this->plan->getName()} - Valid until " . 
                   $this->endDate->format('Y-m-d');
        } catch (\Exception $e) {
            error_log("Error getting membership description {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    // Payable interface methods
    public function getId(): ?int {
        try {
            return (int)substr($this->id, 10); // Remove 'membership_' prefix
        } catch (\Exception $e) {
            error_log("Error getting membership ID: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAmount(): float {
        try {
            return $this->getTotal();
        } catch (\Exception $e) {
            error_log("Error getting membership amount {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function processPayment(Payment $payment): void {
        try {
            if ($this->status !== 'PENDING') {
                throw new \RuntimeException("Payment can only be processed for pending memberships");
            }
            $payment->setAmount($this->getAmount());
            $payment->process();
            $this->payment = $payment;
            $this->activate();
        } catch (\Exception $e) {
            error_log("Error processing membership payment {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function refundPayment(Payment $payment): void {
        try {
            if (!$this->payment || $this->payment->getId() !== $payment->getId()) {
                throw new \InvalidArgumentException("Invalid payment for refund");
            }
            $payment->refund();
            $this->status = 'CANCELLED';
        } catch (\Exception $e) {
            error_log("Error refunding membership payment {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPlan(): MembershipPlan {
        return $this->plan;
    }

    public function getStartDate(): \DateTime {
        return $this->startDate;
    }

    public function getEndDate(): \DateTime {
        return $this->endDate;
    }

    public function getStatus(): string {
        return $this->status;
    }
} 