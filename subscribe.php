<?php
 
// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$email = "";
$email_err = "";

// Get lists
$sql = "SELECT id, name, type, `desc` FROM lists";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$lists = $result->fetch_all(MYSQLI_ASSOC);
 
// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate email
    if (empty($_POST["email"]))
        $email_err = "Enter a email address.";
    else if (filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) === false)
        $email_err = "Invalid email address.";
    else
        $email = $_POST["email"];
    
    // Validate credentials
    if (empty($email_err)) {
        $sql = "SELECT id FROM subscribers WHERE email = ? AND list = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $param_email, $param_list);
        $param_email = $_POST["email"];
        $param_list = $_POST["list"];
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1)
            $email_err = "Already subscribed";
        else $email = $_POST["email"];
    }

    if (empty($email_err)) {
        $sql = "INSERT INTO subscribers (email, list, unsubcode) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $param_email, $param_list, $param_ubsubcode);
        $param_email = $_POST["email"];
        $param_list = $_POST["list"];
        $param_ubsubcode = substr(sha1(random_bytes(64)), 0, 16); // random 16 character code
        if (mysqli_stmt_execute($stmt)) {
            die("Subscribed!");
        } else die("SQL error");
    }
    
    // Close connection
    mysqli_close($link);
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
 
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>MLM Subscribe</title>
        <link rel="stylesheet" type="text/css" href="/style.css">
    </head>
    <body>
        <header><a href="https://arf20.com/">
            <img src="arfnet_logo.png" width="64"><span class="title"><strong>ARFNET</strong></span>
        </a></header>
        <hr>
        <main>
            <div class="wrapper">
                <h2>MLM Subscribe</h2>
                <form action="<?php echo $_SERVER["SCRIPT_NAME"]; ?>" method="post">
                    <label>List: <?php echo getlistbyid($_GET["list"])["name"] ?><br><br>
                    <label>Email</label><br>
                    <input type="email" name="email" class="form-control">
                    <span class="help-block"><?php echo $email_err; ?></span><br>                    
                    <input type="hidden" name="list" value="<?php echo $_GET["list"]; ?>">
                    <br><input type="submit" class="btn btn-primary" value="Subscribe">
                </form>
            </div>
        </main>
    </body>
</html>
