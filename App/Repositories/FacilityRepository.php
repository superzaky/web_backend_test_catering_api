<?php

namespace App\Repositories;

use App\Plugins\Http\Response as Status;
use App\Plugins\Http\Exceptions;
use App\Models\Facility;
use App\Plugins\Db\Db;

use App\Plugins\Di\Factory;

class FacilityRepository {
    /** @var Db|null */
    private $db = null;

    public function __construct() {        
        $di = Factory::getDi();
        $this->db = $di->getShared('db');
    }

    /*
    @throws Exception if an operation fails
    */
    public function create(Facility $facility) {
        $query = 'INSERT INTO Facilities (name) VALUES (:name)';
        $bind = [':name' => $facility->get_name()];
        if ($this->db->executeQuery($query, $bind)) {
            
            $query = 'SELECT *
                        FROM Facilities
                        WHERE id = :id';
            $bind = [':id' => $this->db->getLastInsertedId()];
            return $this->db->fetchObject($query, $bind);
        } else {
            return null;
        }
    }
}
