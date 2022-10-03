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

    public function selectOne($id){
        $sql = "SELECT * FROM $this->table WHERE is_deleted = ? AND Id_$this->table= ?";
        $resp = $this->query($sql, [0,$id]);
        $rows = $resp->statement->fetchAll(PDO::FETCH_CLASS);
        $row=$resp->result && count($rows) == 1 ? $rows[0] : null;
        return $row;
    }

    public function updateOne ($body){
        $idKey=array_key_first($body);
        $id=array_shift($body);
        $lastkey=array_key_last($body);
        $set="";
        foreach($body as $key=>$value){
            $key.="=";
            $value="'".$value."'";
            $set.= $key;
            if($key!=$lastkey."="){
                $set.=$value.",";
            }
            else{
                $set.=$value;
                }
        }
        $sql = "UPDATE $this->table SET $set WHERE $idKey= ? ";
        $resp = $this->query($sql,[$id]);
        $row = $resp->statement->fetchAll(PDO::FETCH_CLASS);
        $row = $this->selectOne($id);
        return  $row;
      }

      public function insertOne($body=[]) {
        $cols = array_keys($body);
        $sqlKeys = "(";
        $prefix="";
        foreach($cols as $key){
            $sqlKeys.="$prefix$key";
            $prefix=",";
        }
        $sqlKeys.=")";
        $values = array_values($body);
        $pointintero  = str_repeat('?,', count($body) - 1) . '?';
        $sql="INSERT INTO $this->table $sqlKeys VALUE ($pointintero)  ";
        $resp = $this->query($sql,$values);
        $row = $resp->statement->fetchAll(PDO::FETCH_CLASS);
        if($resp->result && $resp->statement->rowcount() == 1){
            $insertedId = self::$connection->lastInsertId();
            $row = $this->selectOne($insertedId);
            return $row;
        }
        return false;
    }
}

?>