<?php

final class Connection
{

    private static $connection;

    /**
     * Singleton: Método construtor privado para impedir classe de gerar instancias
     */

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }

    /**
     * Método stático que permite o carregamento do artigo
     * @param $arquivo string
     * @return array
     */
    private static function load(): array
    {
        $arquivo = "configdb.ini";

        if (file_exists($arquivo)) {
            $dados = parse_ini_file($arquivo);
        } else {
            throw new Exception('Erro: Arquivo não encontrado');
        }

        return $dados;
    }

    /**
     * Método montar string de conexão e gerar o objeto PDO
     * @param $dados array
     * @return PDO
     */
    private static function make(array $dados): PDO
    {
        $sgbd = isset($dados['sgbd']) ? $dados['sgbd'] : null;
        $usuario = isset($dados['usuario']) ? $dados['usuario'] : null;
        $senha = isset($dados['senha']) ? $dados['senha'] : null;
        $banco = isset($dados['banco']) ? $dados['banco'] : null;
        $servidor = isset($dados['servidor']) ? $dados['servidor'] : null;
        $porta = isset($dados['porta']) ? $dados['porta'] : null;

        if (!is_null($sgbd)) {
            switch (strtoupper($sgbd)) {
                case 'MYSQL':
                    $porta = isset($porta) ? $porta : 3306;
                    return new PDO("mysql:host={$servidor};port={$porta};dbname={$banco};", $usuario, $senha);
                    break;
                case 'MSSQL':
                    $porta = isset($porta) ? $porta : 1433;
                    return new PDO("mssql:host={$servidor},{$porta};dbname={$banco};", $usuario, $senha);
                    break;
                case 'PGSQL':
                    $porta = isset($porta) ? $porta : 5432;
                    return new PDO("pgsql:dbname={$banco}; user={$usuario}; password={$senha}; host={$servidor}; port={$porta}");
                    break;
                case 'SQLITE':
                    return new PDO("sqlite:{$banco}");
                    break;
                case 'OCI8':
                    return new PDO("oci:dbname={$banco}", $usuario, $senha);
                    break;
                case 'FIREBIRD':
                    return new PDO("firebird:dbname={$banco}", $usuario, $senha);
                    break;

                default:
                    throw new Exception("Erro: tipo de banco de dados não encontrado");
                    break;
            }
        } else {
            throw new Exception("Erro: tipo de banco de dados não informado");
        }
    }

    /**
     * Método estático que devolve a instancia ativa
     */
    public static function getInstance(): PDO
    {
        if (self::$connection == null) {
            //Recebe os dados do arquivo
            self::$connection = self::make(self::load());
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$connection->exec("SET NAMES utf8");
        }
        return self::$connection;
    }
}
