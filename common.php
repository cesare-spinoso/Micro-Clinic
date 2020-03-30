<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname     = "mvcsimulation"; // will use later
$dsn        = "mysql:host=$servername;dbname=$dbname"; // will use later
$options    = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
);

# Function for creating a table based on the sql query (fed in as function)
function sqlQuery($query, $servername, $dbname, $username, $password, $options){
    # Create connection
    $connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password, $options);
    $selecting = $connection->prepare($query);
    $selecting->execute();
    # Html output
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
    return $output;
}

function sqlQueryDisplay($query){
   echo "Using the query: <pre>$query</pre>";
}