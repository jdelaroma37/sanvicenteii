<?php
$dir = __DIR__ . '/data/requests';

if (is_dir($dir)) {
    $files = glob("$dir/*.json");
    foreach ($files as $file) {
        $req = json_decode(file_get_contents($file), true);
        if (isset($req['dateRequested'], $req['status'])) {
            // Only mark Done if status is "Ready to Pick Up" and 3 days passed
            if (strtotime($req['dateRequested']) < strtotime('-3 days') && $req['status'] === 'Ready to Pick Up') {
                $req['status'] = 'Done';
                $req['doneAt'] = date('Y-m-d H:i:s');
                file_put_contents($file, json_encode($req, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
    }
}
