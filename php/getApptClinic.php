<?php
include "headerMain.php"; # This contains all the header info so don't need to repeat the same code
include "commonMain.php"; # This contains the db info and the helper function  produceHtmlTable (see commonMain.php for documentation)
?>
<!--Here provide the cname, address and dateTime as input.
 Make sure the year input is formatted as yyyy-mm-dd HH:MM:SS when you pass it into sql.-->

<h2>Query c: Get details of all appointments at a given clinic on a specific date</h2>
<form method="post">
    <fieldset>
        <legend>Get details of all appointments at a given clinic on a specific date:</legend>

        <label for="cname"> Clinic Name: <input type="text" name="cname" id="cname" placeholder="The Holley Clinic">
        </label>

        <label for="address">Address: <input type="text" name="address" id="address" placeholder="6543 Holley street">
        </label>

        <label for="date">Date: <input type="date" name="date" id="date">
        </label>

    </fieldset>
    <input type="submit" name="submit" value="Search">
</form>

<?php
if(isset($_POST['submit'])){
    $cname = $_POST['cname'];
    $date =  $_POST['date'];
    $address = $_POST['address'];
    $query = "
    select patients.fName as patientFName,
       patients.lName as patientLName,
       appointments.dateAndTime,
       S1.fName       as dentistFName,
       S1.lName       as dentistLName,
       S2.fName       as recepFName,
       S2.lName       as recepLName,
       appointments.status
from appointments,
     patients,
     dentists,
     receptionists,
     staffs S1,
     staffs S2
where appointments.cname = '$cname'
    and appointments.address = '$address'
    and cast(appointments.dateAndTime as date) = '$date'
    and appointments.pid = patients.pid
    and appointments.eidDentist is not null
    and appointments.eidDentist = dentists.eid
    and dentists.eid = S1.eid
    and appointments.eidReceptionist = receptionists.eid
    and receptionists.eid = S2.eid
union
select patients.fName as patientFName,
       patients.lName as patientLName,
       appointments.dateAndTime,
       'TBD'          as dentistFName,
       'TBD'          as dentistLName,
       S2.fName       as recepFName,
       S2.lName       as recepLName,
       appointments.status
from appointments,
     patients,
     receptionists,
     staffs S2
where appointments.cname = '$cname'
    and appointments.address = '$address'
    and cast(appointments.dateAndTime as date) = '$date'
    and appointments.pid = patients.pid
    and appointments.eidDentist is null
    and appointments.eidReceptionist = receptionists.eid
    and receptionists.eid = S2.eid";

    $output = produceHtmlTable($query);

    echo '<br><h3>Output:</h3>';
    echo $output;
}
?>
<a href="homeMain.php">Go home.</a>
<?php include "footerMain.php"; ?>
