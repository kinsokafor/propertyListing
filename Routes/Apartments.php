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

    $router->post('/book', function(){
        $data = (array) json_decode(file_get_contents('php://input'));
        $request = new Requests;
        $request->evoAction()->auth(1,15,14,16,13,6,7,8)->execute(function() use ($data){
            return \Public\Modules\propertyListing\Classes\Bookings::new($data);
        });
    });
});