<?php
use EvoPhp\Api\Requests\Requests;

$router->group('/listing-api/accounts', function() use ($router) {
    $router->post('/registration', function(){
        $data = (array) json_decode(file_get_contents('php://input'));
        $request = new Requests;
        $request->user($data)->auth();
    });
});