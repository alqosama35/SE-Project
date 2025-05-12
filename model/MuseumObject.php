<?php

namespace App\Models;

class MuseumObject extends Model {
    protected static string $table = 'museum_objects';
    
    protected array $fillable = [
        'id',
        'name',
        'description',
        'origin',
        'period',
        'material',
        'image_url',
        'status',
        'collection_id',
        'gallery_id',
        'exhibition_id',
        'restoration_id'
    ];
    
    protected array $relationships = [
        'collection' => Collection::class,
        'gallery' => Gallery::class,
        'exhibition' => Exhibition::class,
        'restoration' => Restoration::class,
        'loan' => Loan::class
    ];

    private const VALID_STATUSES = [
        'AVAILABLE',
        'ON_LOAN',
        'UNDER_RESTORATION',
        'ON_EXHIBITION'
    ];

    private const VALID_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];

    public function getName(): string {
        return $this->getAttribute('name');
    }

    public function setName(string $name): void {
        try {
            if (empty(trim($name))) {
                throw new \InvalidArgumentException("Object name cannot be empty");
            }
            $this->setAttribute('name', $name);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting name for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getDescription(): string {
        return $this->getAttribute('description');
    }

    public function setDescription(string $description): void {
        try {
            if (empty(trim($description))) {
                throw new \InvalidArgumentException("Object description cannot be empty");
            }
            $this->setAttribute('description', $description);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting description for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getOrigin(): string {
        return $this->getAttribute('origin');
    }

    public function setOrigin(string $origin): void {
        try {
            if (empty(trim($origin))) {
                throw new \InvalidArgumentException("Object origin cannot be empty");
            }
            $this->setAttribute('origin', $origin);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting origin for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getPeriod(): string {
        return $this->getAttribute('period');
    }

    public function setPeriod(string $period): void {
        try {
            if (empty(trim($period))) {
                throw new \InvalidArgumentException("Object period cannot be empty");
            }
            $this->setAttribute('period', $period);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting period for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getMaterial(): string {
        return $this->getAttribute('material');
    }

    public function setMaterial(string $material): void {
        try {
            if (empty(trim($material))) {
                throw new \InvalidArgumentException("Object material cannot be empty");
            }
            $this->setAttribute('material', $material);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting material for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getImageUrl(): ?string {
        return $this->getAttribute('image_url');
    }

    public function setImageUrl(?string $imageUrl): void {
        try {
            if ($imageUrl !== null) {
                $extension = strtolower(pathinfo($imageUrl, PATHINFO_EXTENSION));
                if (!in_array($extension, self::VALID_IMAGE_EXTENSIONS)) {
                    throw new \InvalidArgumentException("Invalid image format");
                }
            }
            $this->setAttribute('image_url', $imageUrl);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting image URL for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getStatus(): string {
        return $this->getAttribute('status');
    }

    public function setStatus(string $status): void {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid object status");
            }
            $this->setAttribute('status', $status);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting status for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getCollection(): ?Collection {
        try {
            return Collection::find($this->getAttribute('collection_id'));
        } catch (\Exception $e) {
            error_log("Error getting collection for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function setCollection(?Collection $collection): void {
        try {
            $this->setAttribute('collection_id', $collection ? $collection->getId() : null);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting collection for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getGallery(): ?Gallery {
        try {
            return Gallery::find($this->getAttribute('gallery_id'));
        } catch (\Exception $e) {
            error_log("Error getting gallery for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function setGallery(?Gallery $gallery): void {
        try {
            $this->setAttribute('gallery_id', $gallery ? $gallery->getId() : null);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting gallery for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getExhibition(): ?Exhibition {
        try {
            return Exhibition::find($this->getAttribute('exhibition_id'));
        } catch (\Exception $e) {
            error_log("Error getting exhibition for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function setExhibition(?Exhibition $exhibition): void {
        try {
            $this->setAttribute('exhibition_id', $exhibition ? $exhibition->getId() : null);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting exhibition for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getRestoration(): ?Restoration {
        try {
            return Restoration::find($this->getAttribute('restoration_id'));
        } catch (\Exception $e) {
            error_log("Error getting restoration for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function setRestoration(?Restoration $restoration): void {
        try {
            $this->setAttribute('restoration_id', $restoration ? $restoration->getId() : null);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error setting restoration for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getLoan(): ?Loan {
        try {
            return Loan::where('museum_object_id', '=', $this->getId())
                ->where('status', '=', 'ACTIVE')
                ->first();
        } catch (\Exception $e) {
            error_log("Error getting loan for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function isAvailable(): bool {
        try {
            return $this->getAttribute('status') === 'AVAILABLE';
        } catch (\Exception $e) {
            error_log("Error checking availability for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function isOnLoan(): bool {
        try {
            return $this->getAttribute('status') === 'ON_LOAN';
        } catch (\Exception $e) {
            error_log("Error checking loan status for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function isUnderRestoration(): bool {
        try {
            return $this->getAttribute('status') === 'UNDER_RESTORATION';
        } catch (\Exception $e) {
            error_log("Error checking restoration status for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function isOnExhibition(): bool {
        try {
            return $this->getAttribute('status') === 'ON_EXHIBITION';
        } catch (\Exception $e) {
            error_log("Error checking exhibition status for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByStatus(string $status): array {
        try {
            if (!in_array($status, self::VALID_STATUSES)) {
                throw new \InvalidArgumentException("Invalid object status");
            }
            return self::where('status', '=', $status)->get();
        } catch (\Exception $e) {
            error_log("Error finding museum objects by status {$status}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByCollection(int $collectionId): array {
        try {
            return self::where('collection_id', '=', $collectionId)->get();
        } catch (\Exception $e) {
            error_log("Error finding museum objects by collection {$collectionId}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByGallery(int $galleryId): array {
        try {
            return self::where('gallery_id', '=', $galleryId)->get();
        } catch (\Exception $e) {
            error_log("Error finding museum objects by gallery {$galleryId}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function findByExhibition(int $exhibitionId): array {
        try {
            return self::where('exhibition_id', '=', $exhibitionId)->get();
        } catch (\Exception $e) {
            error_log("Error finding museum objects by exhibition {$exhibitionId}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function searchByName(string $name): array {
        try {
            if (empty(trim($name))) {
                throw new \InvalidArgumentException("Search name cannot be empty");
            }
            return self::where('name', 'LIKE', "%$name%")->get();
        } catch (\Exception $e) {
            error_log("Error searching museum objects by name {$name}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function searchByPeriod(string $period): array {
        try {
            if (empty(trim($period))) {
                throw new \InvalidArgumentException("Search period cannot be empty");
            }
            return self::where('period', 'LIKE', "%$period%")->get();
        } catch (\Exception $e) {
            error_log("Error searching museum objects by period {$period}: " . $e->getMessage());
            throw $e;
        }
    }

    public static function searchByMaterial(string $material): array {
        try {
            if (empty(trim($material))) {
                throw new \InvalidArgumentException("Search material cannot be empty");
            }
            return self::where('material', 'LIKE', "%$material%")->get();
        } catch (\Exception $e) {
            error_log("Error searching museum objects by material {$material}: " . $e->getMessage());
            throw $e;
        }
    }

    public function viewDetails(): MuseumObject {
        return $this;
    }

    public function requestLoan(string $userId): Loan {
        try {
            if ($this->getAttribute('status') !== 'AVAILABLE') {
                throw new \RuntimeException("Object is not available for loan");
            }
            
            $loan = new Loan([
                'museum_object_id' => $this->getAttribute('id'),
                'user_id' => $userId,
                'status' => 'PENDING'
            ]);
            
            $loan->save();
            
            $this->setAttribute('status', 'ON_LOAN');
            $this->setAttribute('current_loan_id', $loan->getAttribute('id'));
            $this->save();
            
            return $loan;
        } catch (\Exception $e) {
            error_log("Error requesting loan for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateMetadata(array $details): void {
        try {
            foreach ($details as $key => $value) {
                if (!in_array($key, $this->fillable)) {
                    throw new \InvalidArgumentException("Invalid metadata field: {$key}");
                }
                $this->setAttribute($key, $value);
            }
            $this->save();
        } catch (\Exception $e) {
            error_log("Error updating metadata for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function uploadImage(string $imageFile): void {
        try {
            $extension = strtolower(pathinfo($imageFile, PATHINFO_EXTENSION));
            if (!in_array($extension, self::VALID_IMAGE_EXTENSIONS)) {
                throw new \InvalidArgumentException("Invalid image format");
            }

            // Create uploads directory if it doesn't exist
            $uploadDir = __DIR__ . '/../../public/uploads/museum_objects/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Generate unique filename
            $filename = uniqid('obj_') . '.' . $extension;
            $targetPath = $uploadDir . $filename;

            // Move uploaded file
            if (!move_uploaded_file($imageFile, $targetPath)) {
                throw new \RuntimeException("Failed to move uploaded file");
            }

            // Set relative path for database storage
            $relativePath = '/uploads/museum_objects/' . $filename;
            $this->setAttribute('image_url', $relativePath);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error uploading image for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function assignToCollection(Collection $collection): void {
        try {
            $this->setAttribute('collection_id', $collection->getAttribute('id'));
            $this->save();
            
            $collection->addMuseumObject($this);
        } catch (\Exception $e) {
            error_log("Error assigning museum object {$this->getAttribute('id')} to collection: " . $e->getMessage());
            throw $e;
        }
    }

    public function assignToGallery(Gallery $gallery): void {
        try {
            if (!$gallery->hasAvailableSpace()) {
                throw new \RuntimeException("Gallery is at full capacity");
            }
            $this->setAttribute('gallery_id', $gallery->getAttribute('id'));
            $this->save();
            
            $gallery->allocateMuseumObject($this->getAttribute('id'));
        } catch (\Exception $e) {
            error_log("Error assigning museum object {$this->getAttribute('id')} to gallery: " . $e->getMessage());
            throw $e;
        }
    }

    public function assignToExhibition(Exhibition $exhibition): void {
        try {
            $this->setAttribute('exhibition_id', $exhibition->getId());
            $this->save();
        } catch (\Exception $e) {
            error_log("Error assigning object {$this->getId()} to exhibition: " . $e->getMessage());
            throw $e;
        }
    }

    public function startRestoration(Restoration $restoration): void {
        try {
            if ($this->getAttribute('status') !== 'AVAILABLE') {
                throw new \RuntimeException("Object is not available for restoration");
            }
            
            $this->setAttribute('status', 'UNDER_RESTORATION');
            $this->setAttribute('restoration_id', $restoration->getAttribute('id'));
            $this->save();
        } catch (\Exception $e) {
            error_log("Error starting restoration for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function completeRestoration(): void {
        try {
            if ($this->getAttribute('status') !== 'UNDER_RESTORATION') {
                throw new \RuntimeException("Object is not under restoration");
            }
            
            $this->setAttribute('status', 'AVAILABLE');
            $this->setAttribute('restoration_id', null);
            $this->save();
        } catch (\Exception $e) {
            error_log("Error completing restoration for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getCurrentLocation(): string {
        try {
            if ($this->getAttribute('current_loan_id')) {
                return "On loan";
            }
            if ($this->getAttribute('restoration_id')) {
                return "Under restoration";
            }
            if ($this->getAttribute('gallery_id')) {
                $gallery = Gallery::find($this->getAttribute('gallery_id'));
                return "In gallery: " . $gallery->getAttribute('name');
            }
            if ($this->getAttribute('exhibition_id')) {
                $exhibition = Exhibition::find($this->getAttribute('exhibition_id'));
                return "In exhibition: " . $exhibition->getAttribute('title');
            }
            if ($this->getAttribute('collection_id')) {
                $collection = Collection::find($this->getAttribute('collection_id'));
                return "In collection: " . $collection->getAttribute('title');
            }
            return "In storage";
        } catch (\Exception $e) {
            error_log("Error getting current location for museum object {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }
} 