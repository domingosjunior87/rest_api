# Rest Api com Symfony 5.3
Api Rest criada com Symfony 5.3, com CRUD de usuário, login e recuperação de senha

## Tecnologias
- PHP 7.3
- Symfony 5.3
- Doctrine
- MySql
- MongoDb

## Instalação
Execute o composer para instalar as dependências da aplicação

    composer install

## Configuração
### MySql
Para configurar o banco de dados do MySql, é necessário informar, no arquivo **.env**, o servidor do banco, usuário e senha. O nome do banco de dados é `rest_api`.

    DATABASE_URL="mysql://root:@127.0.0.1:3306/rest_api"

Caso não possua um servidor de MySql, pode ser criado um utilizando o Docker. [Esse link][1] ensina como fazer isso.

Após configurar os dados de acesso ao banco, basta executar o seguinte comando para criar o banco e as tabelas:

    composer criar-bd

### MongoDB
Para configurar o banco de dados MongoDB, é necessário informar, no arquivo **.env**, o servidor do banco, usuário e senha. O nome do banco de dados é `rest_api`.

    MONGODB_URL=mongodb://127.0.0.1:27017
    MONGODB_DB=rest_api

Caso não possua um servidor de MongoDB, pode ser criado um utilizando o Docker. [Esse link][2] ensina como fazer isso.

Após configurar os dados de acesso ao banco, a própria aplicação irá criar o banco de dados quando for utilizar o mesmo.

### Mensageria para envio de E-mail
A aplicação utiliza o [SendGrid][3] para envio de e-mails. Para configurar esse serviço, é necessário informar, no arquivo **.env**, a chave fornecida pelo SendGrid para enviar os emails.

    MAILER_DSN=sendgrid://CHAVE_SECRETA_AQUI@default

A aplicação utiliza a [mensageria do Symfony][4] para enviar os e-mails, através do [Doctrine][5]. Após configurar o SendGrid, para iniciar o worker que irá fazer funcionar a mensageria, basta executar o seguinte comando:

    php bin/console messenger:consume async

## Utilização
A Api possui 8 endpoints, listados abaixo. Alguns precisam de autenticação para que funcionem, nesses casos, é necessário informar o seguinte cabeçalho na requisição:

    Authorization: Bearer TOKEN_DE_AUTENTICACAO

O Token de autenticação é conseguido após efetuar a chamada ao endpoint de login.

### POST /create_user
Endpoint, do tipo POST, para criar um novo usuário na aplicação. É necessário informar os seguintes campos:
- **nome**
- **email**
- **senha**

Esse endpoint não requer autenticação.

### POST /login
Endpoint, do tipo POST, para efetuar o login na aplicação. É necessário informar os seguintes campos:
- **email**
- **senha**

Se o email e senha estiverem corretos, a resposta será um token que deverá ser utilizado nas requisições que precisarem de autenticação.

Esse endpoint não requer autenticação.

### GET /profile
Endpoint, do tipo GET, para obter os dados do usuário após o login na aplicação. Não é necessário informar nenhum campo adicional.

Esse endpoint requer autenticação.

### GET /usuario
Endpoint, do tipo GET, para obter a lista de usuários da aplicação. Não é necessário informar nenhum campo adicional.

Esse endpoint requer autenticação.

### PUT /usuario/{id}
Endpoint, do tipo PUT, para atualizar os dados de um usuário. É necessário informar os seguintes campos:
- **id**: Identificador do usuário, na URL do endpoint
- **email**: Novo email do usuário, no corpo da requisição
- **nome**: Novo nome do usuário, no corpo da requisição

Esse endpoint requer autenticação.

### DELETE /usuario/{id}
Endpoint, do tipo DELETE, para excluir um usuário. É necessário informar os seguintes campos:
- **id**: Identificador do usuário, na URL do endpoint

Esse endpoint requer autenticação.

### POST /recuperar_senha
Endpoint, do tipo POST, para iniciar o processo de alteração da senha de um usuário. É necessário informar os seguintes campos:
- **email**: Email do usuário que terá a senha alterada

Após a utlização desse endpoint, um e-mail será enviado para o usuário, contendo um código de confirmação, que deverá ser utilizado no endpoint seguinte.

Esse endpoint não requer autenticação.

### POST /nova_senha
Endpoint, do tipo POST, para alterar a senha de um usuário. É necessário informar os seguintes campos:
- **email**: Email do usuário que terá a senha alterada
- **codigo**: Código enviado pelo endpoint anterior para o email do usuário
- **senha**: Nova senha

Após a utlização desse endpoint, o usuário já será capaz de efetuar o login com o e-mail e a nova senha informada.

Esse endpoint não requer autenticação.

### Arquivo Postman
Há um arquivo do postman nesse repositório, chamado ***symfony.postman_collection.json*** para facilitar o teste de utilização dessa Api.

[1]: https://github.com/DevCia/criando-instancias-dos-bancos-de-dados-com-docker#mysql-server
[2]: https://github.com/DevCia/criando-instancias-dos-bancos-de-dados-com-docker#mongodb
[3]: https://sendgrid.com/
[4]: https://symfony.com/doc/current/messenger.html
[5]: https://symfony.com/doc/current/messenger.html#doctrine-transport