<?php

namespace App\Models;

class MuseumObject {
    private string $id;
    private string $name;
    private string $description;
    private string $origin;
    private string $period;
    private string $material;
    private string $imageUrl;
    private MuseumObjectStatus $status;
    private ?Collection $collection = null;
    private ?Gallery $gallery = null;
    private ?Exhibition $exhibition = null;
    private ?Loan $currentLoan = null;
    private ?Restoration $currentRestoration = null;

    public function viewDetails(): MuseumObject {
        // Implementation
        return $this;
    }

    public function requestLoan(string $userId): Loan {
        if ($this->status !== MuseumObjectStatus::AVAILABLE) {
            throw new \Exception("Object is not available for loan");
        }
        $loan = new Loan();
        $this->status = MuseumObjectStatus::ON_LOAN;
        $this->currentLoan = $loan;
        return $loan;
    }

    public function updateMetadata(array $details): void {
        // Implementation
    }

    public function uploadImage(string $imageFile): void {
        // Implementation
    }

    public function assignToCollection(Collection $collection): void {
        $this->collection = $collection;
        $collection->addMuseumObject($this);
    }

    public function assignToGallery(Gallery $gallery): void {
        $this->gallery = $gallery;
        $gallery->allocateMuseumObject($this->id);
    }

    public function assignToExhibition(Exhibition $exhibition): void {
        $this->exhibition = $exhibition;
        $exhibition->addMuseumObject($this->id);
    }

    public function startRestoration(Restoration $restoration): void {
        if ($this->status !== MuseumObjectStatus::AVAILABLE) {
            throw new \Exception("Object is not available for restoration");
        }
        $this->status = MuseumObjectStatus::UNDER_RESTORATION;
        $this->currentRestoration = $restoration;
    }

    public function completeRestoration(): void {
        if ($this->status !== MuseumObjectStatus::UNDER_RESTORATION) {
            throw new \Exception("Object is not under restoration");
        }
        $this->status = MuseumObjectStatus::AVAILABLE;
        $this->currentRestoration = null;
    }

    public function getCurrentLocation(): string {
        if ($this->currentLoan) {
            return "On loan";
        }
        if ($this->currentRestoration) {
            return "Under restoration";
        }
        if ($this->gallery) {
            return "In gallery: " . $this->gallery->getName();
        }
        if ($this->exhibition) {
            return "In exhibition: " . $this->exhibition->getTitle();
        }
        if ($this->collection) {
            return "In collection: " . $this->collection->getTitle();
        }
        return "In storage";
    }

    public function getStatus(): MuseumObjectStatus {
        return $this->status;
    }

    public function setStatus(MuseumObjectStatus $status): void {
        $this->status = $status;
    }
} 