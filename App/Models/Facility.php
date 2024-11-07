<?php

namespace App\Models;

class Facility {
    private $id;
    /** @var string */
    private $name;
    /** @var string */
    private $creationDate;
    /** @var integer */
    private $location_id;
    /**
     * @var Tag[] $tags
     */
    private $tags = [];

    /**
     * @param string $name
     */
    public function __construct(string $name, int $location_id, string $creationDate = null, Tag ...$tags) {
        $this->name = $name;
        $this->location_id = $location_id;
        $this->tags = $tags;
        $this->creationDate = $creationDate;
    }

    function get_id() {
        return $this->id;
    }

    function set_id(int $id) {
        $this->id = $id;
    }

    function getCreationDate() {
        return $this->creationDate;
    }

    function getName() {
        return $this->name;
    }

    function get_location_id() {
        return $this->location_id;
    }

    function get_tags() {
        return $this->tags;
    }
}
