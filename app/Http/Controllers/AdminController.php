<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * 管理画面を表示する処理
     */
    public function index(Request $request)
    {
        /**
         * 管理画面に表示する情報を取得
         */
        // カテゴリーテーブル情報を全件取得
        $categories = Category::all();

        // タグテーブル情報を全件取得
        $tags = Tag::all();

        /**
         * 検索処理
         */
        $keyword = $request->query('keyword');  // クエリパラメータから検索キーワードを取得

        $gender = $request->query('gender');    // クエリパラメータから性別を取得

        $category_id = $request->query('category_id');  // クエリパラメータからカテゴリーIDを取得

        $date = $request->query('date');        // クエリパラメータから日付を取得

        $query = Contact::query();              // Contactモデルのクエリビルダを取得

        if (!empty($keyword)) {                 // キーワードが空でない場合

            if (Str::contains($keyword, ' ')) {  // キーワードにスペースが含まれている場合

                $keywords = explode(' ', $keyword);  // スペースでキーワードを分割

                foreach ($keywords as $word) {

                    $query->where(function ($q) use ($word) {               // クエリビルダに姓と名の両方の部分一致検索条件を追加
                        $q->where('first_name', 'like', '%'.$word.'%')
                          ->orWhere('last_name', 'like', '%'.$word.'%');
                    });
                }
            } else {                            // キーワードにスペースが含まれていない場合
                if (filter_var($keyword, FILTER_VALIDATE_EMAIL)) {    // キーワードがメールアドレスの形式の場合
                    $query->where('email', 'like', '%'.$keyword.'%');       // クエリビルダにメールアドレスの部分一致検索条件を追加
                } else {
                    $query->where('first_name', 'like', '%'.$keyword.'%')   // クエリビルダに名の部分一致検索条件を追加
                          ->orWhere('last_name', 'like', '%'.$keyword.'%'); // クエリビルダに姓の部分一致検索条件を追加
                }
            }
        }

        if ($gender) {
            $query->where('gender', $gender);               // クエリビルダに性別の完全一致検索条件を追加
        }

        if ($category_id) {
            $query->where('category_id', $category_id);     // クエリビルダにカテゴリーIDの完全一致検索条件を追加
        }

        if ($date) {
            $query->where('created_at', '>=', $date);       // クエリビルダに作成日時が指定日以降の検索条件を追加
        }

        // クエリビルダを実行してお問い合わせ情報を7件づつ取得
        $contacts = $query->with(['category', 'tags'])->paginate(7);

        // 管理画面を表示する
        return view('admin.index', compact('contacts', 'categories', 'tags'));
    }

    /**
     * お問い合わせ詳細表示画面へ推移する処理
     */
    public function show(string $id)
    {
        $contact = Contact::find($id);  // お問い合わせテーブルから対象レコードを取り出す

        // NULLチェック
        if (!$contact) {
            abort(404, '該当情報のレコードが見つかりません');
        }

        // お問い合わせ詳細画面に推移
        return view('admin.show', compact('contact'));
    }

    /**
     * お問い合わせの削除処理
     */
    public function destroy(string $id)
    {
        // 削除対象レコードをテーブルから取り出す
        $contact = Contact::find($id);

        // NULLチェック
        if (!$contact) {
            abort(404, '削除対象のレコードが存在しません');
        }

        // レコードを削除
        $contact->delete();

        // 管理画面に戻る
        return redirect()->route('admin.index');
    }
}
