<?php

namespace App\Services\TryOn\Providers;

use App\Services\TryOn\TryOnProvider;
use Illuminate\Support\Facades\Log;

class MockTryOnProvider implements TryOnProvider
{
    public function generateTryOn(string $humanPhotoAbsolutePath, string $garmentPhotoUrl, string $category): array
    {
        Log::info("MockTryOnProvider triggered", [
            'human' => $humanPhotoAbsolutePath,
            'garment' => $garmentPhotoUrl,
            'category' => $category,
        ]);

        if (!file_exists($humanPhotoAbsolutePath)) {
            return [
                'success' => false,
                'result_image_content' => null,
                'error' => 'Foto manusia tidak ditemukan.',
            ];
        }

        $humanData = file_get_contents($humanPhotoAbsolutePath);

        if (!function_exists('imagecreatefromstring')) {
            return [
                'success' => true,
                'result_image_content' => $humanData,
                'error' => null,
            ];
        }

        $humanImg = @imagecreatefromstring($humanData);
        if (!$humanImg) {
            // Graceful fallback for non-image dummy bytes in tests
            return [
                'success' => true,
                'result_image_content' => $humanData,
                'error' => null,
            ];
        }

        $width = imagesx($humanImg);
        $height = imagesy($humanImg);

        // Fetch garment image content
        $garmentData = null;
        if (!empty($garmentPhotoUrl)) {
            if (file_exists($garmentPhotoUrl)) {
                $garmentData = @file_get_contents($garmentPhotoUrl);
            } elseif (filter_var($garmentPhotoUrl, FILTER_VALIDATE_URL)) {
                $garmentData = @file_get_contents($garmentPhotoUrl, false, stream_context_create([
                    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
                ]));
            }
        }

        $garmentImg = $garmentData ? @imagecreatefromstring($garmentData) : null;

        // Position & sizing based on category
        switch ($category) {
            case 'bottom':
                $cardWidth = (int) ($width * 0.52);
                $targetYRatio = 0.50;
                break;
            case 'dress':
                $cardWidth = (int) ($width * 0.65);
                $targetYRatio = 0.25;
                break;
            case 'outer':
                $cardWidth = (int) ($width * 0.62);
                $targetYRatio = 0.24;
                break;
            case 'top':
            default:
                $cardWidth = (int) ($width * 0.58);
                $targetYRatio = 0.25;
                break;
        }

        if ($garmentImg) {
            $gWidth = imagesx($garmentImg);
            $gHeight = imagesy($garmentImg);

            $cardHeight = (int) ($gHeight * ($cardWidth / max(1, $gWidth)));

            // Max height constraint (max 48% of human photo height)
            $maxCardHeight = (int) ($height * 0.48);
            if ($cardHeight > $maxCardHeight) {
                $cardHeight = $maxCardHeight;
                $cardWidth = (int) ($gWidth * ($cardHeight / max(1, $gHeight)));
            }

            $targetX = (int) (($width - $cardWidth) / 2);
            $targetY = (int) ($height * $targetYRatio);

            // Sample corner background colors
            $c1 = imagecolorat($garmentImg, 5, 5);
            $c2 = imagecolorat($garmentImg, max(0, $gWidth - 5), 5);
            $c3 = imagecolorat($garmentImg, 5, max(0, $gHeight - 5));
            $c4 = imagecolorat($garmentImg, max(0, $gWidth - 5), max(0, $gHeight - 5));

            $bgR = (int) ((($c1 >> 16 & 0xFF) + ($c2 >> 16 & 0xFF) + ($c3 >> 16 & 0xFF) + ($c4 >> 16 & 0xFF)) / 4);
            $bgG = (int) ((($c1 >> 8 & 0xFF) + ($c2 >> 8 & 0xFF) + ($c3 >> 8 & 0xFF) + ($c4 >> 8 & 0xFF)) / 4);
            $bgB = (int) ((($c1 & 0xFF) + ($c2 & 0xFF) + ($c3 & 0xFF) + ($c4 & 0xFF)) / 4);

            // Check if background color across corners is relatively uniform
            $var1 = sqrt((($c1>>16&0xFF)-$bgR)**2 + (($c1>>8&0xFF)-$bgG)**2 + (($c1&0xFF)-$bgB)**2);
            $var2 = sqrt((($c2>>16&0xFF)-$bgR)**2 + (($c2>>8&0xFF)-$bgG)**2 + (($c2&0xFF)-$bgB)**2);
            $isSolidBg = ($var1 < 30 && $var2 < 30);

            // Resized garment canvas
            $cardCanvas = imagecreatetruecolor($cardWidth, $cardHeight);
            imagealphablending($cardCanvas, false);
            imagesavealpha($cardCanvas, true);

            $transparent = imagecolorallocatealpha($cardCanvas, 0, 0, 0, 127);
            imagefilledrectangle($cardCanvas, 0, 0, $cardWidth, $cardHeight, $transparent);

            imagecopyresampled($cardCanvas, $garmentImg, 0, 0, 0, 0, $cardWidth, $cardHeight, $gWidth, $gHeight);

            if ($isSolidBg) {
                // Key out background color strictly based on corner color distance
                for ($x = 0; $x < $cardWidth; $x++) {
                    for ($y = 0; $y < $cardHeight; $y++) {
                        $rgb = imagecolorat($cardCanvas, $x, $y);
                        $r = ($rgb >> 16) & 0xFF;
                        $g = ($rgb >> 8) & 0xFF;
                        $b = $rgb & 0xFF;

                        $dist = sqrt(($r - $bgR)**2 + ($g - $bgG)**2 + ($b - $bgB)**2);
                        if ($dist < 35) {
                            imagesetpixel($cardCanvas, $x, $y, $transparent);
                        }
                    }
                }
            } else {
                // Non-solid background (lifestyle image): apply rounded card mask
                $radius = 16;
                for ($x = 0; $x < $cardWidth; $x++) {
                    for ($y = 0; $y < $cardHeight; $y++) {
                        $isTopLeft = ($x < $radius && $y < $radius && (pow($x - $radius, 2) + pow($y - $radius, 2) > pow($radius, 2)));
                        $isTopRight = ($x >= $cardWidth - $radius && $y < $radius && (pow($x - ($cardWidth - $radius), 2) + pow($y - $radius, 2) > pow($radius, 2)));
                        $isBottomLeft = ($x < $radius && $y >= $cardHeight - $radius && (pow($x - $radius, 2) + pow($y - ($cardHeight - $radius), 2) > pow($radius, 2)));
                        $isBottomRight = ($x >= $cardWidth - $radius && $y >= $cardHeight - $radius && (pow($x - ($cardWidth - $radius), 2) + pow($y - ($cardHeight - $radius), 2) > pow($radius, 2)));

                        if ($isTopLeft || $isTopRight || $isBottomLeft || $isBottomRight) {
                            imagesetpixel($cardCanvas, $x, $y, $transparent);
                        }
                    }
                }
            }

            // Merge onto human photo
            imagealphablending($humanImg, true);
            imagecopy($humanImg, $cardCanvas, $targetX, $targetY, 0, 0, $cardWidth, $cardHeight);

            imagedestroy($cardCanvas);
            imagedestroy($garmentImg);
        } else {
            // Stylized placeholder badge if garment photo is unavailable
            $cardWidth = (int) ($width * 0.55);
            $cardHeight = (int) ($height * 0.30);
            $targetX = (int) (($width - $cardWidth) / 2);
            $targetY = (int) ($height * $targetYRatio);

            imagealphablending($humanImg, true);
            $badgeBg = imagecolorallocatealpha($humanImg, 255, 255, 255, 20);
            $badgeBorder = imagecolorallocate($humanImg, 74, 139, 163);
            $textColor = imagecolorallocate($humanImg, 30, 41, 59);

            imagefilledrectangle($humanImg, $targetX, $targetY, $targetX + $cardWidth, $targetY + $cardHeight, $badgeBg);
            imagerectangle($humanImg, $targetX, $targetY, $targetX + $cardWidth, $targetY + $cardHeight, $badgeBorder);
            imagestring($humanImg, 4, $targetX + 15, $targetY + 20, "Produk: " . strtoupper($category), $textColor);
            imagestring($humanImg, 3, $targetX + 15, $targetY + 50, "(Gambar Tidak Tersedia)", $textColor);
        }

        // Render branding footer banner
        $textColor = imagecolorallocate($humanImg, 255, 255, 255);
        $bgColor = imagecolorallocatealpha($humanImg, 15, 23, 42, 25);
        imagefilledrectangle($humanImg, 0, $height - 40, $width, $height, $bgColor);
        imagestring($humanImg, 3, 15, $height - 27, "MOVR AI Try-On | Visual Simulation", $textColor);

        ob_start();
        imagejpeg($humanImg, null, 90);
        $imageContent = ob_get_clean();
        imagedestroy($humanImg);

        return [
            'success' => true,
            'result_image_content' => $imageContent,
            'error' => null,
        ];
    }
}
