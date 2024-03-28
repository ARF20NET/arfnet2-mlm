<?php
require_once "config.php";

// Get lists
$sql = "SELECT id, name, type FROM lists";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$lists = $result->fetch_all(MYSQLI_ASSOC);

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
                    <ul>
                    <?php
                        foreach ($lists as $list) {
                            echo "<li>".$list["name"]." <a href=\"subscribe.php?list=".$list["id"]."\">subscribe</a> <a href=\"archive.php?list=".$list["id"]."\">archive</a></li>\n";
                        }
                        ?>
                    </ul>
                </div>
                <div class="col2">
                    <h3><a href="/login.php">Login</h2>
                </div>
            </div>
        </main>
    </body>
</html>
