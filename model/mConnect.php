<?php 
    class mConnect{
        public function mMoKetNoi(){
            $host="localhost";
            $name="admin";        // XAMPP default username
            $pass="124";            // XAMPP default password (empty)
            $db="we_go";
            return mysqli_connect($host,$name,$pass,$db);
        }
        public function mDongKetNoi($conn){
            $conn->close();
        }
    }
?>