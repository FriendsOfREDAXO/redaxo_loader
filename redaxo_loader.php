<?php
// Language handling
$available_langs = ['de' => 'Deutsch', 'en' => 'English'];
$lang = $_COOKIE['rex_loader_lang'] ?? 'de';
if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $available_langs)) {
    $lang = $_GET['lang'];
    setcookie('rex_loader_lang', $lang, time() + (86400 * 30), "/");
}

$texts = [
    'de' => [
        'title' => 'REDAXO Loader',
        'subtitle' => 'CMS herunterladen und installieren',
        'select_version' => 'REDAXO Version wählen',
        'latest_stable' => 'Aktuelle stabile Version',
        'latest_beta' => 'Aktuelle Beta Version',
        'show_all' => 'Andere Version wählen',
        'hide_all' => 'Versionsauswahl ausblenden',
        'system_check' => 'System-Check',
        'install_start' => 'Installation starten',
        'downloading' => 'Lade herunter...',
        'extracting' => 'Entpacke...',
        'completed' => 'Installation abgeschlossen!',
        'redirecting' => 'Weiterleitung zum Setup in',
        'requirements' => [
            'php_version' => 'PHP Version (min. 8.1)',
            'extensions' => 'Benötigte Erweiterungen',
            'permissions' => 'Verzeichnis-Rechte',
        ],
        'status' => [
            'ok' => 'OK',
            'error' => 'Fehler',
            'checking' => 'Prüfe...'
        ]
    ],
    'en' => [
        'title' => 'REDAXO Loader',
        'subtitle' => 'Download and install CMS',
        'select_version' => 'Select REDAXO version',
        'latest_stable' => 'Latest stable version',
        'latest_beta' => 'Latest beta version',
        'show_all' => 'Choose different version',
        'hide_all' => 'Hide version selection',
        'system_check' => 'System Check',
        'install_start' => 'Start Installation',
        'downloading' => 'Downloading...',
        'extracting' => 'Extracting...',
        'completed' => 'Installation completed!',
        'redirecting' => 'Redirecting to setup in',
        'requirements' => [
            'php_version' => 'PHP Version (min. 8.1)',
            'extensions' => 'Required Extensions',
            'permissions' => 'Directory Permissions',
        ],
        'status' => [
            'ok' => 'OK',
            'error' => 'Error',
            'checking' => 'Checking...'
        ]
    ]
];

// Core functionality
$githubtoken = 'QW3BFZrBzNw9EV4pHbolnN3N1ShdXeDBlaYF0bLxkNs90czg3Xwh2Z';
$loader_name = basename(__FILE__);
$install_path = './';
$install_file = $install_path . 'redaxo.zip';

function checkSystem($selectedVersion = '') {
    $required_extensions = [
        'ctype', 'fileinfo', 'gd', 'iconv', 'intl', 
        'mbstring', 'pdo', 'pdo_mysql', 'pcre', 
        'session', 'tokenizer'
    ];
    
    // Check required extensions
    $missing_extensions = array_filter($required_extensions, function($ext) {
        return !extension_loaded($ext);
    });
    
    return [
        'php_version' => [
            'status' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'value' => PHP_VERSION,
            'required' => '8.1.0'
        ],
        'extensions' => [
            'status' => empty($missing_extensions),
            'value' => $missing_extensions,
            'required' => true
        ],
        'permissions' => [
            'status' => is_writable('./') && (!file_exists('./redaxo') || is_writable('./redaxo')),
            'required' => true
        ]
    ];
}

// AJAX handlers
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $response = ['success' => false];

    switch ($_GET['action']) {
        case 'check':
            $version = isset($_GET['version']) ? $_GET['version'] : '';
            $response = ['success' => true, 'checks' => checkSystem($version)];
            break;

        case 'download':
            if (isset($_GET['url'])) {
                $ch = curl_init($_GET['url']);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTPHEADER => [
                        'Accept: application/vnd.github+json',
                        'Authorization: token ' . base64_decode(strrev($githubtoken)),
                        'User-Agent: REDAXO Loader'
                    ]
                ]);
                $data = curl_exec($ch);
                curl_close($ch);
                
                if (file_put_contents($install_file, $data)) {
                    $response = ['success' => true];
                }
            }
            break;

        case 'extract':
            $zip = new ZipArchive;
            if ($zip->open($install_file) === true) {
                $success = $zip->extractTo($install_path);
                $zip->close();
                if ($success) {
                    // Clean up files
                    @unlink($install_file);
                    @unlink(__FILE__); // Delete loader itself
                    
                    // Get relative path to redaxo folder
                    $relativePath = 'redaxo';
                    if (dirname($_SERVER['SCRIPT_NAME']) != '/') {
                        $relativePath = ltrim(dirname($_SERVER['SCRIPT_NAME']) . '/redaxo', '/');
                    }
                    
                    $response = [
                        'success' => true, 
                        'message' => 'Installation completed and cleanup done',
                        'redirect' => $relativePath
                    ];
                }
            }
            break;
    }
    
    echo json_encode($response);
    exit;
}

// Get releases
$releases = [];
$ch = curl_init('https://api.github.com/repos/redaxo/redaxo/releases');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Accept: application/vnd.github+json',
        'Authorization: token ' . base64_decode(strrev($githubtoken)),
        'User-Agent: REDAXO Loader'
    ]
]);
$releases = json_decode(curl_exec($ch));
curl_close($ch);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $texts[$lang]['title'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
    <link href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAABIFBMVEX///9OltFNmdJMl9BAgL9VjsZNmdNNmdNNmdNOmtUA//9LltJMmdNOl9RNmdNMmNNOmdJOmtNOmtNNmdNNmtRNmdNNmdNNmdJNmdNNmdJNmdNNmdNMmNROmdJNmtJMmdNMmNNNmdNNmNNNmdNNmdJNmdJNmdJNmdNNmdNOm9NNmdNOmdRMmdJNmdNNmdNOmtNKldVNmdNMmdNNmtNOmdNNmdNNmtRNmdNNmtNNmdNmmcxNmdRNmdJJkttNmdNOndhPldOAgP9NmdNNmdNMmNJOmNJOmtJNmNRPmtNPmdJMmNNNmtNOmtRRl9FNmtRNmdNOmdNJm9FMmdNQn89NmdNNmNNMmdRNmdNNmdNOnNVOmNNNmdVQmdNJns5NmdP////jCU31AAAAXnRSTlMAJygbBAn+5J5OASK7O+5XVXlv+YjBolr8eHp7fH1+f5DNldXljrfvyxf62bHS7ZcY+8VMzNGw9lbpBYLIB+YaHQL9+Eqaq4E6LWjJWBY14oAcdRD0Y5PT0CRFHiMVY/kodwAAAAFiS0dEAIgFHUgAAAAJcEhZcwAABG4AAARuAdCjsmgAAAAHdElNRQfjAwwTHCCSC2ZYAAABLklEQVQ4y4WU51bCQBCFV0AEjAU7YBRsYMOGKDYExF6xF+b9H0NPZiaZXRK8/2b225zdO3ej1J96QrrCEaWpF0xF+2LxhAf0g5+ssAsM+AIwOMTAMAQoScBIEDAaQmAsCIBxPOlEIACTDmBhMZVKpjOZaXtmNptjYM4B5rFY8C6eWCRgySnzWBSEd8sr2FuVRq5Jd9exV5RGboj1zS3sbUsjbW84OyW5iY3cLZOsPfea+12NBKgcdDcSDv8x8uhYGtmpk6qSRnZO6pQulfdft2tGIusNFANnZiKbVJ9TbZmJvKD6kj9xZSTymnfcUCNmJDLOwC01Gnd6Iu8ZiDxQ51FpRj65w2zxJDQjo957fH4h4lUaWRFxeSPgXRr5IYDPOhFf4u1/y0T+0H+grdQv/i3d/pZj7q4AAAAldEVYdGRhdGU6Y3JlYXRlADIwMTktMDMtMTJUMTk6Mjg6MzIrMDE6MDCHzxaCAAAAJXRFWHRkYXRlOm1vZGlmeQAyMDE5LTAzLTEyVDE5OjI4OjMyKzAxOjAw9pKuPgAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAABXelRYdFJhdyBwcm9maWxlIHR5cGUgaXB0YwAAeJzj8gwIcVYoKMpPy8xJ5VIAAyMLLmMLEyMTS5MUAxMgRIA0w2QDI7NUIMvY1MjEzMQcxAfLgEigSi4A6hcRdPJCNZUAAAAASUVORK5CYII=" rel="icon" type="image/x-icon">
    <style>
        body {
            font-family: 'Source Sans Pro', sans-serif;
            line-height: 1.6;
            color: #343f50;
            background-color: #fff;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #5b98d7;
            padding: 10px 0;
            margin-bottom: 30px;
        }

        .container {
            width: 100%;
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .navbar-brand {
            display: inline-block;
            padding: 10px 0;
        }

        #redaxo-logo {
            width: 190px;
        }

        .language-switcher {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            gap: 10px;
        }

        .language-btn {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            color: #fff;
            background-color: rgba(255, 255, 255, 0.2);
            font-weight: 600;
            transition: background-color 0.2s;
        }

        .language-btn.active {
            background-color: rgba(255, 255, 255, 0.4);
        }

        .language-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }

        h1 {
            color: #5b98d7;
            font-size: 3rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }

        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .card-header {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .card-title {
            margin: 0;
            font-size: 1.25rem;
            color: #343f50;
            font-weight: 600;
        }

        .card-body {
            padding: 20px;
        }

        .check-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .check-status {
            font-weight: 600;
        }

        .status-ok {
            color: #28a745;
        }

        .status-error {
            color: #dc3545;
        }

        .version-select {
            margin-bottom: 15px;
        }

        .version-option {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .version-option input[type="radio"] {
            margin-right: 10px;
        }

        .version-tag {
            color: #666;
            font-size: 0.9rem;
            margin-left: 5px;
        }

        .version-toggle {
            color: #5b98d7;
            background: none;
            border: none;
            padding: 0;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: underline;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .custom-select {
            display: block;
            width: 100%;
            padding: 10px 15px;
            font-size: 1rem;
            line-height: 1.5;
            color: #343f50;
            background-color: #fff;
            background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill="%235b98d7" d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            appearance: none;
            margin-bottom: 15px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .custom-select:focus {
            border-color: #5b98d7;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(91, 152, 215, 0.25);
        }

        .btn {
            display: inline-block;
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
            user-select: none;
            padding: 12px 20px;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 4px;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
            cursor: pointer;
            width: 100%;
            margin-bottom: 20px;
        }

        .btn-primary {
            color: #fff;
            background-color: #5b98d7;
            border: none;
        }

        .btn-primary:hover {
            background-color: #4b88c7;
        }

        .btn-primary:disabled {
            background-color: #b8d3ef;
            cursor: not-allowed;
        }

        .progress-container {
            margin-top: 20px;
        }

        .progress {
            height: 20px;
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .progress-bar {
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            background-color: #5b98d7;
            height: 100%;
            transition: width 0.3s ease;
        }

        .progress-bar-animated {
            animation: progress-bar-stripes 1s linear infinite;
        }

        .progress-bar-striped {
            background-image: linear-gradient(45deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent);
            background-size: 1rem 1rem;
        }

        @keyframes progress-bar-stripes {
            from { background-position: 1rem 0; }
            to { background-position: 0 0; }
        }

        .progress-status {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .spinner {
            animation: spin 1s linear infinite;
            border: 3px solid rgba(91, 152, 215, 0.3);
            border-radius: 50%;
            border-top-color: #5b98d7;
            display: inline-block;
            height: 20px;
            width: 20px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .text-center {
            text-align: center;
        }

        .text-success {
            color: #28a745;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }

        .col {
            flex-basis: 0;
            flex-grow: 1;
            max-width: 100%;
            padding-right: 15px;
            padding-left: 15px;
        }

        .col-12 {
            flex: 0 0 100%;
            max-width: 100%;
            padding-right: 15px;
            padding-left: 15px;
        }

        @media (min-width: 768px) {
            .col-md-6 {
                flex: 0 0 50%;
                max-width: 50%;
            }
            .offset-md-3 {
                margin-left: 25%;
            }
        }

        a {
            color: #5b98d7;
            text-decoration: none;
            font-weight: 600;
        }

        a:hover {
            color: #4b88c7;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <svg id="redaxo-logo" viewBox="202.64 375.948 190 29">
                    <path fill="#FFF" d="M220.283 376.362l-14.812.006-2.832 28.14h7.17l.858-8.513h6.91l3.775 8.513h7.804l-4.252-9.587c3.902-2.129 5.238-5.124 5.56-8.77.409-4.625-3.525-9.789-10.181-9.789zm-.542 12.499h-8.354l.544-5.394c3.114.016 7.11.038 8.53.038 1.833 0 2.85 1.255 2.85 2.571 0 1.736-1.466 2.785-3.57 2.785zM238.429 393.322h14.316l.638-6.33H239.11c.598-2.541 1.827-3.527 3.569-3.527l13.481.002.71-7.101-14.199-.003c-6.501 0-10.315 3.946-11.29 13.47-.923 9.02 3.089 14.675 10.343 14.675h12.303l.733-7.164c-4.086-.021-10.754-.119-12.812-.128-2.451-.008-3.407-.968-3.519-3.894zM272.084 376.366h-13.562l-2.841 28.142 14.93-.001c13.873 0 16.278-28.141 1.473-28.141zm2.974 13.435c-.491 5.17-2.383 7.572-4.602 7.572h-6.885l1.399-13.877h6.83c2.092 0 3.614 2.55 3.258 6.305z"></path>
                    <path fill="#2A3542" d="M300.14 376.366h-5.529c-6.216 0-9.932 4.109-10.666 10.567l-1.773 17.598h7.17l.897-8.907h11.947l-.919 8.907h7.172l1.74-16.87c.688-6.804-3.688-11.274-10.039-11.295zm2.943 10.575l-.242 2.351h-11.963l.166-1.644c.319-3.144 1.359-4.117 3.586-4.192h5.503c2.374.009 3.216 1.247 2.95 3.485z"></path>
                    <path fill="#2A3542" d="M317.4 376.433l5.222 8.611 5.52-8.611h8.929l-10.11 15.25 8.768 12.801h-8.397l-4.245-6.678-4.444 6.678h-9.245l9.151-13.417-9.139-14.634h7.99z"></path>
                    <path fill="#2A3542" d="M350.776 376.237l-6.472.003c-6.083 0-10.135 5.361-10.957 13.559-.868 8.657 2.417 14.89 10.42 14.913l5.678-.017c6.83.013 10.275-4.826 11.215-14.188.945-9.419-3.461-14.27-9.884-14.27zm2.689 14.426c-.562 5.407-1.865 6.823-4.471 6.823-.606 0-5.037.017-5.037.017-2.415.053-4.041-1.272-3.464-6.859.519-5.018 1.927-7.192 4.326-7.192l5.792-.003c2.62-.001 3.399 1.59 2.854 7.214z"></path>
                    <path fill="#FFF" d="M370.768 397.044h-3.95c-.948 0-1.504.337-1.766 2.58-.23 1.99.404 2.5 1.353 2.5.74 0 2.439.015 3.851.007l-.264 2.609c-1.438.007-3.057-.009-3.765-.009-2.719 0-4.049-2.074-3.769-5.369.26-3.047 1.598-4.926 4.118-4.926h4.456l-.264 2.608zm21.406-.04l-4.273.001c-1.096 0-1.039 1.313-.165 1.313l1.805.003c4.334 0 4.034 6.387-.342 6.387l-5.157-.008.264-2.608 4.987.008c1.005 0 .994-1.398.156-1.398l-1.713-.001c-4.362 0-4.14-6.302.334-6.302l4.367-.002-.263 2.607zm-13.135.037l-.776 7.705h-2.622l.777-7.705-1.828-.017-.719 7.722h-2.619l.964-10.364 7.591.052c2.322.008 3.921 1.658 3.67 4.145l-.636 6.167h-2.622l.664-6.431c.094-.933-.279-1.27-1.078-1.274h-.766z"></path>
                    <path fill="#2A3542" d="M368.855 376.15a5.295 5.295 0 015.292 5.306 5.293 5.293 0 01-10.584 0 5.297 5.297 0 015.292-5.306zm-2.792 8.169a3.918 3.918 0 005.599 0 4.08 4.08 0 000-5.74 3.917 3.917 0 00-5.599 0 4.062 4.062 0 000 5.74zm3.727-2.395l1.464 2.676h-1.8l-1.135-2.246-.274 2.246h-1.533l.788-6.397h1.923c1.574 0 2.399.935 1.938 2.395-.205.681-.687 1.127-1.371 1.326zm-1.084-.962c.539 0 .831-.221.944-.583.189-.534-.035-.864-.659-.864h-.321l-.177 1.447h.213z"></path>
                </svg>
            </div>
            
            <!-- Language Switcher -->
            <div class="language-switcher">
                <?php foreach ($available_langs as $code => $name): ?>
                    <a href="?lang=<?= $code ?>" 
                       class="language-btn <?= $lang === $code ? 'active' : '' ?>">
                        <?= $name ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1><?= $texts[$lang]['title'] ?></h1>
        <p class="subtitle"><?= $texts[$lang]['subtitle'] ?></p>

        <div class="row">
            <div class="col-12 col-md-6 offset-md-3">
                <!-- System Check -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title"><?= $texts[$lang]['system_check'] ?></h2>
                    </div>
                    <div class="card-body">
                        <div id="system-checks">
                            <!-- Filled by JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Version Select & Install -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title"><?= $texts[$lang]['select_version'] ?></h2>
                    </div>
                    <div class="card-body">
                        <?php
                        $stable_release = null;
                        $beta_release = null;
                        foreach ($releases as $release) {
                            if (!$stable_release && !str_contains($release->tag_name, 'beta')) {
                                $stable_release = $release;
                            }
                            if (!$beta_release && str_contains($release->tag_name, 'beta')) {
                                $beta_release = $release;
                            }
                            if ($stable_release && ($beta_release || !str_contains($release->tag_name, 'beta'))) {
                                break;
                            }
                        }
                        ?>
                        <div class="version-select">
                            <div class="version-option">
                                <input type="radio" name="version" 
                                       id="version-stable"
                                       value="<?= htmlspecialchars($stable_release->assets[0]->browser_download_url) ?>"
                                       data-version="<?= htmlspecialchars($stable_release->tag_name) ?>"
                                       checked>
                                <label for="version-stable">
                                    <?= $texts[$lang]['latest_stable'] ?> 
                                    <span class="version-tag">(<?= htmlspecialchars($stable_release->tag_name) ?>)</span>
                                </label>
                            </div>
                            
                            <?php if ($beta_release): ?>
                            <div class="version-option">
                                <input type="radio" name="version" 
                                       id="version-beta"
                                       value="<?= htmlspecialchars($beta_release->assets[0]->browser_download_url) ?>"
                                       data-version="<?= htmlspecialchars($beta_release->tag_name) ?>">
                                <label for="version-beta">
                                    <?= $texts[$lang]['latest_beta'] ?> 
                                    <span class="version-tag">(<?= htmlspecialchars($beta_release->tag_name) ?>)</span>
                                </label>
                            </div>
                            <?php endif; ?>

                            <button type="button" id="toggle-versions" class="version-toggle">
                                <?= $texts[$lang]['show_all'] ?>
                            </button>

                            <select id="version-select" class="custom-select" style="display: none;">
                                <?php foreach ($releases as $release): ?>
                                    <option value="<?= htmlspecialchars($release->assets[0]->browser_download_url) ?>">
                                        <?= htmlspecialchars($release->name) ?>
                                        <?= str_contains($release->tag_name, 'beta') ? ' (Beta)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button id="install-btn" class="btn btn-primary" disabled>
                            <?= $texts[$lang]['install_start'] ?>
                        </button>

                        <div id="progress" style="display: none;">
                            <!-- Progress indicators will be added here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
const texts = <?= json_encode($texts[$lang]) ?>;
let allChecksOk = false;

async function performChecks() {
    const checksDiv = document.getElementById('system-checks');
    checksDiv.innerHTML = `
        <div class="check-item">
            <span>${texts.requirements.php_version}</span>
            <span class="check-status">${texts.status.checking}</span>
        </div>
        <div class="check-item">
            <span>${texts.requirements.extensions}</span>
            <span class="check-status">${texts.status.checking}</span>
        </div>
        <div class="check-item">
            <span>${texts.requirements.permissions}</span>
            <span class="check-status">${texts.status.checking}</span>
        </div>
    `;

    try {
        const response = await fetch('?action=check');
        const checks = await response.json();
        
        // Update checks with results
        const checkItems = checksDiv.querySelectorAll('.check-item');
        
        // PHP Version
        checkItems[0].querySelector('.check-status').textContent = 
            checks.checks.php_version.status ? texts.status.ok : texts.status.error;
        checkItems[0].querySelector('.check-status').className = 
            `check-status ${checks.checks.php_version.status ? 'status-ok' : 'status-error'}`;
            
        // Extensions
        checkItems[1].querySelector('.check-status').textContent = 
            checks.checks.extensions.status ? texts.status.ok : texts.status.error;
        checkItems[1].querySelector('.check-status').className = 
            `check-status ${checks.checks.extensions.status ? 'status-ok' : 'status-error'}`;
            
        // Permissions
        checkItems[2].querySelector('.check-status').textContent = 
            checks.checks.permissions.status ? texts.status.ok : texts.status.error;
        checkItems[2].querySelector('.check-status').className = 
            `check-status ${checks.checks.permissions.status ? 'status-ok' : 'status-error'}`;

        allChecksOk = Object.values(checks.checks).every(check => check.status);
        document.getElementById('install-btn').disabled = !allChecksOk;
    } catch (error) {
        console.error('Error checking system:', error);
        checksDiv.innerHTML = '<div class="check-item"><span class="status-error">Error checking system requirements</span></div>';
    }
}

async function startInstall() {
    const url = document.querySelector('input[name="version"]:checked')?.value || 
                document.getElementById('version-select').value;
    if (!url) return;

    const progress = document.getElementById('progress');
    progress.style.display = 'block';
    
    // Download phase
    progress.innerHTML = `
        <div class="progress">
            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
        </div>
        <div class="progress-status">
            <div class="spinner"></div>
            <span>${texts.downloading}</span>
        </div>
    `;

    try {
        const downloadResponse = await fetch(`?action=download&url=${encodeURIComponent(url)}`);
        const downloadResult = await downloadResponse.json();
        
        if (!downloadResult.success) {
            progress.innerHTML = `<div class="check-item"><span class="status-error">Error downloading REDAXO</span></div>`;
            return;
        }

        // Extract phase
        progress.innerHTML = `
            <div class="progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
            </div>
            <div class="progress-status">
                <div class="spinner"></div>
                <span>${texts.extracting}</span>
            </div>
        `;

        const extractResponse = await fetch('?action=extract');
        const result = await extractResponse.json();

        if (result.success) {
            // Complete with cleanup confirmation
            progress.innerHTML = `
                <div class="text-center text-success">
                    <div style="font-size: 1.2rem; font-weight: 600; margin-bottom: 5px;">
                        ${texts.completed}
                    </div>
                    <div>${texts.redirecting} <span id="countdown">3</span>...</div>
                </div>
            `;
            
            // Countdown before redirect
            let seconds = 3;
            const countdownElement = document.getElementById('countdown');
            const countdownInterval = setInterval(() => {
                seconds--;
                countdownElement.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = result.redirect;
                }
            }, 1000);
        } else {
            progress.innerHTML = `<div class="check-item"><span class="status-error">Installation error</span></div>`;
        }
    } catch (error) {
        console.error('Installation error:', error);
        progress.innerHTML = `<div class="check-item"><span class="status-error">Installation error: ${error.message}</span></div>`;
    }
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Version selection
    document.querySelectorAll('input[name="version"]').forEach(radio => {
        radio.addEventListener('change', e => {
            document.getElementById('install-btn').disabled = !allChecksOk;
        });
    });

    document.getElementById('version-select').addEventListener('change', e => {
        document.getElementById('install-btn').disabled = !allChecksOk;
        document.querySelectorAll('input[name="version"]').forEach(radio => radio.checked = false);
    });

    // Toggle version select
    document.getElementById('toggle-versions').addEventListener('click', e => {
        const select = document.getElementById('version-select');
        const isHidden = select.style.display === 'none';
        select.style.display = isHidden ? 'block' : 'none';
        e.target.textContent = isHidden ? texts.hide_all : texts.show_all;
    });

    // Install button
    document.getElementById('install-btn').addEventListener('click', startInstall);

    // Perform system checks
    performChecks();
});
</script>
</body>
</html>
