# Ready for review

_Select text to add comments on the plan_

# Plano: Welcome Page Pública "TechDevs" + Forum Name Config

## Context

Rota `/` mostra página padrão Laravel. Usuário quer landing page pública estilo Reddit onde visitantes não-cadastrados podem navegar timeline infinita de posts. Login/Register via popup modal (1x por sessão). Nome do fórum ("TechDevs") configurável por admin.

---

## Fase 1: Backend — Settings Table + Model

### 1.1 Migration `create_settings_table`

- `key` string(255) unique
- `value` text nullable
- timestamps

### 1.2 Model `app/Models/Setting.php`

- `fillable: ['key', 'value']`
- Static helpers: `Setting::get('forum_name', 'TechDevs')`, `Setting::set(key, value)`
- Cache com `Cache::remember('setting_' . $key, 3600, ...)`

### 1.3 Seeder `SettingsSeeder.php`

- Seed `forum_name = 'TechDevs'`

---

## Fase 2: Backend — Compartilhar Forum Name via Inertia

### 2.1 Modificar `app/Http/Middleware/HandleInertiaRequests.php`

Adicionar ao `share()`:

```php
'forumName' => fn () => \App\Models\Setting::get('forum_name', 'TechDevs'),
Fase 3: Backend — Rotas Públicas + Controller
3.1 Modificar routes/web.php
Rota / → ForumController::publicIndex (SEM auth)

Mover GET /posts/{postId} e GET /search-posts pra FORA do middleware auth (leitura pública)

Adicionar rota admin: PUT /admin/settings → SettingsController::update (com AdminOnly middleware)

3.2 Modificar app/Http/Controllers/ForumController.php
Extrair lógica compartilhada em private buildPostQuery(Request $request): array

Manter index() pra usuários autenticados → render Forum/Index

Novo publicIndex() → render Welcome com prop isAuthenticated

3.3 Criar app/Http/Controllers/Admin/SettingsController.php
update(): valida forum_name, chama Setting::set(), limpa cache

3.4 Registrar middleware alias admin em bootstrap/app.php
Apontar pra AdminOnly::class

Fase 4: Frontend — Componente PostCard Reutilizável
4.1 Criar resources/js/Components/PostCard.vue
Extrair markup de post card do Forum/Index.vue (linhas ~127-187)

Props: post (Object), isAuthenticated (Boolean, default true)

Emits: vote, request-auth, click

Helpers locais: getAvatarColor, getInitials, getCatBg, formatRelativeTime

Guest clicando vote → emite request-auth em vez de API call

4.2 Modificar resources/js/Pages/Forum/Index.vue
Substituir markup inline de post card por <PostCard>

Substituir logo hardcoded por $page.props.forumName

Fase 5: Frontend — PublicHeader
5.1 Criar resources/js/Components/PublicHeader.vue
Left: Logo icon + $page.props.forumName

Center: Search input

Right (guest): LanguageSwitcher, theme toggle, Login btn (accent), Register btn (outline)

Right (autenticado): LanguageSwitcher, theme toggle, notificações, user dropdown

Props: isAuthenticated

Emits: open-auth(tab), search(query)

Fase 6: Frontend — AuthModal (Login + Register)
6.1 Criar resources/js/Components/AuthModal.vue
Modal com backdrop blur, 2 tabs: Login / Register

Props: show (Boolean), initialTab ('login' | 'register')

Emits: close

Login tab: useForm com email, password, remember → form.post(route('login'))

Register tab: useForm com name, username, email, password, password_confirmation → form.post(route('register'))

Auto-show 1x por sessão: sessionStorage.getItem('auth_modal_shown')

Fechar seta flag: sessionStorage.setItem('auth_modal_shown', 'true')

Botão X pra fechar

Dark mode + accent color

Fase 7: Frontend — Reescrever Welcome.vue
7.1 Reescrever resources/js/Pages/Welcome.vue
Layout:

text
┌─────────────────────────────────────────┐
│ PublicHeader (logo, search, login/reg) │
├─────────────────────────────────────────┤
│ Hero Banner: TechDevs + tagline + CTA  │
├────────┬──────────────────┬─────────────┤
│ Left   │ Center: Feed     │ Right       │
│ Sidebar│ PostCard list    │ Sidebar     │
│ Categ. │ Infinite scroll  │ Tags trend  │
│ Sort   │ Load more        │ Stats       │
│        │                  │ Join CTA    │
├────────┴──────────────────┴─────────────┤
│ AuthModal (popup 1x por sessão)         │
└─────────────────────────────────────────┘
Props: posts, filters, tags, categoryCounts, isAuthenticated

Infinite scroll com IntersectionObserver

Sidebar esquerda: categorias (mesmo padrão do Index.vue)

Sidebar direita: tags populares, stats do fórum, CTA "Junte-se à comunidade"

Vote click em guest → abre AuthModal

Click no post → navega pra /posts/{id} (rota pública)

Fase 8: i18n — Novas strings
8.1 Criar resources/js/i18n/pt-BR/auth.json e en/auth.json
Keys: login, register, email, password, remember_me, forgot_password, name, username, confirm_password, login_title, register_title

8.2 Criar resources/js/i18n/pt-BR/welcome.json e en/welcome.json
Keys: tagline, join_cta, trending_tags, forum_stats, members_count, posts_count, join_community

8.3 Atualizar resources/js/i18n/index.js
Importar e registrar auth + welcome namespaces

Fase 9: Admin — Configurar nome do fórum no perfil
9.1 Adicionar seção no perfil do admin (ou rota dedicada)
Campo input "Nome do Fórum" → PUT /admin/settings

Visível apenas para $page.props.auth.user.is_admin

Arquivos Críticos
Criar (10 arquivos):
Arquivo	Propósito
database/migrations/xxxx_create_settings_table.php	Tabela settings
app/Models/Setting.php	Model key-value
database/seeders/SettingsSeeder.php	Seed forum_name
app/Http/Controllers/Admin/SettingsController.php	Admin settings
resources/js/Components/PostCard.vue	Card reutilizável
resources/js/Components/PublicHeader.vue	Header público
resources/js/Components/AuthModal.vue	Modal login/register
resources/js/i18n/pt-BR/auth.json	i18n auth pt-BR
resources/js/i18n/en/auth.json	i18n auth en
resources/js/i18n/pt-BR/welcome.json + en/welcome.json	i18n welcome
Modificar (7 arquivos):
Arquivo	Mudança
routes/web.php	Rotas públicas + admin settings
bootstrap/app.php	Alias middleware admin
app/Http/Controllers/ForumController.php	Refactor + publicIndex()
app/Http/Middleware/HandleInertiaRequests.php	Share forumName
resources/js/Pages/Welcome.vue	Rewrite completo
resources/js/Pages/Forum/Index.vue	Usar PostCard + forumName
resources/js/i18n/index.js	Registrar auth + welcome
Verificação
bash
# Criar tabela settings
docker compose exec app php artisan migrate

# Seed forum_name
docker compose exec app php artisan db:seed --class=SettingsSeeder

# Reiniciar
docker compose restart app vite
Acessar http://localhost:8000/ deslogado → ver timeline pública com posts

Popup de auth aparece 1x → fechar → recarregar → NÃO aparece de novo

Clicar Login → modal abre na tab login → logar → redireciona pro fórum

Clicar Register → modal abre na tab register → cadastrar → redireciona

Guest clicar vote → abre modal de auth

Posts mostram tags, avatar, preview, contadores (mesmo visual do fórum autenticado)

Admin logado pode alterar nome do fórum → nome atualiza em todo app

Infinite scroll funciona carregando mais posts ao descer

Dark/light mode funciona na página pública

i18n funciona — trocar idioma muda strings do welcome
```
