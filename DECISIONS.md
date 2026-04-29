# Registro de Decisões Arquiteturais

Decisões tomadas durante o desenvolvimento que afetam o rumo do projeto.
Formato: data — título — contexto — decisão — consequências.

---

## 2026-04-29 — PHP não instalado localmente

**Contexto:** Durante o Prompt 1.1, ao tentar rodar `install.php` para validar a estrutura, constatou-se que PHP não está instalado na máquina de desenvolvimento (WSL2 Ubuntu).

**Decisão:** Não instalar PHP localmente por enquanto. O projeto é direcionado a hospedagem PHP compartilhada; testes de backend serão rodados quando PHP for instalado ou via container Docker pontual. Scripts `composer` e `phpunit` ficam pendentes de PHP.

**Consequências:** Testes de backend (PHPUnit) não rodam até que PHP seja instalado. Testes de frontend (Vitest/Node) rodam normalmente. O Prompt 1.4 (setup de testes) precisará instalar PHP primeiro.

**Alternativas consideradas:** Usar Docker com PHP — descartado por ora para não adicionar complexidade à máquina local que já tem Docker para a VPS.
