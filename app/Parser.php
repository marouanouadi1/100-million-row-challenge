<?php

namespace App;

use Exception;

final class Parser
{
    public function parse(string $inputPath, string $outputPath): void
    {
        $handle = fopen($inputPath, 'r');
        $outputHandle = fopen($outputPath, 'w');

        stream_set_write_buffer($outputHandle, 1024 * 1024);

        $visitsByPath = [];

        while (($line = fgets($handle)) !== false) {
            $line = rtrim($line, "\r\n");
            if ($line === '') {
                continue;
            }

            $commaPos = strpos($line, ',');

            $url = substr($line, 0, $commaPos);
            $timestamp = substr($line, $commaPos + 1);

            $date = substr($timestamp, 0, 10);


            $path = $this->extractPath($url);


            if (! isset($visitsByPath[$path])) {
                $visitsByPath[$path] = [$date => 1];
                continue;
            }

            if (isset($visitsByPath[$path][$date])) {
                $visitsByPath[$path][$date]++;
            } else {
                $visitsByPath[$path][$date] = 1;
            }
        }

        foreach ($visitsByPath as &$visitsByDate) {
            ksort($visitsByDate);
        }
        unset($visitsByDate);

        $json = json_encode($visitsByPath, JSON_PRETTY_PRINT);
        if ($json === false) {
            throw new Exception('Failed to encode JSON output.');
        }

        fwrite($outputHandle, $json);

        fclose($handle);
        fclose($outputHandle);
    }

    private function extractPath(string $url): string
    {
        $pos = -1;
        for ($i = 0; $i < 3; $i++) {
            $pos = strpos($url, '/', $pos + 1);
        }

        return substr($url, $pos);
    }
}
