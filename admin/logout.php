<?php
session_start();
if(isset($_SESSION['lm_auth'])){
	unset($_SESSION['lm_auth']);
        unset($_SESSION['lm_conf']);
}
header("Location: index.php");
?>