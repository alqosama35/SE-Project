<?php

namespace App\Models;

enum MuseumObjectStatus {
    case AVAILABLE;
    case ON_LOAN;
    case UNDER_RESTORATION;
    case RESERVED;
    case ARCHIVED;
} 