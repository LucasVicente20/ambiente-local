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
class Negocio_Entidade_AtaRegistroPrecoInterna
{

    /**
     */
    const NOME_TABELA = 'sfpc.tbataregistroprecointerna';

    /**
     * Código sequencial da ata de registro de preço - carpnosequ int4 NOT NULL
     *
     * CONSTRAINT parpexchave PRIMARY KEY(carpnosequ),
     * CONSTRAINT arpno_contem_arpex FOREIGN KEY(carpnosequ) REFERENCES sfpc.tbataregistropreconova(carpnosequ),
     *
     * @var integer
     */
    private $carpnosequ;

    /**
     * Código do Processo Licitatório - int4 NOT NULL,
     * 
     * @var integer
     */
    private $clicpoproc;

    /**
     * Ano do Processo Licitatório - int4 NOT NULL,
     * 
     * @var integer
     */
    private $alicpoanop;

    /**
     * Código do Grupo - int4 NOT NULL,
     * 
     * @var integer
     */
    private $cgrempcodi;

    /**
     * Código da Comissão - int4 NOT NULL,
     * 
     * @var integer
     */
    private $ccomlicodi;

    /**
     * Código do Órgão Licitante - int4 NOT NULL,
     * 
     * @var integer
     */
    private $corglicodi;

    /**
     * Objeto do Processo Licitatório - varchar(1000) NOT NULL,
     * 
     * @var string
     */
    private $earpinobje;

    /**
     * Ano da Numeração da Ata - int4 NOT NULL
     * 
     * @var integer
     */
    private $aarpinanon;

    /**
     * Código da Numeração da Ata para o Órgão no Ano - int4 NOT NULL,
     * 
     * @var integer
     */
    private $carpincodn;

    /**
     * Código do Fornecedor Credenciado - int4 NOT NULL,
     * 
     * @var integer
     */
    private $aforcrsequ;

    /**
     * Data e Hora Inicial da ata - timestamp NULL,
     * 
     * @var string
     */
    private $tarpindini;

    /**
     * Prazo de Vigência em Meses - int4 NULL,
     * 
     * @var integer
     */
    private $aarpinpzvg;

    /**
     * Situação da Ata (A- Ativa / I - Inativa) - bpchar(1) NOT NULL,
     * 
     * @var string
     */
    private $farpinsitu;

    /**
     * Código do Fornecedor Credenciado - int4 NULL,
     * 
     * @var integer
     */
    private $carpnoseq1;

    /**
     * Código do Usuário Responsável - int4 NOT NULL,
     * 
     * @var integer
     */
    private $cusupocodi;

    /**
     * Data/Hora da Última Alteração - timestamp NOT NULL,
     * 
     * @var string
     */
    private $tarpinulat;

    /**
     *
     * @return the integer
     */
    public function getCarpnosequ()
    {
        return $this->carpnosequ;
    }

    /**
     *
     * @param
     *            $carpnosequ
     */
    public function setCarpnosequ($carpnosequ)
    {
        $this->carpnosequ = $carpnosequ;
        return $this;
    }

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

    /**
     *
     * @return the string
     */
    public function getEarpinobje()
    {
        return $this->earpinobje;
    }

    /**
     *
     * @param
     *            $earpinobje
     */
    public function setEarpinobje($earpinobje)
    {
        $this->earpinobje = $earpinobje;
        return $this;
    }

    /**
     *
     * @return the integer
     */
    public function getAarpinanon()
    {
        return $this->aarpinanon;
    }

    /**
     *
     * @param
     *            $aarpinanon
     */
    public function setAarpinanon($aarpinanon)
    {
        $this->aarpinanon = $aarpinanon;
        return $this;
    }

    /**
     *
     * @return the integer
     */
    public function getCarpincodn()
    {
        return $this->carpincodn;
    }

    /**
     *
     * @param
     *            $carpincodn
     */
    public function setCarpincodn($carpincodn)
    {
        $this->carpincodn = $carpincodn;
        return $this;
    }

    /**
     *
     * @return the integer
     */
    public function getAforcrsequ()
    {
        return $this->aforcrsequ;
    }

    /**
     *
     * @param
     *            $aforcrsequ
     */
    public function setAforcrsequ($aforcrsequ)
    {
        $this->aforcrsequ = $aforcrsequ;
        return $this;
    }

    /**
     *
     * @return the string
     */
    public function getTarpindini()
    {
        return $this->tarpindini;
    }

    /**
     *
     * @param
     *            $tarpindini
     */
    public function setTarpindini($tarpindini)
    {
        $this->tarpindini = $tarpindini;
        return $this;
    }

    /**
     *
     * @return the integer
     */
    public function getAarpinpzvg()
    {
        return $this->aarpinpzvg;
    }

    /**
     *
     * @param
     *            $aarpinpzvg
     */
    public function setAarpinpzvg($aarpinpzvg)
    {
        $this->aarpinpzvg = $aarpinpzvg;
        return $this;
    }

    /**
     *
     * @return the string
     */
    public function getFarpinsitu()
    {
        return $this->farpinsitu;
    }

    /**
     *
     * @param
     *            $farpinsitu
     */
    public function setFarpinsitu($farpinsitu)
    {
        $this->farpinsitu = $farpinsitu;
        return $this;
    }

    /**
     *
     * @return the integer
     */
    public function getCarpnoseq1()
    {
        return $this->carpnoseq1;
    }

    /**
     *
     * @param
     *            $carpnoseq1
     */
    public function setCarpnoseq1($carpnoseq1)
    {
        $this->carpnoseq1 = $carpnoseq1;
        return $this;
    }

    /**
     *
     * @return the integer
     */
    public function getCusupocodi()
    {
        return $this->cusupocodi;
    }

    /**
     *
     * @param
     *            $cusupocodi
     */
    public function setCusupocodi($cusupocodi)
    {
        $this->cusupocodi = $cusupocodi;
        return $this;
    }

    /**
     *
     * @return the string
     */
    public function getTarpinulat()
    {
        return $this->tarpinulat;
    }

    /**
     *
     * @param
     *            $tarpinulat
     */
    public function setTarpinulat($tarpinulat)
    {
        $this->tarpinulat = $tarpinulat;
        return $this;
    }
}
