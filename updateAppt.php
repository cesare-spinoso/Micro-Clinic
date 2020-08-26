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
$result = $_POST['clinicNew'];
$result_explode = explode('---', $result);
# variables
$result_explode = explode('---', $result);
$current_date = new DateTime('2020-03-15 09:00:00');
$no_empty_fields = true;
$no_format_err = true;
$submit_arr = array("hin" => "", "fName" => "", "lName" => "", "date" => $_POST['date'], "time" => $_POST['time'],
    "dateNew" => $_POST['dateNew'], "timeNew" => $_POST['timeNew'],
    "clinicNew" => $_POST['clinicNew'],
    "cnameNew" => trim($result_explode[0]), "addressNew" => trim($result_explode[1]));
$err_arr = array("hin" => "", "fName" => "", "lName" => "", "date" => "", "time" => "", "dateNew" => "", "timeNew" => "",
    "clinicNew" => "");
$isempty_arr = array("hin" => false, "fname" => false, "lName" => false, "date" => false, "time" => false,
    "dateNew" => false,  "timeNew" => false, "cnameNew" => false, "addressNew" => false);
$succ_msg = "";
$err_msg = "";

if (isset($_POST["submit"])) {
    foreach ($submit_arr as $key => $value) {
        if (empty($_POST[$key]) and $key != "cnameNew" and $key != "addressNew") {
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
        $dateTimeNew = $submit_arr['dateNew'] . ' ' . $submit_arr['timeNew'];
        $asDate = new DateTime($dateTime);
        $asDateNew = new DateTime($dateTimeNew);
        $dateTime .= ':00';
        $dateTimeNew .= ':00';
        if ($key == "date" || $key == "dateNew") {
            if (!$isempty_arr[$key]) {
                if ($key == "date" && $asDate < $current_date) {
                    $err_arr[$key] = "Invalid date. Can only have dates starting from March 15th 2020.";
                    $no_format_err = false;
                }
                else if($key == "dateNew" && $asDateNew < $current_date) {
                    $err_arr[$key] = "Invalid date. Can only have dates starting from March 15th 2020.";
                    $no_format_err = false;
                }
            }
        }
        if ($key == "time" || $key == "timeNew") {
            if (!$isempty_arr[$key]) {
                if ($key == "time" && !preg_match('/\d{4}-\d{2}-\d{2} (09|10|11|12|13|14|15|16):00:00/', $dateTime)) {
                    $err_arr[$key] = "Invalid time. Appointments are made from 9AM to 4PM. Every hour.";
                    $no_format_err = false;
                }
                else if ($key == "timeNew" && !preg_match('/\d{4}-\d{2}-\d{2} (09|10|11|12|13|14|15|16):00:00/', $dateTimeNew)) {
                    $err_arr[$key] = "Invalid time. Appointments are made from 9AM to 4PM. Every hour.";
                    $no_format_err = false;
                }
            }
        }
    }
//    debug
//    echo "This is format " . $no_format_err . "<br>";
//    echo "This is empty" . $no_empty_fields . "<br>";
//    print_r($isempty_arr);
    if ($no_empty_fields && $no_format_err) {
        try {
            $connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password, $options);
            // First check if the entry already exists use the hin, since this is an alternate key
            $sql = "SELECT pid FROM patients WHERE hin = '{$submit_arr['hin']}';";
            $select = $connection->prepare($sql);
            $select->execute();
            $num_rows = $select->rowCount();
            if ($num_rows == 0) {
                $err_msg = "Could not find your Health Insurance Number!";
            } else {
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
                if ($select->rowCount() == 0) {
                    $err_msg = "No appointment matches this info. Nothing to update. Please visit <a href='addAppt.php'>this page</a>
                                to make a new appointment.";
                } else {
                    $sql = "select cname, address 
                            from appointments 
                            where dateAndTime = '{$dateTime}' 
                            and pid = {$pid};";
                    $select = $connection->prepare($sql);
                    $select->execute();
                    $row = $select->fetch(PDO::FETCH_NUM);

                    if(preg_match('/' . $dateTime . '/', $dateTimeNew)&&
                        preg_match('/' . $row[1] . '/', $submit_arr['addressNew']) &&
                        preg_match('/' . $row[0] . '/', $submit_arr['cnameNew'])){
                        $err_msg = "You're trying to update your appointment to itself! But why?? No updates made.";
                    }
                    else {
                        # Check that the slot is available
                        $sql = "select *
                        from appointments
                        where dateAndTime = '{$dateTimeNew}' and
                              cname = '{$submit_arr['cnameNew']}' and
                              address = '{$submit_arr['addressNew']}';";
                        $select = $connection->prepare($sql);
                        $select->execute();
                        $num_rows = $select->rowCount();
                        if($num_rows > 0){
                            $err_msg = "This appointment time/clinic combination is already taken. Please try a different time!";
                        }
                        else {
                            # find a new dentist and receptionist - same process as in the addAppt
//                            srand(123); # random seed
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
                    worksIn.cname = '{$submit_arr['cnameNew']}' and
                    worksIn.address = '{$submit_arr['addressNew']}';";

                            $select = $connection->prepare($sqlReceptionist);
                            $select->execute();
                            $recep = array();
                            while ($row = $select->fetch(PDO::FETCH_ASSOC)) {
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
                    worksIn.cname = '{$submit_arr['cnameNew']}' and
                    worksIn.address = '{$submit_arr['addressNew']}';";
                            $select = $connection->prepare($sqlDentist);
                            $select->execute();
                            $dentists = array();
                            while ($row = $select->fetch(PDO::FETCH_ASSOC)) {
                                $dentists[] = array('eid' => $row['eid'], 'fName' => $row['fName'], 'lName' => $row['lName']);
                            }
                            if (rand(0, 4) != 0) {
                                $dentRand = $dentists[array_rand($dentists)];
                                $dentistFName = $dentRand['fName'];
                                $dentistLName = $dentRand['lName'];
                                $dentistEID = $dentRand['eid'];
                            }
//                    print_r($dentRand);
                            if ($dentistEID == "NULL") {
                                $sql = "update appointments set dateAndTime = '$dateTimeNew', 
                                                           cname = '{$submit_arr['cnameNew']}',
                                                           address = '{$submit_arr['addressNew']}',
                                                            eidDentist = NULL,
                                                            eidReceptionist = $recepEID
                                                        where dateAndTime = '$dateTime' and
                                                              pid = {$pid};";
//                        echo $sql;
                            } else {
                                $sql = "update appointments set dateAndTime = '$dateTimeNew', 
                                                           cname = '{$submit_arr['cnameNew']}',
                                                           address = '{$submit_arr['addressNew']}',
                                                            eidDentist = {$dentistEID},
                                                            eidReceptionist = {$recepEID}
                                                        where dateAndTime = '$dateTime' and
                                                              pid = {$pid};";
                            }
                            $connection->exec($sql);
//                            echo $sql;
                            $succ_msg = "Successfully modified you appointment on {$dateTime} at {$submit_arr['cnameNew']}.
                             The receptionist that took care of reschdeuling was {$recepFName} {$recepLName}. Your dentist for this
                             appointment will be ${dentistFName} ${dentistLName}. See you!";
                        }
                    }

                }
            }

        } catch (PDOException $e) {
            $err_msg = "Error deleting record: " . $connection->errorCode();
            print_r("In here unfortunately");
            print_r($e->getMessage());
        }
    }
}
?>
<h2>Modify an appointment</h2>
<p>The assumption here is that the present day is 15-03-2020. Can only modify appointments made from this time.</p>
<span class="success"><?php echo "$succ_msg"; ?></span>
<span class="error"><?php echo "$err_msg"; ?></span>
<form method="post">
    Please enter your current appointment info:
    <fieldset>
        <legend>Patient info:</legend>
        <label for="hin">Health Insurance Number (HIN):
            <input type="text" name="hin" id="hin" placeholder="OTIS74613">
            <span class="error"><?php echo $err_arr["hin"] ?></span>
        </label>
        <label for="fName">First Name: <input type="text" name="fName" id="fName" placeholder="Emma">
            <span class="error"><?php echo $err_arr["fName"] ?></span>
        </label>
        <label for="lName">Last Name: <input type="text" name="lName" id="lName" placeholder="Otis">
            <span class="error"><?php echo $err_arr["lName"] ?></span>
        </label>
    </fieldset>
    <fieldset>
        <legend>Current appointment time:</legend>
        <label for="date">Date: </label>
        <input type="date" name="date"><span class="error"><?php echo $err_arr["date"] ?></span>
        <br>
        <label for="time">Time: </label>
        <input type="time" name="time"><span class="error"><?php echo $err_arr["time"] ?></span>
    </fieldset>
    What would you like to change about your appointment? Leave a field blank if you do not wish to change it.
    <fieldset>
        <legend>Change appointment time:</legend>
        <label for="dateNew">New Date: </label>
        <input type="date" name="dateNew"><span class="error"><?php echo $err_arr["dateNew"] ?></span>
        <br>
        <label for="timeNew">New Time: </label>
        <input type="time" name="timeNew"><span class="error"><?php echo $err_arr["timeNew"] ?></span>
    </fieldset>
    <fieldset>
        <legend>Change clinic:</legend>
        <label for="clinicNew">Which of the following would you like to book an appointment?</label>
        <select name="clinicNew" id="clinicNew">
            <option selected></option>
            <?php
            foreach ($clinics as $clinic) {
                ?>
                <option value="<?php echo $clinic['cname'] ?> --- <?php echo $clinic['address'] ?>">
                    <?php echo "{$clinic['cname']}, {$clinic['address']}" ?></option>
            <?php } ?>
        </select>
        <span class="error"><?php echo $err_arr["clinicNew"]?></span>
    </fieldset>
    <input type="submit" name="submit" value="UPDATE">
</form>
<a href="homeMain.php">Go home.</a>
<?php include "footerMain.php"; ?>
<!--print_r($row);-->
<!--echo $dateTimeNew   . "<br>" ;-->
<!--echo $dateTime  . "<br>";-->
<!--echo $submit_arr['cnameNew']  . "<br>";-->
<!--echo $submit_arr['addressNew']  . "<br>";-->
<!--echo $row[0]  . "<br>";-->
<!--echo $row[1]  . "<br>";-->
<!--echo  $dateTime == $dateTimeNew  . "<br>";-->
<!--echo $row[0] == $submit_arr['cnameNew'] . "<br>";-->
<!--echo preg_match('/' . $row[1] . '/', $submit_arr['addressNew']) . "<br>";-->
<!--echo $dateTime == $dateTimeNew && $row[0] == $submit_arr['cnameNew'] &&-->
<!--$row[1] == $submit_arr['addressNew']  . "<br>";-->