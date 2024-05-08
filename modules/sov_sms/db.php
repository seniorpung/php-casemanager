<?php
$mysql_response = array();
$mysql_status ="";
$mysql_Connection=null;
$api_key='Ir77wzSdTdq9AEB9T53tKg==';
$from_no='18643329897';

mysqlConnect();

function mysqlConnect()
{
    global $mysql_Connection, $mysql_response;

    $mysql_Connection = new mysqli("localhost","cvusr","November2022!1234","casemanager");
    if($mysql_Connection->connect_error)
    {
        $mysql_Connection[] = 'Connect Error ('. $mysql_Connection ->connect_errno.')'
        .$mysql_Connection->connect_error;

        echo json_encode($mysql_Connection);
        die();
    }
}

function mysqlQuery($query)
{
    global $mysql_Connection, $mysql_response, $mysql_status;

    $results = false;

    if ($mysql_Connection == null)
    {
        $mysql_status = "No databse connection active";
        return $results;
    }

    if (!($results = $mysql_Connection->query($query))) {
        $mysql_response[] = "Query Error {$mysql_Connection->errno}:{$mysql_Connection->error}";
    }

    return $results;
}

$query="SELECT * FROM db";
    $result=mysqlQuery($query);