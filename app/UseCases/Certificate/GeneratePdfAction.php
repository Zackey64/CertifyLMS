<?php

declare(strict_types=1);

namespace App\UseCases\Certificate;

use App\Models\Certificate;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use RuntimeException;

final class GeneratePdfAction
{
    public function __invoke(Certificate $certificate, string $path): void
    {
        $certificate->load(['user', 'certification']);

        $defaultConfig = (new ConfigVariables)->getDefaults();
        $defaultFontConfig = (new FontVariables)->getDefaults();

        $fontDirs = (new ConfigVariables)->getDefaults()['fontDir'];
        $fontData = (new FontVariables)->getDefaults()['fontdata'];

        $tempDir = storage_path('app/mpdf-temp');

        File::ensureDirectoryExists($tempDir);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'fontDir' => array_merge($fontDirs, [
                '/usr/share/fonts/opentype/ipafont-gothic',
            ]),
            'fontdata' => $fontData + [
                'ipag' => [
                    'R' => 'ipag.ttf',
                ],
            ],
            'default_font' => 'ipag',
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        $html = view('certificates.pdf', [
            'certificate' => $certificate,
        ])->render();

        $mpdf->WriteHTML($html);

        $content = $mpdf->Output('', Destination::STRING_RETURN);

        $saved = Storage::disk('private')->put($path, $content);

        if (! $saved) {
            throw new RuntimeException('修了証 PDF の保存に失敗しました。');
        }
    }
}
