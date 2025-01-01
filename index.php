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
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDegaeRf56G-zlD_SHpbl4omD38ZKuufzw&libraries=places"></script>
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
            <?php
            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                // GOOGLE API KEY
                $googleApiKey = "AIzaSyDegaeRf56G-zlD_SHpbl4omD38ZKuufzw";
                // OPEN WEATHER API KEY
                $openWeatherApiKey = "e3cce80cfb2e503b8549feffd23a1f8c";
                $address = urlencode(trim($_POST["address"]));

                // Geocoding API
                $geoUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=$address&key=$googleApiKey";
                $geoResponse = file_get_contents($geoUrl);
                $geoData = json_decode($geoResponse, true);

                echo '<div class="card mt-4 shadow-sm">';
                echo '<div class="card-body">';
                if ($geoData["status"] === "OK") {
                    $formattedAddress = $geoData["results"][0]["formatted_address"];
                    $location = $geoData["results"][0]["geometry"]["location"];
                    $latitude = $location["lat"];
                    $longitude = $location["lng"];

                    echo "<p class='text-success'><strong>Your location:</strong> $formattedAddress</p>";

                    // Weather API
                    $weatherUrl = "https://api.openweathermap.org/data/2.5/weather?lat=$latitude&lon=$longitude&units=metric&appid=$openWeatherApiKey";
                    $weatherResponse = file_get_contents($weatherUrl);
                    $weatherData = json_decode($weatherResponse, true);
                    $temperature_today = "N/A"; // Default value to avoid undefined variable warning

                    // Check Weather Data
                    if ($weatherData){
                        // Check Weather Temperature
                        if (isset($weatherData["main"]["temp"])) {
                            $temperature_today = number_format((float)$weatherData["main"]["temp"], 2, '.', '');
                            
                        } elseif (isset($weatherData["main"]["temp_max"], $weatherData["main"]["temp_min"])) {
                            $temperature_today = number_format((float)(($weatherData["main"]["temp_max"] + $weatherData["main"]["temp_min"]) / 2), 2, '.', '');
                            
                        }
                        // Check Weather Description
                        $weatherDesc = $weatherData["weather"][0]["description"];
                        echo "<p><strong>Current Weather:</strong> $temperature_today°C, $weatherDesc</p>";
                    }
                    else {
                        echo "<p class='text-warning'>Weather data is currently unavailable.</p>";
                    }
                    
                    // Map Section
                    echo '<div id="map"></div>';

                    // Nearby Places API
                    $placesUrl = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=$latitude,$longitude&radius=1500&type=restaurant&key=$googleApiKey";
                    $placesResponse = file_get_contents($placesUrl);
                    $placesData = json_decode($placesResponse, true);
                    // Handling response OK
                    if ($placesData["status"] === "OK") {
                        echo "<h5 class='mt-4'>Nearby Places:</h5><ul>";
                        foreach ($placesData["results"] as $place) {
                            echo "<li>" . htmlspecialchars($place["name"]) . "</li>";
                        }
                        echo "</ul>";
                    }

                    echo "
                    <script>
                        function initMap() {
                            var location = { lat: $latitude, lng: $longitude };
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
                    </script>";
                }
                // Handling response NULL 
                else {
                    echo "<p class='text-danger'><strong>Invalid address.</strong> Please try again.</p>";
                }
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</div>

<script>
    // Address Autocomplete Initialization
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
