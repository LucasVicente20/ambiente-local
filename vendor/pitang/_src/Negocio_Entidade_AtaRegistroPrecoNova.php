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
 */
class Negocio_Entidade_AtaRegistroPrecoNova
{

    /**
     * Tabela de Ata de Registro de Preço
     */
    const NOME_TABELA = 'sfpc.tbataregistropreconova';

    /**
     * Código sequencial da ata de registro de preço
     *
     * carpnosequ int4 NOT null,
     * CONSTRAINT parpnochave PRIMARY KEY(carpnosequ),
     *
     * @var integer
     */
    private $carpnosequ;

    /**
     * Tipo de Ata (I - Interna ou E - Externa)
     *
     * carpnotiat bpchar(1) NOT null,
     *
     * @var string
     */
    private $carpnotiat;

    /**
     * Data/Hora da Inclusão
     *
     * tarpnoincl timestamp NOT null,
     *
     * @var string
     */
    private $tarpnoincl;

    /**
     * Código do Usuário Responsável
     *
     * cusupocodi int4 NOT null,
     * CONSTRAINT usupo_elabora_arpno FOREIGN KEY(cusupocodi) REFERENCES sfpc.tbusuarioportal(cusupocodi)
     *
     * @var integer
     */
    private $cusupocodi;

    /**
     * Data/Hora da Última Alteração
     *
     * tarpnoulat timestamp NOT null,
     *
     * @var string
     */
    private $tarpnoulat;

    /**
     * Gets the Código sequencial da ata de registro de preço
     *
     * carpnosequ int4 NOT null,
     * CONSTRAINT parpnochave PRIMARY KEY(carpnosequ),.
     *
     * @return integer
     */
    public function getCarpnosequ()
    {
        return $this->carpnosequ;
    }

    /**
     * Sets the Código sequencial da ata de registro de preço
     * carpnosequ int4 NOT null,
     * CONSTRAINT parpnochave PRIMARY KEY(carpnosequ),.
     *
     * @param integer $carpnosequ
     *            the carpnosequ
     *            
     * @return self
     */
    public function setCarpnosequ($carpnosequ)
    {
        $this->carpnosequ = $carpnosequ;
        
        return $this;
    }

    /**
     * Gets the Tipo de Ata (I - Interna ou E - Externa)
     *
     * carpnotiat bpchar(1) NOT null,.
     *
     * @return string
     */
    public function getCarpnotiat()
    {
        return $this->carpnotiat;
    }

    /**
     * Sets the Tipo de Ata (I - Interna ou E - Externa)
     *
     * carpnotiat bpchar(1) NOT null,.
     *
     * @param integer $carpnotiat
     *            the carpnotiat
     *            
     * @return self
     */
    public function setCarpnotiat($carpnotiat)
    {
        $this->carpnotiat = $carpnotiat;
        
        return $this;
    }

    /**
     * Gets the Data/Hora da Inclusão
     *
     * tarpnoincl timestamp NOT null,.
     *
     * @return integer
     */
    public function getTarpnoincl()
    {
        return $this->tarpnoincl;
    }

    /**
     * Sets the Data/Hora da Inclusão
     *
     * tarpnoincl timestamp NOT null,.
     *
     * @param integer $tarpnoincl
     *            the tarpnoincl
     *            
     * @return self
     */
    public function setTarpnoincl($tarpnoincl)
    {
        $this->tarpnoincl = $tarpnoincl;
        
        return $this;
    }

    /**
     * Gets the Código do Usuário Responsável
     *
     * cusupocodi int4 NOT null,
     * CONSTRAINT usupo_elabora_arpno FOREIGN KEY(cusupocodi) REFERENCES sfpc.tbusuarioportal(cusupocodi).
     *
     * @return integer
     */
    public function getCusupocodi()
    {
        return $this->cusupocodi;
    }

    /**
     * Sets the Código do Usuário Responsável
     *
     * cusupocodi int4 NOT null,
     * CONSTRAINT usupo_elabora_arpno FOREIGN KEY(cusupocodi) REFERENCES sfpc.tbusuarioportal(cusupocodi).
     *
     * @param integer $cusupocodi
     *            the cusupocodi
     *            
     * @return self
     */
    public function setCusupocodi($cusupocodi)
    {
        $this->cusupocodi = $cusupocodi;
        
        return $this;
    }

    /**
     * Gets the Data/Hora da Última Alteração
     *
     * tarpnoulat timestamp NOT null,.
     *
     * @return string
     */
    public function getTarpnoulat()
    {
        return $this->tarpnoulat;
    }

    /**
     * Sets the Data/Hora da Última Alteração
     *
     * tarpnoulat timestamp NOT null,.
     *
     * @param string $tarpnoulat
     *            the tarpnoulat
     *            
     * @return self
     */
    public function setTarpnoulat($tarpnoulat)
    {
        $this->tarpnoulat = $tarpnoulat;
        
        return $this;
    }
}
