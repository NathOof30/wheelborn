# Configuração do Projeto WHEELBORN

Configuração padrão consumida pelos agentes e skills (`.agents/skills/`).

## 1. Identificação da Aplicação
- **Nome :** WHEELBORN (RNGboy)
- **Framework :** Laravel 11.x / 12.x
- **Frontend / Réactivité :** Livewire 3/4, Alpine.js, Tailwind CSS
- **Base de données :** SQLite (`database/database.sqlite`)
- **PHP :** ^8.3 (Windows PowerShell / POSIX)

## 2. Comandos do Portão de Verificação (`laravel-verification`)
- **Formatação :**
  ```bash
  ./vendor/bin/pint --test
  ```
- **Formatação (correção automática) :**
  ```bash
  ./vendor/bin/pint
  ```
- **Testes automatizados :**
  ```bash
  php artisan test
  ```
- **Testes com filtro :**
  ```bash
  php artisan test --filter=<NomeDoTeste>
  ```
- **Ambiente de Testes :** SQLite em memória ou banco local de testes (`phpunit.xml`)

## 3. Destinos do Projeto
| Recurso | Destino no Repositório |
| :--- | :--- |
| **Tickets a Fazer** | `tickets/todo/` |
| **Tickets Concluídos** | `tickets/done/` |
| **Especificações & GDD** | `docs/` |
| **Mapa de Features** | `docs/features/` |
| **Evidências de Teste** | `docs/evidencias/` |

## 4. Convenções Arquiteturais (`laravel-deep-modules`)
- Todo arquivo PHP de produção inicia com `declare(strict_types=1);`.
- Propriedades de estado ou IDs em componentes Livewire utilizam `#[Locked]`.
- Mutações de inventário, moedas, charges e combates utilizam `DB::transaction()` com `lockForUpdate()`.
- Código interno, classes, métodos e testes em inglês; textos voltados ao jogador em francês.

