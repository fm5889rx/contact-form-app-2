<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use App\Http\Requests\StoreContactRequest;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * お問い合わせフォーム入力ページ
     */
    public function index()
    {
        // Categoriesテーブルを全件読み込む
        $categories = Category::all();

        // tagsテーブルを全件読み込む
        $tags = Tag::all();

        // 入力フォームにカテゴリー情報を渡してページ表示
        return view('contact.index', compact('categories', 'tags'));
    }

    /**
     * お問い合わせフォーム確認ページ
     */
    // GET /contacts/confirmをリダイレクトするためのワンクッション
    public function confirmView()
    {
        return view('contact.confirm', [
            'validated' => session('validated'),
            'category'  => session('category'),
            'tags'      => session('tags'),
        ]);
    }
    // POSTメッセージ処理
    public function confirm(StoreContactRequest $request)
    {
        // 入力データのバリデーション結果を連想配列に保存（bladeへの値渡し用）
        $validated = $request->validated();

        // Caterogyテーブルから入力されているカテゴリーIDから１レコードを抽出
        $category = Category::findOrFail(($request->category_id));

        // タグIDの配列を取得
        $tagIds = $request->input('tag_ids', []);  // 'tags'はフォームのタグ入力のname属性に合わせる
        // タグIDの配列からTagモデルのレコードを取得
        $tags = Tag::whereIn('id', $tagIds)->get();

        // Confirm画面に推移
        return redirect()->route('contacts.confirm')
                ->with([
                    'validated' => $validated,
                    'category'  => $category,
                    'tags'      => $tags,
                ]);
    }

    /**
     * お問い合わせフォーム送信処理
     */
    public function store(StoreContactRequest $request)
    {
        // 入力データのバリデーション結果を連想配列に保存（タグの保存のため）
        $validated = $request->validated();

        // Contactsテーブルに保存
        $contact = Contact::create($validated);

        // syncメソッドでタグIDの配列を保存（既存のタグとの関連も更新される）
        $contact->tags()->sync($validated['tag_ids'] ?? []);

        // サンクスページに移行
        return redirect('/thanks');
    }

    /**
     * サンクスページ
     */
    public function thanks()
    {
        // サンクスページに移行
        return view('contact.thanks');
    }

    /**
     * お問い合わせデータを CSV でダウンロード（BOM付き）
     */
    public function export(Request $request)
    {
        if (!$request->query()) {       // 検索条件未指定ならば
            // 全件取得＆並び替え
            $contacts = Contact::orderBy('created_at', 'desc')->get();
        } else {                        // 検索条件の指定があるなら
            // 1. クエリビルダーを用意
            $query = Contact::query();

            // 氏名（部分一致）とメールアドレス（完全一致）にkeywordを使用するため、
            // keywordの存在をチェックしてからクエリに条件を追加
            $query->when($request->filled('keyword'), function ($q, $keyword) {
                // ① 文字列を空白で分割（「田中 太郎」→ ['田中','太郎']）
                $words = preg_split('/\s+/', trim($keyword));

                // ② すべての単語を「氏名」カラムで検索
                // ③ かつ「電子メール」も検索対象に追加（OR 条件）
                $q->where(function ($q2) use ($words) {
                    foreach ($words as $w) {
                        // 氏名（first_name, last_name）→ 部分一致
                        $q2->where(function ($q3) use ($w) {
                            $q3->orWhere('first_name', 'like', "%{$w}%")
                                ->orWhere('last_name',  'like', "%{$w}%");
                        });
                    }
                    // メールアドレスは完全一致（※メールの検索はキーワードと別入力の場合は下記 `email` で
                    // → 同じキーワードで検索したい場合はここに入れる）
                    $q2->orWhere('email', $keywords);
                });
            });


            if ($request->gender) {
                if ($request->filled('gender')) {                   // 性別
                    $query->where('gender', $request->gender);
                }
            }

            if ($request->filled('tel')) {                      // 電話番号（完全一致）
                $query->where('tel', $request->tel);
            }

            if ($request->filled('category_id')) {              // カテゴリID
                $query->where('category_id', $request->category_id);
            }

            // 5. 日時（例：created_at が 2024-05-01 から 2024-05-31 まで）
            if ($request->filled('date')) {
                $query->whereBetween('created_at', [
                    $request->date . ' 00:00:00',
                    $request->date. ' 23:59:59',
                ]);
            }

            // 3. ソートと取得
            $contacts = $query
                ->orderBy('created_at', 'desc')   // 作成日時順に降順で取得
                ->get();
        }
dump($query->toSql());  // クエリの内容を確認するためのダンプ
dd($contacts);
        // ダウンロードするファイル名
        $timestamp = now()->format('Ymd_His');
        $fileName = "お問い合わせ一覧_{$timestamp}.csv";

        // CSV 出力用のヘッダー (UTF‑8 BOM であることを明示)
        $headers = [
            'Content-Type'          => 'text/csv; charset=UTF-8',
            'Content-Disposition'   => "attachment; filename=\"{$fileName}\"",
        ];

        // 直接ストリームで返す
        return response()->streamDownload(function () use ($contacts) {
            // php://output を開く
            $output = fopen('php://output', 'w');

            // BOM を先頭に書き込む
            fputs($output, "\xEF\xBB\xBF");

            fputcsv($output, [   // CSVヘッダー行を出力
                    'ID',
                    '氏名',
                    '性別',
                    'メール',
                    '電話番号',
                    '住所',
                    '建物名',
                    'カテゴリ',
                    '内容',
                    '作成日時'
                ]);

            /* ---- 2 行目以降（データ） ---- */
            $genderMap = [              // 性別文字列に変換
                1 => '男性',
                2 => '女性',
                3 => 'その他',
            ];

            // 1 行ずつ CSV へ書き込む
            foreach ($contacts as $contact) {
                // 氏名を結合
                $contact->name = $contact->last_name . ' ' . $contact->first_name;

                // 性別を文字列化
                $genderText = $genderMap[$contact->gender] ?? '';

                // カテゴリ名（存在しない場合は空文字）
                $categoryText = $contact->category ? $contact->category->content : '';

                // CSV行を出力
                fputcsv($output, [
                    $contact->id,
                    $contact->name,
                    $genderText,
                    $contact->email,
                    $contact->tel,
                    $contact->address,
                    $contact->building,
                    $categoryText,
                    $contact->detail,
                    $contact->created_at,
                ]);
            }

            fclose($output);            // CSVファイルをクローズ
        }, $fileName, $headers);
    }
}