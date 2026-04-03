<?php

namespace Tests\Unit;

use Tests\TestCase;

class DompdfRuntimeConfigTest extends TestCase
{
    public function test_dompdf_uses_writable_runtime_directories(): void
    {
        $tempDir = config('dompdf.options.temp_dir');
        $fontDir = config('dompdf.options.font_dir');
        $fontCache = config('dompdf.options.font_cache');

        $this->assertIsString($tempDir);
        $this->assertIsString($fontDir);
        $this->assertSame($fontDir, $fontCache);
        $this->assertStringStartsWith(sys_get_temp_dir(), $tempDir);
        $this->assertStringStartsWith(sys_get_temp_dir(), $fontDir);
        $this->assertDirectoryExists($tempDir);
        $this->assertDirectoryExists($fontDir);
    }
}
