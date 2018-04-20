<?php
include_once 'functions.php';
cookieCheck();
?>
<script type="text/javascript">
 cookieEnabled();
</script>

<?php
session_start();
ob_start();
forceHttps();
checkSession();
InsertSystemBid();



?>
<html>
<link href="layout.css" rel="stylesheet" type="text/css">
<title> Bideveryday.com</title>
<body>
    <div id= "title">
       <noscript>
Sorry: Your browser does not support or has disabled javascript.
Please enable javascript to allow the correct execution of the website.
</noscript> <h1>Bideveryday.com</h1>
    </div>
    
    
