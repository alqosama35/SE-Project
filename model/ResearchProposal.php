<?php

namespace App\Models;

class ResearchProposal extends Model {
    protected static string $table = 'research_proposals';
    
    protected array $fillable = [
        'id',
        'researcher_id',
        'content',
        'status',
        'submitted_at',
        'reviewed_at'
    ];

    private const VALID_STATUSES = ['PENDING', 'APPROVED', 'REJECTED'];

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
    }

    public function getStatus(): string {
        return $this->getAttribute('status');
    }

    public function setStatus(string $status): void {
        if (!in_array($status, self::VALID_STATUSES)) {
            throw new \InvalidArgumentException("Invalid proposal status");
        }
        $this->setAttribute('status', $status);
        $this->save();
    }
} 