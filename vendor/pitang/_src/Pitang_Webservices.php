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
 * @version    GIT: v1.39.0
 */

/**
 * Pitang Webservice REST
 */
abstract class Pitang_Webservices
{

    /**
     *
     * @var string
     */
    const TOKEN = '4f82294dd18d0273b1efebcdf75c1343';

    /**
     * Limit da consulta SQL
     *
     * @var integer
     */
    private $limit;

    /**
     * Offset da consulta SQL
     *
     * @var integer
     */
    private $offset;

    /**
     * Setar o offset da consulta SQL
     *
     * @param integer $limit            
     */
    public function setLimit($limit)
    {
        $limitValidado = filter_var($limit, FILTER_SANITIZE_NUMBER_INT);
        if (! filter_var($limitValidado, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException("O valor de limit informado não é um valor de inteiro", 1);
        }
        
        $this->limit = $limitValidado;
        
        return $this;
    }

    /**
     * Pegar o valor de offset da consulta SQL
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     *
     * @param integer $offset            
     */
    public function setOffSet($offset)
    {
        $offsetValidado = filter_var($offset, FILTER_SANITIZE_NUMBER_INT);
        if (! filter_var($offsetValidado, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException("O valor de offset informado não é um valor de inteiro", 1);
        }
        
        $this->offset = $offsetValidado;
        
        return $this;
    }

    /**
     */
    public function getOffSet()
    {
        return $this->offset;
    }

    /**
     *
     * @param
     *            [type]
     */
    public function __construct()
    {
        $this->limit = 25;
        $this->offset = 0;
    }

    /**
     */
    public function run()
    {
        $this->frontController();
    }

    /**
     */
    abstract public function frontController();
}
