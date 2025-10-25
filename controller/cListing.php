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
    
    public function cGetListingDetail($listingId){
        $mListing = new mListing();
        return $mListing->mGetListingDetail($listingId);
    }
    
    public function cGetListingImages($listingId){
        $mListing = new mListing();
        return $mListing->mGetListingImages($listingId);
    }
    
    public function cGetListingReviews($listingId, $limit = 10){
        $mListing = new mListing();
        return $mListing->mGetListingReviews($listingId, $limit);
    }
    
    public function cGetBookedDates($listingId){
        $mListing = new mListing();
        return $mListing->mGetBookedDates($listingId);
    }
 }
?>