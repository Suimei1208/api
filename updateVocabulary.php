<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy thông tin cần cập nhật từ dữ liệu POST
    $vocabID = isset($_POST["vocabID"]) ? intval($_POST["vocabID"]) : null;
    $newVocabulary = isset($_POST["newVocabulary"]) ? $_POST["newVocabulary"] : null;
    $newMeaning = isset($_POST["newMeaning"]) ? $_POST["newMeaning"] : null;

    if ($vocabID !== null && ($newVocabulary !== null || $newMeaning !== null)) {
        if ($dbCon) {
            // Kiểm tra xem từ vựng có tồn tại không
            $checkQuery = "SELECT * FROM [dbo].[Vocabulary] WHERE vocabID = ?";
            $checkParams = array($vocabID);
            $checkStmt = sqlsrv_prepare($dbCon, $checkQuery, $checkParams);

            if ($checkStmt && sqlsrv_execute($checkStmt)) {
                $existingVocabulary = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

                if ($existingVocabulary) {
                    // Xây dựng câu lệnh UPDATE dựa trên thông tin mới
                    $updateQuery = "UPDATE [dbo].[Vocabulary] SET ";
                    $updateParams = array();

                    if ($newVocabulary !== null) {
                        $updateQuery .= "vocabulary = ?, ";
                        $updateParams[] = $newVocabulary;
                    }

                    if ($newMeaning !== null) {
                        $updateQuery .= "meaning = ?, ";
                        $updateParams[] = $newMeaning;
                    }

                    $updateQuery = rtrim($updateQuery, ", ");  // Loại bỏ dấu phẩy cuối cùng
                    $updateQuery .= " WHERE vocabID = ?";
                    $updateParams[] = $vocabID;

                    // Thực hiện câu lệnh UPDATE
                    $updateStmt = sqlsrv_prepare($dbCon, $updateQuery, $updateParams);

                    if ($updateStmt && sqlsrv_execute($updateStmt)) {
                        $response['status'] = 'OK';
                        $response['data'] = null;
                        $response['message'] = 'Vocabulary updated successfully';
                    } else {
                        $response['status'] = 'NOT OK';
                        $response['message'] = 'Error executing vocabulary update query: ' . print_r(sqlsrv_errors(), true);
                    }
                    sqlsrv_free_stmt($updateStmt);
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
        $response['message'] = 'Invalid data received. vocabID and at least one of newVocabulary or newMeaning are required.';
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
