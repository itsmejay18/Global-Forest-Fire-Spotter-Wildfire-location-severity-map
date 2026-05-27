<?php

namespace App\Services;

use Illuminate\Support\Str;
use SplFileObject;

class WildfireCsvLoader
{
    /**
     * @return array<int, array{
     *   id:int,
     *   latitude:float,
     *   longitude:float,
     *   burned_area:float,
     *   fire_date:string,
     *   severity:string,
     *   country:string,
     *   month:int,
     *   year:int,
     *   duration:int,
     *   name:string
     * }>
     */
    public function load(string $path): array
    {
        $file = new SplFileObject($path);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        $headers = null;
        $incidents = [];
        $rowId = 1;

        foreach ($file as $row) {
            if (!is_array($row) || (count($row) === 1 && $row[0] === null)) {
                continue;
            }

            if ($headers === null) {
                $headers = $this->normalizeHeaders($row);
                continue;
            }

            $data = $this->assocRow($headers, $row);
            if ($data === null) {
                continue;
            }

            if ($this->looksLikeLatLngSchema($data)) {
                $incident = $this->fromLatLngSchema($data, $rowId);
            } elseif ($this->looksLikeXYSchema($data)) {
                $incident = $this->fromXYSchema($data, $rowId);
            } else {
                continue;
            }

            if ($incident !== null) {
                $incidents[] = $incident;
                $rowId++;
            }
        }

        return $incidents;
    }

    /**
     * @param array<int, mixed> $row
     * @return array<int, string>
     */
    private function normalizeHeaders(array $row): array
    {
        return array_map(function ($h) {
            $h = is_string($h) ? trim($h) : '';
            $h = Str::lower($h);
            return $h;
        }, $row);
    }

    /**
     * @param array<int, string> $headers
     * @param array<int, mixed> $row
     * @return array<string, string>|null
     */
    private function assocRow(array $headers, array $row): ?array
    {
        $assoc = [];

        $max = min(count($headers), count($row));
        for ($i = 0; $i < $max; $i++) {
            $key = $headers[$i] ?? '';
            if ($key === '') {
                continue;
            }
            $value = $row[$i];
            $assoc[$key] = is_string($value) ? trim($value) : (string) ($value ?? '');
        }

        if ($assoc === []) {
            return null;
        }

        return $assoc;
    }

    /**
     * @param array<string, string> $data
     */
    private function looksLikeLatLngSchema(array $data): bool
    {
        return array_key_exists('latitude', $data) && array_key_exists('longitude', $data);
    }

    /**
     * @param array<string, string> $data
     */
    private function looksLikeXYSchema(array $data): bool
    {
        return array_key_exists('x', $data) && array_key_exists('y', $data) && array_key_exists('month', $data);
    }

    /**
     * @param array<string, string> $data
     * @return array<string, mixed>|null
     */
    private function fromLatLngSchema(array $data, int $id): ?array
    {
        $latitude = $this->toFloat($data['latitude'] ?? null);
        $longitude = $this->toFloat($data['longitude'] ?? null);
        if ($latitude === null || $longitude === null) {
            return null;
        }

        $burnedArea = $this->toFloat($data['burned_area'] ?? $data['area'] ?? null) ?? 0.0;
        $month = $this->monthToInt($data['month'] ?? null) ?? $this->monthFromDate($data['fire_date'] ?? null) ?? 1;
        $year = $this->toInt($data['year'] ?? null) ?? $this->yearFromDate($data['fire_date'] ?? null) ?? (int) now()->format('Y');

        $severity = $this->normalizeSeverity($data['severity'] ?? null, $burnedArea);
        $country = $this->nonEmpty($data['country'] ?? null) ?? 'Unknown';
        $duration = $this->toInt($data['duration'] ?? null) ?? 0;
        $name = $this->nonEmpty($data['name'] ?? null) ?? "Wildfire #{$id}";

        $fireDate = $this->normalizeDate($data['fire_date'] ?? null, $year, $month);

        return [
            'id' => $id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'burned_area' => $burnedArea,
            'fire_date' => $fireDate,
            'severity' => $severity,
            'country' => $country,
            'month' => $month,
            'year' => $year,
            'duration' => $duration,
            'name' => $name,
        ];
    }

    /**
     * Kaggle "forestfires" dataset (Montesinho park) uses X/Y grid (1..9), not true lat/lng.
     * We still render it by mapping grid cells to an approximate Portugal bounding box.
     *
     * @param array<string, string> $data
     * @return array<string, mixed>|null
     */
    private function fromXYSchema(array $data, int $id): ?array
    {
        $x = $this->toFloat($data['x'] ?? null);
        $y = $this->toFloat($data['y'] ?? null);
        if ($x === null || $y === null) {
            return null;
        }

        $month = $this->monthToInt($data['month'] ?? null) ?? 1;
        $year = (int) now()->format('Y');

        $burnedArea = $this->toFloat($data['area'] ?? null) ?? 0.0;
        $severity = $this->normalizeSeverity(null, $burnedArea);

        // Approximate mapping around Montesinho Natural Park (Portugal).
        // This is only for visualization when true coordinates are not present.
        $baseLat = 41.90;
        $baseLng = -6.95;
        $step = 0.03;
        $latitude = $baseLat + (($y - 1.0) * $step);
        $longitude = $baseLng + (($x - 1.0) * $step);

        return [
            'id' => $id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'burned_area' => $burnedArea,
            'fire_date' => sprintf('%04d-%02d-01', $year, $month),
            'severity' => $severity,
            'country' => 'Portugal',
            'month' => $month,
            'year' => $year,
            'duration' => 0,
            'name' => "Grid Fire ({$x}, {$y})",
        ];
    }

    private function normalizeSeverity(?string $severity, float $burnedArea): string
    {
        $s = $this->nonEmpty($severity);
        if ($s !== null) {
            $s = Str::ucfirst(Str::lower($s));
            if (in_array($s, ['Low', 'Medium', 'High', 'Extreme'], true)) {
                return $s;
            }
        }

        if ($burnedArea >= 1000) {
            return 'Extreme';
        }
        if ($burnedArea >= 500) {
            return 'High';
        }
        if ($burnedArea >= 100) {
            return 'Medium';
        }
        return 'Low';
    }

    private function normalizeDate(?string $value, int $year, int $month): string
    {
        $v = $this->nonEmpty($value);
        if ($v !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) === 1) {
            return $v;
        }

        return sprintf('%04d-%02d-01', $year, $month);
    }

    private function monthToInt(?string $value): ?int
    {
        $v = $this->nonEmpty($value);
        if ($v === null) {
            return null;
        }

        if (ctype_digit($v)) {
            $m = (int) $v;
            return ($m >= 1 && $m <= 12) ? $m : null;
        }

        $v = Str::lower($v);
        $map = [
            'jan' => 1, 'january' => 1,
            'feb' => 2, 'february' => 2,
            'mar' => 3, 'march' => 3,
            'apr' => 4, 'april' => 4,
            'may' => 5,
            'jun' => 6, 'june' => 6,
            'jul' => 7, 'july' => 7,
            'aug' => 8, 'august' => 8,
            'sep' => 9, 'sept' => 9, 'september' => 9,
            'oct' => 10, 'october' => 10,
            'nov' => 11, 'november' => 11,
            'dec' => 12, 'december' => 12,
        ];

        return $map[$v] ?? null;
    }

    private function monthFromDate(?string $value): ?int
    {
        $v = $this->nonEmpty($value);
        if ($v === null || preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $v, $m) !== 1) {
            return null;
        }
        $month = (int) $m[2];
        return ($month >= 1 && $month <= 12) ? $month : null;
    }

    private function yearFromDate(?string $value): ?int
    {
        $v = $this->nonEmpty($value);
        if ($v === null || preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $v, $m) !== 1) {
            return null;
        }
        return (int) $m[1];
    }

    private function toFloat(?string $value): ?float
    {
        $v = $this->nonEmpty($value);
        if ($v === null) {
            return null;
        }

        $v = str_replace([',', ' '], ['', ''], $v);
        if (!is_numeric($v)) {
            return null;
        }

        return (float) $v;
    }

    private function toInt(?string $value): ?int
    {
        $v = $this->nonEmpty($value);
        if ($v === null) {
            return null;
        }
        if (!is_numeric($v)) {
            return null;
        }
        return (int) round((float) $v);
    }

    private function nonEmpty(?string $value): ?string
    {
        $v = is_string($value) ? trim($value) : null;
        return ($v === null || $v === '') ? null : $v;
    }
}

