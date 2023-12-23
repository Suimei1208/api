<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $folderID = isset($_POST["folderID"]) ? intval($_POST["folderID"]) : null;
    $topicID = isset($_POST["topicID"]) ? intval($_POST["topicID"]) : null;

    if ($folderID !== null && $topicID !== null) {
        if ($dbCon) {
            // Xóa FolderDetail dựa trên folderID và topicID
            $deleteQuery = "DELETE FROM [dbo].[FolderDetail] WHERE folderID = ? AND topicID = ?";
            $deleteParams = array($folderID, $topicID);
            $deleteStmt = sqlsrv_prepare($dbCon, $deleteQuery, $deleteParams);

            if ($deleteStmt && sqlsrv_execute($deleteStmt)) {
                $response['status'] = 'OK';
                $response['message'] = 'FolderDetail deleted successfully';
            } else {
                $response['status'] = 'NOT OK';
                $response['message'] = 'Error executing FolderDetail delete query';
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
