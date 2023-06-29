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
 * @category  Pitang Registro Preço
 * @package   registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT: EMPREL-SAD-PORTAL-COMPRAS-REGISTRO-PRECO-BL-FUNC-20160609-1045
 */
interface Programa_Interface
{

    /**
     *
     * @return UI_Interface UI_Interface
     */
    public function getUI();

    /**
     *
     * @param UI_Interface $userInterface
     */
    public function setUI(UI_Interface $userInterface);

    /**
     *
     * @param ProgramaAbstrato $programa
     */
    public static function iniciar(ProgramaAbstrato $programa);
}