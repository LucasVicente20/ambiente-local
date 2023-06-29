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
 * @category  Pitang_Registro_Preco
 * @package   registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @version   GIT: v1.18.0-17-g9920068
 */

/**
 */
class Negocio_ValorObjeto_IntencaoRegistroPreco
{
    /**
     * Código sequencial da intenção de registro de preço por ano.
     *
     * @var int
     */
    private $cintrpsequ;

    /**
     * Ano da intenção de registro de preço.
     *
     * @var int
     */
    private $cintrpsano;

    /**
     * Sets the Código sequencial da intenção de registro de preço por ano.
     *
     * @param int $cintrpsequ
     *            the cintrpsequ
     *            
     * @return self
     */
    private function _setCintrpsequ($cintrpsequ)
    {
        $this->cintrpsequ = (int) $cintrpsequ;
        
        return $this;
    }

    /**
     * Sets the Ano da intenção de registro de preço.
     *
     * @param int $cintrpsano
     *            the cintrpsano
     *            
     * @return self
     */
    private function _setCintrpsano($cintrpsano)
    {
        $this->cintrpsano = (int) $cintrpsano;
        
        return $this;
    }

    /**
     *
     * @param
     *            int
     * @param
     *            int
     */
    public function __construct($cintrpsequ, $cintrpsano)
    {
        $this->_setCintrpsequ($cintrpsequ);
        $this->_setCintrpsano($cintrpsano);
    }

    /**
     * Gets the Código sequencial da intenção de registro de preço por ano.
     *
     * @return int
     */
    public function getCintrpsequ()
    {
        return (int) $this->cintrpsequ;
    }

    /**
     * Gets the Ano da intenção de registro de preço.
     *
     * @return int
     */
    public function getCintrpsano()
    {
        return (int) $this->cintrpsano;
    }

    /**
     * Retorna o número da intenção de Registro de Preço no formato string
     */
    public function getNumeroIntencao()
    {
        return str_pad($this->getCintrpsequ(), 4, '0', STR_PAD_LEFT) . '/' . $this->getCintrpsano();
    }
}
