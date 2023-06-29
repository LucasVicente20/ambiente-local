<?php

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
 * @category  Pitang Registro Preço
 * @package   registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @version   Git: $Id:$
 */

 // 220038--
 
class DadosCarona
{
    public function __construct()
    {
    }
}

class NegocioCarona extends BaseNegocio
{
    private $dados;

    public function __construct()
    {
        $this->dados = new DadosCarona();
    }
}

class View extends BaseIntefaceGraficaUsuario
{
    private $negocio;

    public function __construct()
    {
        $this->negocio = new NegocioCarona();
    }

    public function getNegocio()
    {
        return $this->negocio;
    }

    public function setNegocio(Negocio $negocio)
    {
        $this->negocio = $negocio;
    }

    public function selecionarValoresUsados($ata, $item, $material, $processo, $ano, $orgao)
    {
        return $this->negocio->selecionarValoresUsados($ata, $item, $material, $processo, $ano, $orgao);
    }
}
