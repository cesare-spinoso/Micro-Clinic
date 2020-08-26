<?php
include "headerMain.php"; # This contains all the header info so don't need to repeat the same code
include "commonMain.php"; # This contains the db info and the helper function  produceHtmlTable (see commonMain.php for documentation)
?>
<!--Here no input is required. -->

<h2>Query a: Get details of all dentists in all the clinics</h2>

<?php
    function printQuery(){
        $query = "select staffs.*, worksIn.address, worksIn.cname
from staffs, dentists, worksIn
where staffs.eid = dentists.eid and
      staffs.eid = worksIn.eid
order by staffs.lName";
        $output = produceHtmlTable($query);

        echo $output;
    }
   printQuery();
?>

<a href="homeMain.php">Go home.</a>
<?php include "footerMain.php"; ?>
