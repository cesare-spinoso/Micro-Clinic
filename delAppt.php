<?php
include "headerMain.php";
include "commonMain.php";
# general variables
$result_explode = explode('---', $result);
$current_date = new DateTime('2020-03-15 09:00:00');
$no_empty_fields = true;
$no_format_err = true;
$submit_arr = array("hin" => "", "fName" => "", "lName" => "", "date" => $_POST['date'], "time" => $_POST['time']);
$err_arr = array("hin" => "", "fName" => "", "lName" => "", "date" => "", "time" => "");
$isempty_arr = array("hin" => false, "fname" => false, "lName" => false, "date" => false, "time" => false);
$succ_msg = "";
$err_msg = "";
if (isset($_POST["submit"])) {
    foreach ($submit_arr as $key => $value) {
        if ($key != "cname" && $key != "address" && empty($_POST[$key])) {
            $err_arr[$key] = "You must enter a " . $key . "!";
            $no_empty_fields = false;
            $isempty_arr[$key] = true;
        }
    }
    foreach ($submit_arr as $key => $value) {
        if ($key == "fName" || $key == "lName") {
            if (!$isempty_arr[$key]) {
                if (!preg_match('/^[a-zA-Z]+$/', $_POST[$key])) {
                    $err_arr[$key] = "Invalid format. Enter a string of alphabetical characters only.";
                    $no_format_err = false;
                } else {
                    $submit_arr[$key] = $_POST[$key];
                }
            }
        }
        if ($key == "hin") {
            if (!$isempty_arr[$key] && !preg_match('/^[a-zA-Z]+\d+$/', $_POST[$key])) {
                $err_arr[$key] = "Invalid format. Enter characters then numbers.";
                $no_format_err = false;
            } else {
                $submit_arr[$key] = $_POST[$key];
            }
        }
        $dateTime = $submit_arr['date'] . ' ' . $submit_arr['time'];
        $asDate = new DateTime($dateTime);
        $dateTime .= ':00';
        if($key == "date"){
            if(!$isempty_arr[$key]){
                if($asDate < $current_date){
                    $err_arr[$key] = "Invalid date. Can only delete dates starting from March 15th 2020.";
                    $no_format_err = false;
                }
            }
        }
        if($key == "time"){
            if(!$isempty_arr[$key]){
                if(!preg_match('/\d{4}-\d{2}-\d{2} (09|10|11|12|13|14|15|16):00:00/', $dateTime)){
                    $err_arr[$key] = "Invalid time. Appointments are made from 9AM to 4PM. Every hour.";
                    $no_format_err = false;
                }
            }
        }
    }
    if ($no_empty_fields && $no_format_err) {
        try {
            $connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password, $options);
            // First check if the entry already exists use the hin, since this is an alternate key
            $sql = "SELECT pid FROM patients WHERE hin = '{$submit_arr['hin']}';";
            $select = $connection->prepare($sql);
            $select->execute();
            $num_rows = $select->rowCount();
            if($num_rows == 0){
                $err_msg = "Could not find your Health Insurance Number!";
            }
            else{
                $row = $select->fetch(PDO::FETCH_NUM);
                $pid = $row[0];

                # Get that time slot
                $sql = "select pid, dateAndTime
                        from appointments
                        where dateAndTime = '{$dateTime}' and
                              pid = {$pid};";
                $select = $connection->prepare($sql);
                $select->execute();
                $row = $select->fetch(PDO::FETCH_NUM);
                if($select->rowCount() == 0){
                    $err_msg = "No appointment matches this info. Nothing deleted.";
                }
                else{
                    $sql = "delete from appointments where dateAndTime = '$dateTime' and pid = {$pid};";
                    $connection->exec($sql);
                    $succ_msg = "Successfully deleted you appointment on {$dateTime}. See you!";
                }
            }

        } catch (PDOException $e) {
            $err_msg =  "Error deleting record: " . $connection->errorCode();
            print_r("In here unfortunately");
            print_r($e->getMessage());
        }
    }
}
?>
<h2>Delete an appointment</h2>
<p>The assumption here is that the present day is 15-03-2020. Can only delete appointments made from this time.</p>
<span class="success"><?php echo "$succ_msg"; ?></span>
<span class="error"><?php echo "$err_msg"; ?></span>
<form method="post">
    <fieldset>
        <legend>Patient Info:</legend>
        <label for="hin">Health Insurance Number (HIN):
            <input type="text" name="hin" id="hin" placeholder="SKEE85021">
            <span class="error"><?php echo $err_arr["hin"] ?></span>
        </label>
        <label for="fName">First Name: <input type="text" name="fName" id="fName" placeholder="Bobby">
            <span class="error"><?php echo $err_arr["fName"] ?></span>
        </label>
        <label for="lName">Last Name: <input type="text" name="lName" id="lName" placeholder="Skeesick">
            <span class="error"><?php echo $err_arr["lName"] ?></span>
        </label>
    </fieldset>
    <fieldset>
        <legend>Appointment time:</legend>
        <label for="date">Date: </label>
        <input type="date" name="date"><span class="error"><?php echo $err_arr["date"] ?></span>
        <br>
        <label for="time">Time: </label>
        <input type="time" name="time"><span class="error"><?php echo $err_arr["time"] ?></span>
    </fieldset>
    <input type="submit" name="submit" value="DELETE">
</form>
<a href="homeMain.php">Go home.</a>
<?php include "footerMain.php"; ?>
