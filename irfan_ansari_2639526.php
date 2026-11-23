<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$serverName = "localhost";
$userName = "root";
$password = "";

$conn = mysqli_connect($serverName, $userName, $password);
mysqli_select_db($conn, $dbName);

// Table structure
$createTable = "CREATE TABLE IF NOT EXISTS weather_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(100),
    temperature FLOAT,
    weather_main VARCHAR(50),
    weather_description VARCHAR(100),
    weather_icon VARCHAR(10),
    humidity INT,
    pressure INT,
    wind_speed FLOAT,
    wind_deg FLOAT,
    weather_date DATE,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_city_date (city, weather_date)
)";
mysqli_query($conn, $createTable);

$cityName = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : "Demopolis";
$todayDate = date('Y-m-d');

// Check for fresh data (2 hours)
$select = "SELECT * FROM weather_data WHERE city LIKE '%$cityName%' AND weather_date='$todayDate'";
$result = mysqli_query($conn, $select);
$weatherData = null;
$needsUpdate = true;

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    if ((time() - strtotime($row['last_updated'])) < 7200) {
        $needsUpdate = false;
        $weatherData = $row;
    }
}

if ($needsUpdate) {
    $apiKey = "6b24d3605a4c215d0b777ad3ef2feb69";
    $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($cityName) . "&units=metric&appid=" . $apiKey;
   
    $response = @file_get_contents($url);
    if ($response) {
        $data = json_decode($response, true);
       
        $temp = $data['main']['temp'];
        $main = $data['weather'][0]['main'];
        $desc = $data['weather'][0]['description'];
        $icon = $data['weather'][0]['icon'];
        $hum = $data['main']['humidity'];
        $pres = $data['main']['pressure'];
        $wspeed = $data['wind']['speed'];
        $wdeg = $data['wind']['deg'];

        $insert = "INSERT INTO weather_data
            (city, temperature, weather_main, weather_description, weather_icon, humidity, pressure, wind_speed, wind_deg, weather_date)
            VALUES ('$cityName', $temp, '$main', '$desc', '$icon', $hum, $pres, $wspeed, $wdeg, '$todayDate')
            ON DUPLICATE KEY UPDATE temperature=$temp, weather_main='$main', last_updated=CURRENT_TIMESTAMP";
        mysqli_query($conn, $insert);

        $weatherData = [
            "city" => $cityName,
            "temperature" => $temp,
            "weather_main" => $main,
            "weather_description" => $desc,
            "weather_icon" => $icon,
            "humidity" => $hum,
            "pressure" => $pres,
            "wind_speed" => $wspeed,
            "wind_deg" => $wdeg,
            "date" => date("d F Y")
        ];
    }
} else {
    // Format existing data for JSON output
    $weatherData = [
        "city" => $weatherData['city'],
        "temperature" => $weatherData['temperature'],
        "weather_main" => $weatherData['weather_main'],
        "weather_description" => $weatherData['weather_description'],
        "weather_icon" => $weatherData['weather_icon'],
        "humidity" => $weatherData['humidity'],
        "pressure" => $weatherData['pressure'],
        "wind_speed" => $weatherData['wind_speed'],
        "wind_deg" => $weatherData['wind_deg'],
        "date" => date("d F Y", strtotime($weatherData['weather_date']))
    ];
}

// Wraping in "current" key
echo json_encode(["current" => $weatherData]);
mysqli_close($conn);
?>
