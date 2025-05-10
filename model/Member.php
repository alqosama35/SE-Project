<?php

namespace App\Models;

class Member extends Visitor {
    private string $id;
    private string $name;
    private string $email;
    private string $password;
    private MemberRole $role;
    private array $loans = [];
    private array $memberships = [];
    private array $bookings = [];
    private array $notifications = [];
    private array $payments = [];
    private array $shoppingItems = [];

    public function register(string $name, string $email, string $password): bool {
        // Implementation
        return true;
    }

    public function login(string $email, string $password): bool {
        // Implementation
        return true;
    }

    public function logout(string $sessionId): void {
        // Implementation
    }

    public function updateProfile(string $name, string $email): void {
        // Implementation
    }

    public function changePassword(string $oldPass, string $newPass): bool {
        // Implementation
        return true;
    }

    public function bookTourEvent(string $eventId, int $participants): Booking {
        // Implementation
        return new Booking();
    }

    public function viewMyBookings(): array {
        return $this->bookings;
    }

    public function cancelBooking(string $bookingId): bool {
        // Implementation
        return true;
    }

    public function modifyBooking(string $bookingId, int $participants): bool {
        // Implementation
        return true;
    }

    public function submitDonation(string $programId, float $amount): Donation {
        // Implementation
        return new Donation();
    }

    public function addLoan(Loan $loan): void {
        $this->loans[] = $loan;
    }

    public function addMembership(Membership $membership): void {
        $this->memberships[] = $membership;
    }

    public function addBooking(Booking $booking): void {
        $this->bookings[] = $booking;
    }

    public function addNotification(Notification $notification): void {
        $this->notifications[] = $notification;
    }

    public function addPayment(Payment $payment): void {
        $this->payments[] = $payment;
    }

    public function addShoppingItem(Shopping $shopping): void {
        $this->shoppingItems[] = $shopping;
    }

    public function getLoans(): array {
        return $this->loans;
    }

    public function getMemberships(): array {
        return $this->memberships;
    }

    public function getBookings(): array {
        return $this->bookings;
    }

    public function getNotifications(): array {
        return $this->notifications;
    }

    public function getPayments(): array {
        return $this->payments;
    }

    public function getShoppingItems(): array {
        return $this->shoppingItems;
    }
} 