<?php

namespace App\Controllers;

use App\Plugins\Http\Response as Status;
use App\Plugins\Http\Exceptions;
use App\Models\Facility;
use App\Models\Tag;


//use Store\Model\{Customer, Product}; <--- TODO: implementeer dit voor later ipv bovenstaande
class IndexController extends BaseController {
    protected $facilityrepository=null;
    /**
     * Controller function used to test whether the project was set up properly.
     * @return void
     */
    public function test() {
        // Respond with 200 (OK):
        (new Status\Ok(['message' => 'Hello world!']))->send();
    }

    public function create() {
        $data = json_decode(file_get_contents('php://input'));
        $tags = array_map(function ($name) {
            return new Tag($name);
        }, $data->tags);

        $facility = new Facility($data->name, $data->location_id, ...$tags);
        $facility_repo = $this->__get('facilityrepository');
        $res= $facility_repo->create($facility);
        (new Status\Ok($res))->send();
    }

    public function read() {
        $data = json_decode(file_get_contents('php://input'));
        $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";   
        $parts = parse_url($actual_link);    
        $last = basename( end($parts) );

        $facility_repo = $this->__get('facilityrepository');
        $res= $facility_repo->retrieve((int) $last);

        (new Status\Ok($res))->send();
    }

    public function readMultiple() {
        $data = json_decode(file_get_contents('php://input'));
        $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";   
        $parts = parse_url($actual_link);    
        $last = basename( end($parts) );
        $facilities_string = str_replace("facilities=", "", $last);
        $facility_ids_array = explode(",", $facilities_string);
        $facility_ids_int_array = array_map('intval', $facility_ids_array);

        $facility_repo = $this->__get('facilityrepository');

        $objects = [];
        foreach ($facility_ids_int_array as $facility_id) {
            $objects[]= $facility_repo->retrieve($facility_id);
        }

        (new Status\Ok($objects))->send();
    }

    public function update() {
        $data = json_decode(file_get_contents('php://input'));
        $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";   
        $parts = parse_url($actual_link);    
        $last = basename( end($parts) );
        $tags = array_map(function ($name) {
            return new Tag($name);
        }, $data->tags);

        $facility = new Facility($data->name, $data->location_id, ...$tags);
        $facility->set_id((int) $last);
        $facility_repo = $this->__get('facilityrepository');
        $res= $facility_repo->update($facility);
        (new Status\Ok($res))->send();
    }

    public function delete() {
        $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";   
        $parts = parse_url($actual_link);    
        $last = basename( end($parts) );
        $facility_repo = $this->__get('facilityrepository');
        $id= (int) $last;
        $res = $facility_repo->delete($id) ? 'Facility with id '. $id . ' has been deleted' : 
            'Could not delete facility with id '. $id;
        (new Status\Ok($res))->send();
    }
}
