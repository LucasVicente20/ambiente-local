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
 * @version   GIT: v1.18.0-17-g9920068
 *
 * Adaptacao_Abstrata
 */

 // 220038--
 
class Adaptacao_Abstrata implements Adaptacao_Interface
{
    /**
     *
     * @var Negocio_Interface
     *
     */
    private $negocio;

    /**
     *
     * @return the Negocio_Interface
     */
    public function getNegocio()
    {
        return $this->negocio;
    }

    /**
     *
     * @param Negocio_Interface $negocio            
     */
    public function setNegocio(Negocio_Interface $negocio)
    {
        $this->negocio = $negocio;
        return $this;
    }

    /**
     * [consultarParametrosGerais description]
     * 
     * @return [type] [description]
     * @deprecated [<version>] [<description>]
     */
    public function consultarParametrosGerais()
    {
        return $this->getNegocio()->consultarParametrosGerais();
    }
}
