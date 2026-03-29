  
<?php

function get_weather_and_suggestions(string $city): array
{
    $API_KEY = "";

    $url= ""
    urlencode($city) . "&appid=$API_KEY&units=metric";

    $response = @file_get_contents($url);
    if (!$response) return fallback_weather();

    $data = json_decode($response, true);
    if (!isset($data['weather'][0]['main'])) return fallback_weather();

    $main = strtolower($data['weather'][0]['main']);

    switch ($main) {
        case 'clear':
            return weather_pack('sunny-bg', 'Sunny vibes 🌞', ['Beach', 'Outdoor cafés'], $main);
        case 'clouds':
            return weather_pack('cloudy-bg', 'Cloudy day ☁️', ['Museums', 'Coffee shops'], $main);
        case 'rain':
            return weather_pack('rainy-bg', 'Rainy mood 🌧️', ['Cinema', 'Indoor cafés'], $main);
        case 'snow':
            return weather_pack('snowy-bg', 'Snow vibes ❄️', ['Ski', 'Cabins'], $main);
        case 'thunderstorm':
            return weather_pack('stormy-bg', 'Stormy vibes 🌩️', ['Restaurants', 'Malls'], $main);
        default:
            return fallback_weather();
    }
}

function weather_pack($class, $placeholder, $suggestions, $type): array
{
    return [
        'weather_class' => $class,
        'search_placeholder' => $placeholder,
        'suggestions_array' => $suggestions,
        'weather_type' => $type
    ];
}

function fallback_weather(): array
{
    return weather_pack('default-bg', 'Explore Lebanon 🇱🇧', ['Events', 'Cafés'], 'default');
}
