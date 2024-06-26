<?php

session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: /login.php");
    exit;
}

$username = $_SESSION["username"];
$type = $_SESSION["type"];

if ($type != "admin") die("Permission denied.");

require_once "config.php";

// Get lists
$sql = "SELECT id, name, type FROM lists";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$lists = $result->fetch_all(MYSQLI_ASSOC);

// Get subscribers
$sql = "SELECT id, email, list, unsubcode, subdate, status FROM subscribers";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$subscribers = $result->fetch_all(MYSQLI_ASSOC);

// Get archive
$sql = "SELECT id, list, subject, message, author, date FROM archive";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$archive = $result->fetch_all(MYSQLI_ASSOC);

// Get users
$sql = "SELECT id, username, type FROM users";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$users = $result->fetch_all(MYSQLI_ASSOC);

function getlistbyid($id) {
    global $lists;
    foreach ($lists as $list) {
        if ($list["id"] == $id) {
            return $list;
        }
    }
}

function getuserbyid($id) {
    global $users;
    foreach ($users as $user) {
        if ($user["id"] == $id) {
            return $user;
        }
    }
}

?>

<!doctype html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="/style.css">
        <title>ARFNET MLM</title>
    </head>
    <body>
        <header><a href="https://arf20.com/">
            <img src="arfnet_logo.png" width="64"><span class="title"><strong>ARFNET</strong></span>
        </a></header>
        <hr>
        <main>
            <div class="row">
                <div class="col8">
                    <h2 class="center">ARFNET Mailing List Manager</h2>
                    <h3><?php echo strtoupper($type[0]).substr($type, 1); ?> panel</h3>
                    <div class="row">
                        <div class="col3">
                            <h3>Lists</h3>
                            <table>
                                <tr><th>name</th><th>type</th></tr>
                                <?php
                                foreach ($lists as $list) {
                                    echo "<tr><td>".$list["name"]."</td><td>".$list["type"]."</td></tr>\n";
                                }
                                ?>
                            </table>
                        </div>
                        <div class="col3">
                            <h3>Subscribers</h3>
                            <table>
                                <tr><th>email</th><th>list</th><th>status</th></tr>
                                <?php
                                foreach ($subscribers as $sub) {
                                    echo "<tr><td>".$sub["email"]."</td><td>".getlistbyid($sub["list"])["name"]."</td><td>".$sub["status"]."</tr>\n";
                                }
                                ?>
                            </table>
                        </div>
                        <div class="col3">
                            <h3>Archive</h3>
                            <table>
                                <tr><th>list</th><th>subject</th><th>message</th><th>author</th></tr>
                                <?php
                                foreach ($archive as $msg) {
                                    echo "<tr><td>".getlistbyid($msg["list"])["name"]."</td><td>".$msg["subject"]."</td><td><details><summary></summary><pre>".$msg["message"]."</pre></details></td><td>".getuserbyid($msg["author"])["username"]."</tr>\n";
                                }
                                ?>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col2">
                    <h3>Logged as <?php echo $username; ?></h3>
                    <h3><a href="/logout.php">Logout</h2>
                    <h3><a href="/managelists.php">Manage lists</h2>
                    <h3><a href="/managesubs.php">Manage subscribers</h2>
                    <h3><a href="/managearchive.php">Manage archive</h2>
                    <h3><a href="/post.php">Post</h2>
                </div>
            </div>
        </main>
    </body>
</html>
