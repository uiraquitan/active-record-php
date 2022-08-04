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


    public function save()
    {
        $newContent = $this->convertContent();

        if (isset($this->content[$this->idField])) {
            $sets = [];

            foreach ($newContent as $key => $value) {
                if ($key === $this->idField) {
                    continue;
                }
                $sets[] = "{$key} = {$value}";
            }
            $sql = "update {$this->table} set " . implode(", ", $sets) . " where id = {$this->content[$this->idField]}";
            $sqlM = "Atualizado com sucesso";
        } else {
            $sql = "insert into {$this->table} (" . implode(", ", array_keys($newContent)) . ") values (" . implode(", ", array_values($newContent)) . ")";
            $sqlM = "Cadastrado com sucesso";
        }
        if ($connection = Connection::getInstance()) {
            return $connection->exec($sql);
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
