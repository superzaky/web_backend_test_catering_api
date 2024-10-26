<?php

namespace App\Models;

class Facility {
    private $id;
    /** @var string */
    private $name;
    /** @var string */
    private $creation_date;

    /**
     * @param string $name
     */
    public function __construct(string $name) {
        $this->name = $name;
    }

    function get_name() {
        return $this->name;
    }
}
