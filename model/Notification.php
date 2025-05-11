<?php

namespace App\Models;

class Notification {
    private string $id;
    private string $email;
    private \DateTime $subscribedAt;
    private bool $active;
    private array $preferences;

    private const VALID_PREFERENCES = ['events', 'exhibitions', 'news'];

    public function __construct(array $attributes = []) {
        try {
            $this->id = $attributes['id'] ?? uniqid('notification_');
            $this->email = $attributes['email'] ?? '';
            $this->subscribedAt = $attributes['subscribedAt'] ?? new \DateTime();
            $this->active = $attributes['active'] ?? true;
            $this->preferences = $attributes['preferences'] ?? [
                'events' => true,
                'exhibitions' => true,
                'news' => true
            ];

            if (!$this->validateEmail($this->email)) {
                throw new \InvalidArgumentException("Invalid email format");
            }
            if (!$this->validatePreferences($this->preferences)) {
                throw new \InvalidArgumentException("Invalid preferences");
            }
        } catch (\Exception $e) {
            error_log("Error creating notification: " . $e->getMessage());
            throw $e;
        }
    }

    private function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validatePreferences(array $preferences): bool {
        foreach ($preferences as $key => $value) {
            if (!in_array($key, self::VALID_PREFERENCES)) {
                return false;
            }
            if (!is_bool($value)) {
                return false;
            }
        }
        return true;
    }

    public function subscribe(): void {
        try {
            $this->active = true;
            $this->subscribedAt = new \DateTime();
            $this->sendConfirmationEmail();
        } catch (\Exception $e) {
            error_log("Error subscribing notification {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function unsubscribe(): void {
        try {
            $this->active = false;
            // Send unsubscribe confirmation email
            $this->sendEmail(
                $this->email,
                'Unsubscribed from Museum Notifications',
                'You have been successfully unsubscribed from our notifications.'
            );
        } catch (\Exception $e) {
            error_log("Error unsubscribing notification {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function sendConfirmationEmail(): void {
        try {
            $this->sendEmail(
                $this->email,
                'Welcome to Museum Notifications',
                'Thank you for subscribing to our notifications. You will receive updates about events, exhibitions, and news.'
            );
        } catch (\Exception $e) {
            error_log("Error sending confirmation email for notification {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function updatePreferences(array $preferences): void {
        try {
            if (!$this->validatePreferences($preferences)) {
                throw new \InvalidArgumentException("Invalid preferences");
            }
            $this->preferences = array_merge($this->preferences, $preferences);
        } catch (\Exception $e) {
            error_log("Error updating preferences for notification {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function isActive(): bool {
        try {
            return $this->active;
        } catch (\Exception $e) {
            error_log("Error checking notification active status {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPreferences(): array {
        return $this->preferences;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getSubscribedAt(): \DateTime {
        return $this->subscribedAt;
    }

    private function sendEmail(string $to, string $subject, string $message): void {
        try {
            if (!$this->validateEmail($to)) {
                throw new \InvalidArgumentException("Invalid email address");
            }
            if (empty(trim($subject))) {
                throw new \InvalidArgumentException("Email subject cannot be empty");
            }
            if (empty(trim($message))) {
                throw new \InvalidArgumentException("Email message cannot be empty");
            }
            // Implementation would typically use a mail service
            // For now, we'll just log the email
            error_log("Email to {$to}: {$subject} - {$message}");
        } catch (\Exception $e) {
            error_log("Error sending email for notification {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }
} 