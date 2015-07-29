<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf- 8">
</head>

<body onload="document.donProxy.submit();">

<?php
$baseUrl = 'https://spenden.wikimedia.de';

if(isset($_GET['browsertest']) && $_GET['browsertest'] === 'true') {
	$baseUrl = 'https://test.wikimedia.de';
	unset($_GET['browsertest']);
}

$getParams = "";
foreach( $_GET as $key => $value ) {
	$getParams .= ( $getParams === "" ) ? "?" : "&" . strip_tags($key) . "=" . strip_tags($value);
}
?>
<form name="donProxy" method="POST" action="<?php echo $baseUrl; ?>/spenden/spende.php<?php echo $getParams; ?>">
<?php foreach( $_POST as $key => $value ): ?>
	<input type="hidden" name="<?php echo strip_tags($key); ?>" value="<?php echo strip_tags($value); ?>" />
<?php endforeach; ?>
</form>

</body>

</html>

