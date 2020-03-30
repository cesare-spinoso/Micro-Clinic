<?php
include "commonMain.php";

session_start();

if(!isset($_SESSION["signedin"]) || $_SESSION["signedin"] !== true){
    header("location:https://mvc353.encs.concordia.ca/adminHome.php");
    exit;
}

$err_msg = "";
$query = "";
$output = "";
if(isset($_POST['submit'])){
    if(empty($_POST['query'])){
        $err_msg = "No input!!!";
    }
    else{
        $query = $_POST['query'];
        try{
            $connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password, $options);
            $selecting = $connection->prepare($query);
            $selecting->execute();
            // Output into an html table
            $output = "";
            $output .= "<table border='1'>";
            $first_row = $selecting->fetch(PDO::FETCH_ASSOC);
            $output .= "<tr>";
            foreach(array_keys($first_row) as $key){
                $output .= "<th>{$key}</th>";
            }
            $output .= "</tr><tr>";
            foreach($first_row as $val){
                $output .= "<td>{$val}</td>";
            }
            $output .= "</tr>";
            while($row = $selecting->fetch(PDO::FETCH_ASSOC)){
                $output .= "<tr>";
                foreach ($row as $val){
                    $output .=  "<td>{$val}</td>";
                }
                $output .= "</tr>";
            }
            $output .= "</table>";
        }catch(PDOException $err) {
            echo $err->getMessage();
        }
    }
}

include "headerMain.php"; ?>
<h1>Administrator Page</h1>
<h2>Query Box</h2>
<form method="post">
    <label for="query">SQL Query:</label>
    <textarea name="query" id="query" rows="5" cols="30"></textarea><br>
    <span class="error"><?php echo $err_msg ?></span><br>
    <input type="submit" name="submit" value="SUBMIT">
</form>
<span><?php echo $output == "" ? "":"<h3>The output is</h3>" . $output ?></span>
<h2>List of Tables</h2>
<ul>
    <li>Click <a href="patientsTable.php">here</a> to see the list of registered patients.</li>
    <li>Click <a href="staffsTable.php">here</a> to see the list of registered staff.</li>
    <li>Click <a href="clinicsTable.php">here</a> to see the list of registered clinics.</li>
    <li>Click <a href="apptsTable.php">here</a> to see the history of appointments.</li>
    <li>Click <a href="treatmentsTable.php">here</a> to see the list of treatments.</li>
</ul>
<a href="signoutAdmin.php">Sign me out please!</a>

<?php include "footerMain.php"; ?>
