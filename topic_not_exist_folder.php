<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy folderID từ dữ liệu POST
    $folderID = isset($_POST["folderID"]) ? intval($_POST["folderID"]) : null;

    if ($folderID !== null && $dbCon) {
        $query = "
            SELECT DISTINCT [Topic].[topicID], [Topic].[topicName]
            FROM [dbo].[Topic]
            LEFT JOIN [dbo].[FolderDetail] ON [Topic].[topicID] = [FolderDetail].[topicID] AND [FolderDetail].[folderID] = ?
            WHERE [FolderDetail].[topicID] IS NULL;
        ";

        $params = array($folderID);
        $stmt = sqlsrv_query($dbCon, $query, $params);

        if ($stmt) {
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

        sqlsrv_free_stmt($stmt);
        sqlsrv_close($dbCon);
    } else {
        $response['status'] = 'NOT OK';
        $response['message'] = 'Invalid data or error connecting to SQL Server';
    }
} else {
    $response['status'] = 'NOT OK';
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
