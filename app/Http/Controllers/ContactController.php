<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Contact;
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

        // 入力フォームにカテゴリー情報を渡してページ表示
        return view('contact.index', compact('categories'));
    }

    /**
     * お問い合わせフォーム確認ページ
     */
    public function confirm(StoreContactRequest $request)
    {
dd($request);
        // 入力データのバリデーション結果を連想配列に保存（bladeへの値渡し用）
        $validated = $request->validated();
        // Caterogyテーブルから入力されているカテゴリーIDから１レコードを抽出
        $category = Category::findOrFail(($request->category_id));

        // Confirm画面に推移
        return view('contact.confirm', compact('validated', 'category'));
    }

    /**
     * お問い合わせフォーム送信処理
     */
    public function store(StoreContactRequest $request)
    {
        // Contactsテーブルに保存
        Contact::create($request->validated());

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

            // 2. 各検索項目が入力されている場合だけwhere句を付与
            if ($request->filled('name')) {                     // 氏名（部分一致）
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->filled('email')) {                    // メールアドレス（完全一致）
                $query->where('email', $request->email);
            }

            if ($request->filled('gender')) {                   // 性別
                $query->where('gender', $request->gender);
            }

            if ($request->filled('category_id')) {              // カテゴリID
                $query->where('category_id', $request->category);
            }

            // 5. 日時（例：created_at が 2024-05-01 から 2024-05-31 まで）
            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->whereBetween('created_at', [
                    $request->date_from . ' 00:00:00',
                    $request->date_to   . ' 23:59:59',
                ]);
            }

            // 3. ソートと取得
            $contacts = $query
                ->orderBy('id', 'asc')   // id順に昇順で取得
                ->get();
        }

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

            // ヘッダー行のカラム構成
            $columnHeaders = [
                'id',               // ID
                'name',             // 氏名
                'gender',           // 性別
                'email',            // メール
                'tel',              // 電話番号
                'address',          // 住所
                'building',         // 建物名
                'category_id',      // カテゴリ
                'detail',           // 内容
                'created_at',       // 作成日時
            ];

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
                // 性別を文字列化
                $genderText = $genderMap[$contact->gender] ?? '';

                // カテゴリ名（存在しない場合は空文字）
                $categoryText = $contact->category ? $contact->category->content : '';

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