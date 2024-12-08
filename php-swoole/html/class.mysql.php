<?php 

use Swoole\Coroutine;   
use Swoole\Coroutine\Channel;

class MySQLPool
{
    private $pool;
    private $size;

    public function __construct(int $size = 10)
    {
        $this->size = $size;
        $this->pool = new Channel($size);
        
        for ($i = 0; $i < $size; $i++) {
            go(function() {
                $conn = $this->createConnection();
                
                if ($conn) {
                    $this->pool->push($conn);
                }
            });
        }
    }

    private function createConnection(): ?PDO
    {
        try {
            $conn = new \PDO('mysql:host=database-1.cluster-cniuugikualy.us-east-1.rds.amazonaws.com;port=3306;dbname=banco1', 'admin', 'senha');
            $conn->setAttribute(\PDO::ATTR_PERSISTENT, true);
            echo "Conexão com o banco de dados estabelecida." . PHP_EOL;
            return $conn;
        } catch (\PDOException $e) {
            echo "Erro ao obter conexão com o banco de dados: " . $e->getMessage() . PHP_EOL;
            return null;
        }
    }

    public function getConnection(float $timeout = 30): ?PDO
    {
        $conn = $this->pool->pop($timeout);

        if ($conn && !$this->isConnectionAlive($conn)) {
            echo "Conexão perdida. Reconectando..." . PHP_EOL;
            $conn = $this->createConnection();
        }

        return $conn ?: null;
    }

    private function isConnectionAlive(PDO $conn): bool
    {
        try {
            return $conn->query('SELECT 1') !== false;
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function releaseConnection(PDO $conn): void
    {
        $this->pool->push($conn);
    }

    public function close(): void
    {
        for ($i = 0; $i < $this->size; $i++) {
            $conn = $this->pool->pop();
            if ($conn) {
                $conn = null;
            }
        }
    }
}