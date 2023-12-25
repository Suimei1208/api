<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $topicid = isset($_POST["topicid"]) ? $_POST["topicid"] : null;

    if ($topicid !== null) {
        if ($dbCon) {
            // Trước khi xóa chủ đề, xóa từ vựng liên quan trong bảng Vocabulary
            $deleteVocabularyQuery = "DELETE FROM [dbo].[Vocabulary] WHERE topicID = ?";
            $deleteVocabularyParams = array($topicid);
            $deleteVocabularyStmt = sqlsrv_prepare($dbCon, $deleteVocabularyQuery, $deleteVocabularyParams);

            if ($deleteVocabularyStmt && sqlsrv_execute($deleteVocabularyStmt)) {
                // Sau khi xóa từ vựng, xóa các chi tiết của chủ đề trong bảng TopicDetail
                $deleteDetailQuery = "DELETE FROM [dbo].[TopicDetail] WHERE TopicID = ?";
                $deleteDetailParams = array($topicid);
                $deleteDetailStmt = sqlsrv_prepare($dbCon, $deleteDetailQuery, $deleteDetailParams);

                if ($deleteDetailStmt && sqlsrv_execute($deleteDetailStmt)) {
                    // Tiến hành xóa chủ đề sau khi đã xóa các chi tiết và từ vựng thành công
                    $deleteTopicQuery = "DELETE FROM [dbo].[Topic] WHERE id = ?";
                    $deleteTopicParams = array($topicid);
                    $deleteTopicStmt = sqlsrv_prepare($dbCon, $deleteTopicQuery, $deleteTopicParams);

                    if ($deleteTopicStmt && sqlsrv_execute($deleteTopicStmt)) {
                        $response['status'] = 'OK';
                        $response['data'] = null;
                        $response['message'] = 'Topic and details deleted successfully';
                    } else {
                        $response['status'] = 'NOT OK';
                        $response['message'] = 'Error executing topic deletion query: ' . print_r(sqlsrv_errors(), true);
                    }
                    sqlsrv_free_stmt($deleteTopicStmt);
                } else {
                    $response['status'] = 'NOT OK';
                    $response['message'] = 'Error executing topic detail deletion query: ' . print_r(sqlsrv_errors(), true);
                }
                sqlsrv_free_stmt($deleteDetailStmt);
            } else {
                $response['status'] = 'NOT OK';
                $response['message'] = 'Error executing vocabulary deletion query: ' . print_r(sqlsrv_errors(), true);
            }
            sqlsrv_free_stmt($deleteVocabularyStmt);

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
