<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateController extends Controller
{
    /**
     * 修了証 PDF をダウンロードする。
     */
    public function download(Certificate $certificate): RedirectResponse|StreamedResponse
    {
        $this->authorize('download', $certificate);

        $disk = Storage::disk('private');
        if (! $disk->exists($certificate->pdf_path)) {
            return redirect()->back()->with('error', '修了証 PDF が見つかりません。');
        }

        return $disk->download(
            $certificate->pdf_path,
            '修了証.pdf',
        );
    }
}
