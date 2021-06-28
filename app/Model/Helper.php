<?php 

    namespace app\Model;

    Class Helper extends AppModel 
    {
        private $filename;
        private $data;

        public function findData() {

            $this->data = json_decode(file_get_contents($this->filename));
        }

        public function setFilename($filename) {
            $this->filename = $filename;
        }

        public function getFilename() {

            return $this->filename;
        }

        public function getData() {

            $this->findData();
            
            return $this->data;
        }
    }
