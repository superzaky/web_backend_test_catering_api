<?php

namespace App\Repositories;

use App\Plugins\Http\Exceptions\InternalServerError;
use App\Models\Facility;
use App\Plugins\Db\Db;

use App\Plugins\Di\Factory;
use Exception;

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
        $this->db->beginTransaction();
        try {
            $query = 'INSERT INTO facilities (name, creation_date, location_id) VALUES (:name, :creation_date, :location_id)';
            $bind = [
                ':name' => $facility->getName(),
                ':creation_date' =>  $facility->getCreationDate(),
                ':location_id' => $facility->get_location_id()
            ];

            if ($this->db->executeQuery($query, $bind)) {
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

                $fetched_object = $this->retrieve($facility_id);
                $this->db->commit();
                return $fetched_object;
            } else {
                return null;
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new InternalServerError($e);
        }
    }

    public function retrieve(int $id): \stdClass | bool {
        $query = 'SELECT locations.*, facilities.*, GROUP_CONCAT(tags.name) as tags
            FROM facilities
            JOIN locations ON facilities.location_id = locations.id
            LEFT JOIN facility_tags ON facilities.id = facility_tags.facility_id
            LEFT JOIN tags ON facility_tags.tag_id = tags.id
            WHERE facilities.id = :id';
        $bind = [':id' => $id];
        return $this->db->fetchObject($query, $bind);
    }

    public function update(Facility $facility): \stdClass | bool {
        $this->db->beginTransaction();
        try {
            $query = 'UPDATE facilities SET name=:name, location_id=:location_id WHERE id = :id';
            $bind = [
                ':name' => $facility->getName(),
                ':location_id' => $facility->get_location_id(),
                ':id' => $facility->get_id()
            ];

            if ($this->db->executeQuery($query, $bind)) {
                $query = 'DELETE FROM facility_tags WHERE facility_id = :id';
                $bind = [':id' => $facility->get_id()];
                if ($this->db->executeQuery($query, $bind)) {
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
                        $bind_facility_tags_query = [':facility_id' => $facility->get_id(), 'tag_id' => $tag_id];

                        $this->db->executeQuery($insert_facility_tags_query, $bind_facility_tags_query);
                    }
                    $query = 'SELECT * FROM facilities WHERE id = :id';
                    $bind = [':id' => $facility->get_id()];

                    $fetched_object = $this->db->fetchObject($query, $bind);
                    $fetched_object = $this->retrieve($fetched_object->id);
                    $this->db->commit();
                    return $fetched_object;
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new InternalServerError($e);
        }
    }

    public function delete(int $id): bool {
        $this->db->beginTransaction();
        try {
            $query = 'DELETE FROM facilities WHERE id = :id';
            $bind = [':id' => $id];
            $is_deleted = $this->db->executeQuery($query, $bind);
            $this->db->commit();
            return $is_deleted;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new InternalServerError($e);
        }
    }

    public function searchBy(string $facility_name = null, string $tag_name = null, string $city = null): array | bool {
        $query = 'SELECT facilities.*, locations.city, locations.address, locations.zip_code, locations.country_code, locations.phone_number, GROUP_CONCAT(tags.name) as tags
            FROM facilities
            JOIN locations ON facilities.location_id = locations.id
            LEFT JOIN facility_tags ON facilities.id = facility_tags.facility_id
            LEFT JOIN tags ON facility_tags.tag_id = tags.id
            WHERE 1=1';

        if ($facility_name) {
            $query .= " AND facilities.name LIKE ?";
            $bind[] = '%' . $facility_name . '%';
        }

        if ($tag_name) {
            $query .= " AND tags.name LIKE ?";
            $bind[] = '%' . $tag_name . '%';
        }

        if ($city) {
            $query .= " AND locations.city LIKE ?";
            $bind[] = '%' . $city . '%';
        }

        $query .= " GROUP BY facilities.id";

        return $this->db->fetchObjects($query, $bind);
    }
}
