<?php
// Auto-generate a minimal API reference by scanning the api/ directory recursively.
// The generator looks for PHP files in api/ and extracts basic metadata
// (path, detected $_POST/$_GET params, and first comment block as description).

function build_api_reference() {
    $api_dir = realpath(__DIR__ . '/../api');
    $out = [];
    if(!$api_dir || !is_dir($api_dir)) return $out;

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($api_dir, FilesystemIterator::SKIP_DOTS)
    );

    foreach($iterator as $fileInfo) {
        if(!$fileInfo->isFile()) continue;
        $f = $fileInfo->getFilename();
        if(!preg_match('/\.php$/i', $f)) continue;

        $path = $fileInfo->getPathname();
        $relativePath = str_replace($api_dir . DIRECTORY_SEPARATOR, '', $path);
        $relativePath = str_replace('\\', '/', $relativePath);

        $content = file_get_contents($path);

        // Extract a phpdoc block /** ... */ or first single-line comment
        $description = '';
        if(preg_match('/\/\*\*(.*?)\*\//s', $content, $m)) {
            $desc = $m[1];
            $desc = preg_replace('/\s*\*\s*/', ' ', $desc);
            $description = trim(preg_replace('/\s+/', ' ', strip_tags($desc)));
        } else {
            // fallback: first // comment
            if(preg_match('/\/\/(.*)/', $content, $m2)) {
                $description = trim($m2[1]);
            }
        }

        // Detect parameters by looking for $_POST['name'] or $_GET['name']
        preg_match_all('/\$_(POST|GET)\s*\[\s*["\']([^"\']+)["\']\s*\]/', $content, $pm);
        $params = [];
        if(!empty($pm[2])) {
            foreach(array_unique($pm[2]) as $p) {
                $params[$p] = 'string';
            }
        }

        // Build example call
        $example = '';
        if (!empty($params)) {
            $example = "Example params: " . implode(', ', array_keys($params));
        }

        $id = str_replace('/', '_', preg_replace('/\.php$/i', '', $relativePath));

        $out[] = [
            'id' => $id,
            'path' => 'api/' . $relativePath,
            'method' => empty($params) ? 'GET' : 'POST',
            'params' => $params,
            'param_keys' => array_keys($params), // Add this for easy access
            'description' => $description . ($example ? " [$example]" : ""),
        ];
    }

    return $out;
}

$API_REFERENCE = build_api_reference();

function generate_api_context($refs) {
    $out = "Available APIs:\n";
    foreach($refs as $r) {
        $out .= "- " . ($r['id'] ?? $r['path']) . " (" . ($r['method'] ?? 'GET') . ")\n";
        $out .= "  Path: " . ($r['path'] ?? 'n/a') . "\n";
        $out .= "  Params: ";
        if(!empty($r['params']) && is_array($r['params'])) {
            $pairs = [];
            foreach($r['params'] as $k => $v) $pairs[] = "$k:$v";
            $out .= implode(', ', $pairs) . "\n";
        } else {
            $out .= "none\n";
        }
        $out .= "  Description: " . ($r['description'] ?? '') . "\n\n";
    }
    return $out;
}
