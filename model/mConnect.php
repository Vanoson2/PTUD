<?php 
    class mConnect{
        public function mMoKetNoi(){
            $host="localhost";
            $name="admin";
            $pass="124";
            $db="we_go";
            return mysqli_connect($host,$name,$pass,$db);
        }
        public function mDongKetNoi($conn){
            $conn->close();
        }
    }
?>