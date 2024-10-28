<?php

namespace App\Repositories;

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
    public function create(Facility $facility): \stdClass | bool {
        $query = 'INSERT INTO facilities (name, creation_date, location_id) VALUES (:name, :creation_date, :location_id)';
        $bind = [':name' => $facility->get_name(), ':creation_date' =>  date("Y-m-d"), 
            ':location_id' => $facility->get_location_id()];

        if ($this->db->executeQuery($query, $bind)) {
            $query = 'SELECT *
                        FROM facilities
                        WHERE id = :id';
            $facility_id = $this->db->getLastInsertedId();
            $bind = [':id' => $facility_id];

            foreach ($facility->get_tags() as $tag) {
                $insert_tag_query = 'INSERT IGNORE INTO tags (name) VALUES (:name)';
                $bind_tag_query = [':name' => $tag->get_name()];
                $this->db->executeQuery($insert_tag_query, $bind_tag_query);
                $select_tag_query = 'SELECT *
                    FROM tags
                    WHERE name = :name';

                $tag_id = $this->db->getLastInsertedId();
                $existing_tag = $this->db->fetchObject($select_tag_query, $bind_tag_query);
               
                if ($tag_id == 0) {
                    $tag_id = $existing_tag->id;
                } 
                
                $insert_facility_tags_query = 'INSERT INTO facility_tags (facility_id, tag_id) VALUES (:facility_id, :tag_id)';
                $bind_facility_tags_query = [':facility_id' => $facility_id, 'tag_id' => $tag_id];

                $this->db->executeQuery($insert_facility_tags_query, $bind_facility_tags_query);
            }

            $fetched_object = $this->db->fetchObject($query, $bind);

            return $this->retrieve($fetched_object->id);
        } else {
            return null;
        }
    }

    public function retrieve(int $id): \stdClass | bool {
        $query = '
            SELECT locations.*, facilities.*, GROUP_CONCAT(tags.name) as tags
            FROM facilities
            JOIN locations ON facilities.location_id = locations.id
            LEFT JOIN facility_tags ON facilities.id = facility_tags.facility_id
            LEFT JOIN tags ON facility_tags.tag_id = tags.id
            WHERE facilities.id = :id
        ';
        $bind = [':id' => $id];

        return $this->db->fetchObject($query, $bind);
    }
}
