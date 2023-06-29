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
class Negocio_Repositorio_ParticipanteAtaRP
{

    /**
     * Nome da tabela no Schema.
     *
     * @var string
     */
    const NOME_TABELA = 'sfpc.tbparticipanteatarp';

    /**
     *
     * @param Negocio_ValorObjeto_Cintrpsequ $cintrpsequ            
     * @param Negocio_ValorObjeto_Cintrpsano $cintrpsano            
     */
    public function consultarParticipantesByProcesso(Negocio_ValorObjeto_Cintrpsequ $cintrpsequ, Negocio_ValorObjeto_Cintrpsano $cintrpsano)
    {
        $sql = Dados_Sql_ParticipanteAtaRegistroPreco::selecionaParticipantesPeloProcesso($cintrpsequ, $cintrpsano);
        $resultado = ClaDatabasePostgresql::executarSQL($sql);
        
        ClaDatabasePostgresql::hasError($resultado);
        
        return $resultado;
    }

    /**
     *
     * @param Negocio_ValorObjeto_Carpnosequ $carpnosequ            
     * @return array
     */
    public function procurarPorCarpnosequ(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        $sql = sprintf("
            SELECT *
              FROM " . self::NOME_TABELA . "
             WHERE carpnosequ = %d
            ", $carpnosequ->getValor());
        
        $res = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($res);
        return $res;
    }
}
