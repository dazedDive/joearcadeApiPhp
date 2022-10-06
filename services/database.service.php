<?php
class DatabaseService {

        public function __construct($table)
        {
            $this->table=$table;
        }
    static private $connection=null;
    private function connect(){
        if(self::$connection==null){
            $host="localhost";
            $port="3306";
            $dbName="joe";
            $dsn="mysql:host=$host;port=$port;dbname=$dbName";
            $user="root";
            $pass="";
            try{
                $db_connection = new PDO(
                    $dsn,$user,$pass,
                    array(PDO ::ATTR_ERRMODE => PDO ::ERRMODE_EXCEPTION,
                    PDO ::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8" ,
                    )

                );
            }
            catch (PDOException $e){
                die("Error of dataBase connexion : $e->getMessage()");
            }
            self::$connection=$db_connection;

        }
        return self::$connection;
    }
    
    public function query($sql, $params){
        $statement = $this->connect()->prepare($sql);
        $result = $statement->execute($params);
        return (object) ['result' => $result, 'statement' => $statement];
    }

    public function selectAll(){
        $sql= "SELECT * FROM $this->table WHERE is_deleted = ?";
        $resp= $this->query($sql, [0]);
        $rows = $resp->statement->fetchAll(PDO::FETCH_CLASS);
        return $rows;
    }

    public function selectWhere($where = "1", $params = []){
        $sql = "SELECT * FROM $this->table WHERE $where;";
        $resp = $this->query($sql, $params);
        $rows = $resp->statement->fetchAll(PDO::FETCH_CLASS);
        return $rows;
    }
    
    public function selectOne($id){
        $sql = "SELECT * FROM $this->table WHERE is_deleted = ? AND Id_$this->table= ?";
        $resp = $this->query($sql, [0,$id]);
        $rows = $resp->statement->fetchAll(PDO::FETCH_CLASS);
        $row=$resp->result && count($rows) == 1 ? $rows[0] : null;
        return $row;
    }

    function updateOne($body){ //Version condensée
        $id = $body["Id_$this->table"];
        $where = "Id_$this->table = ?";
        if(isset($body["Id_$this->table"])){
            unset($body["Id_$this->table"]);
        }
        $set = implode(",", array_map(function ($item){ return $item."=?"; }, array_keys($body)));
        $valuesToBind = array_values($body);
        array_push($valuesToBind,$id);
        $sql = "UPDATE $this->table SET $set WHERE $where";
        $resp = $this->query($sql, $valuesToBind);
        if($resp->result && $resp->statment->rowCount() <= 1){
            $row = $this->selectOne($id);
            return $row;
        }
        return false;
    }

    public function insertOne($body = []){ //Version condensée
        if(isset($body["Id_$this->table"])){
            unset($body["Id_$this->table"]);
        }
        $columns = implode(",", array_keys($body));
        $values = implode(",", array_map(function (){ return "?"; },$body));
        $valuesToBind = array_values($body);
        $sql = "INSERT INTO $this->table ($columns) VALUES ($values)";
        $resp = $this->query($sql, $valuesToBind);
        if($resp->result && $resp->statment->rowCount() == 1){
            $insertedId = self::$connection->lastInsertId();
            $row = $this->selectOne($insertedId);
            return $row;
        }
        return false;
    }
}

?>