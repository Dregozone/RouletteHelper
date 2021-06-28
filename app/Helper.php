<?php 

    namespace app;

    $model = new Model\Helper($page);
    $controller = new Controller\Helper($model);
    $view = new View\Helper($model, $controller);

    $filename = "data/history.json";
    $model->setFilename($filename);
    $model->findData();

    if ( !file_exists($filename) || isset($_GET["reset"]) ) {
        $controller->resetData($filename);
    }

    // Write to JSON history
    if ( isset($_GET["write"]) ) {
        $controller->addNumber( $model->cleanse($_GET["write"]) );
    }

    echo $view->title("Roulette Helper");
    echo $view->uiForm();

    // Recommendations
    echo $view->nextBetRecommendations();

    // Look at the x most recently recorded rolls
    echo $view->mostRecent(10);
