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
class Negocio_Entidade_IntencaoRegistroPreco extends Negocio_Entidade_Abstrata
{

    const NOME_TABELA = 'sfpc.tbintencaoregistropreco';

    public function getPK()
    {
        return array(
            'cintrpsequ' => new Negocio_ValorObjeto_Cintrpsequ(),
            'cintrpsano' => new Negocio_ValorObjeto_Cintrpsano()
        );
    }
}
