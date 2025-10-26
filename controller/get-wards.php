<?php
include_once __DIR__ . '/../model/mListing.php';

header('Content-Type: application/json');

$provinceCode = $_GET['province_code'] ?? '';

if (empty($provinceCode)) {
  echo json_encode([]);
  exit;
}

$mListing = new mListing();
$wards = $mListing->mGetWardsByProvince($provinceCode);

echo json_encode($wards);
?>
