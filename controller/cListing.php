<?php 
include_once(__DIR__ . "/../model/listing/mListing.php");
 class cListing{
    public function cCountListingByProvince($provinceName){
        $mListing = new mListing();
        return $mListing->mCountListingByProvince($provinceName);
    }   
 }
?>