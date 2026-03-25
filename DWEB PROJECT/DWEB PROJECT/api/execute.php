<?php
/**
 * Fox Lab – Code Execution Proxy
 * Routes code to a free online execution API (server-side).
 * This avoids CORS issues and allows easy endpoint swapping.
 */

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Read JSON body
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['language']) || empty($input['code'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing language or code']);
    exit;
}

$language = $input['language'];
$version  = $input['version'] ?? '';
$code     = $input['code'];
$filename = $input['filename'] ?? 'main.py';
$stdin    = $input['stdin'] ?? '';

// Allowed languages
$allowed = ['python', 'java'];
if (!in_array($language, $allowed)) {
    http_response_code(400);
    echo json_encode(['error' => 'Language not supported. Use python or java.']);
    exit;
}

// ── Piston API endpoints (try in order) ──
$pistonEndpoints = [
    'https://emkc.org/api/v2/piston/execute',
];

$payload = json_encode([
    'language' => $language,
    'version'  => $version,
    'files'    => [['name' => $filename, 'content' => $code]],
    'stdin'    => $stdin,
    'compile_timeout'      => 10000,
    'run_timeout'          => 5000,
    'compile_memory_limit' => -1,
    'run_memory_limit'     => -1,
]);

$result = null;
$lastError = '';

foreach ($pistonEndpoints as $endpoint) {
    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false,   // dev environment
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        $lastError = "Connection error: $curlErr";
        continue;
    }

    if ($httpCode >= 200 && $httpCode < 300) {
        $result = json_decode($response, true);
        if ($result) break;
        $lastError = "Invalid JSON from API";
        continue;
    }

    $lastError = "API returned status $httpCode";
}

// ── If Piston failed, use a built-in PHP fallback for Python ──
if (!$result && $language === 'python') {
    // Try local Python if available
    $pythonBin = findExecutable('python') ?: findExecutable('python3');
    if ($pythonBin) {
        $result = executeLocally($pythonBin, $code, $stdin);
    }
}

// ── If Piston failed, use a built-in PHP fallback for Java ──
if (!$result && $language === 'java') {
    $javacBin = findExecutable('javac');
    $javaBin  = findExecutable('java');
    if ($javacBin && $javaBin) {
        $result = executeJavaLocally($javacBin, $javaBin, $code, $stdin);
    }
}

if ($result) {
    echo json_encode($result);
} else {
    http_response_code(502);
    echo json_encode([
        'error' => 'All execution backends failed.',
        'detail' => $lastError,
    ]);
}

// ══════════════════════════════════════════════════════════
// Helper functions
// ══════════════════════════════════════════════════════════

/**
 * Find an executable on the system PATH.
 */
function findExecutable(string $name): ?string {
    $isWin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    $cmd = $isWin ? "where $name 2>nul" : "which $name 2>/dev/null";
    $out = trim((string) shell_exec($cmd));
    if ($out && !str_contains($out, 'not found') && !str_contains($out, 'Could not find')) {
        // Take first line (where can return multiple)
        return explode("\n", $out)[0];
    }
    return null;
}

/**
 * Execute Python code locally in a temp file.
 */
function executeLocally(string $pythonBin, string $code, string $stdin): array {
    $tmpDir  = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'foxlab_' . uniqid();
    mkdir($tmpDir, 0700, true);
    $tmpFile = $tmpDir . DIRECTORY_SEPARATOR . 'main.py';
    file_put_contents($tmpFile, $code);

    $desc = [
        0 => ['pipe', 'r'],  // stdin
        1 => ['pipe', 'w'],  // stdout
        2 => ['pipe', 'w'],  // stderr
    ];

    $proc = proc_open("\"$pythonBin\" \"$tmpFile\"", $desc, $pipes, $tmpDir);
    if (!is_resource($proc)) {
        cleanup($tmpDir);
        return makeResult('', 'Failed to start process', 1);
    }

    fwrite($pipes[0], $stdin);
    fclose($pipes[0]);

    $stdout = stream_get_contents($pipes[1]); fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]); fclose($pipes[2]);
    $exit   = proc_close($proc);

    cleanup($tmpDir);
    return makeResult($stdout, $stderr, $exit);
}

/**
 * Execute Java code locally: javac then java.
 */
function executeJavaLocally(string $javacBin, string $javaBin, string $code, string $stdin): array {
    $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'foxlab_' . uniqid();
    mkdir($tmpDir, 0700, true);

    // Detect class name
    preg_match('/public\s+class\s+(\w+)/', $code, $m);
    $className = $m[1] ?? 'Main';
    $srcFile = $tmpDir . DIRECTORY_SEPARATOR . $className . '.java';
    file_put_contents($srcFile, $code);

    $desc = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];

    // Compile
    $proc = proc_open("\"$javacBin\" \"$srcFile\"", $desc, $pipes, $tmpDir);
    if (!is_resource($proc)) { cleanup($tmpDir); return makeResult('', 'Failed to start javac', 1); }
    fclose($pipes[0]);
    $compStdout = stream_get_contents($pipes[1]); fclose($pipes[1]);
    $compStderr = stream_get_contents($pipes[2]); fclose($pipes[2]);
    $compExit   = proc_close($proc);

    if ($compExit !== 0) {
        cleanup($tmpDir);
        return [
            'compile' => ['stdout' => $compStdout, 'stderr' => $compStderr, 'code' => $compExit, 'output' => $compStderr],
            'run'     => ['stdout' => '', 'stderr' => '', 'code' => 1, 'output' => ''],
        ];
    }

    // Run
    $proc = proc_open("\"$javaBin\" -cp \"$tmpDir\" $className", $desc, $pipes, $tmpDir);
    if (!is_resource($proc)) { cleanup($tmpDir); return makeResult('', 'Failed to start java', 1); }
    fwrite($pipes[0], $stdin); fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]); fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]); fclose($pipes[2]);
    $exit   = proc_close($proc);

    cleanup($tmpDir);
    return [
        'compile' => ['stdout' => $compStdout, 'stderr' => '', 'code' => 0, 'output' => ''],
        'run'     => ['stdout' => $stdout, 'stderr' => $stderr, 'code' => $exit, 'output' => $stdout . $stderr],
    ];
}

/**
 * Build a Piston-compatible result shape.
 */
function makeResult(string $stdout, string $stderr, int $exitCode): array {
    return [
        'run' => [
            'stdout' => $stdout,
            'stderr' => $stderr,
            'code'   => $exitCode,
            'output' => $stdout . $stderr,
        ],
    ];
}

/**
 * Remove temp directory recursively.
 */
function cleanup(string $dir): void {
    if (!is_dir($dir)) return;
    foreach (scandir($dir) as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $f;
        is_dir($path) ? cleanup($path) : unlink($path);
    }
    rmdir($dir);
}
