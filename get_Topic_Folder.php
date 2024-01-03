<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

if ( $_SERVER["REQUEST_METHOD"] == "POST") {
    if ($dbCon) {
        // Assuming you pass the folderID as a parameter in the request
        $folderID = isset($_REQUEST["folderID"]) ? intval($_REQUEST["folderID"]) : null;

        if ($folderID !== null) {
            $query = "SELECT t.*
                      FROM [dbo].[FolderDetail] fd
                      INNER JOIN [dbo].[Topic] t ON fd.topicID = t.id
                      WHERE fd.folderID = ?";
            $params = array($folderID);
            $stmt = sqlsrv_prepare($dbCon, $query, $params);

            if ($stmt && sqlsrv_execute($stmt)) {
                $topics = array();

                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $topic = array(
                        'id' => $row['id'],
                        'topicName' => $row['topicName'],
                        'description' => $row['description'],
                        'isPublic' => $row['isPublic'],
                        'ownerID' => $row['ownerID'],
                        'vocabularyCount' => $row['vocabularyCount'],
                        'folderID' => $folderID
                    );

                    $topics[] = $topic;
                }

                $response['status'] = 'OK';
                $response['data'] = $topics;
                $response['message'] = 'Topics in folder retrieved successfully';
            } else {
                $response['status'] = 'NOT OK';
                $response['message'] = 'Error executing query: ' . print_r(sqlsrv_errors(), true);
            }
        } else {
            $response['status'] = 'NOT OK';
            $response['message'] = 'Folder ID is missing';
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
