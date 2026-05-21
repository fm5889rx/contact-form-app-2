<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tag;
use App\Http\Requests\TagRequest;

class TagController extends Controller
{
    /**
     * タグの追加
     */
    public function store(TagRequest $request)
    {
        // tagsテーブルに保存
        Tag::create($request->validated());

        // 管理画面に戻る
        return redirect()->route('admin.index');
    }

    /**
     * 管理画面でタグ編集ボタンを押した時の処理
     */
    public function edit(string $id)
    {
        // 指定されたidのレコードをテーブルから取り出す
        $tag = Tag::find($id);

        // タグ編集画面を呼び出す
        return view('admin.tags.edit', compact('tag'));
    }

    /**
     * タグ編集画面で更新ボタンを押した時の処理
     */
    public function update(TagRequest $request, string $id)
    {
        // 更新対象レコードをテーブルから取り出す
        $tag = Tag::find($id);

        // NULLチェック
        if (!$tag) {
            abort(404, '更新対象のレコードが存在しません');
        }

        // バリデーション付きでレコードを更新
        $tag->update($request->validated());

        // 管理画面に戻る
        return redirect()->route('admin.index');
    }

    /**
     * タグの削除処理
     */
    public function destroy(string $id)
    {
        // 削除対象レコードをテーブルから取り出す
        $tag = Tag::find($id);

        // NULLチェック
        if (!$tag) {
            abort(404, '削除対象のレコードが存在しません');
        }

        // レコードを削除
        $tag->delete();

        // 管理画面に戻る
        return redirect()->route('admin.index');
    }
}
