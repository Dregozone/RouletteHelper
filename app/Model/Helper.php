<?php 

    namespace app\Model;

    Class Helper extends AppModel 
    {
        private $filename;
        private $data;
        private $reds = [];
        private $startBet;
        private $maxToGoBack;

        private $redsInRow = 0;
        private $blacksInRow = 0;

        private $highsInRow = 0;
        private $lowsInRow = 0;

        private $oddsInRow = 0;
        private $evensInRow = 0;

        public function __construct() {

            $this->startBet = 1; // Starting amount to bet, this will then be increased each failure
            $this->maxToGoBack = 10; // Look back up to 10 results when calculating prediction

            $this->reds = [
                1, 
                3, 
                5, 
                7,
                9, 
                12, 
                14,
                16,
                18,
                19,
                21,
                23,
                25,
                27,
                30,
                32,
                34,
                36
            ];
        }

        public function findData() {

            $this->data = json_decode(file_get_contents($this->filename));
        }

        private function findBet($number) {
            
            // Default to waiting for 2 in a row
            //$number = $number -1; // Wait for 3 in a row before acting

            switch ($number) {
                case 1: 
                    return 1;

                case 2:
                    return 2;

                case 3:
                    return 4;

                case 4:
                    return 8;

                case 5:
                    return 16;

                case 6:
                    return 32;

                case 7:
                    return 64;

                default: 
                    return 'Bad situation';
            };
        }

        public function redBlackRecommendation() {
            
            $data = array_reverse( $this->data );

            $numberToGoBack = (sizeof($data)-1) >= $this->maxToGoBack ? $this->maxToGoBack : (sizeof($data)-1);
            for ( $i = $numberToGoBack, $j = 0; $i >= $j; $i-- ) { // Loop backwards through most recent 10 numbers
                
                if ( $i != $numberToGoBack ) { // This is not the first one to be looked at

                    if ( $this->isRed($data[$i]) && $this->isRed($data[($i+1)]) ) {
                        $this->redsInRow++;
                    } else if ( $this->isBlack($data[$i]) && $this->isBlack($data[($i+1)]) ) {
                        $this->blacksInRow++;
                    } else {
                        $this->redsInRow = 0;
                        $this->blacksInRow = 0;
                    }
                }
            }
            
            if ( $this->redsInRow > 0 ) {

                $bet = $this->findBet( $this->redsInRow );

                return "({$this->redsInRow}) £{$bet} Black";
            } else if ( $this->blacksInRow > 0 ) {

                $bet = $this->findBet( $this->blacksInRow );

                return "({$this->blacksInRow}) £{$bet} Red";
            } else {

                return "Skip";
            }
        }
        
        public function highLowRecommendation() {
            
            $data = array_reverse( $this->data );

            $numberToGoBack = (sizeof($data)-1) >= $this->maxToGoBack ? $this->maxToGoBack : (sizeof($data)-1);
            for ( $i = $numberToGoBack, $j = 0; $i >= $j; $i-- ) { // Loop backwards through most recent 10 numbers
                
                if ( $i != $numberToGoBack ) { // This is not the first one to be looked at

                    if ( $this->isHigh($data[$i]) && $this->isHigh($data[($i+1)]) ) {
                        $this->highsInRow++;
                    } else if ( $this->isLow($data[$i]) && $this->isLow($data[($i+1)]) ) {
                        $this->lowsInRow++;
                    } else {
                        $this->highsInRow = 0;
                        $this->lowsInRow = 0;
                    }
                }
            }

            if ( $this->lowsInRow > 0 ) {

                $bet = $this->findBet( $this->lowsInRow );

                return "({$this->lowsInRow}) £{$bet} High";
            } else if ( $this->highsInRow > 0 ) {

                $bet = $this->findBet( $this->highsInRow );

                return "({$this->highsInRow}) £{$bet} Low";
            } else {

                return "Skip";
            }
        }
        
        public function evenOddRecommendation() {
            
            $data = array_reverse( $this->data );

            $numberToGoBack = (sizeof($data)-1) >= $this->maxToGoBack ? $this->maxToGoBack : (sizeof($data)-1);
            for ( $i = $numberToGoBack, $j = 0; $i >= $j; $i-- ) { // Loop backwards through most recent 10 numbers
                
                if ( $i != $numberToGoBack ) { // This is not the first one to be looked at

                    if ( $this->isOdd($data[$i]) && $this->isOdd($data[($i+1)]) ) {
                        $this->oddsInRow++;
                    } else if ( $this->isEven($data[$i]) && $this->isEven($data[($i+1)]) ) {
                        $this->evensInRow++;
                    } else {
                        $this->oddsInRow = 0;
                        $this->evensInRow = 0;
                    }
                }
            }

            if ( $this->oddsInRow > 0 ) {

                $bet = $this->findBet( $this->oddsInRow );

                return "({$this->oddsInRow}) £{$bet} Even";
            } else if ( $this->evensInRow > 0 ) {

                $bet = $this->findBet( $this->evensInRow );

                return "({$this->evensInRow}) £{$bet} Odd";
            } else {

                return "Skip";
            }
        }

        public function findRedBlack($number) {

            if ( $number == "0" || $number == "00" ) {

                return '';
            }

            return in_array($number, $this->reds) ? "Red" : "Black";
        }
        
        public function findHighLow($number) {
            
            if ( $number == "0" || $number == "00" ) {

                return '';
            }

            return $number < 19 ? "Low" : "High";
        }
        
        public function findEvenOdd($number) {
            
            if ( $number == "0" || $number == "00" ) {

                return '';
            }

            return ($number % 2) == 0 ? "Even" : "Odd";
        }

        public function isRed($number) {

            return $this->findRedBlack($number) == "Red";
        }

        public function isBlack($number) {

            return $this->findRedBlack($number) == "Black";
        }

        public function isHigh($number) {

            return $this->findHighLow($number) == "High";
        }

        public function isLow($number) {

            return $this->findHighLow($number) == "Low";
        }

        public function isOdd($number) {

            return $this->findEvenOdd($number) == "Odd";
        }

        public function isEven($number) {

            return $this->findEvenOdd($number) == "Even";
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
