<?php
include "headerMain.php"; # This contains all the header info so don't need to repeat the same code
include "commonMain.php"; # This contains the db info and the helper function  produceHtmlTable (see commonMain.php for documentation)


?>
    <!--Here provide the cname, address and dateTime as input.
     Make sure the year input is formatted as yyyy-mm-dd HH:MM:SS when you pass it into sql.-->
<h2>Query g: Get details of all unpaid bills</h2>
    <h3>List of all bills</h3>
<?php
$sqlAllBills = "select bills.pid as pid, bills.dateAndTime as dateTime, bills.status as billStatus
from bills;";
$outputAll = produceHtmlTable($sqlAllBills);
echo $outputAll;
?>
    <h3>Further details on all unpaid bills</h3>
<?php
# Sql statement for the bill
$sqlUnpaid = "select patients.fName as PatientFirstName, patients.lName as PatientLastName,  bills.pid as Pid, bills.dateAndTime as dateAndTime,
       bills.status as BillStatus, staffs.lName as ReceptionistFirstName, staffs.fName as ReceptionistLastName,  appointments.cname as ClinicName, appointments.address as ClinicAddress
from bills, patients, staffs, appointments
where bills.pid = patients.pid and
      appointments.pid = bills.pid and appointments.dateAndTime = bills.dateAndTime and
      bills.eidReceptionist = staffs.eid and
      bills.status = 'unpaid'
order by bills.dateAndTime, bills.pid;";
# Connection and execution
$connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password, $options);
$selecting = $connection->prepare($sqlUnpaid);
$selecting->execute();
# The table header
$tableHeader = "";
$tableHeader .= "<table border='1'>";
$first_row = $selecting->fetch(PDO::FETCH_ASSOC);
$tableHeader .= "<tr>";
foreach (array_keys($first_row) as $key) {
    $tableHeader .= "<th>{$key}</th>";
}
$tableHeader .= "</tr>";
# Number of rows in the select
$numUnpaidBills = $selecting->rowCount();
$outputUnpaid = "";
for ($i = 1; $i <= $numUnpaidBills; $i++) {
    $outputTemp = "<fieldset>";
    $outputTemp .= $tableHeader;
    if($i == 1){
        $row = $first_row;
    }
    else {
        $row = $selecting->fetch(PDO::FETCH_ASSOC);
    }
    $outputTemp .= "<tr>";
    foreach ($row as $val){
        $outputTemp .=  "<td>{$val}</td>";
    }
    $outputTemp .= "</tr>";
    $outputTemp .= "</table>";
    # The treatements charged in the bill
    $sqlTreatments = "select treatments.name, treatments.toothNumber, treatments.cost 
                      from billedIn, treatments
                      where {$row['Pid']} = billedIn.pid and '{$row['dateAndTime']}' = billedIn.dateAndTime and
                      billedIn.treatmentName = treatments.name and billedIn.toothNumber = treatments.toothNumber;";
    $outputTemp .= produceHtmlTable($sqlTreatments);
    # The total of each bill
    $sqlTotal = "select sum(treatments.cost) as Total
                      from billedIn, treatments
                      where {$row['Pid']} = billedIn.pid and '{$row['dateAndTime']}' = billedIn.dateAndTime and
                      billedIn.treatmentName = treatments.name and billedIn.toothNumber = treatments.toothNumber;";
    $outputTemp .= produceHtmlTable($sqlTotal);
    $outputTemp .= "</fieldset>";
    $outputUnpaid .= $outputTemp;
}

echo $outputUnpaid;
?>
    <a href="homeMain.php">Go home.</a>
<?php include "footerMain.php"; ?>
<?php
