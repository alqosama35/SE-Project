<?php

namespace App\Models;

class Exhibition {
    private string $id;
    private string $title;
    private string $type;
    private \DateTime $startDate;
    private \DateTime $endDate;
    private string $description;
    private array $exhibitedObjects;

    public function __construct() {
        $this->exhibitedObjects = [];
    }

    public function startExhibition(): void {
        // Implementation
    }

    public function endExhibition(): void {
        // Implementation
    }

    public function addMuseumObject(string $id): void {
        if (!in_array($id, $this->exhibitedObjects)) {
            $this->exhibitedObjects[] = $id;
        }
    }

    public function removeMuseumObject(string $id): void {
        $key = array_search($id, $this->exhibitedObjects);
        if ($key !== false) {
            unset($this->exhibitedObjects[$key]);
        }
    }

    public function getDuration(): int {
        return $this->endDate->diff($this->startDate)->days;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getExhibitedObjects(): array {
        return $this->exhibitedObjects;
    }
} 