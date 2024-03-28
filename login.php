<?php
// Initialize the session
session_start();
 
// Check if the user is already logged in, if yes then redirect him to welcome page
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: /".$_SESSION["type"].".php");
    exit;
}
 
// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = "";
 
// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username
    if (empty($_POST["username"]))
        $username_err = "Enter a username.";
    else if (preg_match("/[a-zA-Z0-9_]+/", $_POST["username"]) != 1)
        $username_err = "Invalid username.";
    else
        $username = $_POST["username"];
    
    // Validate password
    if (empty($_POST["password"]))
        $password_err = "Enter a password.";     
    else if (strlen($_POST["password"]) < 8)
        $password_err = "Password must have at least 8 characters.";
    else
        $password = $_POST["password"];
    
    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT id, username, password, status, type FROM users WHERE username = ?";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)){
                // Store result
                mysqli_stmt_store_result($stmt);
                
                // Check if username exists, if yes then verify password
                if (mysqli_stmt_num_rows($stmt) == 1) {                    
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $status, $type);
                    if (mysqli_stmt_fetch($stmt)){
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, check verification
                            if ($status == "verified") {
                                session_start();
                            
                                // Store data in session variables
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["username"] = $username;
                                $_SESSION["type"] = $type;
                                
                                // Redirect user to appropiate page
                                header("location: /".$type.".php");
                            } else {
                                $username_err = "Unverified account, check your email.";
                            }
                        } else {
                            $password_err = "Incorrect password.";
                        }
                    }
                } else{
                    // Display an error message if username doesn't exist
                    $username_err = "No account found with that username.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>CSTIMS Login</title>
        <link rel="stylesheet" type="text/css" href="/style.css">
    </head>
    <body>
        <header><a href="https://arf20.com/">
            <img src="arfnet_logo.png" width="64"><span class="title"><strong>ARFNET</strong></span>
        </a></header>
        <hr>
        <main>
            <div class="wrapper">
                <h2>MLM Login</h2>
                <form action="/login.php" method="post">
                    <div class="form-group row <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                        <div class="column"><label>Username</label></div>
                        <div class="column"><input type="text" name="username" class="form-control" pattern="[a-zA-Z0-9_]+" value="<?php echo $username; ?>"></div>
                        <span class="help-block"><?php echo $username_err; ?></span>
                    </div>    
                    <div class="form-group row <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                        <div class="column"><label>Password</label></div>
                        <div class="column"><input type="password" name="password" class="form-control"></div>
                        <span class="help-block"><?php echo $password_err; ?></span>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary" value="Login">
                    </div>
                    <p>Don't have an account? <a href="register.php">Sign up now</a>.</p>
                </form>
            </div>
        </main>
    </body>
</html>
