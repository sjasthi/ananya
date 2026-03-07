<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Read the api.php docs file
$content = file_get_contents('docs/api.php');

if ($content === false) {
    die("Error reading file");
}

echo "Original file size: " . strlen($content) . " bytes\n";

// Regex pattern to match response blocks with 4 fields (without success/result/error)
// and replace with blocks that have all 6 fields
$pattern = '/("response_code": 200,\s*"message": "[^"]+",\s*"string": "[^"]*",\s*"language": "[^"]+",\s*"data": )(true|false)(\s*\})/m';

$replacement = '$1$2,
  "success": true,
  "result": $2,
  "error": null$3';

$newContent = preg_replace($pattern, $replacement, $content);

if ($newContent === null) {
    die("Regex error: " . preg_last_error());
}

$matches = preg_match_all($pattern, $content, $m);
echo "Found $matches response blocks to update\n";

// Specifically update the contains-all-logical-chars response string
$newContent = str_replace(
    '"message": "All logical characters check completed",
  "string": "hello",',
    '"message": "All logical characters check completed",
  "string": "అనన్య",',
    $newContent
);

echo "Updated file size: " . strlen($newContent) . " bytes\n";

if (file_put_contents('docs/api.php', $newContent) === false) {
    die("Error writing file");
}

echo "File updated successfully!\n";
echo "Total replacements made in response blocks\n";
?>

