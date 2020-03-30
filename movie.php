<?php include "headerMain.php"; include "common.php"; ?>
<?php
$no_empty_fields = true;
$no_format_err = true;
$submit_arr = array("title" => "", "year" => "", "length" => "", "studioName" => "");
$err_arr = array("title" => "", "year" => "", "length" => "", "studioName" => "");
$succ_msg = "";
$err_msg = "";
if(isset($_POST["submit"])){
    foreach($submit_arr as $key => $value){
        if(empty($_POST[$key])){
            $err_arr[$key] = "You must enter a " . $key;
            $no_empty_fields = false;
        }
        else{
                if(($key == 'length' || $key == 'year') &&
                    !preg_match('/\d+/', $_POST[$key])){
                    $err_arr[$key] = "Invalid format. Enter an integer.";
                    $no_format_err = false;
                    continue;
                }
            $submit_arr[$key] = $_POST[$key];
        }
    }
    if($no_empty_fields && $no_format_err){
        try{
            $connection = new PDO("mysql:host=$servername;dbname=$dbname",$username, $password, $options);
            // First check if the entry already exists
            $sql = "SELECT title, year FROM Movie"; //title + year form the key
            $select = $connection->prepare($sql);
            $select->execute();
            while($row = $select->fetch(PDO::FETCH_NUM)){
                if($row[0] == $submit_arr['title'] && $row[1] == $submit_arr['year']){
                    $err_msg = "Entry not added. {$submit_arr['title']} already exists!!!";
                    break;
                }
            }
            // If the entry does not already exists, add it
            if($err_msg === '') {
                $sql = "INSERT INTO Movie VALUES('" . $submit_arr['title'] . "', " . $submit_arr['year'] . ", " .
                    $submit_arr['length'] . ", '" . $submit_arr['studioName'] . "');";
                $connection->exec($sql);
                $succ_msg = "Successfully added " . $submit_arr['title'] . " to the Database!!!";
            }
        }catch(PDOException $e){
            print_r($e->getMessage());
        }
    }
}
//print_r($_POST);
//print_r($submit_arr);
//print_r($err_arr);
?>
<h2>Movies Table</h2>
<span class="success"><?php echo "$succ_msg"; ?></span>
<span class="error"><?php echo "$err_msg"; ?></span>
<form method="post">
   <label for="title">Movie title:
       <input type="text" name="title" id="title">
   <span class="error"><?php echo $err_arr["title"]?></span>
   </label>
    <label for="year">Movie year: <input type="text" name="year" id="year">
        <span class="error"><?php echo $err_arr["year"]?></span>
    </label>
    <label for="length">Movie length: <input type="text" name="length" id="length">
        <span class="error"><?php echo $err_arr["length"]?></span>
    </label>
    <label for="studioName">Studio name:<input type="text" name="studioName" id="studioName">
        <span class="error"><?php echo $err_arr["studioName"]?></span>
    </label>
    <input type="submit" name="submit" value="SUBMIT">
</form>
<a href="home.php">Go home.</a>

<?php include "footerMain.php"; ?>
