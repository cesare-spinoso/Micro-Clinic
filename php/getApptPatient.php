<?php
include "headerMain.php"; # This contains all the header info so don't need to repeat the same code
include "commonMain.php"; # This contains the db info and the helper function  produceHtmlTable (see commonMain.php for documentation)
?>
<!--Here provide the hin maybe fName and lName.-->

<h2>Query d : Get details of all appointments of a given patient</h2>
<form method="post">
    <fieldset>
        <legend>Patient info:</legend>
        <label for="hin">Health Insurance Number (HIN):
            <input type="text" name="hin" id="hin" placeholder="JOHN12345">
        </label>
    </fieldset>
    <input type="submit" name="submit" value="Search">
</form>

<?php
if(isset($_POST['submit'])){
    $hin = $_POST['hin'];

    $query = "select appointments.*
from patients, appointments
where patients.pid = appointments.pid and
      patients.hin = '$hin'  ";

    $output = produceHtmlTable($query);

    echo '<br><h3>Appointments:</h3>';
    echo $output;
}
?>
<a href="homeMain.php">Go home.</a>
<?php include "footerMain.php"; ?>
