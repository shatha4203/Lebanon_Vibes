<?php
function auto_assign_weather_tag(string $category, bool $isOutdoor): string
{
    $category = strtolower($category);

    if ($isOutdoor) {
        if (str_contains($category, 'beach') || str_contains($category, 'hike')) return 'sunny';
        if (str_contains($category, 'ski')) return 'snowy';
        return 'cloudy';
    }

    if (
        str_contains($category, 'cinema') ||
        str_contains($category, 'theater') ||
        str_contains($category, 'museum')
    ) return 'rainy';

    return 'cloudy';
}
