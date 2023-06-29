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
 * @author     Pitang Agile TI <contato@pitang.com>
 * @copyright  2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license    http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version    1.0.0
 */
final class Negocio_ValorObjeto_Cintrpsequ implements Negocio_ValorObjeto_Interface
{

    /**
     * Código sequencial da intenção de registro de preço por ano
     *
     * @var integer
     */
    private $cintrpsequ;

    public function __construct($cintrpsequ = null)
    {
        if (! filter_var($cintrpsequ, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException("cintrpsequ deve ser um inteiro");
        }
        
        $this->cintrpsequ = (int) filter_var($cintrpsequ, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Get Código sequencial da intenção de registro de preço por ano
     */
    public function getValor()
    {
        return $this->cintrpsequ;
    }
}
