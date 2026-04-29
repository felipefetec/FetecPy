---
modulo: "01"
titulo: "Lógica e Algoritmos"
duracao_estimada: "4-6h"
pre_requisito: ""
---

# Módulo 1 — Lógica e Algoritmos

## Por que isso importa?

Antes de aprender qualquer linguagem de programação, você precisa aprender a **pensar como um programador**. E isso não tem nada a ver com computadores.

Pense assim: quando você segue uma receita de bolo, está executando um algoritmo. Quando você resolve um labirinto, está fazendo planejamento lógico. Quando você organiza uma playlist por ordem de preferência, está fazendo uma ordenação.

Programar é escrever essas instruções de uma forma que um computador consiga entender. Mas a parte difícil — pensar na solução — é 100% humana.

:::aviso
Neste módulo você **não vai escrever nenhuma linha de Python**. O objetivo é desenvolver o raciocínio. A sintaxe vem depois.
:::

## O que você vai aprender

- O que é um algoritmo e por que ele importa
- Como decompor problemas em passos pequenos
- O conceito de variáveis, condicionais e repetições — sem código
- Como representar soluções com pseudocódigo

---

## 1.1 O que é programar?

Um computador é uma máquina extremamente obediente e extremamente literal. Ele faz **exatamente** o que você manda — nem mais, nem menos.

Se você esquecer um passo, ele não vai adivinhar. Se você for ambíguo, ele vai travar ou fazer a coisa errada. Por isso, um programador precisa ser **preciso** e **completo** nas instruções.

### A analogia da receita de bolo

Imagine que você está explicando como fazer um bolo para alguém que **nunca cozinhou na vida** e que segue instruções ao pé da letra:

> "Coloque a farinha na tigela."

Qual tigela? Quanto de farinha? A frase está incompleta. O computador travaria aqui.

> "Coloque 2 xícaras de farinha de trigo na tigela grande."

Melhor. Agora está preciso o suficiente para ser executado.

:::dica
Um bom algoritmo não tem ambiguidade. Cada passo deve ter exatamente uma interpretação possível.
:::

### O computador não pensa

Essa é a parte que muita gente erra no começo: o computador não tem intuição. Ele não vai "entender o que você quis dizer". Ele executa instruções — ponto.

Isso parece uma limitação, mas na prática é uma vantagem: um computador nunca vai fazer algo diferente do que você pediu. Se o resultado estiver errado, o problema está nas suas instruções — e você pode encontrá-lo e corrigi-lo.

:::reflexao
Escreva em linguagem natural (português comum) os passos para trocar uma lâmpada queimada. Seja específico o suficiente para que alguém que nunca fez isso antes consiga seguir.
---resposta
1. Desligue o interruptor da luz antes de qualquer coisa.
2. Espere a lâmpada esfriar por pelo menos 5 minutos (lâmpadas quentes podem queimar).
3. Se necessário, posicione uma cadeira ou escada embaixo da lâmpada.
4. Gire a lâmpada queimada no sentido anti-horário até ela soltar.
5. Descarte a lâmpada queimada com cuidado.
6. Pegue a lâmpada nova e gire no sentido horário até apertar (não force demais).
7. Ligue o interruptor para verificar se a lâmpada nova funciona.
:::

---

## 1.2 Algoritmos no dia a dia

Um **algoritmo** é uma sequência finita de passos bem definidos que resolve um problema ou realiza uma tarefa.

Você já usa algoritmos o tempo todo sem perceber:

| Tarefa | Algoritmo |
|--------|-----------|
| Fazer café | Encher a água → ligar a máquina → esperar → colocar o pó → pressionar |
| Atravessar a rua | Olhar para a esquerda → olhar para a direita → se livre, atravessar |
| Procurar uma palavra no dicionário | Abrir no meio → comparar → ir para a metade esquerda ou direita → repetir |

### Três características de um bom algoritmo

1. **Finito** — deve terminar em algum momento. Um algoritmo que roda para sempre não serve.
2. **Preciso** — cada passo deve ser claro e sem ambiguidade.
3. **Eficaz** — deve realmente resolver o problema proposto.

:::curiosidade
O nome "algoritmo" vem do matemático persa **Al-Khwarizmi** (século IX), que escreveu tratados sobre aritmética e álgebra. Seu nome latinizado — *Algoritmi* — deu origem à palavra que usamos até hoje.
:::

---

## 1.3 Variáveis — caixinhas com nome

Imagine uma caixinha com uma etiqueta. Você pode colocar um valor dentro, consultar o valor depois, e trocar o valor quando precisar. Isso é uma **variável**.

```
nome = "Felipe"
idade = 28
altura = 1.75
```

Cada variável tem:
- Um **nome** (a etiqueta da caixinha)
- Um **valor** (o que está dentro)
- Um **tipo** (número, texto, verdadeiro/falso etc.)

### Por que variáveis existem?

Sem variáveis, você teria que saber de antemão todos os valores do seu programa. Mas e quando o valor depende do usuário? Ou de um cálculo?

```
peso = (valor que o usuário digitar)
altura = (outro valor do usuário)
imc = peso / (altura * altura)
```

Aqui, `imc` é calculado automaticamente a partir dos outros valores. Isso só é possível porque usamos variáveis.

:::dica
Escolha nomes de variáveis que descrevam o que elas guardam. `x` e `y` são ruins para variáveis que guardam nome e sobrenome. `nome` e `sobrenome` são muito melhores.
:::

---

## 1.4 Condicionais — tomando decisões

Todo programa interessante precisa tomar decisões. A estrutura básica é:

```
SE (condição) ENTÃO
    faça isso
SENÃO
    faça aquilo
FIM SE
```

Exemplo real:

```
SE (temperatura > 30) ENTÃO
    leve água e protetor solar
SENÃO SE (temperatura > 20) ENTÃO
    leve um casaco leve
SENÃO
    leve casaco pesado
FIM SE
```

### Operadores de comparação

Para criar condições, usamos comparações:

| Operador | Significado | Exemplo |
|----------|-------------|---------|
| `>`  | maior que       | `idade > 18` |
| `<`  | menor que       | `saldo < 0` |
| `>=` | maior ou igual  | `nota >= 7` |
| `<=` | menor ou igual  | `velocidade <= 120` |
| `=`  | igual a         | `nome = "Felipe"` |
| `≠`  | diferente de    | `senha ≠ "1234"` |

:::reflexao
Escreva em pseudocódigo uma condição que verifica se um número é positivo, negativo ou zero. Use SE/SENÃO SE/SENÃO.
---resposta
leia numero

SE (numero > 0) ENTÃO
    imprima "Positivo"
SENÃO SE (numero < 0) ENTÃO
    imprima "Negativo"
SENÃO
    imprima "Zero"
FIM SE
:::

---

## 1.5 Repetições — fazendo coisas várias vezes

Imagine que você precisa imprimir "Olá!" 100 vezes. Escrever 100 linhas seria absurdo. As repetições (também chamadas de **laços** ou **loops**) resolvem isso:

### Enquanto (while)

Repete **enquanto** uma condição for verdadeira:

```
ENQUANTO (não chegou no andar certo) FAÇA
    pressione o botão do próximo andar
FIM ENQUANTO
```

### Para cada (for)

Repete **para cada** item de uma coleção, ou um número fixo de vezes:

```
PARA contador DE 1 ATÉ 10 FAÇA
    imprima contador
FIM PARA
```

:::aviso
Cuidado com o **loop infinito**: se a condição do ENQUANTO nunca ficar falsa, o programa roda para sempre. É um dos erros mais comuns de quem está aprendendo.

Exemplo ruim:
```
contador = 1
ENQUANTO (contador > 0) FAÇA
    contador = contador + 1
FIM ENQUANTO
```
`contador` começa em 1 e só aumenta — nunca vai ser ≤ 0. Loop infinito!
:::

---

## 1.6 Pseudocódigo — escrevendo algoritmos em português

**Pseudocódigo** é uma forma de escrever algoritmos usando linguagem natural estruturada — nem código de verdade, nem texto livre. É uma ponte entre a ideia na sua cabeça e o código final.

Não existe um padrão único de pseudocódigo. O que importa é ser **claro** e **consistente**.

Exemplo — calcular a média de 3 notas:

```
INÍCIO
  leia nota1
  leia nota2
  leia nota3

  media = (nota1 + nota2 + nota3) / 3

  SE (media >= 7) ENTÃO
    imprima "Aprovado"
  SENÃO SE (media >= 5) ENTÃO
    imprima "Recuperação"
  SENÃO
    imprima "Reprovado"
  FIM SE
FIM
```

:::dica
Escrever pseudocódigo antes de codar é um hábito de programador experiente, não de iniciante. Quem pula essa etapa e vai direto para o código costuma ficar travado no meio.
:::

---

## Mini-projeto: Caixa Eletrônico

Escreva o pseudocódigo completo de um caixa eletrônico simples com as seguintes funcionalidades:

1. Pedir senha ao usuário (3 tentativas — se errar as 3, bloquear)
2. Mostrar menu: Ver saldo / Sacar / Depositar / Sair
3. Para cada opção, executar a ação correspondente
4. Voltar ao menu após cada operação (exceto Sair)

Não precisa de código Python — use pseudocódigo em português.

---

## Quiz

1. O que é um algoritmo?
   - a) Um tipo de linguagem de programação
   - b) Uma sequência finita de passos bem definidos que resolve um problema
   - c) Um erro de programação
   - d) O nome do criador do Python

2. Qual a diferença entre SE e ENQUANTO?
   - a) Nenhuma, fazem a mesma coisa
   - b) SE executa um bloco uma vez se a condição for verdadeira; ENQUANTO repete enquanto a condição for verdadeira
   - c) ENQUANTO é mais rápido que SE
   - d) SE é usado só em Python

3. O que é uma variável?
   - a) Um erro no programa
   - b) Um tipo de loop
   - c) Um espaço na memória com nome e valor que pode mudar
   - d) Uma função matemática

4. O que acontece se a condição de um ENQUANTO nunca ficar falsa?
   - a) O programa termina normalmente
   - b) O programa pula o bloco
   - c) O programa entra em loop infinito
   - d) Ocorre um erro de sintaxe

---

## Resumo

Neste módulo você aprendeu que:

- ✓ Um **algoritmo** é uma sequência precisa e finita de passos
- ✓ Computadores são literais — não adivinham suas intenções
- ✓ **Variáveis** guardam valores que podem mudar durante a execução
- ✓ **Condicionais** (SE/SENÃO) permitem tomar decisões
- ✓ **Repetições** (ENQUANTO/PARA) evitam código repetitivo
- ✓ **Pseudocódigo** ajuda a planejar antes de codar

## Próximos passos

No **Módulo 2**, você vai escrever seus primeiros programas em Python. Tudo que você aprendeu aqui sobre variáveis, condicionais e repetições vai aparecer de novo — só que desta vez em código real.
