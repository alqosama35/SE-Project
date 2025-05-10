<?php

namespace App\Models;

enum MemberRole {
    case VISITOR;
    case MEMBER;
    case ADMIN;
    case RESEARCHER;
    case TOUR_GUIDE;
} 