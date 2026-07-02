# **Route設計書**

## **概要**

新お問い合わせフォームシステムのルーティング設計。

- 一般ユーザー向けお問い合わせ機能
- 管理者向けお問い合わせ管理機能
- タグ管理機能
- API機能
- CSVエクスポート機能

を対象とする。

---

# **Web Route**

## お問い合わせ機能

認証：不要

| Method | URI | Controller | Action | Route Name | 認証 |
|---|---|---|---|---|---|
| GET | / | ContactController | index | contacts.index | 不要 |
| POST | /contacts/confirm | ContactController | confirm | contacts.confirm | 不要 |
| POST | /contacts/back | ContactController | back | contacts.back | 不要 |
| POST | /contacts | ContactController | store | contacts.store | 不要 |
| GET | /thanks | ContactController | thanks | contacts.thanks | 不要 |

### 補足

`/contacts/back` は、確認画面から修正ボタンを押した際に使用する。

---

## **認証機能（Fortify）**

| **Method** | **URI** | **説明** |
| --- | --- | --- |
| GET | /login | ログイン画面 |
| POST | /login | ログイン処理 |
| POST | /logout | ログアウト処理 |
| GET | /register | ユーザー登録画面 |
| POST | /register | ユーザー登録処理 |

※ Fortifyにより自動生成

---

## **管理画面**

middleware: auth

| **Method** | **URI** | **Controller** | **Action** | **Route Name** |
| --- | --- | --- | --- | --- |
| GET | /admin | AdminController | index | admin.index |
| GET | /admin/contacts/{contact} | AdminController | show | admin.show |
| DELETE | /admin/contacts/{contact} | AdminController | destroy | admin.delete |

---

## **タグ管理**

middleware: auth

| **Method** | **URI** | **Controller** | **Action** | **Route Name** |
| --- | --- | --- | --- | --- |
| POST | /admin/tags | TagController | store | tags.store |
| GET | /admin/tags/{tag}/edit | TagController | edit | tags.edit |
| PUT | /admin/tags/{tag} | TagController | update | tags.update |
| DELETE | /admin/tags/{tag} | TagController | destroy | tags.delete |

---

## **CSVエクスポート**

middleware: auth

| **Method** | **URI** | **Controller** | **Action** | **Route Name** |
| --- | --- | --- | --- | --- |
| GET | /contacts/export | ContactController | export | contacts.export |

---

# **API Route**

Prefix

/api/v1

---

## **お問い合わせAPI**


| Method | URI | Controller | Action | Route Name | 認証 |
|---|---|---|---|---|---|
| GET | /api/v1/contacts | Api\V1\ContactController | index | api.v1.contacts.index | 不要 |
| GET | /api/v1/contacts/{contact} | Api\V1\ContactController | show | api.v1.contacts.show | 不要 |
| POST | /api/v1/contacts | Api\V1\ContactController | store | api.v1.contacts.store | 不要 |
| PUT | /api/v1/contacts/{contact} | Api\V1\ContactController | update | api.v1.contacts.update | 不要 |
| DELETE | /api/v1/contacts/{contact} | Api\V1\ContactController | destroy | api.v1.contacts.destroy | 不要 |

---


# **Controller一覧**

## **Web**

- AdminController
- ContactController
- TagController

## **API**

- Api\V1\ContactController

---

# Route Model Binding

本アプリでは、一部のルートで Laravel の Route Model Binding を使用する。

URLパラメータに指定された `{contact}` や `{tag}` をもとに、Laravel が対応するモデルを自動取得する。

---

## Contact

| URI | Controller | Action |
|---|---|---|
| /admin/contacts/{contact} | AdminController | show / destroy |
| /api/v1/contacts/{contact} | Api\V1\ContactController | show / update / destroy |

---

## Tag

| URI | Controller | Action |
|---|---|---|
| /admin/tags/{tag}/edit | TagController | edit |
| /admin/tags/{tag} | TagController | update / destroy |