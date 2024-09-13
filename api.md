Neste exemplo vamos construir uma API básica em PHP usando o banco de dados MySQL e a extensão PDO para uma lista de tarefas simples.

Antes de começar, algumas premissas:

* há um servidor de banco de dados escutando em um host e porta conhecidos (se estiver no seu computador, deverá estar em `localhost:3306`);
* nesse banco de dados, há um esquema criado para a sua aplicação;
* o interpretador do PHP está instalado;
* a extensão pdo_mysql está habilitada no seu PHP;
* você tem noções de PHP e de APIs.

---

## Configurando o banco de dados

Primeiro, vamos criar uma tabela chamada "tasks" no banco de dados para armazenar as tarefas. Execute a seguinte query SQL para criar a tabela:

```
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    completed BOOLEAN NOT NULL DEFAULT 0
);
```

## Configurando a conexão com o banco de dados

Como potencialmente precisaremos lidar com o banco de dados em mais de um ponto no sistema, uma boa prática é deixar os dados relacionados a esta conexão em um arquivo à parte.

Por isso, vamos criar um arquivo chamado "database.php" para armazenar as informações de conexão com o banco de dados e criar uma variável `$conn` que usaremos para acessar a conexão criada. Insira o seguinte código no arquivo:

```
<?php

$host = 'localhost';
$db = 'nome_do_banco_de_dados';
$port = 3306;
$user = 'nome_do_usuario';
$pass = 'senha_do_usuario';

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo 'Erro na conexão com o banco de dados: ' . $e->getMessage();
    exit;
}
```

Lembre-se de substituir "nome_do_banco_de_dados", "nome_do_usuario" e "senha_do_usuario" pelos valores corretos, assim como o host e a porta se for necessário.

Para testar se a conexão está funcionando, entre no diretório e execute o comando `php database.php`. Se nenhum resultado for exibido, então está tudo certo.

---

## Criando a API (método 1)

Uma forma simples de pensar na API é usando um arquivo para cada endpoint.

### get_tasks

Crie um arquivo chamado `get_tasks.php` para o endpoint que busca todas as tarefas:

```
<?php

require 'database.php';

try {
    $stmt = $conn->query('SELECT * FROM tasks');
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($tasks);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
```

### add_task

Crie um arquivo chamado `add_task.php` para o endpoint que adiciona uma nova tarefa:

```
<?php

require 'database.php';

if (isset($_POST['title'])) {
    $title = $_POST['title'];

    try {
        $stmt = $conn->prepare('INSERT INTO tasks (title) VALUES (:title)');
        $stmt->bindParam(':title', $title);
        $stmt->execute();
        $taskId = $conn->lastInsertId();
        echo json_encode(['id' => $taskId, 'title' => $title, 'completed' => false]);
    } catch(PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'O título da tarefa é obrigatório']);
}
```

### complete_task

Crie um arquivo chamado `complete_task.php` para o endpoint que marca uma tarefa como concluída:

```
<?php

require 'database.php';

if (isset($_POST['id'])) {
    $taskId = $_POST['id'];

    try {
        $stmt = $conn->prepare('UPDATE tasks SET completed = 1 WHERE id = :id');
        $stmt->bindParam(':id', $taskId);
        $stmt->execute();
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'O ID da tarefa é obrigatório']);
}
```

### delete_task

Crie um arquivo chamado `delete_task.php` para o endpoint que deleta uma tarefa:

```
<?php

require 'database.php';

if (isset($_POST['id'])) {
    $taskId = $_POST['id'];

    try {
        $stmt = $conn->prepare('DELETE FROM tasks WHERE id = :id');
        $stmt->bindParam(':id', $taskId);
        $stmt->execute();
        echo json_encode(['success' => true]);
    } catch(PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'O ID da tarefa é obrigatório']);
}
```

---

### Códigos importantes

Agora, vamos entender alguns trechos importantes dos códigos acima:

```
$stmt = $conn->query('SELECT * FROM tasks');
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

* O trecho acima executa uma consulta SQL para selecionar todas as tarefas da tabela `tasks`.
* `query('SELECT * FROM tasks')` executa a consulta e retorna um objeto `PDOStatement` que representa o resultado.
* `fetchAll(PDO::FETCH_ASSOC)` recupera todas as linhas do resultado como um array associativo, onde a chave é o nome da coluna.

```
if (isset($_POST['title'])) {
    $title = $_POST['title'];
    // ...
}
```

* O trecho acima verifica se o parâmetro `title` foi enviado através de uma requisição POST usando a superglobal `$_POST`.
* `$_POST['title']` recupera o valor do parâmetro `title` enviado no corpo da requisição.
* Essa verificação é importante para garantir que o título da tarefa tenha sido fornecido antes de prosseguir com a inserção.

```
$stmt = $conn->prepare('INSERT INTO tasks (title) VALUES (:title)');
$stmt->bindParam(':title', $title);
$stmt->execute();
```

* O trecho acima prepara uma instrução SQL para inserir uma nova tarefa na tabela `tasks`.
* `prepare('INSERT INTO tasks (title) VALUES (:title)')` prepara a instrução SQL com um parâmetro nomeado `:title`.
* `bindParam(':title', $title)` vincula o valor do parâmetro `:title` à variável `$title`.
* `execute()` executa a instrução preparada.

---

### Testando

Para testar a API, você pode colocar os arquivos acima em um servidor HTTP da sua escolha que esteja configurando com o PHP (como por exemplo o Apache disponível no XAMPP) ou executar o servidor local de testes do PHP através do comando `php -S localhost:8000` ou `php -S 0.0.0.0:8080` para que o servidor escute a partir de qualquer interface (com a porta da sua escolha).

Uma vez que a API esteja disponível em uma porta, você pode criar requests para os arquivos usando alguma ferramenta de testes de API (insomnia, postman, thunder client).

Aqui vamos fazer testes usando o Thunder Client, diretamente do VSCode:

[![Image description](https://res.cloudinary.com/practicaldev/image/fetch/s--MoY5gCi3--/c_limit%2Cf_auto%2Cfl_progressive%2Cq_auto%2Cw_800/https://dev-to-uploads.s3.amazonaws.com/uploads/articles/wjvcu7rbg5fw5qyiehl2.png)](https://res.cloudinary.com/practicaldev/image/fetch/s--MoY5gCi3--/c_limit%2Cf_auto%2Cfl_progressive%2Cq_auto%2Cw_800/https://dev-to-uploads.s3.amazonaws.com/uploads/articles/wjvcu7rbg5fw5qyiehl2.png)

[![Image description](https://res.cloudinary.com/practicaldev/image/fetch/s--Fxl7Jk1e--/c_limit%2Cf_auto%2Cfl_progressive%2Cq_auto%2Cw_800/https://dev-to-uploads.s3.amazonaws.com/uploads/articles/bo8gykna9e5k7t91fq67.png)](https://res.cloudinary.com/practicaldev/image/fetch/s--Fxl7Jk1e--/c_limit%2Cf_auto%2Cfl_progressive%2Cq_auto%2Cw_800/https://dev-to-uploads.s3.amazonaws.com/uploads/articles/bo8gykna9e5k7t91fq67.png)



material de apoio:

[https://dev.to/ranierivalenca/api-basica-com-php-e-mysql-via-pdo-para-uma-todo-list-46da](https://dev.to/ranierivalenca/api-basica-com-php-e-mysql-via-pdo-para-uma-todo-list-46da)
