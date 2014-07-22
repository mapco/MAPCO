<?php

//  error reporting
error_reporting(e_all & ~e_notice);
ini_set('display_errors', on);

include('app/app.config.php');
include('mapco_shop_de/index.php');
