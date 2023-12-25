<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Kiểm tra xem có tham số userId được chuyển đến hay không
    if (isset($_GET["userId"])) {
        $userId = intval($_GET["userId"]);

        if ($dbCon) {
            $query = "SELECT * FROM [dbo].[User] WHERE id = ?";
            $params = array($userId);
            $stmt = sqlsrv_prepare($dbCon, $query, $params);

            if ($stmt && sqlsrv_execute($stmt)) {
                $userData = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

                if ($userData) {
                    $userData['profile_image'] = 'http://10.0.2.2/api/' . $userData['profile_image'];
                    $response['status'] = 'OK';
                    $response['data'] = $userData;
                    $response['message'] = 'User retrieved successfully';
                } else {
                    $response['status'] = 'NOT OK';
                    $response['message'] = 'User not found';
                }
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
        $response['message'] = 'Missing userId parameter';
    }
} else {
    $response['status'] = 'NOT OK';
    $response['message'] = 'Invalid request method';
}

if (!isset($response['data'])) {
    $response['data'] = null;
}

echo json_encode($response);
?>
