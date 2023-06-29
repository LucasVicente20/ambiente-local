<?php

/**
 * Portal da DGCO.
 *
 * PHP version 5.2.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   PortalDGCO
 *
 * @author     Pitang Agile TI <contato@pitang.com>
 * @copyright  2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license    http://www.php.net/license/3_01.txt PHP License 3.01
 */

/**
 */
class Negocio_Repositorio_AtaRegistroPrecoExterna extends Negocio_Repositorio_Abstrato
{

    /**
     * [$tabela description]
     *
     * @var string
     */
    private $tabela = 'sfpc.tbataregistroprecoexterna';

    /**
     *
     * @param stdClass $entidade
     */
    public function inserir($entidade)
    {
        $resultado = $this->getConexao()->autoExecute($this->tabela, (array) $entidade, DB_AUTOQUERY_INSERT);

        if (PEAR::isError($resultado)) {
            $this->getConexao()->rollback();
            return $resultado->getMessage();
        }

        return $entidade;
    }

    public function alterar($entidade)
    {
        $criterio = '';
        $resultado = $this->getConexao()->autoExecute($this->tabela, (array) $entidade, DB_AUTOQUERY_UPDATE, $criterio);

        if (PEAR::isError($resultado)) {
            $this->getConexao()->rollback();
            return $resultado->getMessage();
        }

        return $entidade;
    }

    public function listarTodos()
    {}

    /**
     *
     * @param Negocio_Entidade_AtaRegistroPrecoExterna $entidade
     */
    public function filtrarAtaExterna(Negocio_Entidade_AtaRegistroPrecoExterna $entidade)
    {
        $sql = new Dados_Sql_AtaRegistroPrecoExterna();
        $resultado = ClaDatabasePostgresql::executarSQL($sql->filtrarAtaExterna($entidade));

        ClaDatabasePostgresql::hasError($resultado);
        return $resultado;
    }

    public function getAtaRegistroPrecoNova(Negocio_Entidade_AtaRegistroPrecoExterna $entidade)
    {
        $sql = sprintf("
            SELECT *
              FROM sfpc.tbataregistropreconova
             WHERE carpnosequ = %d
            ", $entidade->getCarpnosequ());

        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);
        return $resultado;
    }

    /**
     *
     * @param Negocio_ValorObjeto_Carpnosequ $carpnosequ
     */
    public function procurar(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        $sql = sprintf("SELECT * FROM " . $this->tabela . " WHERE carpnosequ = %d", $carpnosequ->getValor());
        $res = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($res);
        return current($res);
    }

    /**
     *
     * @param Negocio_ValorObjeto_Carpnosequ $carpnosequ
     * @param Negocio_ValorObjeto_Aarpexanon $aarpexanon
     * @return NULL
     */
    public function consultarAtaRegistroPrecoAtiva(Negocio_ValorObjeto_Carpnosequ $carpnosequ, Negocio_ValorObjeto_Aarpexanon $aarpexanon)
    {
        $sql = new Dados_Sql_AtaRegistroPrecoExterna();
        $resultado = ClaDatabasePostgresql::executarSQL($sql->sqlSelecionaPorAnoNumeracaoECodigoSequencial($aarpexanon->getValor(), $carpnosequ->getValor()));

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }
}
