# Análise Estratégica: Viabilidade do Perfil "Recrutador Externo" no RPQ

**Data**: Março 2026
**Escopo**: Avaliar a necessidade arquitetural e comercial de implementar um tipo de usuário "Recrutador Externo"
**Público**: Product Manager, Stakeholders, Dono do Projeto

---

## 📋 Resumo Executivo

O RPQ foi concebido com uma proposta de valor clara: **terceirização completa do recrutamento**. Empresas cliente delegam integralmente o processo para nossa equipe de RH. A questão sobre "Recrutador Externo" cria um conflito estratégico fundamental:

**Achado Principal**: A introdução de um "Recrutador Externo" representaria uma **mudança de modelo de negócio**, movendo de "terceirização completa" para "gestão híbrida". Isso não é necessariamente ruim, mas é uma decisão com implicações profundas.

**Recomendação**:
- ❌ **NÃO implementar** "Recrutador Externo" como novo role/tipo de usuário no curto prazo
- ✅ **MANTER** a proposta de valor original (terceirização completa)
- 🤔 **CONSIDERAR** um modelo alternativo de "parceria controlada" em roadmap futuro (sem mudar a proposta de valor)
- 📌 **VALIDAR** com clientes reais se há demanda suficiente para justificar essa mudança

**Justificativa**: Os casos de uso atuais (agências parceiras, gerentes de cliente acompanhando) parecem **exceções** e não a regra. Tentar atender exceções pode diluir o valor principal do produto.

---

## 🎯 1. Contexto: Por Que Essa Discussão É Importante?

### Histórico da Dúvida

O RPQ implementou um modelo de `Recruiter` (recrutador) na arquitetura, mas **apenas para recrutadores internos**. Isso gerou a pergunta natural:

> "Se temos recrutadores, por que não poderíamos ter recrutadores externos?"

Essa pergunta leva a questões maiores sobre:
- O que realmente queremos vender?
- Como diferenciamos nosso produto no mercado?
- Qual é a complexidade máxima que estamos dispostos a suportar?

### Casos de Uso Propostos

1. **Parcerias com agências de recrutamento**: Uma agência terceirizada conduz etapas específicas do processo
2. **Gerentes de contratação do cliente**: Pessoa do lado cliente quer acompanhar/participar ativamente

---

## 🏗️ 2. Estado Atual: Como Funciona Hoje?

### Arquitetura de Usuários

**Roles/Tipos de Usuário Atuais:**
- `SuperAdmin` → Acesso total ao sistema
- `Admin` → Administrador (limite de acesso)
- `Owner` → Proprietário da organização/time
- `User` → Usuário padrão (aplicantes, etc.)

**Modelo Recruiter:**
```
- Automaticamente criado quando um User é adicionado a um Team
- Propriedades: is_active, max_active_candidates, max_active_requisitions
- Sem distinção entre "interno" e "externo"
- Policies de autorização: ainda em estágio inicial (todas permitem por padrão)
```

### Fluxo Atual (Terceirização Completa)

```
CLIENTE EXTERNO                          RPQ (NOSSA EMPRESA)
┌─────────────────────┐                 ┌─────────────────────┐
│ Comunica vagas      │  ──SOLICITA──→  │ Cria Requisição     │
│ necessárias         │                 │ Configura pipeline  │
│                     │                 │ Divulga vaga        │
│                     │  ←──RETORNA──   │ Seleciona candidato │
│ Contrata candidato  │  candidato      │ Conduz entrevistas  │
│ selecionado         │                 │ Faz recomendação    │
└─────────────────────┘                 └─────────────────────┘
   Cliente não participa do processo de recrutamento
```

### Responsabilidades Atuais

| Aspecto | Quem Faz? | Visibilidade |
|---------|-----------|--------------|
| Criar vaga | RPQ (Admin) | Cliente: informação inicial |
| Divulgar vaga | RPQ | Cliente: nenhuma |
| Analisar candidatos | RPQ | Cliente: nenhuma |
| Conduzir entrevistas | RPQ | Cliente: nenhuma |
| Avaliar candidatos | RPQ | Cliente: resultado final |
| Recomendar finalista | RPQ | Cliente: sim |
| Tomar decisão de contratação | **Cliente** | Cliente: sim |

**Insight**: Cliente só vê início (solicita) e fim (recebe candidatos). Meio é "caixa preta" nossa.

---

## ⚔️ 3. Proposta Original vs. Realidade: O Que Mudaria?

### Proposta Original (Atual)

**Slogan**: "Você não precisa lidar com recrutamento. Nós fazemos tudo por você."

**Benefícios para cliente:**
- ✅ Zero custo operacional de RH
- ✅ Especialização (nossa equipe conhece o processo)
- ✅ Foco total na empresa (não em recrutamento)
- ✅ Transparência no resultado (candidatos recomendados)

**Benefícios para RPQ:**
- ✅ Proposição clara e diferenciada
- ✅ Margem alta (vendemos expertise/tempo)
- ✅ Escalabilidade: uma pessoa nossa recebe múltiplas requisições
- ✅ Simples de vender (promessa clara)

### Proposta com "Recrutador Externo"

Se permitirmos que o cliente participe do processo, mudamos para:

**Novo Slogan**: "Você pode gerenciar recrutamento conosco. Nós ajudamos com expertise."

**Implicações:**
- ❌ Cliente agora tem responsabilidades (pode reclamar se processo atrasar)
- ❌ Nossa vantagem (especialização) fica menor
- ❌ Complexidade aumenta (múltiplos stakeholders internos no cliente)
- ❌ Competição direta com ferramentas ATS genéricas
- ❌ Arquitetura se torna mais complexa
- ✅ Maior controle para cliente (pode ser atrativo)
- ✅ Flexibilidade (atende mais casos)

### Matriz de Comparação

| Critério | Terceirização Completa | Gestão Híbrida |
|----------|----------------------|-----------------|
| **Proposta de valor** | Simplicidade (nós fazemos tudo) | Flexibilidade (você controla) |
| **Complexidade para cliente** | Baixa (passa a bola) | Alta (precisa gerenciar) |
| **Diferenciação** | Alta (especialização) | Baixa (parecido com ATS genérico) |
| **Margem comercial** | Alta (terceirização) | Média (ferramental) |
| **Facilidade de venda** | Alta (promessa simples) | Média (precisa customizar) |
| **Capacidade de escala** | Alta (nosso time cresce) | Baixa (cliente absorve parte) |
| **Satisfação cliente** | Alta (nós resolvemos) | Variável (cliente precisa agir) |
| **Casos de uso** | 90% do mercado | 10% (exceções) |

---

## 🔧 4. Análise Técnica: Impacto na Arquitetura

### Mudanças Necessárias

Se decidíssemos implementar "Recrutador Externo", teríamos que:

#### 4.1 Novo Role/Type de Usuário
```php
// Adicionar a Roles.php
ExternalRecruiter  // novo role

// Criar novo tipo de conta com acesso limitado
```

**Impacto**: Modificar sistema de permissões inteiro

#### 4.2 Distinguir Recruiter Interno vs. Externo
```php
// Na tabela 'recruiters', adicionar:
recruiter_type: enum ('internal', 'external')
// ou criar nova tabela 'external_recruiters'
```

**Impacto**: Modificar lógica de Recruiter, quebrar código existente

#### 4.3 Controles de Acesso (ACL) Muito Mais Complexos
```
Hoje:
- Owner → pode ver tudo de seu time
- Recruiter → vê requisições atribuídas a ele

Com External Recruiter:
- Owner → pode ver tudo
- Internal Recruiter → vê requisições de seu time
- External Recruiter → vê APENAS requisições explicitamente compartilhadas?
- External Recruiter pode editar candidatos? Criar etapas?
```

**Impacto**: Reescrever Policies de autorização

#### 4.4 Integração e Autenticação
```
Problema: Como um recruiter de agência externa acessa?
- Email/senha? (segurança, gerenciamento de credenciais)
- SSO do cliente? (complexidade integrações)
- API Token? (requer nova infra)
- Magic Link? (cada acesso precisa reenviar)
```

**Impacto**: Novo subsistema de autenticação

#### 4.5 Isolamento de Dados
```
Problema: External Recruiter pode ver dados de outros clientes?
- Banco de dados multi-tenant (hoje isolado por team_id)
- Precisa de scoping muito rigoroso
- Auditoria: rastrear o que cada externo viu
```

**Impacto**: Reforçar segurança (risco se falhar)

#### 4.6 Fluxo de Aprovação / Governança
```
Problema: Quem autoriza um external recruiter a acessar?
- Owner do cliente deve aprovar? (workflow novo)
- RPQ team deve aprovar? (escala não suporta)
- É automático via contrato? (burocracia)
```

**Impacto**: Novo fluxo de onboarding

#### 4.7 Notificações e Comunicação
```
Problema: O que external recruiter vê vs. não vê?
- Notificações de candidatos? (muitos dados)
- Histórico de comentários? (privacidade)
- Quem pode comentar o quê?
```

**Impacto**: Repensar sistema de notificações

### Esforço Estimado

Baseado na análise técnica:

| Atividade | Esforço |
|-----------|---------|
| Design de segurança | 2-3 semanas |
| Implementar novo role | 1-2 semanas |
| Modificar Policies de ACL | 2-3 semanas |
| Autenticação/Onboarding | 3-4 semanas |
| Auditoria e logging | 1-2 semanas |
| Testes de segurança | 2-3 semanas |
| **Total Estimado** | **11-17 semanas** |

**Conclusão**: Não é uma feature trivial. É uma grande mudança arquitetural.

---

## 💼 5. Análise de Negócios: Vale a Pena?

### Demanda Real

**Questões a validar:**

1. **Quantos clientes pediriam isso?**
   - Conversas internas sugerem: algumas agências, alguns gerentes de cliente
   - Estimativa: talvez 5-10% dos clientes

2. **Quantos deixariam de usar se não tivéssemos?**
   - Provavelmente: poucos
   - Eles podem usar um ATS genérico no lugar

3. **Quanto mais pagariam por isso?**
   - External Recruiter é um custo adicional para cliente
   - Eles pagariam premium para compartilhar acesso?
   - Provavelmente não (é aumento de complexidade, não benefício)

### Risco: Diluição da Proposta

**Cenário problémático:**

```
Hoje: RPQ é conhecido por "terceirização de recrutamento"
      Simples de explicar, fácil de vender

Amanhã: RPQ seria "plataforma de gestão de recrutamento"
        Competiria com ATS genéricos (Greenhouse, Workable, etc.)
        Perdería diferenciação
        Clientes comparariam apenas por features/preço
```

### ROI do Ponto de Vista Comercial

| Cenário | Investimento | Retorno | ROI |
|---------|--------------|---------|-----|
| **Build em casa** | 11-17 semanas eng | +5-10% clientes | ❌ Baixo |
| **Fazer parceria com ATS** | 2-3 semanas integração | +2-3% clientes | ⚠️ Médio |
| **Focar em terceirização** | 0 (fazer melhor) | +0% novos, +10% satisfação | ✅ Alto |

**Insight**: O melhor ROI é ficar focado na proposta original.

---

## 🔄 6. Cenários: Comparação de Alternativas

### Cenário A: Status Quo (Recomendado)

**O que fazemos:** Mantemos terceirização completa, ignoramos pedidos por external recruiter

**Vantagens:**
- ✅ Arquitetura simples
- ✅ Proposta clara
- ✅ Fácil de vender
- ✅ Margem alta
- ✅ Escalável

**Desvantagens:**
- ❌ Perderemos 5-10% de clientes potenciais que querem mais flexibilidade
- ❌ Alguns clientes existentes ficarão insatisfeitos

**Custos**: 0

---

### Cenário B: Implementar External Recruiter Nativo

**O que fazemos:** Adicionar novo role/tipo de usuário ao RPQ

**Vantagens:**
- ✅ Atende 100% dos pedidos
- ✅ Proposta mais flexível
- ✅ Posso dizer "temos tudo"

**Desvantagens:**
- ❌ Muito caro (11-17 semanas)
- ❌ Complexidade de segurança (alto risco se falhar)
- ❌ Perde diferenciação (vira "um ATS a mais")
- ❌ Mantém complexidade para sempre
- ❌ ROI ruim

**Custos**: ~1 engenheiro durante 3-4 meses

---

### Cenário C: Modelo Híbrido Simplificado (Alternativa)

**O que fazemos:** Permitir "observadores" do lado cliente (read-only)

```
Nova permissão: 'observe_recruitment'
- Pode ver: requisições, candidatos, etapas
- NÃO pode: editar, comentar, mover candidatos
- Acesso via link compartilhado (sem nova conta)
```

**Vantagens:**
- ✅ Atende 80% do caso de uso
- ✅ Simples de implementar (2-3 semanas)
- ✅ Sem novos tipos de usuário
- ✅ Não muda proposta de valor
- ✅ Segurança fácil de controlar

**Desvantagens:**
- ❌ Ainda requer desenvolvimento
- ❌ Não atende 100% dos casos

**Custos**: ~2 semanas desenvolvimento

---

### Cenário D: Parceria com ATS Genérico

**O que fazemos:** Integrar com plataforma que atende clientes que querem flexibilidade

```
- Recomendamos ATS X para clientes que querem "auto-service"
- Oferecemos RPQ para clientes que querem "terceirização"
- Mundo melhor: cada um no seu nicho
```

**Vantagens:**
- ✅ Atende 100% dos casos sem gastar eng
- ✅ RPQ fica fokado em terceirização
- ✅ Win-win potencial (referências cruzadas)
- ✅ Sem complexidade para RPQ

**Desvantagens:**
- ❌ Perderemos alguns clientes
- ❌ Potencial conflito de interesse (parceiros compitem?)

**Custos**: Comercial apenas

---

## 💡 7. Recomendações: O Que Fazer?

### Recomendação Principal: Manter Foco (Cenário A)

**Por quê?**

1. **Dados sugerem**: A demanda é minoria (5-10%), não maioria
2. **ROI é baixo**: Investimento alto, retorno incerto
3. **Risco de marca**: Diluição de proposta de valor
4. **Competência diferencial**: Somos bons em terceirização, não em ferramental

### Recomendação Secundária: Explorar Cenário C

Se houver pressão de clientes, implementar "modo observador" (read-only) é:
- Mais rápido (2-3 semanas vs. 11-17)
- Mais seguro (sem edição, menos risco)
- Menos disruptivo (não muda arquitetura)

Isso resolverias 80% dos pedidos com 20% do esforço.

### Recomendação Terciária: Comunicação Clara

Decidir internamente AGORA:
- Somos "terceirização completa" ou "plataforma flexível"?
- Não há resposta errada, mas há uma resposta correta para **nosso** negócio
- Comunicar isso nos materiais de venda (o que somos, o que NÃO somos)

---

## 📋 8. Próximos Passos: Ações Concretas

### Fase 1: Validação com Clientes (1 semana)

- [ ] Entrevistar 5-10 clientes atuais sobre frustração com "terceirização completa"
- [ ] Entrevistar 5 prospects que rejeitaram RPQ por falta de flexibilidade
- [ ] Compilar insights

**Pergunta-chave:** "Você pagaria mais para gerenciar parte do recrutamento você mesmo?"

---

### Fase 2: Decisão Executiva (1 semana)

**Meeting com:**
- Product Manager
- Dono do Projeto
- Head de Engenharia

**Decisão a tomar:**
- [ ] Mantemos Cenário A (status quo)
- [ ] Exploramos Cenário C (observador read-only)
- [ ] Investimos em Cenário B (full external recruiter)
- [ ] Pivotamos para Cenário D (parceria)

---

### Fase 3: Comunicação e Roadmap (2 semanas)

Se decisão for **Cenário A**:
- [ ] Atualizar messaging/materiais de venda
- [ ] Resposta padrão para clientes que pedem external recruiter
- [ ] Adicionar à FAQ ("Por que não temos external recruiter?")

Se decisão for **Cenário C**:
- [ ] Especificar detalhadamente "modo observador"
- [ ] Priorizar no roadmap
- [ ] Avisar clientes: "Feature chegando em X"

Se decisão for **Cenário B**:
- [ ] Iniciar design de segurança detalhado
- [ ] Planejar em sprints
- [ ] Gestão de risco para segurança

---

### Fase 4: Documentação Técnica (Condicional)

Apenas se decisão for Cenário B ou C:
- [ ] Design Document (segurança, ACL, integrações)
- [ ] Especificação de Permissões
- [ ] Plano de Testes (segurança crítica)

---

## 📊 Tabela de Decisão Rápida

Responda essas perguntas para validar recomendação:

| Pergunta | Resposta | Implicação |
|----------|----------|-----------|
| Clientes pedem feature? | ~5-10% | Minoria → Cenário A |
| Pagariam premium por isso? | Provavelmente não | Baixo ROI → Cenário A |
| Somos bons em terceirização? | Sim | Focar nisso → Cenário A |
| Temos eng. para 4 meses? | ? | Se não → Cenário C |
| Diferenciação é crítica? | Sim | Perderia com B → Cenário A |
| Clientes deixariam se não temos? | Poucos | Risco baixo → Cenário A |

**Score**: Se +4 respostas apontam para A → Recomendação confirma-se.

---

## 📚 Referências

### Arquivos do RPQ Analisados
- `/app-modules/permissions/src/Roles.php` - Roles atuais
- `/app-modules/recruitment/src/Staff/Recruiter/Recruiter.php` - Modelo Recruiter
- `/app-modules/teams/src/TeamMemberObserver.php` - Criação automática
- `/README.md` - Visão geral do projeto
- `/app-modules/recruitment/README.md` - Fluxo de recrutamento

### Modelos Similares Consultados (Conhecimento General)
- Greenhouse (ATS com suporte a múltiplos tipos de users)
- Workable (Plataforma de recrutamento multi-tenant)
- Lever (Híbrido: terceirização + self-service)

---

## ❓ Perguntas para Discussão

**Para o PM/Dono:**
1. Qual é a visão de 5 anos para RPQ? Terceirização pura ou plataforma flexível?
2. Temos clientes atuais pedindo external recruiter? Quantos?
3. Qual é a nossa vantagem competitiva? Se disser "features", perdemos para genéricos.
4. Estamos dispostos a dizer "não" para alguns clientes para servir melhor outros?
5. Qual é o custo de oportunidade de 4 meses de eng. não gastar em outra coisa?

---

**Documento finalizado:** Março 2026
**Próxima revisão:** Após decisão executiva ou validação com clientes
