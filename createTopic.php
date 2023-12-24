<?php 
    header('Content-Type: application/json');

    require_once('connection.php');

    $response = array();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $topicName = isset($_POST["topicName"]) ? $_POST["topicName"] : null;
        $description = isset($_POST["description"]) ? $_POST["description"] : null;
        $isPublic = isset($_POST["isPublic"]) ? $_POST["isPublic"] : null;
        $ownerID = isset($_POST["ownerID"]) ? $_POST["ownerID"] : null;

        if ($topicName !== null && $description !== null && $isPublic !== null && $ownerID !== null) {
            if ($dbCon) {
                $checkQuery = "SELECT * FROM [dbo].[Topic] WHERE topicName = ? AND ownerID = ?";
                $checkParams = array($topicName, $ownerID);
                $checkStmt = sqlsrv_prepare($dbCon, $checkQuery, $checkParams);

                if ($checkStmt && sqlsrv_execute($checkStmt)) {
                    $existingTopic = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

                    if ($existingTopic) {
                        $response['status'] = 'Invalid';
                        $response['data'] = null;
                        $response['message'] = 'Topic with the same name already exists';
                    } else {                  
                        $insertQuery = "INSERT INTO [dbo].[Topic] (topicName, description, isPublic, ownerID) VALUES (?, ?, ?, ?)";
                        $insertParams = array($topicName, $description, $isPublic, $ownerID);

                        $insertStmt = sqlsrv_prepare($dbCon, $insertQuery, $insertParams);

                        if ($insertStmt && sqlsrv_execute($insertStmt)) {
                            // Lấy dữ liệu của chủ đề vừa được tạo
                            $selectQuery = "SELECT * FROM [dbo].[Topic] WHERE topicName = ? AND ownerID = ?";
                            $selectParams = array($topicName, $ownerID);
                            $selectStmt = sqlsrv_prepare($dbCon, $selectQuery, $selectParams);

                            if ($selectStmt && sqlsrv_execute($selectStmt)) {
                                $createdTopic = sqlsrv_fetch_array($selectStmt, SQLSRV_FETCH_ASSOC);

                                $response['status'] = 'OK';
                                $response['data'] = $createdTopic;
                                $response['message'] = 'Topic created successfully';
                            } else {
                                $response['status'] = 'NOT OK';
                                $response['message'] = 'Error fetching created topic: ' . print_r(sqlsrv_errors(), true);
                            }

                            sqlsrv_free_stmt($selectStmt);
                        } else {
                            $response['status'] = 'NOT OK';
                            $response['message'] = 'Error executing topic creation query';
                        }

                        sqlsrv_free_stmt($insertStmt);                      
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
