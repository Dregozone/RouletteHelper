<?php 

    namespace app\Controller; 
    
    Class Helper 
    {
        private $model;

        public function __construct($model) {
            $this->model = $model;
        }

        public function addNumber( $number ) {

            $data = $this->model->getData();
            $data[] = $number;

            file_put_contents($this->model->getFilename(), json_encode($data));

            header("location: ?p=Helper");
        }

        public function resetData() {

            unlink($this->model->getFilename()); // Delete any historical dataset

            // Prepare initial data
            $initial = [];

            // Create a fresh dataset
            $fp = fopen($this->model->getFilename(), 'w');
            fwrite($fp, json_encode($initial));
            fclose($fp);

            header("location: ?p=Helper");
        }
    }
