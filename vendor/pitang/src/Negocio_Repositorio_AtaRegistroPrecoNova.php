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
 * @category   PortalDGCO
 *
 * @author     Pitang Agile TI <contato@pitang.com>
 * @copyright  2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license    http://www.php.net/license/3_01.txt PHP License 3.01
 */
class Negocio_Repositorio_AtaRegistroPrecoNova extends Negocio_Repositorio_Abstrato
{

    /**
     * Nome da tabela no Schema.
     *
     * @var string
     */
    const NOME_TABELA = 'sfpc.tbataregistropreconova';

    /**
     *
     * @param stdClass $entidade
     */
    public function inserir($entidade)
    {
        $resultado = $this->getConexao()->autoExecute(Negocio_Repositorio_AtaRegistroPrecoNova::NOME_TABELA, (array) $entidade, DB_AUTOQUERY_INSERT);

        if (PEAR::isError($resultado)) {
            $this->getConexao()->rollback();
            return $resultado->getMessage();
        }

        return $entidade;
    }

    /**
     *
     * @param int $processo
     * @param int $orgao
     * @param int $ano
     * @param int $numeroAta
     */
    public function consultarAtaPorChave($processo, $orgao, $ano, $numeroAta)
    {
        $sql = Dados_Sql_AtaRegistroPrecoNova::sqlAtaPorchave($processo, $orgao, $ano, $numeroAta);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    /**
     *
     * @param Negocio_ValorObjeto_Cintrpsequ $cintrpsequ
     * @param Negocio_ValorObjeto_Cintrpsano $cintrpsano
     * @param Negocio_ValorObjeto_Corglicodi $corglicodi
     */
    public function consultarLicitacaoAtaInterna(Negocio_ValorObjeto_Cintrpsequ $cintrpsequ, Negocio_ValorObjeto_Cintrpsano $cintrpsano, Negocio_ValorObjeto_Corglicodi $corglicodi)
    {
        $sql = Dados_Sql_AtaRegistroPrecoNova::sqlLicitacaoAtaInterna($cintrpsano->getValor(), $cintrpsequ->getValor(), $corglicodi->getValor());
        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    /**
     *
     * @param array $param
     * @return string
     */
    private function sqlConsultarAtaInterna($param)
    {
        $sql = "SELECT arpn.*, arp.*, ";
        $sql .= "ol.*, ";
        $sql .= " forn.nforcrrazs, forn.aforcrccgc, forn.aforcrccpf, forn.eforcrlogr, ";
        $sql .= " forn.aforcrnume, forn.eforcrbair, forn.nforcrcida, forn.cforcresta ";
        $sql .= " ,lic.clicpocodl, lic.alicpoanol";
        $sql .= " FROM sfpc.tbataregistropreconova arpn";
        $sql .= " JOIN sfpc.tbataregistroprecointerna arp";
        $sql .= " ON arp.carpnosequ = arpn.carpnosequ";
        $sql .= " JOIN sfpc.tborgaolicitante ol";
        $sql .= " ON ol.corglicodi = arp.corglicodi";

        $sql .= " JOIN sfpc.tblicitacaoportal lic";
        $sql .= " ON lic.clicpoproc = arp.clicpoproc";
        $sql .= " AND lic.alicpoanop = arp.alicpoanop";
        $sql .= " AND lic.cgrempcodi = arp.cgrempcodi";
        $sql .= " AND lic.ccomlicodi = arp.ccomlicodi";
        $sql .= " AND lic.corglicodi = arp.corglicodi";
        $sql .= " JOIN sfpc.tbfornecedorcredenciado forn";
        $sql .= " ON forn.aforcrsequ = arp.aforcrsequ";

        $sql .= " WHERE 1 =1";
        $sql .= " AND arpn.carpnosequ =" . intval($param['ata']);
        $sql .= " AND  arp.clicpoproc =" . intval($param['processo']);
        $sql .= " AND  arp.corglicodi =" . intval($param['orgao']);
        $sql .= " AND  arp.alicpoanop =" . intval($param['ano']);

        return $sql;
    }

    /**
     *
     * @param array $param
     * @return string
     */
    private function sqlConsultarAtaExterna($param)
    {
        $sql = "SELECT arpn.*, arp.*, ";
        $sql .= " forn.nforcrrazs, forn.aforcrccgc, forn.aforcrccpf, forn.eforcrlogr, ";
        $sql .= " forn.aforcrnume, forn.eforcrbair, forn.nforcrcida, forn.cforcresta ";
        $sql .= " FROM sfpc.tbataregistropreconova arpn";
        $sql .= " JOIN sfpc.tbataregistroprecoexterna arp";
        $sql .= " ON arp.carpnosequ = arpn.carpnosequ";
        $sql .= " JOIN sfpc.tbfornecedorcredenciado forn";
        $sql .= " ON forn.aforcrsequ = arp.aforcrsequ";

        $sql .= " WHERE 1 =1";
        $sql .= " AND arpn.carpnosequ =" . intval($param['ata']);
        $sql .= " AND arp.earpexproc LIKE  '" . $param['processo'] . "' ";

        return $sql;
    }

    /**
     *
     * @param unknown $param
     * @return NULL
     */
    public function consultarAta($param)
    {
        if ($param['tipo'] == 'I') {
            $sql = $this->sqlConsultarAtaInterna($param);
        } else
            if ($param['tipo'] == 'E') {
                $sql = $this->sqlConsultarAtaExterna($param);
            }

        $res = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($res);

        return $res;
    }

    /**
     *
     * @param integer $carpnosequ
     */
    public function procurar(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        $sql = sprintf("SELECT * FROM " . self::NOME_TABELA . " WHERE carpnosequ = %d", $carpnosequ->getValor());

        $res = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($res);

        return current($res);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see Negocio_Repositorio_Interface::listarTodos()
     */
    public function listarTodos()
    {
        // TODO: Auto-generated method stub
    }
}
