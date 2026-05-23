<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\SUpport\Facades\Hash;

class AuthController extends Controller
{
   /**
     * ユーザーログインの処理
     */
    public function login(LoginRequest $request)
    {
        // バリデーションはLoginRequestで行われるため、ここではリクエストが有効であることが保証されている

        // 認証の試行
        if (Auth::attempt($request->only('email', 'password'))) {
            // 認証成功
            return redirect()->route('admin.index')->with('success', 'ログインに成功しました。');
        }

        // 認証失敗
        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'メールアドレスまたはパスワードが正しくありません。']);
    }

    /**
     * 管理画面ログアウトの処理
     */
    public function logout()
    {
        return view('auth.login');      // ログイン画面を表示
    }

    /**
     * ユーザー登録の処理
     */
    public function register(RegisterRequest $request)
    {
        // バリデーションはRegisterRequestで行われるため、ここではリクエストが有効であることが保証されている

        // ユーザーの作成
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // ユーザーをログインさせる
        Auth::login($user);

        // 登録完了後のリダイレクト
        return redirect()->route('admin.index')->with('success', 'ユーザー登録が完了しました。');
    }
}
