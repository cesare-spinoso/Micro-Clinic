<?php include "headerMain.php"; include "common.php"; ?>
<?php
$no_empty_fields = true;
$no_format_err = true;
$submit_arr = array("name" => "", "dob" => "");
$err_arr = array("name" => "", "dob" => "");
$succ_msg = "";
$err_msg = "";
if(isset($_POST["submit"])){
    foreach($submit_arr as $key => $value){
        if(empty($_POST[$key])){
            $err_arr[$key] = "You must enter a " . $key;
            $no_empty_fields = false;
        }
        else{
            if($key == 'name' && !preg_match('/[a-zA-Z\s]+/', $_POST[$key])){
                $err_arr[$key] = "Invalid format. Enter a string.";
                $no_format_err = false;
                continue;
            }
            elseif ($key == 'dob' && !preg_match('/\d{4}-(0[1-9]|1[0-2])-(0[1-9]|1[0-9]|2[0-9]|30|31)/', $_POST[$key])){
                $err_arr[$key] = "Invalid date format. Enter a yyyy-mm-dd.";
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
            $sql = "SELECT name FROM Star"; //title + year form the key
            $select = $connection->prepare($sql);
            $select->execute();
            while($row = $select->fetch(PDO::FETCH_NUM)){
                if($row[0] == $submit_arr['name']){
                    $err_msg = "Entry not added. {$submit_arr['name']} already exists!!!";
                    break;
                }
            }
            // If the entry does not already exists, add it
            if($err_msg === '') {
                $sql = "INSERT INTO Star VALUES('{$submit_arr['name']}','{$submit_arr['dob']}');";
                $connection->exec($sql);
                $succ_msg = "Successfully added {$submit_arr['name']} born in {$submit_arr['dob']} to the Database!!!";
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
    <label for="name">Movie star:
        <input type="text" name="name" id="name">
        <span class="error"><?php echo $err_arr["name"]?></span>
    </label>
    <label for="dob">Date of birth: <input type="text" name="dob" id="dob" placeholder="yyyy-mm-dd">
        <span class="error"><?php echo $err_arr["dob"]?></span>
    </label>
    <input type="submit" name="submit" value="SUBMIT">
</form>
<a href="home.php">Go home.</a>

<?php include "footerMain.php"; ?>
