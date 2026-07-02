# お問い合わせフォーム管理システム

Laravel 10を使用して開発したお問い合わせ管理システムです。

一般ユーザーはお問い合わせフォームから問い合わせを送信でき、管理者はログイン後にお問い合わせの検索・閲覧・削除・CSVエクスポートを行うことができます。

また、タグ機能を実装しており、お問い合わせとタグを多対多で管理できる構成となっています。

REST APIを提供しており、お問い合わせデータの取得・登録・更新・削除に対応しています。

---

# 主な機能

## 一般ユーザー

- お問い合わせ入力
- 入力内容確認
- お問い合わせ送信
- サンクスページ表示

## 管理者

- ログイン
- お問い合わせ一覧表示
- お問い合わせ検索
- お問い合わせ詳細表示
- お問い合わせ削除
- CSVエクスポート
- タグ管理
  - 作成
  - 編集
  - 削除

## API

- お問い合わせ一覧取得
- お問い合わせ詳細取得
- お問い合わせ登録
- お問い合わせ更新
- お問い合わせ削除

---

# ER図

![ER図](docs/er.png)

## リレーション

- Category ： Contact = 1 : N
- Contact ： Tag = N : N
- Contact ： contact_tag = 1 : N
- Tag ： contact_tag = 1 : N

---

# 使用技術

| 項目 | 技術 |
|------|------|
| PHP | 8.2 |
| Laravel | 10.x |
| データベース | MySQL 8.0 |
| フロントエンド | Vite |
| CSSフレームワーク | Tailwind CSS 3.4 |
| 認証 | Laravel Fortify |
| 開発環境 | Docker |
| コンテナ管理 | Laravel Sail |
| DB管理 | phpMyAdmin |
| バージョン管理 | Git / GitHub |

---

# APIエンドポイント一覧

## お問い合わせAPI

| Method | URI | Controller | Action | Route Name | 認証 |
|---|---|---|---|---|---|
| GET | /api/v1/contacts | Api\V1\ContactController | index | api.v1.contacts.index | 不要 |
| GET | /api/v1/contacts/{contact} | Api\V1\ContactController | show | api.v1.contacts.show | 不要 |
| POST | /api/v1/contacts | Api\V1\ContactController | store | api.v1.contacts.store | 不要 |
| PUT | /api/v1/contacts/{contact} | Api\V1\ContactController | update | api.v1.contacts.update | 不要 |
| DELETE | /api/v1/contacts/{contact} | Api\V1\ContactController | destroy | api.v1.contacts.destroy | 不要 |

---

# 設計書

- [ルート設計書](docs/route-design.md)
- [ER図](docs/er.png)

---

# 環境構築

## リポジトリをクローン

```bash
git clone https://github.com/taiga-kawakubo/contact-form-app.git
cd contact-form-app
```


## 環境変数ファイルを作成

```bash
cp .env.example .env
```


## データベース設定

`.env` のデータベース設定を以下のように設定します。

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```


## Composer依存関係をインストール
ローカル環境に Composer が入っていない場合でも、Dockerを使って依存関係をインストールできます。

```bash
docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$(pwd):/var/www/html" \
  -w /var/www/html \
  laravelsail/php82-composer:latest \
  composer install --ignore-platform-reqs
```
ローカル環境に Composer が入っている場合は、以下でも実行できます。

```bash
composer install
```


## Sailコンテナ起動

```bash
./vendor/bin/sail up -d
```
エイリアス設定済みの場合は、以下でも実行できます。

```bash
sail up -d
```


## アプリケーションキー生成

```bash
./vendor/bin/sail artisan key:generate
```
エイリアス設定済みの場合は、以下でも実行できます。

```bash
sail artisan key:generate
```


## マイグレーション・シーディング実行

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```
エイリアス設定済みの場合は、以下でも実行できます。
```bash
sail artisan migrate:fresh --seed
```


## フロントエンド依存関係インストール

```bash
./vendor/bin/sail npm install
```
エイリアス設定済みの場合は、以下でも実行できます。
```bash
sail npm install
```


## Vite起動
```bash
./vendor/bin/sail npm run dev
```

エイリアス設定済みの場合は、以下でも実行できます。
```bash
sail npm run dev
```
※ このコマンドは実行中のままにしておく必要があります。
そのため、以降のコマンド操作を行う場合は、別のターミナルタブを開いて実行してください。

---

# 開発環境URL

## アプリケーション

```text
http://localhost
```

## phpMyAdmin

```text
http://localhost:8080
```

---

# 初期管理者ログイン情報

シーディング実行後、以下のアカウントで管理画面にログインできます。

| 項目 | 内容 |
|------|------|
| メールアドレス | test@example.com |
| パスワード | password |

---

# テスト実行方法

LaravelのFeatureテスト・Unitテストは以下のコマンドで実行できます。

```bash
sail artisan test
```

Sailのエイリアスを設定していない場合は、以下のコマンドを使用してください。

```bash
./vendor/bin/sail artisan test
```

---

# テストカバレッジ確認方法（任意）

テストカバレッジは以下のコマンドで確認できます。

```bash
sail artisan test --coverage
```

Sailのエイリアスを設定していない場合は、以下のコマンドを使用してください。

```bash
./vendor/bin/sail artisan test --coverage
```

本アプリケーションでは、Controller・FormRequest・Resource・Modelを中心にテストを作成しています。

主なテスト対象は以下です。

- お問い合わせ登録
- お問い合わせ確認
- 管理画面アクセス制御
- お問い合わせ検索
- お問い合わせ詳細表示
- お問い合わせ削除
- CSVエクスポート
- タグ作成・更新・削除
- 認証・ログアウト
- APIによる一覧取得・詳細取得・登録・更新・削除

現在のテストカバレッジは以下です。

```text
Total: 88.7%
```

---

# コード整形確認

Laravel Pintによるコード整形は以下のコマンドで実行できます。

```bash
sail bin pint
```

整形が必要なファイルがないか確認する場合は、以下を実行します。

```bash
sail bin pint --test
```

---

# ディレクトリ構成

```text
docs/
├── er.drawio
├── er.png
└── route-design.md

app/
database/
resources/
routes/
```

---

# 作成者

川久保 大河