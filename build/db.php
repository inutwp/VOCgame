<?php
class Db
{
    public function __construct()
    {
        $db = GetInstance('database', true);

        $result = new MySQLi('p:'.$db['host'], $db['user'], $db['pass'], $db['db'], $db['port']) or error('db');

        $this->mysqli = $result;
        $this->ApiID = (int) $GLOBALS['ApiID'];
        $this->ClientID = (int) $GLOBALS['ClientID'];
    }

    public function GetApiKey()
    {
        $query = $this->mysqli->query("
        SELECT api_key
        FROM core_api_Key
        WHERE
            api_id = $this->ApiID AND
            client_id = $this->ClientID
        ");

        $query = $query->fetch_assoc();
        return $query['api_key'];
    }
}
