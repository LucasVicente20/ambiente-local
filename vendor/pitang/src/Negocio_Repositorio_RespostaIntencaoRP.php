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
 *
 * @version    GIT: v1.30.1
 */

/**
 */
class Negocio_Repositorio_RespostaIntencaoRP extends Negocio_Repositorio_Abstrato
{

    /**
     * Nome da Tabela no Schema
     *
     * @var string
     */
    const NOME_TABELA = 'sfpc.tbrespostaintencaorp';

    /**
     * Seleciona a situação da resposta da intenção de registro de preço
     * do respectivo orgão
     *
     * @param integer $sequencialIntencao            
     * @param integer $anoIntencao            
     * @param integer $grupoUsuario            
     *
     * @return mixed
     */
    public function selecionaSituacaoIntencaoRPOrgao($sequencialIntencao, $anoIntencao, $grupoUsuario)
    {
        $sql = "
            SELECT rirp.cintrpsequ, rirp.cintrpsano, rirp.corglicodi, rirp.frinrpsitu
            FROM  " . self::NOME_TABELA . " rirp
            INNER JOIN " . Negocio_Repositorio_IntencaoRPOrgao::NOME_TABELA . " irpo
                ON rirp.cintrpsequ = irpo.cintrpsequ AND rirp.cintrpsano = irpo.cintrpsano
                AND rirp.corglicodi = irpo.corglicodi 
            INNER JOIN " . Negocio_Repositorio_OrgaoLicitante::NOME_TABELA . " ol
                ON irpo.corglicodi = ol.corglicodi            
            WHERE rirp.cintrpsequ = ?
                AND rirp.corglicodi = ?
                AND rirp.cintrpsano = ?                
        ";

        $res = $this->getConexao()->getOne($sql, array(
            $sequencialIntencao,
            $grupoUsuario,
            $anoIntencao
        ));
        
        $temErro = ClaDatabasePostgresql::hasError($res);
        
        if ($temErro != false) {
            return $temErro;
        }
        
        return $res;
    }

    /**
     * Seleciona
     *
     * @param Negocio_ValorObjeto_IntencaoRPOrgao $voIRPOrgao            
     * @return mixed
     */
    public function selecionarRespostaIRP(Negocio_ValorObjeto_IntencaoRPOrgao $voIRPOrgao)
    {
        $sql = "
            SELECT
                rirp.cintrpsequ,
                rirp.cintrpsano,
                rirp.corglicodi,
                rirp.frinrpsitu
            FROM
                " . self::NOME_TABELA . " rirp
            WHERE
                rirp.cintrpsequ = ?
                AND rirp.cintrpsano = ?
                AND rirp.corglicodi = ?
        ";
        
        $res = $this->getConexao()->getAll($sql, array(
            $voIRPOrgao->getValorIntencaoRegistroPreco()
                ->getCintrpsequ(),
            $voIRPOrgao->getValorIntencaoRegistroPreco()
                ->getCintrpsano(),
            $voIRPOrgao->getCorglicodi()
        ));
        
        $temErro = ClaDatabasePostgresql::hasError($res);
        
        if ($temErro != false) {
            return $temErro;
        }
        
        return $res;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see Negocio_Repositorio_Interface::listarTodos()
     */
    public function listarTodos()
    {
        $sql = "
            SELECT * FROM " . self::NOME_TABELA . "
        ";
        
        $res = ClaDatabasePostgresql::executarSQL($sql);
        
        ClaDatabasePostgresql::hasError($res);
        
        return $res;
    }
}
