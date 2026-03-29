<?php
class WeatherModel {

    public static function fetch(string $city) {
     $url= "API-KEY"
        return json_decode(file_get_contents($url), true);
    }

    public static function classify(array $data): array {
        $main = strtolower($data['weather'][0]['main']);
        $icon = $data['weather'][0]['icon'];

        if (str_contains($main, 'rain')) return ['rainy','rain'];
        if (str_contains($main, 'cloud')) return ['cloudy','clouds'];
        if (str_contains($main, 'snow')) return ['snowy','snow'];
        if (str_contains($icon, 'n')) return ['clear-night','night'];

        return ['sunny','sun'];
    }
}
