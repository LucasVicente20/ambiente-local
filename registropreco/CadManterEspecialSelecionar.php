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
 * @category  Pitang Registro Preço
 * @package   registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT: EMPREL-SAD-PORTAL-COMPRAS-REGISTRO-PRECO-BL-FUNC-20160601-1550
 * -----------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     26/09/2018
 * Objetivo: Tarefa Redmine 201676
 * -----------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     29/09/2018
 * Objetivo: Tarefa Redmine 206025
 * -----------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     14/11/2018
 * Objetivo: Tarefa Redmine 206842
 * -----------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI - Caio Coutinho
 * Data:     18/03/2019
 * Objetivo: Tarefa Redmine 212703
 * -----------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     13/08/2019
 * Objetivo: Tarefa Redmine 222075
 * -----------------------------------------------------------------------------------------------------
 * Alterado: João Madson
 * Data:     17/09/2019
 * Objetivo: Tarefa Redmine 223217
 * ----------------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     22/10/2021
 * ----------------------------------------------------------------------------------
 * Alterado: Osmar Celestino
 * Data:     08/02/2022
 * Objetivo: #255562
 * ----------------------------------------------------------------------------------
 */

if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

if(!empty($_SESSION['mensagemFeedback'])){    //Condição para chegagem de mensagem de erro indevidamente vindo de outra pag. e limpar o campo de mensagem. |Madson|
    if($_SESSION['conferePagina'] != 'selecionar'){
    unset($_SESSION['mensagemFeedback']);
    unset($_SESSION['conferePagina']);
    }
}
/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 */
class RegistroPreco_Dados_CadMigracaoAtaManter extends Dados_Abstrata
{

    /**
     *
     * @param unknown $ano
     */
    public function sqlProcessoExterno($ano)
    {
        $sql = " SELECT a.carpnosequ, a.aarpexanon, a.carpexcodn, a.earpexproc FROM sfpc.tbataregistroprecoexterna a WHERE a.aarpexanon = %d ";

        return sprintf($sql, $ano);
    }

    /**
     *
     * @param integer $processo
     * @param integer $ano
     * @param integer $orgao
     */
    public function sqlFornecedoresProcessoHomologado(Negocio_ValorObjeto_Clicpoproc $processo, Negocio_ValorObjeto_Alicpoanop $ano, Negocio_ValorObjeto_Corglicodi $orgao, $comissao = null, $grupo = null)
    {
        // TODO: Query antiga, remover quando a nova estiver bem testada
        // $licFaseHomologacao = 13;
        // $sql .= "SELECT DISTINCT fc.aforcrsequ,fc.nforcrrazs";
        // $sql .= " FROM sfpc.tbfornecedorcredenciado fc ";
        // $sql .= " INNER JOIN sfpc.tbitemlicitacaoportal ilp";
        // $sql .= " ON ilp.aforcrsequ = fc.aforcrsequ";
        // $sql .= " INNER JOIN sfpc.tblicitacaoportal lp";
        // $sql .= " ON lp.clicpoproc = ilp.clicpoproc";
        // $sql .= " INNER JOIN sfpc.tbfaselicitacao fl";
        // $sql .= " ON ilp.clicpoproc = fl.clicpoproc";
        // $sql .= " AND fl.cfasescodi = " . filter_var($licFaseHomologacao, FILTER_SANITIZE_NUMBER_INT);
        // $sql .= " where ilp.clicpoproc =" . filter_var($processo->getValor(), FILTER_SANITIZE_NUMBER_INT);
        // $sql .= " and ilp.alicpoanop =" . filter_var($ano->getValor(), FILTER_SANITIZE_NUMBER_INT);
        // $sql .= " and ilp.corglicodi =" . filter_var($orgao->getValor(), FILTER_SANITIZE_NUMBER_INT);

        $sql  = "select distinct b.aforcrsequ, b.NFORCRRAZS";
        $sql .= " from";
        $sql .= " SFPC.TBITEMLICITACAOPORTAL A inner join SFPC.TBFORNECEDORCREDENCIADO B on";
        $sql .= " A.AFORCRSEQU = B.AFORCRSEQU";
        $sql .= " where";
        $sql .= " A.CLICPOPROC = %d";
        $sql .= " and A.ALICPOANOP = %d";
        
        if(!empty($comissao)) {
            $sql .= " and A.CCOMLICODI = ".$comissao;
        }
        
        if(!empty($grupo)) {
            $sql .= " and A.CGREMPCODI = ".$grupo;
        }
        
        $sql .= " and A.CORGLICODI = %d";
        $sql .= " and A.FITELPLOGR = 'S'";

        return sprintf($sql, $processo->getValor(), $ano->getValor(), $orgao->getValor());
    }
   
    /**
     * sqlOrgaoAtaGerada
     *
     * SQL de consulta dos órgãos
     *
     * @return string SQL query
     */
    public function sqlOrgaoAtaGerada()
    {
        $sql  = "SELECT * FROM sfpc.tborgaolicitante WHERE forglisitu = 'A' ORDER BY eorglidesc ASC";

        return $sql;
    }

    /**
     * sqlProcessoRegistroPrecoEmHomologacao
     *
     * Consulta SQL para listar todos os processos que estão na fase 13 = HOMOLOGACAO
     * e que são do tipo registro de preço (flicporegp = 'S')
     *
     * @param Negocio_ValorObjeto_Alicpoanop $ano
     * @param Negocio_ValorObjeto_Corglicodi $orgao
     *
     * @return string Resultado da query
     */
    public function sqlProcessoRegistroPrecoEmHomologacao(Negocio_ValorObjeto_Alicpoanop $ano, Negocio_ValorObjeto_Corglicodi $orgao)
    {
        $sql  = "SELECT DISTINCT ";
        $sql .= " A.CLICPOPROC,";
        $sql .= " A.ALICPOANOP,";
        $sql .= " A.CCOMLICODI,";
        $sql .= " A.CGREMPCODI,";
        $sql .= " A.CORGLICODI,";
        $sql .= " B.ECOMLIDESC";
        $sql .= " FROM SFPC.TBLICITACAOPORTAL A";
        $sql .= " INNER JOIN SFPC.TBCOMISSAOLICITACAO B";
        $sql .= " ON A.CCOMLICODI = B.CCOMLICODI";
        $sql .= " INNER JOIN SFPC.TBFASELICITACAO E";
        $sql .= " ON A.CLICPOPROC = E.CLICPOPROC";

        $sql .= " and A.ALICPOANOP = E.ALICPOANOP ";
        $sql .= " and A.ccomlicodi = E.ccomlicodi ";

        $sql .= " AND E.CFASESCODI IN (13,26)";
        $sql .= " WHERE";
        $sql .= " A.ALICPOANOP = %d";
        $sql .= " AND A.FLICPOREGP LIKE 'S'";
        $sql .= " AND A.CORGLICODI = %d";
        $sql .= " ORDER BY";
        $sql .= " A.ALICPOANOP DESC,";
        $sql .= " A.CLICPOPROC DESC, ";
        $sql .= " B.ECOMLIDESC ASC";

        return sprintf($sql, $ano->getValor(), $orgao->getValor());

    }//end sqlProcessoRegistroPrecoEmHomologacao()

    /**
     *
     * @param Negocio_ValorObjeto_Alicpoanop $ano
     * @return string
     */
    public function sqlDadosProcessoExterno(Negocio_ValorObjeto_Alicpoanop $ano)
    {
        $sql = " SELECT arpe.carpnosequ, arpe.aarpexanon, arpe.earpexproc
                 FROM sfpc.tbataregistropreconova arpn
                 INNER JOIN sfpc.tbataregistroprecoexterna arpe
                    ON arpe.carpnosequ = arpn.carpnosequ
                 WHERE arpe.aarpexanon = %d";

        return sprintf($sql, $ano->getValor());
    }

    public function sqlConsultarProcessosItensAtasInt(Negocio_ValorObjeto_Clicpoproc $processo, Negocio_ValorObjeto_Alicpoanop $ano, Negocio_ValorObjeto_Corglicodi $orgao, $comissao = null, $grupo = null) {
        $sql=" SELECT distinct NULL, NULL, arpi.aforcrsequ, NULL, fc1.nforcrrazs as nforcrrazs1, arpi.carpnosequ,  
        arpi.clicpoproc as proclic, arpi.alicpoanop as anolic,  arpi.carpincodn as numata, arpi.aarpinanon, iarpn.citarpnuml,
        arpi.aforcrsequ as aforcrsequ1, arpi.carpincodn
        FROM sfpc.tbitemataregistropreconova iarpn
        LEFT JOIN sfpc.tbataregistroprecointerna arpi ON iarpn.carpnosequ = arpi.carpnosequ LEFT JOIN sfpc.tbfornecedorcredenciado fc1 ON arpi.aforcrsequ = fc1.aforcrsequ
        left join SFPC.tbfornecedorcredenciado forn 
        on arpi.aforcrsequ = forn.aforcrsequ 
        WHERE arpi.clicpoproc = ".$processo->getValor()." AND arpi.alicpoanop = ".$ano->getValor()." AND arpi.ccomlicodi = $comissao AND arpi.corglicodi = ".$orgao->getValor()." AND arpi.cgrempcodi = $grupo ORDER BY arpi.aarpinanon DESC, arpi.carpincodn ASC";

        return sprintf($sql, $processo->getValor(), $ano->getValor(), $grupo, $comissao, $orgao->getValor());
        $_SESSION['COMISSAOLIC'] = $comissao;
    }


    public function sqlConsultarProcessosItensAtas(Negocio_ValorObjeto_Clicpoproc $processo, Negocio_ValorObjeto_Alicpoanop $ano, Negocio_ValorObjeto_Corglicodi $orgao, $comissao = null, $grupo = null) {
        $sql = " SELECT ilp.citelpsequ, ilp.citelpnuml, ilp.aforcrsequ, ata.citarpitel, fc.nforcrrazs, ata.carpnosequ,
                        ata.clicpoproc, ata.alicpoanop, ata.carpincodn, ata.aarpinanon, ata.citarpnuml, ata.nforcrrazs as nforcrrazs1,
                        ata.aforcrsequ as aforcrsequ1
                 FROM sfpc.tbitemlicitacaoportal ilp LEFT JOIN 
                        (SELECT arpi.clicpoproc, iarpn.cmatepsequ, arpi.alicpoanop, arpi.ccomlicodi, arpi.corglicodi, 
                                arpi.cgrempcodi, iarpn.citarpitel, arpi.carpnosequ, arpi.aarpinanon, iarpn.citarpnuml,
                                arpi.carpincodn, fc1.nforcrrazs, fc1.aforcrsequ
                 FROM sfpc.tbitemataregistropreconova iarpn
                 LEFT JOIN sfpc.tbataregistroprecointerna arpi ON 
                            iarpn.carpnosequ = arpi.carpnosequ
                 LEFT JOIN sfpc.tbfornecedorcredenciado fc1 ON 
                           arpi.aforcrsequ = fc1.aforcrsequ
                    WHERE arpi.clicpoproc = ".$processo->getValor() ."
                            AND arpi.alicpoanop = ".$ano->getValor() ."
                            AND arpi.ccomlicodi = ".$comissao ."
                            AND arpi.corglicodi = ".$orgao->getValor() ."
                            AND arpi.cgrempcodi = ".$grupo .") AS ata ON 
                                ata.citarpitel = ilp.citelpsequ
                                AND ata.clicpoproc = ilp.clicpoproc 
                                AND ata.alicpoanop = ilp.alicpoanop 
                                AND ata.cgrempcodi = ilp.cgrempcodi 
                                AND ata.ccomlicodi = ilp.ccomlicodi 
                                AND ata.corglicodi = ilp.corglicodi
                INNER JOIN sfpc.tbfornecedorcredenciado fc ON 
                           ilp.aforcrsequ = fc.aforcrsequ
                WHERE ilp.clicpoproc = %d
                    AND ilp.alicpoanop = %d
                    AND ilp.cgrempcodi = %d
                    AND ilp.ccomlicodi = %d
                    AND ilp.corglicodi = %d
                ORDER BY ata.aarpinanon DESC, ata.carpincodn ASC";

        return sprintf($sql, $processo->getValor(), $ano->getValor(), $grupo, $comissao, $orgao->getValor());
        $_SESSION['COMISSAOLIC'] = $comissao;
    }

    public function sqlConsultarProcurarAta($carpnosequ) {
        $sql = " SELECT * FROM sfpc.tbataregistroprecointerna WHERE carpnosequ = %d ";

        return sprintf($sql, $carpnosequ);
    }

    public function sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi) {
        $sql = " SELECT ccp.ccenpocorg, ccp.ccenpounid, ccp.corglicodi FROM sfpc.tbcentrocustoportal ccp WHERE 1=1 ";

        if ($corglicodi != null || $corglicodi != "") {
          $sql .= " AND ccp.corglicodi = %d";
        }       

        return sprintf($sql, $corglicodi);
       
    }

}

/**
 * A camada de Negócio conterá o código que irá implementar todas as regras de negócio do sistema.
 *
 * Utiliza serviços da camada de Dados.
 */
class RegistroPreco_Negocio_CadMigracaoAtaManter extends Negocio_Abstrata
{

    public function __construct() {}

    /**
     *
     * {@inheritdoc}
     *
     * @see Negocio_Abstrata::getDados()
     * @return [Dados_Abstrata] [Retorna qualquer um que extenda de Dados_Abstrata]
     */
    public function getDados()
    {
        $this->setDados(new RegistroPreco_Dados_CadMigracaoAtaManter());
        return parent::getDados();
    }

    /**
     *
     * @param integer $processo
     * @param integer $ano
     * @param integer $orgao
     */
    public function consultarFornecedores($processo, $ano, $orgao, $comissao = null, $grupo = null)
    {
        $sql = $this->getDados()->sqlConsultarProcessosItensAtas(new Negocio_ValorObjeto_Clicpoproc($processo), new Negocio_ValorObjeto_Alicpoanop($ano), new Negocio_ValorObjeto_Corglicodi($orgao), $comissao, $grupo);
       
        $res = $this->getDados()->executarSQL($sql);
        $this->getDados()->hasError($res);

        return $res;
    }
    public function consultarFornecedoresInt($processo, $ano, $orgao, $comissao = null, $grupo = null)
    {
        $sql = $this->getDados()->sqlConsultarProcessosItensAtasInt(new Negocio_ValorObjeto_Clicpoproc($processo), new Negocio_ValorObjeto_Alicpoanop($ano), new Negocio_ValorObjeto_Corglicodi($orgao), $comissao, $grupo);
       
        $res = $this->getDados()->executarSQL($sql);
        $this->getDados()->hasError($res);

        return $res;
    }

    /**
     *
     * @param integer $ano
     */
    public function consultarProcessoExterno($ano)
    {
        $sql = $this->getDados()->sqlProcessoExterno($ano);
        $res = $this->getDados()->executarSQL($sql);
        $this->getDados()->hasError($res);
        return $res;
    }

    /**
     * consultarOrgaoComNumeracaoGerada
     *
     * Consulta os órgãos com numeração gerada
     *
     * @return array Lista de órgãos
     */
    public function consultarOrgaoComNumeracaoGerada()
    {
        $sql = $this->getDados()->sqlOrgaoAtaGerada();
        $res = $this->getDados()->executarSQL($sql);
        $this->getDados()->hasError($res);

        return $res;

    }//end consultarOrgaoComNumeracaoGerada()

    public function consultaExisteAtaRegistroPrecoInterna($processo)
    {
        $clicpoproc = new Negocio_ValorObjeto_Clicpoproc($processo->clicpoproc);
        $alicpoanop = new Negocio_ValorObjeto_Alicpoanop($processo->alicpoanop);
        $cgrempcodi = new Negocio_ValorObjeto_Cgrempcodi($processo->cgrempcodi);
        $ccomlicodi = new Negocio_ValorObjeto_Ccomlicodi($processo->ccomlicodi);
        $corglicodi = new Negocio_ValorObjeto_Corglicodi($processo->corglicodi);
        $repositorio = new Negocio_Repositorio_AtaRegistroPrecoInterna();
        $res = $repositorio->procurarPorProcessoLicitatorio($clicpoproc, $alicpoanop, $cgrempcodi, $ccomlicodi, $corglicodi);
        $entidade = current($res);

        return ($entidade->carpnosequ > 0) ? true : false;
    }

    public function consultaExisteAtaRegistroPrecoInternaFornecedor($processo)
    {
        $clicpoproc = new Negocio_ValorObjeto_Clicpoproc($processo->clicpoproc);
        $alicpoanop = new Negocio_ValorObjeto_Alicpoanop($processo->alicpoanop);
        $cgrempcodi = new Negocio_ValorObjeto_Cgrempcodi($processo->cgrempcodi);
        $ccomlicodi = new Negocio_ValorObjeto_Ccomlicodi($processo->ccomlicodi);
        $corglicodi = new Negocio_ValorObjeto_Corglicodi($processo->corglicodi);
        $aforcrsequ = $processo->aforcrsequ;

        $repositorio = new Negocio_Repositorio_AtaRegistroPrecoInterna();
        $res = $repositorio->procurarPorProcessoLicitatorioFornecedor($clicpoproc, $alicpoanop, $cgrempcodi, $ccomlicodi, $corglicodi, $aforcrsequ);

        $entidade = current($res);

        return ($entidade->carpnosequ > 0) ? true : false;
    }

    /**
     * pesquisarProcessoRegistroPrecoEmHomologacao
     *
     * Pesquisa o processo de registro de preco na situacao de homologacao
     *
     * @param integer $ano
     * @param integer $orgao
     *
     * @return array Lista de processos
     */
    public function pesquisarProcessoRegistroPrecoEmHomologacao($ano, $orgao)
    {
        $voAno      = new Negocio_ValorObjeto_Alicpoanop($ano);
        $voOrgao    = new Negocio_ValorObjeto_Corglicodi($orgao);

        $sql = $this->getDados()->sqlProcessoRegistroPrecoEmHomologacao($voAno, $voOrgao);
        $res = $this->getDados()->executarSQL($sql);
        $this->getDados()->hasError($res);
        return $res;

    }//end pesquisarProcessoRegistroPrecoEmHomologacao()

    /**
     *
     * @param integer $ano
     * @return array
     *
     * @throws
     *
     */
    public function getDadosProcessoExterno($ano)
    {
        $voAno = new Negocio_ValorObjeto_Alicpoanop($ano);
        $sql = $this->getDados()->sqlDadosProcessoExterno($voAno);
        $res = $this->getDados()->executarSQL($sql);
        $this->getDados()->hasError($res);
        return $res;
    }

    public function procurar($carpnosequ)
    {   
        $db  = Conexao();      
        $sql = $this->getDados()->sqlConsultarProcurarAta($carpnosequ);
        $res = executarSQL($db, $sql);        
        $itens = array();
        $item = null;

        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }        

        return $itens;
    }

    public function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {   
        $db  = Conexao();      
        $sql = $this->getDados()->sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi);
        $res = executarSQL($db, $sql);
        
        $itens = array();
        $item = null;
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }
        if (PEAR::isError($res)) {
            ExibeErroBD(__FILE__ . "\nLinha: ".__LINE__."\nSql: " . $database->getMessage());
        }    
        
        return $itens;
    }
}

/**
 * A camada de Adaptação e Transformação conterá o código que tratará a lógica de apresentação dos resultados
 * das requisições dos usuários e a troca de dados com sistemas externos.
 *
 * Utiliza serviços da camada de Negócio.
 */
class RegistroPreco_Adaptacao_CadMigracaoAtaManter extends Adaptacao_Abstrata
{

    public function __construct() {}

    /**
     *
     * {@inheritdoc}
     *
     * @see Adaptacao_Abstrata::getNegocio()
     */
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadMigracaoAtaManter());
        return parent::getNegocio();
    }

    /**
     * gerarDadosProcesso
     *
     * Gera os valores dos processos
     *
     * @return array Lista de processos
     *
     */
    public function gerarDadosProcesso($ano, $orgao)
    {
        return $this->getNegocio()->pesquisarProcessoRegistroPrecoEmHomologacao($ano, $orgao);

    }//end gerarDadosProcesso()

    public function getDadosProcessoExterno($ano)
    {
        return $this->getNegocio()->getDadosProcessoExterno($ano);
    }

    /**
     * gerarOrgaoComNumeracaoGerada
     *
     * Consulta os órgãos com numeração gerada
     *
     * @return void
     */
    public function gerarOrgaoComNumeracaoGerada()
    {
        $orgaos = $this->getNegocio()->consultarOrgaoComNumeracaoGerada();
        return $orgaos;

    }//end gerarOrgaoComNumeracaoGerada()

    public function existeAtaRegistroPrecoInterna($processo)
    {
        $ata = $this->getNegocio()->consultaExisteAtaRegistroPrecoInterna($processo);
        return $ata;
    } 

    public function existeAtaRegistroPrecoInternaFornecedor($processo)
    {
        $ata = $this->getNegocio()->consultaExisteAtaRegistroPrecoInternaFornecedor($processo);
        return $ata;
    } 

    public function gerarDadosFornecedorInt($processo, $ano, $orgao, $comissao = null, $grupo = null)
    {
        return $this->getNegocio()->consultarFornecedoresInt($processo, $ano, $orgao, $comissao, $grupo);
    }
    public function gerarDadosFornecedor($processo, $ano, $orgao, $comissao = null, $grupo = null)
    {
        return $this->getNegocio()->consultarFornecedores($processo, $ano, $orgao, $comissao, $grupo);
    }
}

/**
 * A camada de Interface Gráfica com o Usuário é a camada que conterá o código que irá implementar a interação
 * do sistema com os usuários (telas, relatórios e troca de dados).
 *
 * Utiliza serviços da camada de Adaptação e Transformação.
 */
class RegistroPreco_UI_CadMigracaoAtaManter extends UI_Abstrata
{

    const TIPO_ATA_INTERNA = "I";
    const TIPO_ATA_EXTERNA = "E";
    private $tipoAta;
    private $orgaoGestor;
    private $ano;
    private $processo;
    private $codigoComissao;
    private $codigoGrupo;
    private $fornecedor;
    private $processoExterno;
    private $anoProcessoExterno;

    /**
     * [plotarBlocoProcesso description]
     *
     * @param GUI $gui
     *            [description]
     * @param [type] $processos
     *            [description]
     * @return [type] [description]
     */
    private function plotarBlocoProcesso($processos)
    {
        $this->processo = null;
        if (isset($_POST['processo'])) {
            $_processo = explode('-', $_REQUEST['processo']);
            $this->processo = filter_var($_processo[0]);
        }

        $comissaoCode = isset($_POST['comissaoselecionada']) ? filter_var($_POST['comissaoselecionada'], FILTER_SANITIZE_NUMBER_INT) : null;

        $this->getTemplate()->TEXTO_PROCESSO_VAZIO = 'Selecione o processo';
        $this->getTemplate()->PROCESSO_VALUE_VAZIO = - 1;

        if (count($processos) > 0) {
            // Oraganizar por comissão
            $comissoes = array();
            foreach ($processos as $processo) {
                $comissoes[$processo->ccomlicodi][] = $processo;
            }

            $count = 0;
            foreach ($comissoes as $key => $comissao) {
                foreach ($comissao as $key_ => $processo) {
                    if ($processo != null) {
                        if ($key_ == 0) {                                                  
                            $this->getTemplate()->OPTGROUP_INICIO = '<optgroup label="' . $processo->ecomlidesc . '">';
                        }

                        $numeroProcesso = str_pad($processo->clicpoproc, 4, '0', STR_PAD_LEFT);
                        $textoProcesso = $numeroProcesso . '/' . $processo->alicpoanop;
                        if ($this->getAdaptacao()->existeAtaRegistroPrecoInterna($processo)) {
                            $textoProcesso .= ' * ';
                        }
                        $formataId = implode('-', array(
                            $processo->clicpoproc,
                            $processo->alicpoanop,
                            $processo->cgrempcodi,
                            $processo->ccomlicodi,
                            $processo->corglicodi,
                        ));
                        
                        $this->getTemplate()->TEXTO_PROCESSO = $textoProcesso;
                        $this->getTemplate()->PROCESSO_VALUE = $formataId;
                        $this->getTemplate()->VALOR_COMISSAO = $processo->ccomlicodi;
                        $this->getTemplate()->VALOR_GRUPO = $processo->cgrempcodi;
                    }
                    
                    // Vendo se a opção atual deve ter o atributo "selected"CadAtaRegistroPrecoInternaManter
                    if ($processo->clicpoproc == $this->processo && $processo->ccomlicodi == $comissaoCode) {
                        $this->getTemplate()->PROCESSO_SELECTED = "selected";
                    } else {
                        // Caso esta seja a opção atual, limpamos o valor da variável SELECTED
                        $this->getTemplate()->clear("PROCESSO_SELECTED");
                    }
                    $this->getTemplate()->block("BLOCK_OPTION");
                }
                
                $this->getTemplate()->OPTGROUP_FINAL = '</optgroup>';
                $this->getTemplate()->block("BLOCK_OPTIONGROUP");
            }

            $this->getTemplate()->block("BLOCK_PROCESSO");
        }
    }

    /**
     * Preenche os valores do select de órgãos
     * @return void
     */
    private function plotarBlocoOrgao()
    {
        $orgaos = $this->getAdaptacao()->gerarOrgaoComNumeracaoGerada();

        $this->orgaoGestor = isset($_POST['orgaoGestor']) ? filter_var($_POST['orgaoGestor'], FILTER_SANITIZE_NUMBER_INT) : null;

        if (empty($orgaos)) {
            $_SESSION['mensagemFeedback'] = ExibeMensStr('Órgãos não foram informados para exibição', 1, 0);
            $_SESSION['conferepagina'] = 'selecionar';
            return;
        }

        $this->getTemplate()->ORGAO_VALUE       = 0;
        $this->getTemplate()->ORGAO_TEXT        = 'Selecione o órgão';
        $this->getTemplate()->ORGAO_SELECTED    = "selected";
        $this->getTemplate()->block("BLOCO_ORGAO");

        foreach ($orgaos as $orgao) {
            $this->getTemplate()->ORGAO_VALUE   = $orgao->corglicodi;
            $this->getTemplate()->ORGAO_TEXT    = $orgao->eorglidesc;

            // Vendo se a opção atual deve ter o atributo "selected"
            if ($this->orgaoGestor == $orgao->corglicodi) {
                $this->getTemplate()->ORGAO_SELECTED = "selected";
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
                $this->getTemplate()->clear("ORGAO_SELECTED");
            }

            $this->getTemplate()->block("BLOCO_ORGAO");
        }//end foreach

    }//end plotarBlocoOrgao()

    /**
     * plotarBlocoAno
     *
     * Monta o select de ano
     *
     * @param array $anos Lista com anos
     *
     * @return void
     */
    private function plotarBlocoAno(array $anos)
    {
        $this->ano = isset($_POST['GerarNumeracaoAno']) ? filter_var($_POST['GerarNumeracaoAno'], FILTER_SANITIZE_STRING) : null;
        $anoSelecionado = $anos[$this->ano];

        $this->getTemplate()->ANO_VALUE     = -1;
        $this->getTemplate()->ANO_TEXT      = 'Selecione o ano';
        $this->getTemplate()->ANO_SELECTED  = "selected";
        $this->getTemplate()->block("BLOCO_ANO");

        foreach ($anos as $value => $text) {
            $this->getTemplate()->ANO_VALUE = $value;
            $this->getTemplate()->ANO_TEXT  = $text;

            // Vendo se a opção atual deve ter o atributo "selected"
            if ($anoSelecionado === $text) {
                $this->getTemplate()->ANO_SELECTED = "selected";
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
                $this->getTemplate()->clear("ANO_SELECTED");
            }
            $this->getTemplate()->block("BLOCO_ANO");
        }

    }//end plotarBlocoAno()

    /**
     *
     * @param array $anos
     */
    private function plotarBlocoAnoExterno(array $anos)
    {
        $this->anoProcessoExterno = isset($_POST['anoProcessoExterno']) ? (int) filter_var($_POST['anoProcessoExterno'], FILTER_SANITIZE_STRING) : null;
        $this->getTemplate()->ANO_VALUE = - 1;
        $this->getTemplate()->ANO_TEXT = 'Selecione o ano';
        $this->getTemplate()->ANO_SELECTED_EXTERNO = "selected";
        $this->getTemplate()->block("BLOCO_ANO_EXTERNO");

        foreach ($anos as $value => $text) {
            $this->getTemplate()->ANO_VALUE = $value;
            $this->getTemplate()->ANO_TEXT = $text;

            // Vendo se a opção atual deve ter o atributo "selected"
            if ($this->anoProcessoExterno === $value) {
                $this->getTemplate()->ANO_SELECTED_EXTERNO = "selected";
            } else {
                // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
                $this->getTemplate()->clear("ANO_SELECTED_EXTERNO");
            }
            $this->getTemplate()->block("BLOCO_ANO_EXTERNO");
        }
    }

    private function plotarBlocoProcessoExterno(array $processos)
    {
        $this->processoExterno = isset($_POST['processoexterno']) ? filter_var($_POST['processoexterno']) : null;
        $this->getTemplate()->PROCESSO_VALUE = - 1;
        $this->getTemplate()->TEXTO_PROCESSO = 'Selecione o processo';
        $this->getTemplate()->PROCESSO_SELECTED = "selected";
        $this->getTemplate()->block('BLOCK_OPTION_EXTERNO');
        if (! empty($processos)) {
            foreach ($processos as $processo) {
                $this->getTemplate()->PROCESSO_VALUE = $processo->carpnosequ;
                $this->getTemplate()->TEXTO_PROCESSO = $processo->earpexproc;

                // Vendo se a opção atual deve ter o atributo "selected"
                if ($this->processoExterno === $processo->carpnosequ) {
                    $this->getTemplate()->PROCESSO_SELECTED = "selected";
                } else {
                    // Caso esta não seja a opção atual, limpamos o valor da variável SELECTED
                    $this->getTemplate()->clear("PROCESSO_SELECTED");
                }

                $this->getTemplate()->block('BLOCK_OPTION_EXTERNO');
            }
            $this->getTemplate()->block("BLOCK_PROCESSO_EXTERNO");
        }
    }


    /**
     * Monta o bloco de fornecedor
     *
     * @param array $fornecedores Lista de fornecedores
     * @return void
     */
    private function plotarBlocoFornecedor($fornecedores,  $fornecedorInt)
    {
        $fornecedorPOST = null;
        $btnSelecionar = false;
        if (isset($_POST['fornecedor'])) {
            $fornecedorPOST = filter_var($_POST['fornecedor'], FILTER_SANITIZE_NUMBER_INT);
            if (! filter_var($fornecedorPOST, FILTER_VALIDATE_INT)) {
                $_SESSION['mensagemFeedback'] = ExibeMensStr('Fornecedor informando não é válido', 1, 0);
                $_SESSION['conferepagina'] = 'selecionar';
                return;
            }
        }

        if (empty($fornecedores)) {
            return;
        }

        $dados = $this->prepararDados($fornecedores);

        if(!empty($dados['atas_nao_geradas'])) {
            foreach ($dados['atas_nao_geradas'] as $key => $fornecedor) {     
                $this->getTemplate()->VALOR_LOTE                 = implode(',', array_unique($fornecedor['lotes']));
                $this->getTemplate()->VALOR_FORNECEDOR           = $fornecedor['nforcrrazs'];
                $this->getTemplate()->FORNECEDOR_VALUE           = $key;
                $this->getTemplate()->block("BLOCO_RESULTADO_ATAS_NAO_GERADAS");
            } 

            $this->getTemplate()->block("BLOCO_RESULTADO_AREA_ATAS_NAO_GERADAS");
            $btnSelecionar = true;
        }

       

           

         $dadosInt = $this->prepararDados($fornecedorInt);

        // Ata com numeração
        if(!empty($dadosInt['atas_geradas'])) {            
            foreach ($dadosInt['atas_geradas'] as $key => $ata) {
                $ataInterna = $this->getAdaptacao()->getNegocio()->procurar($ata['carpnosequ']);
                $dto = $this->getAdaptacao()->getNegocio()->consultarDCentroDeCustoUsuario($ataInterna[0]->cgrempcodi, $ataInterna[0]->cusupocodi, $ataInterna[0]->corglicodi);
                $objeto = current($dto);
                $uri = 'CadRegistroPrecoLicitacaoNumeracaoAtas.php?';
                $uri .= 'processo=' . $ata['clicpoproc'] . '&';
                $uri .= 'ano=' . $ata['alicpoanop'] . '&';
                $uri .= 'fornecedor=' . $ata['aforcrsequ1'];
                $uri .= '&ata=' . $ataInterna[0]->carpincodn;
                $uri .= '&ccenpocorg=' . $objeto->ccenpocorg;
                $uri .= '&ccenpounid=' . $objeto->ccenpounid;
                $uri .= '&seqAta=' . $ata['carpnosequ'];

                $numeroAtaFormatado = $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);                            
                $numeroAtaFormatado .= "." . str_pad($ataInterna[0]->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ata['aarpinanon'];
             
                $this->getTemplate()->VALOR_SEQ_ATA = "<input type='hidden' name='carpnosequ[".$ata['aforcrsequ1']. $ata['carpincodn'] ."]' value='".$ata['carpnosequ']."'>";  
                $this->getTemplate()->VALOR_CARPINCODN = "<input type='hidden' name='carpincodn[".$ata['aforcrsequ1']. $ata['carpincodn'] ."]' value='".$ata['carpincodn']."'>";  
                $this->getTemplate()->VALOR_ENDERECO_DETALHE_ATA = $numeroAtaFormatado;                
                $this->getTemplate()->VALOR_NUMERO_ATA_HIDDEN = $numeroAtaFormatado;
                $this->getTemplate()->VALOR_FORNECEDOR = $ata['nforcrrazs1'];
                $this->getTemplate()->VALOR_LOTE = implode(',', array_unique($ata['lotes']));
                $this->getTemplate()->FORNECEDOR_VALUE = $ata['aforcrsequ1'] . '-' . $ata['carpincodn'];
                $this->getTemplate()->block("BLOCO_RESULTADO_ATAS_GERADAS");
            }
            print_r($numeroAtaFormatado);
            $btnSelecionar = true;
            $this->getTemplate()->block("BLOCO_RESULTADO_AREA_ATAS_GERADAS");
            
        }   
        
        if($btnSelecionar) {
            $this->getTemplate()->block("BOTAO_SELECIONAR"); 
        }

    }//end plotarBlocoFornecedor()

    /**
     * Função para separar os tipos de atas
     * para exibição
     * 
     * @param $atas
     * @return array
     */
    public function prepararDados($atas) { // abaco
        $dados = array(
            'atas_geradas' => array(), 
            'atas_nao_geradas' => array()
        );

        if(!empty($atas)) {
            foreach($atas as $key => $value) {
                // Verificar se tem numeração
                if(empty($value->carpnosequ)) {
                    $dados['atas_nao_geradas'][$value->aforcrsequ]['nforcrrazs'] = $value->nforcrrazs;
                    $dados['atas_nao_geradas'][$value->aforcrsequ]['lotes'][] = $value->citelpnuml;
                } else {
                    $dados['atas_geradas'][$value->aforcrsequ1 . $value->carpincodn]['nforcrrazs1'] = $value->nforcrrazs1;
                    $dados['atas_geradas'][$value->aforcrsequ1 . $value->carpincodn]['carpnosequ'] = $value->carpnosequ;
                    $dados['atas_geradas'][$value->aforcrsequ1 . $value->carpincodn]['alicpoanop'] = $value->alicpoanop;
                    $dados['atas_geradas'][$value->aforcrsequ1 . $value->carpincodn]['aforcrsequ1'] = $value->aforcrsequ1;
                    $dados['atas_geradas'][$value->aforcrsequ1 . $value->carpincodn]['carpincodn'] = $value->carpincodn;
                    $dados['atas_geradas'][$value->aforcrsequ1 . $value->carpincodn]['aarpinanon'] = $value->aarpinanon;
                    $dados['atas_geradas'][$value->aforcrsequ1 . $value->carpincodn]['lotes'][] = $value->citarpnuml;                    
                }
            }
        }
        
        return $dados;
    }

    public function __construct() {
        $this->setTemplate(new TemplatePaginaPadrao("templates/CadManterEspecialSelecionar.html", "Registro de Preço > Manter Especial > Selecionar"));
    }

    public function getAdaptacao() {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadMigracaoAtaManter());
        return parent::getAdaptacao();
    }

    /**
     * Gera valores do órgão gestor
     *
     * @param array $processos Lista de processos
     * @return void
     */
    public function gerarValoresOrgaoGestor($processos = null)
    {
        $this->plotarBlocoOrgao($orgaos);        
        $this->plotarBlocoProcesso($processos);

    }//end gerarValoresOrgaoGestor()

    public function selecionarAlterarAta()
    {
        $anos = HelperPitang::carregarAno();
        $this->processo = null;
        unset($_SESSION['requestDaVez']);

        $processo_ = isset($_POST['processo']) ? $_POST['processo'] : null;
        
        if(!empty($processo_)) {
            $processo_ = explode('-', $processo_);
            $this->processo = $processo_[0];
        }

        if (! filter_var($this->processo, FILTER_VALIDATE_INT)) {
            $_SESSION['mensagemFeedback'] = ExibeMensStr('O Processo não foi informado', 1, 0);
            $_SESSION['conferepagina'] = 'selecionar';
            return;
        }

        $this->codigoGrupo = isset($_POST['gruposelecionada']) ? filter_var($_POST['gruposelecionada'], FILTER_SANITIZE_NUMBER_INT) : null;
        if (! filter_var($this->codigoGrupo, FILTER_VALIDATE_INT)) {
            $_SESSION['mensagemFeedback'] = ExibeMensStr('O Processo não foi informado', 1, 0);
            $_SESSION['conferepagina'] = 'selecionar';
            return;
        }

        $this->codigoComissao = isset($_POST['comissaoselecionada']) ? filter_var($_POST['comissaoselecionada'], FILTER_SANITIZE_NUMBER_INT) : null;
        if (! filter_var($this->codigoComissao, FILTER_VALIDATE_INT)) {
            $_SESSION['mensagemFeedback'] = ExibeMensStr('O Processo não foi informado', 1, 0);
            $_SESSION['conferepagina'] = 'selecionar';
            return;
        }

        $this->orgaoGestor = isset($_POST['orgaoGestor']) ? filter_var($_POST['orgaoGestor'], FILTER_SANITIZE_NUMBER_INT) : null;

        if (! filter_var($this->orgaoGestor, FILTER_VALIDATE_INT)) {
            $_SESSION['mensagemFeedback'] = ExibeMensStr('Órgão não foi informando', 1, 0);
            $_SESSION['conferepagina'] = 'selecionar';
            return;
        }

        $idAno = isset($_POST['GerarNumeracaoAno']) ? filter_var($_POST['GerarNumeracaoAno'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->ano = $anos[$idAno];
        if (! filter_var($this->ano, FILTER_VALIDATE_INT)) {
            $_SESSION['mensagemFeedback'] = ExibeMensStr('Ano da Ata de Registro de Preço não foi informando', 1, 0);
            $_SESSION['conferepagina'] = 'selecionar';
            return;
        }

        $this->fornecedor = isset($_POST['fornecedor']) ? $_POST['fornecedor'] : null;
        if (empty($this->fornecedor)) {
            $_SESSION['mensagemFeedback'] = ExibeMensStr('Fornecedor da Ata de Registro de Preço não foi informando', 1, 0);
            $_SESSION['conferepagina'] = 'selecionar';
            return;
        }

        if (empty($this->processo) || empty($this->orgaoGestor) || empty($this->ano) || empty($this->fornecedor)) {
            $_SESSION['mensagemFeedback'] = ExibeMensStr('Não foi possivel continua', 1, 0);
            $_SESSION['conferepagina'] = 'selecionar';
            return;
        }

        $fornecedor = explode('-', $this->fornecedor);

        $this->fornecedor = $fornecedor[0];        
        $carpincodn = count($fornecedor == 2) ? $fornecedor[1] : null;
        $ata = '';
        $lote = isset($_POST['lote'][$this->fornecedor]) ? $_POST['lote'][$this->fornecedor] : '' ;

        if(!is_null($carpincodn)) {
            if(isset($_POST['carpnosequ'][$this->fornecedor . $carpincodn])) {
                $ata = $_POST['carpnosequ'][$this->fornecedor.$carpincodn];
            }            
        } else {
            if(isset($_POST['carpnosequ'][$this->fornecedor])) {
                $ata = $_POST['carpnosequ'][$this->fornecedor];
            }
        }

        AddMenuAcesso('/registropreco/CadManterEspecial.php');        
        $uri = "CadManterEspecial.php?tipo=I&ano=".$this->ano."&fullprocesso=".$_POST['processo']."&processo=".$this->processo."&orgao=".$this->orgaoGestor."&fornecedor=".$this->fornecedor."&comissaocodigo=".$this->codigoComissao."&grupocodigo=".$this->codigoGrupo."&ata=".$ata."&lote=".$lote;

        header('Location: ' . $uri);
        exit();
    }

    /**
     * gerarDadosFornecedor
     *
     * Gera os dados do fornecedor
     *
     * @return void
     */
    public function gerarDadosFornecedor()
    {
        $anos = HelperPitang::carregarAno();
        $gerarNumeracaoAno  = isset($_POST['GerarNumeracaoAno']) ? filter_var($_POST['GerarNumeracaoAno'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->ano          = $anos[$gerarNumeracaoAno];
        $this->orgaoGestor  = isset($_POST['orgaoGestor']) ? filter_var($_POST['orgaoGestor'], FILTER_SANITIZE_NUMBER_INT) : null;
        
        $this->codigoComissao   = isset($_POST['comissaoselecionada']) ? filter_var($_POST['comissaoselecionada'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->codigoGrupo      = isset($_POST['gruposelecionada']) ? filter_var($_POST['gruposelecionada'], FILTER_SANITIZE_NUMBER_INT) : null;
        $this->processo         = isset($_POST['processo']) ? $_POST['processo'] : null;
        
        $processo = explode('-', $this->processo);
        
        if (!empty($this->processo)) {
            $fornecedores = $this->getAdaptacao()->gerarDadosFornecedor($processo[0], $this->ano, $this->orgaoGestor, $this->codigoComissao, $this->codigoGrupo);
            $fornecedorInt = $this->getAdaptacao()->gerarDadosFornecedorInt($processo[0], $this->ano, $this->orgaoGestor, $this->codigoComissao, $this->codigoGrupo);
            $this->plotarBlocoFornecedor($fornecedores, $fornecedorInt);
        }

        //$this->getAdaptacao()->gerarDadosFornecedor($this->processo, $this->ano, $this->orgaoGestor);
        
        $this->gerarValoresProcesso();
    }//end gerarDadosFornecedor()

    /**
     * processarPrincipal
     *
     * Este método é a porta de entrada default da página. Sempre que executar algum comando é aconselhável chamá-lo
     *
     * @return void
     */
    public function processarPrincipal()
    {
        // Limpa os itens deletados
        $_SESSION['item'] = null;
        $_SESSION['itens_deletados'] = null;
        unset($_SESSION['post_itens_armazenar_tela']);
        unset($_SESSION['Arquivos_Upload']);
        $this->imprimeBlocoMensagem();
        $this->tipoAta = "I";

        $this->gerarValoresOrgaoGestor();

        $anos = HelperPitang::carregarAno();
        $this->plotarBlocoAno($anos);
        $this->getTemplate()->block('BLOCO_TIPO_INTERNA');

    }//end processarPrincipal()

    /**
     * Gera os valores do processo, após selecionar o ano
     *
     * @return void
     */
    public function gerarValoresProcesso()
    {
        $this->tipoAta = filter_var($_REQUEST['TipoATA']);
        $anos = HelperPitang::carregarAno();

        if (! isset($_REQUEST['GerarNumeracaoAno'])) {
            $_SESSION['mensagemFeedback'] = ExibeMensStr("Número da Ata de Registro de Preço Externa não foi informada", 1, 0);
            $_SESSION['conferepagina'] = 'selecionar';
            return;
        }

        if (! isset($_REQUEST['orgaoGestor'])) {
            $_SESSION['mensagemFeedback'] = ExibeMensStr("Órgão Gestor da Ata de Registro de Preço Externa não foi informada", 1, 0);
            $_SESSION['conferepagina'] = 'selecionar';
            return;
        }

        $this->ano          = $anos[filter_var($_POST['GerarNumeracaoAno'], FILTER_SANITIZE_NUMBER_INT)];
        $this->orgaoGestor  = filter_var($_REQUEST['orgaoGestor'], FILTER_SANITIZE_NUMBER_INT);

        if($this->ano <= 0 or $this->orgaoGestor <= 0) {
            return;
        }

        $processos = $this->getAdaptacao()->gerarDadosProcesso($this->ano, $this->orgaoGestor);

        $this->gerarValoresOrgaoGestor($processos);

    }//end gerarValoresProcesso()

}

/**
 * [$app description]
 *
 * @var Negocio
 */
$app = new RegistroPreco_UI_CadMigracaoAtaManter();

$acao = isset($_POST['Botao']) ? filter_var($_POST['Botao'], FILTER_SANITIZE_STRING) : 'Principal';
unset($_SESSION['itemAtaSubmitDocument']);

switch ($acao) {
    case 'SelecionarOrgaoInterno':
        $app->gerarValoresProcesso();
        break;
    case 'SelecionarProcesso':       
        $app->gerarValoresProcesso();        
        break;
    case 'SelecionarFornecedores':
        $app->gerarDadosFornecedor();
        break;
    case 'Selecionar':
        $app->selecionarAlterarAta();
        break;
}
$app->processarPrincipal();

echo $app->getTemplate()->show();

if(isset($_SESSION['orgaos_c'])) {
    unset($_SESSION['orgaos_c']);
}

if(isset($_SESSION['itens_esconder'])) {
    unset($_SESSION['itens_esconder']);
}