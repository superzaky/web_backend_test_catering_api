<?php

namespace App\Controllers;

use App\Plugins\Http\Response as Status;
use Exception;
use App\Models\{Facility, Tag};

class IndexController extends BaseController {
    protected $facilityrepository = null;
    /**
     * Controller function used to test whether the project was set up properly.
     * @return void
     */
    public function test() {
        // Respond with 200 (OK):
        (new Status\Ok(['message' => 'Hello world!']))->send();
    }

    public function create() {
        try {
            $data = json_decode(file_get_contents('php://input'));
            $tags = array_map(function ($name) {
                return new Tag($name);
            }, $data->tags);

            $facility = new Facility($data->name, $data->location_id, date("Y-m-d"), ...$tags);
            $facility_repo = $this->__get('facilityrepository');
            $res = $facility_repo->create($facility);
            (new Status\Ok($res))->send();
        } catch (Exception $e) {
            (new Status\BadRequest($e->getMessage()))->send();
        }
    }

    public function read() {
        try {
            $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $parts = parse_url($actual_link);
            $last = basename(end($parts));

            $facility_repo = $this->__get('facilityrepository');
            $res = $facility_repo->retrieve((int) $last);

            (new Status\Ok($res))->send();
        } catch (Exception $e) {
            (new Status\BadRequest($e->getMessage()))->send();
        }
    }

    public function readMultiple() {
        try {
            $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $parts = parse_url($actual_link);
            $last = basename(end($parts));
            $facilities_string = str_replace("facilities=", "", $last);
            $facility_ids_array = explode(",", $facilities_string);
            $facility_ids_int_array = array_map('intval', $facility_ids_array);

            $facility_repo = $this->__get('facilityrepository');

            $objects = [];
            foreach ($facility_ids_int_array as $facility_id) {
                $objects[] = $facility_repo->retrieve($facility_id);
            }

            (new Status\Ok($objects))->send();
        } catch (Exception $e) {
            (new Status\BadRequest($e->getMessage()))->send();
        }
    }

    public function update() {
        try {
            $data = json_decode(file_get_contents('php://input'));
            $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $parts = parse_url($actual_link);
            $last = basename(end($parts));
            $tags = array_map(function ($name) {
                return new Tag($name);
            }, $data->tags);
            $facility = new Facility($data->name, $data->location_id, null, ...$tags);
            $facility->set_id((int) $last);
            $facility_repo = $this->__get('facilityrepository');
            $res = $facility_repo->update($facility);
            (new Status\Ok($res))->send();
        } catch (Exception $e) {
            (new Status\BadRequest($e->getMessage()))->send();
        }
    }

    public function delete() {
        try {
            $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $parts = parse_url($actual_link);
            $last = basename(end($parts));
            $facility_repo = $this->__get('facilityrepository');
            $id = (int) $last;
            $res = $facility_repo->delete($id) ? 'Facility with id ' . $id . ' has been deleted' :
                'Could not delete facility with id ' . $id;
            (new Status\Ok($res))->send();
        } catch (Exception $e) {
            (new Status\BadRequest($e->getMessage()))->send();
        }
    }

    public function search() {
        try {
            $facility_name = isset($_GET['facility_name']) ? $_GET['facility_name'] : null;
            $tag_name = isset($_GET['tag_name']) ? $_GET['tag_name'] : null;
            $city = isset($_GET['city']) ? $_GET['city'] : null;
            $facility_repo = $this->__get('facilityrepository');
            $res = $facility_repo->searchBy($facility_name, $tag_name, $city);
            (new Status\Ok($res))->send();
        } catch (Exception $e) {
            (new Status\BadRequest($e->getMessage()))->send();
        }
    }
}
