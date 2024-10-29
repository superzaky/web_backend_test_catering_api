<?php

namespace App\Models;

class Tag {
    private $id;
    /** @var string */
    private $name;
    /**
     * @var Facility[] $facilities
     */
    private array $facilities = [];

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
