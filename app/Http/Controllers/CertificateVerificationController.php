<?php

namespace App\Http\Controllers;

use App\Models\WatchCertificate;
use App\Services\BlockchainService;
use Illuminate\View\View;

class CertificateVerificationController extends Controller
{
    public function show(string $hash, BlockchainService $blockchainService): View
    {
        $localCertificate = WatchCertificate::where('certificate_hash', $hash)
            ->with('watch')
            ->first();

        $consensus = $blockchainService->verifyConsensusAcrossNodes($hash);
        $isOwner = $localCertificate && auth()->id() === $localCertificate->user_id;
        $serial = $localCertificate?->serial_number;
        if ($isOwner) {
            $localCertificate->load('user');
        } elseif ($serial !== null) {
            $serial = preg_replace('/^(SN-W\d+)-O\d+-I\d+-/', '$1-O0-I0-', $serial);
        }

        return view('certificates.show', [
            'hash' => $hash,
            'certificate' => $localCertificate,
            'consensus' => $consensus,
            'isOwner' => $isOwner,
            'serial' => $serial,
            'title' => __('messages.cert_title', ['hash' => substr($hash, 0, 8).'...']),
            'subtitle' => __('messages.cert_subtitle'),
        ]);
    }
}
