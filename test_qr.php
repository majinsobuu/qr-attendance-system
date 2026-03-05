<?php
require_once "phpqrcode/qrlib.php";

$data = "Hello QR Test";
$file = "qrcodes/test.png";

QRcode::png($data, $file);

echo "<h3>QR Generated:</h3>";
echo "<img src='$file'>";
?>