<?php
use EvoPhp\Api\Requests\Requests;

$router->group('/listing-api/apartments', function() use ($router) {
    $router->post('/new', function(){
        $data = (array) json_decode(file_get_contents('php://input'));
        $request = new Requests;
        $request->evoAction()->auth(1,15,14,16)->execute(function() use ($data){
            return \Public\Modules\propertyListing\Classes\Apartments::new($data);
        });
    });

    $router->post('/edit/{id}', function($params){
        $data = (array) json_decode(file_get_contents('php://input'));
        $request = new Requests;
        $request->evoAction()->auth(1,15,14,16)->execute(function() use ($data, $params){
            return \Public\Modules\propertyListing\Classes\Apartments::update((int) $params['id'], $data);
        });
    });

    $router->post('/book', function(){
        $data = (array) json_decode(file_get_contents('php://input'));
        $request = new Requests;
        $request->evoAction()->auth(1,15,14,16,13,6,7,8)->execute(function() use ($data){
            return \Public\Modules\propertyListing\Classes\Bookings::new($data);
        });
    });

    $router->post('/unavailable-dates', function(){
        $data = (array) json_decode(file_get_contents('php://input'));
        $request = new Requests;
        $request->evoAction()->auth(1,15,14,16,13,6,7,8)->execute(function() use ($data){
            $today = new DateTime();
            $todaySQL = $today->format('Y-m-d');
            $nextMonth = new DateTime();
            $nextMonth->modify('+2 month');
            $nextMonthSQL = $nextMonth->format('Y-m-d');
            return \Public\Modules\propertyListing\Classes\Bookings::unavailableDates(
                (int) $data['apartment_id'], 
                $todaySQL, 
                $nextMonthSQL
            );
        });
    });

    $router->get('/', function($params) {
        $request = new Requests;
        $request->apartments($params)->auth();
    });
});