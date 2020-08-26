<?php
include "headerMain.php"; # This contains all the header info so don't need to repeat the same code
include "commonMain.php"; # This contains the db info and the helper function  produceHtmlTable (see commonMain.php for documentation)
?>

<h2>Query e: Get the number of missed appointments for each patient</h2>
<?php
$query = "select patients.pid, patients.fName, patients.lName, count(*), 'missed appointment(s)' as missed
from appointments, patients
where appointments.pid = patients.pid and appointments.status = 'missed'
group by patients.pid";

$output = produceHtmlTable($query);
if($output){
echo '<h3>Missed Appointments:</h3>';
echo $output;
}
?>

<a href="homeMain.php">Go home.</a>
<?php include "footerMain.php"; ?>
