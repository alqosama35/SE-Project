<?php

namespace App\Models;

class TourGuide extends Staff {
    protected static string $table = 'tour_guides';
    
    protected array $fillable = [
        'id',
        'staff_id',
        'specialization',
        'languages',
        'rating',
        'total_tours',
        'status'
    ];
    
    protected array $relationships = [
        'tours' => Tour::class
    ];

    private const VALID_SPECIALIZATIONS = [
        'ART',
        'HISTORY',
        'ARCHAEOLOGY',
        'NATURAL_SCIENCE',
        'CULTURAL_HERITAGE'
    ];

    private const VALID_LANGUAGES = [
        'ENGLISH',
        'SPANISH',
        'FRENCH',
        'GERMAN',
        'ITALIAN',
        'CHINESE',
        'JAPANESE',
        'RUSSIAN',
        'ARABIC'
    ];

    private const VALID_STATUSES = ['AVAILABLE', 'ON_TOUR', 'OFF_DUTY'];

    public function getSpecialization(): string {
        return $this->getAttribute('specialization');
    }

    public function setSpecialization(string $specialization): void {
        try {
            if (!in_array($specialization, self::VALID_SPECIALIZATIONS)) {
                throw new \InvalidArgumentException("Invalid tour guide specialization");
            }
            $this->setAttribute('specialization', $specialization);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting specialization for tour guide {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getLanguages(): array {
        $languages = $this->getAttribute('languages');
        return is_string($languages) ? json_decode($languages, true) : $languages;
    }

    public function setLanguages(array $languages): void {
        try {
            foreach ($languages as $language) {
                if (!in_array($language, self::VALID_LANGUAGES)) {
                    throw new \InvalidArgumentException("Invalid language: {$language}");
                }
            }
            $this->setAttribute('languages', json_encode($languages));
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting languages for tour guide {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function addLanguage(string $language): void {
        try {
            if (!in_array($language, self::VALID_LANGUAGES)) {
                throw new \InvalidArgumentException("Invalid language: {$language}");
            }
            $languages = $this->getLanguages();
            if (!in_array($language, $languages)) {
                $languages[] = $language;
                $this->setLanguages($languages);
            }
        } catch (\Exception $e) {
            error_log("Error adding language for tour guide {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function removeLanguage(string $language): void {
        try {
            $languages = $this->getLanguages();
            $key = array_search($language, $languages);
            if ($key !== false) {
                unset($languages[$key]);
                $this->setLanguages(array_values($languages));
            }
        } catch (\Exception $e) {
            error_log("Error removing language for tour guide {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getRating(): float {
        return (float)$this->getAttribute('rating');
    }

    public function setRating(float $rating): void {
        try {
            if ($rating < 0 || $rating > 5) {
                throw new \InvalidArgumentException("Rating must be between 0 and 5");
            }
            $this->setAttribute('rating', $rating);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting rating for tour guide {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getTotalTours(): int {
        return (int)$this->getAttribute('total_tours');
    }

    public function setTotalTours(int $totalTours): void {
        try {
            if ($totalTours < 0) {
                throw new \InvalidArgumentException("Total tours cannot be negative");
            }
            $this->setAttribute('total_tours', $totalTours);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting total tours for tour guide {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getStatus(): string {
        return $this->getAttribute('status');
    }

    public function setStatus(string $status): void {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid tour guide status");
            }
            $this->setAttribute('status', $status);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting status for tour guide {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getTours(): array {
        try {
            return Tour::where('guide_id', '=', $this->getAttribute('id'))->get();
        } catch (\Exception $e) {
            error_log("Error getting tours for tour guide {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function isAvailable(): bool {
        return $this->getStatus() === 'AVAILABLE';
    }

    public function isOnTour(): bool {
        return $this->getStatus() === 'ON_TOUR';
    }

    public function isOffDuty(): bool {
        return $this->getStatus() === 'OFF_DUTY';
    }

    public function incrementTotalTours(): void {
        try {
            $this->setTotalTours($this->getTotalTours() + 1);
        } catch (\Exception $e) {
            error_log("Error incrementing total tours for tour guide {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateRating(float $newRating): void {
        try {
            $currentRating = $this->getRating();
            $totalTours = $this->getTotalTours();
            $newAverageRating = (($currentRating * $totalTours) + $newRating) / ($totalTours + 1);
            $this->setRating($newAverageRating);
        } catch (\Exception $e) {
            error_log("Error updating rating for tour guide {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findBySpecialization(string $specialization): array {
        try {
            if (!in_array($specialization, self::VALID_SPECIALIZATIONS)) {
                throw new \InvalidArgumentException("Invalid tour guide specialization");
            }
            return self::where('specialization', '=', $specialization)->get();
        } catch (\Exception $e) {
            error_log("Error finding tour guides by specialization {$specialization}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByLanguage(string $language): array {
        try {
            if (!in_array($language, self::VALID_LANGUAGES)) {
                throw new \InvalidArgumentException("Invalid language: {$language}");
            }
            return self::where('languages', 'LIKE', "%{$language}%")->get();
        } catch (\Exception $e) {
            error_log("Error finding tour guides by language {$language}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByStatus(string $status): array {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid tour guide status");
            }
            return self::where('status', '=', $status)->get();
        } catch (\Exception $e) {
            error_log("Error finding tour guides by status {$status}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findAvailable(): array {
        try {
            return self::where('status', '=', 'AVAILABLE')->get();
        } catch (\Exception $e) {
            error_log("Error finding available tour guides: " . $e->getMessage());
            throw $e;
        }
    }
} 