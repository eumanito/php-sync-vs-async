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

#### 5.1. Configurando JMeter
- Acesse o menu Arquivo > Templates e selecione `Simple HTTP request`
- Acesse o menu Editar > Adicionar > Elemento de Configuração > `Variáveis Definidas pelo Usuário`
- Defina as variáveis: 
    - URL: http://localhost:8080
    - Method: GET
- Acesse o menu Editar > Adicionar > Ouvinte > `Ver Árvore de Resultados`
- Acesse o menu Editar > Adicionar > Ouvinte > `Relatório de Sumário`
- Acesse o menu Editar > Adicionar > Threads (User) > `Grupo de Usuários`
- Defina a variável:
    - Número de usuários virtuais (threads): 100

### 5.2. Ou utilize o plano criado
- Acesse o menu Arquivo > Abrir
- Selecione o arquivo `plano.jmx` (esta na pasta [jmeter](https://github.com/eumanito/php-sync-vs-async/tree/main/jmeter))

### 6. Extras
Para análise estatística foi utilizado o R, com os métodos ANOVA (Análise de Variância e Test t).
Na pasta [R](https://github.com/eumanito/php-sync-vs-async/tree/main/R)  deste repositório estão os algoritmos utilizados.

Na pasta [cpu-monitor](https://github.com/eumanito/php-sync-vs-async/tree/main/cpu-monitor) alguns scripts para monitoramento de CPU. Estes não foram utilizados para esta análise, mas deixei compartilhado para análises futuras.

### 7. Artigo
Todo o material aqui contido fez parte do desenvolvimento do [artigo](https://github.com/eumanito/php-sync-vs-async/tree/main/resultados/Artigo.pdf) para o meu trabalho de conclusão de curso (TCC). 