# SaldoUp

O **SaldoUp** é uma aplicação web full-stack para gerenciamento financeiro pessoal. Com uma interface direta e responsiva, reúne em um só lugar o registro de entradas e saídas, a organização dos lançamentos e o acompanhamento do saldo geral.

## Funcionalidades

- Cadastro e consulta de transações financeiras.
- Resumo visual de entradas, saídas e saldo geral.
- Classificação dos lançamentos por categoria.
- Exclusão de transações.
- Login e separação dos lançamentos por usuário.
- Análise financeira opcional com recomendações geradas pelo Gemini.
- Assistente visual interativo Clippy, com mensagens e dicas sobre organização financeira.

## Diferenciais e arquitetura

### Backend e persistência

O backend é implementado em PHP, sem framework, e utiliza **PDO** para conectar à base de dados **MySQL**. Os dados dos usuários e das transações são persistidos nas tabelas `usuarios` e `transacoes`, criadas ou atualizadas pelo script [`schema.sql`](./schema.sql). Cada transação pertence a um usuário. As senhas são armazenadas como hashes; as credenciais de conexão podem ser definidas por variáveis de ambiente ou no arquivo `.env` local.

### Categorização

Cada lançamento é organizado em uma categoria nativa do SaldoUp:

- Tecnologia & Dev
- Alimentação
- Lazer & Rolês
- Saúde & Suplementos
- Contas & Fixas
- Outros (padrão)

### Análise financeira com IA

O módulo de análise envia as transações para a API do **Google AI Studio (Gemini)**. A integração é feita no backend por meio de cURL e é opcional: sem uma chave de API configurada, as funcionalidades de controle financeiro continuam disponíveis.

### Interface e experiência

A interface combina **Dark Mode**, referências visuais de fintechs, cards de resumo, detalhes em estilo glass, animações e adaptação a diferentes tamanhos de tela. O assistente interativo **Clippy** complementa a experiência com mensagens e dicas financeiras.

## Requisitos

- PHP com as extensões **PDO MySQL** habilitadas.
- MySQL.
- Servidor web, como Apache do XAMPP, ou o servidor embutido do PHP.
- Extensão **cURL** do PHP para habilitar a análise com Gemini.
- Chave de API do Gemini para utilizar a análise com IA.

## Instalação com XAMPP

1. Copie ou clone a pasta do projeto para `C:\xampp\htdocs\saldo-Up`.
2. Abra o painel do XAMPP e inicie **Apache** e **MySQL**.
3. Acesse o phpMyAdmin em <http://localhost/phpmyadmin>.
4. Importe o arquivo `schema.sql`. O script cria o banco `saldoup`, a tabela `usuarios`, a tabela `transacoes` e a coluna que associa cada transação ao seu usuário. Se já houver uma tabela `transacoes`, o script adiciona a coluna quando necessário e associa os registros existentes ao usuário de teste de ID `1`.
5. Copie `.env.example` para `.env` na raiz do projeto e ajuste as configurações de conexão. O nome do banco precisa corresponder ao que o schema criou:

   ```powershell
   Copy-Item .env.example .env
   ```

6. Acesse <http://localhost/saldo-Up/>.

O `.env` é opcional para a leitura da configuração em si: se ele não existir, a aplicação usa os valores padrão definidos em `conexao.php`. Como o schema cria o banco `saldoup`, configure `DB_NAME=saldoup` no `.env` para instalações locais (ou ajuste `DB_NAME` para o banco utilizado). Sem configuração, o nome padrão é `railway`. O `.env` está ignorado pelo Git; nunca publique suas credenciais ou chaves de API.

Em ambientes hospedados como Railway, a conexão reconhece as variáveis `MYSQLHOST`, `MYSQLDATABASE`, `MYSQLUSER`, `MYSQLPASSWORD` e `MYSQLPORT`. Quando não estiverem definidas, usa `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` e `DB_PORT`, respectivamente. A porta padrão é `3306`. Em caso de falha, os detalhes ficam no log do PHP; `APP_DEBUG=true` habilita a exibição dos detalhes de conexão na resposta e deve ser usado apenas durante o diagnóstico, nunca em produção.

O schema inclui uma conta para primeiro acesso:

| Usuário | Senha |
| --- | --- |
| `lucas` | `123` |

Use essa conta apenas para teste local. A tela de login fica em <http://localhost/saldo-Up/login.php> e o cadastro de novas contas em <http://localhost/saldo-Up/cadastro.php>. O cadastro exige um usuário de 3 a 100 caracteres e uma senha de pelo menos 6 caracteres, que é armazenada com hash. Após criar a conta, a sessão é iniciada automaticamente; os lançamentos cadastrados ficam associados a esse usuário. O botão **Sair** encerra a sessão. Senhas em texto puro de instalações anteriores são convertidas para hash após o primeiro login válido.

## Execução com o servidor embutido do PHP

Depois de importar `schema.sql` para o MySQL e configurar a conexão, abra um terminal na pasta do projeto e execute:

```bash
php -S localhost:8000
```

Em seguida, acesse <http://localhost:8000>.

## Configuração da API Gemini

A análise financeira é opcional. Para habilitá-la, adicione a chave de API ao `.env` na raiz do projeto:

```dotenv
GEMINI_API_KEY=sua_chave_de_api
GEMINI_MODEL=gemini-3.1-flash-lite
```

`GEMINI_MODEL` também é opcional; quando omitido, o SaldoUp utiliza `gemini-3.1-flash-lite`. Mantenha a chave privada: não a publique, não a inclua em capturas de tela e não envie o arquivo `.env` para um repositório público.

## Estrutura principal

```text
.
├── assets/       # Ícones e recursos visuais
├── public/       # Recursos públicos da interface
├── scripts/      # Comportamentos em JavaScript
├── styles/       # Estilos da aplicação
├── conexao.php   # Configuração e conexão PDO com MySQL
├── ia_analise.php # Endpoint de análise com Gemini
├── index.php     # Tela principal e resumo financeiro
└── schema.sql    # Criação do banco e da tabela de transações
```

## Autoria e licença

Desenvolvido por **Lucas Araujo de Souza (Imlucsz)**.

O SaldoUp é distribuído sob a **Licença MIT**. Consulte [`LICENSE`](./LICENSE) para ver o texto completo.
