<?php

namespace App\Http\Controllers;

use App\Models\WatchCertificate;
use App\Services\BlockchainService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificateVerificationController extends Controller
{
    public function show(string $hash, BlockchainService $blockchainService): View
    {
        $localCertificate = WatchCertificate::where('certificate_hash', $hash)
            ->with(['watch', 'order', 'user'])
            ->first();

        $consensus = $blockchainService->verifyConsensusAcrossNodes($hash);

        return view('certificates.show', [
            'hash' => $hash,
            'certificate' => $localCertificate,
            'consensus' => $consensus,
            'title' => __('messages.cert_title', ['hash' => substr($hash, 0, 8).'...']),
            'subtitle' => __('messages.cert_subtitle'),
        ]);
    }
}
