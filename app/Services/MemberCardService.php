<?php

namespace App\Services;

use App\Models\Member;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MemberCardService
{
  public function generate(Member $member): string
  {
    $width = 600;
    $height = 420;
    $image = imagecreatetruecolor($width, $height);

    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 30, 30, 30);
    $blue = imagecolorallocate($image, 37, 99, 235);
    $gray = imagecolorallocate($image, 100, 116, 139);

    imagefilledrectangle($image, 0, 0, $width, $height, $white);
    imagefilledrectangle($image, 0, 0, $width, 70, $blue);

    // Header
    imagestring($image, 5, 22, 28, 'MEMBER CARD', $white);

    // Nama
    imagestring($image, 5, 30, 100, strtoupper($member->name), $black);

    // Kode
    imagestring($image, 4, 30, 135, 'Kode: ' . $member->member_code, $blue);

    // Gender
    $gender = $member->gender ? ucfirst($member->gender) : '-';
    imagestring($image, 3, 30, 165, 'Gender: ' . $gender, $gray);

    // --- QR CODE ---
    $qrPng = QrCode::format('png')
      ->size(160)
      ->margin(1)
      ->errorCorrection('M')
      ->generate($member->member_code);

    $qr = imagecreatefromstring($qrPng);
    $qw = imagesx($qr);
    $qh = imagesy($qr);
    $qx = (int) (($width - $qw) / 2);
    imagecopy($image, $qr, $qx, 200, 0, 0, $qw, $qh);
    imagedestroy($qr);

    // Teks di bawah QR
    $code = $member->member_code;
    $textX = (int) (($width - strlen($code) * 9) / 2);
    imagestring($image, 4, $textX, 375, $code, $black);

    $relativePath = 'member-cards/' . $member->member_code . '.png';
    $fullPath = storage_path('app/public/' . $relativePath);

    if (!is_dir(dirname($fullPath))) {
      mkdir(dirname($fullPath), 0755, true);
    }

    imagepng($image, $fullPath);
    imagedestroy($image);

    return $relativePath;
  }
}
