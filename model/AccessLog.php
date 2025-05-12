<?php

namespace App\Models;

class AccessLog extends Model {
    protected static string $table = 'access_logs';
    
    protected array $fillable = [
        'id',
        'researcher_id',
        'timestamp',
        'resource_type',
        'status'
    ];

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
    }
} 