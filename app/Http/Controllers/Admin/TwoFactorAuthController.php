<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthController extends Controller
{
    public function show()
    {
        return view('admin.2fa.show');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey(auth('admin')->user()->two_factor_secret, $request->code);

        if ($valid) {
            session(['admin_2fa_verified' => true]);
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors(['code' => 'The provided code is invalid.']);
    }
}
