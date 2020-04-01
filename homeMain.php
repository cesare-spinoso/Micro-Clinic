<?php
include "headerMain.php";
?>
<h1>Comp 353 - Main</h1>
<h2>What do you want to do?</h2>
<h3>Query the database</h3>
<ul>
    <li>Get all the details of dentists in all clinics <a href="getDentists.php">here</a>.</li>
    <li>Get details of all appointments for a given dentist for a specific week <a href="getApptDentists.php">here</a>.</li>
    <li>Get details of all appointments at a given clinic on a specific date <a href="getApptClinic.php">here</a>.</li>
    <li>Get details of all appointments of a given patient <a href="getApptPatient.php">here</a>.</li>
    <li>Get the number of missed appointments for each patient </li>
    <li></li>
    <li></li>
</ul>
<h3>Make a transaction</h3>
<ul>
    <li>Add a patient <a href="addPatient.php">here</a>.</li>
    <li>Add a staff member <a href="addStaff.php"> here</a>.</li>
    <li>Add an appointment <a href="addAppt.php"> here</a>.</li>
</ul>
<?php include "footerMain.php"; ?>
