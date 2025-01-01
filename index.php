<?php
// Include external files for configuration and utility functions
$config = require 'config.php'; // Import config array
require_once 'utils.php';

// Extract configuration values
$GOOGLE_API_KEY = $config['GOOGLE_API_KEY'];
$OPENWEATHER_API_KEY = $config['OPENWEATHER_API_KEY'];

// Initialize variables
$address = '';
$formattedAddress = '';
$latitude = null;
$longitude = null;
$temperature_today = "N/A";
$weatherDesc = "";
$nearbyPlaces = [];
$errorMessage = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Trim and encode address input
    $address = trim($_POST["address"]);

    if (!validateInput($address)) {
        $errorMessage = "Invalid input. Please enter a valid address.";
    } else {
        try {
            // Geocoding API
            $geoData = fetchGeocodeData($address, $GOOGLE_API_KEY);

            if ($geoData["status"] === "OK") {
                $formattedAddress = $geoData["results"][0]["formatted_address"];
                $location = $geoData["results"][0]["geometry"]["location"];
                $latitude = $location["lat"];
                $longitude = $location["lng"];

                // Weather API
                $weatherData = fetchWeatherData($latitude, $longitude, $OPENWEATHER_API_KEY);
                if ($weatherData) {
                    $temperature_today = getTemperature($weatherData);
                    $weatherDesc = $weatherData["weather"][0]["description"] ?? "Unavailable";
                }

                // Nearby Places API
                $nearbyPlaces = fetchNearbyPlaces($latitude, $longitude, $GOOGLE_API_KEY);
            } else {
                $errorMessage = "Unable to find the address. Please try again.";
            }
        } catch (Exception $e) {
            $errorMessage = "An error occurred: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nearby Places Recommendation</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Google Maps JavaScript API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $GOOGLE_API_KEY; ?>&libraries=places"></script>
    <style>
        #map {
            height: 400px;
            width: 100%;
            margin-top: 20px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-sm-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center">
                    <h4>Recommend Me Nearby Places</h4>
                </div>
                <div class="card-body">
                    <form id="addressForm" method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="address" class="form-label">Enter Address</label>
                            <input type="text" id="address" name="address" class="form-control" placeholder="e.g. CelcomDigi Tower" required>
                            <div class="invalid-feedback">
                                Please enter an address.
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Submit</button>
                    </form>
                </div>
            </div>

            <?php if ($formattedAddress): ?>
                <div class="card mt-4 shadow-sm">
                    <div class="card-body">
                        <p class='text-success'><strong>Your location:</strong> <?php echo htmlspecialchars($formattedAddress); ?></p>
                        <p><strong>Current Weather:</strong> <?php echo htmlspecialchars($temperature_today); ?>°C, <?php echo htmlspecialchars($weatherDesc); ?></p>
                        <div id="map"></div>
                        <h5 class='mt-4'>Nearby Places:</h5>
                        <ul>
                            <?php foreach ($nearbyPlaces as $place): ?>
                                <li><?php echo htmlspecialchars($place); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <script>
                    function initMap() {
                        var location = { lat: <?php echo $latitude; ?>, lng: <?php echo $longitude; ?> };
                        var map = new google.maps.Map(document.getElementById('map'), {
                            zoom: 15,
                            center: location
                        });
                        var marker = new google.maps.Marker({
                            position: location,
                            map: map
                        });
                    }
                    initMap();
                </script>
            <?php elseif ($errorMessage): ?>
                <div class="card mt-4 shadow-sm">
                    <div class="card-body text-danger">
                        <?php echo htmlspecialchars($errorMessage); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Address Autocomplete Initialisation
    let autocomplete;
    function initAutocomplete() {
        const addressInput = document.getElementById('address');
        autocomplete = new google.maps.places.Autocomplete(addressInput);
    }
    google.maps.event.addDomListener(window, 'load', initAutocomplete);

    // Bootstrap validation script
    (() => {
        'use strict';
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>

</body>
</html>

