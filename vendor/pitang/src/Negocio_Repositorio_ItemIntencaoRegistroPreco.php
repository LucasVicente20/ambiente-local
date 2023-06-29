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
 *
 * @version    GIT: v1.20.0-25-g2cf2ab8
 */
#---------------------------------------------------------------------
# Alterado: Pitang Agile TI - Caio Coutinho
# data: 01/04/2019
# Objetivo: Tarefa Redmine 214041
#---------------------------------------------------------------------

class Negocio_Repositorio_ItemIntencaoRegistroPreco extends Negocio_Repositorio_Abstrato
{

    /**
     * [$tabela description].
     *
     * @var string
     */
    const NOME_TABELA = 'sfpc.tbitemintencaoregistropreco';

    /**
     * Inserir um novo item na Intencao de Registro de Preço.
     *
     * @param stdClass $entidade
     *            Entidade para armazenamento
     *
     * @return bool
     *
     * @throws Exception [<description>]
     */
    public function inserir($entidade)
    {
        foreach ($entidade as $item) {
            $resultado = $this->getConexao()->autoExecute(self::NOME_TABELA, (array) $item, DB_AUTOQUERY_INSERT);
            $temErro = ClaDatabasePostgresql::hasError($resultado);

            if ($temErro != false) {
                $this->getConexao()->rollback();
                return $temErro;
            }
        }

        return true;
    }

    /**
     *
     * @param Negocio_ValorObjeto_IntencaoRPOrgao $voIRPOrgao
     */
    public function selecionaTodosItensIntencaoRPOrgao(Negocio_ValorObjeto_IntencaoRPOrgao $voIRPOrgao)
    {
        $sql = "
            SELECT
                irirp.cintrpsequ,
                irirp.cintrpsano,
                irirp.corglicodi,
                irirp.citirpsequ,
                iirp.aitirporde,
                iirp.cmatepsequ,
                mp.ematepdesc,
                iirp.eitirpdescmat,
                iirp.cservpsequ,
                sp.eservpdesc,
                iirp.eitirpdescse,
                irirp.airirpqtpr,
                iirp.vitirpvues
            FROM
                sfpc.tbitemintencaoregistropreco iirp 
            FULL OUTER JOIN sfpc.tbitemrespostaintencaorp irirp ON 
                iirp.citirpsequ = irirp.citirpsequ
                AND iirp.cintrpsequ = irirp.cintrpsequ
                AND iirp.cintrpsano = irirp.cintrpsano
            LEFT JOIN sfpc.tbmaterialportal mp ON mp.cmatepsequ = iirp.cmatepsequ
            LEFT JOIN sfpc.tbservicoportal sp ON sp.cservpsequ = iirp.cservpsequ
            WHERE iirp.cintrpsequ = ?
                AND iirp.cintrpsano = ?
                AND (irirp.corglicodi = ? OR irirp.cintrpsequ IS NULL)
            ORDER BY iirp.aitirporde ASC
        ";

        $res = $this->getConexao()->getAll($sql, array(
            $voIRPOrgao->getValorIntencaoRegistroPreco()->getCintrpsequ(),
            $voIRPOrgao->getValorIntencaoRegistroPreco()->getCintrpsano(),
            $voIRPOrgao->getCorglicodi()
        ), DB_FETCHMODE_OBJECT);

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
