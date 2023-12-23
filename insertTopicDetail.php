<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $topicID = isset($_POST["topicID"]) ? intval($_POST["topicID"]) : null;
    $userID = isset($_POST["userID"]) ? intval($_POST["userID"]) : null;

    if ($topicID !== null && $userID !== null) {
        if ($dbCon) {
            // Kiểm tra xem TopicDetail đã tồn tại chưa
            $checkQuery = "SELECT * FROM [dbo].[TopicDetail] WHERE TopicID = ? AND UserID = ?";
            $checkParams = array($topicID, $userID);
            $checkStmt = sqlsrv_prepare($dbCon, $checkQuery, $checkParams);

            if ($checkStmt && sqlsrv_execute($checkStmt)) {
                $existingTopicDetail = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

                if ($existingTopicDetail) {
                    $response['status'] = 'NOT OK';
                    $response['message'] = 'TopicDetail already exists';
                } else {
                    // Thêm TopicDetail mới
                    $insertQuery = "INSERT INTO [dbo].[TopicDetail] (TopicID, UserID) VALUES (?, ?)";
                    $insertParams = array($topicID, $userID);
                    $insertStmt = sqlsrv_prepare($dbCon, $insertQuery, $insertParams);

                    if ($insertStmt && sqlsrv_execute($insertStmt)) {
                        $response['status'] = 'OK';
                        $response['message'] = 'TopicDetail inserted successfully';
                    } else {
                        $response['status'] = 'NOT OK';
                        $response['message'] = 'Error executing TopicDetail insert query';
                    }
                    sqlsrv_free_stmt($insertStmt);
                }
                sqlsrv_free_stmt($checkStmt);
            } else {
                $response['status'] = 'NOT OK';
                $response['message'] = 'Error checking existing TopicDetail';
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

echo json_encode($response);
?>
