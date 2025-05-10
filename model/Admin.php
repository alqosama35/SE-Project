<?php

namespace App\Models;

class Admin extends Member {
    private string $id;
    private array $managedFeedbacks = [];
    private array $managedContacts = [];
    private array $managedRestorations = [];

    public function manageUserAccounts(): void {
        // Implementation
    }

    public function manageContent(): void {
        // Implementation
    }

    public function approveRejectRegistrations(): void {
        // Implementation
    }

    public function viewSystemReports(): void {
        // Implementation
    }

    public function manageFeedbackAndInquiries(): void {
        // Implementation
    }

    public function manageAccessPermissions(): void {
        // Implementation
    }

    public function addManagedFeedback(Feedback $feedback): void {
        $this->managedFeedbacks[] = $feedback;
    }

    public function addManagedContact(Contact $contact): void {
        $this->managedContacts[] = $contact;
    }

    public function addManagedRestoration(Restoration $restoration): void {
        $this->managedRestorations[] = $restoration;
    }

    public function getManagedFeedbacks(): array {
        return $this->managedFeedbacks;
    }

    public function getManagedContacts(): array {
        return $this->managedContacts;
    }

    public function getManagedRestorations(): array {
        return $this->managedRestorations;
    }
} 