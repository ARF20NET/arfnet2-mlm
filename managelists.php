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

// GET actions
//   delete entry
if (isset($_GET["del"])) {
    $sql = "DELETE FROM lists WHERE id = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $param_id);
    $param_id = $_GET["del"];
    if (!mysqli_stmt_execute($stmt) || mysqli_stmt_affected_rows($stmt) != 1) {
        echo "SQL error.";
    } else header("location: ".$_SERVER['SCRIPT_NAME']);
}

// POST actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // add entry
    if (isset($_POST["add"])) {
        $sql = "INSERT INTO lists (name, type, `desc`) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $param_name, $param_type, $param_desc);
        $param_name = $_POST["name"];
        $param_type = $_POST["type"];
        $param_desc = $_POST["desc"];

        if (!mysqli_stmt_execute($stmt) || (mysqli_stmt_affected_rows($stmt) != 1)) {
            echo "SQL error.";
        } else header("location: ".$_SERVER['SCRIPT_NAME']);
    }

    // edit entry
    if (isset($_POST["save"])) {
        $sql = "UPDATE lists SET name = ?, type = ?, `desc` = ? WHERE id = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $param_name, $param_type, $param_desc, $param_id);
        $param_name = $_POST["name"];
        $param_type = $_POST["type"];
        $param_desc = $_POST["desc"];
        $param_id = $_POST["id"];

        if (!mysqli_stmt_execute($stmt) || (mysqli_stmt_affected_rows($stmt) != 1)) {
            echo "SQL error.";
        } else header("location: ".$_SERVER['SCRIPT_NAME']);
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
                    <h3>Lists</h3>

                    <?php
                    if (isset($_GET["edit"])) {
                        $list = getlistbyid($_GET["edit"]);
                        echo "<div class=\"form\"><h3>Edit list ".$list["id"]."</h3><form action=\"".$_SERVER['SCRIPT_NAME']."\" method=\"post\">\n"
                            ."<label>Name</label><br><input type=\"text\" name=\"name\" value=\"".$list["name"]."\"><br>\n"
                            ."<label>Type</label><br><select name=\"type\"><option value=\"public\" ".($list["type"] == "public" ? "selected" : "").">public</option><option value=\"hidden\" ".($service["type"] == "hidden" ? "selected" : "").">hidden</option></select><br>\n"
                            ."<label>Description</label><br><textarea name=\"desc\" rows=\"10\" cols=\"80\">".$list["desc"]."</textarea><br>\n"
                            ."<input type=\"hidden\" name=\"id\" value=\"".$list["id"]."\">"
                            ."<br><input type=\"submit\" name=\"save\" value=\"Save\"><a href=\"".$_SERVER['SCRIPT_NAME']."\">cancel</a>"
                            ."</form></div>";
                    }

                    if (isset($_GET["add"])) {
                        echo "<div class=\"form\"><h3>Create list</h3><form action=\"".$_SERVER['SCRIPT_NAME']."\" method=\"post\">\n"
                            ."<label>Name</label><br><input type=\"text\" name=\"name\"><br>\n"
                            ."<label>Type</label><br><select name=\"type\"><option value=\"public\">public</option><option value=\"hidden\">hidden</option></select><br>\n"
                            ."<label>Description</label><br><textarea name=\"desc\" rows=\"10\" cols=\"80\"></textarea><br>\n"
                            ."<br><input type=\"submit\" name=\"add\" value=\"Add\"><a href=\"".$_SERVER['SCRIPT_NAME']."\">cancel</a>"
                            ."</form></div>";
                    }
                    ?>

                    <a href="?add">add</a>
                    <table>
                        <tr><th>id</th><th>name</th><th>type</th><th>description</th><th>actions</th></tr>
                        <?php
                        foreach ($lists as $list) {
                            echo "<tr><td>".$list["id"]."</td>"
                            ."<td>".$list["name"]."</td>"
                            ."<td>".$list["type"]."</td>"
                            ."<td>".$list["desc"]."</td>"
                            ."<td><a href=\"?del=".$list["id"]."\">del</a> <a href=\"?edit=".$list["id"]."\">edit</a></td></tr>\n";
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
