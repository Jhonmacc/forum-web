# TechDevs Forum Web

## English

TechDevs Forum Web is a modern forum application built with Laravel, Inertia.js, Vue 3, and Tailwind CSS. The project has evolved into a complete community experience: public visitor page, modal-based authentication, admin dashboard, tag management, post voting, threaded comments, rich TipTap editor, user notifications, multi-language support, and light/dark mode.

The project now runs preferably with Docker Compose, including PHP-FPM, Nginx, MySQL, Redis, a queue worker, and Vite.

![Project Image](https://i.imgur.com/w2lobVh.png)

![Project Image](https://i.imgur.com/zseuvxk.png)

![Project Image](https://i.imgur.com/We3Vv33.png)

![Project Image](https://i.imgur.com/RtVYtWp.png)

### Main Features

- Public `/` page where visitors can browse discussions, create an account, or sign in.
- Login and registration centralized in the Welcome Page modal.
- Integrated forum layout with header, collapsible sidebar, navigation, dark/light mode, and PT-BR/EN language switcher.
- Discussion listing with database-driven tag filters, search, sorting, and temporal `hot` ranking.
- Upvote-based voting system, highlighting the most relevant posts.
- Modern post view with a clean continuous layout and threaded comments.
- Comments and replies with visual hierarchy, connected lines, votes, replies, edit/delete actions, and `@username` mentions.
- Rich TipTap/ProseMirror editor for posts, comments, and replies.
- Support for links, images, internal post references, and safe external link previews.
- Backend HTML sanitization for posts, comments, and replies.
- Notification system for votes, comments, replies, and mentions.
- Modern tag management with colors, icons, descriptions, editing, and SweetAlert2 confirmations.
- Redesigned user profile integrated with the forum UI.
- Admin dashboard with forum business metrics, available only to administrators.
- PT-BR and EN translations for the main screens and messages.

### Architecture

#### Backend

- Laravel 11
- Fortify and Sanctum for authentication
- Inertia Laravel
- MySQL 8
- Redis for queues/cache
- Eloquent ORM relationships for posts, tags, users, comments, replies, votes, and notifications
- Middlewares for locale and admin-only areas
- Services for HTML sanitization and safe link preview generation

#### Frontend

- Vue 3
- Inertia.js
- Tailwind CSS
- Vite
- TipTap/ProseMirror
- Vue I18n
- Font Awesome
- SweetAlert2

#### Docker

The `docker-compose.yml` file starts these services:

- `forum-app`: Laravel application with PHP 8.2 FPM, Composer, and Node.js.
- `forum-nginx`: web server available at `http://localhost:8000`.
- `forum-mysql`: MySQL 8 database.
- `forum-redis`: Redis for queues/cache.
- `forum-queue`: Laravel queue worker.
- `forum-vite`: Vite development server available on port `5173`.

### Business Rules

- Visitors can see the public page and open posts, but must sign in to vote, comment, or reply.
- Login and registration must happen through the main page modal.
- Regular users cannot see or access the Admin Dashboard or Tag Management.
- Only administrators can access `/dashboard` and `/forum/tags`.
- Voting is currently simple upvote only, without downvotes.
- The `hot` sorting combines votes, comments, and recency.
- Tags are dynamic database entities; colors, icons, and counters no longer depend on fixed names.
- Link previews block local, private, loopback, and metadata IP URLs to reduce SSRF risk.
- Rich content is sanitized on the backend before being stored/rendered.

### Installation With Docker Compose

#### Requirements

- Git
- Docker
- Docker Compose v2

#### 1. Clone the Project

```bash
git clone https://github.com/Jhonmacc/forum-web.git
cd forum-web
```

#### 2. Create the Environment File

```bash
cp .env.example .env
```

Update `.env` to use the Docker services:

```env
APP_NAME=TechDevs
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

APP_LOCALE=en
APP_FALLBACK_LOCALE=pt-BR
APP_FAKER_LOCALE=en_US

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=forum_web
DB_USERNAME=laravel
DB_PASSWORD=secret

SESSION_DRIVER=database
CACHE_STORE=redis
QUEUE_CONNECTION=redis

REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
VITE_APP_NAME="${APP_NAME}"
```

#### 3. Start the Containers

```bash
docker compose up -d --build
```

#### 4. Install Dependencies Inside the Container

The image build already installs dependencies, but these commands are safe to ensure `vendor` and `node_modules` are correct in the local volume:

```bash
docker compose exec app composer install
docker compose exec app npm install
```

#### 5. Prepare Laravel

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed --class=SettingsSeeder
docker compose exec app php artisan storage:link
```

#### 6. Build Production Assets, If Needed

For development, the `forum-vite` service already runs `npm run dev`.

To generate production assets:

```bash
docker compose exec app npm run build
```

#### 7. Access the Project

Open:

```text
http://localhost:8000
```

Vite is available at:

```text
http://localhost:5173
```

### Creating an Administrator

Create an account normally through the main page at `http://localhost:8000`. Then promote the user to administrator:

```bash
docker compose exec app php artisan tinker
```

In Tinker:

```php
$user = \App\Models\User::where('email', 'your-email@example.com')->first();
$user->forceFill(['is_admin' => true])->save();
```

After that, the user can access:

```text
http://localhost:8000/dashboard
http://localhost:8000/forum/tags
```

### Useful Commands

View logs:

```bash
docker compose logs -f
```

View Laravel logs only:

```bash
docker compose logs -f app
```

View Vite logs:

```bash
docker compose logs -f vite
```

Run migrations again:

```bash
docker compose exec app php artisan migrate
```

Clear Laravel cache:

```bash
docker compose exec app php artisan optimize:clear
```

Run build:

```bash
docker compose exec app npm run build
```

Stop containers:

```bash
docker compose down
```

Stop containers and remove database/cache volumes:

```bash
docker compose down -v
```

### Main Routes

- `/`: public Welcome Page with discussion list, login, and registration.
- `/forum`: authenticated forum with sidebar, filters, sorting, and post creation.
- `/posts/{id}`: public/authenticated post view.
- `/users/{id}`: public profile with user posts.
- `/user/profile`: authenticated user account settings.
- `/forum/tags`: tag management, admin only.
- `/dashboard`: admin dashboard, admin only.

### Tests and Manual Validation

After important changes, run:

```bash
docker compose exec app php artisan test
docker compose exec app npm run build
```

It is also useful to validate:

- registration and login through the Welcome Page;
- visitor access to posts;
- vote/comment blocking for visitors;
- post creation with the rich editor;
- comments and replies creation;
- `@username` mentions;
- external link previews;
- tag filters after editing names, colors, and icons;
- dashboard and tags with an admin user;
- dashboard/tags blocking for regular users;
- PT-BR/EN language switcher;
- light/dark mode.

### Security Notes

- Sensitive actions use backend authentication and authorization.
- Dashboard and tags are protected by admin middleware.
- HTML content is sanitized before being saved.
- External link previews restrict private and local URLs.
- Visitors cannot vote, comment, or reply without authentication.
- Post, comment, and profile fields have frontend character limits and backend validation.

### Project Tags

`Laravel` `Vue.js` `Inertia.js` `Tailwind CSS` `Docker` `MySQL` `Redis` `TipTap` `Forum` `UX`

Developed by Jhon Amorim.

---

## Português

TechDevs Forum Web é uma aplicação de fórum moderna criada com Laravel, Inertia.js, Vue 3 e Tailwind CSS. O projeto evoluiu para uma experiência completa de comunidade: tela pública para visitantes, autenticação por modal, dashboard administrativo, gerenciamento de tags, posts com votação, comentários em árvore, editor rico com TipTap, notificações entre usuários, multi-idioma e suporte a modo claro/escuro.

O projeto agora roda preferencialmente com Docker Compose, incluindo PHP-FPM, Nginx, MySQL, Redis, worker de filas e Vite.

![Imagem do Projeto](https://i.imgur.com/w2lobVh.png)

![Imagem do Projeto](https://i.imgur.com/zseuvxk.png)

![Imagem do Projeto](https://i.imgur.com/We3Vv33.png)

![Imagem do Projeto](https://i.imgur.com/RtVYtWp.png)

### Principais Recursos

- Tela pública em `/` para visitantes visualizarem discussões, criarem conta ou fazerem login.
- Login e cadastro centralizados no modal da Welcome Page.
- Layout integrado do fórum com header, sidebar recolhível, navegação, modo dark/light e troca de idioma PT-BR/EN.
- Listagem de discussões com filtros por tags reais do banco, busca, ordenação e ranking temporal `hot`.
- Sistema de votação por upvote, com destaque para posts mais relevantes.
- Tela de visualização de post com UX inspirada em fóruns modernos, conteúdo contínuo e comentários em árvore.
- Comentários e respostas com hierarquia visual, linhas conectadas, votos, respostas, edição, exclusão e menções com `@username`.
- Editor rico com TipTap/ProseMirror para posts, comentários e respostas.
- Suporte a links, imagens, referências internas de posts e previews externos seguros.
- Sanitização de HTML no backend para conteúdo de posts, comentários e respostas.
- Sistema de notificações para votos, comentários, respostas e menções.
- Gerenciamento moderno de tags com cores, ícones, descrições, edição e exclusão com SweetAlert2.
- Perfil do usuário redesenhado e integrado ao visual do fórum.
- Dashboard administrativo com métricas de negócio do fórum, acessível somente para usuários administradores.
- Multi-idioma em PT-BR e EN para as principais telas e mensagens.

### Arquitetura

#### Backend

- Laravel 11
- Fortify e Sanctum para autenticação
- Inertia Laravel
- MySQL 8
- Redis para filas/cache
- Eloquent ORM com relacionamentos para posts, tags, usuários, comentários, respostas, votos e notificações
- Middlewares para locale e área administrativa
- Services para sanitização de HTML e geração segura de previews de links

#### Frontend

- Vue 3
- Inertia.js
- Tailwind CSS
- Vite
- TipTap/ProseMirror
- Vue I18n
- Font Awesome
- SweetAlert2

#### Docker

O `docker-compose.yml` sobe os seguintes serviços:

- `forum-app`: aplicação Laravel com PHP 8.2 FPM, Composer e Node.js.
- `forum-nginx`: servidor web disponível em `http://localhost:8000`.
- `forum-mysql`: banco MySQL 8.
- `forum-redis`: Redis para filas/cache.
- `forum-queue`: worker de filas Laravel.
- `forum-vite`: servidor Vite disponível na porta `5173`.

### Regras de Negócio Importantes

- Usuários visitantes podem ver a página pública e abrir posts, mas precisam fazer login para votar, comentar ou responder.
- Login e cadastro devem ser feitos pelo modal da tela principal.
- Usuários comuns não veem nem acessam o Dashboard administrativo ou Gerenciamento de Tags.
- Apenas administradores podem acessar `/dashboard` e `/forum/tags`.
- A votação atual é upvote simples, sem downvote.
- A ordenação `hot` combina votos, comentários e recência.
- Tags são entidades dinâmicas do banco; cores, ícones e contadores não dependem mais de nomes fixos.
- Link previews bloqueiam URLs locais, privadas, loopback e metadata IPs para reduzir risco de SSRF.
- Conteúdos ricos são sanitizados no backend antes de persistir/renderizar.

### Instalação com Docker Compose

#### Pré-requisitos

- Git
- Docker
- Docker Compose v2

#### 1. Clonar o projeto

```bash
git clone https://github.com/Jhonmacc/forum-web.git
cd forum-web
```

#### 2. Criar o arquivo de ambiente

```bash
cp .env.example .env
```

Atualize o `.env` para usar os serviços do Docker:

```env
APP_NAME=TechDevs
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

APP_LOCALE=pt-BR
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=pt_BR

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=forum_web
DB_USERNAME=laravel
DB_PASSWORD=secret

SESSION_DRIVER=database
CACHE_STORE=redis
QUEUE_CONNECTION=redis

REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
VITE_APP_NAME="${APP_NAME}"
```

#### 3. Subir os containers

```bash
docker compose up -d --build
```

#### 4. Instalar dependências dentro do container

O build já instala dependências, mas estes comandos são seguros para garantir que `vendor` e `node_modules` estejam corretos no volume local:

```bash
docker compose exec app composer install
docker compose exec app npm install
```

#### 5. Preparar o Laravel

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed --class=SettingsSeeder
docker compose exec app php artisan storage:link
```

#### 6. Compilar assets para produção, se necessário

Para desenvolvimento, o serviço `forum-vite` já executa `npm run dev`.

Para gerar assets de produção:

```bash
docker compose exec app npm run build
```

#### 7. Acessar o projeto

Abra:

```text
http://localhost:8000
```

O Vite fica disponível em:

```text
http://localhost:5173
```

### Criando um Administrador

Crie uma conta normalmente pela tela principal em `http://localhost:8000`. Depois, promova o usuário para administrador:

```bash
docker compose exec app php artisan tinker
```

No Tinker:

```php
$user = \App\Models\User::where('email', 'seu-email@exemplo.com')->first();
$user->forceFill(['is_admin' => true])->save();
```

Depois disso, o usuário poderá acessar:

```text
http://localhost:8000/dashboard
http://localhost:8000/forum/tags
```

### Comandos Úteis

Ver logs:

```bash
docker compose logs -f
```

Ver logs apenas do Laravel:

```bash
docker compose logs -f app
```

Ver logs do Vite:

```bash
docker compose logs -f vite
```

Executar migrations novamente:

```bash
docker compose exec app php artisan migrate
```

Limpar cache do Laravel:

```bash
docker compose exec app php artisan optimize:clear
```

Rodar build:

```bash
docker compose exec app npm run build
```

Parar containers:

```bash
docker compose down
```

Parar containers e remover volumes do banco/cache:

```bash
docker compose down -v
```

### Rotas Principais

- `/`: Welcome Page pública com listagem de discussões, login e cadastro.
- `/forum`: fórum autenticado com sidebar, filtros, ordenações e criação de posts.
- `/posts/{id}`: visualização pública/autenticada do post.
- `/users/{id}`: perfil público com posts do usuário.
- `/user/profile`: configurações da conta do usuário autenticado.
- `/forum/tags`: gerenciamento de tags, somente admin.
- `/dashboard`: dashboard administrativo, somente admin.

### Testes e Validação Manual

Após alterações importantes, recomenda-se executar:

```bash
docker compose exec app php artisan test
docker compose exec app npm run build
```

Também é útil validar:

- cadastro e login pela Welcome Page;
- acesso visitante a posts;
- bloqueio de voto/comentário para visitante;
- criação de post com editor rico;
- criação de comentários e respostas;
- menções com `@username`;
- previews de links externos;
- filtros por tags após editar nomes, cores e ícones;
- dashboard e tags com usuário admin;
- bloqueio de dashboard/tags para usuário comum;
- troca de idioma PT-BR/EN;
- modo claro/escuro.

### Observações de Segurança

- As ações sensíveis usam autenticação e autorização no backend.
- Dashboard e tags são protegidos por middleware administrativo.
- Conteúdo HTML é sanitizado antes de ser salvo.
- Preview de links externos aplica restrições contra URLs privadas e locais.
- Visitantes não podem votar, comentar ou responder sem autenticação.
- Campos de posts, comentários e perfil possuem limites de caracteres no frontend e validação no backend.

### Tags do Projeto

`Laravel` `Vue.js` `Inertia.js` `Tailwind CSS` `Docker` `MySQL` `Redis` `TipTap` `Forum` `UX`

Desenvolvido por Jhon Amorim.
