<?php
/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
define('DB_SERVER', getenv('DATABASE_SERVER'));

define('DB_USERNAME', getenv('DATABASE_USERNAME'));
define('DB_PASSWORD', getenv('DATABASE_PASSWORD'));

//define('DB_NAME', 'u241458058_case');
define('DB_NAME', 'casemanager');

define('SERVER_NAME', $_SERVER['SERVER_NAME']);
define('HTTP_HOST', $_SERVER['HTTP_HOST']);
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

if(HTTP_HOST == 'localhost'){
	define('ROOT_PATH', DOCUMENT_ROOT. "/casemanager");
	define('ROOT_URL', "http://".HTTP_HOST."/casemanager");
}
else{
	define('ROOT_PATH', DOCUMENT_ROOT);
	define('ROOT_URL', "https://".HTTP_HOST);
	//define('ROOT_PATH', DOCUMENT_ROOT. "/casemanager");
	//define('ROOT_URL', "http://".HTTP_HOST."/casemanager");

}

/* Attempt to connect to MySQL database */
// $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
$link = mysqli_connect('localhost', 'root', '', 'casemanager');

// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$gmailid = 'dev9@sovratec.com'; // YOUR gmail email
$gmailpassword = 'DevValidationJan2022!'; // YOUR gmail password
$gmailusername = 'dev9@sovratec.com'; // YOUR gmail User name

define('MAILER_ID', 'dev9@sovratec.com');

?>