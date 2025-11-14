<?php
$password = 'securepassword123'; // IMPORTANT: Use the password you want to log in with
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo "<h1>The BCRYPT Hash for '$password' is:</h1>";
echo "<h2>" . $hashed_password . "</h2>";
echo "<p>Copy and paste this hash into your schema.sql file.</p>";
?>
