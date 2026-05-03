# Editor Rico, “Colar Como” e Referências de Posts

## Summary
Implementar uma experiência de escrita mais parecida com Reddit/TabNews: `TextQuill` mais completo para posts, comentários e respostas, com menu “Colar como” para links, referências internas a posts via `/post` e colagem automática de `/posts/{id}`, além de bookmarks externos seguros salvos como snapshot. Comentários/respostas passarão a aceitar HTML rico sanitizado, com upload de imagens permitido.

## Key Changes
- Evoluir `TextQuill` para um editor reutilizável com modos:
  - `post`: toolbar completa com título/heading, bold, italic, strike, link, imagem, listas, quote, code, code block, tabela quando viável, limpar formatação.
  - `comment`: toolbar compacta com bold, italic, strike, link, imagem, listas, quote, code, menção de usuário e referência de post.
- Substituir o `textarea` de comentários/respostas por um `RichTextComposer` baseado em `TextQuill`, preservando contador, limite de caracteres e estados de envio.
- Criar menu flutuante “Colar como” ao colar URL:
  - URL: insere link normal.
  - Criar marcador: gera card preview externo.
  - Mencionar post: disponível quando a URL for interna `/posts/{id}`.
- Implementar `/post` no editor:
  - abre busca de posts usando endpoint dedicado;
  - insere chip/link/card de referência ao post selecionado;
  - colar URL interna `/posts/{id}` também transforma em referência de post.
- Permitir upload de imagem em comentários/respostas usando endpoint existente de upload, com os mesmos limites e validações.

## Backend / Data Flow
- Adicionar sanitização server-side com `ezyang/htmlpurifier` ou equivalente direto:
  - sanitizar `posts.description`, `comments.content` e `replies.body`;
  - permitir apenas tags/atributos seguros do editor;
  - permitir classes/data attributes necessários para menções, post refs e preview cards.
- Adicionar tabela `link_previews`:
  - `id`, `url_hash`, `url`, `domain`, `title`, `description`, `image_url`, `status`, `fetched_at`, timestamps;
  - `url_hash` único para reaproveitar snapshots.
- Criar endpoints autenticados:
  - `GET /posts/search?query=` retorna posts para seletor `/post`;
  - `POST /links/preview` recebe URL externa e retorna snapshot seguro.
- Segurança do preview:
  - aceitar apenas `http/https`;
  - bloquear localhost, IP privado, loopback e metadata IPs;
  - timeout curto, limite de tamanho da resposta e redirects controlados;
  - extrair Open Graph/title/description sem executar scripts.
- Manter compatibilidade:
  - comentários antigos em texto simples continuam renderizando;
  - comentários novos podem salvar HTML sanitizado;
  - extração de menções `@username` continua funcionando a partir do texto visível/HTML sanitizado.

## Frontend UX
- `TextQuill` terá toolbar moderna e dark/light mode alinhado ao fórum.
- Menu “Colar como” aparecerá perto do cursor após colar link.
- `/post` abrirá dropdown de posts com título, autor e resumo curto.
- Referências de post renderizam como chip ou card compacto clicável.
- Bookmarks externos renderizam como card com domínio, título, descrição e imagem quando disponível.
- Comentários/respostas seguem com limite de `2.000` caracteres de texto visível; posts seguem com título `120` e descrição `10.000`.

## Test Plan
- Criar post com formatação rica, link normal, imagem e bookmark externo.
- Criar comentário/resposta com bold, lista, quote, code, imagem, menção de usuário e referência de post.
- Colar `/posts/{id}` e confirmar transformação em referência interna.
- Digitar `/post`, buscar post e inserir referência.
- Validar que HTML perigoso é removido no backend.
- Validar que URLs privadas/localhost não geram preview.
- Validar dark/light mode, PT-BR/EN e limites de caracteres.
- Rodar `php -l`, testes de validação/sanitização e `npm run build` quando dependências estiverem instaladas.

## Assumptions
- Não haverá embed arbitrário externo no v1; apenas URL, bookmark e referência de post.
- Comentários/respostas passam a aceitar HTML sanitizado, não Markdown.
- Bookmarks externos serão snapshots salvos no banco para estabilidade e performance.
- Upload de imagens em comentários usará o endpoint atual, com validação equivalente à descrição do post.
