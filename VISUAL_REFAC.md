# FetecPy Pro — Guia de Refatoração Visual (2026)

> **Status:** Proposta Aprovada / Aguardando Implementação
> **Objetivo:** Elevar a estética da plataforma para um padrão premium "SaaS de alta prateleira", focando em modernidade, clareza e profissionalismo.

---

## 🎨 Paleta de Cores (Premium Dark Mode)

Abandonamos o cinza padrão por tons mais profundos e azulados que reduzem o cansaço visual e aumentam o contraste percebido.

| Elemento | Hex Code | Uso |
| :--- | :--- | :--- |
| **Background (Base)** | `#020617` (Slate 950) | Fundo principal de todas as páginas. |
| **Surface (Cards)** | `#0f172a` (Slate 900) | Fundo de cards e modais. |
| **Border (Subtle)** | `#1e293b` (Slate 800) | Bordas finas de 1px para separação de blocos. |
| **Primary (Python)** | `#10b981` (Emerald 500) | Botões de ação, progresso e sucessos. |
| **Secondary (Info)** | `#3b82f6` (Blue 500) | Links e estados "Em Andamento". |
| **Text (Primary)** | `#f8fafc` (Slate 50) | Títulos e textos de alta importância. |
| **Text (Secondary)** | `#94a3b8` (Slate 400) | Descrições e textos auxiliares. |

---

## 📐 Filosofia de Layout: "Bento Grid Moderno"

O layout deve deixar de ser uma lista simples para usar blocos que se encaixam, aproveitando melhor o espaço em telas grandes e empilhando com elegância no mobile.

1.  **Bordas:** Raio de curvatura padrão de `1rem` (16px) para cards e `0.5rem` (8px) para botões.
2.  **Sombras:** Uso de `shadow-2xl` com opacidade reduzida para criar profundidade.
3.  **Gradients:** Uso sutil de gradientes radiais no fundo para evitar que a tela pareça "plana demais".
    *   *Exemplo:* Um brilho suave de `#1e293b` no centro da tela.

---

## 🧩 Componentes Principais

### 1. O Terminal (Módulo 9)
O centro das atenções da nova fase.
*   **Aparência:** Bloco sólido com fundo `#000000` (preto absoluto).
*   **Header:** Barra superior com botões de controle (estilo MacOS: fechar, minimizar, expandir) apenas para estética.
*   **Fonte:** `JetBrains Mono` com `leading-relaxed`.

### 2. Cards de Módulo (Dashboard)
*   **Interatividade:** Efeito de "levitação" ao passar o mouse (`hover:-translate-y-1`).
*   **Visual:** Ícone do módulo em um círculo com fundo translúcido (ex: `bg-emerald-500/10`).

### 3. Navegação (Sidebar/Header)
*   **Header:** Fixado (`sticky`) com efeito de "Glassmorphism" (`backdrop-blur-md bg-slate-950/80`).
*   **Atalhos:** Ícones minimalistas (outline) que ficam preenchidos (solid) ao serem selecionados.

---

## 🛠️ Diretrizes de Implementação (Para o Claude Code)

Quando for iniciar a refatoração, siga esta ordem para garantir consistência:

1.  **Configuração de Cores**: Injetar as novas cores como tokens no Tailwind (ex: `primary`, `surface`, `base`).
2.  **Refatorar `public/index.html` (Login)**: É a primeira impressão. Deve usar o novo card centralizado com sombras profundas.
3.  **Refatorar `public/app.html` (Dashboard)**: Transformar o grid de módulos em uma Bento Grid.
4.  **Atualizar `public/assets/js/markdown.js`**: Ajustar os estilos de renderização do Markdown para que os blocos combinem com o novo tema.

---
*Arquivo criado pelo assistente Antigravity em 04/05/2026.*
