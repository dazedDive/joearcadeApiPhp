<?php 
class AuthController {
   
    public function __construct($params)
  {
    $method = array_shift($params);
    $this->action=null;
    if(isset($method) && ctype_digit($method)){ 
        return $this;  
      }
    $request_body = file_get_contents('php://input');
    $this->body = $request_body ? json_decode($request_body, true) : null;
    $this->table = lcfirst(str_replace("Controller","",get_called_class()));



    if ($_SERVER['REQUEST_METHOD'] == "POST" && ($method="login")){
        $this->action = $this->loginV2();
      }

    
    }
    // public function login(){
    //     $dbs = new DatabaseService('account');
    //     $rows = $dbs->selectAll();
    //     $login = $this->body['login'];
    //     $password = $this->body['password'];
    //     foreach($rows as $row){
    //     if ($row->login==$login) {
    //         if($password==$row->password){
    //             $appuserId=$row->Id_appUser;
    //             $dbs = new DatabaseService('appuser');
    //             $row= $dbs->selectOne($appuserId);
    //             return [ "result" => true, "role" => $row->Id_role	 ];
    //         }else{
    //             return [ "result" => false ];
    //         }
    //     }
    //     else {
    //         continue;
    //     }
    // }
    // return [ "result" => false ];
    // }
    ///JoshAdmin101
    public function loginV2(){
        $dbs= new DatabaseService('account');
        $login = filter_var($this->body['login'], FILTER_SANITIZE_EMAIL);
        if (!filter_var($login, FILTER_VALIDATE_EMAIL)){
            return ["result" => false];
        }
        $password = $this->body['password'];
        $row=$dbs->selectWhere("login = ? AND is_deleted = ?", [$login,0]);
        $prefix = $_ENV['config']->hash->prefix;
        if(isset($row[0])&& password_verify($password,$prefix.$row[0]->password)){
           
            return [ "result" => true, "role" => $row[0]->is_admin];
        }
        else {
            return [ "result" => false];
        }
    }


}

?>
