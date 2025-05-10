<?php

namespace App\Models;

class Gallery {
    private string $id;
    private string $name;
    private string $floor;
    private array $displayedObjects;

    public function __construct() {
        $this->displayedObjects = [];
    }

    public function scheduleDisplay(string $museumObjectId, array $dateRange): void {
        $this->displayedObjects[$museumObjectId] = $dateRange;
    }

    public function allocateMuseumObject(string $id): void {
        if (!isset($this->displayedObjects[$id])) {
            $this->displayedObjects[$id] = [
                'startDate' => new \DateTime(),
                'endDate' => null
            ];
        }
    }

    public function removeMuseumObject(string $id): void {
        unset($this->displayedObjects[$id]);
    }

    public function getName(): string {
        return $this->name;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getDisplayedObjects(): array {
        return $this->displayedObjects;
    }
} 