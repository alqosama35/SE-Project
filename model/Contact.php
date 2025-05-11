<?php

namespace App\Models;

class Contact extends Model {
    protected static string $table = 'contacts';
    
    protected array $fillable = [
        'id',
        'visitor_id',
        'name',
        'email',
        'subject',
        'message',
        'sent_at',
        'status',
        'response'
    ];
    
    protected array $relationships = [
        'visitor' => Visitor::class
    ];

    private function validateEmail(string $email): void {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email format");
        }
    }

    private function validateMessage(string $message): void {
        if (empty(trim($message))) {
            throw new \InvalidArgumentException("Message cannot be empty");
        }
        if (strlen($message) > 1000) {
            throw new \InvalidArgumentException("Message cannot exceed 1000 characters");
        }
    }

    public function getVisitor(): ?Visitor {
        try {
            return Visitor::find($this->getAttribute('visitor_id'));
        } catch (\Exception $e) {
            error_log("Error getting visitor for contact {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function setVisitor(Visitor $visitor): void {
        try {
            $this->setAttribute('visitor_id', $visitor->getId());
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting visitor for contact {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getName(): string {
        return $this->getAttribute('name');
    }

    public function setName(string $name): void {
        try {
            if (empty(trim($name))) {
                throw new \InvalidArgumentException("Name cannot be empty");
            }
            $this->setAttribute('name', $name);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting name for contact {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getEmail(): string {
        return $this->getAttribute('email');
    }

    public function setEmail(string $email): void {
        try {
            $this->validateEmail($email);
            $this->setAttribute('email', $email);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting email for contact {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getSubject(): string {
        return $this->getAttribute('subject');
    }

    public function setSubject(string $subject): void {
        try {
            if (empty(trim($subject))) {
                throw new \InvalidArgumentException("Subject cannot be empty");
            }
            $this->setAttribute('subject', $subject);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting subject for contact {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getMessage(): string {
        return $this->getAttribute('message');
    }

    public function setMessage(string $message): void {
        try {
            $this->validateMessage($message);
            $this->setAttribute('message', $message);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting message for contact {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getSentAt(): string {
        return $this->getAttribute('sent_at');
    }

    public function setSentAt(string $sentAt): void {
        try {
            $date = new \DateTime($sentAt);
            $this->setAttribute('sent_at', $date->format('Y-m-d H:i:s'));
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting sent_at for contact {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getStatus(): string {
        return $this->getAttribute('status');
    }

    public function setStatus(string $status): void {
        try {
            if (!in_array($status, ['PENDING', 'RESPONDED', 'CLOSED'])) {
                throw new \InvalidArgumentException("Invalid status: $status");
            }
            $this->setAttribute('status', $status);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting status for contact {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getResponse(): ?string {
        return $this->getAttribute('response');
    }

    public function setResponse(?string $response): void {
        try {
            if ($response !== null) {
                $this->validateMessage($response);
            }
            $this->setAttribute('response', $response);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting response for contact {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function isPending(): bool {
        return $this->getAttribute('status') === 'PENDING';
    }

    public function isResponded(): bool {
        return $this->getAttribute('status') === 'RESPONDED';
    }

    public function isClosed(): bool {
        return $this->getAttribute('status') === 'CLOSED';
    }

    public function respond(string $response): void {
        try {
            $this->setResponse($response);
            $this->setStatus('RESPONDED');
        } catch (\Exception $e) {
            error_log("Error responding to contact {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function close(): void {
        try {
            $this->setStatus('CLOSED');
        } catch (\Exception $e) {
            error_log("Error closing contact {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByVisitor(int $visitorId): array {
        try {
            return self::where('visitor_id', '=', $visitorId)->get();
        } catch (\Exception $e) {
            error_log("Error finding contacts by visitor {$visitorId}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByStatus(string $status): array {
        try {
            if (!in_array($status, ['PENDING', 'RESPONDED', 'CLOSED'])) {
                throw new \InvalidArgumentException("Invalid status: $status");
            }
            return self::where('status', '=', $status)->get();
        } catch (\Exception $e) {
            error_log("Error finding contacts by status {$status}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findPending(): array {
        try {
            return self::where('status', '=', 'PENDING')->get();
        } catch (\Exception $e) {
            error_log("Error finding pending contacts: " . $e->getMessage());
            throw $e;
        }
    }
} 