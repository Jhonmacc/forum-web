# Sistema de Fórum Interativo

Bem-vindo(a) ao **Sistema de Fórum Interativo**, um projeto completo desenvolvido para oferecer uma experiência fluida e dinâmica para comunidades online! Este sistema foi construído do zero com tecnologias modernas, permitindo que os usuários criem, editem e interajam com posts de forma intuitiva.

![Imagem do Projeto](https://i.imgur.com/w2lobVh.png)

![Imagem do Projeto](https://i.imgur.com/zseuvxk.png)

![Imagem do Projeto](https://i.imgur.com/We3Vv33.png)

![Imagem do Projeto](https://i.imgur.com/RtVYtWp.png)

## 📋 Sobre o Projeto

Este projeto é um fórum interativo que combina funcionalidades robustas com um design moderno e responsivo. Ele foi desenvolvido para promover engajamento entre os usuários, com recursos como notificações em tempo real, comentários aninhados, filtros dinâmicos e upload de imagens. O objetivo foi criar uma solução completa para comunidades online, com foco em usabilidade e performance.

### 🔹 Funcionalidades Principais

- **Sistema de Filas de Notificações**: Notificações assíncronas usando filas no Laravel, permitindo que os usuários sejam notificados em tempo real sobre novas interações (ex.: comentários, curtidas) sem sobrecarregar o sistema.
- **Comentários com Respostas e Curtidas**: Sistema de comentários aninhados (respostas em múltiplos níveis) com suporte a curtidas nos comentários e nos posts, promovendo maior engajamento.
- **Filtros e Mecanismo de Busca**: Filtros dinâmicos por tags (ex.: Suporte, Ideias, Artigos, Bugs) e ordenação (ex.: mais recentes, mais antigos, últimas atividades), além de um mecanismo de busca eficiente para encontrar posts rapidamente.
- **Atualização Dinâmica da Listagem de Posts**: Botão "Atualizar" para recarregar a lista de posts em tempo real (mantendo os filtros) e botão "Carregar Mais" para carregamento infinito, melhorando a experiência do usuário.
- **Upload de Imagens nos Posts**: Suporte para upload de imagens nos posts, permitindo que os usuários enriqueçam suas publicações com conteúdo visual.
- **Sistema de Tags Personalizáveis**: Tags com cores e ícones (usando Font Awesome), que podem ser atribuídas aos posts para facilitar a categorização e busca.
- **Modais Interativos**: Modais para criação e edição de posts com validação de formulários, integração com o editor Quill para texto rico, e um contorno amarelo sombreado para destaque visual.
- **Design Responsivo e Moderno**: Interface limpa e responsiva, construída com Tailwind CSS, garantindo uma ótima experiência em dispositivos móveis e desktops.

### 🔹 Tecnologias Utilizadas

- **Laravel 11**: Backend robusto com Eloquent ORM para gerenciar modelos e relacionamentos, filas para notificações assíncronas, e validação de dados.
- **Inertia.js**: Integração perfeita entre o backend Laravel e o frontend Vue, permitindo uma experiência de SPA (Single Page Application) sem a complexidade de uma API REST tradicional.
- **Vue 3**: Frontend reativo e dinâmico, com componentes reutilizáveis e gerenciamento de estado eficiente.
- **Tailwind CSS com Vite**: Estilização rápida e moderna, com Vite para um build rápido e eficiente durante o desenvolvimento.
- **Outras Dependências**:
  - **Font Awesome**: Para ícones personalizáveis nas tags.
  - **Quill**: Editor de texto rico para formatação de posts.
  - **SweetAlert2**: Para alertas visuais e interativos.
  - **PrimeVue**: Componentes adicionais de UI (ex.: multiselect).

### 🔹 Desafios e Aprendizados

Durante o desenvolvimento, enfrentei alguns desafios técnicos:
- **Sincronização de Estados**: Garantir a sincronização entre o frontend e o backend foi um desafio, resolvido com o uso eficiente do Inertia.js.
- **Comentários Aninhados**: Implementar um sistema de comentários com respostas em múltiplos níveis exigiu o uso de relações recursivas no Eloquent.
- **Otimização de Carregamento**: Otimizar o carregamento de posts com filtros dinâmicos foi resolvido com um mecanismo de "Carregar Mais" (infinite scroll).
- **Notificações Assíncronas**: Configurar filas no Laravel para notificações em tempo real, garantindo performance e escalabilidade.

Esses desafios me ensinaram muito sobre a integração de tecnologias modernas e como criar uma experiência de usuário fluida e interativa.

### 🔹 Impacto do Projeto

Este fórum é uma solução completa para comunidades online, permitindo que os usuários criem, editem e interajam com posts de forma intuitiva. As funcionalidades de notificações, comentários e filtros tornam o sistema altamente engajador, enquanto o design responsivo e o upload de imagens garantem uma experiência visual rica.

## 🚀 Como Instalar e Executar o Projeto

Siga os passos abaixo para configurar e executar o projeto localmente.

### Pré-requisitos

- **PHP**: ^8.2
- **Composer**: Última versão
- **Node.js**: ^18.x ou superior
- **NPM**: Última versão
- **MySQL** (ou outro banco de dados compatível com Laravel)
- **Redis** (opcional, para filas de notificações)

### Passos de Instalação
Sevidor NGINX ou Apache da sua escolha
Recomendo fortemente baixar o HERB => https://herd.laravel.com/windows Herd é um ambiente de desenvolvimento nativo e rápido para Laravel e PHP para Windows. Ele inclui tudo o que você precisa para começar a desenvolver com Laravel, incluindo PHP e nginx.
Depois de instalar o Herd, você está pronto para começar a desenvolver com Laravel.
No Herb escolha versão do PHP 8.2 e pronto.
Eu acho melhor que o XAMPP ou WAMP SERVER.

1. **Clone o Repositório**:
   ```bash
   git clone https://github.com/Jhonmacc/forum-web.git
    ```
1.2. cd forum-web

2. Instale as Dependências do PHP: Execute o Composer para instalar as dependências do Laravel e as Dependências do Frontend: Execute o NPM para instalar as dependências do Vue e outras bibliotecas:
```bash
composer install
npm install
````
3. Configure o ambiente e gere a Chave da Aplicação: Execute o comando Artisan para gerar a chave da aplicação::
```bash
cp .env.example .env
php artisan key:generate
````
4. Configure o banco de dados no .env, banco recomendado Mysql:
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

# Configuração de filas (opcional, para notificações assíncronas)
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
````
5. Execute as Migrações: Crie as tabelas no banco de dados executando as migrações:
```bash
php artisan migrate
````
6. Configure o Storage: Crie um link simbólico para o storage (necessário para upload de imagens):
```bash
php artisan storage:link
````
7. Compile os assets:
```bash
npm run build
npm run dev
````
8. Inicialize o servidor do laravel:
```bash
php artisan serve
````
9. Inicie as filas works para notificações em tempo real
```bash
php artisan queue:work
````
10. O projeto estará disponível em http://localhost:suaporta
Clique em Registrar e crie o seu usuário após isso será redirecionado para tela principal do forum 
http://localhost:suaporta/forum
Necessário e Importante: Tela para criar as tags http://localhost:suaporta/tags

## 🏷️ Tags

`#Php` `#Laravel` `#Vuejs` `#TailwindCSS` `#Mysql` `#DesenvolvimentoWeb` `#Forum` `#Inovação` `#Tecnologia`

💻 Desenvolvido com ❤️ e muita dedicação para uma imerssão maior em UX Designer.

   
