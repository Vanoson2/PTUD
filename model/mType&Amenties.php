<?php 
include_once("mConnect.php");
class mType{
    public function mGetAllTypes(){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $strSelect = "SELECT * FROM place_type";
            $result = $conn->query($strSelect);
            return $result;
        }else{
            return false;
        }
    }
    public function mGetAllAmenities(){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $strSelect = "SELECT * FROM amenity";
            $result = $conn->query($strSelect);
            return $result;
        }else{
            return false;
        }
    }
}
?>