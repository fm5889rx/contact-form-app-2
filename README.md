# 確認テスト README.md

## A.プロジェクト名
新お問い合わせフォーム

### 開発者
森　太

## B.概要

### 目的
確認テストとして、「新お問い合わせフォーム」の仕様書が提供されたので、<br>
仕様書を基に以下の機能を実装しました。<br>
**なお、bladeファイルは仕様書と共に提供されました。**

- お問い合わせ入力フォーム<br>
　 http://localhost で表示。<br>
　 日本語の姓名順になるように提供bladeファイルを修正。

- お問い合わせ内容確認フォーム<br>
　 入力フォームの確認画面ボタンで表示。<br>
　 送信ボタンの押下でContactsテーブルに保存。<br>
　 日本語の姓名順になるように提供bladeファイルを修正。

- サンクスフォーム<br>
　 テーブル保存後サンクスフォームに移行。<br>
　 HOMEボタンで入力フォームの初期画面に戻る。

- 管理画面<br>
　 http://localhost/admin で表示。<br>
　 登録済みのお問い合わせ内容を一覧表示。7件/ページで表示。<br>
　 名前、メールアドレス、お問い合わせカテゴリ、日付で検索し、<br>
　 検索条件にマッチしたテーブル内容を再表示。<BR>
　 表示行にある詳細ボタンで塞画面を表示。<BR>
　 日本語の姓名順になるようにbladeファイルを修正。

- お問い合わせ詳細フォーム<br>
　 選択したお問い合わせ情報を表示。<br>
　 フォーム内の削除ボタンでお問い合わせ情報をテーブルから削除。<br>
　 日本語の姓名順になるようにbladeファイルを修正。

- タグ編集ページ<br>
　 管理画面の下半分に配置。<br>
　 tagsテーブルの内容を一覧表示、<br>
　 入力ボックスで入力した文字列を新規タグとしてテーブルに登録。<br>
　 編集ボタンを押すとタグ編集画面を表示。<br>
　 削除ボタンで該当タグを削除。

- ログイン画面<br>
　 http://localhodt/login で表示。<br>
　 ログインボタンで管理画面を表示。

- 管理者登録画面<br>
　 http://localhodt/register で表示。<br>
　 登録ボタンで管理者ユーザ情報をusersテーブルに保存。

- 【応用】CSVエクスポート<br>
　 管理画面のエクスポートボタンを押すと、現在一覧表示されている<br>
　 お問い合わせデータをCSVファイルに出力。<br>
　 CSVファイルはシステム規定のダウンロードフォルダに、”お問い合わせ一覧_yyyymmdd_HHMMSS.csv”の<br>
　 ユニークなファイル名で保存。<br>
　 検索条件をつけると、一致したレコードだけエクスポートされる。
<br>


## C.ＥＲ図

```mermaid
erDiagram
  contacts||--o{ categories : "1つの問い合わせは1つのカテゴリーを持つ"
  contacts ||--o{ contact_tag : "1つの問い合わせは0以上のタグを持つ"
  tags ||--o{ contact_tag : "複数の問い合わせは0以上のタグを持つ"

  users {
    bigint id PK
    varchar(255) name
    varchar(255) email
    timestamp email_verified_at
    varchar(100) remenber_token
    timestamp created_at
    timestamp updated_at
  }

  categories {
    bigint id PK
    varchar(255) content
    timestamp created_at
    timestamp updated_at
  }

  contacts {
    bigint id PK
    bigint category_id FK
    varchar(255) first_name
    varchar(255) last_name
    tinyint gender
    varchar(255) email
    varchar(11) tel
    varchar(255) address
    varchar(255) building
    varchar(120) datail
    timestamp created_at
    timestamp updated_at
  }

  tags {
    bigint id PK
    varchar(59) name
    timestamp created_at
    timestamp updated_at
  }

  contact_tag {
    bigint id PK
    bigint contact_id FK
    bigint tag_id FK
    timestamp created_at
    timestamp updated_at
  }
```


## D.環境構築手順

新お問い合わせフォームを制作するための開発環境をセットアップします。<br>
以下の環境は事前に準備されているものとします。<br>
- Docker Desktop: Dockerを実行するためのツール<br>
- Git、GitHub: バージョン管理システム<br>
- テキストエディタ: VS Code推奨<br>
<br>

### Step0:ホームディレクトリに移動
ご自分の環境に合わせて、ホームディレクトリに移動します。<br>
この配下に開発環境が展開されます。<br>
```
cd ~/coachtech
```

### Step1:Laravelプロジェクトの作成
以下のDockerコマンドを実行して、Laravel 10プロジェクトを作成します。<br>
```
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
    laravelsail/php82-composer:latest \
    composer create-project laravel/laravel:^10.0 contact-form-app
```
ホームディレクトリ配下に`contact-form-app`フォルダが作成されます。<br>
プロジェクトに必要なファイル群はこのフォルダ（プロジェクトフォルダ）内に格納されます。<br>
<br>

### Step2:Laravel Sailのインストール
プロジェクト作成後、プロジェクトフォルダ（`contact-form-app`）移動し、Laravel Sailをインストールします。<br>

```
# プロジェクトディレクトリに移動
cd contact-form-app

# Laravel Sailをインストール
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
    laravelsail/php82-composer:latest \
    composer require laravel/sail --dev

# Sailの設定ファイルをパブリッシュ（MySQLを選択）
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    -e COMPOSER_CACHE_DIR=/tmp/composer_cache \
    laravelsail/php82-composer:latest \
    php artisan sail:install --with=mysql
```

### Step3:フロントエンドのセットアップ

1. エイリアス登録
```
echo "alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'" >> ~/.zshrc 
```

2. Sailの起動
初回起動時はダウンロードと環境構築を行うため、数分時間がかかりまさす。
```
sail up -d
```

3. Tailwind CSSのインストール
提供されたbladeファイルが正しく動作するようにTailwond CSSをインストールします。<br>

```
# NPM依存パッケージのインストール
sail npm install

# Tailwindo CSSのインストール
sail npm install -D tailwindcss@^3.4.0 postcss autoprefixer

# 設定ファイルの生成
sail npx tailwindcss init -p
```

4. Tailwind CSSのテンプレートパス設定
VSCodeを使って、Tailwind CSSに関係するファイルを以下の内容に変更します。<br>

```
# VSCode起動
code .
```

- ファイル: `tailwind.config.js`
```php
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
```

- ファイル: `resource/css/app.css`
```php
@tailwind base;
@tailwind components;
@tailwind utilities;
```

5. .envファイルとphpMyAdminの設定
開発中に使用するデータベース用ツールphpMyAdminを設定します。<br>

- `.env`ファイルの確認<br>
`.env`ファイルを開き、データベース説情報が以下と一致しているかを確認します。<br>
（開発環境構築直後は下記の通りになっています）<br>

ファイル: `.env`
```php
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

- ohoMyAdminの追加
`compose.yaml`を開き、`mysql`サービスの後に以下の設定を追加して保存します。<br>

ファイル: `compose.yaml`
```php
    phpmyadmin:
        image: 'phpmyadmin:latest'
        ports:
            - '${FORWARD_PHPMYADMIN_PORT:-8080}:80'
        environment:
            PMA_HOST: mysql
            PMA_USER: '${DB_USERNAME}'
            PMA_PASSWORD: '${DB_PASSWORD}'
        networks:
            - sail
        depends_on:
            - mysql
```

**注意：yamlファイルはインデントがずれると正しく動作しません。<br>
　　  `phpMyAdmin`のインデントをすでにある`mysql`と同じに揃えてください。**<br>

6. Sailの再起動と準備

- Sailの再起動
```
sail down
sail up -d
```

- アプリケーションキーの生成
```
sail artisan key:generate
```

### Step5.Viteサーバ起動

　新しいターミナルを開いて、開発サーバーを起動します。<br>
　**開発中は常に実行したままにしてください。**<br>

```
sail npm run dev
```

### Step6:Laravel Fortifyのセットアップ

1. Fortifyのインストール

 ```
 sail composer require laravel/fortify
 ```

2. Fortifyの設定ファイルを公開

```
sail artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"
```

3. サービスプロバイダの登録
`config/app.php`にFortifyサービスプロバイダを登録します。

```php
'providers' => [
    // ...既存のプロバイダ

    /*
     * Application Service Providers...
     */
    App\Providers\AppServiceProvider::class,
    // App\Providers\AuthServiceProvider::class,
    // App\Providers\BroadcastServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
    App\Providers\FortifyServiceProvider::class, // ← この行を追加
])->toArray(),
```

### Step6:動作確認

1. phpMyAdminの動作確認
ブラウザで`http://localhost:8080`にアクセスし、phpMyAdminが表示されることを確認します。


## E.使用技術
- OS：mscOS Tahoe 26.4.1(CPU:Apple M4)
- PHP：8.3.30
- Laravel：10.50.2
- DB：MySQL 8.0
- Webサーバ：Nginx
- フロントエンド：Vite、Tailwind CSS 3.4.0
- 開発ツール：Docker 4.74.0、sail 8.5、phpMyAdmin 5.2.3

## 開発環境URL
http://localhost