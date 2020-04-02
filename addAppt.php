<?php
include "headerMain.php";
include "commonMain.php";
$clinics = array();
# To generate the clinics in the form
try {
    $connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password, $options);
    $sql = "select cname, address from clinics;";
    $select = $connection->prepare($sql);
    $select->execute();
    while ($row = $select->fetch(PDO::FETCH_NUM)) {
        array_push($clinics, array('cname' => $row[0], 'address' => $row[1]));
    }
} catch (PDOException $e) {
    print_r("In here unfortunately");
    print_r($e->getMessage());
}
# process the input of clinic, this allows to return both the cname and address at the same time
$result = $_POST['clinic'];
$result_explode = explode('---', $result);
# general variables
$current_date = new DateTime('2020-03-15 09:00:00');
$no_empty_fields = true;
$no_format_err = true;
$submit_arr = array("hin" => "", "fName" => "", "lName" => "", "cname" => trim($result_explode[0]),
    "address" => trim($result_explode[1]), "date" => $_POST['date'], "time" => $_POST['time']);
$err_arr = array("hin" => "", "fName" => "", "lName" => "", "date" => "", "time" => "");
$isempty_arr = array("sin" => false, "fname" => false, "lName" => false, "date" => false, "time" => false);
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
                    $err_arr[$key] = "Invalid date. Pick a date starting from March 15th 2020.";
                }
            }
        }
        if($key == "time"){
            if(!$isempty_arr[$key]){
                if(!preg_match('/\d{4}-\d{2}-\d{2} (09|10|11|12|13|14|15|16):00:00/', $dateTime)){
                    $err_arr[$key] = "Invalid time. Appointments can only be made from 9AM to 4PM. Every hour.";
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

                # Check if this time slot is available
                $sql = "select *
                        from appointments 
                        where dateAndTime = '{$dateTime}' and
                              cname = '{$submit_arr['cname']}' and
                              address = '{$submit_arr['address']}';";
                $select = $connection->prepare($sql);
                $select->execute();
                $row = $select->fetch(PDO::FETCH_NUM);
                if($select->rowCount() > 0){
                    $err_msg = "Time slot already booked! Please try another time.";
                }
                else{
                    srand(123); # random seed
                    $recepFName = "";
                    $recepLName = "";
                    $recepEID = 0;
                    $dentistFName = "TBA";
                    $dentistLName = "TBA";
                    $dentistEID = "NULL";
                    # Assign this receptionist and a dentist, to do this need the list of receptionists and dentists that work there
                    $sqlReceptionist = "select staffs.eid as eid, staffs.fName as fName, staffs.lName as lName 
                    from staffs, receptionists, worksIn
                    where staffs.eid = receptionists.eid and 
                    staffs.eid = worksIn.eid and
                    worksIn.cname = '{$submit_arr['cname']}' and
                    worksIn.address = '{$submit_arr['address']}';";

                    $select = $connection->prepare($sqlReceptionist);
                    $select->execute();
                    $recep = array();
                    while($row = $select->fetch(PDO::FETCH_ASSOC)){
                        $recep[] = array('eid' => $row['eid'], 'fName' => $row['fName'], 'lName' => $row['lName']);
                    }
                    $recepRand = $recep[array_rand($recep)];
                    $recepFName = $recepRand['fName'];
                    $recepLName = $recepRand['lName'];
                    $recepEID = $recepRand['eid'];
//                    print_r($recepRand);

                    $sqlDentist = "select staffs.eid as eid, staffs.fName as fName, staffs.lName as lName
                    from staffs, dentists, worksIn
                    where staffs.eid = dentists.eid and 
                    staffs.eid = worksIn.eid and
                    worksIn.cname = '{$submit_arr['cname']}' and
                    worksIn.address = '{$submit_arr['address']}';";
                    $select = $connection->prepare($sqlDentist);
                    $select->execute();
                    $dentists = array();
                    while($row = $select->fetch(PDO::FETCH_ASSOC)){
                        $dentists[] = array('eid' => $row['eid'], 'fName' => $row['fName'], 'lName' => $row['lName']);
                    }
                    if(rand(0, 4) != 0) {
                        $dentRand = $dentists[array_rand($dentists)];
                        $dentistFName = $dentRand['fName'];
                        $dentistLName = $dentRand['lName'];
                        $dentistEID = $dentRand['eid'];
                    }
//                    print_r($dentRand);
                    if($dentistEID == "NULL") {
                        $sql = "INSERT INTO appointments VALUES({$pid}, '{$dateTime}', 'scheduled', NULL, {$recepEID},
                                '{$submit_arr['address']}', '{$submit_arr['cname']}');";
//                        echo $sql;
                    }
                    else{
                        $sql = "INSERT INTO appointments VALUES({$pid}, '{$dateTime}', 'scheduled', {$dentistEID}, {$recepEID},
                                '{$submit_arr['address']}', '{$submit_arr['cname']}');";
//                        echo $sql;
                    }
                    echo
                    $connection->exec($sql);
                    $succ_msg = "You have booked your appointment at {$submit_arr['cname']} at {$dateTime}. Our receptionist {$recepFName}
                     {$recepLName} scheduled your appointment. Your dentist will be {$dentistFName} {$dentistLName}. See you soon!";
                }
            }

        } catch (PDOException $e) {
            print_r("In here unfortunately");
            print_r($e->getMessage());
        }
    }
}
?>
<h2>Create an appointment</h2>
<p>The assumption here is that the present day is 15-03-2020. Can only make appointments starting from then.</p>
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
        <legend>Clinic Choice:</legend>
        <label for="clinic">Which of the following would you like to book an appointment?</label>
        <select name="clinic" id="clinic">
            <?php
            foreach ($clinics as $clinic) {
                ?>
                <option value="<?php echo $clinic['cname'] ?> --- <?php echo $clinic['address'] ?>">
                    <?php echo "{$clinic['cname']}, {$clinic['address']}" ?></option>
            <?php } ?>
        </select>
    </fieldset>
    <fieldset>
        <legend>Appointment time:</legend>
        <label for="date">Date: </label>
        <input type="date" name="date"><span class="error"><?php echo $err_arr["date"] ?></span>
        <br>
        <label for="time">Time: </label>
        <input type="time" name="time"><span class="error"><?php echo $err_arr["time"] ?></span>
    </fieldset>
    <input type="submit" name="submit" value="SUBMIT">
</form>
<a href="homeMain.php">Go home.</a>
<?php include "footerMain.php"; ?>
