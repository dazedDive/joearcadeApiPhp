<?php
class DatabaseController {
    public function __construct($props)
    {
       $id=array_shift($props);
       $this->action=null;

       if (isset ($id) && !ctype_digit($id)){
        return $this;
    }
        
        $request_body = file_get_contents('php://input');
        $this->body = $request_body ? json_decode($request_body, true) : null;
       $this->table = lcfirst(str_replace("Controller", "", get_called_class()));
        
       if ($_SERVER['REQUEST_METHOD'] == "GET" && !isset($id)){
        $this->action = $this->getAll();
       }
       
       if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($id)){
        $this->action = $this->getOne($id);
       }
       if ($_SERVER['REQUEST_METHOD'] == "POST"  && !isset($id)){
        $this->action = $this->create();
       }

       if ($_SERVER['REQUEST_METHOD'] == "PUT" && isset($id)){
        $this->action = $this->uppdate($id);
       }

       if ($_SERVER=['REQUEST_METHOD'] == "PATCH" && isset($id)){
        $this->action = $this->softDelete($id);
       }
     
    }

    public function getAll(){
         $dbs = new DatabaseService($this->table);
         $rows = $dbs->selectAll();
         return $rows;
    }

    public function getOne($id){
        $dbs = new DatabaseService($this->table);
        $rows = $dbs->selectOne($id);
        return $rows;
    }

    public function create(){
        $dbs = new DatabaseService($this->table);
        $row = $dbs->insertOne($this->body);
        return $row;
        }

    public function uppdate($id){
        $dbs = new DatabaseService($this->table);
        if ( $id != $this->body [ "Id_$this->table" ]){
        return false ;
        }
         $row = $dbs->updateOne($this->body);
        return $row;
    }
    public function softDelete($id){
        return "ca marche";
    }

    public function delete($id){
        return "ca marche";
    }
}
?>