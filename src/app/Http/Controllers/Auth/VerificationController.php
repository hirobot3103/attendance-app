<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class VerificationController extends Controller
{
    public function notice(Request $request)
    {
        $user = $request->user();
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        return view('auth.verify-email', ['verificationUrl' => $verificationUrl]);
    }

    public function resend(Request $request)
    {
        // 既に認証済みの場合
        if ($request->user()->hasVerifiedEmail()) {
            return redirect('/email/verify')->with('status_done', 'すでにメール認証が完了しています。');
        }

        // 認証メールの再送信
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status_resend', '確認メールを再送信しました。');
    }
}
