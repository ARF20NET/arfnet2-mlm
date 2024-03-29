<?php

if (empty($_GET["list"])) die("List required");
$list = $_GET["list"];

require_once "config.php";

// Get archive
$sql = "SELECT id, subject, message, date, author FROM archive WHERE list = ? ORDER BY id DESC";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "s", $param_id);
$param_id = $list;
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$archive = $result->fetch_all(MYSQLI_ASSOC);

// Get lists
$sql = "SELECT id, name FROM lists";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$lists = $result->fetch_all(MYSQLI_ASSOC);

// Get users
$sql = "SELECT id, username, type FROM users";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$users = $result->fetch_all(MYSQLI_ASSOC);

$listname = getlistbyid($list)["name"];

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
                    <h3>ARFNET Mailing Lists</h3>
                    <h4><?php echo $listname; ?></h4>
                    <p><?php echo $listname."@".MAIL_DOMAIN; ?></p>
                    <div>
                    <?php
                    foreach ($archive as $msg) {
                        echo "<pre>\n"
                            ."Message-ID: ".$msg["id"]."\n"
                            ."Subject: ".$msg["subject"]."\n"
                            ."From: ".getuserbyid($msg["author"])["username"]."@".MAIL_DOMAIN."\n"
                            ."To: ".$listname."@".MAIL_DOMAIN."\n"
                            ."Bcc: [REDACTED]\n"
                            ."Date: ".$msg["date"]."\n\n"
                            .$msg["message"]
                            ."</pre><hr>";
                    }
                    ?>
                    </div>
                </div>
                <div class="col2">
                    <h3><a href="/">Back</h2>
                </div>
            </div>
        </main>
    </body>
</html>
