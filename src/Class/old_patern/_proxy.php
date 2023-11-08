<?php

interface MySqlDatabse{
    public function select();
    public function execute();
}
class RealMySqlDatabase{

    private $connection = null;

    public function __construct()
    {
        $connection = new PDO();
        // On est connecté
    }

    public function select(){

    }

    public function execute(){

    }
}

class ProxyMySQLDatabsase implements MySqlDatabse {

    private $realMySqlDatabase = null;

    public function select()
    {
        if($this->realMySqlDatabase == null){
            $this->realMySqlDatabase = new RealMySqlDatabase();
        }

        return $this->realMySqlDatabase->select();
    }

    public function execute()
    {
        if($this->realMySqlDatabase == null){
            $this->realMySqlDatabase = new RealMySqlDatabase();
        }

        return $this->realMySqlDatabase->execute();
    }
}