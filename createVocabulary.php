<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Tạo mảng để lưu trữ giá trị
    $vocabularyData = array(
        'vocabulary' => isset($_POST["vocabulary"]) ? $_POST["vocabulary"] : null,
        'meaning' => isset($_POST["meaning"]) ? $_POST["meaning"] : null,
        'topicID' => isset($_POST["topicID"]) ? intval($_POST["topicID"]) : null
    );

    // Kiểm tra xem có dữ liệu từ vựng và ý nghĩa hay không
    if ($vocabularyData['vocabulary'] !== null && $vocabularyData['meaning'] !== null) {
        if ($dbCon) {
            $checkTopicQuery = "SELECT * FROM [dbo].[Topic] WHERE id = ?";
            $checkTopicParams = array($vocabularyData['topicID']);
            $checkTopicStmt = sqlsrv_prepare($dbCon, $checkTopicQuery, $checkTopicParams);

            if ($checkTopicStmt && sqlsrv_execute($checkTopicStmt)) {
                $existingTopic = sqlsrv_fetch_array($checkTopicStmt, SQLSRV_FETCH_ASSOC);

                if ($existingTopic) {
                    // Thực hiện câu lệnh INSERT để thêm từ vựng mới
                    $insertQuery = "INSERT INTO [dbo].[Vocabulary] (vocabulary, meaning, topicID) VALUES (?, ?, ?)";
                    $insertParams = array($vocabularyData['vocabulary'], $vocabularyData['meaning'], $vocabularyData['topicID']);
                    $insertStmt = sqlsrv_prepare($dbCon, $insertQuery, $insertParams);

                    if ($insertStmt && sqlsrv_execute($insertStmt)) {
                        $response['status'] = 'OK';
                        $response['data'] = null;
                        $response['message'] = 'Vocabulary created successfully';
                    } else {
                        $response['status'] = 'NOT OK';
                        $response['message'] = 'Error executing vocabulary creation query: ' . print_r(sqlsrv_errors(), true);
                    }
                    sqlsrv_free_stmt($insertStmt);
                } else {
                    $response['status'] = 'NOT OK';
                    $response['message'] = 'Invalid topicID. Topic not found.';
                }
                sqlsrv_free_stmt($checkTopicStmt);
            } else {
                $response['status'] = 'NOT OK';
                $response['message'] = 'Error checking existing topic for vocabulary';
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
        $response['message'] = 'Invalid data received. Vocabulary and meaning are required.';
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
