<?php

namespace App\Services;

class QrCodeService
{
    private static array $exp = [];

    private static array $log = [];

    private static bool $tablesInitialized = false;

    private static function initTables(): void
    {
        if (self::$tablesInitialized) {
            return;
        }

        self::$exp = array_fill(0, 512, 0);
        self::$log = array_fill(0, 256, 0);
        $val = 1;
        for ($i = 0; $i < 255; $i++) {
            self::$exp[$i] = $val;
            self::$log[$val] = $i;
            $val <<= 1;
            if ($val & 0x100) {
                $val ^= 0x11D;
            }
        }
        for ($i = 255; $i < 512; $i++) {
            self::$exp[$i] = self::$exp[$i - 255];
        }

        self::$tablesInitialized = true;
    }

    private static function gfMul(int $x, int $y): int
    {
        if ($x === 0 || $y === 0) {
            return 0;
        }

        return self::$exp[self::$log[$x] + self::$log[$y]];
    }

    private static function rsGenerator(int $degree): array
    {
        $poly = [1];
        for ($i = 0; $i < $degree; $i++) {
            $next = [1, self::$exp[$i]];
            $res = array_fill(0, count($poly) + 1, 0);
            for ($j = 0; $j < count($poly); $j++) {
                $res[$j] ^= self::gfMul($poly[$j], $next[0]);
                $res[$j + 1] ^= self::gfMul($poly[$j], $next[1]);
            }
            $poly = $res;
        }

        return $poly;
    }

    private static function rsRemainder(array $data, array $gen): array
    {
        $deg = count($gen) - 1;
        $work = array_merge($data, array_fill(0, $deg, 0));
        for ($i = 0; $i < count($data); $i++) {
            $lead = $work[$i];
            if ($lead !== 0) {
                for ($j = 0; $j < count($gen); $j++) {
                    $work[$i + $j] ^= self::gfMul($lead, $gen[$j]);
                }
            }
        }

        return array_slice($work, count($data));
    }

    /**
     * Version specs for Level M (Version 1 - 14)
     * [version, size, totalCw, dataCw, ecCwPerBlock, b1Count, b1Data, b2Count, b2Data, [alignPos], versionBits]
     */
    private static array $versionSpecsM = [
        1 => [1,  21, 26,  16,  10, 1, 16, 0, 0,  [],                  0],
        2 => [2,  25, 44,  28,  16, 1, 28, 0, 0,  [6, 18],             0],
        3 => [3,  29, 70,  44,  26, 1, 44, 0, 0,  [6, 22],             0],
        4 => [4,  33, 100, 64,  18, 2, 32, 0, 0,  [6, 26],             0],
        5 => [5,  37, 134, 86,  24, 2, 43, 0, 0,  [6, 30],             0],
        6 => [6,  41, 172, 108, 16, 4, 27, 0, 0,  [6, 34],             0],
        7 => [7,  45, 196, 124, 18, 4, 31, 0, 0,  [6, 22, 38],         0x07C94],
        8 => [8,  49, 242, 154, 22, 2, 38, 2, 39, [6, 24, 42],         0x085BC],
        9 => [9,  53, 292, 182, 22, 3, 36, 2, 37, [6, 26, 46],         0x09A99],
        10 => [10, 57, 346, 216, 26, 4, 43, 1, 44, [6, 28, 50],         0x0A4D3],
        11 => [11, 61, 404, 252, 30, 1, 50, 4, 51, [6, 30, 54],         0x0BBF6],
        12 => [12, 65, 466, 290, 22, 6, 36, 2, 37, [6, 32, 58],         0x0C762],
        13 => [13, 69, 532, 334, 22, 8, 37, 4, 38, [6, 34, 62],         0x0D847],
        14 => [14, 73, 580, 365, 24, 4, 40, 5, 41, [6, 26, 46, 66],     0x0E60D],
    ];

    public static function formatTlv(string $tag, string $val): string
    {
        return $tag.str_pad((string) strlen($val), 2, '0', STR_PAD_LEFT).$val;
    }

    public static function crc16(string $str): string
    {
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($str); $i++) {
            $crc ^= (ord($str[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    public static function generateQrisPayload(
        string $orderNumber,
        int|float $amount,
        string $merchantName = 'BOOK STORE INDONESIA',
        string $city = 'JAKARTA',
        string $postalCode = '10110',
        string $nmid = 'ID1020039482910'
    ): string {
        $mInfo = self::formatTlv('00', 'ID.CO.QRIS.WWW')
            .self::formatTlv('01', '936009990000000000')
            .self::formatTlv('02', $nmid)
            .self::formatTlv('03', 'UMI');

        $danaInfo = self::formatTlv('00', 'ID.DANA.WWW')
            .self::formatTlv('01', '936009990000000000')
            .self::formatTlv('02', $nmid);

        $addInfo = self::formatTlv('01', $orderNumber)
            .self::formatTlv('07', 'A01');

        $cleanAmount = (string) intval($amount);

        $raw = self::formatTlv('00', '01')
            .self::formatTlv('01', '12')
            .self::formatTlv('26', $mInfo)
            .self::formatTlv('51', $danaInfo)
            .self::formatTlv('52', '5942')
            .self::formatTlv('53', '360')
            .self::formatTlv('54', $cleanAmount)
            .self::formatTlv('58', 'ID')
            .self::formatTlv('59', mb_substr($merchantName, 0, 25))
            .self::formatTlv('60', mb_substr($city, 0, 15))
            .self::formatTlv('61', $postalCode)
            .self::formatTlv('62', $addInfo)
            .'6304';

        $crc = self::crc16($raw);

        return $raw.$crc;
    }

    public static function generateSvg(string $text, int $displaySize = 280, int $margin = 2): string
    {
        self::initTables();

        $textLen = strlen($text);
        $selectedSpec = null;

        foreach (self::$versionSpecsM as $spec) {
            $ver = $spec[0];
            $charCountBits = ($ver <= 9) ? 8 : 16;
            $dataBitCap = $spec[3] * 8;
            $neededBits = 4 + $charCountBits + ($textLen * 8);
            if ($neededBits <= $dataBitCap) {
                $selectedSpec = $spec;
                break;
            }
        }

        if (! $selectedSpec) {
            $selectedSpec = self::$versionSpecsM[14];
        }

        [$ver, $gridSize, $totalCw, $dataCw, $ecCw, $b1Count, $b1Data, $b2Count, $b2Data, $alignCoords, $verBits] = $selectedSpec;

        // 1. Bit stream
        $bits = [];
        $addBits = function (int $val, int $len) use (&$bits): void {
            for ($i = $len - 1; $i >= 0; $i--) {
                $bits[] = ($val >> $i) & 1;
            }
        };

        // Byte Mode indicator = 4 (0100)
        $addBits(4, 4);
        $charCountBits = ($ver <= 9) ? 8 : 16;
        $addBits($textLen, $charCountBits);
        for ($i = 0; $i < $textLen; $i++) {
            $addBits(ord($text[$i]), 8);
        }

        $maxBits = $dataCw * 8;
        $termLen = min(4, $maxBits - count($bits));
        for ($i = 0; $i < $termLen; $i++) {
            $bits[] = 0;
        }
        while (count($bits) % 8 !== 0 && count($bits) < $maxBits) {
            $bits[] = 0;
        }

        $bytes = [];
        for ($i = 0; $i < count($bits); $i += 8) {
            $b = 0;
            for ($j = 0; $j < 8; $j++) {
                $b = ($b << 1) | $bits[$i + $j];
            }
            $bytes[] = $b;
        }

        $pad = [0xEC, 0x11];
        $padIdx = 0;
        while (count($bytes) < $dataCw) {
            $bytes[] = $pad[$padIdx % 2];
            $padIdx++;
        }

        // 2. Error Correction
        $dataBlocks = [];
        $ecBlocks = [];
        $genPoly = self::rsGenerator($ecCw);

        $bytePos = 0;
        for ($b = 0; $b < $b1Count; $b++) {
            $chunk = array_slice($bytes, $bytePos, $b1Data);
            $bytePos += $b1Data;
            $dataBlocks[] = $chunk;
            $ecBlocks[] = self::rsRemainder($chunk, $genPoly);
        }
        for ($b = 0; $b < $b2Count; $b++) {
            $chunk = array_slice($bytes, $bytePos, $b2Data);
            $bytePos += $b2Data;
            $dataBlocks[] = $chunk;
            $ecBlocks[] = self::rsRemainder($chunk, $genPoly);
        }

        // 3. Interleaving
        $finalCodewords = [];
        $maxDataInBlock = max($b1Data, $b2Data ?: 0);
        $totalBlocks = $b1Count + $b2Count;

        for ($i = 0; $i < $maxDataInBlock; $i++) {
            for ($b = 0; $b < $totalBlocks; $b++) {
                if ($i < count($dataBlocks[$b])) {
                    $finalCodewords[] = $dataBlocks[$b][$i];
                }
            }
        }
        for ($i = 0; $i < $ecCw; $i++) {
            for ($b = 0; $b < $totalBlocks; $b++) {
                $finalCodewords[] = $ecBlocks[$b][$i];
            }
        }

        // 4. Matrix creation
        // -1 = unassigned, 0/1 = data, 2/3 = function/reserved
        $grid = array_fill(0, $gridSize, array_fill(0, $gridSize, -1));

        // Finders
        $placeFinder = function (int $r0, int $c0) use (&$grid, $gridSize): void {
            for ($r = -1; $r <= 7; $r++) {
                for ($c = -1; $c <= 7; $c++) {
                    $rCurr = $r0 + $r;
                    $cCurr = $c0 + $c;
                    if ($rCurr >= 0 && $rCurr < $gridSize && $cCurr >= 0 && $cCurr < $gridSize) {
                        if ($r >= 0 && $r <= 6 && $c >= 0 && $c <= 6) {
                            if ($r === 0 || $r === 6 || $c === 0 || $c === 6 || ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4)) {
                                $grid[$rCurr][$cCurr] = 3;
                            } else {
                                $grid[$rCurr][$cCurr] = 2;
                            }
                        } else {
                            $grid[$rCurr][$cCurr] = 2;
                        }
                    }
                }
            }
        };

        $placeFinder(0, 0);
        $placeFinder(0, $gridSize - 7);
        $placeFinder($gridSize - 7, 0);

        // Alignments
        if (! empty($alignCoords)) {
            $numCoords = count($alignCoords);
            for ($i = 0; $i < $numCoords; $i++) {
                for ($j = 0; $j < $numCoords; $j++) {
                    $r = $alignCoords[$i];
                    $c = $alignCoords[$j];
                    if (($r < 9 && $c < 9) || ($r < 9 && $c >= $gridSize - 8) || ($r >= $gridSize - 8 && $c < 9)) {
                        continue;
                    }
                    for ($dr = -2; $dr <= 2; $dr++) {
                        for ($dc = -2; $dc <= 2; $dc++) {
                            if (abs($dr) === 2 || abs($dc) === 2 || ($dr === 0 && $dc === 0)) {
                                $grid[$r + $dr][$c + $dc] = 3;
                            } else {
                                $grid[$r + $dr][$c + $dc] = 2;
                            }
                        }
                    }
                }
            }
        }

        // Timing
        for ($i = 8; $i < $gridSize - 8; $i++) {
            if ($grid[6][$i] === -1) {
                $grid[6][$i] = ($i % 2 === 0) ? 3 : 2;
            }
            if ($grid[$i][6] === -1) {
                $grid[$i][6] = ($i % 2 === 0) ? 3 : 2;
            }
        }

        // Dark module
        $grid[4 * $ver + 9][8] = 3;

        // Reserve Format Info
        for ($i = 0; $i <= 8; $i++) {
            if ($grid[8][$i] === -1) {
                $grid[8][$i] = 2;
            }
            if ($grid[$i][8] === -1) {
                $grid[$i][8] = 2;
            }
        }
        for ($i = 0; $i < 8; $i++) {
            if ($grid[8][$gridSize - 1 - $i] === -1) {
                $grid[8][$gridSize - 1 - $i] = 2;
            }
            if ($grid[$gridSize - 1 - $i][8] === -1) {
                $grid[$gridSize - 1 - $i][8] = 2;
            }
        }

        // Reserve Version Info if ver >= 7
        if ($ver >= 7) {
            for ($i = 0; $i < 6; $i++) {
                for ($j = 0; $j < 3; $j++) {
                    // bottom-left
                    $grid[$gridSize - 11 + $j][$i] = 2;
                    // top-right
                    $grid[$i][$gridSize - 11 + $j] = 2;
                }
            }
        }

        // 5. Place Data Bits
        $allBits = [];
        foreach ($finalCodewords as $cw) {
            for ($b = 7; $b >= 0; $b--) {
                $allBits[] = ($cw >> $b) & 1;
            }
        }

        $bitIdx = 0;
        $totalBits = count($allBits);
        $up = true;

        for ($right = $gridSize - 1; $right > 0; $right -= 2) {
            if ($right === 6) {
                $right--;
            }
            for ($v = 0; $v < $gridSize; $v++) {
                $row = $up ? ($gridSize - 1 - $v) : $v;
                for ($col = $right; $col >= $right - 1; $col--) {
                    if ($grid[$row][$col] === -1) {
                        $bit = ($bitIdx < $totalBits) ? $allBits[$bitIdx++] : 0;
                        // Mask 0: (row + col) % 2 === 0
                        $mask = (($row + $col) % 2 === 0);
                        $grid[$row][$col] = ($mask ? ($bit ^ 1) : $bit);
                    }
                }
            }
            $up = ! $up;
        }

        // 6. Format Info (Level M = 00, Mask 0 = 000) -> 0x5412
        $formatBits = 0x5412;
        $coordsTopLeft = [
            [8, 0], [8, 1], [8, 2], [8, 3], [8, 4], [8, 5], [8, 7], [8, 8],
            [7, 8], [5, 8], [4, 8], [3, 8], [2, 8], [1, 8], [0, 8],
        ];
        $coordsSplit = [
            [$gridSize - 1, 8], [$gridSize - 2, 8], [$gridSize - 3, 8], [$gridSize - 4, 8],
            [$gridSize - 5, 8], [$gridSize - 6, 8], [$gridSize - 7, 8],
            [8, $gridSize - 8], [8, $gridSize - 7], [8, $gridSize - 6], [8, $gridSize - 5],
            [8, $gridSize - 4], [8, $gridSize - 3], [8, $gridSize - 2], [8, $gridSize - 1],
        ];

        for ($i = 0; $i < 15; $i++) {
            $bitVal = (($formatBits >> $i) & 1);
            [$r1, $c1] = $coordsTopLeft[$i];
            $grid[$r1][$c1] = $bitVal ? 3 : 2;

            [$r2, $c2] = $coordsSplit[$i];
            $grid[$r2][$c2] = $bitVal ? 3 : 2;
        }

        // 7. Version Info if ver >= 7
        if ($ver >= 7 && $verBits !== 0) {
            for ($i = 0; $i < 18; $i++) {
                $bitVal = (($verBits >> $i) & 1);
                $r = (int) floor($i / 3);
                $c = $i % 3;
                // bottom-left
                $grid[$gridSize - 11 + $c][$r] = $bitVal ? 3 : 2;
                // top-right
                $grid[$r][$gridSize - 11 + $c] = $bitVal ? 3 : 2;
            }
        }

        // 8. Render SVG
        $viewSize = $gridSize + (2 * $margin);
        $pathD = '';

        for ($r = 0; $r < $gridSize; $r++) {
            for ($c = 0; $c < $gridSize; $c++) {
                $val = $grid[$r][$c];
                if ($val === 1 || $val === 3) {
                    $x = $c + $margin;
                    $y = $r + $margin;
                    $pathD .= "M{$x},{$y}h1v1h-1z ";
                }
            }
        }

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$viewSize} {$viewSize}" width="{$displaySize}" height="{$displaySize}" shape-rendering="crispEdges">
    <rect width="100%" height="100%" fill="#ffffff"/>
    <path fill="#0f172a" d="{$pathD}"/>
</svg>
SVG;
    }
}
