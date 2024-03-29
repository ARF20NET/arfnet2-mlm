<?php

session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: /login.php");
    exit;
}

$id = $_SESSION["id"];
$username = $_SESSION["username"];
$type = $_SESSION["type"];

if ($type != "admin") die("Permission denied.");

require_once "config.php";

// Get lists
$sql = "SELECT id, name, type, `desc` FROM lists";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$lists = $result->fetch_all(MYSQLI_ASSOC);

// POST actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // post entry
    $sql = "INSERT INTO archive (list, subject, message, author) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $param_list, $param_subject, $param_message, $param_author);
    $param_list = $_POST["list"];
    $param_subject = $_POST["subject"];
    $param_message = $_POST["message"];
    $param_author = $_POST["author"];

    if (!mysqli_stmt_execute($stmt) || (mysqli_stmt_affected_rows($stmt) != 1)) {
        echo "SQL error.";
    } else {
        // Get list subscribers
        $sql = "SELECT email FROM subscribers WHERE status = 'active' AND list = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $param_list);
        $param_list = $_POST["list"];
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $subs = $result->fetch_all(MYSQLI_ASSOC);

        $mailer->setFrom(getlistbyid($_POST["list"])["name"]."@".MAIL_DOMAIN);
        $mailer->addReplyTo($username."@".MAIL_DOMAIN);
        foreach ($subs as $sub)
            $mailer->AddBCC($sub["email"]);
        $mailer->Subject = $_POST["subject"];
        $mailer->Body = $_POST["message"];

        if (!$mailer->send()) {
            echo 'Mailer Error [ask arf20]: ' . $mailer->ErrorInfo;
        } else header("location: /admin.php");
    }
}

function getlistbyid($id) {
    global $lists;
    foreach ($lists as $list) {
        if ($list["id"] == $id) {
            return $list;
        }
    }
}

?>

<!doctype html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="/style.css">
        <title>ARFNET CSTIMS</title>
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
                    <h3>Service offerings</h3>

                    <div class="form"><h3>Post message</h3><form action="<?php echo $_SERVER["SCRIPT_NAME"]; ?>" method="post">
                    <label>List</label><br><select name="list">
                    <?php
                    foreach ($lists as $list)
                        echo "<option value=\"".$list["id"]."\">".$list["name"]."</option>"; 
                    ?></select><br><br>
                    <label>Subject</label><br><input type="text" name="subject"><br>
                    <label>Message</label><br><textarea name="message" rows="20" cols="80"></textarea><br>
                    <input type="hidden" name="author" value="<?php echo $id; ?>">
                    <br><input type="submit" name="add" value="Post"><a href="/admin.php">cancel</a>
                    </form></div>
                </div>
                <div class="col2">
                    <h3>Logged as <?php echo $username; ?></h3>
                    <h3><a href="/logout.php">Logout</h2>
                    <h3><a href="/admin.php">Back to admin panel</h2>
                </div>
            </div>
        </main>
    </body>
</html>
