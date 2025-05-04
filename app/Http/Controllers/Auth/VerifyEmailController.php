<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SRO\Portal\MuhAlteredInfo;
use Exception;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('profile', absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {

            if (config('global.general.server.version') !== 'vSRO') {
                DB::beginTransaction();
                try {
                    MuhAlteredInfo::where('JID', $request->user()->jid)->update(['EmailReceptionStatus' => 'Y', 'EmailCertificationStatus' => 'Y']);

                } catch (Exception $e) {
                    DB::rollBack();
                }
                DB::commit();
            }

            event(new Verified($request->user()));
        }

        return redirect()->intended(route('profile', absolute: false).'?verified=1');
    }
}
