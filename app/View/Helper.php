<?php 

    namespace app\View;

    Class Helper 
    {
        private $model;
        private $controller;

        public function __construct($model, $controller) {
            $this->model = $model;
            $this->controller = $controller;
        }

        public function title($title) {

            $html = '
                <h1 style="text-align: center;">
                    ' . $title . '
                </h1>
            ';

            return $html;
        }

        public function uiForm() {

            $html = '
                <div style="display: flex;">
                    <div style="width: 30%;">
                        <label for="number" style="width: 48%; padding-right: 1%; text-align: right;">Number:</label>
                        <input class="form-control" style="display: inline-block; width: 50%;" type="number" min="1" max="36" name="number" id="number" />
                    </div>

                    <div style="width: 10%;">
                        <div class="btn btn-info" onclick="recordNumber()">
                            Record
                        </div>
                    </div>

                    <div style="width: 10%;">
                        <a href="?write=0">
                            <div class="btn btn-success">
                                0
                            </div>
                        </a>

                        <a href="?write=00">
                            <div class="btn btn-success">
                                00
                            </div>
                        </a>
                    </div>

                    <div style="width: 10%;">
                        <div class="btn btn-danger" onclick="resetData()">
                            Reset
                        </div>
                    </div>
                </div>
            ';

            return $html;
        }

        public function mostRecent($limit) {
            
            $data = array_reverse($this->model->getData());

            $html = '
                <div>
                    <table class="table table-sm table-hover table-striped" style="font-size: 80%; text-align: center;">
                        <thead>
                            <tr>
                                <th style="width: 25%;">Num.</th>
                                <th style="width: 25%;"> Red / Black </th>
                                <th style="width: 25%;"> High / Low </th>
                                <th style="width: 25%;"> Even / Odd </th>
                            </tr>
                        </thead>
                        <tbody>
            ';

            $curNum = 0;
            foreach ( $data as $number ) {

                if ( $curNum < $limit ) {
                    $html .= '
                        <tr>
                            <td>' . $number . '</td>
                            <td>' . $this->model->findRedBlack($number) . '</td>
                            <td>' . $this->model->findHighLow($number) . '</td>
                            <td>' . $this->model->findEvenOdd($number) . '</td>
                        </tr>
                    ';
                }

                $curNum++;
            }

            $html .= '
                    </tbody>
                </table>
            ';

            return $html;
        }

        public function nextBetRecommendations() {

            $html = '
                <div style="margin: 1.75% 0;">
                    <table class="table table-sm" style="font-size: 90%; text-align: center;">
                        <thead>
                            <tr>
                                <th>Red / Black</th>
                                <th>High / Low</th>
                                <th>Even / Odd</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="font-size: 140%;">
                                <td>' . $this->model->redBlackRecommendation() . '</td>
                                <td>' . $this->model->highLowRecommendation() . '</td>
                                <td>' . $this->model->evenOddRecommendation() . '</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            ';

            return $html;
        }
    }
