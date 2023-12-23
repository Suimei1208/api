<?php
header('Content-Type: application/json');

require_once('connection.php');

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy thông tin cần cập nhật từ dữ liệu POST
    $userID = isset($_POST["userID"]) ? intval($_POST["userID"]) : null;
    $newEmail = isset($_POST["newEmail"]) ? $_POST["newEmail"] : null;
    $newProfileImage = isset($_POST["newProfileImage"]) ? $_POST["newProfileImage"] : null;
    $newPassword = isset($_POST["newPassword"]) ? $_POST["newPassword"] : null;
    $newAge = isset($_POST["newAge"]) ? intval($_POST["newAge"]) : null;

    if ($userID !== null || ($newEmail !== null || $newPassword !== null || $newAge !== null)) {
        if ($dbCon) {
            // Kiểm tra xem người dùng có tồn tại không
            $checkQuery = "SELECT * FROM [dbo].[User] WHERE id = ?";
            $checkParams = array($userID);
            $checkStmt = sqlsrv_prepare($dbCon, $checkQuery, $checkParams);

            if ($checkStmt && sqlsrv_execute($checkStmt)) {
                $existingUser = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

                if ($existingUser) {
                    // Lấy đường dẫn của avatar cũ từ cơ sở dữ liệu
                    $oldAvatarPath = $existingUser['profile_image'];

                    // Nếu đường dẫn tồn tại, thực hiện xóa file
                    if (!empty($oldAvatarPath) && file_exists($oldAvatarPath)) {
                        unlink($oldAvatarPath);
                    }

                    // Xây dựng câu lệnh UPDATE dựa trên thông tin mới
                    $updateQuery = "UPDATE [dbo].[User] SET ";
                    $updateParams = array();

                    if ($newEmail !== null) {
                        $updateQuery .= "email = ?, ";
                        $updateParams[] = $newEmail;
                    }

                    if ($newProfileImage !== null) {
                        // Tạo một thư mục để lưu trữ avatar nếu nó chưa tồn tại
                        $avatarDirectory = 'avatar';
                        if (!file_exists($avatarDirectory)) {
                            mkdir($avatarDirectory, 0777, true);
                        }

                        // Tạo tên file mới cho avatar
                        $newAvatarFileName = uniqid("profile_image_", true) . '.jpg';
                        $newAvatarPath = $avatarDirectory . '/' . $newAvatarFileName;

                        // Lưu ảnh mới vào thư mục và lấy đường dẫn
                        file_put_contents($newAvatarPath, base64_decode($newProfileImage));

                        $updateQuery .= "profile_image = ?, ";
                        $updateParams[] = $newAvatarPath;
                    }

                    if ($newPassword !== null) {
                        $updateQuery .= "password = ?, ";
                        $updateParams[] = $newPassword;
                    }

                    if ($newAge !== null) {
                        $updateQuery .= "age = ?, ";
                        $updateParams[] = $newAge;
                    }

                    $updateQuery = rtrim($updateQuery, ", ");  // Loại bỏ dấu phẩy cuối cùng
                    $updateQuery .= " WHERE id = ?";
                    $updateParams[] = $userID;

                    // Thực hiện câu lệnh UPDATE
                    $updateStmt = sqlsrv_prepare($dbCon, $updateQuery, $updateParams);

                    if ($updateStmt && sqlsrv_execute($updateStmt)) {
                        // Lấy dữ liệu mới sau khi cập nhật thành công
                        $newDataQuery = "SELECT * FROM [dbo].[User] WHERE id = ?";
                        $newDataParams = array($userID);
                        $newDataStmt = sqlsrv_prepare($dbCon, $newDataQuery, $newDataParams);

                        if ($newDataStmt && sqlsrv_execute($newDataStmt)) {
                            $newUserData = sqlsrv_fetch_array($newDataStmt, SQLSRV_FETCH_ASSOC);
                            $newUserData['profile_image'] = 'http://10.0.2.2/api/' . $newUserData['profile_image'];
                            $response['status'] = 'OK';
                            $response['data'] = $newUserData;
                            $response['message'] = 'User updated successfully';
                        } else {
                            $response['status'] = 'NOT OK';
                            $response['message'] = 'Error fetching updated user data';
                        }
                        sqlsrv_free_stmt($newDataStmt);
                    } else {
                        $response['status'] = 'NOT OK';
                        $response['message'] = 'Error executing user update query: ' . print_r(sqlsrv_errors(), true);
                    }
                    sqlsrv_free_stmt($updateStmt);
                } else {
                    $response['status'] = 'NOT OK';
                    $response['message'] = 'Invalid userID. User not found.';
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
        $response['message'] = 'Invalid data received. userID and at least one of newEmail, newUsername, newProfileImage, newPassword, or newAge are required.';
    }
} else {
    $response['status'] = 'NOT OK';
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
