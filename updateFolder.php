<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $folderID = isset($_POST["folderID"]) ? intval($_POST["folderID"]) : null;
    $folderName = isset($_POST["folderName"]) ? $_POST["folderName"] : null;
    $folderDescription = isset($_POST["folderDescription"]) ? $_POST["folderDescription"] : null;

    if ($folderID !== null) {
        if ($dbCon) {
            $updateQuery = "UPDATE [dbo].[Folder] SET ";
            $updateParams = array();

            if ($folderName !== null) {
                $updateQuery .= "folderName = ?, ";
                $updateParams[] = $folderName;
            }

            if ($folderDescription !== null) {
                $updateQuery .= "folderDescription = ?, ";
                $updateParams[] = $folderDescription;
            }

            // Loại bỏ dấu ',' cuối cùng nếu có
            $updateQuery = rtrim($updateQuery, ', ');

            $updateQuery .= " WHERE folderID = ?";
            $updateParams[] = $folderID;

            $updateStmt = sqlsrv_prepare($dbCon, $updateQuery, $updateParams);

            if ($updateStmt && sqlsrv_execute($updateStmt)) {
                // Lấy dữ liệu mới sau khi cập nhật thành công
                $newDataQuery = "SELECT * FROM [dbo].[Folder] WHERE folderID = ?";
                $newDataParams = array($folderID);
                $newDataStmt = sqlsrv_prepare($dbCon, $newDataQuery, $newDataParams);

                if ($newDataStmt && sqlsrv_execute($newDataStmt)) {
                    $newFolderData = sqlsrv_fetch_array($newDataStmt, SQLSRV_FETCH_ASSOC);
                    $response['status'] = 'OK';
                    $response['data'] = $newFolderData;
                    $response['message'] = 'Folder updated successfully';
                } else {
                    $response['status'] = 'NOT OK';
                    $response['message'] = 'Error fetching updated folder data';
                }
                sqlsrv_free_stmt($newDataStmt);
            } else {
                $response['status'] = 'NOT OK';
                $response['message'] = 'Error executing folder update query';
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
