<?php

namespace App\Models;

class Tour {
    private string $id;
    private string $name;
    private int $size;
    private TourGuide $tourGuide;
} 