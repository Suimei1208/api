<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = isset($_POST["userID"]) ? $_POST["userID"] : null;
    $nameID = isset($_POST["nameID"]) ? $_POST["nameID"] : null;
    $folderDescription = isset($_POST["folderDescription"]) ? $_POST["folderDescription"] : null;

    if ($userID !== null) {
        if ($dbCon) {
            $insertQuery = "INSERT INTO [dbo].[Folder] (userID, nameID, folderDescription) VALUES (?, ?, ?)";
            $insertParams = array($userID, $nameID, $folderDescription);

            $insertStmt = sqlsrv_prepare($dbCon, $insertQuery, $insertParams);

            if ($insertStmt && sqlsrv_execute($insertStmt)) {
                $response['status'] = 'OK';
                $response['data'] = null;
                $response['message'] = 'Folder created successfully';
            } else {
                $response['status'] = 'NOT OK';
                $response['message'] = 'Error executing folder creation query';
            }

            if ($dbCon) {
                sqlsrv_close($dbCon);
            }
        } else {
            $response['status'] = 'NOT OK';
            $response['message'] = 'Error connecting to SQL Server';
        }
    } else {
        $response['status'] = 'NOT OK';
        $response['message'] = 'Invalid data received';
    }
} else {
    $response['status'] = 'NOT OK';
    $response['message'] = 'Invalid request method';
}

if (!isset($response['data'])) {
    $response['data'] = null;
}

echo json_encode(array('status' => $response['status'], 'data' => $response['data'], 'message' => $response['message']));
?>
