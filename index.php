<?php
/////////////////////////HEADER D'AUTORISATION////////////////////////
header ( "Access-Control-Allow-Origin: http://localhost:3000" );
////////////////AUTORISATION DE PARTAGE DE COOKIE////////////////
header ( 'Access-Control-Allow-Headers: Authorization' );
header ( "Access-Control-Allow-Credentials: true" );
///////////////AUTHORIZATION POUR LES POST ET PUT////////////////
header ( "Access-Control-Allow-Methods: GET,POST,PUT,PATCH,OPTIONS" );
if ( $_SERVER [ 'REQUEST_METHOD' ] == "OPTIONS" ){
    header ( 'HTTP/1.0 200 OK' );
    die ;
    }
///////////////////////////////////////////////////////////////////////
//////////////////////////IMPORTATION FICHIERS/////////////////////////
require_once 'services/database.service.php';
require_once 'controllers/database.controller.php';
require_once 'vendor/autoload.php';
///////////////////////////////////////////////////////////////////////
///////////////////////ATTRIBUTION DE LA CONFIG par defaut en DEV////////////
$_ENV["current"] = "dev";
$config = file_get_contents("configs/".$_ENV["current"].".config.json"); 
$_ENV['config']=json_decode($config);

//////////////////////DECOUPAGE URI APPEL ROULE///////////////////////
$route = trim($_SERVER["REQUEST_URI"], '/');
$route = filter_var($route,FILTER_SANITIZE_URL);
$route = explode ( '/' , $route );
$controllerName = array_shift($route);

//////////////////PROTECTION DE LAPI AVEC LE MIDDLEWARE/////////////////
require_once 'middlewares/auth.middleware.php';
$req = $_SERVER['REQUEST_METHOD'] . "/" . trim($_SERVER["REQUEST_URI"], '/');
$am = new AuthMiddleware($req);
$am->verify();

//////////////////APEL DES CONTROLLERS////////////////////////////

$controllerClassName = ucfirst($controllerName."Controller");
$controllerFilePath = "controllers/".$controllerName.".controller.php";
if(!file_exists($controllerFilePath)){
    header ("HTTP/1.0 404 Not Found");
    die;
}
require_once $controllerFilePath;
$controller = new $controllerClassName ($route);
//////////////////REPONSE DE L'API////////////////////////
$response = $controller->action;
if (!isset($response)){
    header ("HTTP/1.0 404 Not Found");
}
echo json_encode($response);
?>