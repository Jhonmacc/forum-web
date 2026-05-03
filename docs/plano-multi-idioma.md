Aqui está sua documentação organizada e formatada em **Markdown (MD)** de forma clara e profissional:

---

# 🌐 Plano: Implementação de Multi-Idioma (i18n) - pt-BR / English

## 📌 Contexto

O fórum atualmente possui todas as strings de UI hardcoded em português.
O objetivo é adicionar suporte a **pt-BR** e **English**, com seletor no header, utilizando uma arquitetura profissional.

**Stack:**

- Laravel 11
- Vue 3
- Inertia.js

**Arquitetura escolhida:**

- Frontend: `vue-i18n v9`
- Backend: `Laravel __()`
- Bridge: `Inertia middleware`

---

# 🚀 Fase 1: Infraestrutura Base

## 1.1 Instalar vue-i18n

```bash
npm install vue-i18n@9
```

---

## 1.2 Estrutura de diretórios

### Frontend

```
resources/js/i18n/
├── index.js
├── pt-BR/
│   ├── common.json
│   ├── forum.json
│   ├── comments.json
│   ├── profile.json
│   └── tags.json
└── en/
    ├── common.json
    ├── forum.json
    ├── comments.json
    ├── profile.json
    └── tags.json
```

### Backend

```
lang/
├── pt-BR/
│   ├── messages.php
│   ├── notifications.php
│   └── validation.php
└── en/
    ├── messages.php
    ├── notifications.php
    └── validation.php
```

---

## 1.3 Configurar vue-i18n

Arquivo: `resources/js/i18n/index.js`

- `legacy: false`
- `globalInjection: true`
- Locale padrão: `pt-BR`
- Fallback: `pt-BR`
- Eager-load das traduções

Registrar em `resources/js/app.js`:

```js
app.use(i18n);
```

---

## 1.4 Middleware SetLocale

Criar: `app/Http/Middleware/SetLocale.php`

**Prioridade:**

1. Header `X-Locale`
2. Usuário autenticado
3. Cookie
4. Default `pt-BR`

```php
App::setLocale()
Carbon::setLocale()
```

Registrar em `bootstrap/app.php` antes de `HandleInertiaRequests`.

---

## 1.5 Inertia Bridge

Arquivo: `HandleInertiaRequests.php`

Compartilhar:

```php
locale
availableLocales
```

---

## 1.6 Axios

Arquivo: `resources/js/bootstrap.js`

```js
headers["X-Locale"] = localStorage.getItem("locale");
```

---

## 1.7 Migration

```php
$table->string('locale', 5)->default('pt-BR')
```

Adicionar ao `$fillable` em `User.php`.

---

## 1.8 Rota de troca de idioma

```php
POST /locale
```

- Salva no usuário (auth)
- Salva cookie (1 ano)

---

## 1.9 Configuração

`config/app.php`

```php
'locale' => 'pt-BR',
'available_locales' => ['pt-BR', 'en']
```

Remover hardcode do Carbon no `AppServiceProvider`.

---

# 🗂️ Fase 2: Traduções (Frontend)

## 2.1 pt-BR

- common.json
- forum.json
- comments.json
- profile.json
- tags.json

## 2.2 en

Mesma estrutura com traduções.

---

# 🧠 Fase 3: Traduções (Backend)

## 3.1 messages.php

Mensagens de controllers

## 3.2 notifications.php

Templates com placeholders:

```
:name, :title, :type
```

## 3.3 validation.php

Mensagens customizadas

---

# 🌍 Fase 4: Language Switcher

## 4.1 Criar componente

`LanguageSwitcher.vue`

Funções:

- Trocar locale vue-i18n
- Atualizar localStorage
- Atualizar Axios
- POST `/locale`
- `router.reload()`

## 4.2 Header

- Importar componente
- Substituir strings por:

```js
$t("common.key");
```

---

# 🔄 Fase 5: Converter Vue

Substituir todas as strings por `$t()`:

### Principais arquivos:

- Forum/Index.vue
- CreatePostModal.vue
- EditPost.vue
- TaggingModal.vue
- TextQuill.vue
- Profile (todos forms)
- Tags/Index.vue
- Document/ListWord.vue

---

# ⚙️ Fase 6: Backend

## Controllers

Substituir mensagens por:

```php
__('messages.key')
```

### Arquivos:

- CommentController
- PostsController
- TagController
- ForumController

---

## 🔁 Refatoração importante

Sort:

```
'últimas' → 'latest'
'mais novo' → 'newest'
'mais velho' → 'oldest'
```

---

## Notificações

```php
__('notifications.key')
```

Arquivos:

- CommentLiked.php
- PostLikedNotification.php
- MentionedInComment.php
- MentionedInReply.php
- CommentReplied.php

---

# 📁 Arquivos Críticos

## 🆕 Criar (21 arquivos)

| Arquivo              | Descrição    |
| -------------------- | ------------ |
| i18n/index.js        | Configuração |
| pt-BR/\*.json        | Traduções    |
| en/\*.json           | Traduções    |
| LanguageSwitcher.vue | Seletor      |
| lang/\*              | Backend      |
| SetLocale.php        | Middleware   |
| migration locale     | DB           |

---

## ✏️ Modificar (20 arquivos)

- package.json
- app.js
- bootstrap.js
- config/app.php
- bootstrap/app.php
- HandleInertiaRequests.php
- AppServiceProvider.php
- User.php
- routes/web.php
- Header.vue
- Todos os componentes Vue
- Controllers

---

# ✅ Verificação

## Setup

```bash
docker compose exec vite npm install
docker compose exec app php artisan migrate
docker compose restart app vite
```

---

## Testes

### ✔️ pt-BR

- UI igual ao atual

### ✔️ English

- UI traduzida

### ✔️ Persistência

- Reload mantém idioma

### ✔️ Backend

- Mensagens no idioma correto

### ✔️ Sort

```
?sort=latest
```

### ✔️ Notificações

- No idioma do usuário

---

Se quiser, posso te entregar também:

- 🔥 arquivos JSON prontos (pt-BR + en)
- 🔥 boilerplate do `i18n/index.js`
- 🔥 código completo do `LanguageSwitcher.vue`

Só falar 👍
