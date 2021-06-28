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
                <h1>
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
    }
