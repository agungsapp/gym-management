<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class FonnteService
{
  private string $apiUrl;
  private string $token;
  private ?string $defaultTarget;

  public function __construct(?string $token = null, ?string $defaultTarget = null, ?string $apiUrl = null)
  {
    $this->apiUrl = $apiUrl ?? config('services.fonnte.url', 'https://api.fonnte.com/send');
    $this->token = $token ?? config('services.fonnte.token', '');
    $this->defaultTarget = $defaultTarget ?? config('services.fonnte.target');
  }

  /**
   * Normalisasi nomor ke format 62...
   */
  public function normalizePhone(string $phone): string
  {
    $target = preg_replace('/[^0-9]/', '', $phone);

    if (str_starts_with($target, '0')) {
      $target = '62' . substr($target, 1);
    }

    return $target;
  }

  /**
   * Kirim pesan teks saja
   */
  public function kirimPesanWhatsApp(string $pesan, ?string $targetPhone = null): string|false
  {
    $target = $targetPhone
      ? $this->normalizePhone($targetPhone)
      : $this->defaultTarget;

    if (!$target) {
      Log::warning('Fonnte: target kosong, pesan tidak dikirim.');
      return false;
    }

    Log::info("Fonnte kirim teks ke {$target}: {$pesan}");

    $curl = curl_init();

    curl_setopt_array($curl, [
      CURLOPT_URL => $this->apiUrl,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => [
        'target' => $target,
        'message' => $pesan,
      ],
      CURLOPT_HTTPHEADER => [
        "Authorization: {$this->token}",
      ],
    ]);

    $response = curl_exec($curl);

    if ($response === false) {
      Log::error('Fonnte curl error: ' . curl_error($curl));
    }

    curl_close($curl);
    Log::info('Fonnte response: ' . $response);

    return $response;
  }

  /**
   * Kirim pesan + gambar (member card)
   */
  public function kirimPesanDenganGambar(string $pesan, string $filePath, string $targetPhone): string|false
  {
    if (!file_exists($filePath)) {
      Log::error("Fonnte: file tidak ditemukan: {$filePath}");
      return false;
    }

    $target = $this->normalizePhone($targetPhone);

    Log::info("Fonnte kirim gambar ke {$target}: {$filePath}");

    $curl = curl_init();

    curl_setopt_array($curl, [
      CURLOPT_URL => $this->apiUrl,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => 60,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => [
        'target' => $target,
        'message' => $pesan,
        'file' => new \CURLFile($filePath, 'image/png', basename($filePath)),
      ],
      CURLOPT_HTTPHEADER => [
        "Authorization: {$this->token}",
      ],
    ]);

    $response = curl_exec($curl);

    if ($response === false) {
      Log::error('Fonnte curl error: ' . curl_error($curl));
    }

    curl_close($curl);
    Log::info('Fonnte response: ' . $response);

    return $response;
  }
}
