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
 * @category  Pitang Registro Preço
 *
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT: EMPREL-SAD-PORTAL-COMPRAS-REL-COD-013000-1-g08214d3
 */

/**
 */
class Negocio_ValorObjeto_ItemIntencaoRegistroPreco
{
    /**
     * Valor Objeto da Intenção de Registro de Preço
     *
     * @var Negocio_ValorObjeto_IntencaoRegistroPreco
     */
    private $valorIntencaoRegistroPreco;

    /**
     * Código sequencial dos itens da intenção de registro de preço
     *
     * @var int
     */
    private $citirpsequ;

    /**
     * [__construct description]
     *
     * @param Negocio_ValorObjeto_IntencaoRegistroPreco $valorIntencao            
     * @param integer $citirpsequ
     *            Código sequencial dos itens da intenção de registro de preço
     */
    public function __construct(Negocio_ValorObjeto_IntencaoRegistroPreco $valorIntencaoRegistroPreco, $citirpsequ)
    {
        $this->_setValorIntencaoRegistroPreco($valorIntencaoRegistroPreco);
        $this->_setCitirpsequ($citirpsequ);
    }

    /**
     * Gets the Valor Objeto da Intenção de Registro de Preço.
     *
     * @return Negocio_ValorObjeto_IntencaoRegistroPreco
     */
    public function getValorIntencaoRegistroPreco()
    {
        return $this->valorIntencaoRegistroPreco;
    }

    /**
     * Sets the Valor Objeto da Intenção de Registro de Preço.
     *
     * @param Negocio_ValorObjeto_IntencaoRegistroPreco $valorIntencaoRegistroPreco
     *            the valor intencao registro preco
     *            
     * @return self
     */
    private function _setValorIntencaoRegistroPreco(Negocio_ValorObjeto_IntencaoRegistroPreco $valorIntencaoRegistroPreco)
    {
        $this->valorIntencaoRegistroPreco = $valorIntencaoRegistroPreco;
        
        return $this;
    }

    /**
     * Gets the Código sequencial dos itens da intenção de registro de preço
     *
     * @return int
     */
    public function getCitirpsequ()
    {
        return $this->citirpsequ;
    }

    /**
     * Sets the Código sequencial dos itens da intenção de registro de preço
     *
     * @param integer $citirpsequ
     *            Código sequencial dos itens da intenção de registro de preço
     *            
     * @return self
     */
    private function _setCitirpsequ($citirpsequ)
    {
        $this->citirpsequ = $citirpsequ;
        
        return $this;
    }
}
