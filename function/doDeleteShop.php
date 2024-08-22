<?php
require_once("../db_connect.php");
include("../function/login_status_inspect.php");

$role = $_SESSION["user"]["role"];

if ($role != "admin") {
    header("location: dashboard-home_Joe.php");
    exit;
}

$delete_shop_id = $_SESSION["shop"]["shop_id"];

// 取得所有與該 shop_id 關聯的 user_id
$sql_get_user_ids = "SELECT users.user_id FROM shop JOIN users ON shop.user_id = users.user_id WHERE shop.shop_id = $delete_shop_id";
$get_user_id_result = $conn->query($sql_get_user_ids);

if ($get_user_id_result->num_rows > 0) {
    $conn->begin_transaction();

    try {
        // 更新 shop 的 activation 狀態
        $sql1 = "UPDATE shop SET activation = 0 WHERE shop_id = $delete_shop_id";
        if (!$conn->query($sql1)) {
            throw new Exception("更新 shop 資料錯誤: " . $conn->error);
        }

        // 更新與該 shop_id 關聯的所有使用者的角色
        while ($row = $get_user_id_result->fetch_assoc()) {
            $user_id = $row["user_id"];
            $sql2 = "UPDATE users SET role = 'user' WHERE user_id = $user_id";
            if (!$conn->query($sql2)) {
                throw new Exception("更新 user 資料錯誤: " . $conn->error);
            }
        }

        // 提交交易
        $conn->commit();
        // 成功訊息存入 session 中
        $_SESSION['message'] = "Shop 狀態與 User 角色已成功更新。";
    } catch (Exception $e) {
        // 回滾交易
        $conn->rollback();
        // 錯誤訊息存入 session 中
        $_SESSION['error'] = $e->getMessage();
    }
} else {
    $_SESSION['error'] = "沒有找到與該 shop_id 關聯的使用者。";
}

$conn->close();
header("location: ../page/shop-info-admin.php");
exit;
?>
