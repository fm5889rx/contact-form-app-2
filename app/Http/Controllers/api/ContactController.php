<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApiStoreContactRequest;
use App\Http\Requests\ApiIndexContactRequest;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ApiIndexContactRequest $request): AnonymousResourceCollection
    {
        /**
         * 検索処理
         */
        // バリデーションの実行
        $request->validated();

        // クエリビルダーを作成
        $query = Contact::query();

        // クエリビルダーに条件を追加していく
        // ID検索
        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        // キーワード検索の処理
        $keyword = $request->filled('keyword') ? trim($request->keyword) : null;

        if ($keyword) {
            // 氏名の検索
            $query->where(function ($q) use ($keyword) {
                // 文字列を空白で分割（「田中 太郎」→ ['田中','太郎']）
                $words = preg_split('/\s+/', trim($keyword));

                // 分割したそれぞれの文字列で氏名を部分一致検索
                foreach ($words as $w) {
                    // 氏名（first_name, last_name）→ 部分一致
                    $q->where(function ($q2) use ($w) {
                        $q2->orWhere('first_name', 'like', "%{$w}%")
                            ->orWhere('last_name',  'like', "%{$w}%");
                    });
                }

            })->orWhere('email', $keyword);     // メールアドレスは完全一致
        }

        // それ以外の検索条件を作る
        // 性別
        $query->when($request->filled('gender'),  fn($q) => $q->where('gender', $request->gender));

        // カテゴリーID
        $query->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->category_id));

        // 作成日
        $query->when($request->filled('date'), fn($q) => $q->whereBetween(
            'created_at',
            [$request->date . ' 00:00:00',
            $request->date . ' 23:59:59']));

        // タグID
        $query->when($request->filled('tag_id'), fn($q) => $q->whereHas('tags', fn($q2) => $q2->where('id', $request->tag_id)));

        // ページネーションを適用するページ数を計算
        $perpage = $request->per_page ?? 7; // デフォルトは7件

        
        // ID順に並び替えて取得
        $contacts = $query->with('tags')->orderBy('id', 'asc')->paginate($perpage);
        
        // レコードが見つからない場合は404エラーを返す
        if ($contacts->total() === 0) {
            return abort(404, 'レコードが見つかりません');
        }

        // APIレスポンスとしてContactResourceのコレクションを返す
        return ContactResource::collection($contacts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ApiStoreContactRequest $request)
    {
        // バリデーションを実行してテーブルを作成
        $contact = Contact::create($request->validated());

        // タグの保存
        if ($request->filled('tags')) {
            $contact->tags()->sync($request->tags);
        }

        // 作成したレコードをContactResourceで整形して返す
        return new ContactResource($contact)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // IDでレコードを検索して取得
        // レコードが見つからない場合は404エラーを返す
        try {
            $contact = Contact::FindOrFail($id);
        } catch (ModelNotFoundException $e) {
            return abort(404, 'レコードが見つかりません');
        }

        // APIレスポンスとしてContactResourceを返す
        return response()->json($contact);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ApiStoreContactRequest $request, string $id)
    {
        // IDでレコードを検索して取得
        // レコードが見つからない場合は404エラーを返す
        try {
            $contact = Contact::findOrFail($id);
        }
        catch (ModelNotFoundException $e) {
            return abort(404, 'レコードが見つかりません');
        }

        // バリデーションを実行してテーブルを更新
        $contact->update($request->validated());

        // タグの保存
        if ($request->filled('tags')) {
            $contact->tags()->sync($request->tags);
        }
    
        // 更新したレコードをContactResourceで整形して返す
        return new ContactResource($contact)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // IDでレコードを検索して取得
        // レコードが見つからない場合は404エラーを返す
        try {
            $contact = Contact::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return abort(404, 'レコードが見つかりません');
        }

        // レコードを削除
        $contact->delete($id);

        // 204 No Contentのレスポンスを返す
        return response()->json(null, 204);
    }
}
