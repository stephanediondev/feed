<?php

namespace App\Helper;

use GeoIp2\Database\Reader;

final class MaxmindHelper
{
    /**
     * @return array<mixed>
     */
    public static function get(string $ip): array
    {
        $data = [];

        $file = __DIR__.'/../../maxmind/GeoLite2-City/GeoLite2-City.mmdb';

        if (file_exists($file)) {
            $reader = new Reader($file);
            $record = $reader->city($ip);
            $reader->close();

            if ($country = $record->country->name) {
                $data['country'] = $country;
            }

            if ($subdivision = $record->mostSpecificSubdivision->name) {
                $data['subdivision'] = $subdivision;
            }

            if ($city = $record->city->name) {
                $data['city'] = $city;
            }

            if ($latitude = $record->location->latitude) {
                $data['latitude'] = $latitude;
            }

            if ($longitude = $record->location->longitude) {
                $data['longitude'] = $longitude;
            }
        }

        return $data;
    }
}
