<?php  

use Public\Modules\propertyListing\PLIController;
use EvoPhp\Api\Requests\Requests;

//API End points
include_once("Routes/Properties.php");
include_once("Routes/Apartments.php");
//Pages

$router->get('/secured', function($params){
    $controller = new PLIController;
    $controller->{'PLIMain/index'}($params)->auth(2,3,4,5,11,12)->setData(["pageTitle" => "Admin"]);
}); 

$router->get('/customers', function($params){
    $controller = new PLIController;
    $controller->{'PLICustomers/index'}($params)->auth(6,7,8,9,10,13)->setData(["pageTitle" => "Customer"]);
});

$router->get('/home', function($params){
    $controller = new PLIController;
    $controller->{'PLIPublic/index'}($params)->auth()->setData(["pageTitle" => "Public"]);
});