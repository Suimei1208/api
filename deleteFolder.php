<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $folderID = isset($_POST["folderID"]) ? $_POST["folderID"] : null;

    if ($folderID !== null) {
        if ($dbCon) {
            // Trước tiên, xóa FolderDetail liên quan đến folderID
            $deleteFolderDetailQuery = "DELETE FROM [dbo].[FolderDetail] WHERE folderID = ?";
            $deleteFolderDetailParams = array($folderID);
            $deleteFolderDetailStmt = sqlsrv_prepare($dbCon, $deleteFolderDetailQuery, $deleteFolderDetailParams);

            if ($deleteFolderDetailStmt && sqlsrv_execute($deleteFolderDetailStmt)) {
                // Lấy danh sách topicID từ FolderDetail
                $getTopicIDsQuery = "SELECT topicID FROM [dbo].[FolderDetail] WHERE folderID = ?";
                $getTopicIDsParams = array($folderID);
                $getTopicIDsStmt = sqlsrv_prepare($dbCon, $getTopicIDsQuery, $getTopicIDsParams);

                if ($getTopicIDsStmt && sqlsrv_execute($getTopicIDsStmt)) {
                    while ($row = sqlsrv_fetch_array($getTopicIDsStmt, SQLSRV_FETCH_ASSOC)) {
                        $topicID = $row['topicID'];

                        // Xóa các bản ghi trong bảng Topic liên quan đến topicID
                        // $deleteTopicQuery = "DELETE FROM [dbo].[Topic] WHERE id = ?";
                        // $deleteTopicParams = array($topicID);
                        // $deleteTopicStmt = sqlsrv_prepare($dbCon, $deleteTopicQuery, $deleteTopicParams);

                        // if ($deleteTopicStmt && sqlsrv_execute($deleteTopicStmt)) {
                            // Tiến hành xóa Folder sau khi xóa thành công FolderDetail và Topic
                            $deleteFolderQuery = "DELETE FROM [dbo].[Folder] WHERE folderID = ?";
                            $deleteFolderParams = array($folderID);
                            $deleteFolderStmt = sqlsrv_prepare($dbCon, $deleteFolderQuery, $deleteFolderParams);

                            if ($deleteFolderStmt && sqlsrv_execute($deleteFolderStmt)) {
                                $response['status'] = 'OK';
                                $response['data'] = null;
                                $response['message'] = 'Folder and related items deleted successfully';
                            } else {
                                $response['status'] = 'NOT OK';
                                $response['message'] = 'Error executing folder deletion query: ' . print_r(sqlsrv_errors(), true);
                            }
                        //     sqlsrv_free_stmt($deleteFolderStmt);
                        // } else {
                        //     $response['status'] = 'NOT OK';
                        //     $response['message'] = 'Error executing topic deletion query: ' . print_r(sqlsrv_errors(), true);
                        // }
                        // sqlsrv_free_stmt($deleteTopicStmt);
                    }
                } else {
                    $response['status'] = 'NOT OK';
                    $response['message'] = 'Error getting topicIDs: ' . print_r(sqlsrv_errors(), true);
                }
                sqlsrv_free_stmt($getTopicIDsStmt);
            } else {
                $response['status'] = 'NOT OK';
                $response['message'] = 'Error executing folder detail deletion query: ' . print_r(sqlsrv_errors(), true);
            }
            sqlsrv_free_stmt($deleteFolderDetailStmt);

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
