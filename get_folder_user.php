<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = isset($_POST["userID"]) ? intval($_POST["userID"]) : null;

    if ($userID !== null) {
        if ($dbCon) {
            $query = "SELECT * FROM [dbo].[Folder] WHERE userID = ?";
            $params = array($userID);
            $stmt = sqlsrv_prepare($dbCon, $query, $params);
            echo json_encode($stmt);


            if ($stmt && sqlsrv_execute($stmt)) {
                $result = array();

                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $infoQuery = "SELECT * FROM [dbo].[Folder] WHERE userID = ?";
                    $infoParams = array($userID);
                    $infoStmt = sqlsrv_prepare($dbCon, $infoQuery, $infoParams);

                    if ($infoStmt && sqlsrv_execute($infoStmt)) {
                        $infoResult = array();

                    } else {
                        $response['status'] = 'NOT OK';
                        $response['message'] = 'Error executing additional info query';
                        echo json_encode($response);
                        exit(); // Exit if there is an error retrieving additional info
                    }

                    sqlsrv_free_stmt($infoStmt);
                    $result[] = $row;
                }

                $response['status'] = 'OK';
                $response['message'] = 'Records retrieved successfully';
                $response['data'] = $result;
            } else {
                $response['status'] = 'NOT OK';
                $response['message'] = 'Error executing query to retrieve records';
            }

            sqlsrv_free_stmt($stmt);
            sqlsrv_close($dbCon);
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

echo json_encode($response);
?>