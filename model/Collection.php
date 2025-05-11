<?php

namespace App\Models;

class Collection extends Model {
    protected static string $table = 'collections';
    
    protected array $fillable = [
        'id',
        'title',
        'description',
        'curator_id'
    ];
    
    protected array $relationships = [
        'curator' => Staff::class,
        'museumObjects' => MuseumObject::class
    ];

    public function addMuseumObject(MuseumObject $object): void {
        try {
            if ($object->getAttribute('collection_id')) {
                throw new \RuntimeException("Museum object is already assigned to a collection");
            }
            $object->assignToCollection($this);
        } catch (\Exception $e) {
            error_log("Error adding museum object to collection {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function removeMuseumObject(MuseumObject $object): void {
        try {
            if ($object->getAttribute('collection_id') !== $this->getAttribute('id')) {
                throw new \RuntimeException("Museum object does not belong to this collection");
            }
            $object->setAttribute('collection_id', null);
            $object->save();
        } catch (\Exception $e) {
            error_log("Error removing museum object from collection {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getMuseumObjects(): array {
        try {
            return MuseumObject::where('collection_id', '=', $this->getAttribute('id'))->get();
        } catch (\Exception $e) {
            error_log("Error getting museum objects for collection {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getCurator(): ?Staff {
        try {
            return Staff::find($this->getAttribute('curator_id'));
        } catch (\Exception $e) {
            error_log("Error getting curator for collection {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function setCurator(Staff $curator): void {
        try {
            if (!$curator->hasRole('CURATOR')) {
                throw new \InvalidArgumentException("Staff member must have curator role");
            }
            $this->setAttribute('curator_id', $curator->getAttribute('id'));
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting curator for collection {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getTitle(): string {
        return $this->getAttribute('title');
    }

    public function setTitle(string $title): void {
        try {
            if (empty(trim($title))) {
                throw new \InvalidArgumentException("Title cannot be empty");
            }
            $this->setAttribute('title', $title);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting title for collection {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getDescription(): string {
        return $this->getAttribute('description');
    }

    public function setDescription(string $description): void {
        try {
            $this->setAttribute('description', $description);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting description for collection {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByCurator(string $curatorId): array {
        try {
            return self::where('curator_id', '=', $curatorId)->get();
        } catch (\Exception $e) {
            error_log("Error finding collections by curator {$curatorId}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function searchByTitle(string $title): array {
        try {
            if (empty(trim($title))) {
                throw new \InvalidArgumentException("Search title cannot be empty");
            }
            return self::where('title', 'LIKE', "%$title%")->get();
        } catch (\Exception $e) {
            error_log("Error searching collections by title {$title}: " . $e->getMessage());
            throw $e;
        }
    }
} 