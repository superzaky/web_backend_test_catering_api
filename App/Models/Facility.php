<?php

namespace App\Models;

class Facility {
    private $id;
    /** @var string */
    private $name;
    /** @var string */
    private $creation_date;
    /** @var integer */
    private $location_id;
    /**
     * @var Tag[] $tags
     */
    private $tags = [];

    /**
     * @param string $name
     */
    public function __construct(string $name, int $location_id, Tag ...$tags) {
        $this->name = $name;
        $this->location_id = $location_id;
        $this->tags = $tags;
    }

    function get_id() {
        return $this->id;
    }

    function set_id(int $id) {
        $this->id = $id;
    }

    function get_name() {
        return $this->name;
    }

    function get_location_id() {
        return $this->location_id;
    }

    function get_tags() {
        return $this->tags;
    }
}
