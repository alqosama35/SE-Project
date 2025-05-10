<?php

namespace App\Models;

class Feedback {
    private string $id;
    private Visitor $visitor;
    private string $targetType;
    private string $targetId;
    private string $content;
    private int $rating;
    private \DateTime $submittedAt;
    private string $status;

    public function submit(): void {
        // Implementation
    }

    public function edit(string $newContent): void {
        // Implementation
    }

    public function approve(): void {
        // Implementation
    }

    public function reject(string $reason): void {
        // Implementation
    }
} 