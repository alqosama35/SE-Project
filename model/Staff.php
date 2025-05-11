<?php

namespace App\Models;

class Staff extends Model {
    protected static string $table = 'staff';
    
    protected array $fillable = [
        'id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'position',
        'department',
        'hire_date',
        'status'
    ];
    
    protected array $relationships = [
        'user' => User::class
    ];

    private const VALID_POSITIONS = [
        'CURATOR',
        'CONSERVATOR',
        'EDUCATOR',
        'SECURITY',
        'ADMINISTRATOR',
        'TOUR_GUIDE',
        'RESTORER'
    ];

    private const VALID_DEPARTMENTS = [
        'COLLECTIONS',
        'EDUCATION',
        'SECURITY',
        'ADMINISTRATION',
        'TOURS',
        'RESTORATION'
    ];

    private const VALID_STATUSES = ['ACTIVE', 'ON_LEAVE', 'TERMINATED'];

    private string $id;
    private string $position;
    private array $managedCollections = [];
    private array $managedExhibitions = [];
    private array $managedRestorations = [];

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $this->id = $attributes['id'] ?? uniqid('staff_');
        $this->position = $attributes['position'] ?? 'STAFF';
    }

    public function hasRole(string $role): bool {
        return $this->position === $role;
    }

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
            error_log("Error setting first name for staff {$this->getAttribute('id')}: " . $e->getMessage());
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
            error_log("Error setting last name for staff {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getEmail(): string {
        return $this->getAttribute('email');
    }

    public function setEmail(string $email): void {
        try {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException("Invalid email format");
            }
            $this->setAttribute('email', $email);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting email for staff {$this->getAttribute('id')}: " . $e->getMessage());
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
            error_log("Error setting phone for staff {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPosition(): string {
        return $this->getAttribute('position');
    }

    public function setPosition(string $position): void {
        try {
            if (!in_array($position, self::VALID_POSITIONS)) {
                throw new \InvalidArgumentException("Invalid staff position");
            }
            $this->setAttribute('position', $position);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting position for staff {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getDepartment(): string {
        return $this->getAttribute('department');
    }

    public function setDepartment(string $department): void {
        try {
            if (!in_array($department, self::VALID_DEPARTMENTS)) {
                throw new \InvalidArgumentException("Invalid department");
            }
            $this->setAttribute('department', $department);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting department for staff {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getHireDate(): \DateTime {
        return new \DateTime($this->getAttribute('hire_date'));
    }

    public function setHireDate(\DateTime $hireDate): void {
        try {
            if ($hireDate > new \DateTime()) {
                throw new \InvalidArgumentException("Hire date cannot be in the future");
            }
            $this->setAttribute('hire_date', $hireDate->format('Y-m-d'));
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting hire date for staff {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getStatus(): string {
        return $this->getAttribute('status');
    }

    public function setStatus(string $status): void {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid staff status");
            }
            $this->setAttribute('status', $status);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting status for staff {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getUser(): ?User {
        try {
            return User::find($this->getAttribute('user_id'));
        } catch (\Exception $e) {
            error_log("Error getting user for staff {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function setUser(User $user): void {
        try {
            $this->setAttribute('user_id', $user->getId());
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting user for staff {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getFullName(): string {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    public function isActive(): bool {
        return $this->getStatus() === 'ACTIVE';
    }

    public function isOnLeave(): bool {
        return $this->getStatus() === 'ON_LEAVE';
    }

    public function isTerminated(): bool {
        return $this->getStatus() === 'TERMINATED';
    }

    public function getYearsOfService(): int {
        return $this->getHireDate()->diff(new \DateTime())->y;
    }

    public function getCuratedCollections(): array {
        return Collection::where('curator_id', '=', $this->getAttribute('id'))->get();
    }

    public function getCuratedExhibitions(): array {
        return Exhibition::where('curator_id', '=', $this->getAttribute('id'))->get();
    }

    public function getRestorations(): array {
        return Restoration::where('restorer_id', '=', $this->getAttribute('id'))->get();
    }

    public static function findByPosition(string $position): array {
        try {
            if (!in_array($position, self::VALID_POSITIONS)) {
                throw new \InvalidArgumentException("Invalid staff position");
            }
            return self::where('position', '=', $position)->get();
        } catch (\Exception $e) {
            error_log("Error finding staff by position {$position}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByDepartment(string $department): array {
        try {
            if (!in_array($department, self::VALID_DEPARTMENTS)) {
                throw new \InvalidArgumentException("Invalid department");
            }
            return self::where('department', '=', $department)->get();
        } catch (\Exception $e) {
            error_log("Error finding staff by department {$department}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByStatus(string $status): array {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid staff status");
            }
            return self::where('status', '=', $status)->get();
        } catch (\Exception $e) {
            error_log("Error finding staff by status {$status}: " . $e->getMessage());
            throw $e;
        }
    }
} 