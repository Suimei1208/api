<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

function deleteFolder($dbCon, $folderId) {
    $deleteQuery = "DELETE FROM [dbo].[Folder] WHERE folderID = ?";
    $deleteParams = array($folderId);

    $deleteStmt = sqlsrv_prepare($dbCon, $deleteQuery, $deleteParams);

    if ($deleteStmt && sqlsrv_execute($deleteStmt)) {
        return true; // Xóa thành công
    } else {
        // In chi tiết lỗi SQL
        if ($deleteStmt) {
            die(print_r(sqlsrv_errors(), true));
        }
        return false; // Lỗi khi xóa
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $folderId = isset($_POST["folder_id"]) ? $_POST["folder_id"] : null;

    if ($folderId !== null) {
        if ($dbCon) {
            $deleteResult = deleteFolder($dbCon, $folderId);

            if ($deleteResult) {
                $response['status'] = 'OK';
                $response['message'] = 'Folder deleted successfully';
            } else {
                $response['status'] = 'NOT OK';
                $response['message'] = 'Error deleting folder';
            }

            // Đóng kết nối
            if ($dbCon) {
                sqlsrv_close($dbCon);
            }
        } else {
            $response['status'] = 'NOT OK';
            $response['message'] = 'Error connecting to SQL Server';
        }
    } else {
        $response['status'] = 'NOT OK';
        $response['message'] = 'Invalid folder_id received';
    }
} else {
    $response['status'] = 'NOT OK';
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
