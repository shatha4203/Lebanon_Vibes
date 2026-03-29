<?php

function recommend_business_categories(string $weather): array
{
    return match ($weather) {
        'clear' => ['Beach', 'Café', 'Outdoor'],
        'clouds' => ['Café', 'Museum'],
        'rain' => ['Cinema', 'Indoor Café'],
        'snow' => ['Hotel', 'Cabin'],
        'thunderstorm' => ['Restaurant', 'Mall'],
        default => ['Café']
    };
}
?>