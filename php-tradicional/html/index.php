<?php

function obterConexaoBd() {
    $conn = new mysqli('database-1.cluster-cniuugikualy.us-east-1.rds.amazonaws.com', 'admin', 'senha', 'banco1');
    if ($conn->connect_error) {
        die("Erro na conexão com o banco de dados: " . $conn->connect_error);
    }
    return $conn;
}

function insertData($conn) {
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

    if ($conn->query($sql) === TRUE) {
        echo "Nota fiscal inserida com sucesso.\n";
        return $conn->insert_id;
    } else {
        echo "Erro ao inserir a nota fiscal: " . $conn->error . "\n";
        return null;
    }
}

function updateData($conn, $id) {
    $payload = str_repeat('B', 12700); // Aproximadamente 12,7KB de dados
    $columns = [];
    
    for ($i = 1; $i <= 15; $i++) {
        $columns[] = "coluna_varchar_$i = '" . substr($payload, ($i - 1) * 136, 136) . "'";
    }
    
    $columns = implode(", ", $columns);
    
    $sql2 = "UPDATE tabela1 SET $columns WHERE id = $id";
    
    if ($conn->query($sql2) === FALSE) {
        echo "Erro ao atualizar a nota fiscal: " . $conn->error . "\n";
    }
}

function selectData($conn, $id) {
    $sql3 = "SELECT * FROM tabela1 WHERE id = $id";
    
    $result = $conn->query($sql3);
    if ($result === FALSE) {
        echo "Erro ao consultar a nota fiscal: " . $conn->error . "\n";
    } else {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo(json_encode($data) . "\n");    
    }
}

function deleteData($conn, $id) {
    $sql4 = "DELETE FROM tabela1 WHERE id = $id";
    
    if ($conn->query($sql4) === FALSE) {
        echo "Erro ao excluir a nota fiscal: " . $conn->error . "\n";
    }
}

function createAndSaveXML($id) {
    $xmlContent = str_repeat('<elemento>valor</elemento>', 910); // Aproximadamente 50KB de dados
    $xmlContent = '<?xml version="1.0" encoding="UTF-8"?><root>' . $xmlContent . '</root>';
    
    $filePath = 'nota_fiscal_'.$id.'.xml';
    
    if (file_put_contents($filePath, $xmlContent)) {
        echo "Arquivo XML criado e gravado com sucesso ($filePath).\n";
    } else {
        echo "Erro ao gravar o arquivo XML.\n";
    }
}

function createAndSavePDF($id) {
    $filePath = 'nota_fiscal_'.$id.'.pdf';
    
    // 140KB
    $pdfContent = str_repeat('Conteudo qualquer para o PDF ', 7000);
    
    $pdfContent = "%PDF-1.4\n" . $pdfContent . "\n%%EOF";
    
    if (file_put_contents($filePath, $pdfContent)) {
        echo "Arquivo PDF criado e gravado com sucesso ($filePath).\n";
    } else {
        echo "Erro ao gravar o arquivo PDF.\n";
    }
}

function executar() {
    $conn = obterConexaoBd();
    
    // 1. Inserir uma nova nota fiscal
    $ids = [];
    for ($i = 0; $i < 14; $i++) {
        $ids[] = insertData($conn);
        echo("Nota fiscal inserida com sucesso (ID: {$ids[$i]}).\n");
    }

    // 2. Atualizar a nota fiscal inserida 4 vezes
    for ($i = 0; $i < 4; $i++) {
        updateData($conn, $ids[$i]);
    }

    // 3. Consultar a nota utilizando id (index) 175 vezes
    for ($i = 0; $i < 175; $i++) {
        $randomId = $ids[array_rand($ids)];
        $result = selectData($conn, $randomId);
        if ($result) {
            echo("Nota fiscal consultada com sucesso (ID: $randomId).\n");
            //echo(json_encode($result));
        }
    }

    // 4. Deletar a nota fiscal
    deleteData($conn, $ids[0]);

    // 5. Gravar XML ~ 50KB
    createAndSaveXML($ids[0]);

    // 6. Gravar PDF ~ 140KB
    createAndSavePDF($ids[0]);

    $conn->close();
}

executar();
