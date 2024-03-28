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
$sql = "SELECT id, name, type, `desc` FROM lists";
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

// GET actions
//   delete entry
if (isset($_GET["del"])) {
    $sql = "DELETE FROM subscribers WHERE id = ?";
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
        $sql = "INSERT INTO subscribers (email, list, unsubcode) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $param_email, $param_list, $param_unsubcode);
        $param_email = $_POST["email"];
        $param_list = $_POST["list"];
        $param_unsubcode = substr(sha1(random_bytes(64)), 0, 16); // random 16 character code

        if (!mysqli_stmt_execute($stmt) || (mysqli_stmt_affected_rows($stmt) != 1)) {
            echo "SQL error.";
        } else header("location: ".$_SERVER["SCRIPT_NAME"]);
    }

    // edit entry
    if (isset($_POST["save"])) {
        $sql = "UPDATE subscribers SET email = ?, list = ?, status = ? WHERE id = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $param_email, $param_list, $param_status, $param_id);
        $param_email = $_POST["email"];
        $param_list = $_POST["list"];
        $param_status = $_POST["status"];
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

function getsubscriberbyid($id) {
    global $subscribers;
    foreach ($subscribers as $subscriber) {
        if ($subscriber["id"] == $id) {
            return $subscriber;
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
                    <h3>Subscribers</h3>

                    <?php
                    if (isset($_GET["edit"])) {
                        $subscriber = getsubscriberbyid($_GET["edit"]);
                        $list_options = "";
                        foreach ($lists as $list)
                            $list_options .= "<option value=\"".$list["id"]."\" ".($subscriber["list"] == $list["id"] ? "selected" : "").">".$list["name"]."</option>";
                        echo "<div class=\"form\"><h3>Edit subscriber ".$subscriber["id"]."</h3><form action=\"".$_SERVER["SCRIPT_NAME"]."\" method=\"post\">\n"
                            ."<label>Email</label><br><input type=\"text\" name=\"email\" value=\"".$subscriber["email"]."\"><br>\n"
                            ."<label>List</label><br><select name=\"list\">$list_options</select><br>\n"
                            ."<label>Status</label><br><select name=\"status\"><option value=\"active\" ".($subscriber["status"] == "active" ? "selected" : "").">active</option><option value=\"inactive\" ".($subscriber["status"] == "inactive" ? "selected" : "").">inactive</option></select><br>\n"
                            ."<input type=\"hidden\" name=\"id\" value=\"".$subscriber["id"]."\">"
                            ."<br><input type=\"submit\" name=\"save\" value=\"Save\"><a href=\"".$_SERVER["SCRIPT_NAME"]."\">cancel</a>"
                            ."</form></div>";
                    }

                    if (isset($_GET["add"])) {
                        foreach ($lists as $list)
                            $list_options .= "<option value=\"".$list["id"]."\">".$list["name"]."</option>";
                        echo "<div class=\"form\"><h3>Create list</h3><form action=\"".$_SERVER["SCRIPT_NAME"]."\" method=\"post\">\n"
                            ."<label>Email</label><br><input type=\"text\" name=\"email\"><br>\n"
                            ."<label>List</label><br><select name=\"list\">$list_options</select><br>\n"
                            ."<label>Status</label><br><select name=\"status\"><option value=\"active\">active</option><option value=\"inactive\">inactive</option></select><br>\n"
                            ."<br><input type=\"submit\" name=\"add\" value=\"Add\"><a href=\"".$_SERVER["SCRIPT_NAME"]."\">cancel</a>"
                            ."</form></div>";
                    }
                    ?>

                    <a href="?add">add</a>
                    <table>
                        <tr><th>id</th><th>email</th><th>list</th><th>unsubcode</th><th>subdate</th><th>status</th><th>actions</th></tr>
                        <?php
                        foreach ($subscribers as $sub) {
                            echo "<tr><td>".$sub["id"]."</td>"
                            ."<td>".$sub["email"]."</td>"
                            ."<td>".getlistbyid($sub["list"])["name"]."</td>"
                            ."<td>".$sub["unsubcode"]."</td>"
                            ."<td>".$sub["subdate"]."</td>"
                            ."<td>".$sub["status"]."</td>"
                            ."<td><a href=\"?del=".$sub["id"]."\">del</a> <a href=\"?edit=".$sub["id"]."\">edit</a></td></tr>\n";
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
