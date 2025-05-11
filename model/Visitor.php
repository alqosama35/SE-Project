<?php

namespace App\Models;

class Visitor extends User {
    protected static string $table = 'visitors';
    
    protected array $fillable = [
        'id',
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'address',
        'membership_id',
        'preferences',
        'status'
    ];
    
    protected array $relationships = [
        'membership' => Membership::class,
        'tickets' => Ticket::class,
        'visits' => Visit::class
    ];

    private const VALID_STATUSES = ['ACTIVE', 'INACTIVE', 'BLOCKED'];

    public function getFirstName(): string {
        return $this->getAttribute('first_name');
    }

    public function setFirstName(string $firstName): void {
        try {
            if (empty(trim($firstName))) {
                throw new \InvalidArgumentException("First name cannot be empty");
            }
            $this->setAttribute('first_name', $firstName);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting first name for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getLastName(): string {
        return $this->getAttribute('last_name');
    }

    public function setLastName(string $lastName): void {
        try {
            if (empty(trim($lastName))) {
                throw new \InvalidArgumentException("Last name cannot be empty");
            }
            $this->setAttribute('last_name', $lastName);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting last name for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPhone(): string {
        return $this->getAttribute('phone');
    }

    public function setPhone(string $phone): void {
        try {
            if (empty(trim($phone))) {
                throw new \InvalidArgumentException("Phone number cannot be empty");
            }
            $this->setAttribute('phone', $phone);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting phone for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAddress(): string {
        return $this->getAttribute('address');
    }

    public function setAddress(string $address): void {
        try {
            if (empty(trim($address))) {
                throw new \InvalidArgumentException("Address cannot be empty");
            }
            $this->setAttribute('address', $address);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting address for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getMembership(): ?Membership {
        try {
            $membershipId = $this->getAttribute('membership_id');
            if (!$membershipId) {
                return null;
            }
            $result = (new QueryBuilder(Membership::class))->where('id', '=', $membershipId)->first();
            return $result instanceof Membership ? $result : null;
        } catch (\Exception $e) {
            error_log("Error getting membership for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function setMembership(?Membership $membership): void {
        try {
            $this->setAttribute('membership_id', $membership ? $membership->getId() : null);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting membership for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPreferences(): array {
        $preferences = $this->getAttribute('preferences');
        return is_string($preferences) ? json_decode($preferences, true) : $preferences;
    }

    public function setPreferences(array $preferences): void {
        try {
            $this->setAttribute('preferences', json_encode($preferences));
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting preferences for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getStatus(): string {
        return $this->getAttribute('status');
    }

    public function setStatus(string $status): void {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid visitor status");
            }
            $this->setAttribute('status', $status);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting status for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getTickets(): array {
        try {
            return Ticket::where('visitor_id', '=', $this->getAttribute('id'))->get();
        } catch (\Exception $e) {
            error_log("Error getting tickets for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getVisits(): array {
        try {
            return Visit::where('visitor_id', '=', $this->getAttribute('id'))->get();
        } catch (\Exception $e) {
            error_log("Error getting visits for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getFullName(): string {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    public function isActive(): bool {
        return $this->getStatus() === 'ACTIVE';
    }

    public function isInactive(): bool {
        return $this->getStatus() === 'INACTIVE';
    }

    public function isBlocked(): bool {
        return $this->getStatus() === 'BLOCKED';
    }

    public function hasMembership(): bool {
        return $this->getAttribute('membership_id') !== null;
    }

    public function getActiveTickets(): array {
        try {
            return Ticket::where('visitor_id', '=', $this->getAttribute('id'))
                ->where('status', '=', 'PURCHASED')
                ->get();
        } catch (\Exception $e) {
            error_log("Error getting active tickets for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getUpcomingVisits(): array {
        try {
            $now = new \DateTime();
            return Visit::where('visitor_id', '=', $this->getAttribute('id'))
                ->where('status', '=', 'SCHEDULED')
                ->where('check_in_time', '>', $now->format('Y-m-d H:i:s'))
                ->get();
        } catch (\Exception $e) {
            error_log("Error getting upcoming visits for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByStatus(string $status): array {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid visitor status");
            }
            return self::where('status', '=', $status)->get();
        } catch (\Exception $e) {
            error_log("Error finding visitors by status {$status}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByMembership(int $membershipId): array {
        try {
            return self::where('membership_id', '=', $membershipId)->get();
        } catch (\Exception $e) {
            error_log("Error finding visitors by membership {$membershipId}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findActive(): array {
        try {
            return self::where('status', '=', 'ACTIVE')->get();
        } catch (\Exception $e) {
            error_log("Error finding active visitors: " . $e->getMessage());
            throw $e;
        }
    }

    public function browseExhibits(): array {
        try {
            return Exhibition::where('status', '=', 'ACTIVE')->get();
        } catch (\Exception $e) {
            error_log("Error browsing exhibits for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function viewExhibitDetails(string $exhibitId): array {
        try {
            $exhibit = (new QueryBuilder(Exhibition::class))->where('id', '=', $exhibitId)->first();
            if (!$exhibit) {
                throw new \InvalidArgumentException("Exhibit not found");
            }
            return [
                'id' => $exhibit->getAttribute('id'),
                'title' => $exhibit->getAttribute('title'),
                'description' => $exhibit->getAttribute('description'),
                'start_date' => $exhibit->getAttribute('start_date'),
                'end_date' => $exhibit->getAttribute('end_date')
            ];
        } catch (\Exception $e) {
            error_log("Error viewing exhibit details for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function searchExhibits(string $query): array {
        try {
            return Exhibition::where('title', 'LIKE', "%{$query}%")
                ->orWhere('description', 'LIKE', "%{$query}%")
                ->get();
        } catch (\Exception $e) {
            error_log("Error searching exhibits for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function viewToursAndEvents(): array {
        try {
            return Tour::where('status', '=', 'ACTIVE')->get();
        } catch (\Exception $e) {
            error_log("Error viewing tours and events for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function viewDonationPrograms(): array {
        try {
            return DonationProgram::where('active', '=', true)->get();
        } catch (\Exception $e) {
            error_log("Error viewing donation programs for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function applyAsVolunteer(string $applicationDetails): bool {
        try {
            // Implementation would typically involve creating a volunteer application
            return true;
        } catch (\Exception $e) {
            error_log("Error applying as volunteer for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function viewFamilyGroupVisitsInfo(): array {
        try {
            return Tour::where('type', '=', 'FAMILY')
                ->where('status', '=', 'ACTIVE')
                ->get();
        } catch (\Exception $e) {
            error_log("Error viewing family group visits info for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function viewMuseumMap(): array {
        try {
            return Gallery::all();
        } catch (\Exception $e) {
            error_log("Error viewing museum map for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function viewFoodAndDrink(): array {
        try {
            return ShoppingItem::where('category', '=', 'FOOD_AND_DRINK')
                ->where('status', '=', 'AVAILABLE')
                ->get();
        } catch (\Exception $e) {
            error_log("Error viewing food and drink for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function shopSouvenirs(): array {
        try {
            return ShoppingItem::where('category', '=', 'SOUVENIR')
                ->where('status', '=', 'AVAILABLE')
                ->get();
        } catch (\Exception $e) {
            error_log("Error shopping souvenirs for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function viewEventDetails(string $eventId): array {
        try {
            $event = (new QueryBuilder(Tour::class))->where('id', '=', $eventId)->first();
            if (!$event) {
                throw new \InvalidArgumentException("Event not found");
            }
            return [
                'id' => $event->getAttribute('id'),
                'title' => $event->getAttribute('title'),
                'description' => $event->getAttribute('description'),
                'date' => $event->getAttribute('date'),
                'capacity' => $event->getAttribute('capacity'),
                'current_participants' => $event->getAttribute('current_participants')
            ];
        } catch (\Exception $e) {
            error_log("Error viewing event details for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function registerForEvent(string $eventId): void {
        try {
            $event = (new QueryBuilder(Tour::class))->where('id', '=', $eventId)->first();
            if (!$event) {
                throw new \InvalidArgumentException("Event not found");
            }
            $currentParticipants = (int)$event->getAttribute('current_participants');
            $capacity = (int)$event->getAttribute('capacity');
            if ($currentParticipants >= $capacity) {
                throw new \RuntimeException("Event is fully booked");
            }
            $event->setAttribute('current_participants', $currentParticipants + 1);
            $event->save();
        } catch (\Exception $e) {
            error_log("Error registering for event for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function viewTourDetails(string $tourId): array {
        try {
            $tour = (new QueryBuilder(Tour::class))->where('id', '=', $tourId)->first();
            if (!$tour) {
                throw new \InvalidArgumentException("Tour not found");
            }
            return [
                'id' => $tour->getAttribute('id'),
                'title' => $tour->getAttribute('title'),
                'description' => $tour->getAttribute('description'),
                'date' => $tour->getAttribute('date'),
                'capacity' => $tour->getAttribute('capacity'),
                'current_participants' => $tour->getAttribute('current_participants')
            ];
        } catch (\Exception $e) {
            error_log("Error viewing tour details for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function bookTour(string $tourId): void {
        try {
            $tour = (new QueryBuilder(Tour::class))->where('id', '=', $tourId)->first();
            if (!$tour) {
                throw new \InvalidArgumentException("Tour not found");
            }
            $currentParticipants = (int)$tour->getAttribute('current_participants');
            $capacity = (int)$tour->getAttribute('capacity');
            if ($currentParticipants >= $capacity) {
                throw new \RuntimeException("Tour is fully booked");
            }
            $tour->setAttribute('current_participants', $currentParticipants + 1);
            $tour->save();
        } catch (\Exception $e) {
            error_log("Error booking tour for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function viewTicketDetails(string $ticketId): array {
        try {
            $ticket = (new QueryBuilder(Ticket::class))->where('id', '=', $ticketId)->first();
            if (!$ticket) {
                throw new \InvalidArgumentException("Ticket not found");
            }
            return [
                'id' => $ticket->getAttribute('id'),
                'type' => $ticket->getAttribute('type'),
                'price' => $ticket->getAttribute('price'),
                'status' => $ticket->getAttribute('status')
            ];
        } catch (\Exception $e) {
            error_log("Error viewing ticket details for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function purchaseTicket(string $ticketId): void {
        try {
            $ticket = (new QueryBuilder(Ticket::class))->where('id', '=', $ticketId)->first();
            if (!$ticket) {
                throw new \InvalidArgumentException("Ticket not found");
            }
            if ($ticket->getAttribute('status') !== 'AVAILABLE') {
                throw new \RuntimeException("Ticket is not available");
            }
            $ticket->setAttribute('visitor_id', $this->getId());
            $ticket->setAttribute('status', 'PURCHASED');
            $ticket->save();
        } catch (\Exception $e) {
            error_log("Error purchasing ticket for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function filterExhibits(array $criteria): array {
        try {
            $query = new QueryBuilder(Exhibition::class);
            foreach ($criteria as $key => $value) {
                $query->where($key, '=', $value);
            }
            return $query->get();
        } catch (\Exception $e) {
            error_log("Error filtering exhibits for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function viewShoppingItems(): array {
        try {
            return ShoppingItem::where('status', '=', 'AVAILABLE')->get();
        } catch (\Exception $e) {
            error_log("Error viewing shopping items for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function addToCart(string $itemId): void {
        try {
            $item = (new QueryBuilder(ShoppingItem::class))->where('id', '=', $itemId)->first();
            if (!$item) {
                throw new \InvalidArgumentException("Item not found");
            }
            if ($item->getAttribute('status') !== 'AVAILABLE') {
                throw new \RuntimeException("Item is not available");
            }
            $item->setAttribute('visitor_id', $this->getId());
            $item->setAttribute('status', 'IN_CART');
            $item->save();
        } catch (\Exception $e) {
            error_log("Error adding item to cart for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function viewCart(): array {
        try {
            return ShoppingItem::where('visitor_id', '=', $this->getAttribute('id'))
                ->where('status', '=', 'IN_CART')
                ->get();
        } catch (\Exception $e) {
            error_log("Error viewing cart for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function checkout(): void {
        try {
            $cartItems = $this->viewCart();
            if (empty($cartItems)) {
                throw new \RuntimeException("Cart is empty");
            }
            foreach ($cartItems as $item) {
                $item->setStatus('PURCHASED');
                $item->save();
            }
        } catch (\Exception $e) {
            error_log("Error checking out for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function viewMembershipPlans(): array {
        try {
            return (new QueryBuilder(Membership::class))
                ->where('status', '=', 'ACTIVE')
                ->get();
        } catch (\Exception $e) {
            error_log("Error viewing membership plans for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function purchaseMembership(string $planId): void {
        try {
            $plan = (new QueryBuilder(Membership::class))->where('id', '=', $planId)->first();
            if (!$plan || !($plan instanceof Membership)) {
                throw new \InvalidArgumentException("Membership plan not found");
            }
            if ($plan->getAttribute('status') !== 'ACTIVE') {
                throw new \RuntimeException("Membership plan is not active");
            }
            $this->setMembership($plan);
        } catch (\Exception $e) {
            error_log("Error purchasing membership for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function makeDonation(string $programId, float $amount): void {
        try {
            $program = (new QueryBuilder(DonationProgram::class))->where('id', '=', $programId)->first();
            if (!$program) {
                throw new \InvalidArgumentException("Donation program not found");
            }
            $now = new \DateTime();
            $startDate = new \DateTime($program->getAttribute('startDate'));
            $endDate = new \DateTime($program->getAttribute('endDate'));
            if (!$program->getAttribute('active') || $now < $startDate || $now > $endDate) {
                throw new \RuntimeException("Donation program is not active");
            }
            $donation = new Donation([
                'visitor_id' => $this->getAttribute('id'),
                'program_id' => $programId,
                'amount' => $amount,
                'status' => 'PENDING'
            ]);
            $donation->save();
        } catch (\Exception $e) {
            error_log("Error making donation for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function submitFeedback(string $feedback): void {
        try {
            // Implementation would typically involve creating a feedback record
        } catch (\Exception $e) {
            error_log("Error submitting feedback for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function contactSupport(string $message): void {
        try {
            // Implementation would typically involve creating a support ticket
        } catch (\Exception $e) {
            error_log("Error contacting support for visitor {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function addShoppingItem(ShoppingItem $item): void {
        try {
            $item->setAttribute('visitor_id', $this->getId());
            $item->save();
        } catch (\Exception $e) {
            error_log("Error adding shopping item for visitor {$this->getId()}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getRole(): string {
        return 'VISITOR';
    }
} 