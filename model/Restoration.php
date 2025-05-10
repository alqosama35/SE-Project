<?php

namespace App\Models;

class Restoration {
    private string $id;
    private MuseumObject $object;
    private \DateTime $startDate;
    private \DateTime $endDate;
    private string $description;
    private string $status;

    public function begin(): void {
        $this->startDate = new \DateTime();
        $this->status = "IN_PROGRESS";
        $this->object->startRestoration($this);
    }

    public function complete(): void {
        $this->endDate = new \DateTime();
        $this->status = "COMPLETED";
        $this->object->completeRestoration();
    }

    public function updateStatus(string $status): void {
        $this->status = $status;
    }

    public function assignStaff(string $staffName): void {
        // Implementation
    }

    public function getObject(): MuseumObject {
        return $this->object;
    }

    public function getId(): string {
        return $this->id;
    }
} 