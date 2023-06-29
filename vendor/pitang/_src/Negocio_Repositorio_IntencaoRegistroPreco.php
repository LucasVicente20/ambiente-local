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
 *
 * @version    GIT: v1.20.0-25-g2cf2ab8
 */

/**
 */
class Negocio_Repositorio_IntencaoRegistroPreco extends Negocio_Repositorio_Abstrato
{

    /**
     * [$tabela description]
     *
     * @var string
     */
    private $tabela = 'sfpc.tbintencaoregistropreco';

    /**
     * [consultarUltimoItemDaIntencao description]
     *
     * @param [type] $cintrpsano
     *            [description]
     * @return [type] [description]
     */
    public function consultarUltimoItemDaIntencao($cintrpsano)
    {
        $res = $this->getConexao()->getOne(Dados_Sql_IntencaoRegistroPreco::ultimoCodigoSequencialIntencaoRP($cintrpsano));
        
        if (PEAR::isError($res)) {
            die($res->getMessage());
        }
        
        return intval($res);
    }

    /**
     * [inserir description]
     *
     * @param [type] $entidade
     *            [description]
     * @return [type] [description]
     */
    public function inserir($entidade)
    {
        $intencao = Negocio_Entidade::singleton('sfpc.tbintencaoregistropreco');
        
        foreach ((array) $intencao as $key => $value) {
            if (isset($entidade->$key)) {
                $intencao->$key = $entidade->$key;
            }
        }
        
        $dataBr = explode('/', $intencao->tintrpdlim);
        $data = $dataBr[2] . '-' . $dataBr[1] . '-' . $dataBr[0];
        $dataLimite = new DateTime($data);
        $intencao->cintrpsano = (int) $dataLimite->format('Y');
        $intencao->cintrpsequ = (int) $this->consultarUltimoItemDaIntencao($intencao->cintrpsano) + 1;
        $intencao->tintrpdlim = (string) $dataLimite->format('Y-m-d H:i:s');
        $intencao->fintrpsitu = 'I';
        $intencao->tintrpdcad = (string) date('Y-m-d H:i:s');
        $intencao->tintrpulat = (string) date('Y-m-d H:i:s');
        $intencao->tintrpdlim = $data;
        
        $resultado = $this->getConexao()->autoExecute($this->tabela, (array) $intencao, DB_AUTOQUERY_INSERT);
        
        if (PEAR::isError($resultado)) {
            $this->getConexao()->rollback();
            return $resultado->getMessage();
        }
        
        return $intencao;
    }

    /**
     *
     * @param
     *            Negocio_ValorObjeto_IntencaoRegistroPreco
     *            
     * @return [type]
     */
    public function procurar(Negocio_ValorObjeto_IntencaoRegistroPreco $intencao)
    {
        $sql = sprintf('SELECT * FROM ' . $this->tabela . ' WHERE cintrpsequ = %d AND cintrpsano = %d', $intencao->getCintrpsequ(), $intencao->getCintrpsano());
        
        $this->getConexao()->setFetchMode(DB_FETCHMODE_ASSOC);
        
        return $this->getConexao()->getAll($sql);
    }

    /**
     * [listarTodos description]
     *
     * @return [type] [description]
     */
    public function listarTodos()
    {
        return true;
    }

    /**
     * [getIntencaoByDataInicioAndDataFimAndGrupoUsuario description]
     *
     * @param integer $sequencialIntencao
     *            [description]
     * @param integer $anoIntencao
     *            [description]
     * @param integer $dataInicioCadastro
     *            [description]
     * @param string $dataFimCadastro
     *            [description]
     * @param string $centroCusto
     *            [description]
     * @return array [description]
     */
    public function getIntencaoByDataInicioAndDataFimAndGrupoUsuario($sequencialIntencao, $anoIntencao, $dataInicioCadastro, $dataFimCadastro, $centroCusto = null, $situacao = null)
    {
        $sql = Dados_Sql_IntencaoRegistroPreco::sqlSelectIntencaoByDataInicioAndDataFimAndGrupoUsuario($sequencialIntencao, $anoIntencao, $dataInicioCadastro, $dataFimCadastro, $centroCusto, $situacao);
        
        return ClaDatabasePostgresql::executarSQL($sql);
    }

    /**
     *
     * @param unknown $dataInicioCadastro            
     * @param unknown $dataFimCadastro            
     * @param Negocio_ValorObjeto_Cintrpsequ $cintrpsequ            
     * @param Negocio_ValorObjeto_Cintrpsano $cintrpsano            
     * @param unknown $centroCusto            
     * @return NULL
     */
    public function listarTodasIRPRespondidas($dataInicioCadastro, $dataFimCadastro, Negocio_ValorObjeto_Cintrpsequ $cintrpsequ = null, Negocio_ValorObjeto_Cintrpsano $cintrpsano = null, $centroCusto = null)
    {
        $sqlObjeto = new Dados_Sql_IntencaoRegistroPreco();
        $sql = $sqlObjeto->sqllistarTodasIRPRespondidas($dataInicioCadastro, $dataFimCadastro, $cintrpsequ, $cintrpsano, $centroCusto);
        
        $res = ClaDatabasePostgresql::executarSQL($sql);
        
        ClaDatabasePostgresql::hasError($res);
        
        return $res;
    }
}
