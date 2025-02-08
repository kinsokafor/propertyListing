<?php
use EvoPhp\Api\Requests\Requests;

$router->group('/listing-api/views', function() use ($router) {
    $router->post('/update/{content_id}', function($params){
        $data = array_merge($params, (array) json_decode(file_get_contents('php://input')));
        $request = new Requests;
        $request->evoAction()->auth()->execute(function() use ($data){
            return \Public\Modules\propertyListing\Classes\Views::new($data);
        });
    });
});