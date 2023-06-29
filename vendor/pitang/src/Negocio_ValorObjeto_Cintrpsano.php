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
 * @version    1.0.0
 */
final class Negocio_ValorObjeto_Cintrpsano implements Negocio_ValorObjeto_Interface
{
    /**
     * 
     *
     * @var integer
     */
    private $cintrpsano;

    /**
     * Construtor da classe
     * 
     * @param integer $cintrpsano [description]
     * 
     * @throws InvalidArgumentException [<description>]
     */
    public function __construct($cintrpsano = null)
    {
        if (! filter_var($cintrpsano, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException("cintrpsano deve ser um inteiro");
        }
        
        $this->cintrpsano = filter_var($cintrpsano, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Get 
     */
    public function getValor()
    {
        return $this->cintrpsano;
    }
}
