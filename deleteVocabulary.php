<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy ID của từ vựng cần xóa từ dữ liệu POST
    $vocabID = isset($_POST["vocabID"]) ? intval($_POST["vocabID"]) : null;

    if ($vocabID !== null) {
        if ($dbCon) {
            // Kiểm tra xem từ vựng có tồn tại không
            $checkQuery = "SELECT * FROM [dbo].[Vocabulary] WHERE vocabID = ?";
            $checkParams = array($vocabID);
            $checkStmt = sqlsrv_prepare($dbCon, $checkQuery, $checkParams);

            if ($checkStmt && sqlsrv_execute($checkStmt)) {
                $existingVocabulary = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

                if ($existingVocabulary) {
                    // Thực hiện câu lệnh DELETE để xóa từ vựng
                    $deleteQuery = "DELETE FROM [dbo].[Vocabulary] WHERE vocabID = ?";
                    $deleteParams = array($vocabID);
                    $deleteStmt = sqlsrv_prepare($dbCon, $deleteQuery, $deleteParams);

                    if ($deleteStmt && sqlsrv_execute($deleteStmt)) {
                        $response['status'] = 'OK';
                        $response['data'] = null;
                        $response['message'] = 'Vocabulary deleted successfully';
                    } else {
                        $response['status'] = 'NOT OK';
                        $response['message'] = 'Error executing vocabulary deletion query: ' . print_r(sqlsrv_errors(), true);
                    }
                    sqlsrv_free_stmt($deleteStmt);
                } else {
                    $response['status'] = 'NOT OK';
                    $response['message'] = 'Invalid vocabID. Vocabulary not found.';
                }
                sqlsrv_free_stmt($checkStmt);
            } else {
                $response['status'] = 'NOT OK';
                $response['message'] = 'Error checking existing vocabulary';
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
        $response['message'] = 'Invalid data received. vocabID is required.';
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
