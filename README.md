# TIFVini

Sistema de controle de presença e movimentação de alunos com integração de comunicação em tempo real via MQTT, autenticação por perfil e histórico de frequência.

## Sobre o projeto

Este projeto foi desenvolvido para registrar a entrada e saída de alunos em um ambiente escolar, com apoio de dispositivos/sensores conectados por MQTT. A ideia é centralizar o controle de acesso, manter um histórico de movimentações e gerar uma visão clara da frequência dos alunos para professores e administradores.

A aplicação combina:

- backend em Laravel para autenticação, regras de negócio e persistência dos dados;
- comunicação com dispositivos via protocolo MQTT;
- painel web para professores acompanhar relatórios e históricos;
- fluxo para alunos realizarem registro de entrada/saída e consultar seu próprio histórico;
- estrutura preparada para integração com um front-end/mobile em React Native dentro da pasta `controle/controle`.

## Funcionalidades

- cadastro e autenticação de usuários;
- diferenciação entre perfil de aluno e professor;
- registro de entrada e saída por movimentação;
- cálculo e armazenamento de frequência;
- histórico de acessos e relatórios por usuário;
- controle de acesso exclusivo para professores em áreas administrativas;
- integração com tópicos MQTT para envio/recebimento de eventos.

## Stack tecnológica

- PHP 8.3
- Laravel 13
- MySQL / SQLite para persistência
- MQTT com `php-mqtt/client`
- React Native / Expo (na pasta `controle/controle`)

## Estrutura do repositório

- `app/` — models, controllers, serviços e regras de negócio
- `routes/` — rotas web e API
- `database/migrations/` — estrutura das tabelas
- `config/` — configurações da aplicação, incluindo MQTT
- `controle/controle/` — aplicação cliente mobile/web em Expo
- `tests/` — testes automatizados

## Requisitos

- PHP 8.3+
- Composer
- Node.js e npm
- Banco de dados configurado no `.env`
- Broker MQTT acessível para receber e publicar mensagens

## Como rodar

1. Instale as dependências do PHP:

```bash
composer install
```

2. Configure o ambiente:

```bash
cp .env.example .env
php artisan key:generate
```

3. Configure as variáveis do banco e do MQTT no arquivo `.env`.

4. Execute as migrações:

```bash
php artisan migrate
```

5. Inicie a aplicação:

```bash
php artisan serve
```

6. Para o frontend mobile/web da pasta `controle/controle`:

```bash
cd controle/controle
npm install
npm start
```

## Observações

Este projeto foi pensado como uma solução de controle de presença com automação e monitoramento em tempo real, ideal para uso em ambientes acadêmicos ou institucionais que precisam registrar acessos de forma prática e organizada.

## Licença

Este projeto está sob a licença MIT.
