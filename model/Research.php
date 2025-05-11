<?php

namespace App\Models;

class Research extends Model {
    protected static string $table = 'research';
    
    protected array $fillable = [
        'id',
        'title',
        'description',
        'start_date',
        'end_date',
        'researcher_id',
        'status'
    ];
    
    protected array $relationships = [
        'researcher' => Researcher::class,
        'relatedObjects' => MuseumObject::class
    ];

    private const VALID_STATUSES = ['PENDING', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'];

    public function submitProposal(string $proposal): bool {
        try {
            if (empty(trim($proposal))) {
                throw new \InvalidArgumentException("Proposal cannot be empty");
            }
            $this->setAttribute('description', $proposal);
            $this->setAttribute('status', 'SUBMITTED');
            return $this->save();
        } catch (\Exception $e) {
            error_log("Error submitting research proposal {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateDetails(string $title, string $description): void {
        try {
            if (!empty($title) && empty(trim($title))) {
                throw new \InvalidArgumentException("Title cannot be empty");
            }
            if (!empty($description) && empty(trim($description))) {
                throw new \InvalidArgumentException("Description cannot be empty");
            }

            if (!empty($title)) {
                $this->setAttribute('title', $title);
            }
            if (!empty($description)) {
                $this->setAttribute('description', $description);
            }
            $this->save();
        } catch (\Exception $e) {
            error_log("Error updating research details {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function addRelatedObject(string $objectId): void {
        try {
            $object = MuseumObject::find($objectId);
            if (!$object) {
                throw new \InvalidArgumentException("Museum object not found");
            }
            $relatedObjects = $this->getAttribute('related_objects') ?? [];
            if (!in_array($objectId, $relatedObjects)) {
                $relatedObjects[] = $objectId;
                $this->setAttribute('related_objects', $relatedObjects);
                $this->save();
            }
        } catch (\Exception $e) {
            error_log("Error adding related object to research {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function removeRelatedObject(string $objectId): void {
        try {
            $relatedObjects = $this->getAttribute('related_objects') ?? [];
            $this->setAttribute('related_objects', array_filter(
                $relatedObjects,
                fn($id) => $id !== $objectId
            ));
            $this->save();
        } catch (\Exception $e) {
            error_log("Error removing related object from research {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getResearchDuration(): int {
        try {
            $start = new \DateTime($this->getAttribute('start_date'));
            $end = new \DateTime($this->getAttribute('end_date'));
            $interval = $start->diff($end);
            return $interval->days;
        } catch (\Exception $e) {
            error_log("Error calculating research duration {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getTitle(): string {
        return $this->getAttribute('title');
    }

    public function getDescription(): string {
        return $this->getAttribute('description');
    }

    public function getStartDate(): \DateTime {
        return new \DateTime($this->getAttribute('start_date'));
    }

    public function getEndDate(): \DateTime {
        return new \DateTime($this->getAttribute('end_date'));
    }

    public function getResearcher(): ?Researcher {
        try {
            return Researcher::find($this->getAttribute('researcher_id'));
        } catch (\Exception $e) {
            error_log("Error getting researcher for research {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getRelatedObjects(): array {
        try {
            $objectIds = $this->getAttribute('related_objects') ?? [];
            return array_map(fn($id) => MuseumObject::find($id), $objectIds);
        } catch (\Exception $e) {
            error_log("Error getting related objects for research {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getStatus(): string {
        return $this->getAttribute('status');
    }

    public function setStatus(string $status): void {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid research status");
            }
            $this->setAttribute('status', $status);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting status for research {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByStatus(string $status): array {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid research status");
            }
            return self::where('status', '=', $status)->get();
        } catch (\Exception $e) {
            error_log("Error finding research by status {$status}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByResearcher(int $researcherId): array {
        try {
            return self::where('researcher_id', '=', $researcherId)->get();
        } catch (\Exception $e) {
            error_log("Error finding research by researcher {$researcherId}: " . $e->getMessage());
            throw $e;
        }
    }
} 