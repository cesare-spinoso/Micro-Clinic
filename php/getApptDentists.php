<?php
include "headerMain.php"; # This contains all the header info so don't need to repeat the same code
include "commonMain.php"; # This contains the db info and the helper function  produceHtmlTable (see commonMain.php for documentation)
?>
<!--Here provide the SIN for the dentist (alternate key)/ could also do EID. I imagine it might be easier
for a user to remember their SIN than their EID. Also provide a way of picking the week and somehow turn this into a sunday
(of that week).
Make sure the year input is formatted as yyyy-mm-dd HH:MM:SS when you pass it into sql.-->

<h2>Query b: Get details of all appointments for a given dentist for a specific week</h2>
<form method="post">
    <fieldset>
        <legend>get all appointments for a dentist for a specific week:</legend>
        <label for="hin">Social Insurance Number (SIN):
            <input type="text" name="sin" id="sin" placeholder="0000">
        </label>
        <label for="date">Date: <input type="date" name="date" id="date">
        </label>
    </fieldset>
    <input type="submit" name="submit" value="Search">
</form>

<?php
if(isset($_POST['submit'])){
    $sin = $_POST['sin'];
    $date = date('Y-m-d H:i:s', strtotime('last Sunday', strtotime($_POST['date'])));

    $query = "
select patients.fName as patientFName,
       patients.lName as patientLName,
       appointments.dateAndTime,
       S2.fName       as recepFName,
       S2.lName       as recepLName,
       appointments.status
from appointments,
     patients,
     staffs S1,
     staffs S2
where 
    DATEDIFF(appointments.dateAndTime, '$date') <7
    and DATEDIFF(appointments.dateAndTime, '$date') >=0
    and appointments.pid = patients.pid
    and {$sin} = S1.sin
    and appointments.eidDentist = S1.eid
    and appointments.eidReceptionist = S2.eid
    order by dateAndTime";

    $output = produceHtmlTable($query);

    echo '<br><h3>Appointments:</h3>';
    echo $output;
}
?>
<a href="homeMain.php">Go home.</a>
<?php include "footerMain.php"; ?>
