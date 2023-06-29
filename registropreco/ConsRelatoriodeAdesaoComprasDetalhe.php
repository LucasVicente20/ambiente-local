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
 * @category  Pitang Registro Preço
 *
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT: EMPREL-SAD-PORTAL-COMPRAS-REGISTRO-PRECO-BL-FUNC-20160601-1550
 */

#-------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     24/10/2018
# Objetivo: Tarefa Redmine 205787
#-------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     02/01/2019
# Objetivo: Tarefa Redmine 208259
#-------------------------------------------------------------------------
# Alterado: Caio Coutinho - Pitang Agile TI
# Data:     12/02/2019
# Objetivo: Tarefa Redmine 210926
#-------------------------------------------------------------------------
# Alterado: Osmar Celestino
# Data:     23/08/2022
# Objetivo: Cr 218188 && Cr 225681
# -----------------------------------------------------------------------------

// 220038--

if (! @require_once dirname(__FILE__) . '/../bootstrap.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}

Seguranca();
/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 *
 * @author jfsi
 */
class RegistroPreco_Dados_ConsRelatoriodeAdesaoComprasDetalhe extends Dados_Abstrata
{
    public function sqlMaterial($tipoPesquisa, $valor)
    {
        $sql = 'select mp.cmatepsequ from sfpc.tbmaterialportal mp';
        $sql .= ' where 1 = 1';
        if ($tipoPesquisa == '0') {
            $sql .= " and mp.cmatepsequ = $valor";
        }
        if ($tipoPesquisa == '1') {
            $sql .= " and mp.ematepcomp like '%$valor%'";
        }
        if ($tipoPesquisa == '2') {
            $sql .= " and mp.ematepcomp like '$valor%'";
        }

        return $sql;
    }

    public function sqlServico($tipoPesquisa, $valor)
    {
        $sql = 'select mp.cservpsequ from sfpc.tbservicoportal mp';
        $sql .= ' where 1 = 1';
        if ($tipoPesquisa == '0') {
            $sql .= " and mp.cservpsequ = $valor";
        }
        if ($tipoPesquisa == '1') {
            $sql .= " and mp.eservpdesc like '%$valor%'";
        }
        if ($tipoPesquisa == '2') {
            $sql .= " and mp.eservpdesc like '$valor%'";
        }

        return $sql;
    }

    public function sqlFornecedorPorCpfCnpj($cpfCnpj)
    {
        $sql = 'select aforcrsequ,
                nforcrrazs,
                eforcrlogr,
                aforcrnume,
                eforcrcomp,
                eforcrbair,
                nforcrcida,
                aforcrccpf, 
                aforcrccgc,               
                cforcresta from sfpc.tbfornecedorcredenciado fc';
        $sql .= " where fc.aforcrccgc ='$cpfCnpj'";
        $sql .= " or fc.aforcrccpf ='$cpfCnpj'";

        return $sql;
    }

    /**
     *
     * @param ArrayObject $dto
     * @return NULL
     */
    public function consultarExtratoAta()
    {
        $conexaoDb = Conexao();
        
            $orgao = ($dados['tipo_ata']== 'E')? 'arp.earpexorgg as orgaodesc': 'org.eorglidesc as orgaodesc';
            $sql =  "SELECT DISTINCT scc.csolcosequ, scc.ctpcomcodi, scc.esolcoobje, scc.asolcoanos, scc.csolcocodi, cc.ccenpocorg, scc.fsolcorpcp,".$orgao.", cc.ccenpounid, fc.nforcrrazs, scc.carpnosequ, scc.tsolcodata";
            $sql .= " FROM sfpc.tbsolicitacaocompra AS scc INNER JOIN sfpc.tbcentrocustoportal AS cc ON scc.ccenposequ = cc.ccenposequ ";
            $sql .= " INNER JOIN sfpc.tbataregistropreconova on scc.carpnosequ = sfpc.tbataregistropreconova.carpnosequ ";
            $sql .= " INNER JOIN sfpc.tbitemataregistropreconova IARPN ON sfpc.tbataregistropreconova.carpnosequ = IARPN.carpnosequ";              
            if($_REQUEST["tipoAta"] == "E"){
            $sql .= " INNER JOIN sfpc.tbataregistroprecoexterna as arp ON  sfpc.tbataregistropreconova.carpnosequ = arp.carpnosequ AND ";
            $sql .= " scc.carpnosequ = arp.carpnosequ INNER JOIN sfpc.tbfornecedorcredenciado as fc ON ";
            $sql .= " arp.aforcrsequ = fc.aforcrsequ ";  
            }else{
            $sql .= " INNER JOIN sfpc.tbataregistroprecointerna as arpi ON sfpc.tbataregistropreconova.carpnosequ = arpi.carpnosequ AND ";
            $sql .= " scc.carpnosequ = arpi.carpnosequ INNER JOIN sfpc.tbfornecedorcredenciado as fc ON ";
            $sql .= " arpi.aforcrsequ = fc.aforcrsequ ";
            $sql .= "INNER JOIN sfpc.tborgaolicitante org on arpi.corglicodi = org.corglicodi ";    
            }
            $sql .= " where scc.csitsocodi IN (3,4) AND scc.ctpcomcodi = 5 ";
            $sql .= " and scc.carpnosequ = ".$_REQUEST['ata'];
            $sql .= " ORDER BY scc.asolcoanos DESC, scc.csolcocodi ASC, cc.ccenpounid ASC, cc.ccenpocorg ASC";
            $resultado = executarSQL($conexaoDb, $sql);
            $dadosPesquisa = array();
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosPesquisa[] = (object) array(
                                'csolcosequ'=> $retorno->csolcosequ,
                                'ctpcomcodi'=> $retorno->ctpcomcodi,
                                'esolcoobje'=> $retorno->esolcoobje,
                                'asolcoanos'=> $retorno->asolcoanos,
                                'csolcocodi'=> $retorno->csolcocodi,
                                'ccenpocorg'=> $retorno->ccenpocorg,
                                'ccenpounid'=> $retorno->ccenpounid,
                                'nforcrrazs'=> $retorno->nforcrrazs,
                                'carpnosequ'=> $retorno->carpnosequ,
                                'orgaodesc' => $retorno->orgaodesc,
                                'fsolcorpcp' => $retorno->fsolcorpcp,
                                'tsolcodata' =>$retorno->tsolcodata,
                        );
            }
    
        return $dadosPesquisa;
    }
    

    /**
     */
    public function consultarOrgaosParticipantesAtas()
    {
        // $sql = 'select distinct ol.corglicodi, ol.eorglidesc from sfpc.tbparticipanteatarp parp';
        // $sql .= ' join sfpc.tborgaolicitante ol';
        // $sql .= ' on ol.corglicodi = parp.corglicodi';

        $sql = '';

        $sql = "SELECT DISTINCT	org.corglicodi, org.eorglidesc
				FROM			sfpc.tborgaolicitante org 
				INNER JOIN		sfpc.tbparticipanteatarp parp 
					ON	org.corglicodi = parp.corglicodi
				WHERE			org.forglisitu = 'A'
				ORDER BY		org.eorglidesc ASC";
        

        $resultado = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($resultado);

        return $resultado;
    }

    /**
     */
    public function consultarOrgaosGerenciador()
    {
        // $sql = '
        // SELECT distinct o.corglicodi, o.eorglidesc
        // FROM  sfpc.tborgaolicitante o
        // INNER JOIN sfpc.tbataregistroprecointerna a ON o.corglicodi = a.corglicodi
        //     ORDER BY o.eorglidesc ASC
        // ';

        $sql = '';

        

            $sql = "SELECT DISTINCT	org.corglicodi, org.eorglidesc
					FROM			sfpc.tborgaolicitante org
					WHERE			org.forglisitu = 'A'
					ORDER BY		org.eorglidesc ASC";
        
       

        $res = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($res);

        return $res;
    }
	// consultar órgão gestor da ata externa (loc 1234)
	public function consultarOrgaoGestorAtaExterna()
	{
		$sql = '';
		$sql = "SELECT DISTINCT	earpexorgg
				FROM			sfpc.tbataregistroprecoexterna
				WHERE			farpexsitu = 'A'
				ORDER BY		earpexorgg ASC";
		$res = ClaDatabasePostgresql::executarSQL($sql);
		
		ClaDatabasePostgresql::hasError($res);
		return $res;
	}

    /**
     */
    public function consultarComissaoLicitacao()
    {
        
        $sql = '';        

        $sql = "SELECT		* 
				FROM		SFPC.TBCOMISSAOLICITACAO
				WHERE 		FCOMLISTAT = 'A' 
				ORDER BY	ECOMLIDESC ASC ";
        
        $res = ClaDatabasePostgresql::executarSQL($sql);

        ClaDatabasePostgresql::hasError($res);

        return $res;
    }



    public function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {   
        $db = Conexao();

        $sql = "
            SELECT
                   ccp.ccenpocorg, ccp.ccenpounid, ccp.corglicodi
              FROM sfpc.tbcentrocustoportal ccp
             WHERE 1=1 ";

        if ($corglicodi != null || $corglicodi != "") {
          $sql .= " AND ccp.corglicodi = %d";
        }

        $sql = sprintf($sql, $corglicodi);
        
        $res = executarSQL($db, $sql);
        
        $itens = array();
        $item = null;
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
            $itens[] = $item;
        }
        $this->hasError($res);
        $db->disconnect();
        return $itens;
    }

    public function sqlConsultarGrupo($tipoGrupo)
    {
        $sql = 'SELECT DISTINCT CGRUMSCODI,EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO';
        $sql .= " WHERE FGRUMSTIPO = '$tipoGrupo'";
        $sql .= " AND FGRUMSSITU = 'A'";
        $sql .= ' ORDER BY EGRUMSDESC';
        

        return $sql;
    }

    public function sqlAtaPorchave($processo, $orgao, $ano, $chaveAta)
    {
        $sql  = "select a.carpincodn, a.earpinobje, a.aarpinanon, a.aarpinpzvg, a.tarpindini, a.cgrempcodi, a.cusupocodi, f.nforcrrazs, d.edoclinome,";
        $sql .= " a.corglicodi, a.carpnosequ, a.alicpoanop, s.csolcosequ, a.aarpinanon, carpnoseq1, ";

        $sql .= " f.nforcrrazs, f.aforcrccgc, f.aforcrccpf, f.eforcrlogr, ";
        $sql .= " f.aforcrnume, f.eforcrbair, f.nforcrcida, f.cforcresta, ";

        $sql .= " fa.nforcrrazs as razaoFornecedorAtual, fa.aforcrccgc as cgcFornecedorAtual, fa.aforcrccpf as cpfFornecedorAtual, fa.eforcrlogr as logradouroFornecedorAtual, ";
        $sql .= " fa.aforcrnume as numeroEnderecoFornecedorAtual, fa.eforcrbair as bairroFornecedorAtual, fa.nforcrcida as cidadeFornecedorAtual, fa.cforcresta as estadoFornecedorAtual ";

        $sql .= " from sfpc.tbataregistroprecointerna a";

        $sql .= " left outer join sfpc.tbsolicitacaolicitacaoportal s";
        $sql .= " on (s.clicpoproc = a.clicpoproc";
        $sql .= " and s.alicpoanop = a.alicpoanop";
        $sql .= " and s.ccomlicodi = a.ccomlicodi";
        $sql .= " and s.corglicodi = a.corglicodi)";

        $sql .= " left outer join sfpc.tbfornecedorcredenciado f";
        $sql .= " on f.aforcrsequ = a.aforcrsequ";

        $sql .= " left outer join sfpc.tbfornecedorcredenciado fa";
        $sql .= " on fa.aforcrsequ = (select afa.aforcrsequ from sfpc.tbataregistroprecointerna afa where afa.carpnosequ = a.carpnoseq1)";

        $sql .= " left outer join sfpc.tbdocumentolicitacao d";
        $sql .= " on d.clicpoproc = a.clicpoproc";
        $sql .= " and d.clicpoproc = " . $processo;
        $sql .= " and d.corglicodi = " . $orgao;
        $sql .= " and d.alicpoanop = " . $ano;

        $sql .= " where a.carpnosequ = " . $chaveAta;

        return $sql;
    }
}

/**
 * A camada de Negócio conterá o código que irá implementar todas as regras de negócio do sistema.
 *
 * Utiliza serviços da camada de Dados.
 *
 * @author jfsi
 */
class RegistroPreco_Negocio_ConsRelatoriodeAdesaoComprasDetalhe extends Negocio_Abstrata
{
    public function __construct()
    {
        $this->setDados(new RegistroPreco_Dados_ConsRelatoriodeAdesaoComprasDetalhe());
        return parent::getDados();
    }

    public function consultarServico($tipoPesquisa, $valor)
    {
       

        $db = Conexao();
         $sql = $this->getDados()->sqlServico($tipoPesquisa, $valor);
       
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);

        
        $db->disconnect();

        return $retorno;

       
    }

    public function consultarMaterial($tipoPesquisa, $valor)
    {        
        $db = Conexao();
        $sql = $this->getDados()->sqlMaterial($tipoPesquisa, $valor);
       
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
        $db->disconnect();

        return $retorno;
    }

    public function consultarFornecedorPorCpfCnpj($cpfCnpj)
    {
        
        $db = Conexao();
        $sql = $this->getDados()->sqlFornecedorPorCpfCnpj($cpfCnpj);
       
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
        $db->disconnect();

        return $retorno;

    }

    public function consultarOrgaosParticipantesAtas()
    {
        return $this->getDados()->consultarOrgaosParticipantesAtas();
    }

    public function consultarOrgaosGerenciador()
    {
        return $this->getDados()->consultarOrgaosGerenciador();
    }
	
    public function consultarComissaoLicitacao()
    {
        return $this->getDados()->consultarComissaoLicitacao();
    }

    public function consultarOrgaoGestorAtaExterna()
    {
        return $this->getDados()->consultarOrgaoGestorAtaExterna();   
    }

    public function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {
        return $this->getDados()->consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi);
    }

    public function consultarGrupo($tipo)
    {       
        $db = Conexao();
        $sql = $this->getDados()->sqlConsultarGrupo($tipo);
		
        $res = executarSQL($db, $sql);
        $itens = array();
        $item = null;
        $itemTipo = new stdClass();        
        while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {            
            $itens[] = $item;            
        }
        $db->disconnect();
        return $itens;
    }

    /**
     *
     * @param ArrayObject $dto
     */
    public function consultarExtratoAta(ArrayObject $dto)
    {
        return $this->getDados()->consultarExtratoAta();
    }

    public function consultarAtaPorChave($processo, $orgao, $ano, $numeroAta)
    {
        $db = Conexao();
        $sql = $this->getDados()->sqlAtaPorchave($processo, $orgao, $ano, $numeroAta);
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($ata, DB_FETCHMODE_OBJECT);
        return $ata;
    }
}

/**
 * A camada de Adaptação e Transformação conterá o código que tratará a lógica de apresentação dos resultados
 * das requisições dos usuários e a troca de dados com sistemas externos.
 *
 * Utiliza serviços da camada de Negócio.
 *
 * @author jfsi
 */
class RegistroPreco_Adaptacao_ConsRelatoriodeAdesaoComprasDetalhe extends Adaptacao_Abstrata
{
    
    /**
     *
     * {@inheritdoc}
     *
     * @see Adaptacao_Abstrata::getNegocio()
     */
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_ConsRelatoriodeAdesaoComprasDetalhe());
        return parent::getNegocio();
    }

    public function informarMaterial()
    {
        $tipoPesquisaMaterial = $_POST['pesquisaMaterial'];
        if ($tipoPesquisaMaterial == 0) {
            $valor = intval($_POST['material']);
        } else {
            $valor = strtoupper($_POST['material']);
        }

        $material = $this->getNegocio()->consultarMaterial($tipoPesquisaMaterial, $valor);
        $_SESSION['COD_MATERIAL_HIDDEN'] = $material->cmatepsequ;
        //$this->getTemplate()->COD_MATERIAL = $_SESSION['COD_MATERIAL_HIDDEN'];
        return;
    }

    public function informarServico()
    {
        $tipoPesquisaServico = $_POST['pesquisaServico'];
        if ($tipoPesquisaServico == 0) {
            $valor = intval($_POST['servico']);
        } else {
            $valor = strtoupper($_POST['servico']);
        }
        $servico = $this->getNegocio()->consultarServico($tipoPesquisaServico, $valor);
        $_SESSION['COD_SERVICO_HIDDEN'] = $servico->cservpsequ;
        //$this->getTemplate()->COD_SERVICO = $_SESSION['COD_SERVICO_HIDDEN'];
        return;
    }

    public function informarFornecedor()
    {
        $cpfCNPJ = eregi_replace('([^0-9])', '', $_POST['fornecedor']);
        $fornecedor = $this->getNegocio()->consultarFornecedorPorCpfCnpj($cpfCNPJ);

        $_SESSION['FORNECEDOR_COMPLETO'] = $fornecedor;
        $_SESSION['COD_FORNECEDOR_HIDDEN'] = $fornecedor->aforcrsequ;
        //$this->getTemplate()->COD_FORNECEDOR = $_SESSION['COD_FORNECEDOR_HIDDEN'];
        return $fornecedor;
    }

    public function consultarOrgaoParticipantes()
    {
        return $this->getNegocio()->consultarOrgaosParticipantesAtas();
    }

    public function consultarOrgaoGerenciado()
    {
        return $this->getNegocio()->consultarOrgaosGerenciador();
    }
	
	public function consultarOrgaoGestorExterno()
    {
        return $this->getNegocio()->consultarOrgaoGestorAtaExterna();
    }

    public function consultarComissaoLicitacao()
    {
        return $this->getNegocio()->consultarComissaoLicitacao();
    }

    public function consultarOrgaoGestorAtaExterna()
    {
        return $this->getNegocio()->consultarOrgaoGestorAtaExterna();   
    }

    public function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {
        return $this->getNegocio()->consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi);
    }


    public function consultarExtratoAta()
    {
        return $this->getNegocio()->consultarExtratoAta(new ArrayObject($_POST));
    }
}

/**
 * A camada de Interface Gráfica com o Usuário é a camada que conterá o código que irá implementar a interação
 * do sistema com os usuários (telas, relatórios e troca de dados).
 *
 * Utiliza serviços da camada de Adaptação e Transformação.
 *
 * @author jfsi
 */
class RegistroPreco_UI_ConsRelatoriodeAdesaoComprasDetalhe extends UI_Abstrata
{
    private function carregaCheckbox()
    {
        $_SESSION['tipoAta'] = ($_REQUEST['tipoAta']) ? $_REQUEST['tipoAta']: $_SESSION['tipoAta'];
        $_SESSION['cpfcnpj'] = ($_REQUEST['cpfcnpj'])?$_REQUEST['cpfcnpj']: $_SESSION['cpfcnpj'];
        $_SESSION['identificadorGrupo'] = ($_REQUEST['identificadorGrupo'])?$_REQUEST['identificadorGrupo']:$_SESSION['identificadorGrupo'];
        $_SESSION['inativos'] = ($_REQUEST['inativos'])?$_REQUEST['inativos']: $_SESSION['inativos'];
        $_SESSION['vigentes'] = ($_REQUEST['vigentes']) ? $_REQUEST['vigentes']:$_SESSION['vigentes'];
        $_SESSION['situacao_ata'] = ($_REQUEST['situacao_ata'])?$_REQUEST['situacao_ata']:$_SESSION['situacao_ata'];




        $_SESSION['tipoAta'] = ($_REQUEST['tipoAta']) ? $_REQUEST['tipoAta']:'';
        $_SESSION['cpfcnpj'] = ($_REQUEST['cpfcnpj'])?$_REQUEST['cpfcnpj']:'';
        $_SESSION['identificadorGrupo'] = ($_REQUEST['identificadorGrupo'])?$_REQUEST['identificadorGrupo']:'';
        $_SESSION['inativos'] = ($_REQUEST['inativos'])?$_REQUEST['inativos']:'';
        $_SESSION['vigentes'] = ($_REQUEST['vigentes']) ? $_REQUEST['vigentes']:'';
        $_SESSION['situacao_ata'] = ($_REQUEST['situacao_ata'])?$_REQUEST['situacao_ata']:'';
        
        
    }

    private function carregaSelect()
    {
        $_SESSION['pesquisaMaterial'] = ($_REQUEST['pesquisaMaterial']) ? $_REQUEST['pesquisaMaterial'] : $_SESSION['pesquisaMaterial'];
        $_SESSION['pesquisaServico'] = ($_REQUEST['pesquisaServico'])? $_REQUEST['pesquisaServico'] : $_SESSION['pesquisaServico'];
        $_SESSION['identificadorGrupo'] = ($_REQUEST['identificadorGrupo']) ?$_REQUEST['identificadorGrupo'] :$_SESSION['identificadorGrupo'];
        $_SESSION['identificadorGrupo'] = ($_REQUEST['identificadorGrupo']) ? $_REQUEST['identificadorGrupo'] :$_SESSION['identificadorGrupo'];
        $_SESSION['inativos'] = ($_REQUEST['inativos']) ? $_REQUEST['inativos'] : $_SESSION['inativos'];
        $_SESSION['tipoAta'] = ($_REQUEST['tipoAta']) ? $_REQUEST['tipoAta'] : $_SESSION['tipoAta'];
        $_SESSION['vigentes'] = ($_REQUEST['tipovigentesAta']) ? $_REQUEST['vigentes'] : $_SESSION['vigentes'];
        $_SESSION['situacao_ata'] = ($_REQUEST['situacao_ata']) ? $_REQUEST['situacao_ata'] : $_SESSION['situacao_ata'];
    

 

        $_SESSION['pesquisaMaterial'] = ($_REQUEST['pesquisaMaterial']) ? $_REQUEST['pesquisaMaterial'] : '';
        $_SESSION['pesquisaServico'] = ($_REQUEST['pesquisaServico'])? $_REQUEST['pesquisaServico'] : '';
        $_SESSION['identificadorGrupo'] = ($_REQUEST['identificadorGrupo']) ?$_REQUEST['identificadorGrupo'] :'' ;
        $_SESSION['identificadorGrupo'] = ($_REQUEST['identificadorGrupo']) ? $_REQUEST['identificadorGrupo'] :'' ;
        $_SESSION['inativos'] = ($_REQUEST['inativos']) ? $_REQUEST['inativos'] :'' ;
        $_SESSION['tipoAta'] = ($_REQUEST['tipoAta']) ? $_REQUEST['tipoAta'] : '';
        $_SESSION['vigentes'] = ($_REQUEST['tipovigentesAta']) ? $_REQUEST['vigentes'] : '';
        $_SESSION['situacao_ata'] = ($_REQUEST['situacao_ata']) ? $_REQUEST['situacao_ata'] : '';
        
    }

    /**
     */
    private function recuperarDadosTela()
    {

        $_SESSION['processo'] = ($_REQUEST['processo'])? $_REQUEST['processo'] : $_SESSION['processo'];
        $_SESSION['codigoAtaE'] = ($_REQUEST['codigoAtaE']) ?$_REQUEST['codigoAtaE'] : $_SESSION['codigoAtaE'];
        $_SESSION['codigoAtaE'] = ($_REQUEST['codigoAtaE']) ? $_REQUEST['codigoAtaE'] :$_SESSION['codigoAtaE'] ;
        $_SESSION['anoAtaE'] = ($_REQUEST['anoAtaE']) ? $_REQUEST['anoAtaE'] : $_SESSION['anoAtaE'] ;
        $_SESSION['linkAta'] = ($_REQUEST['linkAta']) ? $_REQUEST['linkAta'] : $_SESSION['linkAta'];
        $_SESSION['ano'] = ($_REQUEST['ano']) ? $_REQUEST['ano'] : $_SESSION['ano'];
        $_SESSION['fornecedor'] = ($_REQUEST['fornecedor']) ? $_REQUEST['fornecedor'] :  $_SESSION['fornecedor'];
        $_SESSION['material'] = ($_REQUEST['material']) ? $_REQUEST['material'] : $_SESSION['material'];
        $_SESSION['servico'] = ($_REQUEST['servico']) ? $_REQUEST['servico'] : $_SESSION['servico'];
        $_SESSION['objeto'] = ($_REQUEST['objeto']) ? $_REQUEST['objeto'] : $_SESSION['objeto']; 
        $_SESSION['fornecedorRaz'] = ($_POST['fornecedorRaz']) ? $_POST['fornecedorRaz'] : $_SESSION['fornecedorRaz']; 

         
        $_SESSION['processo'] = ($_REQUEST['processo'])? $_REQUEST['processo'] : '';
        $_SESSION['codigoAtaE'] = ($_REQUEST['codigoAtaE']) ?$_REQUEST['codigoAtaE'] :'' ;
        $_SESSION['codigoAtaE'] = ($_REQUEST['codigoAtaE']) ? $_REQUEST['codigoAtaE'] :'' ;
        $_SESSION['anoAtaE'] = ($_REQUEST['anoAtaE']) ? $_REQUEST['anoAtaE'] :'' ;
        $_SESSION['linkAta'] = ($_REQUEST['linkAta']) ? $_REQUEST['linkAta'] : '';
        $_SESSION['ano'] = ($_REQUEST['ano']) ? $_REQUEST['ano'] : '';
        $_SESSION['fornecedor'] = ($_REQUEST['fornecedor']) ? $_REQUEST['fornecedor'] : '';
        $_SESSION['material'] = ($_REQUEST['material']) ? $_REQUEST['material'] : '';
        $_SESSION['servico'] = ($_REQUEST['servico']) ? $_REQUEST['servico'] : '';
        $_SESSION['objeto'] = ($_REQUEST['objeto']) ? $_REQUEST['objeto'] : ''; 
        $_SESSION['fornecedorRaz'] = ($_POST['fornecedorRaz']) ? $_POST['fornecedorRaz'] : ''; 
        

        $this->carregaCheckbox();
        $this->carregaSelect();
    }

    /**
     * [plotarBlocoOrgao description].
     *
     * @param [type] $orgaos
     *            [description]
     *
     * @return [type] [description]
     */


    /**
     * [plotarBlocoOrgao description].
     *
     * @param [type] $orgaos
     *            [description]
     *
     * @return [type] [description]
     */
    private function plotarBlocoOrgaoGerenciador($orgaos)
    {
        $orgaoNumeracao = filter_input(INPUT_POST, 'orgaoGerenciador', FILTER_VALIDATE_INT);
        if ($orgaos == null) {
            return;
        }

        foreach ($orgaos as $orgao) {
            $this->getTemplate()->ORGAO_VALUE_GERENCIADOR = $orgao->corglicodi;

            $this->getTemplate()->ORGAO_TEXT_GERENCIADOR = $orgao->eorglidesc;

            // Vendo se a opção atual deve ter o atributo "selected"
            if ($orgaoNumeracao == $orgao->corglicodi) {
                $this->getTemplate()->ORGAO_SELECTED_GERENCIADOR = 'selected';
            } else {
                $this->getTemplate()->clear('ORGAO_SELECTED_GERENCIADOR');
            }

            $this->getTemplate()->block('BLOCO_ORGAO_GERENCIADOR');
        }
    }
	
	/**
     * [plotarBlocoOrgao description].
     *
     * @param [type] $orgaos
     *            [description]
     *
     * @return [type] [description]
     */
   
	


    /**
     * [plotarBlocoOrgao description].
     *
     * @param [type] $comissoes
     *            [description]
     *
     * @return [type] [description]
     */
    private function plotarBlocoComissaoLicitacao($comissoes)
    {
        $comissaoNumeracao = filter_input(INPUT_POST, 'orgaoComissaoLicitacao', FILTER_VALIDATE_INT);
        if ($comissoes == null) {
            return;
        }

        foreach ($comissoes as $comissao) {
            $this->getTemplate()->COMISSAO_VALUE_GERENCIADOR = $comissao->ccomlicodi;

            $this->getTemplate()->COMISSAO_TEXT_GERENCIADOR = $comissao->ecomlidesc;

            // Vendo se a opção atual deve ter o atributo "selected"
            if ($comissaoNumeracao == $comissao->ccomlicodi) {
                $this->getTemplate()->COMISSAO_SELECTED_GERENCIADOR = 'selected';
            } else {
                $this->getTemplate()->clear('COMISSAO_SELECTED_GERENCIADOR');
            }

            $this->getTemplate()->block('BLOCO_COMISSAO_GERENCIADOR');
        }
    }

    private function plotarBlocoResultadoAta()
    {
     

        
        // Organizar por orgão

        $atas = $this->getAdaptacao()->consultarExtratoAta();

                foreach ($atas as $dados) {  
                    $data = substr($dados->tsolcodata,0,2).'/'.substr($dados->tsolcodata,5,2).'/'.substr($dados->tsolcodata,0,4);                
                        $codigoDinamico = str_pad($dados->csolcocodi,4,'0',STR_PAD_LEFT);
                        $codigoDinamicoUni = str_pad($dados->ccenpounid,2,'0',STR_PAD_LEFT);
                        $tipoAta = (empty($POST['tipoAta'])) ? "I" : $POST['tipoAta']; 
                        $ataDesc = ($_POST['tipoAta'] == 'I') ? "INTERNA" : "EXTERNA";
                        $sarpDesc = ($_POST['tipoSarp'] == 'P') ? "PARTICIPANTE" : "CARONA";
                        $this->getTemplate()->SCC  = $dados->ccenpocorg.$codigoDinamicoUni.'.'.$codigoDinamico.'.'.$dados->asolcoanos;
                        $this->getTemplate()->VALOR_OBJETO    = $dados->esolcoobje;
                        $this->getTemplate()->VALOR_ORGAO    = $dados->orgaodesc;
                        $this->getTemplate()->DATA_SCC    = $data;
                        $this->getTemplate()->FORNECEDOR    = $dados->nforcrrazs;
                        $this->getTemplate()->TIPO_ATA_DESC  = $ataDesc;
                        $this->getTemplate()->TIPO_SOLICITACAO  = $sarpDesc;
                        //OSMAR
                    
                $this->getTemplate()->block('BLOCO_RESULTADO_ATA');
            }
    }

    /**
     * [plotarBlocoOrgao description].
     *
     * @param [type] $orgaos
     *            [description]
     *
     * @return [type] [description]
     */
 

    /**
     * [__construct description].
     */
    public function __construct()
    {        
        $template = new TemplatePaginaPadrao("templates/ConsRelatoriodeAdesaoComprasDetalhe.html", "Registro de Preço > Extrato Atas");
        $template->NOME_PROGRAMA = 'ConsRelatoriodeAdesaoComprasDetalhe';
        $this->setTemplate($template);
    }


    /**
     *
     * {@inheritdoc}
     *
     * @see UI_Abstrata::getAdaptacao()
     */
    public function getAdaptacao()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_ConsRelatoriodeAdesaoComprasDetalhe());
        return parent::getAdaptacao();
    }

    public function processMaterial()
    {
        $this->getAdaptacao()->informarMaterial();
    }

    public function processServico()
    {
        $this->getAdaptacao()->informarServico();
    }

    public function processFornecedor()
    {
        $fornecedorSelecionado = $this->getAdaptacao()->informarFornecedor();
        $this->getTemplate()->VALORES_AUXILIARES_FORNECEDOR = $this->montarforn($_SESSION['FORNECEDOR_COMPLETO']);
        
    }

    public function montarforn($fornecedor){
        if($fornecedor == '' || $fornecedor == null){return '';}
       return $fornecedor->nforcrrazs . ' <br/>' .$fornecedor->eforcrlogr . ', - ' . $fornecedor->eforcrbair . ' - ' . $fornecedor->nforcrcida . '/' . $fornecedor->cforcresta;
    }

    public function proccessPrincipal()
    {
        if(count($_REQUEST) <=1){
            $_SESSION['COD_MATERIAL_HIDDEN'] = '';
            $_SESSION['COD_SERVICO_HIDDEN'] = '';
            $_SESSION['COD_FORNECEDOR_HIDDEN'] = '';
            $_SESSION['FORNECEDOR_COMPLETO'] = '';
            $_SESSION['grupos_plotados'] = '';
        }
        
        $this->plotarBlocoResultadoAta();    
    }

   

    public function processVoltar()
    {
        header('Location: ' . 'ConsRelatoriodeAdesaoComprasDetalhe.php');
        exit();
       
    }

    public function consultarExtratoAta()
    {
        $extratoAtas = $this->getAdaptacao()->consultarExtratoAta();
        $this->recuperarDadosTela();
        $this->plotarBlocoOrgaoGerenciador($this->getAdaptacao()
            ->consultarOrgaoGerenciado());
        $this->plotarBlocoComissaoLicitacao($this->getAdaptacao()
            ->consultarComissaoLicitacao());
        $this->getTemplate()->COD_MATERIAL = $_SESSION['COD_MATERIAL_HIDDEN'];
        $this->getTemplate()->COD_SERVICO = $_SESSION['COD_SERVICO_HIDDEN'];
        $this->getTemplate()->COD_FORNECEDOR = $_SESSION['COD_FORNECEDOR_HIDDEN'];

        $itensInativos = true;
        if(!empty($_POST['inativos']) && $_POST['inativos'] == 'I') {
            $itensInativos = false;
        }
        
   
        if (empty($extratoAtas)) {
            $this->getTemplate()->block('bloco_sem_resultado_ata');
        }else{
            $this->plotarBlocoResultadoAta($extratoAtas, $itensInativos);
        }

    }

    public function processExtratoAta()
    {
        $ataSelecionada     = $_POST['tipoSelecionado'];
        $linkAtaSelecionada = $_POST['linkAta'];

        if (isset($ataSelecionada) && !empty($ataSelecionada)) {

            header('Location: ' . $ataSelecionada[0]);
            exit();
            
        } else {
            $_SESSION['mensagemFeedback'][] = 'Órgão Gestor não selecionado!';
            $retorno = false;
        }
    }
}

/**
 * [$app description].
 *
 * @var Negocio
 */
$app = new RegistroPreco_UI_ConsRelatoriodeAdesaoComprasDetalhe();

$acao = isset($_POST['Botao']) ? filter_var($_POST['Botao'], FILTER_SANITIZE_STRING) : null;

switch ($acao) {
    case 'Voltar':
        $app->processVoltar();
        break;
    case 'Pesquisar':
        $app->consultarExtratoAta();
        break;
    case 'Grupo':
        $app->processGrupos();
        $app->proccessPrincipal();
        break;
    case 'PesquisaFornecedor':
        $app->processFornecedor();
        $app->proccessPrincipal();
        $app->consultarExtratoAta();
        break;
    case 'PesquisaMaterial':
        $app->processMaterial();
        $app->proccessPrincipal();
        break;
    case 'PesquisaServico':
        $app->processServico();
        $app->proccessPrincipal();
        break;
    case 'Extrato':
        $app->processExtratoAta();
        break;
    default:
        $app->proccessPrincipal();
        break;
}

echo $app->getTemplate()->show();
