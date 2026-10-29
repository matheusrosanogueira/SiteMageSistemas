# Site Mage Sistemas — magesistemas.com.br

Site institucional da **Mage Sistemas** (automação comercial e fiscal), construído como página estática — sem frameworks, sem build, sem dependências. É só hospedar.

![Mage Sistemas](img/og.png)

## Estrutura

```
index.html          O site inteiro (HTML + CSS + JS em um único arquivo)
webchat.js          Widget de webchat da plataforma Mage (api.magesistemas.com)
privacidade.html    Política de privacidade (LGPD)
sistema-para-*.html Nove páginas de segmento (SEO + conversão)
sitemap.xml         Mapa do site para o Google
robots.txt          Diretivas de indexação
m.php               Coletor de métricas próprio (sem cookies, sem IP)
painel.php          Painel de métricas (senha definida no 1º acesso)
stats/              Registros de visitas (bloqueada ao público)
baixar-logos.php    Script de setup: baixa os logos dos parceiros e se apaga sozinho
img/
  logo.png          Logomarca (esfera) — também é o favicon
  og.png            Imagem de compartilhamento (WhatsApp/redes sociais)
  seg-*.jpg         Ilustrações neon dos 9 segmentos
  parceiro-*.png    Logos das adquirentes/TEF (gerados pelo baixar-logos.php)
  hero-magelite.png, lite-*.png, erp-*.png, web-painel.png
                    Telas ilustrativas dos sistemas (substituir pelos
                    prints reais quando disponíveis — mesmo nome de arquivo)
```

A lista completa de nomes de imagem esperados está comentada no topo do `index.html`. Qualquer imagem salva com o nome certo em `img/` aparece no site automaticamente, sem mexer em código.

## Identidade visual

Paleta extraída da logomarca, controlada por variáveis CSS no topo do `index.html`:

| Variável | Cor | Uso |
|---|---|---|
| `--bg` | `#0A0718` | Fundo (azul-marinho profundo) |
| `--violet` | `#7C5CFF` | Violeta principal |
| `--gold` | `#E23A9D` | Magenta de destaque (CTAs, ênfases) |
| `--deep` | `#3B29BD` | Índigo escuro |

Tipografia: Bricolage Grotesque (títulos) + Inter (texto), via Google Fonts.

## Publicação (cPanel)

1. Enviar os arquivos para `public_html` (preservando as pastas `gestor20`, `arquivos` e `update` que já existem na raiz — **não apagar**).
2. Acessar **uma vez** `https://magesistemas.com.br/baixar-logos.php` para baixar os logos dos parceiros (o script se autodestrói ao final).
3. Conferir em aba anônima.

> O conteúdo anterior da hospedagem deve ser preservado em uma pasta de backup antes de qualquer troca, e os subdomínios (ex.: `dashboard.magesistemas.com.br`) não devem ser alterados.

### Deploy automático via Git (opcional)

O cPanel tem **Git™ Version Control**, e este repositório já traz o `.cpanel.yml` pronto:

1. cPanel → **Git™ Version Control** → **Create**;
2. Clone URL: `https://github.com/matheusrosanogueira/SiteMageSistemas.git` (repositório privado exige chave SSH/token — ver abaixo);
3. Repository Path: `repositories/site-magesistemas` (nunca `public_html` — o clone fica à parte e o deploy copia para lá);
4. Após criar: **Manage** → aba **Pull or Deploy** → **Update from Remote** → **Deploy HEAD Commit**.

A cada atualização enviada ao GitHub, basta repetir o passo 4. O deploy copia `index.html`, `privacidade.html`, `webchat.js` e `img/` para `public_html`, sem apagar nada que já exista lá (pastas `gestor20`, `arquivos`, `update` e os logos `parceiro-*.png` baixados no servidor ficam intactos).

## Produtos apresentados

- **Mage Lite** — ERP próprio da Mage (produto principal)
- **Mage ERP** — solução desktop completa (retaguarda + PDV + TEF + BI)
- **Linha Mage Web** — MageWeb, MageFood, MageShop, MagePet, MageAgro (100% online)

Contato: (61) 99413-8978 · [@magesistemas](https://instagram.com/magesistemas)
