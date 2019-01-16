<?php
class DataService
{
    private $mysql;

    function __construct(
        $database_name,
        $username,
        $password,
        $host
    )
    {
        $this->mysql = mysqli_connect($host, $username, $password, $database_name);
        
        if (!$this->mysql)
            throw new Exception('Database connection error: ' . mysqli_connect_error());
    }
    
    public function escape(
        $str
    )
    {
        return mysqli_real_escape_string($this->mysql, $str);
    }

    public function perform_query(
        $query
    )
    {
        $result = mysqli_query($this->mysql, $query);
        
        if (mysqli_error($this->mysql) != '')
        {
            print_r(mysqli_error($this->mysql));
            return null;
        }
        
        return $result;
    }
    
    public function retrieve_row(
        $query_result
    )
    {
        $result = $this->retrieve_rows($query_result);
        
        if (count($result) != 1)
            return null;
        
        return $result[0];
    }
    
    public function retrieve_rows(
        $query_result
    )
    {
        $result = array();
        $index = 0;
        
        while ($row = mysqli_fetch_assoc($query_result))
        {
            $result[$index] = new stdClass();
            
            foreach ($row as $column => $value)
                $result[$index]->{$column} = $this->clean($value);

            $index++;
        }

        return $result;
    }
    
    private function clean(
        $str
    )
    {
        if (is_string($str))
            if (!mb_detect_encoding($str, 'UTF-8', TRUE))
                $str = utf8_encode($str);

        return $str;
    }
}