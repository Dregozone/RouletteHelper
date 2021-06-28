<?php 

    $page = $_GET["p"] ?? "Helper";

    require_once("app\Model\AppModel.php");
    require_once("app\Model\\" . $page . ".php");
    require_once("app\Controller\\" . $page . ".php");
    require_once("app\View\\" . $page . ".php");

    echo '
        <!DOCTYPE html>
        <html lang="en">
            <head>
                <title>' . $page . ' - Roulette Helper</title>

                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css" />
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" />
                <link rel="stylesheet" href="public/css/Shared.css" />
                <link rel="stylesheet" href="public/css/' . $page . '.css" />

                <script src="public/js/Shared.js"></script>
                <script src="public/js/' . $page . '.js"></script>
            </head>
            <body>

                <main class="container">
    ';

    include "app/{$page}.php";

    echo '
                </main>

            </body>
        </html>    
    ';
