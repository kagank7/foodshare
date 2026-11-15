<?php
// Génère l'image du QR code d'une réservation
// utilisation : <img src="qrcode.php?code=FS-48K2">
// librairie utilisée : phpqrcode (http://phpqrcode.sourceforge.net/)
include 'lib/phpqrcode/qrlib.php';

$code = $_GET['code'] ?? '';
// on n'accepte que des codes du type FS-XXXX
if (!preg_match('/^FS-[A-Z0-9]{4}$/', $code)) {
    http_response_code(400);
    exit;
}
QRcode::png('FOODSHARE:' . $code, false, QR_ECLEVEL_L, 6, 0);
