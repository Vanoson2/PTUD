<?php
include_once(__DIR__ . '/mConnect.php');

class mReview {
    
    // Tạo review mới
    public function mCreateReview($listingId, $userId, $rating, $comment){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn){
            $listingId = intval($listingId);
            $userId = intval($userId);
            $rating = intval($rating);
            $comment = $conn->real_escape_string($comment);
            
            $strInsert = "INSERT INTO review 
                         (listing_id, user_id, rating, comment)
                         VALUES 
                         ($listingId, $userId, $rating, '$comment')";
            
            $result = $conn->query($strInsert);
            
            if (!$result) {
                return false;
            }
            
            // Return the new review_id
            return $conn->insert_id;
        }else{
            return false;
        }
    }
    
    // Tạo ảnh review
    public function mCreateReviewImages($reviewId, $imageUrls){
        $p = new mConnect();
        $conn = $p->mMoKetNoi();
        if($conn && !empty($imageUrls)){
            $reviewId = intval($reviewId);
            $values = [];
            
            foreach($imageUrls as $index => $url){
                $url = $conn->real_escape_string($url);
                $sortOrder = intval($index);
                $values[] = "($reviewId, '$url', $sortOrder)";
            }
            
            if(empty($values)){
                return true; // No images to insert
            }
            
            $strInsert = "INSERT INTO review_image 
                         (review_id, file_url, sort_order)
                         VALUES " . implode(', ', $values);
            
            $result = $conn->query($strInsert);
            return $result;
        }
        return true; // No images or no connection
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
