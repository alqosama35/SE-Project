<?php

namespace App\Models;

class Gallery extends Model {
    protected static string $table = 'galleries';
    
    protected array $fillable = [
        'id',
        'name',
        'description',
        'location',
        'capacity'
    ];
    
    protected array $relationships = [
        'museumObjects' => MuseumObject::class,
        'exhibitions' => Exhibition::class
    ];

    public function allocateMuseumObject(string $objectId): void {
        try {
            $object = MuseumObject::find($objectId);
            if (!$object) {
                throw new \InvalidArgumentException("Museum object not found");
            }
            if (!$this->hasAvailableSpace()) {
                throw new \RuntimeException("Gallery is at full capacity");
            }
            $object->setAttribute('gallery_id', $this->getId());
            $object->save();
        } catch (\Exception $e) {
            error_log("Error allocating museum object {$objectId} to gallery {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function removeMuseumObject(string $objectId): void {
        try {
            $object = MuseumObject::find($objectId);
            if (!$object) {
                throw new \InvalidArgumentException("Museum object not found");
            }
            if ($object->getAttribute('gallery_id') !== $this->getAttribute('id')) {
                throw new \RuntimeException("Museum object is not in this gallery");
            }
            $object->setAttribute('gallery_id', null);
            $object->save();
        } catch (\Exception $e) {
            error_log("Error removing museum object {$objectId} from gallery {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getMuseumObjects(): array {
        try {
            return MuseumObject::where('gallery_id', '=', $this->getAttribute('id'))->get();
        } catch (\Exception $e) {
            error_log("Error getting museum objects for gallery {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getExhibitions(): array {
        try {
            return Exhibition::where('gallery_id', '=', $this->getAttribute('id'))->get();
        } catch (\Exception $e) {
            error_log("Error getting exhibitions for gallery {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getName(): string {
        return $this->getAttribute('name');
    }

    public function setName(string $name): void {
        try {
            if (empty(trim($name))) {
                throw new \InvalidArgumentException("Gallery name cannot be empty");
            }
            $this->setAttribute('name', $name);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting name for gallery {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getLocation(): string {
        return $this->getAttribute('location');
    }

    public function setLocation(string $location): void {
        try {
            if (empty(trim($location))) {
                throw new \InvalidArgumentException("Gallery location cannot be empty");
            }
            $this->setAttribute('location', $location);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting location for gallery {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getCapacity(): int {
        return (int)$this->getAttribute('capacity');
    }

    public function setCapacity(int $capacity): void {
        try {
            if ($capacity < 0) {
                throw new \InvalidArgumentException("Gallery capacity cannot be negative");
            }
            if ($capacity < $this->getCurrentOccupancy()) {
                throw new \RuntimeException("New capacity cannot be less than current occupancy");
            }
            $this->setAttribute('capacity', $capacity);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting capacity for gallery {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getCurrentOccupancy(): int {
        try {
            return count($this->getMuseumObjects());
        } catch (\Exception $e) {
            error_log("Error getting current occupancy for gallery {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function hasAvailableSpace(): bool {
        try {
            return $this->getCurrentOccupancy() < $this->getCapacity();
        } catch (\Exception $e) {
            error_log("Error checking available space for gallery {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByLocation(string $location): array {
        try {
            if (empty(trim($location))) {
                throw new \InvalidArgumentException("Location cannot be empty");
            }
            return self::where('location', '=', $location)->get();
        } catch (\Exception $e) {
            error_log("Error finding galleries by location {$location}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findAvailableGalleries(): array {
        try {
            return self::where('capacity', '>', 0)->get();
        } catch (\Exception $e) {
            error_log("Error finding available galleries: " . $e->getMessage());
            throw $e;
        }
    }
} 