<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = isset($_POST["userID"]) ? intval($_POST["userID"]) : null;

    if ($userID !== null) {
        if ($dbCon) {
            // Truy vấn cơ sở dữ liệu để lấy TOP dựa trên UserID
            $query = "SELECT * FROM [dbo].[TopicDetail] WHERE UserID = ?";
            $params = array($userID);
            $stmt = sqlsrv_prepare($dbCon, $query, $params);

            if ($stmt && sqlsrv_execute($stmt)) {
                $result = array();

                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $folderID = $row['TopicID'];
                    $infoQuery = "SELECT * FROM [dbo].[Topic] WHERE id = ?";
                    $infoParams = array($folderID);
                    $infoStmt = sqlsrv_prepare($dbCon, $infoQuery, $infoParams);

                    if ($infoStmt && sqlsrv_execute($infoStmt)) {
                        $infoResult = array();

                        while ($infoRow = sqlsrv_fetch_array($infoStmt, SQLSRV_FETCH_ASSOC)) {
                            $infoResult[] = $infoRow;
                        }

                        $row['additionalInfo'] = $infoResult;
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
