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
});