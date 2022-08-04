<?php

/**
 * Classe abstrada AciveRecord
 */
abstract class ActiveRecord
{

    /**
     * content array
     */
    private $content;

    private static $connection;
    protected $table = null;
    protected $idField = null;
    protected $logTimeStamp;

    public function __construct()
    {
        if (!is_bool($this->logTimeStamp)) {
            $this->logTimeStamp = true;
        }

        if ($this->table == null) {
            $this->table = strtolower(get_class($this));
        }

        if ($this->idField == null) {
            $this->idField = "id";
        }
    }



    public function __set($parameter, $value)
    {
        $this->content[$parameter] = $value;
    }

    public function __get($parameter)
    {
        return $this->content[$parameter];
    }

    public function __isset($name)
    {
        return isset($this->content[$name]);
    }

    public function __unset($parameter)
    {
        if (isset($parameter)) {
            unset($this->content[$parameter]);
            return true;
        }
        return false;
    }


    public function __clone()
    {
        if (isset($this->content[$this->idField])) {
            unset($this->content[$this->idField]);
        }
    }

    public function toArray()
    {
        return $this->content;
    }

    public function fromArray(array $array)
    {
        $this->content = $array;
    }


    public function toJson()
    {
        return json_encode($this->content);
    }

    public function fromJson(string $json)
    {
        $this->content = json_encode($json);
    }

    public static function setConnection(PDO $connection)
    {
        self::$connection = $connection;
    }

    /**
     * Method Státic find
     */

    public static function find(int $parameter)
    {
        // Pega o nome da classe
        $class = get_called_class();
        $idField = (new $class())->idField;
        $table = (new $class())->table;

        $sql = "select * from " . (is_null($table) ? strtolower($class) : $table);
        $sql .= " where " . (is_null($idField) ? "id" : $idField);
        $sql .= " = " . $parameter;

        if (self::$connection = Connection::getInstance()) {
            $result = self::$connection->query($sql);

            if ($result) {
                $newObject =  $result->fetchObject(get_called_class());
            }
            return $newObject;
        }
    }

    public static function count(string $fieldName = "*", string $filter = "")
    {
        $class = get_called_class();
        $table = (new $class)->table;

        $sql = "select count($fieldName) as t from " . (is_null($table) ? $class : $table);
        $sql .= ($filter !== "") ? " where {$filter}" : "";
        $sql .= ";";

        if (self::$connection = Connection::getInstance()) {
            $q = self::$connection->prepare($sql);
            $q->execute();
            $a = $q->fetch(PDO::FETCH_ASSOC);

            return (int)$a['t'];
        }else{
            throw new Exception("Não ha conexão com o banco de dados");
            
        }
    }

    // Method Atualizar e cadastrar

    public function save()
    {
        $newContent = $this->convertContent();

        if (isset($this->content[$this->idField])) {
            $sets = [];

            foreach ($newContent as $key => $value) {
                if ($key === $this->idField || $key === "created_at" || $key === "updated_at") {
                    continue;
                }
                $sets[] = "{$key} = {$value}";
            }
            if ($this->logTimeStamp === true) {
                $sets[] = "updated_at = '" . date('Y-m-d H:i:s') . "'";
            }
            $sql = "update {$this->table} set " . implode(", ", $sets) . " where $this->idField = {$this->content[$this->idField]}";
        } else {

            if ($this->logTimeStamp === true) {
                $newContent['created_at'] = "'" . date('Y-m-d H:i:s') . "'";
                $newContent['updated_at'] = "'" . date('Y-m-d H:i:s') . "'";
            }

            $sql = "insert into {$this->table} (" . implode(", ", array_keys($newContent)) . ") values (" . implode(", ", array_values($newContent)) . ")";
        }
        if (self::$connection = Connection::getInstance()) {
            return self::$connection->exec($sql);
        }
    }


    /**
     * Retorna apenas um usuário
     */
    public static function findFirst(string $filter = "")
    {
        return self::all($filter, 1);
    }

    /**
     * Method para retorno de  uma grade de objetos
     */

    public static function all(string $filter = "", int $limit = 0, int $offset = 0)
    {
        $class = get_called_class();
        $table = (new $class())->table;

        $sql = "select * from " . (is_null($table) ? strtolower($class) : $table);
        $sql .= ($filter !== "") ? " where {$filter}" : "";
        $sql .= ($limit > 0) ? " limit {$limit}" : "";
        $sql .= ($offset > 0) ?  " offset {$offset}" : "";
        $sql .= ";";

        if (self::$connection = Connection::getInstance()) {
            $resul = self::$connection->query($sql);
            return $resul->fetchAll(PDO::FETCH_CLASS, get_called_class());
        } else {
            throw new Exception("Erro ao conectar com o banco de dados");
        }
    }

    /**
     * Method delete
     */
    public function delete()
    {
        if (isset($this->content[$this->idField])) {

            $sql = "delete from {$this->table} where {$this->idField} = {$this->content[$this->idField]}";

            if (self::$connection = Connection::getInstance()) {
                return self::$connection->exec($sql);
            } else {
                throw new Exception("Não conexão com o banco de dados");
            }
        }
    }

    /**
     * Impredindo valores nulos, vazios e etc;
     */
    private function format($value)
    {
        if (is_string($value) && !empty($value)) {
            return "'" . addslashes($value) . "'";
        } else if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        } else if ($value !== '') {
            return $value;
        } else {
            return "NULL";
        }
    }


    private function convertContent()
    {
        $newContent = array();
        foreach ($this->content as $key => $value) {
            if (is_scalar($value)) {
                $newContent[$key] = $this->format($value);
            }
        }
        return $newContent;
    }
}
