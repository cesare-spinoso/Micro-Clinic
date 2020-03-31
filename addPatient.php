<?php
include "headerMain.php";
include "commonMain.php";
$no_empty_fields = true;
$no_format_err = true;
$submit_arr = array("hin" => "", "fName" => "", "lName" => "", "address" => "", "phoneNumber" => "");
$err_arr = array("hin" => "", "fname" => "", "lName" => "", "address" => "", "phoneNumber" => "");
$isempty_arr = array("hin" => false, "fname" => false, "lName" => false, "address" => false, "phoneNumber" => false);
$succ_msg = "";
$err_msg = "";
if (isset($_POST["submit"])) {
    foreach ($submit_arr as $key => $value) {
        if (empty($_POST[$key])) {
            $err_arr[$key] = "You must enter a " . $key . "!";
            $no_empty_fields = false;
            $isempty_arr[$key] = true;
        }
    }
    foreach ($submit_arr as $key => $value) {
        if ($key == "fName" || $key == "lName") {
            if(!$isempty_arr[$key]) {
                if (!preg_match('/[a-zA-Z]+/', $_POST[$key])) {
                    $err_arr[$key] = "Invalid format. Enter a string of alphabetical characters only.";
                    $no_format_err = false;
                }
                else{
                    $submit_arr[$key] = $_POST[$key];
                }
            }
        }
        if ($key == "hin") {
            if (!$isempty_arr[$key] && !preg_match('/[a-zA-Z]+\d+/', $_POST[$key])) {
                $err_arr[$key] = "Invalid format. Enter characters then numbers.";
                $no_format_err = false;
            }
            else{
                $submit_arr[$key] = $_POST[$key];
            }
        }
        if ($key == "phoneNumber") {
            if (!$isempty_arr[$key] && !preg_match('/\d{3}-\d{3}-\d{4}/', $_POST[$key])) {
                $err_arr[$key] = "Invalid format. Enter characters then numbers.";
                $no_format_err = false;
            }
            else{
                $submit_arr[$key] = $_POST[$key];
            }
        }
        if ($key == "address") {
            if (!$isempty_arr[$key] && !preg_match('/\d+\s+[a-zA-Z]+\s+[a-zA-Z]+/', $_POST[$key])) {
                $err_arr[$key] = "Invalid format. Enter characters then numbers.";
                $no_format_err = false;
            }
            else{
                $submit_arr[$key] = $_POST[$key];
            }
        }
    }
    if ($no_empty_fields && $no_format_err) {
        try {
            $connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password, $options);
            // First check if the entry already exists use the hin, since this is an alternate key
            $sql = "SELECT hin FROM patients;";
            $select = $connection->prepare($sql);
            $select->execute();
            while ($row = $select->fetch(PDO::FETCH_NUM)) {
                if ($row[0] == $submit_arr['hin']) {
                    $err_msg = "Patient info not added. The HIN {$submit_arr['hin']} already exists!!!";
                    break;
                }
            }
            // If the entry does not already exists, add it, get the last pid and add 1
            if ($err_msg === '') {
                // First get the last pid
                $sql = "SELECT max(pid) FROM patients;";
                $select = $connection->prepare($sql);
                $select->execute();
                $row = $select->fetch(PDO::FETCH_NUM);
                $last_pid = $row[0];
                $new_pid = $last_pid + 1;
                //
                $sql = "INSERT INTO patients VALUES({$new_pid}, '{$submit_arr['hin']}', '{$submit_arr['fName']}', '{$submit_arr['lName']}',
                            '{$submit_arr['address']}', '{$submit_arr['phoneNumber']}');";
                $connection->exec($sql);
                $succ_msg = "Successfully added " . $submit_arr['fName'] . " " . $submit_arr['lName'] . " to the Database!!!
                Please note that the pid given is {$new_pid}.";
            }
        } catch (PDOException $e) {
            print_r("In here unfortunately");
            print_r($e->getMessage());
        }
    }
}
?>
<h2>Add a patient</h2>
<span class="success"><?php echo "$succ_msg"; ?></span>
<span class="error"><?php echo "$err_msg"; ?></span>
<form method="post">
    <label for="hin">Health Insurance Number (HIN):
        <input type="text" name="hin" id="hin" placeholder="JOHN12345">
        <span class="error"><?php echo $err_arr["hin"] ?></span>
    </label>
    <label for="fName">First Name: <input type="text" name="fName" id="fName" placeholder="John">
        <span class="error"><?php echo $err_arr["fName"] ?></span>
    </label>
    <label for="lName">Last Name: <input type="text" name="lName" id="lName" placeholder="Doe">
        <span class="error"><?php echo $err_arr["lName"] ?></span>
    </label>
    <label for="address">Studio name:<input type="text" name="address" id="address" placeholder="123 Dream street">
        <span class="error"><?php echo $err_arr["address"] ?></span>
    </label>
    <label for="phoneNumber">Studio name:<input type="text" name="phoneNumber" id="phoneNumber"
                                                placeholder="123-456-7890">
        <span class="error"><?php echo $err_arr["phoneNumber"] ?></span>
    </label>
    <input type="submit" name="submit" value="SUBMIT">
</form>
<a href="homeMain.php">Go home.</a>
<?php include "footerMain.php"; ?>
