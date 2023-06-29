<?php
// 220038--
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
 * @author     Pitang Agile TI <contato@pitang.com>
 * @copyright  2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license    http://www.php.net/license/3_01.txt PHP License 3.01
 */

/**
 * Repositorio Ata Registro Preco Interna.
 */
class Negocio_Repositorio_AtaRegistroPrecoInterna implements Negocio_Repositorio_Interface
{

    /**
     * Nome da tabela no Schema.
     *
     * @var string
     */
    const NOME_TABELA = 'sfpc.tbataregistroprecointerna';

    /**
     * Executar comandos SQL (string).
     *
     * @param string $sql
     *            [description]
     *
     * @return [type] [description]
     */
    private function executarSQL($sql)
    {
        $res = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($res);

        return $res;
    }

    /**
     *
     * @param Negocio_ValorObjeto_Carpnosequ $carpnosequ
     */
    public function procurar(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        $sql = sprintf('SELECT * FROM ' . self::NOME_TABELA . ' WHERE carpnosequ = %d', $carpnosequ->getValor());
        $res = $this->executarSQL($sql);

        return current($res);
    }

    /**
     *
     * @param Negocio_ValorObjeto_Carpnosequ $carpnoseq1
     */
    public function procurarOrigem(Negocio_ValorObjeto_Carpnosequ $carpnoseq1)
    {
        $sql = sprintf('SELECT * FROM ' . self::NOME_TABELA . ' WHERE carpnoseq1 = %d', $carpnoseq1->getValor());
        $res = $this->executarSQL($sql);

        return current($res);
    }

    /**
     */
    public function listarTodos()
    {
        $sql = sprintf('SELECT * FROM %s ', self::NOME_TABELA);

        return $this->executarSQL($sql);
    }

    /**
     *
     * @param Negocio_ValorObjeto_Clicpoproc $clicpoproc
     * @param Negocio_ValorObjeto_Alicpoanop $alicpoanop
     * @param Negocio_ValorObjeto_Cgrempcodi $cgrempcodi
     * @param Negocio_ValorObjeto_Ccomlicodi $ccomlicodi
     * @param Negocio_ValorObjeto_Corglicodi $corglicodi
     */
    public function procurarPorProcessoLicitatorio(Negocio_ValorObjeto_Clicpoproc $clicpoproc, Negocio_ValorObjeto_Alicpoanop $alicpoanop, Negocio_ValorObjeto_Cgrempcodi $cgrempcodi, Negocio_ValorObjeto_Ccomlicodi $ccomlicodi, Negocio_ValorObjeto_Corglicodi $corglicodi)
    {
        $sql = sprintf('SELECT carpnosequ FROM ' . self::NOME_TABELA . ' WHERE clicpoproc = %d AND alicpoanop = %d AND cgrempcodi = %d AND ccomlicodi = %d AND corglicodi = %d ', $clicpoproc->getValor(), $alicpoanop->getValor(), $cgrempcodi->getValor(), $ccomlicodi->getValor(), $corglicodi->getValor());
        $res = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($res);

        return $res;
    }

    public function procurarPorProcessoLicitatorioFornecedor(Negocio_ValorObjeto_Clicpoproc $clicpoproc, Negocio_ValorObjeto_Alicpoanop $alicpoanop, Negocio_ValorObjeto_Cgrempcodi $cgrempcodi, Negocio_ValorObjeto_Ccomlicodi $ccomlicodi, Negocio_ValorObjeto_Corglicodi $corglicodi, $aforcrsequ)
    {
        $sql = sprintf('SELECT carpnosequ FROM ' . self::NOME_TABELA . ' WHERE clicpoproc = %d AND alicpoanop = %d AND cgrempcodi = %d AND ccomlicodi = %d AND corglicodi = %d AND aforcrsequ = %d ', $clicpoproc->getValor(), $alicpoanop->getValor(), $cgrempcodi->getValor(), $ccomlicodi->getValor(), $corglicodi->getValor(), $aforcrsequ);
        $res = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($res);

        return $res;
    }

    /**
     *
     * @param unknown $valorAnoNumeracao
     * @param unknown $valorOrgao
     * @return StdClass[]
     */
    public function procurarPorOrgaoAno($valorAnoNumeracao, $valorOrgao)
    {
        $sql = sprintf('SELECT * FROM %s ', self::NOME_TABELA);
        $sql = sprintf($sql . ' WHERE aarpinanon = %d AND corglicodi = %d ', $valorAnoNumeracao, $valorOrgao);
        $res = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($res);

        return $res;
    }
}
