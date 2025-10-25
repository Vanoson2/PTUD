<?php 
include_once(__DIR__ . "/../model/mListing.php");
 class cListing{
    public function cCountListingByProvince($provinceName){
        $mListing = new mListing();
        return $mListing->mCountListingByProvince($provinceName);
    }
    
    public function cSearchListingsByLocation($location){
        $mListing = new mListing();
        return $mListing->mSearchListingsByLocation($location);
    }
    
    public function cSearchListingsWithFilters($location, $checkin = null, $checkout = null, $guests = 1){
        $mListing = new mListing();
        return $mListing->mSearchListingsWithFilters($location, $checkin, $checkout, $guests);
    }
    
    public function cGetListingAmenities($listingId){
        $mListing = new mListing();
        return $mListing->mGetListingAmenities($listingId);
    }
 }
?>