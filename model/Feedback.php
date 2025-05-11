<?php

namespace App\Models;

class Feedback extends Model {
    protected static string $table = 'feedbacks';
    
    protected array $fillable = [
        'id',
        'visitor_id',
        'rating',
        'comment',
        'created_at',
        'status'
    ];
    
    protected array $relationships = [
        'visitor' => Visitor::class
    ];

    public function getVisitor(): ?Visitor {
        return Visitor::find($this->getAttribute('visitor_id'));
    }

    public function setVisitor(Visitor $visitor): void {
        $this->setAttribute('visitor_id', $visitor->getId());
        $this->save();
    }

    public function getRating(): int {
        return (int)$this->getAttribute('rating');
    }

    public function setRating(int $rating): void {
        if ($rating < 1 || $rating > 5) {
            throw new \InvalidArgumentException('Rating must be between 1 and 5');
        }
        $this->setAttribute('rating', $rating);
        $this->save();
    }

    public function getComment(): string {
        return $this->getAttribute('comment');
    }

    public function setComment(string $comment): void {
        $this->setAttribute('comment', $comment);
        $this->save();
    }

    public function getCreatedAt(): string {
        return $this->getAttribute('created_at');
    }

    public function setCreatedAt(string $createdAt): void {
        $this->setAttribute('created_at', $createdAt);
        $this->save();
    }

    public function getStatus(): string {
        return $this->getAttribute('status');
    }

    public function setStatus(string $status): void {
        $this->setAttribute('status', $status);
        $this->save();
    }

    public function isPending(): bool {
        return $this->getAttribute('status') === 'PENDING';
    }

    public function isApproved(): bool {
        return $this->getAttribute('status') === 'APPROVED';
    }

    public function isRejected(): bool {
        return $this->getAttribute('status') === 'REJECTED';
    }

    public static function findByVisitor(int $visitorId): array {
        return self::where('visitor_id', '=', $visitorId)->get();
    }

    public static function findByStatus(string $status): array {
        return self::where('status', '=', $status)->get();
    }

    public static function findByRating(int $rating): array {
        return self::where('rating', '=', $rating)->get();
    }

    public static function findPending(): array {
        return self::where('status', '=', 'PENDING')->get();
    }
} 