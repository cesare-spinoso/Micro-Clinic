<?php

include "commonMain.php";

session_start();

if(!isset($_SESSION["signedin"]) || $_SESSION["signedin"] !== true){
    header("location:https://mvc353.encs.concordia.ca/adminHome.php");
    exit;
}

$query = "select * from staffs;";
$output = produceHtmlTable($query);

$queryDentists = "select staffs.* from staffs, dentists where staffs.eid = dentists.eid;";
$outputDentists = produceHtmlTable($queryDentists);

$queryDentAssist = "select staffs.* from staffs, dentalAssistants where staffs.eid = dentalAssistants.eid;";
$outputDentAssist = produceHtmlTable($queryDentAssist);

$queryRecep = "select staffs.* from staffs, receptionists where staffs.eid = receptionists.eid;";
$outputRecep = produceHtmlTable($queryRecep);

include "headerMain.php";
?>
    <h1>List of Staff</h1>
<?php echo $output; ?>
    <h1>List of Dentists</h1>
<?php echo $outputDentists; ?>
    <h1>List of Dental Assistants</h1>
<?php echo $outputDentAssist = produceHtmlTable($queryDentAssist); ?>
    <h1>List of Receptionists</h1>
<?php echo $outputRecep; ?>
    <a href="admin.php">Back to admin's page.</a>
<?php include "footerMain.php"; ?>