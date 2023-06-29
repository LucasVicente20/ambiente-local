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
 * @category  Pitang_Registro_Preco
 * @package   registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @version   GIT: v1.18.0-17-g9920068
 */

/**
 * Classe Abstrata para criação de um novo programa para o portal de compras
 */
abstract class ProgramaAbstrato implements Programa_Interface
{

    /**
     *
     * @var UI_Interface
     */
    private $userInterface;

    /**
     *
     * @return UI_Interface UI_Interface
     */
    public function getUI()
    {
        return $this->userInterface;
    }

    /**
     *
     * @param UI_Interface $userInterface
     */
    public function setUI(UI_Interface $userInterface)
    {
        $this->userInterface = $userInterface;
        return $this;
    }

    /**
     * [executar description]
     *
     * @return [type] [description]
     */
    protected function executar()
    {
        $this->configuracao();
        $this->frontController();
        $this->getUI()
            ->getTemplate()
            ->show();
    }

    /**
     *
     * @param ProgramaAbstrato $programa
     */
    public static function iniciar(ProgramaAbstrato $programa)
    {
        $programa->executar();
    }

    /**
     */
    abstract protected function frontController();

    /**
     */
    abstract protected function configuracao();
}
