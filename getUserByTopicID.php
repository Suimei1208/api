<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "GET" || $_SERVER["REQUEST_METHOD"] == "POST") {
    $topicID = isset($_REQUEST["topicID"]) ? intval($_REQUEST["topicID"]) : null;

    if ($topicID !== null) {
        if ($dbCon) {
            // Truy vấn lấy ownerID từ Topic
            $topicQuery = "SELECT ownerID FROM [dbo].[Topic] WHERE id = ?";
            $topicParams = array($topicID);
            $topicStmt = sqlsrv_prepare($dbCon, $topicQuery, $topicParams);

            if ($topicStmt && sqlsrv_execute($topicStmt)) {
                $ownerID = null;

                while ($row = sqlsrv_fetch_array($topicStmt, SQLSRV_FETCH_ASSOC)) {
                    $ownerID = $row['ownerID'];
                }

                if ($ownerID !== null) {
                    // Truy vấn lấy thông tin người sở hữu từ User
                    $userQuery = "SELECT * FROM [dbo].[User] WHERE id = ?";
                    $userParams = array($ownerID);
                    $userStmt = sqlsrv_prepare($dbCon, $userQuery, $userParams);

                    if ($userStmt && sqlsrv_execute($userStmt)) {
                        $userData = sqlsrv_fetch_array($userStmt, SQLSRV_FETCH_ASSOC);

                        if ($userData) {
                            if($userData['profile_image'] != null){
                                $userData['profile_image'] = 'http://10.0.2.2/api/' . $userData['profile_image'];
                            }
                            $response['status'] = 'OK';
                            $response['data'] = $userData;
                            $response['message'] = 'User information retrieved successfully';
                        } else {
                            $response['status'] = 'NOT OK';
                            $response['message'] = 'User not found';
                        }
                    } else {
                        $response['status'] = 'NOT OK';
                        $response['message'] = 'Error executing user query: ' . print_r(sqlsrv_errors(), true);
                    }

                    if ($userStmt) {
                        sqlsrv_free_stmt($userStmt);
                    }
                } else {
                    $response['status'] = 'NOT OK';
                    $response['message'] = 'OwnerID not found for the specified topic';
                }
            } else {
                $response['status'] = 'NOT OK';
                $response['message'] = 'Error executing topic query: ' . print_r(sqlsrv_errors(), true);
            }

            if ($topicStmt) {
                sqlsrv_free_stmt($topicStmt);
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
        $response['message'] = 'Invalid data received. Topic ID is required.';
    }
} else {
    $response['status'] = 'NOT OK';
    $response['message'] = 'Invalid request method';
}

if (!isset($response['data'])) {
    $response['data'] = null;
}

echo json_encode($response);
?>
