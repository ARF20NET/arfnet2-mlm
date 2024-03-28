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

// GET actions
//   delete entry
if (isset($_GET["del"])) {
    $sql = "DELETE FROM archive WHERE id = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $param_id);
    $param_id = $_GET["del"];
    if (!mysqli_stmt_execute($stmt) || mysqli_stmt_affected_rows($stmt) != 1) {
        echo "SQL error.";
    } else header("location: ".$_SERVER["SCRIPT_NAME"]);
}

// POST actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // add entry
    if (isset($_POST["add"])) {
        $sql = "INSERT INTO archive (list, subject, message, author) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $param_list, $param_subject, $param_message, $param_author);
        $param_list = $_POST["list"];
        $param_subject = $_POST["subject"];
        $param_message = $_POST["message"];
        $param_author = $_POST["author"];

        if (!mysqli_stmt_execute($stmt) || (mysqli_stmt_affected_rows($stmt) != 1)) {
            echo "SQL error.";
        } else header("location: ".$_SERVER["SCRIPT_NAME"]);
    }

    // edit entry
    if (isset($_POST["save"])) {
        $sql = "UPDATE archive SET subject = ?, message = ? WHERE id = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $param_subject, $param_message, $param_id);
        $param_subject = $_POST["subject"];
        $param_message = $_POST["message"];
        $param_id = $_POST["id"];

        if (!mysqli_stmt_execute($stmt) || (mysqli_stmt_affected_rows($stmt) != 1)) {
            echo "SQL error.";
        } else header("location: ".$_SERVER["SCRIPT_NAME"]);
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

function getuserbyid($id) {
    global $users;
    foreach ($users as $user) {
        if ($user["id"] == $id) {
            return $user;
        }
    }
}

function getmessagebyid($id) {
    global $archive;
    foreach ($archive as $msg) {
        if ($msg["id"] == $id) {
            return $msg;
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
                    <h3>Archive manage</h3>

                    <?php
                    if (isset($_GET["edit"])) {
                        $msg = getmessagebyid($_GET["edit"]);
                        echo "<div class=\"form\"><h3>Edit message ".$msg["id"]."</h3><form action=\"".$_SERVER["SCRIPT_NAME"]."\" method=\"post\">\n"
                            ."<label>List</label><br><label>".getlistbyid($msg["list"])["name"]."</lavel><br>\n"
                            ."<label>Subject</label><br><input type=\"text\" name=\"subject\" value=\"".$msg["subject"]."\"><br>\n"
                            ."<label>Message</label><br><textarea name=\"message\" rows=\"20\" cols=\"80\">".$msg["message"]."</textarea><br>\n"
                            ."<input type=\"hidden\" name=\"id\" value=\"".$msg["id"]."\">"
                            ."<br><input type=\"submit\" name=\"save\" value=\"Save\"><a href=\"".$_SERVER["SCRIPT_NAME"]."\">cancel</a>"
                            ."</form></div>";
                    }

                    if (isset($_GET["add"])) {
                        foreach ($lists as $list)
                            $list_options .= "<option value=\"".$list["id"]."\">".$list["name"]."</option>";
                        echo "<div class=\"form\"><h3>Add message</h3><form action=\"".$_SERVER["SCRIPT_NAME"]."\" method=\"post\">\n"
                            ."<label>List</label><br><select name=\"list\">$list_options</select><br>\n"
                            ."<label>Subject</label><br><input type=\"text\" name=\"subject\"><br>\n"
                            ."<label>Message</label><br><textarea name=\"message\" rows=\"20\" cols=\"80\">".$msg["message"]."</textarea><br>\n"
                            ."<input type=\"hidden\" name=\"author\" value=\"".$id."\"><br>\n"
                            ."<br><input type=\"submit\" name=\"add\" value=\"Add\"><a href=\"".$_SERVER["SCRIPT_NAME"]."\">cancel</a>"
                            ."</form></div>";
                    }
                    ?>

                    <a href="?add">add</a>
                    <table>
                        <tr><th>id</th><th>list</th><th>subject</th><th>message</th><th>author</th><th>date</th><th>actions</th></tr>
                        <?php
                        foreach ($archive as $msg) {
                            echo "<tr><td>".$msg["id"]."</td>"
                            ."<td>".getlistbyid($msg["list"])["name"]."</td>"
                            ."<td>".$msg["subject"]."</td>"
                            ."<td><details><summary></summary><pre>".$msg["message"]."</pre></td>"
                            ."<td>".getuserbyid($msg["author"])["username"]."</td>"
                            ."<td>".$msg["date"]."</td>"
                            ."<td><a href=\"?del=".$msg["id"]."\">del</a> <a href=\"?edit=".$msg["id"]."\">edit</a></td></tr>\n";
                        }
                        ?>
                    </table>
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
