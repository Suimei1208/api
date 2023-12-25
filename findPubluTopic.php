<?php 
    header('Content-Type: application/json');

    require_once('connection.php');

    $response = array();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve the list of public topics
        //$ownerID = isset($_POST["ownerID"]) ? $_POST["ownerID"] : null;
        if ($dbCon) {
            $publicTopicsQuery = "SELECT * FROM [dbo].[Topic] WHERE isPublic = 1";
            $publicTopicsStmt = sqlsrv_prepare($dbCon, $publicTopicsQuery);

            if ($publicTopicsStmt && sqlsrv_execute($publicTopicsStmt)) {
                $publicTopics = array();

                while ($publicTopic = sqlsrv_fetch_array($publicTopicsStmt, SQLSRV_FETCH_ASSOC)) {
                    $publicTopics[] = $publicTopic;
                }

                $response['status'] = 'OK';
                $response['publicTopics'] = $publicTopics;
                $response['message'] = 'Successfully retrieved the list of public topics';
            } else {
                $response['status'] = 'NOT OK';
                $response['message'] = 'Error fetching the list of public topics';
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

    echo json_encode($response);
?>
