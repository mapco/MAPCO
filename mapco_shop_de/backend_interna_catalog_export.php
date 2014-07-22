<?php

// Header
include("config.php");
include('functions/cms_core.php');

$get['catalogNumber'] = $_GET['catalogNumber'];
include("../APIs/catalog/CatalogExportPDF.php");