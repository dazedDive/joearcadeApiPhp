<?php 

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

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



  if ($_SERVER['REQUEST_METHOD'] == "POST" && ($method=="login")){
      $this->action = $this->loginV2();
    }
  if ($_SERVER['REQUEST_METHOD'] == "POST" && ($method=="register")){
      $this->action = $this->register();
    }
  if ($_SERVER['REQUEST_METHOD'] == "GET" && ($method=="check")){
      $this->action = $this->check();
    }
  if ($_SERVER['REQUEST_METHOD'] == "POST" && ($method=="validate")){
      $this->action = $this->validate();
    }

  if ($_SERVER['REQUEST_METHOD'] == "POST" && ($method=="create")){
      $this->action = $this->create();
    }
  if ($_SERVER['REQUEST_METHOD'] == "POST" && ($method=="reset")){
      $this->action = $this->reset();
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
            $secretKey = $_ENV['config']->jwt->secret;
            $issuedAt = time();
            $expireAt = $issuedAt + 60*60*24;
            $serverName = "joe.api";
            $status = $row[0]->is_admin;
            $userId = $row[0]->Id_account;
            $requestData = [
              'iat' => $issuedAt,
              'iss' => $serverName,
              'nbf' => $issuedAt,
              'exp' => $expireAt,
              'userRole' => $status,
              'userId' => $userId
            ];
            $token = JWT::encode($requestData, $secretKey, 'HS512');
            return [ "result" => true, "is_admin" => $row[0]->is_admin, "id" =>$row[0]->Id_account, "token"=>$token];
        }
        else {
            return [ "result" => false];
        }
    }

    public function check(){
      $headers = apache_request_headers();
      if (isset($headers["Authorization"])){
        $token = $headers["Authorization"];
      }
      $secretKey = $_ENV['config']->jwt->secret;
      if(isset($token)&&!empty($token)){
        try{
          $payload = JWT::decode($token, new Key($secretKey, 'HS512'));
        }
        catch(Exception $e){
          $payload = null;
        }
        if (isset($payload) &&
        $payload->iss ==="joe.api" &&
        $payload->nbf < time() &&
        $payload->exp > time()) 
        {
          return ["result" => true, "is_admin" => $payload->userRole, "id" =>$payload->userId];
        }
      }
      return ["result" => false];
    }

    public function register(){
      
      $mail=$this->body['email'];
      $name=$this->body['name'];
      $firstname=$this->body['firstname'];
      $adress=$this->body['adresse'];
      $tel=$this->body['telephone'];
      $cp=$this->body['codepostal'];
      $city=$this->body['ville'];
      $dbs= new DatabaseService('account');
      
      $checkmail=$dbs->selectWhere("login = ? AND is_deleted = ?", [$mail,0]);
      if(count($checkmail)==1){
        return "Mail ".$mail. " deja utilisé, veuillez vous connecter";
        die;}

      $secretKey = $_ENV['config']->jwt->secret;
            $issuedAt = time();
            $expireAt = $issuedAt + 60*60*24;
            $serverName = "joe.api";
            $userMail = $mail;
            $userName = $name;
            $userFirstName = $firstname;
            $userTel = $tel;
            $userAdress = $adress;
            $userCp = $cp;
            $userCity = $city;
            $requestData = [
              'iat' => $issuedAt,
              'iss' => $serverName,
              'nbf' => $issuedAt,
              'exp' => $expireAt,
              'usermail' => $userMail,
              'userName' => $userName,
              'userFirstName' => $userFirstName,
              'userTel' => $userTel,
              'userAdress' => $userAdress,
              'userCp' => $userCp,
              'userCity' => $userCity
            ];
            $token = JWT::encode($requestData, $secretKey, 'HS512');
            $href = "http://localhost:3000/creation/password/$token " ;
            require_once('services/mailer.service.php');
            $ms = new MailerService();
            $mailParams = [
              "fromAddress"=>["monCompte@joe-arcade.fr", "monCompte joe-arcade.fr"],
              "destAdresses"=>[$mail],
              "replyAdress"=>["monCompte@joe-arcade.fr", "monCompte joe-arcade.fr"],
              "subject"=>"Confirmation d'adresse Email",
              "body"=>"Salut ".$userFirstName.",afin de valider votre compte, veuillez cliquer
              sur ce <a href=$href>lien</a>",
              "altBody"=>"Joe Arcade ! La location de Flipper facile et fun ! "
            ];
            $ms->send($mailParams);
            return "Un mail de confirmation a été envoyé à votre adresse mail, veuillez cliquer sur le lien
            contenue dans celui ci pour finaliser la création de votre compte ;)";
    }
    public function validate(){
      $token = $this->body['token'] ?? "";
      if (isset($token)){
        $secretKey = $_ENV['config']->jwt->secret;
        
        try{
          $payload = JWT::decode($token, new Key($secretKey, 'HS512'));
        }
        catch(Exception $e){
          $payload = null;
        }
        if (isset($payload) &&
          $payload->iss === "blog.api" &&
          $payload->nbf < time() &&
          $payload->exp > time())

          {
            return ["result" => true,
            "userFirstName" => $payload->userFirstName,
            "mail" => $payload->usermail,
            "login" => $payload->usermail,
            "userName" =>$payload->userName,
            "userAdress" =>$payload->userAdress,
            "userCp" =>$payload->userCp,
            "userCity" =>$payload->userCity,
            "userTel" =>$payload->userTel];
          }
          else{
            return ["result" => false];
          }
      }

    }
  public function create(){
    
    $login=$this->body['login'];
    $mail=$this->body['mail'];
    $name=$this->body['userName'];
    $firstName=$this->body['userFirstName'];
    $tel=$this->body['userTel'];
    $adress=$this->body['userAdress'];
    $cp=$this->body['userCp'];
    $city=$this->body['userCity'];
    $password = password_hash($this->body["password"], PASSWORD_ARGON2ID, [
      'memory_cost' => 1024,
      'time_cost' => 2,
      'threads' => 2
    ]);
    $prefix = $_ENV['config']->hash->prefix;
    $password = str_replace($prefix,"",$password);
    $dbs = new DatabaseService('account');

    $body = ['login'=>$login
            ,'password'=>$password
            ,'is_admin'=>0
            ,'is_deleted'=>0];

    $row=$dbs->insertOne($body);
    if(isset($row)){
      $dbs = new DatabaseService('customer');
      $body = ['first_name'=>$firstName,
              'last_name'=>$name,
              'telephone'=>$tel,
              'mail'=>$mail,
              'adresse_facturation'=>$adress." ".$cp.":".$city,
              'is_deleted'=>0,
              'Id_account'=>$row->Id_account];
      $dbs->insertOne($body);
      return ["inscription"=>true];
    }else{
      return ["inscription"=>false];
    }
  }

  public function reset(){
    $login=$this->body;
    $dbs = new DatabaseService('account');
    $checkmail=$dbs->selectWhere("login = ? AND is_deleted = ?", [$login,0]);
      if(count($checkmail)==0){
        return "l'adresse : ".$login. " n'existe pas...";
        die;}
    $secretKey = $_ENV['config']->jwt->secret;
    $issuedAt = time();
    $expireAt = $issuedAt + 60*60*24;
    $serverName = "joe.api";  
    $userMail = $login;
    $requestData = [
      'iat' => $issuedAt,
      'iss' => $serverName,
      'nbf' => $issuedAt,
      'exp' => $expireAt,
      'usermail' => $userMail];
    $token = JWT::encode($requestData, $secretKey, 'HS512');
    $href = "http://localhost:3000/reset/password/$token " ;
    require_once('services/mailer.service.php');
    $ms = new MailerService();
    $mailParams = [
      "fromAddress"=>["monCompte@joe-arcade.fr", "monCompte joe-arcade.fr"],
      "destAdresses"=>[$login],
      "replyAdress"=>["monCompte@joe-arcade.fr", "monCompte joe-arcade.fr"],
      "subject"=>"Création d'un nouveau mot de passe",
      "body"=>"Salut ,afin de modifier votre mot de passe, veuillez cliquer
      sur ce <a href=$href>lien</a>",
      "altBody"=>"Joe Arcade ! La location de Flipper facile et fun ! "
    ];
    $ms->send($mailParams);
    return "Un lien a été envoyé à votre adresse mail, veuillez cliquer 
    celui ci pour modifier votre mot de passe ;)";   

  }
}

?>
