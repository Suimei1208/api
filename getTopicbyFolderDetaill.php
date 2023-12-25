<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $folderID = isset($_POST["folderID"]) ? intval($_POST["folderID"]) : null;

    if ($folderID !== null) {
        if ($dbCon) {
            // Truy vấn SQL để lấy các chủ đề trong thư mục từ bảng FolderDetail và Topic
            $query = "SELECT T.* FROM [dbo].[Topic] T
                      JOIN [dbo].[FolderDetail] FD ON T.id = FD.topicID
                      WHERE FD.folderID = ?";
            $params = array($folderID);
            $stmt = sqlsrv_prepare($dbCon, $query, $params);

            if ($stmt && sqlsrv_execute($stmt)) {
                $topics = array();

                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $topics[] = $row;
                }

                $response['status'] = 'OK';
                $response['data'] = $topics;
                $response['message'] = 'Topics retrieved successfully';
            } else {
                $response['status'] = 'NOT OK';
                $response['message'] = 'Error executing query: ' . print_r(sqlsrv_errors(), true);
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
