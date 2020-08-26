<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinic";
$dsn = "mysql:host=$servername;dbname=$dbname";
$options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
);


# Input: an sql query that is written as a string e.g.  "Select * from appts;"
# Output: Connects to the server and executes the query. Takes the output and produces a standard html table.
function produceHtmlTable($query) {
    global $servername, $dbname, $username, $password, $options;
    $output = "";
    try{
        $connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password, $options);
        $selecting = $connection->prepare($query);
        $selecting->execute();
        if($selecting->rowCount() > 0) {
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
        else{
            $output = "Output is empty.<br><br>";
        }
    }catch(PDOException $err) {
        if (preg_match('/^SQLSTATE\[.*]: (.*):/', strval($err->getMessage()), $matches)) {
            $err_msg = "<span class='error'>Something went wrong in your SQL command. Error message: " . $matches[1] . ".</span>";
            echo $err_msg;
        }
    }
    return $output;
}

