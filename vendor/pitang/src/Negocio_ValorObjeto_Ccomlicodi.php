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
final class Negocio_ValorObjeto_Ccomlicodi implements Negocio_ValorObjeto_Interface
{

    /**
     *
     * @var integer
     */
    private $ccomlicodi;

    /**
     *
     * @param integer $cgrempcodi            
     * @throws InvalidArgumentException
     */
    public function __construct($ccomlicodi = null)
    {
        if (! filter_var($ccomlicodi, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException("ccomlicodi deve ser um inteiro");
        }
        
        $this->ccomlicodi = (int) filter_var($ccomlicodi, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see Negocio_ValorObjeto_Interface::getValor()
     */
    public function getValor()
    {
        return $this->ccomlicodi;
    }

    /**
     */
    public function __toString()
    {
        return $this->getValor();
    }
}
