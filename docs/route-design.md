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

## **お問い合わせ機能**

| **Method** | **URI** | **Controller** | **Action** | **Route Name** | **認証** |
| --- | --- | --- | --- | --- | --- |
| GET | / | ContactController | create | contacts.create | 不要 |
| POST | /contacts/confirm | ContactController | confirm | contacts.confirm | 不要 |
| POST | /contacts | ContactController | store | contacts.store | 不要 |
| GET | /thanks | ContactController | thanks | contacts.thanks | 不要 |

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
| GET | /admin/contacts/{contact} | AdminController | show | admin.contacts.show |
| DELETE | /admin/contacts/{contact} | AdminController | destroy | admin.contacts.destroy |

---

## **タグ管理**

middleware: auth

| **Method** | **URI** | **Controller** | **Action** | **Route Name** |
| --- | --- | --- | --- | --- |
| POST | /admin/tags | TagController | store | admin.tags.store |
| GET | /admin/tags/{tag}/edit | TagController | edit | admin.tags.edit |
| PUT | /admin/tags/{tag} | TagController | update | admin.tags.update |
| DELETE | /admin/tags/{tag} | TagController | destroy | admin.tags.destroy |

---

## **CSVエクスポート**

middleware: auth

| **Method** | **URI** | **Controller** | **Action** | **Route Name** |
| --- | --- | --- | --- | --- |
| GET | /admin/export | ExportController | export | admin.export |

---

# **API Route**

Prefix

/api/v1

---

## **お問い合わせAPI**

| **Method** | **URI** | **Controller** | **Action** | **認証** |
| --- | --- | --- | --- | --- |
| GET | /api/v1/contacts | Api\ContactController | index | 不要 |
| GET | /api/v1/contacts/{contact} | Api\ContactController | show | 不要 |
| POST | /api/v1/contacts | Api\ContactController | store | 不要 |
| PUT | /api/v1/contacts/{contact} | Api\ContactController | update | 不要 |
| DELETE | /api/v1/contacts/{contact} | Api\ContactController | destroy | 不要 |

---


# **Controller一覧**

## **Web**

- ContactController
- CategoryController
- TagController
- ExportController

## **API**

- Api\ContactController

---

# **Route Model Binding**

## **Contact**

```php
/admin/contacts/{contact}

/api/v1/contacts/{contact}
```

```php
public function destroy(Contact $contact)
```

Laravelが自動でContactモデルを取得する。

---

## **Tag**

```php
/admin/tags/{tag}/edit

/admin/tags/{tag}
```

```php
public function update(Tag $tag)
```

Laravelが自動でTagモデルを取得する。