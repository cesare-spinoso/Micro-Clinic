<?php

include "commonMain.php";

session_start();

if(!isset($_SESSION["signedin"]) || $_SESSION["signedin"] !== true){
    header("location: https://mvc353.encs.concordia.ca/mainProject/adminHome.php");
    exit;
}

$query = "select * from treatments;";
$output = produceHtmlTable($query);

include "headerMain.php";
?>
<h1>List of Treatments</h1>
<?php echo $output; ?>
<a href="admin.php">Back to admin's page.</a>
<?php include "footerMain.php"; ?>