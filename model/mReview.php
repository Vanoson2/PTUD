<?php
include_once(__DIR__ . '/mConnect.php');

class mReview {
    
    // Tạo review mới
    public function mCreateReview($listingId, $userId, $rating, $comment, $imgRating = null){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $listingId = intval($listingId);
            $userId = intval($userId);
            $rating = intval($rating);
            $comment = $conn->real_escape_string($comment);
            $imgRating = $imgRating ? "'" . $conn->real_escape_string($imgRating) . "'" : "NULL";
            
            $strInsert = "INSERT INTO review 
                         (listing_id, user_id, rating, comment, imgRating)
                         VALUES 
                         ($listingId, $userId, $rating, '$comment', $imgRating)";
            
            return $conn->query($strInsert);
        }else{
            return false;
        }
    }
    
    // Kiểm tra user đã review listing này chưa
    public function mCheckUserReviewed($listingId, $userId){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $listingId = intval($listingId);
            $userId = intval($userId);
            
            $strSelect = "SELECT review_id 
                         FROM review 
                         WHERE listing_id = $listingId 
                         AND user_id = $userId 
                         LIMIT 1";
            
            $result = $conn->query($strSelect);
            return $result && $result->num_rows > 0;
        }else{
            return false;
        }
    }
    
    // Lấy reviews của một listing
    public function mGetListingReviews($listingId, $limit = 10, $offset = 0){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $listingId = intval($listingId);
            $limit = intval($limit);
            $offset = intval($offset);
            
            $strSelect = "SELECT r.*, u.full_name, u.avatar
                         FROM review r
                         INNER JOIN user u ON r.user_id = u.user_id
                         WHERE r.listing_id = $listingId
                         ORDER BY r.created_at DESC
                         LIMIT $limit OFFSET $offset";
            
            $result = $conn->query($strSelect);
            return $result;
        }else{
            return false;
        }
    }
}
?>
