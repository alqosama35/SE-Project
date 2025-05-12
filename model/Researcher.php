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

            // Check if researcher has required permissions
            if (!$this->hasPermission('access_research_resources')) {
                throw new \RuntimeException("Insufficient permissions to access research resources");
            }

            // Log access attempt
            $accessLog = new AccessLog([
                'researcher_id' => $this->getAttribute('id'),
                'timestamp' => date('Y-m-d H:i:s'),
                'resource_type' => 'research_resources'
            ]);
            $accessLog->save();

            // Update last access time
            $this->setAttribute('last_resource_access', date('Y-m-d H:i:s'));
            $this->save();

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

            $researchProposal = new \App\Models\ResearchProposal([
                'researcher_id' => $this->getAttribute('id'),
                'content' => $proposal,
                'status' => 'PENDING',
                'submitted_at' => date('Y-m-d H:i:s')
            ]);
            $researchProposal->save();

            // Notify staff about new proposal
            $notification = new \App\Models\Notification([
                'type' => 'NEW_RESEARCH_PROPOSAL',
                'message' => "New research proposal submitted",
                'priority' => 'HIGH',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $notification->save();

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

            $appointment = new \App\Models\ResearchAppointment([
                'researcher_id' => $this->getAttribute('id'),
                'scheduled_time' => $dateTime->format('Y-m-d H:i:s'),
                'status' => 'SCHEDULED',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $appointment->save();

            // Notify staff about new appointment
            $notification = new \App\Models\Notification([
                'type' => 'NEW_RESEARCH_APPOINTMENT',
                'message' => "New research appointment scheduled for " . $dateTime->format('Y-m-d H:i:s'),
                'priority' => 'MEDIUM',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $notification->save();

            return true;
        } catch (\Exception $e) {
            error_log("Error scheduling research appointment for researcher {$this->getAttribute('id')}: " . $e->getMessage());
            throw $e;
        }
    }

    public function collaborateWithStaff(string $staffId): void {
        try {
            $staff = \App\Models\Staff::find($staffId);
            if (!$staff) {
                throw new \InvalidArgumentException("Staff member not found");
            }

            $collaboration = new \App\Models\ResearchCollaboration([
                'researcher_id' => $this->getAttribute('id'),
                'staff_id' => $staffId,
                'status' => 'ACTIVE',
                'started_at' => date('Y-m-d H:i:s')
            ]);
            $collaboration->save();

            // Notify staff member about collaboration
            $notification = new \App\Models\Notification([
                'type' => 'NEW_RESEARCH_COLLABORATION',
                'message' => "New research collaboration request from researcher",
                'priority' => 'MEDIUM',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            $notification->save();

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

    private function hasPermission(string $permission): bool {
        try {
            // Get researcher's role and permissions from database
            $role = $this->getAttribute('role');
            $permissions = $this->getAttribute('permissions') ?? [];
            
            // Check if researcher has the required permission
            return in_array($permission, $permissions);
        } catch (\Exception $e) {
            error_log("Error checking permission for researcher {$this->getAttribute('id')}: " . $e->getMessage());
            return false;
        }
    }
} 