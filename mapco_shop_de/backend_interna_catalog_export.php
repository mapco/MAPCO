<?php

// Header
include("config.php");
include('functions/cms_core.php');

$get['catalogNumber'] = 1;
include("../APIs/catalog/CatalogExportPDF.php");