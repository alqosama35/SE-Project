<?php

namespace App\Models;

class Exhibition extends Model {
    protected static string $table = 'exhibitions';
    
    protected array $fillable = [
        'id',
        'title',
        'description',
        'start_date',
        'end_date',
        'gallery_id',
        'curator_id'
    ];
    
    protected array $relationships = [
        'gallery' => Gallery::class,
        'curator' => Staff::class,
        'museumObjects' => MuseumObject::class
    ];

    public function addMuseumObject(string $objectId): void {
        try {
            $object = MuseumObject::find($objectId);
            if (!$object) {
                throw new \InvalidArgumentException("Museum object not found");
            }
            $object->setAttribute('exhibition_id', $this->getId());
            $object->save();
        } catch (\Exception $e) {
            error_log("Error adding museum object {$objectId} to exhibition {$this->getId()}: " . $e->getMessage());
            throw $e;
        }
    }

    public function removeMuseumObject(string $objectId): void {
        $object = MuseumObject::find($objectId);
        if ($object && $object->getAttribute('exhibition_id') === $this->getAttribute('id')) {
            $object->setAttribute('exhibition_id', null);
            $object->save();
        }
    }

    public function getMuseumObjects(): array {
        return MuseumObject::where('exhibition_id', '=', $this->getAttribute('id'))->get();
    }

    public function getGallery(): ?Gallery {
        return Gallery::find($this->getAttribute('gallery_id'));
    }

    public function setGallery(Gallery $gallery): void {
        $this->setAttribute('gallery_id', $gallery->getAttribute('id'));
        $this->save();
    }

    public function getCurator(): ?Staff {
        return Staff::find($this->getAttribute('curator_id'));
    }

    public function setCurator(Staff $curator): void {
        $this->setAttribute('curator_id', $curator->getAttribute('id'));
        $this->save();
    }

    public function getTitle(): string {
        return $this->getAttribute('title');
    }

    public function setTitle(string $title): void {
        $this->setAttribute('title', $title);
        $this->save();
    }

    public function getStartDate(): ?\DateTime {
        $date = $this->getAttribute('start_date');
        return $date ? new \DateTime($date) : null;
    }

    public function setStartDate(?\DateTime $date): void {
        $this->setAttribute('start_date', $date ? $date->format('Y-m-d') : null);
        $this->save();
    }

    public function getEndDate(): ?\DateTime {
        $date = $this->getAttribute('end_date');
        return $date ? new \DateTime($date) : null;
    }

    public function setEndDate(?\DateTime $date): void {
        $this->setAttribute('end_date', $date ? $date->format('Y-m-d') : null);
        $this->save();
    }

    public function isActive(): bool {
        $now = new \DateTime();
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();
        
        if (!$startDate) {
            return false;
        }
        
        if ($now < $startDate) {
            return false;
        }
        
        if ($endDate && $now > $endDate) {
            return false;
        }
        
        return true;
    }

    public static function findActive(): array {
        $now = (new \DateTime())->format('Y-m-d');
        return self::where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->orWhereNull('end_date')
            ->get();
    }

    public static function findByCurator(string $curatorId): array {
        return self::where('curator_id', '=', $curatorId)->get();
    }

    public static function findByGallery(string $galleryId): array {
        return self::where('gallery_id', '=', $galleryId)->get();
    }
} 