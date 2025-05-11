<?php

namespace App\Models;

class Researcher extends Member {
    protected static string $table = 'researchers';
    
    protected array $fillable = [
        'id',
        'member_id',
        'specialization',
        'qualifications'
    ];
    
    protected array $relationships = [
        'researchProjects' => Research::class
    ];

    private string $id;
    private array $researchProjects = [];

    public function accessResearchResources(): void {
        try {
            if (!$this->isActive()) {
                throw new \RuntimeException("Researcher account is not active");
            }
            // Implementation for accessing research resources
        } catch (\Exception $e) {
            error_log("Error accessing research resources for researcher {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function submitResearchProposals(string $proposal): bool {
        try {
            if (empty(trim($proposal))) {
                throw new \InvalidArgumentException("Research proposal cannot be empty");
            }
            // Implementation for submitting research proposals
            return true;
        } catch (\Exception $e) {
            error_log("Error submitting research proposal for researcher {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function scheduleResearchAppointments(\DateTime $dateTime): bool {
        try {
            if ($dateTime < new \DateTime()) {
                throw new \InvalidArgumentException("Appointment date cannot be in the past");
            }
            // Implementation for scheduling research appointments
            return true;
        } catch (\Exception $e) {
            error_log("Error scheduling research appointment for researcher {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function collaborateWithStaff(string $staffId): void {
        try {
            $staff = Staff::find($staffId);
            if (!$staff) {
                throw new \InvalidArgumentException("Staff member not found");
            }
            // Implementation for staff collaboration
        } catch (\Exception $e) {
            error_log("Error collaborating with staff for researcher {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function requestRestrictedAccess(string $resourceId): bool {
        try {
            if (empty(trim($resourceId))) {
                throw new \InvalidArgumentException("Resource ID cannot be empty");
            }
            // Implementation for requesting restricted access
            return true;
        } catch (\Exception $e) {
            error_log("Error requesting restricted access for researcher {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function viewResearchHistory(): array {
        try {
            return Research::where('researcher_id', '=', $this->getAttribute('id'))->get();
        } catch (\Exception $e) {
            error_log("Error viewing research history for researcher {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function addResearchProject(Research $research): void {
        try {
            $research->setAttribute('researcher_id', $this->getAttribute('id'));
            $research->save();
        } catch (\Exception $e) {
            error_log("Error adding research project for researcher {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function getResearchProjects(): array {
        try {
            return Research::where('researcher_id', '=', $this->getAttribute('id'))->get();
        } catch (\Exception $e) {
            error_log("Error getting research projects for researcher {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function isActive(): bool {
        try {
            return $this->getAttribute('status') === 'ACTIVE';
        } catch (\Exception $e) {
            error_log("Error checking active status for researcher {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }
} 