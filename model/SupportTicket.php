<?php

namespace App\Models;

class SupportTicket extends Model {
    protected static string $table = 'support_tickets';
    
    protected array $fillable = [
        'id',
        'visitor_id',
        'subject',
        'message',
        'priority',
        'status',
        'created_at',
        'updated_at'
    ];

    private const VALID_PRIORITIES = ['LOW', 'MEDIUM', 'HIGH'];
    private const VALID_STATUSES = ['OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED'];

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
    }

    public function getStatus(): string {
        return $this->getAttribute('status');
    }

    public function setStatus(string $status): void {
        if (!in_array($status, self::VALID_STATUSES)) {
            throw new \InvalidArgumentException("Invalid ticket status");
        }
        $this->setAttribute('status', $status);
        $this->save();
    }

    public function getPriority(): string {
        return $this->getAttribute('priority');
    }

    public function setPriority(string $priority): void {
        if (!in_array($priority, self::VALID_PRIORITIES)) {
            throw new \InvalidArgumentException("Invalid ticket priority");
        }
        $this->setAttribute('priority', $priority);
        $this->save();
    }
} 