<?php
include "header.php";
include "common.php";
$servername = "mvc353.encs.concordia.ca";
$username = "mvc353_4";
$password = "Potatoe1";
$dbname     = "mvc353_4"; // will use later
$query = "SELECT name, dob, position
FROM player
WHERE name like 'T%';";
sqlQueryDisplay($query);
try{
    # Create connection
    $connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
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
}catch(PDOException $e){
    print_r($e->getMessage());
}
?>
<span><?php echo $output; ?></span>
<? include "footer.php"; ?>
