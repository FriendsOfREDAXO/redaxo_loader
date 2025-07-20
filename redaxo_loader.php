<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Start session if you want language preference to persist across requests without URL param
// session_start();

/**
 * Download REDAXO releases from github
 * License: MIT License
 * Version: 1.5 (Enhanced)
 * https://github.com/FriendsOfREDAXO/redaxo_loader
 */

// --- Configuration ---
// github token from @rex-bot https://github.com/rex-bot (obfuscated)
$githubtoken = 'QW3BFZrBzNw9EV4pHbolnN3N1ShdXeDBlaYF0bLxkNs90czg3Xwh2Z';
// github releases from
define('REPO', 'redaxo/redaxo');
// set loader name
$loader_name = basename(__FILE__);
// Paths
$install_path = './';
$install_file = $install_path . 'redaxo.zip';
$loader_file = $install_path . $loader_name;
// Supported Languages
$supported_languages = ['de', 'en'];
$default_language = 'de';

// --- Language Detection ---
$current_lang = $default_language;
if (isset($_GET['lang']) && in_array($_GET['lang'], $supported_languages)) {
    $current_lang = $_GET['lang'];
    // Optional: Store in session
    // $_SESSION['lang'] = $current_lang;
} /* elseif (isset($_SESSION['lang'])) { // Optional: Check session
    $current_lang = $_SESSION['lang'];
} */ elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    foreach ($langs as $lang) {
        $lang_code = substr(trim(explode(';', $lang)[0]), 0, 2);
        if (in_array($lang_code, $supported_languages)) {
            $current_lang = $lang_code;
            break;
        }
    }
}

// --- Translations ---
$translations = [
    'redaxo_loader_title' => ['de' => 'REDAXO Loader', 'en' => 'REDAXO Loader'],
    'redaxo_loader_tagline' => ['de' => 'Lade die gewünschte Version des <a href="https://www.redaxo.org" target="_blank">REDAXO CMS</a> von GitHub herunter,<br>entpacke sie automatisch auf deinem Server und beginne sofort mit der Installation.', 'en' => 'Download the desired version of the <a href="https://www.redaxo.org" target="_blank">REDAXO CMS</a> from GitHub,<br>unpack it automatically on your server and start the installation immediately.'],
    'select_version_prompt' => ['de' => 'Bitte REDAXO-Version wählen', 'en' => 'Please select REDAXO version'],
    'button_download_unpack' => ['de' => 'REDAXO herunterladen und entpacken', 'en' => 'Download and unpack REDAXO'],
    'error_requirements_title' => ['de' => 'Fehler: Voraussetzungen nicht erfüllt', 'en' => 'Error: Requirements not met'],
    'error_js_download' => ['de' => 'Ein Fehler beim Herunterladen ist aufgetreten.', 'en' => 'An error occurred during download.'],
    'error_js_unzip' => ['de' => 'Ein Fehler beim Entpacken ist aufgetreten.', 'en' => 'An error occurred during unpacking.'],
    'alert_download_success' => ['de' => '<code>redaxo.zip</code> wurde heruntergeladen und wird jetzt entpackt.', 'en' => '<code>redaxo.zip</code> has been downloaded and will now be unpacked.'],
    'alert_unzip_error' => ['de' => 'Beim Entpacken ist ein Fehler aufgetreten', 'en' => 'An error occurred during unpacking'],
    'alert_unzip_success' => ['de' => 'REDAXO wurde erfolgreich entpackt. Du wirst in 5 Sekunden <a href="{redirect_url}">zum Setup</a> weitergeleitet.', 'en' => 'REDAXO was successfully unpacked. You will be redirected <a href="{redirect_url}">to the setup</a> in 5 seconds.'],
    'js_alert_unzipping' => ['de' => 'REDAXO wurde heruntergeladen und wird jetzt entpackt.', 'en' => 'REDAXO has been downloaded and will now be unpacked.'],
    'github_rate_limit_exceeded' => ['de' => 'Die Anfragen an die GitHub-API von diesem Server (IP: <code>{server_ip}</code>) wurden aufgebraucht.<br>Ein Reset erfolgt ca. am: <strong>{reset_time} Uhr</strong>. Bitte versuche es danach erneut.', 'en' => 'The GitHub API request limit for this server (IP: <code>{server_ip}</code>) has been exceeded.<br>A reset will occur around: <strong>{reset_time}</strong>. Please try again after that time.'],
    'error_folder_exists' => ['de' => 'Es existiert bereits ein Ordner <code>/redaxo</code>.', 'en' => 'A folder named <code>/redaxo</code> already exists.'],
    'error_invalid_loader_name' => ['de' => 'Der Dateiname des Loaders darf nicht <code>index.php</code> sein.', 'en' => 'The loader filename must not be <code>index.php</code>.'],
    'error_php_curl_missing' => ['de' => 'Die PHP-Erweiterung <code>curl</code> wurde nicht gefunden.', 'en' => 'The PHP extension <code>curl</code> was not found.'],
    'error_php_zip_missing' => ['de' => 'Die PHP-Erweiterung <code>zip</code> wurde nicht gefunden.', 'en' => 'The PHP extension <code>zip</code> was not found.'],
    'error_cmd_curl_missing' => ['de' => 'Das Kommandozeilen-Tool <code>curl</code> wurde nicht gefunden.', 'en' => 'The command line tool <code>curl</code> was not found.'],
    'error_cmd_unzip_missing' => ['de' => 'Das Kommandozeilen-Tool <code>unzip</code> wurde nicht gefunden.', 'en' => 'The command line tool <code>unzip</code> was not found.'],
    'error_json_decode_missing' => ['de' => 'Die Funktion <code>json_decode</code> wurde nicht gefunden.', 'en' => 'The function <code>json_decode</code> was not found.'],
    'error_file_put_contents_missing' => ['de' => 'Die Funktion <code>file_put_contents</code> wurde nicht gefunden.', 'en' => 'The function <code>file_put_contents</code> was not found.'],
    'error_invalid_download_url' => ['de' => 'Ungültige Download-URL angegeben.', 'en' => 'Invalid download URL provided.'],
    'info_published' => ['de' => 'Veröffentlicht', 'en' => 'Published'],
    'info_view_on_github' => ['de' => 'Auf GitHub ansehen', 'en' => 'View on GitHub'],
    'error_github_fetch' => ['de' => 'REDAXO-Versionen konnten nicht von GitHub abgerufen werden.', 'en' => 'Could not fetch REDAXO versions from GitHub.'],
    'nav_de' => ['de' => 'DE', 'en' => 'DE'],
    'nav_en' => ['de' => 'EN', 'en' => 'EN'],
];

function t($key, $replacements = []) {
    global $translations, $current_lang, $default_language;
    $lang_to_use = $current_lang ?? $default_language;
    if (isset($translations[$key][$lang_to_use])) {
        $text = $translations[$key][$lang_to_use];
        foreach ($replacements as $placeholder => $value) {
            $text = str_replace('{' . $placeholder . '}', $value, $text);
        }
        return $text;
    }
    // Fallback to default language or key
    if (isset($translations[$key][$default_language])) {
         $text = $translations[$key][$default_language];
         foreach ($replacements as $placeholder => $value) {
            $text = str_replace('{' . $placeholder . '}', $value, $text);
        }
        return $text . ' ['.$lang_to_use.'?]' ; // Indicate fallback
    }
    return $key; // Return key if no translation found
}

// --- Helper Functions ---

/**
 * Checks if a command-line tool is available in the system's PATH.
 * @param string $command The command name (e.g., 'curl', 'unzip').
 * @return bool True if available, false otherwise.
 */
function is_command_available($command) {
    // Use 'command -v' which is POSIX standard and generally preferred over 'which'
    // Redirect output to /dev/null to prevent printing to output
    // Check the return status (0 means success)
    @exec('command -v ' . escapeshellarg($command) . ' > /dev/null 2>&1', $output, $return_var);
    return $return_var === 0;
}

/**
 * Basic check if URL seems like a valid GitHub release download URL for the specified repo.
 * @param string $url
 * @return bool
 */
function checkUrl($url) {
    // Basic validation: starts with the repo URL and contains /releases/download/
    return strpos($url, 'https://github.com/' . REPO . '/releases/download/') === 0 &&
           filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Checks GitHub API rate limit using provided token.
 * @param string $url GitHub Rate Limit API endpoint.
 * @param string $githubtoken Obfuscated token.
 * @return array|false Array with limit info or false on failure.
 */
function check_x_rate_limit($url, $githubtoken) {
    $limits = false;
    if (false !== filter_var($url, FILTER_VALIDATE_URL)) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'REDAXO Loader');
        curl_setopt($ch, CURLOPT_HEADER, false); // We don't need headers in the body for rate_limit endpoint
        curl_setopt($ch, CURLOPT_ENCODING, '');

        $header = [
            'Accept: application/vnd.github+json',
            'X-GitHub-Api-Version: 2022-11-28',
            'Authorization: token ' . base64_decode(strrev($githubtoken))
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $data = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200 && $data) {
            $ghlimits = json_decode($data, true);
            // Check the 'core' limit for REST API v3
            if (isset($ghlimits['resources']['core'])) {
                $limits = $ghlimits['resources']['core'];
            } elseif (isset($ghlimits['rate'])) { // Fallback for older structure or different endpoint?
                $limits = $ghlimits['rate'];
            }
        }
    }
    return $limits;
}

/**
 * Fetches content from a URL using cURL with GitHub token authentication.
 * Handles redirects automatically.
 * @param string $url URL to fetch.
 * @param string $githubtoken Obfuscated token.
 * @return string|false Content or false on failure.
 */
function curl_file_get_contents($url, $githubtoken) {
    if (false === filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Important for GitHub release downloads
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'REDAXO Loader');
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    // Required for GitHub API calls, harmless for direct downloads
    $header = [
        'Accept: application/vnd.github+json', // Less relevant for direct download but good practice
        'X-GitHub-Api-Version: 2022-11-28',
        'Authorization: token ' . base64_decode(strrev($githubtoken))
    ];
    // For direct asset downloads, GitHub might require Accept: application/octet-stream
    // but usually works without it if following redirects. Let's keep the API headers for simplicity.
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

    $content = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check if download was successful (typically 200 OK, or 302 Found then 200 OK due to redirect)
    if ($http_code >= 200 && $http_code < 400 && $content !== false) {
        return $content;
    }

    return false;
}

// --- Requirements Check ---
$required = [];
$releases = null;

if ('index.php' === $loader_name) {
    $required[] = t('error_invalid_loader_name');
}
if (!in_array('curl', get_loaded_extensions())) {
    $required[] = t('error_php_curl_missing');
} else {
    // Check rate limit only if PHP curl is available
    $x_rate_limit = check_x_rate_limit('https://api.github.com/rate_limit', $githubtoken);
    if ($x_rate_limit === false) {
         $required[] = 'GitHub API rate limit could not be checked.'; // Generic error
    } elseif ($x_rate_limit['remaining'] <= 0) {
        $required[] = t('github_rate_limit_exceeded', [
            'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'N/A',
            'reset_time' => date('d.m.Y H:i:s', $x_rate_limit['reset'] ?? time())
        ]);
    } else {
        // Fetch releases only if rate limit is okay
        $releasesJson = curl_file_get_contents('https://api.github.com/repos/' . REPO . '/releases', $githubtoken);
        if ($releasesJson) {
            $releases = json_decode($releasesJson);
            if (json_last_error() !== JSON_ERROR_NONE) {
                 $required[] = 'Failed to decode GitHub releases JSON: ' . json_last_error_msg();
                 $releases = null;
            }
        } else {
            $required[] = t('error_github_fetch');
        }
    }
}
if (!in_array('zip', get_loaded_extensions())) {
    $required[] = t('error_php_zip_missing');
}
// Check for command line tools
if (!is_command_available('curl')) {
    $required[] = t('error_cmd_curl_missing');
}
if (!is_command_available('unzip')) {
    $required[] = t('error_cmd_unzip_missing');
}
if (!function_exists('json_decode')) {
    $required[] = t('error_json_decode_missing');
}
if (!function_exists('file_put_contents')) {
    $required[] = t('error_file_put_contents_missing');
}
if (!is_writable($install_path)) {
     $required[] = t('error_not_writable', ['path' => realpath($install_path) ?: $install_path]);
}
// Check for existing redaxo folder only if other requirements pass
if (empty($required) && is_dir($install_path . 'redaxo')) {
    $required[] = t('error_folder_exists');
}


// --- AJAX Handling ---
if (isset($_GET['func'])) {
    header('Content-Type: text/html; charset=utf-8'); // Ensure correct encoding for AJAX response
    $func = $_GET['func'];

    // Only proceed if basic requirements were met initially
    // Note: Rate limit might have changed, but we checked once on page load.
    // A second check here might be good for robustness but increases API usage.

    if ('download' === $func) {
        $url = $_GET['url'] ?? null;
        if ($url && checkUrl($url)) {
            $redaxo_core = curl_file_get_contents($url, $githubtoken);
            if ($redaxo_core !== false) {
                if (file_put_contents($install_file, $redaxo_core)) {
                    echo '<div class="alert alert-warning">' . t('alert_download_success') . '</div>';
                } else {
                    // Error writing file
                     http_response_code(500); // Internal Server Error
                     echo '<div class="alert alert-danger">Error writing file to disk. Check permissions.</div>';
                }
            } else {
                // Error downloading file
                http_response_code(500); // Internal Server Error
                echo '<div class="alert alert-danger">Error downloading file from GitHub.</div>';
            }
        } else {
            http_response_code(400); // Bad Request
            echo '<div class="alert alert-danger">' . t('error_invalid_download_url') . '</div>';
        }
        exit; // Important: Stop script execution after AJAX response
    }

    if ('unzip' === $func) {
        if (!file_exists($install_file)) {
             http_response_code(404); // Not found
             echo '<div class="alert alert-danger">Error: redaxo.zip not found.</div>';
             exit;
        }

        $zip = new ZipArchive();
        $res = $zip->open($install_file);

        if ($res === true) {
            // Try to find the base directory inside the zip (e.g., redaxo-5.15.1/)
            $firstEntry = $zip->getNameIndex(0);
            $baseDir = '';
            if (strpos($firstEntry, '/') !== false) {
                $baseDir = explode('/', $firstEntry)[0] . '/';
            }

            if ($zip->extractTo($install_path)) {
                $zip->close();

                $sourceDir = $install_path . $baseDir . 'redaxo'; // Path inside the extracted folder
                $targetDir = $install_path . 'redaxo';

                // Check if extraction created a subdirectory (typical for GitHub zips)
                if ($baseDir !== '' && is_dir($sourceDir)) {
                    // Move contents from subdirectory/redaxo to ./redaxo
                    if (rename($sourceDir, $targetDir)) {
                        // Optionally remove the now empty base directory (best effort)
                         @rmdir($install_path . $baseDir);
                    } else {
                         echo '<div class="alert alert-danger">Error moving extracted files from subfolder. Please move contents of '.htmlspecialchars($sourceDir).' to '.htmlspecialchars($targetDir).' manually.</div>';
                         @unlink($install_file); // Clean up zip even on move error
                         exit;
                    }
                } elseif (!is_dir($targetDir)) {
                     // If baseDir was empty, but ./redaxo still doesn't exist, something went wrong
                     echo '<div class="alert alert-danger">Extraction seemed successful, but the /redaxo directory was not found. Check the zip contents.</div>';
                     @unlink($install_file); // Clean up zip
                     exit;
                }

                // Calculate redirect URL relative to the *current* script execution
                $base_uri = dirname($_SERVER['SCRIPT_NAME']); // Get directory part of the current script's URL path
                // Ensure it ends with a slash if it's not the root
                $base_uri = rtrim($base_uri, '/') . '/';
                $redirect = $base_uri . 'redaxo/'; // Append 'redaxo/'

                echo '<div class="alert alert-success">' . t('alert_unzip_success', ['redirect_url' => $redirect]) . '</div>';
                @unlink($install_file); // Use @ to suppress errors if file is already gone
                 echo '<script>setTimeout(function(){ window.location.replace("' . addslashes($redirect) . '"); }, 5000);</script>';
                @unlink($loader_file); // Self-destruct loader script
            } else {
                $zip->close(); // Close zip even if extraction fails
                 http_response_code(500);
                 echo '<div class="alert alert-danger">'.t('alert_unzip_error').' (ExtractTo failed). Check permissions and disk space.</div>';
            }
        } else {
            http_response_code(500);
            echo '<div class="alert alert-danger">' . t('alert_unzip_error') . ' (Could not open ZipArchive, error code: ' . $res . ').</div>';
        }
        exit; // Important: Stop script execution after AJAX response
    }
}
// --- HTML Output ---
// Only show HTML if not handling an AJAX request
?>
<!DOCTYPE html>
<html lang="<?= $current_lang ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?= t('redaxo_loader_title') ?></title>

    <!-- Favicon -->
    <link href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAABIFBMVEX///9OltFNmdJMl9BAgL9VjsZNmdNNmdNNmdNOmtUA//9LltJMmdNOl9RNmdNMmNNOmdJOmtNOmtNNmdNNmtRNmdNNmdNNmdJNmdNNmdJNmdNNmdNMmNROmdJNmtJMmdNMmNNNmdNNmNNNmdNNmdJNmdJNmdJNmdNNmdNOm9NNmdNOmdRMmdJNmdNNmdNOmtNKldVNmdNMmdNNmtNOmdNNmdNNmtRNmdNNmtNNmdNmmcxNmdRNmdJJkttNmdNOndhPldOAgP9NmdNNmdNMmNJOmNJOmtJNmNRPmtNPmdJMmNNNmtNOmtRRl9FNmtRNmdNOmdNJm9FMmdNQn89NmdNNmNNMmdRNmdNNmdNOnNVOmNNNmdVQmdNJns5NmdP////jCU31AAAAXnRSTlMAJygbBAn+5J5OASK7O+5XVXlv+YjBolr8eHp7fH1+f5DNldXljrfvyxf62bHS7ZcY+8VMzNGw9lbpBYLIB+YaHQL9+Eqaq4E6LWjJWBY14oAcdRD0Y5PT0CRFHiMVY/kodwAAAAFiS0dEAIgFHUgAAAAJcEhZcwAABG4AAARuAdCjsmgAAAAHdElNRQfjAwwTHCCSC2ZYAAABLklEQVQ4y4WU51bCQBCFV0AEjAU7YBRsYMOGKDYExF6xF+b9H0NPZiaZXRK8/2b225zdO3ej1J96QrrCEaWpF0xF+2LxhAf0g5+ssAsM+AIwOMTAMAQoScBIEDAaQmAsCIBxPOlEIACTDmBhMZVKpjOZaXtmNptjYM4B5rFY8C6eWCRgySnzWBSEd8sr2FuVRq5Jd9exV5RGboj1zS3sbUsjbW84OyW5iY3cLZOsPfea+12NBKgcdDcSDv8x8uhYGtmpk6qSRnZO6pQulfdft2tGIusNFANnZiKbVJ9TbZmJvKD6kj9xZSTymnfcUCNmJDLOwC01Gnd6Iu8ZiDxQ51FpRj65w2zxJDQjo957fH4h4lUaWRFxeSPgXRr5IYDPOhFf4u1/y0T+0H+grdQv/i3d/pZj7q4AAAAldEVYdGRhdGU6Y3JlYXRlADIwMTktMDMtMTJUMTk6Mjg6MzIrMDE6MDCHzxaCAAAAJXRFWHRkYXRlOm1vZGlmeQAyMDE5LTAzLTEyVDE5OjI4OjMyKzAxOjAw9pKuPgAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAABXelRYdFJhdyBwcm9maWxlIHR5cGUgaXB0YwAAeJzj8gwIcVYoKMpPy8xJ5VIAAyMLLmMLEyMTS5MUAxMgRIA0w2QDI7NUIMvY1MjEzMQcxAfLgEigSi4A6hcRdPJCNZUAAAAASUVORK5CYII=" rel="icon" type="image/x-icon">

    <!-- Bootstrap core CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #5b98d7;
            --primary-color-hover: #4a89c6;
            --text-color: #343f50;
            --bg-color: #fff;
            --card-bg: #f8f9fa;
            --card-border: rgba(0, 0, 0, .125);
            --navbar-bg: #5b98d7; /* Default Bootstrap dark navbar */
            --navbar-color: rgba(255, 255, 255, 0.75);
            --navbar-hover-color: rgba(255, 255, 255, 1);
            --link-color: var(--primary-color);
            --link-hover-color: var(--primary-color-hover);
            --heading-color: var(--primary-color);
            --alert-warning-bg: #fff3cd;
            --alert-warning-text: #856404;
            --alert-warning-border: #ffeeba;
            --alert-success-bg: #d4edda;
            --alert-success-text: #155724;
            --alert-success-border: #c3e6cb;
            --alert-danger-bg: #f8d7da;
            --alert-danger-text: #721c24;
            --alert-danger-border: #f5c6cb;
            --progress-bar-bg: var(--primary-color);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --text-color: #adb5bd; /* Lighter grey for text */
                --bg-color: #212529; /* Dark background */
                --card-bg: #343a40; /* Slightly lighter dark for cards */
                --card-border: rgba(255, 255, 255, .125);
                --navbar-bg: #000; /* Match page background */
                --navbar-color: rgba(255, 255, 255, 0.6);
                --navbar-hover-color: rgba(255, 255, 255, 0.8);
                --link-color: #8cbceb; /* Lighter blue for links */
                --link-hover-color: #a1cffa;
                --heading-color: #8cbceb;
                 /* Adjust alert colors for dark mode */
                --alert-warning-bg: #4d3c0c;
                --alert-warning-text: #ffeeba;
                --alert-warning-border: #856404;
                --alert-success-bg: #0b2e13;
                --alert-success-text: #c3e6cb;
                --alert-success-border: #155724;
                --alert-danger-bg: #491217;
                --alert-danger-text: #f5c6cb;
                --alert-danger-border: #721c24;
                --progress-bar-bg: #8cbceb; /* Lighter blue for progress */
            }
            /* Make logo readable on dark background */
             #redaxo-logo path[fill="#2A3542"] {
                 fill: #ced4da; /* Change dark parts to light grey */
            }
        }

        body {
            font-family: 'Source Sans Pro', sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--bg-color);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .navbar {
            background-color: var(--navbar-bg) !important; /* Override Bootstrap */
        }
        .navbar .navbar-brand,
        .navbar .nav-link {
             color: var(--navbar-color) !important;
        }
         .navbar .nav-link:hover,
         .navbar .navbar-brand:hover {
             color: var(--navbar-hover-color) !important;
         }
         .navbar .nav-link.active {
              font-weight: bold;
              color: var(--navbar-hover-color) !important;
         }


        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover, .btn-primary:focus {
             background-color: var(--primary-color-hover);
             border-color: var(--primary-color-hover);
        }
         .btn-primary.disabled, .btn-primary:disabled {
              background-color: var(--primary-color);
              border-color: var(--primary-color);
              opacity: 0.65;
          }

        .progress-bar {
            background-color: var(--progress-bar-bg) !important;
        }

        a {
            color: var(--link-color);
            font-weight: 600;
        }
        a:hover {
            color: var(--link-hover-color);
        }

        h1, h2, h3 {
            color: var(--heading-color);
            font-weight: 600;
        }
        h1 { font-size: 3.125rem; }
        h2 { font-size: 2rem; }

        .btn { width: 100%; }

        .card {
             background-color: var(--card-bg);
             border-color: var(--card-border);
             color: var(--text-color);
        }
         .card-header {
             background-color: rgba(0,0,0,0.05); /* Subtle header background */
             border-bottom-color: var(--card-border);
         }
         .card-header h2 {
             margin-bottom: 0;
             font-size: 1.5rem; /* Smaller heading in card */
         }

         /* Style alerts using CSS variables */
         .alert {
              color: var(--alert-text-color);
              background-color: var(--alert-bg-color);
              border-color: var(--alert-border-color);
          }
          .alert-warning {
              --alert-text-color: var(--alert-warning-text);
              --alert-bg-color: var(--alert-warning-bg);
              --alert-border-color: var(--alert-warning-border);
          }
          .alert-success {
              --alert-text-color: var(--alert-success-text);
              --alert-bg-color: var(--alert-success-bg);
              --alert-border-color: var(--alert-success-border);
          }
          .alert-danger {
              --alert-text-color: var(--alert-danger-text);
              --alert-bg-color: var(--alert-danger-bg);
              --alert-border-color: var(--alert-danger-border);
          }
          .alert code { /* Make code readable in alerts */
               color: inherit;
               background-color: rgba(0,0,0,0.1);
               padding: .1em .3em;
               border-radius: 3px;
          }


        .rex-version-list { display: none; } /* Initially hide details */
        #redaxo-logo { width: 190px; }

        .lang-switch { margin-left: auto; } /* Push language switcher to the right */
        .lang-switch .nav-link { padding: 0.5rem 0.3rem; } /* Reduce padding slightly */

    </style>
</head>

<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand navbar-dark static-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <svg id="redaxo-logo" viewBox="202.64 375.948 190 29">
                    <!-- SVG paths remain unchanged -->
                     <path fill="#FFF" d="M220.283 376.362l-14.812.006-2.832 28.14h7.17l.858-8.513h6.91l3.775 8.513h7.804l-4.252-9.587c3.902-2.129 5.238-5.124 5.56-8.77.409-4.625-3.525-9.789-10.181-9.789zm-.542 12.499h-8.354l.544-5.394c3.114.016 7.11.038 8.53.038 1.833 0 2.85 1.255 2.85 2.571 0 1.736-1.466 2.785-3.57 2.785zM238.429 393.322h14.316l.638-6.33H239.11c.598-2.541 1.827-3.527 3.569-3.527l13.481.002.71-7.101-14.199-.003c-6.501 0-10.315 3.946-11.29 13.47-.923 9.02 3.089 14.675 10.343 14.675h12.303l.733-7.164c-4.086-.021-10.754-.119-12.812-.128-2.451-.008-3.407-.968-3.519-3.894zM272.084 376.366h-13.562l-2.841 28.142 14.93-.001c13.873 0 16.278-28.141 1.473-28.141zm2.974 13.435c-.491 5.17-2.383 7.572-4.602 7.572h-6.885l1.399-13.877h6.83c2.092 0 3.614 2.55 3.258 6.305z"></path>
                     <path fill="#2A3542" d="M300.14 376.366h-5.529c-6.216 0-9.932 4.109-10.666 10.567l-1.773 17.598h7.17l.897-8.907h11.947l-.919 8.907h7.172l1.74-16.87c.688-6.804-3.688-11.274-10.039-11.295zm2.943 10.575l-.242 2.351h-11.963l.166-1.644c.319-3.144 1.359-4.117 3.586-4.192h5.503c2.374.009 3.216 1.247 2.95 3.485z"></path>
                     <path fill="#2A3542" d="M317.4 376.433l5.222 8.611 5.52-8.611h8.929l-10.11 15.25 8.768 12.801h-8.397l-4.245-6.678-4.444 6.678h-9.245l9.151-13.417-9.139-14.634h7.99z"></path>
                     <path fill="#2A3542" d="M350.776 376.237l-6.472.003c-6.083 0-10.135 5.361-10.957 13.559-.868 8.657 2.417 14.89 10.42 14.913l5.678-.017c6.83.013 10.275-4.826 11.215-14.188.945-9.419-3.461-14.27-9.884-14.27zm2.689 14.426c-.562 5.407-1.865 6.823-4.471 6.823-.606 0-5.037.017-5.037.017-2.415.053-4.041-1.272-3.464-6.859.519-5.018 1.927-7.192 4.326-7.192l5.792-.003c2.62-.001 3.399 1.59 2.854 7.214z"></path>
                     <path fill="#FFF" d="M370.768 397.044h-3.95c-.948 0-1.504.337-1.766 2.58-.23 1.99.404 2.5 1.353 2.5.74 0 2.439.015 3.851.007l-.264 2.609c-1.438.007-3.057-.009-3.765-.009-2.719 0-4.049-2.074-3.769-5.369.26-3.047 1.598-4.926 4.118-4.926h4.456l-.264 2.608zm21.406-.04l-4.273.001c-1.096 0-1.039 1.313-.165 1.313l1.805.003c4.334 0 4.034 6.387-.342 6.387l-5.157-.008.264-2.608 4.987.008c1.005 0 .994-1.398.156-1.398l-1.713-.001c-4.362 0-4.14-6.302.334-6.302l4.367-.002-.263 2.607zm-13.135.037l-.776 7.705h-2.622l.777-7.705-1.828-.017-.719 7.722h-2.619l.964-10.364 7.591.052c2.322.008 3.921 1.658 3.67 4.145l-.636 6.167h-2.622l.664-6.431c.094-.933-.279-1.27-1.078-1.274h-.766z"></path>
                     <path fill="#2A3542" d="M368.855 376.15a5.295 5.295 0 015.292 5.306 5.293 5.293 0 01-10.584 0 5.297 5.297 0 015.292-5.306zm-2.792 8.169a3.918 3.918 0 005.599 0 4.08 4.08 0 000-5.74 3.917 3.917 0 00-5.599 0 4.062 4.062 0 000 5.74zm3.727-2.395l1.464 2.676h-1.8l-1.135-2.246-.274 2.246h-1.533l.788-6.397h1.923c1.574 0 2.399.935 1.938 2.395-.205.681-.687 1.127-1.371 1.326zm-1.084-.962c.539 0 .831-.221.944-.583.189-.534-.035-.864-.659-.864h-.321l-.177 1.447h.213z"></path>
                </svg>
            </a>
             <!-- Language Switcher -->
             <ul class="navbar-nav lang-switch">
                 <li class="nav-item">
                     <a class="nav-link <?= $current_lang == 'de' ? 'active' : '' ?>" href="?lang=de"><?= t('nav_de') ?></a>
                 </li>
                 <li class="nav-item">
                     <a class="nav-link <?= $current_lang == 'en' ? 'active' : '' ?>" href="?lang=en"><?= t('nav_en') ?></a>
                 </li>
             </ul>
        </div>
    </nav>

    <!-- Page Content -->
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <h1 class="mt-5"><?= t('redaxo_loader_title') ?></h1>
                <p class="lead"><?= t('redaxo_loader_tagline') ?></p>
            </div>
        </div>

        <?php if (!empty($required)): ?>
            <div class="row">
                <div class="col-12 my-3">
                    <div class="alert alert-danger">
                        <h2><?= t('error_requirements_title') ?></h2>
                        <ul><li><?= implode('</li><li>', $required) ?></li></ul>
                    </div>
                </div>
            </div>
        <?php else: // Requirements met, show download form ?>
            <div class="row">
                <div class="col-12 col-md-8 offset-md-2 my-4">
                    <form id="loader-form">
                        <?php if ($releases && count($releases) > 0): ?>
                            <div class="form-group">
                                <label for="version-select"><?= t('select_version_prompt') ?></label>
                                <select id="version-select" class="form-control" required>
                                    <option selected disabled value=""><?= t('select_version_prompt') ?>...</option>
                                    <?php foreach ($releases as $release): ?>
                                        <?php if (!empty($release->assets[0]->browser_download_url)): ?>
                                            <option value="<?= htmlspecialchars($release->assets[0]->browser_download_url) ?>" data-github-id="<?= htmlspecialchars($release->id) ?>">
                                                <?= htmlspecialchars($release->name) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="my-3 btn btn-primary btn-lg disabled" id="start-loader" disabled><?= t('button_download_unpack') ?></button>
                        <?php else: ?>
                            <div class="alert alert-warning"><?= t('error_github_fetch') ?></div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-md-8 offset-md-2">
                    <div id="info" class="my-3"></div> <!-- Area for AJAX messages -->
                </div>
            </div>

            <!-- Release Details (initially hidden) -->
            <?php if ($releases): ?>
                <?php foreach ($releases as $release): ?>
                     <?php if (!empty($release->assets[0]->browser_download_url)): // Only show if downloadable asset exists ?>
                        <div class="row rex-version-list" id="v-<?= htmlspecialchars($release->id) ?>">
                            <div class="col-12 col-md-8 offset-md-2 my-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h2><?= htmlspecialchars($release->name) ?></h2>
                                    </div>
                                    <div class="card-body">
                                        <p><?= t('info_published') ?>: <?= date('d.m.Y', strtotime($release->published_at)) ?></p>
                                        <p><?= t('info_view_on_github') ?>: <a href="<?= htmlspecialchars($release->html_url) ?>" target="_blank" rel="noopener noreferrer"><?= htmlspecialchars($release->html_url) ?></a></p>
                                        <?php /* // Optional: Display release body (markdown needs processing for safe HTML)
                                            require_once 'Parsedown.php'; // Example using Parsedown library
                                            $Parsedown = new Parsedown();
                                            echo '<div class="release-notes">' . $Parsedown->text(htmlspecialchars($release->body)) . '</div>';
                                        */ ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                     <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

        <?php endif; // End requirements check ?>

    </div><!-- /.container -->

    <!-- Bootstrap core JavaScript & jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>

    <?php if (empty($required)): // Only include JS logic if requirements met ?>
    <script>
        $(document).ready(function() {

            const LOADER_URL = '<?= addslashes($loader_name) ?>'; // Get loader name dynamically
            const LANG = '<?= $current_lang ?>'; // Pass current language to JS

            // Translations for JS alerts (can be extended)
            const jsTranslations = {
                alertUnzipping: '<?= addslashes(t('js_alert_unzipping')) ?>',
                errorDownload: '<?= addslashes(t('error_js_download')) ?>',
                errorUnzip: '<?= addslashes(t('error_js_unzip')) ?>'
            };

            $('#version-select').on('change', function() {
                if ($(this).val()) {
                    $('#start-loader').removeClass('disabled').prop('disabled', false);
                    $('.rex-version-list').hide();
                    var gitId = $(this).find(':selected').data('github-id');
                    $('#v-' + gitId).slideDown(); // Show selected version details smoothly
                } else {
                     $('#start-loader').addClass('disabled').prop('disabled', true);
                     $('.rex-version-list').hide();
                }
            });

            function showProgress(message = '') {
                 $('#info').html(
                     (message ? '<div class="alert alert-info">' + message + '</div>' : '') +
                     '<div class="progress mb-3">' +
                     '<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>' +
                     '</div>'
                 );
             }

             function showError(message) {
                 $('#info').html('<div class="alert alert-danger">' + message + '</div>');
                 // Re-enable form? Maybe not, page refresh might be needed if server state is bad
                 // $('#start-loader').removeClass('disabled').prop('disabled', false);
                 // $('#version-select').prop('disabled', false);
             }


            function unzip() {
                $.ajax({
                    url: LOADER_URL + '?func=unzip&lang=' + LANG, // Pass lang
                    type: 'GET',
                    dataType: 'html', // Expect HTML response (alert + script)
                    beforeSend: function() {
                        // Update progress message or keep spinner
                        showProgress(); // Just show spinner while unzipping
                    },
                    success: function(data, textStatus, jqXHR) {
                        $('#info').html(data); // Display success message and redirect script from PHP
                        // Disable form elements permanently after success
                        $('#version-select').prop('disabled', true);
                        $('#start-loader').addClass('disabled').prop('disabled', true);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                         // Try to get error message from response body if available
                         let errorMsg = jqXHR.responseText || jsTranslations.errorUnzip;
                         // Avoid showing full HTML pages as error message
                         if (errorMsg.trim().startsWith('<') || errorMsg.length > 500) {
                            errorMsg = jsTranslations.errorUnzip + ' (Server error: ' + jqXHR.status + ')';
                         }
                         showError(errorMsg);
                    }
                });
            }

            /* Form submit handler */
            $('#loader-form').on('submit', function(e) {
                e.preventDefault(); // prevent native submit

                var downloadUrl = $('#version-select').val();
                if (!downloadUrl) return; // Should not happen if button is enabled

                $('#version-select').prop('disabled', true);
                $('#start-loader').addClass('disabled').prop('disabled', true);

                $.ajax({
                    url: LOADER_URL + '?func=download&lang=' + LANG + '&url=' + encodeURIComponent(downloadUrl), // Pass lang & URLencode URL
                    type: 'GET',
                    dataType: 'html', // Expect HTML alert back
                    beforeSend: function() {
                        showProgress(); // Show spinner
                    },
                    success: function(data, textStatus, jqXHR) {
                        $('#info').html(data); // Show the "downloaded, now unzipping" message
                        // Use the JS translation for the alert confirmation
                        alert(jsTranslations.alertUnzipping);
                        unzip(); // Start unzip process
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                         let errorMsg = jqXHR.responseText || jsTranslations.errorDownload;
                         if (errorMsg.trim().startsWith('<') || errorMsg.length > 500) {
                             errorMsg = jsTranslations.errorDownload + ' (Server error: ' + jqXHR.status + ')';
                         }
                         showError(errorMsg);
                         // Re-enable form on download error
                         $('#version-select').prop('disabled', false);
                         $('#start-loader').removeClass('disabled').prop('disabled', false);
                    }
                });
            });

        });
    </script>
    <?php endif; ?>

</body>
</html>
