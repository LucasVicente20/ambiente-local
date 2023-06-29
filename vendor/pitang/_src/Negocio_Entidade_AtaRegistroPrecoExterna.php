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
class Negocio_Entidade_AtaRegistroPrecoExterna
{

    /**
     */
    const NOME_TABELA = 'sfpc.tbataregistroprecoexterna';

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
     * Ano da numeração da ata - aarpexanon int4 NOT null
     *
     * @var integer
     */
    private $aarpexanon;

    /**
     * Código da numeração da ata para o ano - carpexcodn int4 NOT null
     *
     * @var integer
     */
    private $carpexcodn;

    /**
     * Processo Licitatório Externo - earpexproc varchar(100) NOT null,
     *
     * @var string
     */
    private $earpexproc;

    /**
     * Código da Modalidade - cmodlicodi int4 null,
     *
     * CONSTRAINT modli_contem_arpex FOREIGN KEY(cmodlicodi) REFERENCES sfpc.tbmodalidadelicitacao(cmodlicodi),
     *
     * @var integer
     */
    private $cmodlicodi;

    /**
     * Órgão Gestor - earpexorgg varchar(100) NOT null,
     *
     * @var string
     */
    private $earpexorgg;

    /**
     * Objeto do Processo Licitatório - earpexobje varchar(1000) NOT null,
     *
     * @var string
     */
    private $earpexobje;

    /**
     * Data e Hora Inicial da ata - tarpexdini timestamp null,
     *
     * @var string
     */
    private $tarpexdini;

    /**
     * Prazo de Vigência em Meses - aarpexpzvg int4 null,
     *
     * @var [type]
     */
    private $aarpexpzvg;

    /**
     * Código do Fornecedor Original - aforcrsequ int4 null,
     *
     * CONSTRAINT forcr_participa_arpex FOREIGN KEY(aforcrsequ) REFERENCES sfpc.tbfornecedorcredenciado(aforcrsequ),
     *
     * @var [type]
     */
    private $aforcrsequ;

    /**
     * Código do Fornecedor Original - aforcrseq1 int4 null,
     *
     * CONSTRAINT forc1_participa_arpex FOREIGN KEY(aforcrseq1) REFERENCES sfpc.tbfornecedorcredenciado(aforcrsequ),
     *
     * @var [type]
     */
    private $aforcrseq1;

    /**
     * Situação da Ata (A- Ativa / I - Inativa) - farpexsitu bpchar(1) NOT null,
     *
     * @var [type]
     */
    private $farpexsitu;

    /**
     * Código do Usuário - cusupocodi int4 NOT null,
     *
     * CONSTRAINT usupo_elabora_arpex FOREIGN KEY(cusupocodi) REFERENCES sfpc.tbusuarioportal(cusupocodi)
     *
     * @var [type]
     */
    private $cusupocodi;

    /**
     * tarpinulat timestamp NOT null,
     *
     * @var [type]
     */
    private $tarpinulat;

    /**
     *
     * @var Negocio_Entidade_AtaRegistroPrecoNova
     */
    private $entidadeAtaRegistroPrecoNova;

    /**
     * Gets the Código sequencial da ata de registro de preço - carpnosequ int4 NOT NULL
     * CONSTRAINT parpexchave PRIMARY KEY(carpnosequ),
     * CONSTRAINT arpno_contem_arpex FOREIGN KEY(carpnosequ) REFERENCES sfpc.tbataregistropreconova(carpnosequ),.
     *
     * @return integer
     */
    public function getCarpnosequ()
    {
        return $this->carpnosequ;
    }

    /**
     * Sets the Código sequencial da ata de registro de preço - carpnosequ int4 NOT NULL
     * CONSTRAINT parpexchave PRIMARY KEY(carpnosequ),
     * CONSTRAINT arpno_contem_arpex FOREIGN KEY(carpnosequ) REFERENCES sfpc.tbataregistropreconova(carpnosequ),.
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
     * Gets the Ano da numeração da ata - aarpexanon int4 NOT null.
     *
     * @return integer
     */
    public function getAarpexanon()
    {
        return $this->aarpexanon;
    }

    /**
     * Sets the Ano da numeração da ata - aarpexanon int4 NOT null.
     *
     * @param integer $aarpexanon
     *            the aarpexanon
     *
     * @return self
     */
    public function setAarpexanon($aarpexanon)
    {
        $this->aarpexanon = $aarpexanon;

        return $this;
    }

    /**
     * Gets the Código da numeração da ata para o ano - carpexcodn int4 NOT null.
     *
     * @return integer
     */
    public function getCarpexcodn()
    {
        return $this->carpexcodn;
    }

    /**
     * Sets the Código da numeração da ata para o ano - carpexcodn int4 NOT null.
     *
     * @param integer $carpexcodn
     *            the carpexcodn
     *
     * @return self
     */
    public function setCarpexcodn($carpexcodn)
    {
        $this->carpexcodn = $carpexcodn;

        return $this;
    }

    /**
     * Gets the Processo Licitatório Externo - earpexproc varchar(100) NOT null,.
     *
     * @return string
     */
    public function getEarpexproc()
    {
        return $this->earpexproc;
    }

    /**
     * Sets the Processo Licitatório Externo - earpexproc varchar(100) NOT null,.
     *
     * @param string $earpexproc
     *            the earpexproc
     *
     * @return self
     */
    public function setEarpexproc($earpexproc)
    {
        $this->earpexproc = $earpexproc;

        return $this;
    }

    /**
     * Gets the Código da Modalidade - cmodlicodi int4 null,
     * CONSTRAINT modli_contem_arpex FOREIGN KEY(cmodlicodi) REFERENCES sfpc.tbmodalidadelicitacao(cmodlicodi),.
     *
     * @return integer
     */
    public function getCmodlicodi()
    {
        return $this->cmodlicodi;
    }

    /**
     * Sets the Código da Modalidade - cmodlicodi int4 null,
     * CONSTRAINT modli_contem_arpex FOREIGN KEY(cmodlicodi) REFERENCES sfpc.tbmodalidadelicitacao(cmodlicodi),.
     *
     * @param integer $cmodlicodi
     *            the cmodlicodi
     *
     * @return self
     */
    public function setCmodlicodi($cmodlicodi)
    {
        $this->cmodlicodi = $cmodlicodi;

        return $this;
    }

    /**
     * Gets the Órgão Gestor - earpexorgg varchar(100) NOT null,.
     *
     * @return string
     */
    public function getEarpexorgg()
    {
        return $this->earpexorgg;
    }

    /**
     * Sets the Órgão Gestor - earpexorgg varchar(100) NOT null,.
     *
     * @param string $earpexorgg
     *            the earpexorgg
     *
     * @return self
     */
    public function setEarpexorgg($earpexorgg)
    {
        $this->earpexorgg = $earpexorgg;

        return $this;
    }

    /**
     * Gets the Objeto do Processo Licitatório - earpexobje varchar(1000) NOT null,.
     *
     * @return string
     */
    public function getEarpexobje()
    {
        return $this->earpexobje;
    }

    /**
     * Sets the Objeto do Processo Licitatório - earpexobje varchar(1000) NOT null,.
     *
     * @param string $earpexobje
     *            the earpexobje
     *
     * @return self
     */
    public function setEarpexobje($earpexobje)
    {
        $this->earpexobje = $earpexobje;

        return $this;
    }

    /**
     * Gets the Data e Hora Inicial da ata - tarpexdini timestamp null,.
     *
     * @return string
     */
    public function getTarpexdini()
    {
        return $this->tarpexdini;
    }

    /**
     * Sets the Data e Hora Inicial da ata - tarpexdini timestamp null,.
     *
     * @param string $tarpexdini
     *            the tarpexdini
     *
     * @return self
     */
    public function setTarpexdini($tarpexdini)
    {
        $this->tarpexdini = $tarpexdini;

        return $this;
    }

    /**
     * Gets the Prazo de Vigência em Meses - aarpexpzvg int4 null,.
     *
     * @return [type]
     */
    public function getAarpexpzvg()
    {
        return $this->aarpexpzvg;
    }

    /**
     * Sets the Prazo de Vigência em Meses - aarpexpzvg int4 null,.
     *
     * @param [type] $aarpexpzvg
     *            the aarpexpzvg
     *
     * @return self
     */
    public function setAarpexpzvg($aarpexpzvg)
    {
        $this->aarpexpzvg = $aarpexpzvg;

        return $this;
    }

    /**
     * Gets the Código do Fornecedor Original - aforcrsequ int4 null,
     * CONSTRAINT forcr_participa_arpex FOREIGN KEY(aforcrsequ) REFERENCES sfpc.tbfornecedorcredenciado(aforcrsequ),.
     *
     * @return [type]
     */
    public function getAforcrsequ()
    {
        return $this->aforcrsequ;
    }

    /**
     * Sets the Código do Fornecedor Original - aforcrsequ int4 null,
     * CONSTRAINT forcr_participa_arpex FOREIGN KEY(aforcrsequ) REFERENCES sfpc.tbfornecedorcredenciado(aforcrsequ),.
     *
     * @param [type] $aforcrsequ
     *            the aforcrsequ
     *
     * @return self
     */
    public function setAforcrsequ($aforcrsequ)
    {
        $this->aforcrsequ = $aforcrsequ;

        return $this;
    }

    /**
     * Gets the Código do Fornecedor Original - aforcrseq1 int4 null,
     * CONSTRAINT forc1_participa_arpex FOREIGN KEY(aforcrseq1) REFERENCES sfpc.tbfornecedorcredenciado(aforcrsequ),.
     *
     * @return [type]
     */
    public function getAforcrseq1()
    {
        return $this->aforcrseq1;
    }

    /**
     * Sets the Código do Fornecedor Original - aforcrseq1 int4 null,
     * CONSTRAINT forc1_participa_arpex FOREIGN KEY(aforcrseq1) REFERENCES sfpc.tbfornecedorcredenciado(aforcrsequ),.
     *
     * @param [type] $aforcrseq1
     *            the aforcrseq1
     *
     * @return self
     */
    public function setAforcrseq1($aforcrseq1)
    {
        $this->aforcrseq1 = $aforcrseq1;

        return $this;
    }

    /**
     * Gets the Situação da Ata (A- Ativa / I - Inativa) - farpexsitu bpchar(1) NOT null,.
     *
     * @return [type]
     */
    public function getFarpexsitu()
    {
        return $this->farpexsitu;
    }

    /**
     * Sets the Situação da Ata (A- Ativa / I - Inativa) - farpexsitu bpchar(1) NOT null,.
     *
     * @param [type] $farpexsitu
     *            the farpexsitu
     *
     * @return self
     */
    public function setFarpexsitu($farpexsitu)
    {
        $this->farpexsitu = $farpexsitu;

        return $this;
    }

    /**
     * Gets the Código do Usuário - cusupocodi int4 NOT null,
     * CONSTRAINT usupo_elabora_arpex FOREIGN KEY(cusupocodi) REFERENCES sfpc.tbusuarioportal(cusupocodi).
     *
     * @return [type]
     */
    public function getCusupocodi()
    {
        return $this->cusupocodi;
    }

    /**
     * Sets the Código do Usuário - cusupocodi int4 NOT null,
     * CONSTRAINT usupo_elabora_arpex FOREIGN KEY(cusupocodi) REFERENCES sfpc.tbusuarioportal(cusupocodi).
     *
     * @param [type] $cusupocodi
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
     * Gets the tarpinulat timestamp NOT null,.
     *
     * @return [type]
     */
    public function getTarpinulat()
    {
        return $this->tarpinulat;
    }

    /**
     * Sets the tarpinulat timestamp NOT null,.
     *
     * @param [type] $tarpinulat
     *            the tarpinulat
     *
     * @return self
     */
    public function setTarpinulat($tarpinulat)
    {
        $this->tarpinulat = $tarpinulat;

        return $this;
    }

    /**
     *
     * @return the Negocio_Entidade_AtaRegistroPrecoNova
     */
    public function getEntidadeAtaRegistroPrecoNova()
    {
        return $this->entidadeAtaRegistroPrecoNova;
    }

    /**
     *
     * @param Negocio_Entidade_AtaRegistroPrecoNova $entidadeAtaRegistroPrecoNova
     */
    public function setEntidadeAtaRegistroPrecoNova(Negocio_Entidade_AtaRegistroPrecoNova $entidadeAtaRegistroPrecoNova = null)
    {
        if (! empty($entidadeAtaRegistroPrecoNova)) {
            $this->entidadeAtaRegistroPrecoNova = $entidadeAtaRegistroPrecoNova;
        }
        return $this;
    }
}
