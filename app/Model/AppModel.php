<?php 

    namespace app\Model;

    Class AppModel 
    {
        public function cleanse($dirty) {
            
            return trim(\htmlspecialchars($dirty));
        }
    }
