<?php 


// Database configuration
$db_host = 'localhost';
$db_name = 'opsinsyts';
$db_user = 'root';    
$db_password = '';    

// Create connection using MySQLi
$connection = new mysqli($db_host, $db_user, $db_password, $db_name,3306);

?>