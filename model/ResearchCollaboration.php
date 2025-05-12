<?php

namespace App\Models;

class ResearchCollaboration extends Model {
    protected static string $table = 'research_collaborations';
    
    protected array $fillable = [
        'id',
        'researcher_id',
        'staff_id',
        'status',
        'started_at',
        'ended_at'
    ];

    private const VALID_STATUSES = ['ACTIVE', 'COMPLETED', 'CANCELLED'];

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
    }

    public function getStatus(): string {
        return $this->getAttribute('status');
    }

    public function setStatus(string $status): void {
        if (!in_array($status, self::VALID_STATUSES)) {
            throw new \InvalidArgumentException("Invalid collaboration status");
        }
        $this->setAttribute('status', $status);
        $this->save();
    }
} 