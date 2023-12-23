<?php 
    header('Content-Type: application/json');

    require_once('connection.php');

    $response = array();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = isset($_POST["username"]) ? $_POST["username"] : null;
        $password = isset($_POST["password"]) ? $_POST["password"] : null;
        $email = isset($_POST["email"]) ? $_POST["email"] : null;
        $age = isset($_POST["age"]) ? $_POST["age"] : null;

        if ($username !== null && $password !== null && $email !== null) {
            if ($dbCon) {
                $checkQuery = "SELECT * FROM [dbo].[User] WHERE username = ? OR email = ?";
                $checkParams = array($username, $email);
                $checkStmt = sqlsrv_prepare($dbCon, $checkQuery, $checkParams);

                if ($checkStmt && sqlsrv_execute($checkStmt)) {
                    $existingUser = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

                    if ($existingUser) {
                        $response['status'] = 'Invalid';
                        $response['data'] = null;
                        $response['message'] = 'Username or email already exists';
                    } else {                  
                        $insertQuery = "INSERT INTO [dbo].[User] (username, password, email, age) VALUES (?, ?, ?, ?)";
                        $insertParams = array($username, $password, $email, $age);

                        $insertStmt = sqlsrv_prepare($dbCon, $insertQuery, $insertParams);

                        if ($insertStmt && sqlsrv_execute($insertStmt)) {
                            $response['status'] = 'OK';
                            $response['data'] = null;
                            $response['message'] = 'Registration success';
                        } else {
                            $response['status'] = 'NOT OK';
                            $response['message'] = 'Error executing registration query';
                        }
                        sqlsrv_free_stmt($insertStmt);                      
                    }
                    sqlsrv_free_stmt($checkStmt);
                } else {
                    $response['status'] = 'NOT OK';
                    $response['message'] = 'Error checking existing user';
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
