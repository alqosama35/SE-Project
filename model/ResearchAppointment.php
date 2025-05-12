<?php

namespace App\Models;

class ResearchAppointment extends Model {
    protected static string $table = 'research_appointments';
    
    protected array $fillable = [
        'id',
        'researcher_id',
        'scheduled_time',
        'status',
        'created_at',
        'updated_at'
    ];

    private const VALID_STATUSES = ['SCHEDULED', 'COMPLETED', 'CANCELLED'];

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
    }

    public function getStatus(): string {
        return $this->getAttribute('status');
    }

    public function setStatus(string $status): void {
        if (!in_array($status, self::VALID_STATUSES)) {
            throw new \InvalidArgumentException("Invalid appointment status");
        }
        $this->setAttribute('status', $status);
        $this->save();
    }
} 