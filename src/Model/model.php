<?php
class model
{
    public function getConnection($dbuser = "", $dbpass = "")
    {
        $options = [
            PDO::ATTR_CASE => PDO::CASE_LOWER,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_TO_STRING,
        ];
        
        try {
            return new PDO("", 
            $dbuser, $dbpass, $options);
        } catch (PDOException $e) {
            return null;
        }
    }
    
    private function bindParams($stid, $params, $withType = false)
    {
        foreach ($params as $key => &$value) {
            if (! $withType) {
                $stid->bindParam($key, $value);
                continue;
            }
            switch (gettype($value)) {
                case 'integer' : $type = PDO::PARAM_INT; break;
                case 'bool'    : $type = PDO::PARAM_BOOL; break;
                case 'string'  : $type = PDO::PARAM_STR; break; 
                case 'NULL'    : $type = PDO::PARAM_NULL; break;
                default        : continue 2;
            }
            $stid->bindParam($key, $value, $type);
        }
    }

    public function doPing($dbuser, $dbpass)
    {
        $conn = $this->getConnection($dbuser, $dbpass);

        if (null === $conn) {
            return false;
        }

        return true;
    }

    public function executeQuery($query, $params = [])
    {
        $conn = $this->getConnection();

        if (null === $conn) {
            return [];
        }

        $stid = $conn->prepare($query);
        $this->bindParams($stid, $params);

        try {
            $stid->execute();
        } catch (Exception $e) {
            // var_dump($params);
            // echo "\n";
            // var_dump($stid->errorInfo());
            return [];
        }

        $rows = $stid->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    public function executeUpdate($query, $params = [])
    {
        $conn = $this->getConnection();

        if (null === $conn) {
            return false;
        }

        $stid = $conn->prepare($query);
        $this->bindParams($stid, $params);
        // var_dump($stid->debugDumpParams());
        try {
            $stid->execute();
        } catch (Exception $e) {
            // var_dump($params);
            // echo "\n";
            // var_dump($stid->errorInfo());
            return false;
        }

        return true;
    }

    public function executeUpdateCautious($query, $params = [])
    {
        $conn = $this->getConnection();

        if (null === $conn) {
            return false;
        }

        $stid = $conn->prepare($query);
        $this->bindParams($stid, $params);
        // var_dump($stid->debugDumpParams());
        try {
            $stid->execute();
        } catch (Exception $e) {
            // var_dump($params);
            // echo "\n";
            // var_dump($stid->errorInfo());
            return false;
        }

        return $stid->rowCount();
    }
}