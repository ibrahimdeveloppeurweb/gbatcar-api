<?php

$content = file_get_contents('/Users/mac/Documents/GBATCAR/gbatcar-api/src/Manager/Client/PaymentManager.php');
if (preg_match("/'VALID[^']*'/u", $content, $matches)) {
    echo "Found: " . $matches[0] . "\n";
    echo "Hex: " . bin2hex($matches[0]) . "\n";
}
else {
    echo "Pattern not found.\n";
}