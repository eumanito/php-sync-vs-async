<?php

use OpenSwoole\Http\Server;
use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;

OpenSwoole\Runtime::enableCoroutine(true, OpenSwoole\Runtime::HOOK_ALL);

require_once('class.mysql.php');

$server = new OpenSwoole\HTTP\Server("0.0.0.0", 80);

$server->on("start", function () {
    echo "Servidor Swoole iniciado na porta 80." . PHP_EOL;
});

$pool = null;

$server->on("workerStart", function () use (&$pool) {
    $pool = new MySQLPool();
});

function insertData($conn) {
    if ($conn === null) {
        echo "Erro: Conexão com o banco de dados não está disponível." . PHP_EOL;
        return;
    }

    $payload = str_repeat('A', 12700); // Aproximadamente 12,7KB de dados
    $columns = [];
    $values = [];
    
    for ($i = 1; $i <= 15; $i++) {
        $columns[] = "coluna_varchar_$i";
        $values[] = "'" . substr($payload, ($i - 1) * 136, 136) . "'";
    }
    
    $columns = implode(", ", $columns);
    $values = implode(", ", $values);
    
    $sql = "INSERT INTO tabela1 ($columns) VALUES ($values)";
    
    try {
        $conn->query($sql);
    } catch (\Exception $e) {
        echo "Erro: " . $e->getMessage() . PHP_EOL;
    }
}

function updateData($conn, $id) {
    if ($conn === null) {
        echo "Erro: Conexão com o banco de dados não está disponível." . PHP_EOL;
        return;
    }

    $payload = str_repeat('B', 12700); // Aproximadamente 12,7KB de dados
    $columns = [];
    
    for ($i = 1; $i <= 15; $i++) {
        $columns[] = "coluna_varchar_$i = '" . substr($payload, ($i - 1) * 136, 136) . "'";
    }
    
    $columns = implode(", ", $columns);
    
    $sql2 = "UPDATE tabela1 SET $columns WHERE id = $id";
    
    try {
        $conn->query($sql2);
    } catch (\Exception $e) {
        echo "Erro: " . $e->getMessage() . PHP_EOL;
    }
}

function selectData($conn, $id) {
    if ($conn === null) {
        echo "Erro: Conexão com o banco de dados não está disponível." . PHP_EOL;
        return null;
    }

    $sql3 = "SELECT * FROM tabela1 WHERE id = $id";
    
    try {
        $result = $conn->query($sql3);
        return $result->fetchAll();
    } catch (\Exception $e) {
        echo "Erro: " . $e->getMessage() . PHP_EOL;
        return null;
    }
}

function deleteData($conn, $id) {
    if ($conn === null) {
        echo "Erro: Conexão com o banco de dados não está disponível." . PHP_EOL;
        return null;
    }

    $sql4 = "DELETE FROM tabela1 WHERE id = $id";
    
    try {
        $result = $conn->query($sql4);
        return $result->fetchAll();
    } catch (\Exception $e) {
        echo "Erro: " . $e->getMessage() . PHP_EOL;
        return null;
    }
}

$server->on("request", function ($request, $response) use (&$pool) {
    go(function() use ($request, $response, &$pool) {
        $conn = $pool->getConnection();

        if ($conn === null) {
            echo "Erro ao obter conexão com o banco de dados." . PHP_EOL;
            $response->status(500);
            $response->end("Erro ao obter conexão com o banco de dados.");
            return;
        }

        $response->header("Content-Type", "text/plain");

        // Obter e exibir o conteúdo do corpo da requisição POST
        $bodyContent = $request->rawContent();
        echo "Conteúdo do corpo da requisição POST: " . $bodyContent . PHP_EOL;


        // 1. Inserir uma nova nota fiscal
        $ids = [];
        for ($i = 0; $i < 14; $i++) {
            insertData($conn);
            $ids[] = $conn->lastInsertId();
            echo("Nota fiscal inserida com sucesso (ID: {$ids[$i]}).\n");
        }

        // 2. Atualizar a nota fiscal inserida 4 vezes
        for ($i = 0; $i < 4; $i++) {
            updateData($conn, $ids[$i]);
            echo("Nota fiscal atualizada com sucesso (ID: {$ids[$i]}).\n");
        }

        // 3. Consultar a nota utilizando id (index) 175 vezes
        for ($i = 0; $i < 175; $i++) {
            $randomId = $ids[array_rand($ids)];
            $result = selectData($conn, $randomId);
            if ($result) {
                echo("Nota fiscal consultada com sucesso (ID: $randomId).\n");
                //$response->write(json_encode($result));
            }
        }

        // 4. Deletar a nota fiscal
        deleteData($conn, $ids[0]);
        echo("Nota fiscal deletada com sucesso (ID: {$ids[0]}).\n");

        // 5. Gravar XML ~ 50KB
        $xmlContent = str_repeat('<elemento>valor</elemento>', 910); // Aproximadamente 50KB de dados
        $xmlContent = '<?xml version="1.0" encoding="UTF-8"?><root>' . $xmlContent . '</root>';
        
        $filePath = 'nota_fiscal_'.$ids[0].'.xml';
        
        if (file_put_contents($filePath, $xmlContent)) {
            echo "Arquivo XML criado e gravado com sucesso ($filePath).\n";
        } else {
            echo "Erro ao gravar o arquivo XML.\n";
        }

        // 6. Gravar PDF ~ 140KB
        $pdfContent = str_repeat('Conteudo qualquer para o PDF ', 7000);
        $pdfContent = "%PDF-1.4\n" . $pdfContent . "\n%%EOF";
        $filePath = 'nota_fiscal_'.$ids[0].'.pdf';
        
        if (file_put_contents($filePath, $pdfContent)) {
            echo "Arquivo PDF criado e gravado com sucesso ($filePath).\n";
        } else {
            echo "Erro ao gravar o arquivo PDF.\n";
        }

        $pool->releaseConnection($conn);
        $response->end();
    });
});

$server->on("shutdown", function() use (&$pool) {
    $pool->close();
});

$server->start();