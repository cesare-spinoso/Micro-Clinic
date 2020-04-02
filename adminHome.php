<?php
include "commonMain.php";
# Start the session
session_start();
# if you want to erase global variables use session_unset();
# If the logged in user comes back to home page redirect them
if(isset($_SESSION["signedin"]) && $_SESSION["signedin"] === true){
    header("location: https://mvc353.encs.concordia.ca/mainProject/admin.php");
    exit;
}
$err_msg = "";
$signedIn = false;
if (isset($_POST['submit'])) {
    if (!empty($_POST['usernameIn']) & !empty($_POST['passwordIn'])) {
        $usernameIn = $_POST['usernameIn'];
        $passwordIn = $_POST['passwordIn'];
        # Search through the list of username/password
        # Create a PDO connection
        try {
            $connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password, $options);
            $sql = "select * from admin;";
            $select = $connection->prepare($sql);
            $select->execute();
            while ($row = $select->fetch(PDO::FETCH_NUM)) {
                if ($row[0] == $usernameIn && $row[1] == $passwordIn) {
                    session_start();
                    $_SESSION["signedin"] = true;
                    header("location: https://mvc353.encs.concordia.ca/mainProject/admin.php");
                }
            }
            if (!isset($_SESSION["signedin"]) || $_SESSION !== true) {
                $err_msg = "Invalid username/password";
            }

        } catch (PDOException $e) {
            print_r($e->getMessage());
        }
    } else {
        $err_msg = "One or more fields are empty.";
    }
}
include "headerMain.php";
?>
<h1>Administrator Login</h1>
<p>It seems like you have found the admin page. But not so fast! Password required.</p>
<form method="post">
    <label for="query">Admin login:</label>
    <label>Username: <input type="text" name="usernameIn"></label>
    <label>Password: <input type="password" name="passwordIn"></label>
    <span class="error"><?php echo $err_msg ?></span><br>
    <input type="submit" name="submit" value="SUBMIT">
</form>
<?php include "footerMain.php"; ?>
