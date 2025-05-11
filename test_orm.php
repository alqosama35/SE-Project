<?php

use App\Models\Database;
use App\Models\MuseumObject;
use App\Models\Collection;
use App\Models\Gallery;
use App\Models\Exhibition;
use App\Models\Loan;
use App\Models\Restoration;

require_once __DIR__ . '/model/Model.php';
require_once __DIR__ . '/model/Database.php';
require_once __DIR__ . '/model/QueryBuilder.php';
require_once __DIR__ . '/model/MuseumObject.php';
require_once __DIR__ . '/model/Collection.php';
require_once __DIR__ . '/model/Gallery.php';
require_once __DIR__ . '/model/Exhibition.php';
require_once __DIR__ . '/model/Loan.php';
require_once __DIR__ . '/model/Restoration.php';

// Test database connection
try {
    $db = Database::getInstance();
    echo "Database connection successful!\n";
    
    // Test creating a museum object
    $object = new MuseumObject([
        'id' => uniqid(),
        'name' => 'Ancient Vase',
        'description' => 'A beautiful ancient vase from the Roman period',
        'origin' => 'Rome',
        'period' => 'Roman',
        'material' => 'Ceramic',
        'status' => 'AVAILABLE'
    ]);
    
    if ($object->save()) {
        echo "Museum object created successfully!\n";
        
        // Test finding the object
        $found = MuseumObject::find($object->getAttribute('id'));
        if ($found) {
            echo "Found museum object: " . $found->getAttribute('name') . "\n";
        }
        
        // Test updating the object
        $object->setAttribute('description', 'Updated description');
        if ($object->save()) {
            echo "Museum object updated successfully!\n";
        }
        
        // Test querying objects
        $objects = MuseumObject::where('status', '=', 'AVAILABLE')->get();
        echo "Found " . count($objects) . " available objects\n";
        
        // Test deleting the object
        if ($object->delete()) {
            echo "Museum object deleted successfully!\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 