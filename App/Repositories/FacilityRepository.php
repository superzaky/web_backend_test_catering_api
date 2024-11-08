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


    /**
     * Creates a new facility in the database.
     *
     * @param Facility $facility The facility object to be created.
     *
     * @return \stdClass|bool|null The created facility object with its ID, or null if creation fails.
     *
     * @throws InternalServerError If an exception occurs during the database operation.
     */
    public function create(Facility $facility): \stdClass | bool | null {
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


    /**
     * Retrieves a facility from the database by its ID.
     *
     * @param int $id The ID of the facility to retrieve.
     *
     * @return \stdClass|bool|null The retrieved facility object with its details, location, and tags.
     * If the facility is not found, returns null.
     * If an error occurs during the database operation, returns false.
     *
     * @throws InternalServerError If an exception occurs during the database operation.
     */
    public function retrieve(int $id): \stdClass | bool | null {
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

    /**
     * Searches facilities based on provided criteria.
     *
     * @param string|null $facilityName The name of the facility to search for.
     * @param string|null $tagName The name of the tag to search for.
     * @param string|null $city The city where the facility is located.
     *
     * @return array|bool An array of matching facilities or false if an error occurs.
     * Each facility object contains facility details, location details, and tags.
     * If no parameters are provided, all facilities are returned.
     */
    public function searchBy(string $facilityName = null, string $tagName = null, string $city = null): array | bool {
        $query = 'SELECT facilities.*, locations.city, locations.address, locations.zip_code, locations.country_code, locations.phone_number, GROUP_CONCAT(tags.name) as tags
            FROM facilities
            JOIN locations ON facilities.location_id = locations.id
            LEFT JOIN facility_tags ON facilities.id = facility_tags.facility_id
            LEFT JOIN tags ON facility_tags.tag_id = tags.id';

        $query .= " GROUP BY facilities.id";

        $conditions = [];
        $bind = [];

        if ($tagName) {
            $conditions[] = "GROUP_CONCAT(tags.name) LIKE ?";
            $bind[] = '%' . $tagName . '%';
        }   

        if ($facilityName) {
            $conditions[] = "facilities.name LIKE ?";
            $bind[] = '%' . $facilityName . '%';
        }

        if ($city) {
            $conditions[] = "locations.city LIKE ?";
            $bind[] = '%' . $city . '%';
        }

        if (!empty($conditions)) {
            $query .= " HAVING " . implode(" AND ", $conditions);
        }

        return $this->db->fetchObjects($query, $bind);
    }

}
