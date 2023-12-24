<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = isset($_POST["userID"]) ? intval($_POST["userID"]) : null;

    if ($userID !== null) {
        if ($dbCon) {
            // Truy vấn cơ sở dữ liệu để lấy TOP dựa trên UserID
            $query = "SELECT * FROM [dbo].[TopicDetail] WHERE UserID = ?";
            $params = array($userID);
            $stmt = sqlsrv_prepare($dbCon, $query, $params);

            if ($stmt && sqlsrv_execute($stmt)) {
                $result = array();

                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $result[] = $row;
                }

                $response['status'] = 'OK';
                $response['message'] = 'Top records retrieved successfully';
                $response['data'] = $result;
            } else {
                $response['status'] = 'NOT OK';
                $response['message'] = 'Error executing query to retrieve top records';
            }

            sqlsrv_free_stmt($stmt);
            sqlsrv_close($dbCon);
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
