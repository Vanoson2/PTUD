<?php
include_once(__DIR__ . "/../model/mType&Amenties.php"); 
class cTypeAndAmenties{
    public function cGetAllTypes(){
        
        $mType = new mType();
        $tbl=$mType->mGetAllTypes();
        if(!$tbl){
            return false;
        }else{
            return $tbl;
        }
    }
    public function cGetAllAmenities(){
        
        $mType = new mType();
        $tbl=$mType->mGetAllAmenities();
        if(!$tbl){
            return false;
        }else{
            return $tbl;
        }
    }
}
?>