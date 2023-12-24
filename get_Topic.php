<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();
if ($_SERVER["REQUEST_METHOD"] == "GET" || $_SERVER["REQUEST_METHOD"] == "POST") {
    if ($dbCon) {
        $query = "SELECT * FROM [dbo].[Topic]";
        $stmt = sqlsrv_prepare($dbCon, $query);

        if ($stmt && sqlsrv_execute($stmt)) {
            $topics = array();

            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $topic = array(
                    'id' => $row['id'],
                    'topicName' => $row['topicName'],
                    'description' => $row['description'],
                    'isPublic' => $row['isPublic'],
                    'ownerID' => $row['ownerID']
                );

                $topics[] = $topic;
            }

            $response['status'] = 'OK';
            $response['data'] = $topics;
            $response['message'] = 'Topics retrieved successfully';
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
}

if (!isset($response['data'])) {
    $response['data'] = null;
}

echo json_encode($response);
?>
