<?php  

use Public\Modules\propertyListing\PLIController;
use EvoPhp\Api\Requests\Requests;

//API End points

//Pages

$router->get('/secured', function($params){
    $controller = new PLIController;
    $controller->{'PLIMain/index'}($params)->auth(2,3,4)->setData(["pageTitle" => "Admin"]);
}); 
