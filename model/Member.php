<?php

namespace App\Models;

use App\Models\Event;
use App\Models\Booking;
use App\Models\Donation;
use App\Models\DonationProgram;
use App\Models\Loan;
use App\Models\Membership;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\ShoppingItem;
use App\Models\QueryBuilder;

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

    public function __construct(array $attributes = []) {
        $this->id = $attributes['id'] ?? uniqid('member_');
        $this->name = $attributes['name'] ?? '';
        $this->email = $attributes['email'] ?? '';
        $this->password = $attributes['password'] ?? '';
        $this->role = $attributes['role'] ?? MemberRole::MEMBER;
    }

    private function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validatePassword(string $password): bool {
        // At least 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 special char
        return strlen($password) >= 8 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password) &&
               preg_match('/[^A-Za-z0-9]/', $password);
    }

    public function register(string $name, string $email, string $password): bool {
        try {
            if (empty(trim($name))) {
                throw new \InvalidArgumentException("Name cannot be empty");
            }
            if (!$this->validateEmail($email)) {
                throw new \InvalidArgumentException("Invalid email format");
            }
            if (!$this->validatePassword($password)) {
                throw new \InvalidArgumentException("Password does not meet security requirements");
            }

            $this->name = $name;
            $this->email = $email;
            $this->password = password_hash($password, PASSWORD_DEFAULT);
            $this->role = MemberRole::MEMBER;
            return true;
        } catch (\Exception $e) {
            error_log("Error registering member: " . $e->getMessage());
            throw $e;
        }
    }

    public function login(string $email, string $password): bool {
        try {
            if (!$this->validateEmail($email)) {
                throw new \InvalidArgumentException("Invalid email format");
            }
            return $this->email === $email && 
                   password_verify($password, $this->password);
        } catch (\Exception $e) {
            error_log("Error logging in member: " . $e->getMessage());
            throw $e;
        }
    }

    public function logout(string $sessionId): void {
        try {
            // Implementation would typically involve session management
            session_destroy();
        } catch (\Exception $e) {
            error_log("Error logging out member: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateProfile(string $name, string $email): void {
        try {
            if (!empty($name) && empty(trim($name))) {
                throw new \InvalidArgumentException("Name cannot be empty");
            }
            if (!empty($email) && !$this->validateEmail($email)) {
                throw new \InvalidArgumentException("Invalid email format");
            }

            if (!empty($name)) {
                $this->name = $name;
            }
            if (!empty($email)) {
                $this->email = $email;
            }
        } catch (\Exception $e) {
            error_log("Error updating member profile: " . $e->getMessage());
            throw $e;
        }
    }

    public function changePassword(string $oldPass, string $newPass): bool {
        try {
            if (!password_verify($oldPass, $this->password)) {
                throw new \InvalidArgumentException("Current password is incorrect");
            }
            if (!$this->validatePassword($newPass)) {
                throw new \InvalidArgumentException("New password does not meet security requirements");
            }

            $this->password = password_hash($newPass, PASSWORD_DEFAULT);
            return true;
        } catch (\Exception $e) {
            error_log("Error changing member password: " . $e->getMessage());
            throw $e;
        }
    }

    public function bookTourEvent(string $eventId, int $participants): Booking {
        try {
            if ($participants <= 0) {
                throw new \InvalidArgumentException("Number of participants must be positive");
            }

            /** @var Event|null $event */
            $event = (new QueryBuilder(Event::class))->where('id', '=', $eventId)->first();
            if (!$event) {
                throw new \InvalidArgumentException("Event not found");
            }
            if (!$event->checkAvailability()) {
                throw new \RuntimeException("Event is not available");
            }

            $booking = new Booking([
                'participants' => $participants,
                'pricePerPerson' => $event->getPrice()
            ]);

            $this->addBooking($booking);
            return $booking;
        } catch (\Exception $e) {
            error_log("Error booking tour event: " . $e->getMessage());
            throw $e;
        }
    }

    public function viewMyBookings(): array {
        try {
            return $this->bookings;
        } catch (\Exception $e) {
            error_log("Error viewing member bookings: " . $e->getMessage());
            throw $e;
        }
    }

    public function cancelBooking(string $bookingId): bool {
        try {
            foreach ($this->bookings as $key => $booking) {
                if ($booking->getId() === $bookingId) {
                    $booking->cancel();
                    return true;
                }
            }
            throw new \InvalidArgumentException("Booking not found");
        } catch (\Exception $e) {
            error_log("Error cancelling booking: " . $e->getMessage());
            throw $e;
        }
    }

    public function modifyBooking(string $bookingId, int $participants): bool {
        try {
            if ($participants <= 0) {
                throw new \InvalidArgumentException("Number of participants must be positive");
            }

            foreach ($this->bookings as $booking) {
                if ($booking->getId() === $bookingId) {
                    $booking->modifyParticipants($participants);
                    return true;
                }
            }
            throw new \InvalidArgumentException("Booking not found");
        } catch (\Exception $e) {
            error_log("Error modifying booking: " . $e->getMessage());
            throw $e;
        }
    }

    public function submitDonation(string $programId, float $amount): Donation {
        try {
            if ($amount <= 0) {
                throw new \InvalidArgumentException("Donation amount must be positive");
            }

            /** @var DonationProgram|null $program */
            $program = (new QueryBuilder(DonationProgram::class))->where('id', '=', $programId)->first();
            if (!$program) {
                throw new \InvalidArgumentException("Donation program not found");
            }
            if (!$program->isActive()) {
                throw new \RuntimeException("Donation program is not active");
            }

            $donation = new Donation([
                'amount' => $amount,
                'program_id' => $programId
            ]);

            $program->addDonation($amount);
            return $donation;
        } catch (\Exception $e) {
            error_log("Error submitting donation: " . $e->getMessage());
            throw $e;
        }
    }

    public function addLoan(Loan $loan): void {
        try {
            $this->loans[] = $loan;
        } catch (\Exception $e) {
            error_log("Error adding loan: " . $e->getMessage());
            throw $e;
        }
    }

    public function addMembership(Membership $membership): void {
        try {
            $this->memberships[] = $membership;
        } catch (\Exception $e) {
            error_log("Error adding membership: " . $e->getMessage());
            throw $e;
        }
    }

    public function addBooking(Booking $booking): void {
        try {
            $this->bookings[] = $booking;
        } catch (\Exception $e) {
            error_log("Error adding booking: " . $e->getMessage());
            throw $e;
        }
    }

    public function addNotification(Notification $notification): void {
        try {
            $this->notifications[] = $notification;
        } catch (\Exception $e) {
            error_log("Error adding notification: " . $e->getMessage());
            throw $e;
        }
    }

    public function addPayment(Payment $payment): void {
        try {
            $this->payments[] = $payment;
        } catch (\Exception $e) {
            error_log("Error adding payment: " . $e->getMessage());
            throw $e;
        }
    }

    public function addShoppingItem(ShoppingItem $item): void {
        try {
            $this->shoppingItems[] = $item;
            parent::addShoppingItem($item);
        } catch (\Exception $e) {
            error_log("Error adding shopping item: " . $e->getMessage());
            throw $e;
        }
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

    public function getId(): ?int {
        return (int)substr($this->id, 7); // Remove 'member_' prefix
    }

    public function getName(): string {
        return $this->name;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getRole(): string {
        return $this->role->value;
    }
} 