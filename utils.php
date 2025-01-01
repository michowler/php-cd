<?php
require_once 'config.php';
// Validate input address to prevent empty or malicious values
function validateInput($input) {
    return !empty($input) && preg_match("/^[a-zA-Z0-9\s,.-]+$/", $input);
}

// Fetch geocode data using Google Maps API
function fetchGeocodeData($address, $apiKey) {
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=$apiKey";
    $response = file_get_contents($url);
    if ($response === false) {
        throw new Exception("Failed to fetch geocode data.");
    }
    return json_decode($response, true);
}

// Fetch weather data using OpenWeather API
function fetchWeatherData($latitude, $longitude, $apiKey) {
    $url = "https://api.openweathermap.org/data/2.5/weather?lat=$latitude&lon=$longitude&units=metric&appid=$apiKey";
    $response = file_get_contents($url);
    if ($response === false) {
        throw new Exception("Failed to fetch weather data.");
    }
    return json_decode($response, true);
}

// Get temperature from weather data
function getTemperature($weatherData) {
    return $weatherData['main']['temp'] ?? "N/A";
}

// Fetch nearby places using Google Places API
function fetchNearbyPlaces($latitude, $longitude, $apiKey, $type = 'restaurant', $radius = 1500) {
    $url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=$latitude,$longitude&radius=$radius&type=$type&key=$apiKey";
    $response = file_get_contents($url);
    if ($response === false) {
        throw new Exception("Failed to fetch nearby places.");
    }
    $data = json_decode($response, true);
    $places = [];
    if (isset($data['results'])) {
        foreach ($data['results'] as $result) {
            $places[] = $result['name'];
        }
    }
    return $places;
}
?>
