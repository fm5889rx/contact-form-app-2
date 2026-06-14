<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use App\Http\Requests\StoreContactRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
                            $q->where('first_name', 'like', '%' . $word . '%')
                                ->orWhere('last_name', 'like', '%' . $word . '%');
                        });
                    }
                } else {                            // キーワードにスペースが含まれていない場合
                    if (filter_var($keyword, FILTER_VALIDATE_EMAIL)) {    // キーワードがメールアドレスの形式の場合
                        $query->where('email', 'like', '%' . $keyword . '%');       // クエリビルダにメールアドレスの部分一致検索条件を追加
                    } else {
                        $query->where('first_name', 'like', '%' . $keyword . '%')   // クエリビルダに名の部分一致検索条件を追加
                            ->orWhere('last_name', 'like', '%' . $keyword . '%'); // クエリビルダに姓の部分一致検索条件を追加
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