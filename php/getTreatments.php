<?php
include "headerMain.php"; # This contains all the header info so don't need to repeat the same code
include "commonMain.php"; # This contains the db info and the helper function  produceHtmlTable (see commonMain.php for documentation)
?>
<!--Here provide the hin (alternate key for patient), and dateTime as input.
 Make sure the year input is formatted as yyyy-mm-dd HH:MM:SS when you pass it into sql.-->

<h2>Query f: Get details of all the treatments made during a given appointment</h2>
<form method="post">
    <fieldset>
        <legend>Appointment info:</legend>
        <label for="hin">Health Insurance Number (HIN):
            <input type="text" name="hin" id="hin" placeholder="JOHN12345">
        </label>
        <label for="date">Date: <input type="date" name="date" max="<?php echo date("Y-m-d"); ?>" id="date">
        </label>
    </fieldset>
    <input type="submit" name="submit" value="Search">
</form>

<?php
if(isset($_POST['submit'])){
    $hin = $_POST['hin'];
    $date = $_POST['date'];
    $queryName = "select fName, lName from patients where hin = '$hin'";
    $outputName = produceHtmlTable($queryName);

    $query = "select executedBy.treatmentName, executedBy.toothNumber, staffs.fName as staffFName, staffs.lName as staffLName
from executedBy, patients, staffs
where executedBy.pid = patients.pid and cast(executedBy.dateAndTime as date) = '$date' and
      patients.hin = '$hin' and executedBy.eidDentalWorker = staffs.eid";
    $output = produceHtmlTable($query);

    echo "<br><h4>Treaments for patient:</h4>";
    echo $outputName;
    echo "<br>";
    echo $output;
}
?>

<a href="homeMain.php">Go home.</a>
<?php include "footerMain.php"; ?>


