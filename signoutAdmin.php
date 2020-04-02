<?php
session_start();
session_unset();
session_destroy();
header("location: https://mvc353.encs.concordia.ca/mainProject/adminHome.php");