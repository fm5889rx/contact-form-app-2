<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Contact;
use App\Http\Requests\StoreContactRequest;

class ContactController extends Controller
{
    /**
     * お問い合わせフォーム入力ページ
     */
    public function index()
    {
        // Categoriesテーブルを全件読み込む
        $categories = Category::all();

        // 入力フォームにカテゴリー情報を渡してページ表示
        return view('contact.index', compact('categories'));
    }

    /**
     * お問い合わせフォーム確認ページ
     */
    public function confirm(StoreContactRequest $request)
    {
        // 入力データのバリデーション結果を連想配列に保存（bladeへの値渡し用）
        $validated = $request->validated();
        // Caterogyテーブルから入力されているカテゴリーIDから１レコードを抽出
        $category = Category::find($request->category_id);

        // Confirm画面に推移
        return view('contact.confirm', compact('validated', 'category'));
    }

    /**
     * サンクスページ
     */
    public function thanks(StoreContactRequest $request)
    {
        // Contactsテーブルに保存
        Contact::create($request->validated());

        // サンクスページに移行
        return view('contact.thanks');
    }
}