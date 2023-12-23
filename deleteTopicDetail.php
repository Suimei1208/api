<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $topicID = isset($_POST["topicID"]) ? intval($_POST["topicID"]) : null;
    $userID = isset($_POST["userID"]) ? intval($_POST["userID"]) : null;

    if ($topicID !== null && $userID !== null) {
        if ($dbCon) {
            // Xóa TopicDetail dựa trên TopicID và UserID
            $deleteQuery = "DELETE FROM [dbo].[TopicDetail] WHERE TopicID = ? AND UserID = ?";
            $deleteParams = array($topicID, $userID);
            $deleteStmt = sqlsrv_prepare($dbCon, $deleteQuery, $deleteParams);

            if ($deleteStmt && sqlsrv_execute($deleteStmt)) {
                $response['status'] = 'OK';
                $response['message'] = 'TopicDetail deleted successfully';
            } else {
                $response['status'] = 'NOT OK';
                $response['message'] = 'Error executing TopicDetail delete query';
            }
            sqlsrv_free_stmt($deleteStmt);

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
