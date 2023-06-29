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
 * @version   GIT: EMPREL-SAD-PORTAL-COMPRAS-REGISTRO-PRECO-BL-FUNC-20160609-0940
 */

/**
 * UI_Interface.
 */
interface UI_Interface
{

    /**
     *
     * @return Template
     */
    public function getTemplate();

    /**
     *
     * @param Template $template
     */
    public function setTemplate(Template $template);

    /**
     *
     * @param Adaptacao_Interface $adaptacao
     */
    public function setAdaptacao(Adaptacao_Interface $adaptacao);

    /**
     *
     * @return Adaptacao_Interface
     */
    public function getAdaptacao();

    /**
     *
     * @param unknown $mensagem
     */
    public function blockErro($mensagem);

    /**
     */
    public function limparMensagemSistema();

    /**
     *
     * @param unknown $mensagem
     * @param unknown $tipo
     * @param number $troca
     */
    public function mensagemSistema($mensagem, $tipo, $troca = 0);

    /**
     *
     * @param unknown $mensagem
     * @param unknown $tipo
     * @param unknown $troca
     */
    public function setMensagemFeedBack($mensagem, $tipo = 0, $troca = 0);
}
