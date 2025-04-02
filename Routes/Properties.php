<?php
use EvoPhp\Api\Requests\Requests;

$router->group('/listing-api/properties', function() use ($router) {
    $router->post('/new', function(){
        $data = (array) json_decode(file_get_contents('php://input'));
        $request = new Requests;
        $request->evoAction()->auth(1,15,14,16)->execute(function() use ($data){
            return \Public\Modules\propertyListing\Classes\Properties::new($data);
        });
    });

    $router->post('/edit/{id}', function($params){
        $data = (array) json_decode(file_get_contents('php://input'));
        $request = new Requests;
        $request->evoAction()->auth(1,15,14,16)->execute(function() use ($data, $params){
            return \Public\Modules\propertyListing\Classes\Properties::update((int) $params['id'], $data);
        });
    });

    $router->post('/book-inspection', function(){
        $data = (array) json_decode(file_get_contents('php://input'));
        $request = new Requests;
        $request->evoAction()->auth(1,15,14,16,13,6,7,8)->execute(function() use ($data){
            return \Public\Modules\propertyListing\Classes\Inspections::new($data);
        });
    });

    $router->post('/get/inspections', function(){
        $data = (array) json_decode(file_get_contents('php://input'));
        $request = new Requests;
        $request->evoAction()->auth(1,15,14,16)->execute(function() use ($data){
            return \Public\Modules\propertyListing\Classes\Inspections::getByOwner($data, "all");
        });
    });

    $router->post('/get/unscheduled-inspections', function(){
        $data = (array) json_decode(file_get_contents('php://input'));
        $request = new Requests;
        $request->evoAction()->auth(1,15,14,16)->execute(function() use ($data){
            return \Public\Modules\propertyListing\Classes\Inspections::getByOwner($data, "unscheduled");
        });
    });

    $router->post('/get/scheduled-inspections', function(){
        $data = (array) json_decode(file_get_contents('php://input'));
        $request = new Requests;
        $request->evoAction()->auth(1,15,14,16)->execute(function() use ($data){
            return \Public\Modules\propertyListing\Classes\Inspections::getByOwner($data, "scheduled");
        });
    });
    // $router->post('/unavailable-dates', function(){
    //     $data = (array) json_decode(file_get_contents('php://input'));
    //     $request = new Requests;
    //     $request->evoAction()->auth(1,15,14,16,13,6,7,8)->execute(function() use ($data){
    //         $today = new DateTime();
    //         $todaySQL = $today->format('Y-m-d');
    //         $nextMonth = new DateTime();
    //         $nextMonth->modify('+2 month');
    //         $nextMonthSQL = $nextMonth->format('Y-m-d');
    //         return \Public\Modules\propertyListing\Classes\Bookings::unavailableDates(
    //             (int) $data['apartment_id'], 
    //             $todaySQL, 
    //             $nextMonthSQL
    //         );
    //     });
    // });

    $router->get('/', function($params) {
        $request = new Requests;
        $request->properties($params)->auth();
    });
});