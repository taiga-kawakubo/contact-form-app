# View Analysis

## tags.edit

### 表示用データ

#### $tag

- id
- name

### 画面アクション

#### 更新処理

送信先

- PUT /admin/tags/{id}

送信値

- name

#### 一覧へ戻る

遷移先

- GET /admin

---

## admin.index

### 表示用データ

#### $contacts

- id
- first_name
- last_name
- gender
- email

##### category

- id
- content

##### tags

- id
- name

#### $categories

- id
- content

#### $tags

- id
- name

### 画面アクション

#### 検索

送信先

- GET /admin

入力値

- keyword
- gender
- category_id
- date

#### エクスポート

遷移先

- GET /contacts/export

クエリ

- keyword
- gender
- category_id
- date

#### タグ追加

送信先

- POST /admin/tags

入力値

- name

#### タグ編集

遷移先

- GET /admin/tags/{id}/edit

パラメータ

- tag_id

#### タグ削除

送信先

- DELETE /admin/tags/{id}

パラメータ

- tag_id

#### お問い合わせ詳細

遷移先

- GET /admin/contacts/{id}

パラメータ

- contact_id

---

## admin.show

### 表示用データ

#### $contact

- id
- first_name
- last_name
- gender
- email
- tel
- address
- building
- detail

##### category

- id
- content

##### tags

- id
- name

### 画面アクション

#### 一覧へ戻る

遷移先

- GET /admin

#### 削除

送信先

- DELETE /admin/contacts/{id}

パラメータ

- contact_id

---

## contact._form

### 表示用データ

#### $categories

- id
- content

#### $tags

- id
- name

### 入力項目

#### お名前

- first_name
- last_name

#### 性別

- gender

#### メールアドレス

- email

#### 電話番号

入力項目

- tel1
- tel2
- tel3

保存項目

- tel

#### 住所

- address

#### 建物名

- building

#### お問い合わせの種類

- category_id

#### タグ

- tag_ids[]

#### お問い合わせ内容

- detail

---

## contact.confirm

### 表示用データ

#### $validated

- first_name
- last_name
- gender
- email
- tel
- address
- building
- category_id
- tag_ids
- detail

#### $category

- id
- content

#### $tags

- id
- name

### 画面アクション

#### お問い合わせ作成

送信先

- POST /contacts

送信値

- first_name
- last_name
- gender
- email
- tel
- address
- building
- category_id
- tag_ids[]
- detail

#### 修正

- history.back()

---

## contact.index

### 表示用データ

#### $categories

- id
- content

#### $tags

- id
- name

### 画面アクション

#### 確認画面

送信先

- POST /contacts/confirm

送信値

- first_name
- last_name
- gender
- email
- tel
- address
- building
- category_id
- tag_ids[]
- detail