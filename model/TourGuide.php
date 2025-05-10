<?php

namespace App\Models;

class TourGuide extends Member {
    private string $id;
    private array $tours = [];
    private array $feedbacks = [];

    public function manageGuideProfile(): void {
        // Implementation
    }

    public function viewFeedbackAndRatings(): array {
        return $this->feedbacks;
    }

    public function submitTourFeedback(Feedback $feedback): void {
        // Implementation
    }

    public function addTour(Tour $tour): void {
        $this->tours[] = $tour;
    }

    public function addFeedback(Feedback $feedback): void {
        $this->feedbacks[] = $feedback;
    }

    public function getTours(): array {
        return $this->tours;
    }
} 