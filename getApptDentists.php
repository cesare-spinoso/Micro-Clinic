<?php
include "headerMain.php"; # This contains all the header info so don't need to repeat the same code
include "commonMain.php"; # This contains the db info and the helper function  produceHtmlTable (see commonMain.php for documentation)
?>
<!--Here provide the SIN for the dentist (alternate key)/ could also do EID. I imagine it might be easier
for a user to remember their SIN than their EID. Also provide a way of picking the week and somehow turn this into a sunday
(of that week).
Make sure the year input is formatted as yyyy-mm-dd HH:MM:SS when you pass it into sql.-->

<h2>Your title</h2>
<form method="post">
    <fieldset>
        <legend>Patient info:</legend>
        <label for="hin">Health Insurance Number (HIN):
            <input type="text" name="hin" id="hin" placeholder="JOHN12345">
        </label>
        <label for="fName">First Name: <input type="text" name="fName" id="fName" placeholder="John">
        </label>
        <label for="lName">Last Name: <input type="text" name="lName" id="lName" placeholder="Doe">
        </label>
        <label for="address">Address: <input type="text" name="address" id="address" placeholder="123 Dream street">
        </label>
        <label for="phoneNumber">Phone Number: <input type="text" name="phoneNumber" id="phoneNumber"
                                                      placeholder="123-456-7890">
        </label>
    </fieldset>
    <input type="submit" name="submit" value="SUBMIT">
</form>
<a href="homeMain.php">Go home.</a>
<?php include "footerMain.php"; ?>
