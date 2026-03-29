<?php
require_once ' db_connection.php';
require_once ' WeatherModel.php';

class HomeController {

    public static function index() {
        $city = $_GET['city'] ?? 'Beirut';

        $raw = WeatherModel::fetch($city);
        [$weatherClass, $effect] = WeatherModel::classify($raw);

        $theme = $weatherClass === 'clear-night' ? 'dark' : 'light';

        require 'home.php';
    }
}
?>