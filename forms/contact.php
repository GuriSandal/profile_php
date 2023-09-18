<?php
  $servername = "127.0.0.1";
  $username = "u143594098_profile_admin";
  $password = "Profile@Pass@1998";
  $dbname = "u143594098_profile";
  
  // Create a connection
  $conn = mysqli_connect($servername, $username, $password, $dbname);
  
  // Check the connection
  if (!$conn) {
      die("Connection failed: " . mysqli_connect_error());
  }
  $name =  $_POST['name'];
  $subject = $_POST['subject'];
  $message = $_POST['message'];
  $email = $_POST['email'];
  
  $sql = "INSERT INTO contact VALUES('$name', '$email', '$subject', '$message')";

  if (mysqli_query($conn, $sql)) {
      echo "OK";
  } else {
      echo "Error updating record: " . mysqli_error($conn);
  }

  mysqli_close($conn);

  

?>
