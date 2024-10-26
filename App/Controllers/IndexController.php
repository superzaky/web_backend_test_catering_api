<?php

namespace App\Controllers;

use App\Plugins\Http\Response as Status;
use App\Plugins\Http\Exceptions;
use App\Models\Facility;
use App\Repositories\FacilityRepository;
use App\Plugins\Di\Factory;

//use Store\Model\{Customer, Product}; <--- TODO: implementeer dit voor later ipv bovenstaande
class IndexController extends BaseController {
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

        $di = Factory::getDi();
        $di->setShared('facilityrepository', function () {
            return new FacilityRepository();
        });
        $facility = new Facility($data->name);
        $facility_repo = $this->__get('facilityrepository');
        $res= $facility_repo->create($facility);
        // Respond with 200 (OK):
        (new Status\Ok(['message' => 'res : '. $res]))->send();
    }
}
