<?php
require_once("../db_connect.php");

if (!isset($_GET["id"])) {
    echo "請循正常管道進入此頁";
    exit;
}

$id = $_GET["id"];
$sql = "UPDATE articles SET activation = 0 WHERE article_id = $id";
$conn->query($sql);

$conn->close();
header("location:../page/articles.php?status=all");