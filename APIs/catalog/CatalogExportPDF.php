<?php
/***
 * @author: rlange@mapco.de
 * Catalog API
 *
 *	PDF Export - Catalog
 *
 *******************************************************************************/

DEFINE('TCPDF_VERSION', 		'tcpdf_6_0_088');

// Include the main TCPDF library (search for installation path).
$realPath = realpath('../vendor/'. TCPDF_VERSION . '/tcpdf.php');
require_once($realPath);

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Mapco');
$pdf->SetTitle('Catalog Title');
$pdf->SetSubject('Vehicles Data for a special category');
$pdf->SetKeywords('Category ec');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 061', PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set font
$pdf->SetFont('helvetica', '', 8);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

$data = array();
$data['from'] = 'cms_catalog';
$data['select'] = '*';
$data['where'] = "
	id_catalog = " . $get['catalogNumber'];
$cmsCatalog = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 1, 'web',  __FILE__, __LINE__);

$catalogCacheFolder = '../assets/CmsCatalog/';
$fileContent = file_get_contents($catalogCacheFolder . $cmsCatalog['filename']);

$pdf->AddPage();
$pdf->writeHTML($fileContent, true, false, true, false, '');
$pdf->lastPage();

//	close and output PDF document
$pdf->Output($catalogCacheFolder . $cmsCatalog['filename'] . '.pdf', 'I'); //I, D, F, FI, E
