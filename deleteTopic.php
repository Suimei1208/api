<?php 
    header('Content-Type: application/json');

    require_once('connection.php');

    $response = array();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $topicName = isset($_POST["topicName"]) ? $_POST["topicName"] : null;

        if ($topicName !== null) {
            if ($dbCon) {
                $checkQuery = "SELECT * FROM [dbo].[Topic] WHERE topicName = ?";
                $checkParams = array($topicName);
                $checkStmt = sqlsrv_prepare($dbCon, $checkQuery, $checkParams);

                if ($checkStmt && sqlsrv_execute($checkStmt)) {
                    $existingTopic = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

                    if ($existingTopic) {
                        $deleteQuery = "DELETE FROM [dbo].[Topic] WHERE topicName = ?";
                        $deleteParams = array($topicName);

                        $deleteStmt = sqlsrv_prepare($dbCon, $deleteQuery, $deleteParams);

                        if ($deleteStmt && sqlsrv_execute($deleteStmt)) {
                            $response['status'] = 'OK';
                            $response['data'] = null;
                            $response['message'] = 'Topic deleted successfully';
                        } else {
                            $response['status'] = 'NOT OK';
                            $response['message'] = 'Error executing topic deletion query';
                        }
                        sqlsrv_free_stmt($deleteStmt);
                    } else {
                        $response['status'] = 'Invalid';
                        $response['data'] = null;
                        $response['message'] = 'Topic not found';
                    }
                    sqlsrv_free_stmt($checkStmt);
                } else {
                    $response['status'] = 'NOT OK';
                    $response['message'] = 'Error checking existing topic';
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
