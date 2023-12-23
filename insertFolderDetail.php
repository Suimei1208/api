<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $folderID = isset($_POST["folderID"]) ? intval($_POST["folderID"]) : null;
    $topicID = isset($_POST["topicID"]) ? intval($_POST["topicID"]) : null;

    if ($folderID !== null && $topicID !== null) {
        if ($dbCon) {
            // Kiểm tra xem FolderDetail đã tồn tại chưa
            $checkQuery = "SELECT * FROM [dbo].[FolderDetail] WHERE folderID = ? AND topicID = ?";
            $checkParams = array($folderID, $topicID);
            $checkStmt = sqlsrv_prepare($dbCon, $checkQuery, $checkParams);

            if ($checkStmt && sqlsrv_execute($checkStmt)) {
                $existingFolderDetail = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

                if ($existingFolderDetail) {
                    $response['status'] = 'NOT OK';
                    $response['message'] = 'FolderDetail already exists';
                } else {
                    // Thêm FolderDetail mới
                    $insertQuery = "INSERT INTO [dbo].[FolderDetail] (folderID, topicID) VALUES (?, ?)";
                    $insertParams = array($folderID, $topicID);
                    $insertStmt = sqlsrv_prepare($dbCon, $insertQuery, $insertParams);

                    if ($insertStmt && sqlsrv_execute($insertStmt)) {
                        $response['status'] = 'OK';
                        $response['message'] = 'FolderDetail inserted successfully';
                    } else {
                        $response['status'] = 'NOT OK';
                        $response['message'] = 'Error executing FolderDetail insert query';
                    }
                    sqlsrv_free_stmt($insertStmt);
                }
                sqlsrv_free_stmt($checkStmt);
            } else {
                $response['status'] = 'NOT OK';
                $response['message'] = 'Error checking existing FolderDetail';
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
