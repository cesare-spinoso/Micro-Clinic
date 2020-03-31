<?php
include "headerMain.php";
include "commonMain.php";
$no_empty_fields = true;
$no_format_err = true;
$submit_arr = array("sin" => "", "fName" => "", "lName" => "", "phoneExtension" => "", "role" => $_POST['role']);
$err_arr = array("sin" => "", "fName" => "", "lName" => "", "phoneExtension" => "");
$isempty_arr = array("sin" => false, "fname" => false, "lName" => false, "phoneExtension" => false);
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
            if (!$isempty_arr[$key]) {
                if (!preg_match('/[a-zA-Z]+/', $_POST[$key])) {
                    $err_arr[$key] = "Invalid format. Enter a string of alphabetical characters only.";
                    $no_format_err = false;
                } else {
                    $submit_arr[$key] = $_POST[$key];
                }
            }
        }
        if ($key == "sin") {
            if (!$isempty_arr[$key] && !preg_match('/\d+/', $_POST[$key])) {
                $err_arr[$key] = "Invalid format. Enter characters then numbers.";
                $no_format_err = false;
            } else {
                $submit_arr[$key] = $_POST[$key];
            }
        }
        if ($key == "phoneExtension") {
            if (!$isempty_arr[$key] && !preg_match('/514-123-4567 ext\. \d+/', $_POST[$key])) {
                $err_arr[$key] = "Invalid format. Enter 514-123-4567 ext. then your ";
                $no_format_err = false;
            } else {
                $submit_arr[$key] = $_POST[$key];
            }
        }
    }
    if ($no_empty_fields && $no_format_err) {
        try {
            $connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password, $options);
            // First check if the entry already exists use the hin, since this is an alternate key
            $sql = "SELECT sin FROM staffs;";
            $select = $connection->prepare($sql);
            $select->execute();
            while ($row = $select->fetch(PDO::FETCH_NUM)) {
                if ($row[0] == $submit_arr['sin']) {
                    $err_msg = "Staff info not added. The SIN {$submit_arr['sin']} already exists!!!";
                    break;
                }
            }
            // If the entry does not already exists, add it, get the last pid and add 1
            if ($err_msg === '') {
                // First get the last pid
                $sql = "SELECT max(eid) FROM staffs;";
                $select = $connection->prepare($sql);
                $select->execute();
                $row = $select->fetch(PDO::FETCH_NUM);
                $last_eid = $row[0];
                $new_eid = $last_eid + 1;
                //
                $sql = "INSERT INTO staffs VALUES({$new_eid}, {$submit_arr['sin']}, '{$submit_arr['fName']}', '{$submit_arr['lName']}',
                            '{$submit_arr['phoneExtension']}');";
                $connection->exec($sql);
                if($submit_arr['role'] != "other") {
                    $sql = "INSERT INTO {$submit_arr['role']} VALUES({$new_eid});";
                }
                $connection->exec($sql);
                $succ_msg = "Successfully added " . $submit_arr['fName'] . " " . $submit_arr['lName'] . " to the Database!!!
                Please note the staff eid given is {$new_eid}.";
            }
        } catch (PDOException $e) {
            print_r("In here unfortunately");
            print_r($e->getMessage());
        }
    }
}
?>
<h2>Add a staff member</h2>
<span class="success"><?php echo "$succ_msg"; ?></span>
<span class="error"><?php echo "$err_msg"; ?></span>
<form method="post">
    <fieldset>
    <label for="hin">Social Insurance Number (HIN):
        <input type="text" name="sin" id="sin" placeholder="9417">
        <span class="error"><?php echo $err_arr["sin"] ?></span>
    </label>
    <label for="fName">First Name: <input type="text" name="fName" id="fName" placeholder="John">
        <span class="error"><?php echo $err_arr["fName"] ?></span>
    </label>
    <label for="lName">Last Name: <input type="text" name="lName" id="lName" placeholder="Doe">
        <span class="error"><?php echo $err_arr["lName"] ?></span>
    </label>
    <label for="phoneExtension">Phone Extension:<input type="text" name="phoneExtension" id="phoneExtension"
                                                       placeholder="514-123-4567 ext. 1111">
        <span class="error"><?php echo $err_arr["phoneExtension"] ?></span>
    </label>
    <label for="role">Enter your new position: </label>
    <select id="role" name="role">
        <option value="receptionists">Receptionist</option>
        <option value="dentists">Dentist</option>
        <option value="dentalAssistants">Dental Assistant</option>
        <option value="other" selected>Other</option>
    </select><br>
    </fieldset>
    <input type="submit" name="submit" value="SUBMIT">
</form>
<a href="homeMain.php">Go home.</a>
<?php include "footerMain.php"; ?>
