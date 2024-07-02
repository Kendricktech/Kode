<?php
// Enable error reporting for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set CORS headers to allow cross-origin requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Retrieve and sanitize the visitor's name
    $visitor_name = isset($_GET['visitor_name']) ? htmlspecialchars($_GET['visitor_name']) : null;

    if (empty($visitor_name)) {
        echo json_encode(["error" => "Please enter a valid name. Encode your request in the form: 'http://yourdomain.com/api/hello?visitor_name=YourName'"]);
        exit;
    }

    // Function to get the client's IP address
    function getClientIP() {
        $ipAddress = '';

        if (isset($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Check for multiple IP addresses in the `HTTP_X_FORWARDED_FOR` header and take the first one
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($ipList as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    $ipAddress = $ip;
                    break;
                }
            }
        } elseif (isset($_SERVER['HTTP_X_FORWARDED']) && filter_var($_SERVER['HTTP_X_FORWARDED'], FILTER_VALIDATE_IP)) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && filter_var($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'], FILTER_VALIDATE_IP)) {
            $ipAddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
            $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED']) && filter_var($_SERVER['HTTP_FORWARDED'], FILTER_VALIDATE_IP)) {
            $ipAddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipAddress = 'UNKNOWN';
        }

        return $ipAddress;
    }

    // Get the client's IP address
    $client_ip = getClientIP();

    // Initialize city and temperature variables
    $city = 'Unknown';
    $temperature = 'Unknown';

    // Define API keys and endpoints
    $weather_api_keys = [
        'weatherapi' => '6c71540d41ec4442a8c51559240107',
        'openweathermap' => 'c62a80c3b0148cfab2eef4a80363c853',
        'accuweather' => 'Kj3IiAKqbAmy6YaCtDVrgseIG9Ls2uND'
    ];
    $ipgeolocation_token = 'ea90e7e8ff07435a9476aeac4dcf234e';
    $location_urls = [
        "ipgeolocation" => "https://api.ipgeolocation.io/ipgeo?apiKey={$ipgeolocation_token}&ip={$client_ip}"
    ];

    try {
        $cities = [];
        $latitude = null;
        $longitude = null;

        // Fetch location data from each API
        foreach ($location_urls as $key => $url) {
            $location_response = @file_get_contents($url);
            if ($location_response !== FALSE) {
                $location_data = json_decode($location_response, true);
                if (isset($location_data['city'])) {
                    $cities[] = $location_data['city'];
                }
                if (!isset($latitude) && isset($location_data['latitude'])) {
                    $latitude = $location_data['latitude'];
                    $longitude = $location_data['longitude'];
                }
            }
        }

        // Determine the most common city from the responses
        if (!empty($cities)) {
            $city_counts = array_count_values($cities);
            $max_count = max($city_counts);
            $most_common_cities = array_keys(array_filter($city_counts, function($count) use ($max_count) {
                return $count == $max_count;
            }));
            $city = $most_common_cities[array_rand($most_common_cities)];
        } else {
            $city = 'Unknown';
        }

        // Define the weather API URLs
        $weather_urls = [];
        if (isset($latitude) && isset($longitude)) {
            $weather_urls = [
                "weatherapi" => "http://api.weatherapi.com/v1/current.json?key={$weather_api_keys['weatherapi']}&q={$city}&aqi=no",
                "openweathermap" => "http://api.openweathermap.org/data/2.5/weather?q={$city}&appid={$weather_api_keys['openweathermap']}&units=metric",
                "openmeteo" => "https://api.open-meteo.com/v1/forecast?latitude={$latitude}&longitude={$longitude}&hourly=temperature_2m"
            ];
        }

        // Attempt to fetch the weather data from each API in order
        $temperatures = [];
        foreach ($weather_urls as $key => $url) {
            $weather_response = @file_get_contents($url);
            if ($weather_response !== FALSE) {
                $weather_data = json_decode($weather_response, true);
                if ($key == 'weatherapi') {
                    $temp = $weather_data['current']['temp_c'] ?? null;
                    if ($temp !== null) $temperatures[] = strval($temp);
                } elseif ($key == 'openweathermap') {
                    $temp = $weather_data['main']['temp'] ?? null;
                    if ($temp !== null) $temperatures[] = strval($temp);
                } elseif ($key == 'openmeteo') {
                    $temp = $weather_data['hourly']['temperature_2m'][0] ?? null;
                    if ($temp !== null) $temperatures[] = strval($temp);
                }
            }
        }

        // Determine the most common temperature from the responses
        if (!empty($temperatures)) {
            $temperature_counts = array_count_values($temperatures);
            $max_count = max($temperature_counts);
            $most_common_temps = array_keys(array_filter($temperature_counts, function($count) use ($max_count) {
                return $count == $max_count;
            }));
            $temperature = $most_common_temps[array_rand($most_common_temps)];
        } else {
            $temperature = 'Unknown';
        }

        // Prepare the response
        $response = [
            "client_ip" => $client_ip,
            "location" => $city,
            "greeting" => "Hello, " . htmlspecialchars($visitor_name) . "! The temperature is " . $temperature . " Celsius in " . $city
        ];

        // Send the response as JSON
        header('Content-Type: application/json');
        echo json_encode($response);

    } catch (Exception $e) {
        // Handle exceptions and send an error response
        header('Content-Type: application/json');
        echo json_encode(["error" => htmlspecialchars($e->getMessage())]);
    }
} else {
    // Send a 405 Method Not Allowed response if the request method is not GET
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
}
?>
