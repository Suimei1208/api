<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Tạo mảng để lưu trữ giá trị
    $topicData = array(
        'topicID' => isset($_POST["topicID"]) ? intval($_POST["topicID"]) : null,
        'topicName' => isset($_POST["topicName"]) ? $_POST["topicName"] : null,
        'description' => isset($_POST["description"]) ? $_POST["description"] : null,
        'isPublic' => isset($_POST["isPublic"]) ? $_POST["isPublic"] : null,
        'ownerID' => isset($_POST["ownerID"]) ? $_POST["ownerID"] : null
    );

    $topicData = array_filter($topicData, function ($value) {
        return $value !== null;
    });

    if ($dbCon) {
        // Xây dựng câu lệnh UPDATE
        $updateQuery = "UPDATE [dbo].[Topic] SET ";
        $updateParams = array();

        // Xây dựng phần SET của câu lệnh UPDATE và thêm tham số vào mảng $updateParams
        foreach ($topicData as $key => $value) {
            if ($key !== 'topicID') {
                $updateQuery .= "$key = ?, ";
                $updateParams[] = $value;
            }
        }

        $updateQuery = rtrim($updateQuery, ", ");  // Loại bỏ dấu phẩy cuối cùng
        $updateQuery .= " WHERE id = ?";
        $updateParams[] = $topicData['topicID'];

        // Thực hiện câu lệnh UPDATE
        $updateStmt = sqlsrv_prepare($dbCon, $updateQuery, $updateParams);

        if ($updateStmt && sqlsrv_execute($updateStmt)) {
            // Cập nhật thành công, lấy dữ liệu mới sau khi cập nhật
            $selectQuery = "SELECT * FROM [dbo].[Topic] WHERE id = ?";
            $selectParams = array($topicData['topicID']);
            $selectStmt = sqlsrv_prepare($dbCon, $selectQuery, $selectParams);
        
            if ($selectStmt && sqlsrv_execute($selectStmt)) {
                $updatedTopic = sqlsrv_fetch_array($selectStmt, SQLSRV_FETCH_ASSOC);
        
                $response['status'] = 'OK';
                $response['data'] = $updatedTopic;
                $response['message'] = 'Topic updated successfully';
            } else {
                $response['status'] = 'NOT OK';
                $response['message'] = 'Error fetching updated topic: ' . print_r(sqlsrv_errors(), true);
            }
        
            sqlsrv_free_stmt($selectStmt);
        } else {
            $response['status'] = 'NOT OK';
            $response['message'] = 'Error executing topic update query: ' . print_r(sqlsrv_errors(), true);
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
    $response['message'] = 'Invalid request method';
}

if (!isset($response['data'])) {
    $response['data'] = null;
}
echo json_encode(array('status' => $response['status'], 'data' => $response['data'], 'message' => $response['message']));
?>
