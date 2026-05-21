<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Http\Request;

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
        // お問い合わせ情報を7件づつ取得
        // $contacts = Contact::orderBy('id')->paginate(7);
        // カテゴリーテーブル情報を全件取得
        $categories = Category::all();
        // タグテーブル情報を全件取得
        $tags = Tag::all();

        /**
         * 検索処理
         */
        $keyword = $request->query('keyword');
        $gender = $request->query('gender');
        $category_id = $request->query('category_id');
        $date = $request->query('date');

        $query = Contact::query();

        if ($keyword) {
            $query->where('last_name', 'like', '%'.$keyword.'%');
        }
        if ($gender) {
            $query->where('gender', $gender);
        }
        if ($category_id) {
            $query->where('category_id', $category_id);
        }
        if ($date) {
            $query->where('created_at', '>=', $date);
        }
        $contacts = $query->with(['category', 'tags'])->paginate(7);

        // 管理画面を表示する
        return view('admin.index', compact('contacts', 'categories', 'tags'));
    }

    /**
     * お問い合わせ詳細表示画面へ推移する処理
     */
    public function show(string $id)
    {
        $contact = Contact::find($id);

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
