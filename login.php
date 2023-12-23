<?php
header('Content-Type: application/json');
require_once('connection.php');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    if ($dbCon) {
        $query = "SELECT * FROM [dbo].[User] WHERE username = ? AND password = ?";
        $params = array($username, $password);
        $stmt = sqlsrv_prepare($dbCon, $query, $params);

        if ($stmt) {
            if (sqlsrv_execute($stmt)) {
                $userData = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

                if ($userData) {
                    // Lấy đường dẫn tới ảnh từ thư mục 'avatar' trong XAMPP
                    $userData['profile_image'] = 'http://10.0.2.2/api/' . $userData['profile_image'];

                    $response['status'] = 'OK';
                    $response['data'] = $userData;
                    $response['message'] = 'Load success';
                } else {
                    $response['status'] = 'Invalid';
                    $response['data'] = null;
                    $response['message'] = 'User not found';
                }
            } else {
                echo "Query: " . $query . " with parameters: " . implode(", ", $params);
                $response['status'] = 'NOT OK';
                $response['message'] = 'Error executing query';
            }
        } else {
            $response['status'] = 'NOT OK';
            $response['message'] = 'Error preparing query';
        }
        sqlsrv_free_stmt($stmt);
        // Kiểm tra và đóng kết nối
        if ($dbCon) {
            sqlsrv_close($dbCon);
        }
    } else {
        $response['status'] = false;
        $response['message'] = 'Error connecting to SQL Server';
    }
}

// Kiểm tra nếu 'data' đã được đặt giá trị
if (!isset($response['data'])) {
    $response['data'] = null;
}

echo json_encode(array('status' => $response['status'], 'data' => $response['data'], 'message' => $response['message']));
?>
