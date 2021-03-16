<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);


/**
 * Download REDAXO releases from github
 * License: MIT License
 */

// github releases from
define('REPO', 'redaxo/redaxo');  

// set loader name
$loader_name = basename(__FILE__);

$install_path = './';
$install_file = $install_path . 'redaxo.zip';
$loader_file = $install_path . $loader_name;


// check requirements
$required = [];

if (basename(__FILE__) == 'index.php') {
    $required[] = 'Der Dateiname des Loaders darf nicht <code>index.php</code> sein';
}

if (!in_array('curl', get_loaded_extensions())) {
    $required[] = 'Die Klasse <code>curl</code> wurde nicht gefunden';
}

if (!in_array('zip', get_loaded_extensions())) {
    $required[] = 'Die Klasse <code>zip</code> wurde nicht gefunden';
}

if (!function_exists('json_decode')) {
    $required[] = 'Die Funktion <code>json_decode</code> wurde nicht gefunden';
}
if (!function_exists('file_put_contents')) {
    $required[] = 'Die Funktion <code>file_put_contents</code> wurde nicht gefunden';
}



function checkUrl($url)
{
    if (strpos($url, 'https://github.com/'.REPO.'/releases/') !== false) {
        return true;
    } else {
        return false;
    }
}

// Funktion die file_get_contents mit curl ersetzt
function curl_file_get_contents($url)
{
    if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
        $curly = curl_init();
        curl_setopt($curly, CURLOPT_HEADER, 0);
        curl_setopt($curly, CURLOPT_RETURNTRANSFER, 1); //Return Data
        curl_setopt($curly, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curly, CURLOPT_URL, $url);
        curl_setopt($curly, CURLOPT_USERAGENT, "REDAXO Loader");
        $content = curl_exec($curly);
        
        $error = curl_error($curly);
        $errno = curl_errno($curly);
        
        if (CURLE_OK !== $errno || $error) {
            if (!$error && function_exists('curl_strerror')) {
                 $error = curl_strerror($errno);
            }
            throw new Exception('curl error '.$errno.' while downloading');
        }
        
        curl_close($curly);
        return $content;
    }
    return false;
}

$releases = curl_file_get_contents('https://api.github.com/repos/' . REPO . '/releases');
$releases = json_decode($releases);

// Für den Ajax-Aufruf
if (isset($_GET['func'])) {
    $func = $_GET['func'];

    if ($func === "download") {
        $url = $_GET['url'];
        if (checkUrl($url)) {
            $redaxo_core = curl_file_get_contents($url);
            if (file_put_contents($install_file, $redaxo_core)) {
                echo '<div class="alert alert-warning"><code>redaxo.zip</code> wurde heruntergeladen und wird jetzt entpackt.</div>';
            }
        } else {
            echo 'Falsche Datei';
            exit();
        }
    }

    if ($func === "unzip") {
        $zip = new ZipArchive;
        $res = $zip->open($install_file);

        if ($res === true) {
            $zip->extractTo($install_path);
            $zip->close();
            $redirect = $_SERVER['REQUEST_URI'];
            $redirect = str_replace($loader_name.'?func=unzip', 'redaxo', $redirect);
            echo '<div class="alert alert-success">REDAXO wurde erfolgreich entpackt. Du wirst in 5 Sekunden <a href="'.$redirect.'">zum Setup</a> weitergeleitet.</div>';
            unlink($install_file);
            echo '<script>setTimeout(function(){ window.location.replace("' . $redirect . '"); }, 5000);</script>';
            unlink($loader_file);
        } else {
            echo '<div class="alert alert-danger">Beim Entpacken ist ein Fehler aufgetreten</div>';
        }
    }
}
// Wenn nicht im Ajax Aufruf
else {
    ?>
    <!DOCTYPE html>
    <html lang="de">

    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <title>REDAXO Loader</title>

        <!-- Bootstrap core CSS -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">

        <link href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAABIFBMVEX///9OltFNmdJMl9BAgL9VjsZNmdNNmdNNmdNOmtUA//9LltJMmdNOl9RNmdNMmNNOmdJOmtNOmtNNmdNNmtRNmdNNmdNNmdJNmdNNmdJNmdNNmdNMmNROmdJNmtJMmdNMmNNNmdNNmNNNmdNNmdJNmdJNmdJNmdNNmdNOm9NNmdNOmdRMmdJNmdNNmdNOmtNKldVNmdNMmdNNmtNOmdNNmdNNmtRNmdNNmtNNmdNmmcxNmdRNmdJJkttNmdNOndhPldOAgP9NmdNNmdNMmNJOmNJOmtJNmNRPmtNPmdJMmNNNmtNOmtRRl9FNmtRNmdNOmdNJm9FMmdNQn89NmdNNmNNMmdRNmdNNmdNOnNVOmNNNmdVQmdNJns5NmdP////jCU31AAAAXnRSTlMAJygbBAn+5J5OASK7O+5XVXlv+YjBolr8eHp7fH1+f5DNldXljrfvyxf62bHS7ZcY+8VMzNGw9lbpBYLIB+YaHQL9+Eqaq4E6LWjJWBY14oAcdRD0Y5PT0CRFHiMVY/kodwAAAAFiS0dEAIgFHUgAAAAJcEhZcwAABG4AAARuAdCjsmgAAAAHdElNRQfjAwwTHCCSC2ZYAAABLklEQVQ4y4WU51bCQBCFV0AEjAU7YBRsYMOGKDYExF6xF+b9H0NPZiaZXRK8/2b225zdO3ej1J96QrrCEaWpF0xF+2LxhAf0g5+ssAsM+AIwOMTAMAQoScBIEDAaQmAsCIBxPOlEIACTDmBhMZVKpjOZaXtmNptjYM4B5rFY8C6eWCRgySnzWBSEd8sr2FuVRq5Jd9exV5RGboj1zS3sbUsjbW84OyW5iY3cLZOsPfea+12NBKgcdDcSDv8x8uhYGtmpk6qSRnZO6pQulfdft2tGIusNFANnZiKbVJ9TbZmJvKD6kj9xZSTymnfcUCNmJDLOwC01Gnd6Iu8ZiDxQ51FpRj65w2zxJDQjo957fH4h4lUaWRFxeSPgXRr5IYDPOhFf4u1/y0T+0H+grdQv/i3d/pZj7q4AAAAldEVYdGRhdGU6Y3JlYXRlADIwMTktMDMtMTJUMTk6Mjg6MzIrMDE6MDCHzxaCAAAAJXRFWHRkYXRlOm1vZGlmeQAyMDE5LTAzLTEyVDE5OjI4OjMyKzAxOjAw9pKuPgAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAABXelRYdFJhdyBwcm9maWxlIHR5cGUgaXB0YwAAeJzj8gwIcVYoKMpPy8xJ5VIAAyMLLmMLEyMTS5MUAxMgRIA0w2QDI7NUIMvY1MjEzMQcxAfLgEigSi4A6hcRdPJCNZUAAAAASUVORK5CYII=" rel="icon" type="image/x-icon">

        <style>
            body {
                font-family: Source Sans Pro, sans-serif;
                line-height: 1.6;
                color: #343f50;
                background-color: #fff;
            }

            .bg-dark,
            .btn,
            .progress-bar {
                background-color: #5b98d7 !important;
            }

            a {
                color: #5b98d7;
                font-weight: 600;
            }

            h1 {
                color: #5b98d7;
                font-size: 3.125rem;
                font-weight: 600;
            }

            .btn {
                width: 100%;
                border: none;
            }

            .rex-version-list {
                display: none;
            }

            #redaxo-logo {
                width: 190px;
            }
        </style>
    </head>

    <body>

        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark static-top">
            <div class="container">
                <div class="navbar-brand"><svg id="redaxo-logo" viewBox="202.64 375.948 190 29">
                        <path fill="#FFF" d="M220.283 376.362l-14.812.006-2.832 28.14h7.17l.858-8.513h6.91l3.775 8.513h7.804l-4.252-9.587c3.902-2.129 5.238-5.124 5.56-8.77.409-4.625-3.525-9.789-10.181-9.789zm-.542 12.499h-8.354l.544-5.394c3.114.016 7.11.038 8.53.038 1.833 0 2.85 1.255 2.85 2.571 0 1.736-1.466 2.785-3.57 2.785zM238.429 393.322h14.316l.638-6.33H239.11c.598-2.541 1.827-3.527 3.569-3.527l13.481.002.71-7.101-14.199-.003c-6.501 0-10.315 3.946-11.29 13.47-.923 9.02 3.089 14.675 10.343 14.675h12.303l.733-7.164c-4.086-.021-10.754-.119-12.812-.128-2.451-.008-3.407-.968-3.519-3.894zM272.084 376.366h-13.562l-2.841 28.142 14.93-.001c13.873 0 16.278-28.141 1.473-28.141zm2.974 13.435c-.491 5.17-2.383 7.572-4.602 7.572h-6.885l1.399-13.877h6.83c2.092 0 3.614 2.55 3.258 6.305z"></path>
                        <path fill="#2A3542" d="M300.14 376.366h-5.529c-6.216 0-9.932 4.109-10.666 10.567l-1.773 17.598h7.17l.897-8.907h11.947l-.919 8.907h7.172l1.74-16.87c.688-6.804-3.688-11.274-10.039-11.295zm2.943 10.575l-.242 2.351h-11.963l.166-1.644c.319-3.144 1.359-4.117 3.586-4.192h5.503c2.374.009 3.216 1.247 2.95 3.485z"></path>
                        <path fill="#2A3542" d="M317.4 376.433l5.222 8.611 5.52-8.611h8.929l-10.11 15.25 8.768 12.801h-8.397l-4.245-6.678-4.444 6.678h-9.245l9.151-13.417-9.139-14.634h7.99z"></path>
                        <path fill="#2A3542" d="M350.776 376.237l-6.472.003c-6.083 0-10.135 5.361-10.957 13.559-.868 8.657 2.417 14.89 10.42 14.913l5.678-.017c6.83.013 10.275-4.826 11.215-14.188.945-9.419-3.461-14.27-9.884-14.27zm2.689 14.426c-.562 5.407-1.865 6.823-4.471 6.823-.606 0-5.037.017-5.037.017-2.415.053-4.041-1.272-3.464-6.859.519-5.018 1.927-7.192 4.326-7.192l5.792-.003c2.62-.001 3.399 1.59 2.854 7.214z"></path>
                        <path fill="#FFF" d="M370.768 397.044h-3.95c-.948 0-1.504.337-1.766 2.58-.23 1.99.404 2.5 1.353 2.5.74 0 2.439.015 3.851.007l-.264 2.609c-1.438.007-3.057-.009-3.765-.009-2.719 0-4.049-2.074-3.769-5.369.26-3.047 1.598-4.926 4.118-4.926h4.456l-.264 2.608zm21.406-.04l-4.273.001c-1.096 0-1.039 1.313-.165 1.313l1.805.003c4.334 0 4.034 6.387-.342 6.387l-5.157-.008.264-2.608 4.987.008c1.005 0 .994-1.398.156-1.398l-1.713-.001c-4.362 0-4.14-6.302.334-6.302l4.367-.002-.263 2.607zm-13.135.037l-.776 7.705h-2.622l.777-7.705-1.828-.017-.719 7.722h-2.619l.964-10.364 7.591.052c2.322.008 3.921 1.658 3.67 4.145l-.636 6.167h-2.622l.664-6.431c.094-.933-.279-1.27-1.078-1.274h-.766z"></path>
                        <path fill="#2A3542" d="M368.855 376.15a5.295 5.295 0 015.292 5.306 5.293 5.293 0 01-10.584 0 5.297 5.297 0 015.292-5.306zm-2.792 8.169a3.918 3.918 0 005.599 0 4.08 4.08 0 000-5.74 3.917 3.917 0 00-5.599 0 4.062 4.062 0 000 5.74zm3.727-2.395l1.464 2.676h-1.8l-1.135-2.246-.274 2.246h-1.533l.788-6.397h1.923c1.574 0 2.399.935 1.938 2.395-.205.681-.687 1.127-1.371 1.326zm-1.084-.962c.539 0 .831-.221.944-.583.189-.534-.035-.864-.659-.864h-.321l-.177 1.447h.213z"></path>
                    </svg></div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h1 class="mt-5">REDAXO Loader</h1>
                    <p class="lead">Lade die gewünschte Version des <a href="https://www.redaxo.org" target="_blank">REDAXO CMS</a> von GitHub herunter,<br>entpacke sie automatisch auf deinem Server und beginne sofort mit der Installation.</p>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 offset-md-3 my-4">
                    <?php
                    $folder = "./redaxo";

    if (!is_dir($folder) && count($required) <= 0) {
        ?>
                        <form id="loader-form">
                            <?php
                            echo '<select id="version-select" class="form-control" required>';
        echo '<option selected disabled>Bitte REDAXO-Version w&auml;hlen</option>';
        if ($releases) {
            foreach ($releases as $release) {
                echo '<option value="' . $release->assets[0]->browser_download_url . '" data-github-id="' . $release->id . '">' . $release->name . '</option>';
            }
        }
        echo '</select>'; ?>
                            <button type="submit" class="my-3 btn btn-primary disabled" id="start-loader" disabled>REDAXO herunterladen und entpacken</button>
                        </form>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div id="info"></div>
                </div>
            </div>


        <?php
                        if ($releases) {
                            foreach ($releases as $release) {
                                echo '<div class="row rex-version-list" id="v-' . $release->id . '">';
                                echo '<div class="col-12 my-3">';
                                echo '<div class="card">';

                                echo '<div class="card-header">';
                                echo '<h2>' . $release->name . '</h2>';
                                echo '</div>';

                                echo '<div class="card-body">';
                                echo '<p>Veröffentlicht: ' . date('d.m.Y', strtotime($release->published_at)) . '</p>';
                                echo '<p>Auf GitHub ansehen: <a href="' . $release->html_url . '" target="_blank">' . $release->html_url . '</a></p>';
                                #echo strip_tags($release->body);
                                echo '</div>';

                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                        } else {
                            echo '<div class="row">';
                            echo '<div class="col-12 my-3">';
                            echo '<div class="alert alert-warning">REDAXO-Versionen auf GitHub können nicht gelesen werden.</div>';
                            echo '</div>';
                            echo '</div>';
                        }
    } elseif (count($required) > 0) {
        echo '<div class="row">';
        echo '<div class="col-12 my-3">';
        echo '<div class="alert alert-warning">';
        echo '<h2>Fehler: Voraussetzungen nicht erfüllt</h2>';
        echo '<ul><li>' . implode('</li><li>', $required) . '</li></ul>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<div class="row">';
        echo '<div class="col-12 my-3">';
        echo '<div class="alert alert-warning">Es existiert bereits ein Ordner <code>/redaxo</code>.</div>';
        echo '</div>';
        echo '</div>';
    } ?>


        </div>

        <!-- Bootstrap core JavaScript -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
        <script>
            $(document).ready(function() {

                $('#version-select').on('change', function() {
                    $('#start-loader').removeClass('disabled');
                    $('#start-loader').removeAttr('disabled');
                    $('.rex-version-list').hide();
                    var url = this.value;
                    var gitId = $('#version-select').find(':selected').data('github-id');
                    //console.log(gitId);
                    $('#v-' + gitId).show();
                });

                function unzip() {
                    $.ajax({
                        url: '<?=$loader_name?>?func=unzip',
                        error: function() {
                            $('#info').html('<div class="alert alert-danger">Ein Fehler beim Entpacken ist aufgetreten.</div>');
                        },
                        beforeSend: function() {
                            $('#info').html('<div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div></div>');
                        },
                        dataType: 'html',
                        success: function(data) {
                            $('#info').html(data);
                        },
                        type: 'GET'
                    });
                }
                /* Form submit loader */
                $('#loader-form').on('submit', function(e) {
                    var form = $("#loader-form");
                    var downloadUrl = $('#version-select').find(':selected').val()
                    e.preventDefault(); // prevent native submit
                    $.ajax({
                        url: '<?=$loader_name?>?func=download&url=' + downloadUrl,
                        error: function() {
                            $('#info').html('<div class="alert alert-danger">Ein Fehler ist aufgetreten.</div>');
                        },
                        beforeSend: function() {
                            $('#info').html('<div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div></div>');
                        },
                        dataType: 'html',
                        success: function(data) {
                            $('#info').html(data);
                            alert("REDAXO wurde heruntergeladen und wird jetzt entpackt.");
                            unzip();
                        },
                        type: 'GET'
                    });
                });

            });
        </script>

    </body>

    </html>
<?php
}
?>
