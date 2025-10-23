<?php 
    class mConnect{
        public function mMoKetNoi(){
            $host="localhost";
            $name="huytrong";
            $pass="huytrong1310";
            $db="we_go";
            return mysqli_connect($host,$name,$pass,$db);
        }
        public function mDongKetNoi($conn){
            $conn->close();
        }
    }
?>