<?php include "header.php"; include "common.php";
$no_input_err = true;
$err_msg = "";
$query = "";
$output = "";
if(isset($_POST['submit'])){
    if(empty($_POST['query'])){
        $err_msg = "No input!!!";
        $no_input_err = false;
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
?>

<h2>Query the Database</h2>
<span class="success"></span>
<span class="error"></span>
<form method="post">
    <label for="query">SQL Query:</label>
    <textarea name="query" id="query" rows="5" cols="30"></textarea><br>
    <span class="error"><?php echo $err_msg ?></span><br>
    <input type="submit" name="submit" value="SUBMIT">
</form>
<span><?php echo $output ?></span>
<a href="home.php">Go home.</a>
<?php include "footer.php" ?>
