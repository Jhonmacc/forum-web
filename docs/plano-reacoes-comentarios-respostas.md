# Sistema de Reações em Comentários e Respostas

## Summary

Implementar reações visuais para comentários e respostas, mantendo a regra atual de negócio: cada reação conta como uma curtida única. Posts continuam recebendo apenas votação/upvote, sem reações.

O usuário poderá abrir uma paleta de reações ao clicar no botão atual de coração, escolher uma reação, trocar a reação depois, ou remover a reação clicando novamente na reação já escolhida.

## Key Changes

### Backend

- Adicionar um campo `reaction_type` na tabela `likes`, nullable/default, usado apenas quando o like estiver associado a `comment_id` ou `reply_id`.
- Manter `post_id` funcionando como votação simples, sem usar `reaction_type`.
- Criar uma lista oficial de reações permitidas:
  - `liked`: Gostei
  - `congrats`: Parabéns
  - `support`: Apoio
  - `love`: Amei
  - `amazing`: Incrível
  - `funny`: Divertido
- Atualizar `CommentController@toggleLike` e `toggleLikeReply` para aceitar `reaction_type`.
- Se o usuário ainda não reagiu: criar o like com a reação escolhida.
- Se o usuário já reagiu com outro tipo: atualizar `reaction_type`, mantendo `likes_count`.
- Se o usuário clicar na mesma reação já ativa: remover a reação, mantendo comportamento atual de toggle.
- Retornar no JSON:
  - `liked`
  - `likes_count`
  - `reaction_type`
  - opcionalmente `reaction_counts` por tipo para futuras melhorias visuais.

### Frontend

- Atualizar normalização em `EditPost.vue` para detectar `reaction_type` do like do usuário atual em comentários e respostas.
- Trocar o botão simples de coração em `CommentNode.vue` por um componente visual de reações.
- O clique no botão abre uma paleta flutuante com as 6 reações.
- Ao escolher uma reação:
  - aplica atualização otimista;
  - troca ícone/cor/label imediatamente;
  - envia `POST /comments/{id}/like` ou `POST /replies/{id}/like` com `{ reaction_type }`;
  - sincroniza com a resposta do backend.
- A reação ativa deve aparecer salva ao recarregar a página.
- Usar efeitos leves e divertidos:
  - escala/bounce no ícone escolhido;
  - brilho suave conforme tipo da reação;
  - paleta com transição de entrada;
  - tooltip/label para cada reação.
- Manter acessibilidade mínima:
  - botões com `title`/`aria-label`;
  - fechamento da paleta ao clicar fora ou selecionar reação.

### Visual das Reações

- `liked / Gostei`: polegar azul.
- `congrats / Parabéns`: palmas verdes.
- `support / Apoio`: mãos/coração em tom lilás.
- `love / Amei`: coração coral/vermelho.
- `amazing / Incrível`: lâmpada amarela.
- `funny / Divertido`: rosto sorrindo azul.

Implementar os ícones como configuração frontend centralizada, usando Font Awesome quando possível; quando não houver equivalente bom, usar emoji estilizado ou SVG inline pequeno no componente.

## Test Plan

- Criar reação em comentário sem reação anterior.
- Criar reação em resposta sem reação anterior.
- Trocar de `Gostei` para `Amei` sem alterar `likes_count`.
- Clicar novamente na mesma reação ativa e confirmar que remove a curtida.
- Recarregar a página e confirmar que a reação escolhida permanece.
- Validar que posts continuam apenas com upvote, sem paleta de reação.
- Validar visitante:
  - ao tentar reagir, abre modal de login/cadastro;
  - nenhuma chamada protegida é feita sem autenticação.
- Validar backend:
  - `reaction_type` inválido retorna erro de validação;
  - `likes_count` continua contando total de registros, não soma por tipo.
- Rodar:
  - `php -l` nos controllers/model/migration alterados;
  - `npm run build`.

## Assumptions

- A paleta abre ao clicar no botão de reação.
- Cada usuário pode ter somente uma reação por comentário ou resposta.
- Reagir com outro tipo altera a reação existente.
- Reagir novamente com o mesmo tipo remove a reação.
- A contagem exibida continua sendo total de curtidas/reacoes, não separada por tipo.
- Posts continuam usando apenas o sistema atual de votos/upvotes.
