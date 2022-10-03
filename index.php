<?php
header ( "Access-Control-Allow-Origin: http://localhost:3000" );
header ( "Access-Control-Allow-Methods: *" );
if ( $_SERVER [ 'REQUEST_METHOD' ] == "OPTIONS" ){
header ( 'HTTP/1.0 200 OK' );
die ;
}
require_once 'services/database.service.php';
require_once 'controllers/database.controller.php';


//recupertation des controllers et des controllers file path //
$route = trim($_SERVER["REQUEST_URI"], '/');
$route = filter_var($route,FILTER_SANITIZE_URL);
$route = explode ( '/' , $route );
$controllerName = array_shift($route);
$controllerClassName = ucfirst($controllerName."Controller");
$controllerFilePath = "controllers/".$controllerName.".controller.php";



if(!file_exists($controllerFilePath)){
    header ("HTTP/1.0 404 Not Found");
    die;
}

require_once $controllerFilePath;
$controller = new $controllerClassName ($route);

$response = $controller->action;
if (!isset($response)){
    header ("HTTP/1.0 404 Not Found");
}
echo json_encode($response);
?>