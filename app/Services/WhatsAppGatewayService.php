<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppGatewayService
{
  private string $baseUrl;
  private ?string $apiKey;

  public function __construct(?string $baseUrl = null, ?string $apiKey = null)
  {
    $this->baseUrl = rtrim($baseUrl ?? config('services.wa_gateway.url'), '/');
    $this->apiKey = $apiKey ?? config('services.wa_gateway.api_key');
  }

  private function headers(): array
  {
    $headers = [
      'Accept' => 'application/json',
    ];

    if ($this->apiKey) {
      $headers['X-API-Key'] = $this->apiKey;
      // Kalau gateway kamu pakai Authorization Bearer, ganti ke:
      // $headers['Authorization'] = 'Bearer ' . $this->apiKey;
    }

    return $headers;
  }

  /**
   * Normalisasi ke 628...
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
   * Kirim teks saja
   * POST /send-text
   * { "phone": "628...", "message": "..." }
   */
  public function sendText(string $message, string $phone): array|false
  {
    $phone = $this->normalizePhone($phone);

    try {
      $response = Http::withHeaders($this->headers())
        ->timeout(30)
        ->post($this->baseUrl . '/send-text', [
          'phone' => $phone,
          'message' => $message,
        ]);

      Log::info('WA Gateway send-text', [
        'phone' => $phone,
        'status' => $response->status(),
        'body' => $response->body(),
      ]);

      if (!$response->successful()) {
        return false;
      }

      return $response->json() ?? ['raw' => $response->body()];
    } catch (\Throwable $e) {
      Log::error('WA Gateway send-text error: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Kirim media dari file lokal (multipart)
   * POST /send-media
   * -F phone=... -F caption=... -F file=@...
   */
  public function sendMediaFromFile(string $filePath, string $phone, ?string $caption = null): array|false
  {
    if (!file_exists($filePath)) {
      Log::error("WA Gateway: file tidak ada: {$filePath}");
      return false;
    }

    $phone = $this->normalizePhone($phone);

    try {
      $request = Http::withHeaders($this->headers())
        ->timeout(60)
        ->attach(
          'file',
          file_get_contents($filePath),
          basename($filePath)
        );

      $response = $request->post($this->baseUrl . '/send-media', array_filter([
        'phone' => $phone,
        'caption' => $caption,
      ]));

      Log::info('WA Gateway send-media (file)', [
        'phone' => $phone,
        'file' => $filePath,
        'status' => $response->status(),
        'body' => $response->body(),
      ]);

      if (!$response->successful()) {
        return false;
      }

      return $response->json() ?? ['raw' => $response->body()];
    } catch (\Throwable $e) {
      Log::error('WA Gateway send-media file error: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Kirim media dari URL
   * POST /send-media JSON
   * { "phone": "...", "url": "...", "caption": "..." }
   */
  public function sendMediaFromUrl(string $url, string $phone, ?string $caption = null): array|false
  {
    $phone = $this->normalizePhone($phone);

    try {
      $response = Http::withHeaders($this->headers())
        ->timeout(60)
        ->post($this->baseUrl . '/send-media', array_filter([
          'phone' => $phone,
          'url' => $url,
          'caption' => $caption,
        ]));

      Log::info('WA Gateway send-media (url)', [
        'phone' => $phone,
        'url' => $url,
        'status' => $response->status(),
        'body' => $response->body(),
      ]);

      if (!$response->successful()) {
        return false;
      }

      return $response->json() ?? ['raw' => $response->body()];
    } catch (\Throwable $e) {
      Log::error('WA Gateway send-media url error: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Alias biar mirip pemakaian Fonnte lama
   */
  public function kirimPesanWhatsApp(string $pesan, string $targetPhone): array|false
  {
    return $this->sendText($pesan, $targetPhone);
  }

  public function kirimPesanDenganGambar(string $pesan, string $filePath, string $targetPhone): array|false
  {
    return $this->sendMediaFromFile($filePath, $targetPhone, $pesan);
  }
}
