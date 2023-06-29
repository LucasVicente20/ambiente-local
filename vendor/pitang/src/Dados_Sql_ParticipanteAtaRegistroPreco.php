<?php
// 220038--
/**
 * Portal da DGCO
 *
 * PHP version 5.2.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Pitang_Registro_Preco
 * @package   registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @version   GIT: v1.18.0-17-g9920068
 */

/**
 */
class Dados_Sql_ParticipanteAtaRegistroPreco
{

    /**
     * Seleciona os participantes da Ata de Registro Preço utilizando o
     * código da Ata (carpnosequ)
     *
     * @param integer $codigoAta            
     *
     * @return string Comando SQL que seleciona todos os participantes da Ata de registro de Preço
     */
    public static function selecionaParticipantePeloCodigoAta($codigoAta)
    {
        assercao(is_null($codigoAta), '$codigoAta requirido');
        assercao(! is_integer($codigoAta), '$codigoAta deve ser inteiro');
        
        $sql = "
            SELECT
                par.carpnosequ,
                par.corglicodi,
                par.fpatrpexcl,
                par.cusupocodi,
                par.tpatrpulat,
                org.eorglidesc
            FROM
                sfpc.tbparticipanteatarp par
            INNER JOIN
                sfpc.tborgaolicitante org
                ON org.corglicodi = par.corglicodi
            WHERE
            par.carpnosequ = %d
        ";
        
        return sprintf($sql, $codigoAta);
    }

    /**
     * Seleciona os participantes da Ata de Registro Preço utilizando o
     * código da Ata (carpnosequ)
     *
     * @param integer $codigoAta            
     *
     * @return string Comando SQL que seleciona todos os participantes da Ata de registro de Preço
     */
    public function selecionaParticipantePeloCodigoAta(Negocio_ValorObjeto_Carpnosequ $carpnosequ)
    {
        assercao(is_null($carpnosequ->getValor()), '$carpnosequ requirido');
        assercao(! is_integer($carpnosequ->getValor()), '$carpnosequ deve ser inteiro');
        
        $sql = "
            SELECT
                par.carpnosequ,
                par.corglicodi,
                par.fpatrpexcl,
                par.cusupocodi,
                par.tpatrpulat,
                org.eorglidesc
            FROM
                sfpc.tbparticipanteatarp par
            INNER JOIN
                sfpc.tborgaolicitante org
                ON org.corglicodi = par.corglicodi
            WHERE
            par.carpnosequ = %d
        ";
        
        return sprintf($sql, $carpnosequ->getValor());
    }

    public function selecionaParticipantesPeloProcesso(Negocio_ValorObjeto_Cintrpsequ $cintrpsequ, Negocio_ValorObjeto_Cintrpsano $cintrpsano)
    {
        $sql = "

        ";
        
        return sprintf($sql, $cintrpsequ->getValor(), $cintrpsano->getValor());
    }
}
