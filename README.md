# PHP sync vs PHP async
Este repositório contém dois ambientes para realizar testes de carga comparativos entre PHP síncrono (tradicional com Apache) e PHP assíncrono (utilizando Swoole).

## Pré-requisitos
- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)
- [Apache JMeter](https://jmeter.apache.org/)

## Estrutura do Projeto
- `Dockerfile`: Define a imagem Docker personalizada para o ambiente PHP com Apache.
- `docker-compose.yml`: Define os serviços Docker e suas configurações.
- `create_table.sql`: Script SQL para criar a tabela inicial no banco de dados MySQL.
- `apache.conf`: Arquivo de configuração do Apache (modelo síncrono).
- `html/`: Diretório contendo o arquivo PHP com a lógica.

## Instruções para Uso

### 1. Clonar o Repositório
```sh
git clone https://github.com/eumanito/php-sync-vs-async.git
cd php-sync-vs-async
```

### 2. Construir e Iniciar os Containers
```sh
docker-compose up -d --build
```

### 3. Acessar o phpMyAdmin
O phpMyAdmin estará disponível em http://localhost:8081.
- Usuário: usuario
- Senha: senha
- Host: mysql

### 4. Acessar a Aplicação
Ao executar uma requisição GET em http://localhost:8080 a aplicação irá criar, editar e excluir registros do banco de dados.

### 5. Teste de Carga
Para criar e executar os testes de carga, foi utilizado o [Apache JMeter](https://jmeter.apache.org/). A seguir, inclua as ferramentas sugeridas no JMeter para o execução e análise.
#### 5.1 Configurando JMeter
5.1.1 Acesse o menu Arquivo > Templates e selecione `Simple HTTP request`

5.1.2 Acesse o menu Editar > Adicionar > Elemento de Configuração > `Variáveis Definidas pelo Usuário`

Defina as variáveis: 
- URL: http://localhost:8080
- Method: GET

5.1.3 Acesse o menu Editar > Adicionar > Ouvinte > `Ver Árvore de Resultados`

5.1.4 Acesse o menu Editar > Adicionar > Ouvinte > `Relatório de Sumário`

5.1.5 Acesse o menu Editar > Adicionar > Threads (User) > `Grupo de Usuários`

Defina a variável:
- Número de usuários virtuais (threads): 100
