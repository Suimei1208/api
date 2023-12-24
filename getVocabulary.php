<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "GET" || $_SERVER["REQUEST_METHOD"] == "POST") {
    if ($dbCon) {
        $query = "SELECT * FROM [dbo].[Vocabulary]";
        $stmt = sqlsrv_prepare($dbCon, $query);

        if ($stmt && sqlsrv_execute($stmt)) {
            $vocabularies = array();

            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $vocabulary = array(
                    'vocabID' => $row['vocabID'],
                    'vocabulary' => $row['vocabulary'],
                    'meaning' => $row['meaning'],
                    'topicID' => $row['topicID']
                );

                $vocabularies[] = $vocabulary;
            }

            $response['status'] = 'OK';
            $response['data'] = $vocabularies;
            $response['message'] = 'Vocabularies retrieved successfully';
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
    $response['message'] = 'Invalid request method';
}

if (!isset($response['data'])) {
    $response['data'] = null;
}

echo json_encode($response);
?>
