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
class Negocio_ValorObjeto_LicitacaoPortal
{
    /**
     * Código do Processo Licitatório
     *
     * @var integer
     */
    private $clicpoproc;

    /**
     * Ano do Processo Licitatório
     *
     * @var integer
     */
    private $alicpoanop;

    /**
     * Código do Grupo
     *
     * @var integer
     */
    private $cgrempcodi;

    /**
     * Código da Comissão
     *
     * @var integer
     */
    private $ccomlicodi;

    /**
     * Código do Órgão Licitante
     *
     * @var integer
     */
    private $corglicodi;

    /**
     *
     * @return the integer
     */
    public function getClicpoproc()
    {
        return $this->clicpoproc;
    }

    /**
     *
     * @param
     *            $clicpoproc
     */
    public function setClicpoproc($clicpoproc)
    {
        $this->clicpoproc = $clicpoproc;
        return $this;
    }

    /**
     *
     * @return the integer
     */
    public function getAlicpoanop()
    {
        return $this->alicpoanop;
    }

    /**
     *
     * @param
     *            $alicpoanop
     */
    public function setAlicpoanop($alicpoanop)
    {
        $this->alicpoanop = $alicpoanop;
        return $this;
    }

    /**
     *
     * @return the integer
     */
    public function getCgrempcodi()
    {
        return $this->cgrempcodi;
    }

    /**
     *
     * @param
     *            $cgrempcodi
     */
    public function setCgrempcodi($cgrempcodi)
    {
        $this->cgrempcodi = $cgrempcodi;
        return $this;
    }

    /**
     *
     * @return the integer
     */
    public function getCcomlicodi()
    {
        return $this->ccomlicodi;
    }

    /**
     *
     * @param
     *            $ccomlicodi
     */
    public function setCcomlicodi($ccomlicodi)
    {
        $this->ccomlicodi = $ccomlicodi;
        return $this;
    }

    /**
     *
     * @return the integer
     */
    public function getCorglicodi()
    {
        return $this->corglicodi;
    }

    /**
     *
     * @param
     *            $corglicodi
     */
    public function setCorglicodi($corglicodi)
    {
        $this->corglicodi = $corglicodi;
        return $this;
    }
}
