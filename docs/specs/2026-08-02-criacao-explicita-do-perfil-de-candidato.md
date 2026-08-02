---
type: spec
title: "Criação explícita do perfil de candidato"
module: users, candidates, panel-app
status: proposed
date: 2026-08-02
author: Clintonrocha98
related:
  issue: 3pontos-tech/recruit-party-quest#261
---

# Criação explícita do perfil de candidato

## Contexto

`UserObserver::created()` cria um `Candidate` para **todo** `User` que entra no banco e
atribui a role `user`, decidindo isso com base num acesso à relação:

```php
// app-modules/users/src/UserObserver.php:14
public function created(User $user): void
{
    if (! $user->candidate) {
        Candidate::query()->create([...]);
        $user->assignRole('user');
    }
}
```

Três problemas se acumulam nessas doze linhas.

**1. O acesso à relação cacheia `null`.** É a issue #261. No momento em que `created`
dispara, o `Candidate` ainda não existe; o Eloquent resolve a relação como `null` e guarda
esse valor na instância. O `Candidate` nasce logo depois, mas a instância continua
enxergando `null` pelo resto do request.

```
  User::create()
       │
       ├──► evento `created`
       │         ├──► $user->candidate  ──► null  ──► CACHEADO na instância
       │         └──► Candidate::create(...)         (existe no banco, relação segue null)
       ▼
  $user->candidate  ────────────────────────────►  null  ✗
```

Em produção isso afeta quem cria um `User` e lê `$user->candidate` no mesmo request —
hoje, o registro via Socialite (`app/Socialite/CreateUserFromOauth.php:27`).

**2. O `if` é código morto.** No evento `created` o `Candidate` nunca pode existir: a FK
`candidates.user_id` impede que ele venha antes. A condição é sempre verdadeira, e
`! $user->candidate()->exists()` — a correção sugerida na issue — apenas troca o cache
indevido por um `SELECT EXISTS` cuja resposta é sempre a mesma. O efeito colateral disso
é o `assignRole()` ficar dentro de um `if` que não é dele: se um dia a condição for falsa,
o usuário fica sem role.

**3. Qualquer `User::create()` fabrica um candidato.** Inclusive dentro de factories. Isso
gera duplicatas em dev e testes (`Candidate::factory()->create()` resolve
`user_id => User::factory()`, o observer cria o Candidate A e a factory insere o B) e
impede a `unique` em `candidates.user_id` que tornaria a duplicação impossível. Sem essa
constraint, a relação `hasOne` resolve arbitrariamente entre os dois registros, e testes
que gravam em `$candidate` acabam lendo outro pelo `auth()->user()->candidate`.

Em produção a tabela está limpa — nada em prod passa por factory. A única porta de
duplicação real hoje é o `createOptionForm` do select de user em
`app-modules/panel-admin/src/Filament/Resources/Recruitment/Candidates/Schemas/CandidateForm.php:44`,
que cria um `User` inline (observer cria o Candidate) e em seguida grava um segundo pelo
próprio formulário. Nunca foi exercitada.

### Estado medido

Experimentos na suíte completa (1201 testes), cada patch aplicado e revertido:

| Cenário | Testes quebrados |
| --- | --- |
| `$user->candidate()->exists()` — correção da issue | 1 (`ProfileCardTest`) |
| Observer sem criar Candidate, mantendo `assignRole` | 78 |
| Observer vazio | 99 (23 fail + 76 error) |

### Efeito colateral no produto

Como todo `User` vira candidato, o super admin do seeder, os owners e os recrutadores
criados pelo painel admin carregam perfis de candidato vazios que aparecem no
`ListCandidates` do painel admin.

## Objetivos

- O `Candidate` passa a nascer de forma explícita, num único ponto, no momento em que o
  dado ganha sentido: o onboarding.
- O módulo `users` deixa de conhecer `He4rt\Candidates` — dependência que hoje existe no
  código sem estar declarada no `composer.json` do módulo.
- `candidates.user_id` ganha `unique`, tornando a duplicação impossível em vez de silenciosa.
- Nenhum `Candidate` é criado por efeito colateral de `User::create()`.

## Não-objetivos

- Decidir se recrutador ou owner pode se candidatar a vagas. O desenho evita a pergunta:
  quem abrir o painel app e passar pelo onboarding ganha um perfil, seja qual for a role.
- Limpar `Candidate`s legados. Produção está limpa; dev é descartável (`migrate:fresh --seed`).
- Migration de dados de qualquer natureza.
- Alterar o fluxo de convite de time ou a tenancy.

## Arquitetura

```
  ┌─────────────────────┐
  │  AppRegisterPage    │──┐
  ├─────────────────────┤  │                    ┌──────────────────────────┐
  │ CreateUserFromOauth │──┼──► User::create ──► │ UserObserver::created    │
  ├─────────────────────┤  │                    │  assignRole(Roles::User) │
  │ CreateUser (admin)  │──┤                    │  (sem Candidate)         │
  ├─────────────────────┤  │                    └──────────────────────────┘
  │ DatabaseSeeder      │──┘
  └─────────────────────┘

  ┌─────────────────────┐      ┌────────────────────────────────┐
  │ OnboardingWizard    │────► │ EnsureCandidateProfile (Action)│──► firstOrCreate
  │      ::mount()      │      │      módulo candidates         │
  └─────────────────────┘      └────────────────────────────────┘
```

### `users` — o observer encolhe

```php
// antes
public function created(User $user): void
{
    if (! $user->candidate) {
        Candidate::query()->create([
            'user_id' => $user->id,
            'is_onboarded' => false,
            'preferred_language' => 'en',
            'expected_salary_currency' => 'USD',
            'is_open_to_remote' => true,
        ]);

        $user->assignRole('user');
    }
}

// depois
public function created(User $user): void
{
    $user->assignRole(Roles::User);
}
```

A role base continua sendo invariante de todo `User` — é identidade, não perfil. O `import`
de `He4rt\Candidates\Models\Candidate` sai do observer, e a string `'user'` dá lugar ao
enum `Roles::User`. O módulo `users` continua importando o model em `User.php`, onde vive a
relação `candidate()` — desacoplar isso é um trabalho à parte, fora deste escopo.

### `candidates` — a Action

`EnsureCandidateProfile` em `app-modules/candidates/src/Actions/`, com o método `execute()`
que é a convenção do projeto. Recebe o `User`, devolve o `Candidate`, idempotente:

```php
final class EnsureCandidateProfile
{
    public function execute(User $user): Candidate
    {
        return Candidate::query()->firstOrCreate(
            ['user_id' => $user->getKey()],
            [
                'is_onboarded' => false,
                'preferred_language' => 'pt_BR',
                'expected_salary_currency' => 'BRL',
                'is_open_to_remote' => true,
            ],
        );
    }
}
```

Migration nova no módulo, adicionando índice único em `candidates.user_id`. Como
`Candidate` usa `SoftDeletes`, o índice precisa ser **parcial** — do contrário um perfil
soft-deleted bloquearia a criação de um novo para o mesmo usuário, cenário que
`UserPreferredLocaleTest:18` já exercita:

```sql
CREATE UNIQUE INDEX candidates_user_id_unique ON candidates (user_id) WHERE deleted_at IS NULL;
```

O `firstOrCreate` acima passa a ser garantido pelo banco, não só pela aplicação.

Dois dos quatro defaults divergem de propósito do `default()` das colunas e do que o
observer gravava, assumindo o público brasileiro da plataforma:

| Campo | Coluna / observer | Action |
| --- | --- | --- |
| `is_onboarded` | `false` | `false` |
| `is_open_to_remote` | `true` | `true` |
| `preferred_language` | `'en'` | `'pt_BR'` |
| `expected_salary_currency` | `'USD'` | `'BRL'` |

A grafia `pt_BR` — e não `pt_br` — é a que `User::preferredLocale()` entrega ao Laravel e a
que o select do onboarding (`OnboardingWizard.php:299`) oferece; em minúsculas o locale
cairia no fallback. Manter os quatro explícitos na Action deixa a intenção legível sem
depender do schema.

### `panel-app` — o onboarding

```php
// antes — OnboardingWizard::mount:108
$this->record = $user->candidate;

// depois
$this->record = resolve(EnsureCandidateProfile::class)($user);
```

O middleware `RedirectIfOnboardingIncomplete` já é null-safe (`:35`) e empurra para o
wizard quem não tem perfil — o caminho até a Action está garantido para todo usuário do
painel, exceto SuperAdmin e Admin, que têm bypass em `:26`.

### `panel-app` — null-safety

Com o bypass de admin, um SuperAdmin ou Admin sem `Candidate` navegando pelo painel app
alcança código que hoje assume a relação não-nula. Isso funciona atualmente só porque todo
usuário tem perfil. Pontos a tratar:

| Arquivo | Linhas |
| --- | --- |
| `panel-app/src/Filament/Resources/Applications/Tables/ApplicationsTable.php` | 18 |
| `panel-app/src/Livewire/JobApplicationForm.php` | 64 |
| `panel-app/src/Livewire/Jobs/BookmarkJobButton.php` | 37 |
| `panel-app/src/Livewire/ProfileCard.php` | 17 |
| `panel-app/src/Livewire/MyProfile/CandidateSkills.php` | 30, 95 |
| `panel-app/src/Livewire/MyProfile/CandidateProfileInfo.php` | 31, 43, 83 |
| `panel-app/src/Livewire/MyProfile/CandidateEducation.php` | 32, 109 |
| `panel-app/src/Livewire/MyProfile/CandidatePreferences.php` | 33, 129 |
| `panel-app/src/Livewire/MyProfile/CandidateWorkExperience.php` | 36, 125 |
| `panel-app/src/Livewire/MyProfile/CandidateResumeUpload.php` | 41, 70 |
| `candidates/src/Actions/Onboarding/StoreCandidateEducation.php` | 15 |
| `candidates/src/Actions/Onboarding/StoreCandidateWorkExperiences.php` | 16 |

Já null-safe, sem ação: `SavedJobsWidget` (`?->`), `ViewJobRequisition` (protegido por
`if`), `User::getFilamentAvatarUrl:90`, `User::preferredLocale:129`,
`RedirectIfOnboardingIncomplete:35`.

### Factories

Nenhuma factory muda. Os fixtures que precisam de perfil passam a criá-lo explicitamente,
usando o padrão que já existe em dez arquivos do repositório:

```php
// antes — depende do observer e precisa do refresh para furar o cache
$this->user = User::factory()->create();
$this->user->refresh();
$this->candidate = $this->user->candidate;

// depois — o fixture declara o que quer
$this->user = User::factory()->create();
$this->candidate = Candidate::factory()->for($this->user, 'user')->create([
    'is_onboarded' => false,
]);
```

Descartada a alternativa de um state `withCandidate()` na `UserFactory`: ela obrigaria o
módulo `users` a importar `He4rt\Candidates` de volta — agora pelas factories — desfazendo
justamente o desacoplamento que este trabalho busca.

A `CandidateFactory` não muda: `Candidate::factory()->create()` passa a produzir um `User`
sem perfil e exatamente um `Candidate`, que é o comportamento correto. Pelo mesmo motivo,
os dez arquivos que hoje seguem o padrão `User::factory()` + `Candidate::factory()->for()`
— e que geram duplicata — passam a ficar corretos **sem edição alguma**.

## Comportamento esperado

**Registro pelo painel app**

- **Dado** um visitante que preenche o formulário de registro
- **Então** um `User` é criado com a role `user`, sem `Candidate`
- **E** o middleware o redireciona para `/onboarding`
- **E** ao montar o wizard, o `Candidate` é criado com os defaults
- **E** ao concluir o onboarding, `is_onboarded` passa a `true`

**Registro via Socialite**

- **Dado** um primeiro login OAuth com e-mail válido
- **Então** um `User` é criado com a role `user`, sem `Candidate`
- **E** `$user->candidate` no mesmo request devolve `null` — de forma honesta, porque o
  perfil realmente não existe, e não por cache indevido
- **E** o fluxo segue igual ao registro por formulário

**Usuário criado pelo painel admin**

- **Dado** um admin criando um recrutador em `CreateUser`
- **Então** o `User` nasce com a role `user` e as roles marcadas no formulário
- **E** nenhum `Candidate` é criado
- **E** ele não aparece no `ListCandidates`

**Admin visitando o painel app**

- **Dado** um SuperAdmin sem `Candidate` que abre `/app`
- **Então** o bypass do middleware o deixa passar
- **E** as páginas que dependem de perfil se comportam como "sem perfil" em vez de estourar

**Onboarding chamado duas vezes**

- **Dado** um usuário que já tem `Candidate` e recarrega o wizard
- **Então** `EnsureCandidateProfile` devolve o perfil existente
- **E** nenhum registro novo é criado

**Compatibilidade com dados existentes**

- **Dado** um `User` de produção que já tem `Candidate`
- **Então** nada muda para ele: a relação resolve o mesmo registro de sempre
- **E** a migration `unique` aplica sem violação (verificado: prod não tem duplicatas)

## Fases

1. **Domínio** — `EnsureCandidateProfile` + testes unitários (cria, é idempotente,
   aplica os defaults).
2. **Observer** — remove a criação, troca `'user'` por `Roles::User`, tira o `if`.
3. **Onboarding** — `mount()` passa a usar a Action.
4. **Null-safety** — os 12 pontos da tabela acima, com teste para o caso admin-sem-perfil.
5. **Migration** — índice único parcial em `candidates.user_id`.
6. **Fixtures** — os 15 arquivos que dependem da criação implícita passam a declarar o
   `Candidate`, e os comentários de contorno do PR #260 saem.
7. **Contratos que mudam** — `AfterRegisterTest` (hoje asserta `Candidate` count 1 logo
   após o registro) reescrito para a nova regra, e teste de regressão da #261.

## Riscos

**Um entry point futuro esquecer de criar o perfil.** É o preço de trocar implícito por
explícito. Mitigação: o único consumidor de `Candidate` é o painel app, e todo caminho
até ele passa pelo `RedirectIfOnboardingIncomplete`, que empurra para o wizard. A Action
é idempotente, então chamá-la a mais nunca causa dano.

**Admin com bypass alcançando código sem perfil.** Tratado na fase 4. É um bug latente que
já existe hoje — a mudança apenas o torna alcançável.

**15 arquivos de teste tocados.** O conserto é mecânico, mas cada `beforeEach` merece uma
olhada: qual `Candidate` aquele teste realmente quer, o do fixture ou o do fluxo. O número
99 é o teto medido com o observer completamente vazio; mantendo `assignRole` nele, a
medição foi de 78.

## Alternativas consideradas

**Só a correção da issue** (`$user->candidate()->exists()` ou `setRelation`). Custo de 1
teste, resolve o cache `null` e nada mais: a criação implícita continua, a `unique` segue
inviável (quebraria ~25 pontos da suíte por violação de constraint) e o acoplamento
`users → candidates` permanece.

**Criar o `Candidate` no registro**, em `AppRegisterPage` e `CreateUserFromOauth`. Segue a
regra ao pé da letra, mas deixa quem chega ao painel app por outra porta sem perfil,
forçando uma decisão de produto sobre recrutador poder se candidatar. Descartado por
resolver menos com mais escopo.

**Evento `UserRegistered` + listener em `candidates`.** Inverte a dependência entre módulos
com mais elegância, mas adiciona indireção sem consumidor além do próprio listener. A
Action chamada do onboarding já entrega o desacoplamento. Vale reabrir quando surgir o
segundo interessado no registro (e-mail de boas-vindas, analytics).

**Chamar a Action num middleware do painel app**, garantindo perfil para todo visitante e
dispensando a fase 4. Descartado: recria a criação implícita, agora disfarçada, e volta a
gerar perfis vazios para admins que só passaram pelo painel.
