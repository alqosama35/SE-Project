<?php

namespace App\Models;

class Collection {
    private string $id;
    private string $title;
    private string $description;
    private array $museumObjects;

    public function __construct() {
        $this->museumObjects = [];
    }

    public function addMuseumObject(MuseumObject $museumObject): void {
        if (!in_array($museumObject, $this->museumObjects)) {
            $this->museumObjects[] = $museumObject;
            $museumObject->assignToCollection($this);
        }
    }

    public function removeMuseumObject(string $id): void {
        foreach ($this->museumObjects as $key => $object) {
            if ($object->getId() === $id) {
                unset($this->museumObjects[$key]);
                break;
            }
        }
    }

    public function listMuseumObjects(): array {
        return $this->museumObjects;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getId(): string {
        return $this->id;
    }
} 