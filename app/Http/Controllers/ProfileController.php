<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\DonateLog;
use App\Models\Setting;
use App\Models\SRO\Account\SkSilk;
use App\Models\SRO\Account\TbUser;
use App\Models\SRO\Portal\AphChangedSilk;
use App\Models\SRO\Portal\MuEmail;
use App\Models\SRO\Portal\MuhAlteredInfo;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;

class ProfileController extends Controller
{
    public function index(Request $request): View
    {
        $characterRace = config('ranking.character_race');
        $vipLevel = config('ranking.vip_level');

        if (config('global.server.version') === 'vSRO') {
            $characterImage = config('ranking.character_image_vsro');
        }else {
            $characterImage = config('ranking.character_image');
        }

        return view('profile.index', [
            'user' => $request->user(),
            'characterImage' => $characterImage,
            'characterRace' => $characterRace,
            'vipLevel' => $vipLevel,
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        if (config('settings.update_type') == 'verify_code') {
            $email = $request->input('new_email');
            $codeRecord = DB::table('password_reset_tokens')->where('email', $request->user()->email)->first();

            if (!$codeRecord || !($request->input('verify_code_email') === $codeRecord->token) || Carbon::parse($codeRecord->created_at)->addMinutes(30)->isPast()) {
                return back()->withErrors(['verify_code_email' => 'The provided verification code is invalid or expired.']);
            }

            $request->user()->email = $email;
            $request->user()->email_verified_at = null;
            $request->user()->save();
        }else {
            $email = $request->input('email');
            $request->user()->fill($request->validated());

            if ($request->user()->isDirty('email')) {
                $request->user()->email_verified_at = null;
            }

            $request->user()->save();
        }

        DB::beginTransaction();
        try {

            if (config('global.server.version') === 'vSRO') {
                TbUser::where('JID', $request->user()->jid)->update(['Email' => $email]);
            }else {
                MuEmail::where('JID', $request->user()->jid)->update(['EmailAddr' => $email]);

                if (config('settings.register_confirm')) {
                    MuhAlteredInfo::where('JID', $request->user()->jid)->update(['EmailAddr' => $email, 'EmailReceptionStatus' => 'N', 'EmailCertificationStatus' => 'N']);
                } else {
                    MuhAlteredInfo::where('JID', $request->user()->jid)->update(['EmailAddr' => $email, 'EmailReceptionStatus' => 'Y', 'EmailCertificationStatus' => 'Y']);
                }
            }

            if (config('settings.update_type') == 'verify_code') {
                DB::table('password_reset_tokens')->where('email', $request->user()->email)->delete();
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['email' => ["Something went wrong, Please try again later."]]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function send_code(Request $request)
    {
        $user = $request->user();
        $code = random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => $code, 'created_at' => now()]
        );

        Mail::raw("Your verification code is: $code", function ($message) use ($user) {
            $message->to($user->email)->subject('Email Change Verification Code');
        });

        return back()->with('status', $request->input('send-verify-code-name'));
    }

    public function update_settings(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Settings updated!');
    }

    public function donate(Request $request): View
    {
        return view('profile.donate', [
            'user' => $request->user(),
        ]);
    }

    public function silk_history(Request $request): View
    {
        $page = $request->get('page', 1);
        $data = AphChangedSilk::getSilkHistory($request->user()->jid, 25, $page);

        return view('profile.silk-history', [
            'user' => $request->user(),
            'data' => $data,
        ]);
    }

    public function passcode(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $tbUser = TbUser::where('password', md5($request->password))->first();
        if ($tbUser) {
            DB::connection('account')->statement("DELETE FROM SILKROAD_R_ACCOUNT.._SecondaryPassword WHERE UserJID = ?", [$tbUser->JID]);
            return redirect()->back()->with('passcode_success', 'Your secondary password has been reset successfully!');
        }

        return redirect()->back()->with('passcode_error', 'Invalid password provided. Please try again.');
    }
    public function redeem(Request $request)
    {
        $request->validate([
            'voucher_code' => 'required|string',
        ]);

        $voucher = Voucher::where('code', $request->voucher_code)->first();

        if (!$voucher) {
            return redirect()->back()->with('voucher_error', 'Invalid voucher code.');
        }

        if ($voucher->status) {
            return redirect()->back()->with('voucher_error', 'This voucher has already been used.');
        }

        if ($voucher->valid_date && Carbon::now()->greaterThan($voucher->valid_date)) {
            return redirect()->back()->with('voucher_error', 'This voucher has expired.');
        }

        $user = Auth::user();

        if (config('global.server.version') === 'vSRO') {
            SkSilk::setSkSilk($user->jid, $voucher->type, $voucher->amount);
        } else {
            AphChangedSilk::setChangedSilk($user->jid, $voucher->type, $voucher->amount);
        }

        DonateLog::setDonateLog('Voucher', (string) Str::uuid(), 'true', 0, $voucher->amount, "User:{$user->username} Has Redeemed:{$voucher->code}", $user->jid, $request->ip());
        $voucher->update(['user_id' => $user->jid, 'status' => true]);

        return redirect()->back()->with('voucher_success', 'Voucher redeemed successfully!');
    }
}
