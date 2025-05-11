<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Admin extends Member {
    private string $id;
    private array $managedFeedbacks = [];
    private array $managedContacts = [];
    private array $managedRestorations = [];

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $this->id = $attributes['id'] ?? uniqid('admin_');
        $this->role = MemberRole::ADMIN;
    }

    public function manageUserAccounts(): void {
        try {
            // Get all users from database
            $users = User::all();
            
            // Process user management actions
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                foreach ($users as $user) {
                    if (isset($_POST['user_' . $user->getId()])) {
                        $action = filter_input(INPUT_POST, 'user_' . $user->getId(), FILTER_SANITIZE_STRING);
                        
                        switch ($action) {
                            case 'activate':
                                $user->setAttribute('status', 'ACTIVE');
                                break;
                            case 'deactivate':
                                $user->setAttribute('status', 'INACTIVE');
                                break;
                            case 'delete':
                                $user->delete();
                                break;
                            default:
                                throw new \InvalidArgumentException("Invalid action: $action");
                        }
                        
                        if ($action !== 'delete') {
                            $user->save();
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            error_log("Error managing user accounts: " . $e->getMessage());
            throw $e;
        }
    }

    public function manageContent(): void {
        try {
            // Get all content items (exhibits, collections, etc.)
            $collections = Collection::all();
            $exhibitions = Exhibition::all();
            $museumObjects = MuseumObject::all();
            
            // Process content management actions
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $contentType = filter_input(INPUT_POST, 'content_type', FILTER_SANITIZE_STRING);
                $contentId = filter_input(INPUT_POST, 'content_id', FILTER_SANITIZE_STRING);
                $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
                
                if (!$contentType || !$contentId || !$action) {
                    throw new \InvalidArgumentException("Missing required parameters");
                }
                
                $content = null;
                switch ($contentType) {
                    case 'collection':
                        $content = Collection::find($contentId);
                        break;
                    case 'exhibition':
                        $content = Exhibition::find($contentId);
                        break;
                    case 'museum_object':
                        $content = MuseumObject::find($contentId);
                        break;
                    default:
                        throw new \InvalidArgumentException("Invalid content type: $contentType");
                }
                
                if ($content) {
                    switch ($action) {
                        case 'update':
                            foreach ($_POST as $key => $value) {
                                if ($key !== 'content_type' && $key !== 'content_id' && $key !== 'action') {
                                    $content->setAttribute($key, filter_var($value, FILTER_SANITIZE_STRING));
                                }
                            }
                            $content->save();
                            break;
                        case 'delete':
                            $content->delete();
                            break;
                        default:
                            throw new \InvalidArgumentException("Invalid action: $action");
                    }
                }
            }
        } catch (\Exception $e) {
            error_log("Error managing content: " . $e->getMessage());
            throw $e;
        }
    }

    public function approveRejectRegistrations(): void {
        try {
            // Get pending registrations
            $pendingRegistrations = User::where('status', '=', 'PENDING')->get();
            
            // Process registration actions
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                foreach ($pendingRegistrations as $registration) {
                    if (isset($_POST['registration_' . $registration->getId()])) {
                        $action = filter_input(INPUT_POST, 'registration_' . $registration->getId(), FILTER_SANITIZE_STRING);
                        
                        switch ($action) {
                            case 'approve':
                                $registration->setAttribute('status', 'ACTIVE');
                                $registration->save();
                                
                                // Create member record if approved
                                $member = new Member([
                                    'id' => uniqid(),
                                    'user_id' => $registration->getId(),
                                    'role' => MemberRole::MEMBER
                                ]);
                                $member->save();
                                break;
                                
                            case 'reject':
                                $registration->delete();
                                break;
                            default:
                                throw new \InvalidArgumentException("Invalid action: $action");
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            error_log("Error approving/rejecting registrations: " . $e->getMessage());
            throw $e;
        }
    }

    public function viewSystemReports(): void {
        try {
            // Get system statistics
            $totalVisitors = (new QueryBuilder(Visitor::class))->count();
            $totalMembers = (new QueryBuilder(Member::class))->where('role', '=', MemberRole::MEMBER)->count();
            $totalRevenue = (new QueryBuilder(Payment::class))->where('status', '=', 'COMPLETED')->sum('amount');
            $activeExhibits = (new QueryBuilder(Exhibition::class))->where('status', '=', 'ACTIVE')->count();
            
            // Get recent activities
            $recentPayments = (new QueryBuilder(Payment::class))
                ->orderBy('payment_date', 'DESC')
                ->limit(10)
                ->get();
                
            $recentBookings = (new QueryBuilder(Booking::class))
                ->orderBy('created_at', 'DESC')
                ->limit(10)
                ->get();
                
            $recentFeedbacks = (new QueryBuilder(Feedback::class))
                ->orderBy('created_at', 'DESC')
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            error_log("Error viewing system reports: " . $e->getMessage());
            throw $e;
        }
    }

    public function manageFeedbackAndInquiries(): void {
        try {
            // Get all feedback and inquiries
            $feedbacks = Feedback::all();
            $contacts = Contact::all();
            
            // Process feedback and inquiry actions
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (isset($_POST['feedback_id'])) {
                    $feedbackId = filter_input(INPUT_POST, 'feedback_id', FILTER_SANITIZE_STRING);
                    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
                    $response = filter_input(INPUT_POST, 'response', FILTER_SANITIZE_STRING);
                    
                    $feedback = Feedback::find($feedbackId);
                    if ($feedback) {
                        switch ($action) {
                            case 'respond':
                                if (!$response) {
                                    throw new \InvalidArgumentException("Response cannot be empty");
                                }
                                $feedback->setAttribute('response', $response);
                                $feedback->setAttribute('status', 'RESPONDED');
                                $feedback->save();
                                break;
                            case 'delete':
                                $feedback->delete();
                                break;
                            default:
                                throw new \InvalidArgumentException("Invalid action: $action");
                        }
                    }
                }
                
                if (isset($_POST['contact_id'])) {
                    $contactId = filter_input(INPUT_POST, 'contact_id', FILTER_SANITIZE_STRING);
                    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
                    $response = filter_input(INPUT_POST, 'response', FILTER_SANITIZE_STRING);
                    
                    $contact = Contact::find($contactId);
                    if ($contact) {
                        switch ($action) {
                            case 'respond':
                                if (!$response) {
                                    throw new \InvalidArgumentException("Response cannot be empty");
                                }
                                $contact->setAttribute('response', $response);
                                $contact->setAttribute('status', 'RESPONDED');
                                $contact->save();
                                break;
                            case 'delete':
                                $contact->delete();
                                break;
                            default:
                                throw new \InvalidArgumentException("Invalid action: $action");
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            error_log("Error managing feedback and inquiries: " . $e->getMessage());
            throw $e;
        }
    }

    public function manageAccessPermissions(): void {
        try {
            // Get all users and their permissions
            $users = User::all();
            
            // Process permission changes
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                foreach ($users as $user) {
                    if (isset($_POST['permissions_' . $user->getId()])) {
                        $permissions = filter_input(INPUT_POST, 'permissions_' . $user->getId(), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
                        $permissions = json_decode($permissions, true);
                        
                        if (!isset($permissions['role']) || !isset($permissions['specific'])) {
                            throw new \InvalidArgumentException("Invalid permissions format");
                        }
                        
                        // Update user role and permissions
                        $user->setAttribute('role', $permissions['role']);
                        $user->setAttribute('permissions', json_encode($permissions['specific']));
                        $user->save();
                    }
                }
            }
        } catch (\Exception $e) {
            error_log("Error managing access permissions: " . $e->getMessage());
            throw $e;
        }
    }

    public function addManagedFeedback(Feedback $feedback): void {
        $this->managedFeedbacks[] = $feedback;
    }

    public function addManagedContact(Contact $contact): void {
        $this->managedContacts[] = $contact;
    }

    public function addManagedRestoration(Restoration $restoration): void {
        $this->managedRestorations[] = $restoration;
    }

    public function getManagedFeedbacks(): array {
        return $this->managedFeedbacks;
    }

    public function getManagedContacts(): array {
        return $this->managedContacts;
    }

    public function getManagedRestorations(): array {
        return $this->managedRestorations;
    }
} 