# Registro de Decisões Arquiteturais

Decisões tomadas durante o desenvolvimento que afetam o rumo do projeto.
Formato: data — título — contexto — decisão — consequências.

---

## 2026-04-29 — PHP instalado localmente para testes

**Contexto:** O projeto roda em hospedagem PHP compartilhada, mas não faz sentido depender do servidor remoto para validar código durante o desenvolvimento.

**Decisão:** PHP 8.3 instalado localmente via `apt` (php-cli, php-sqlite3, php-mbstring). Todos os testes de backend rodam na máquina local, sem precisar subir para o host.

**Consequências:** `install.php`, PHPUnit e qualquer script PHP podem ser executados localmente. Ciclo de desenvolvimento mais rápido e independente do servidor de produção.

**Alternativas consideradas:** Usar Docker com PHP — descartado por adicionar complexidade desnecessária quando o `apt` resolve com menos fricção.
