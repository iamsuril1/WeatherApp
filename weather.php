<?php
date_default_timezone_set('Asia/Kathmandu');
$serverName = "localhost";
$userName = "root";
$password = "";
$conn = mysqli_connect($serverName, $userName, $password);

if (!$conn) {
    echo "Failed to connect" . mysqli_connect_error();
    exit;
}

$createDatabase = "CREATE DATABASE IF NOT EXISTS if0_36583840_prototype";
if (!mysqli_query($conn, $createDatabase)) {
    echo "Failed to create database: " . mysqli_connect_error();
    exit;
}

mysqli_select_db($conn, 'if0_36583840_prototype');

$createTable = "CREATE TABLE IF NOT EXISTS weather_data (
    city VARCHAR(255) NOT NULL,
    temp FLOAT NOT NULL,
    humidity FLOAT NOT NULL,
    wind FLOAT NOT NULL,
    wind_direction FLOAT NOT NULL,
    pressure FLOAT NOT NULL,
    weather_condition VARCHAR(255) NOT NULL,
    weather_icon VARCHAR(255) NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP
)";
if (!mysqli_query($conn, $createTable)) {
    echo "Failed to create table";
    exit;
}

$cityName = isset($_GET['q']) ? $_GET['q'] : "Luton";

$selectAllData = "SELECT * FROM weather_data WHERE LOWER(city) = LOWER('{$cityName}')";
$result = mysqli_query($conn, $selectAllData);
$current_time = time();

if (mysqli_num_rows($result) == 0) {
    $url = "https://api.openweathermap.org/data/2.5/weather?units=metric&q={$cityName}&appid=9ae92a0734944b7ea45822aad48cbf55";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    $temp = $data['main']['temp'];
    $humidity = $data['main']['humidity'];
    $wind = $data['wind']['speed'];
    $wind_direction = $data['wind']['deg'];
    $pressure = $data['main']['pressure'];
    $weather_condition = $data['weather'][0]['description'];
    $weather_icon = $data['weather'][0]['icon'];

    $insertData = "INSERT INTO weather_data (city, temp, humidity, wind, wind_direction, pressure, weather_condition, weather_icon)
                   VALUES ('$cityName', '$temp', '$humidity', '$wind', '$wind_direction', '$pressure', '$weather_condition', '$weather_icon')";

    if (!mysqli_query($conn, $insertData)) {
        echo "Failed to insert data: " . mysqli_error($conn);
        exit;
    }
} else {
    $row = mysqli_fetch_assoc($result);
    $last_fetched = strtotime($row["created_at"]);
    $difference = $current_time - $last_fetched;

    if ($difference > 7200) {
        $deleteRow = "DELETE FROM weather_data WHERE city = '$cityName'";
        if (mysqli_query($conn, $deleteRow)) {
            $url = "https://api.openweathermap.org/data/2.5/weather?units=metric&q={$cityName}&appid=0368d55838887e1faa66be995a04b33e";
            $response = file_get_contents($url);
            $data = json_decode($response, true);
            $temp = $data['main']['temp'];
            $humidity = $data['main']['humidity'];
            $wind = $data['wind']['speed'];
            $wind_direction = $data['wind']['deg'];
            $pressure = $data['main']['pressure'];
            $weather_condition = $data['weather'][0]['description'];
            $weather_icon = $data['weather'][0]['icon'];

            $insertData = "INSERT INTO weather_data (city, temp, humidity, wind, wind_direction, pressure, weather_condition, weather_icon)
                           VALUES ('$cityName', '$temp', '$humidity', '$wind', '$wind_direction', '$pressure', '$weather_condition', '$weather_icon')";

            if (!mysqli_query($conn, $insertData)) {
                echo "Failed to insert data: " . mysqli_error($conn);
                exit;
            }
        }
    }
}

$result = mysqli_query($conn, $selectAllData);
$rows = array();
while ($row = mysqli_fetch_assoc($result)) {
    $row['temperature'] = $row['temp']; 
    $row['icon_code'] = $row['weather_icon']; 
    $row['weather_description'] = $row['weather_condition']; 
    unset($row['temp'], $row['weather_icon'], $row['weather_condition']); 
    $rows[] = $row;
}

$json_data = json_encode($rows);
header('Content-Type: application/json');
echo $json_data;

?>
