<?php

namespace Tests\Unit;

use App\Services\QrCodeService;
use PHPUnit\Framework\TestCase;

class QrCodeServiceTest extends TestCase
{
    public function test_format_tlv(): void
    {
        $tlv = QrCodeService::formatTlv('00', '01');
        $this->assertEquals('000201', $tlv);

        $tlvName = QrCodeService::formatTlv('59', 'BOOK STORE');
        $this->assertEquals('5910BOOK STORE', $tlvName);
    }

    public function test_crc16_emvco(): void
    {
        $crc = QrCodeService::crc16('0002010102116304');
        $this->assertIsString($crc);
        $this->assertEquals(4, strlen($crc));
    }

    public function test_generate_qris_payload(): void
    {
        $payload = QrCodeService::generateQrisPayload('INV-20261124-001', 125000);

        $this->assertStringStartsWith('000201010212', $payload);
        $this->assertStringContainsString('BOOK STORE INDONESIA', $payload);
        $this->assertStringContainsString('ID1020039482910', $payload);
        $this->assertStringContainsString('INV-20261124-001', $payload);
        $this->assertStringContainsString('5406125000', $payload);
        $this->assertStringContainsString('5303360', $payload);
        $this->assertStringContainsString('5802ID', $payload);
    }

    public function test_generate_svg(): void
    {
        $payload = QrCodeService::generateQrisPayload('INV-20261124-001', 125000);
        $svg = QrCodeService::generateSvg($payload, 280);

        $this->assertStringStartsWith('<svg', $svg);
        $this->assertStringEndsWith('</svg>', trim($svg));
        $this->assertStringContainsString('viewBox=', $svg);
        $this->assertStringContainsString('width="280"', $svg);
        $this->assertStringContainsString('height="280"', $svg);
        $this->assertStringContainsString('<path fill="#0f172a"', $svg);
    }
}
