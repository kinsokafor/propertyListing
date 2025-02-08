<?php
use EvoPhp\Api\Requests\Requests;

$router->group('/listing-api/reviews', function() use ($router) {
    $router->post('/update/{content_id}', function($params){
        $data = array_merge($params, (array) json_decode(file_get_contents('php://input')));
        $request = new Requests;
        $request->evoAction()->auth()->execute(function() use ($data){
            return \Public\Modules\propertyListing\Classes\Reviews::new($data);
        });
    });

    $router->get('/', function($params){
        $request = new Requests;
        $request->reviews($params)->auth();
    });
});