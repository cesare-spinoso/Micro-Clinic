<?php include "headerMain.php"; include "common.php"; ?>
<?php
$no_empty_fields = true;
$no_format_err = true;
$submit_arr = array("title" => "", "year" => "", "starName" => "");
$err_arr = array("title" => "", "year" => "", "starName" => "");
$succ_msg = "";
$err_msg = "";
if(isset($_POST["submit"])){
    foreach($submit_arr as $key => $value){
        if(empty($_POST[$key])){
            $err_arr[$key] = "You must enter a " . $key;
            $no_empty_fields = false;
        }
        else{
            if($key == 'starName' && !preg_match('/[a-zA-Z\s]+/', $_POST[$key])){
                $err_arr[$key] = "Invalid format. Enter a string.";
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
            $sql = "SELECT title, year, starName FROM StarsIn"; //title + year form the key
            $select = $connection->prepare($sql);
            $select->execute();
            while($row = $select->fetch(PDO::FETCH_NUM)){
                if($row[0] == $submit_arr['title'] && $row[1] == $submit_arr['year'] && $row[2] == $submit_arr['starName']){
                    $err_msg = "Entry not added. It already exists!!!";
                    break;
                }
            }
            // If the entry does not already exists, add it
            if($err_msg === '') {
                $sql = "INSERT INTO StarsIn VALUES('{$submit_arr['title']}',{$submit_arr['year']},'{$submit_arr['starName']}');";
                $connection->exec($sql);
                $succ_msg = "Successfully added {$submit_arr['title']} starring {$submit_arr['starName']} to the Database!!!";
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
<h2>StarsIn Table</h2>
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
    <label for="starName">Movie star: <input type="text" name="starName" id="starName">
        <span class="error"><?php echo $err_arr["starName"]?></span>
    </label>
    <input type="submit" name="submit" value="SUBMIT">
</form>
<a href="home.php">Go home.</a>

<?php include "footerMain.php"; ?>
