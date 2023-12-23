<?php
// PHP Data Objects(PDO) Sample Code:
try {
    $conn = new PDO("sqlsrv:server = tcp:flashcard.database.windows.net,1433; Database = FlashCard", "final", "Deptraiso1nhabe");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
    print("Error connecting to SQL Server.");
    die(print_r($e));
}

// SQL Server Extension Sample Code:
$connectionInfo = array("UID" => "final", "pwd" => "Deptraiso1nhabe", "Database" => "FlashCard", "LoginTimeout" => 30, "Encrypt" => 1, "TrustServerCertificate" => 0);
$serverName = "tcp:flashcard.database.windows.net,1433";
$dbCon = sqlsrv_connect($serverName, $connectionInfo);
?>