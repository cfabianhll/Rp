<?php
$servername = "localhost";
$username = "trootech";
$password = "precio_red@987321";
$dbname = "redprecio";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$file = fopen("city_imp.csv","r");
while(! feof($file))
{
    $data = fgetcsv($file);
    $city_id = '"' .  $data[0] .'"';
    $township_name ='"' .  $data[1] .'"';
    $city_name ='"' .  $data[2] .'"';
    $country_name ='"' .  $data[3] .'"';

     $sql = "INSERT INTO  township_city_combination (city_id, township_name, city_name, country_name)
VALUES ($city_id,$township_name,$city_name,$country_name)";


    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
        die();
    }

}
echo "completed";

fclose($file);
die();
?>

