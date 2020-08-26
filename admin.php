<?php
include "commonMain.php";

session_start();

if (!isset($_SESSION["signedin"]) || $_SESSION["signedin"] !== true) {
    header("location:https://mvc353.encs.concordia.ca/mainProject/adminHome.php");
    exit;
}

$err_msg = "";
$succ_msg = "";
$query = "";
$output = "";
$succ_output = "";
if (isset($_POST['submit'])) {
    if (empty($_POST['query'])) {
        $err_msg = "No input!!!";
    } else {
        $query = $_POST['query'];
        try {
            $connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password, $options);
            if (preg_match('/^select.*\s*from(.*\s*)*/i', $query)){
                $selecting = $connection->prepare($query);
                $selecting->execute();
                // Output into an html table
                $output = "";
                $output .= "<table border='1'>";
                $first_row = $selecting->fetch(PDO::FETCH_ASSOC);
                $output .= "<tr>";
                foreach (array_keys($first_row) as $key) {
                    $output .= "<th>{$key}</th>";
                }
                $output .= "</tr><tr>";
                foreach ($first_row as $val) {
                    $output .= "<td>{$val}</td>";
                }
                $output .= "</tr>";
                while ($row = $selecting->fetch(PDO::FETCH_ASSOC)) {
                    $output .= "<tr>";
                    foreach ($row as $val) {
                        $output .= "<td>{$val}</td>";
                    }
                    $output .= "</tr>";
                }
                $output .= "</table>";
            }
            else if (preg_match('/^insert\s+into\s+(\w+)(.*\s*)*/i', $query, $matches) ||
                preg_match('/^update\s+(\w+) (.*\s*)*/i', $query, $matches) ||
                preg_match('/^delete\s+from\s*(\w+) (.*\s*)*/i', $query, $matches)){
                $table = $matches[1];
                $connection->exec($query);
                $succ_msg = "Transaction successful. The new contents of $table are: <br>";
                $query = "select * from $table;";
                $succ_output = produceHtmlTable($query);
            }
            else if (preg_match('/drop\s+table (\s.)*/i', $query) ||
            preg_match('/create\s+table(.\s)*/', $query)){
                $connection->exec($query);
                $succ_msg = "DDL successful. The relational schema is now: <br>";
                $query = "show tables;";
                $succ_output = produceHtmlTable($query);
            }
            else if (preg_match('/alter\s+table\s+(\w*) (\s.)*/i', $query, $matches)){
                $table = $matches[1];
                $connection->exec($query);
                $succ_msg = "DDL successful. The new schema of $table is: <br>";
                $query = "desc $table;";
                $succ_output = produceHtmlTable($query);
            }
            else{
                $succ_output = produceHtmlTable($query);
            }
        } catch (PDOException $err) {
            # Use some regex to parse the error code
            if (preg_match('/^SQLSTATE\[.*]: (.*):/', strval($err->getMessage()), $matches)) {
                $err_msg = "Something went wrong in your SQL command. Error message: " . $matches[1] . ".";
                $output = "";
            }
        } catch(Exception $err) {
            $err_msg = "Something went wrong.";
        }
    }
}

include "headerMain.php"; ?>
<h1>Administrator Page</h1>
<h2>Query Box</h2>
<form method="post">
    <label for="query">SQL Statement:</label>
    <textarea name="query" id="query" rows="5" cols="30"></textarea><br>
    <span class="error"><?php echo $err_msg ?></span><br>
    <input type="submit" name="submit" value="SUBMIT">
</form>
<span class="success"><?php echo $succ_msg ?></span>
<span><?php echo $succ_output ?></span>
<span><?php echo $output == "" ? "" : "<h3>The output is</h3>" . $output ?></span>
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
