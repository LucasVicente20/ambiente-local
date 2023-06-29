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
# Alterado: Lucas Vicente
# Data:     29/08/2022
# Objetivo: Cr 219490
# -----------------------------------------------------------------------------
# Alterado: Osmar Celestino
# Data:     29/05/2023
# Objetivo: Cr 283794
# -----------------------------------------------------------------------------

// 220038--
require_once("../funcoes.php");
if (! @require_once dirname(__FILE__) . '/../bootstrap.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}

Seguranca();
/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 *
 * @author jfsi
 */


class RegistroPreco_Dados_ConsAtaRegistroPrecoExtratoAta extends Dados_Abstrata
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
    public function consultarExtratoAta(ArrayObject $dto)
    {
        //osmar local onde a consulta é realizada
        // die('aqui');
        
        $materialInformado  = strtoupper2($dto['material']);
        $servicoInformado   = strtoupper2($dto['servico']);
        $comissaoLicitacao  = $dto['orgaoComissaoLicitacao'];
        $objeto             = $dto['objeto'];
        $objetoComAcento    = $objeto;
        $materialInformadoComAcento = $materialInformado;
        $servicoInformadoComAcento = $servicoInformado;

        $materialInformado = RetiraAcentos($materialInformado);
        $servicoInformado = RetiraAcentos($servicoInformado); 
        
        $objeto = preg_replace('/[áàãâä]/ui', 'A', $objeto);
        $objeto = preg_replace('/[éèêë]/ui', 'E', $objeto);
        $objeto = preg_replace('/[íìîï]/ui', 'I', $objeto);
        $objeto = preg_replace('/[óòõôö]/ui', 'O', $objeto);
        $objeto = preg_replace('/[úùûü]/ui', 'U', $objeto);
        $objeto = preg_replace('/[Ç]/ui', 'C', $objeto);
        $objeto = preg_replace('/[\x00-\x1F\x7F-\xFF]/', ' ', $objeto);
          
        $identificadorGrupo = $dto['identificadorGrupo'];
        
        $field_objeto = $dto['tipoAta'] == 'I' ? 'arpi.earpinobje' : 'arpi.earpexobje';
        $vigencia     = $dto['tipoAta'] == 'I' ? 'arpi.aarpinpzvg' : 'arpi.aarpexpzvg';
        $situacao     = $dto['tipoAta'] == 'I' ? 'arpi.farpinsitu' : 'arpi.farpexsitu';

        $campoVigencia = $dto['tipoAta'] == 'I' ? 'arpi.tarpindini' : 'arpi.tarpexdini';

        $sql = "SELECT distinct ($campoVigencia + ($vigencia || ' month')::INTERVAL) as vigencia, arpn.carpnosequ, $situacao,";


        if ($dto['tipoAta'] == 'I') {
            $sql .= 'arpi.clicpoproc,arpi.alicpoanop,arpi.ccomlicodi,arpi.earpinobje,arpi.corglicodi,cl.ecomlidesc, ol.eorglidesc, arpi.cgrempcodi, arpi.cusupocodi, arpi.aarpinanon, arpi.carpincodn, ';
        } else if ($dto['tipoAta'] == 'E') {
            $sql .= 'arpi.carpexcodn, arpi.earpexorgg, arpi.earpexproc, arpi.aarpexanon, arpi.earpexobje, arpi.tarpexdini, ';
            $sql .= 'fc1.nforcrrazs as nforcrrazs1, fc1.aforcrccgc as aforcrccgc1, fc1.aforcrccpf as aforcrccpf1, ';
        }
        

        $sql .= 'arpn.carpnotiat, fc.nforcrrazs, fc.aforcrccgc, fc.aforcrccpf FROM sfpc.tbataregistropreconova arpn';

        if ($dto['tipoAta'] == 'I') {
            $sql .= ' inner JOIN sfpc.tbataregistroprecointerna arpi';
            $sql .= ' on arpi.carpnosequ = arpn.carpnosequ';
        } else if ($dto['tipoAta'] == 'E') {
            $sql .= ' inner JOIN sfpc.tbataregistroprecoexterna arpi';
            $sql .= ' on arpi.carpnosequ = arpn.carpnosequ';
                   
        }

        $sql .= ' LEFT JOIN sfpc.tbparticipanteatarp parp';
        $sql .= ' on parp.carpnosequ = arpn.carpnosequ';

        $sql .= ' LEFT JOIN sfpc.tbfornecedorcredenciado fc';
        $sql .= ' on fc.aforcrsequ = arpi.aforcrsequ';        
        
        $sql .= ' LEFT JOIN sfpc.tbitemataregistropreconova iarpn';
        $sql .= ' on iarpn.carpnosequ = arpn.carpnosequ';

        if ($identificadorGrupo == 'M' || !empty($dto['material'])) {
            $sql .= ' JOIN sfpc.tbmaterialportal mp';
            $sql .= ' on mp.cmatepsequ = iarpn.cmatepsequ';
            $sql .= ' JOIN sfpc.tbsubclassematerial scm';
            $sql .= ' on scm.csubclsequ = mp.csubclsequ';
            
            if(!empty($_REQUEST['grupo']) || !empty($dto['grupo'])) {
                $grupo = !empty($_REQUEST['grupo']) ? $_REQUEST['grupo'] : $dto['grupo'];
                $sql .= ' and scm.cgrumscodi = '. $grupo;
            }
        }

        if ($identificadorGrupo == 'S' || !empty($dto['servico']) ) {
            $sql .= ' JOIN sfpc.tbservicoportal sp';
            $sql .= ' on sp.cservpsequ = iarpn.cservpsequ';

            if(!empty($_REQUEST['grupo']) || !empty($dto['grupo'])) {
                $grupo = !empty($_REQUEST['grupo']) ? $_REQUEST['grupo'] : $dto['grupo'];
                $sql .= ' and sp.cgrumscodi = '. $grupo;
            }            
        }
        
        if (!empty($dto['numeroAta']) && $dto['tipoAta'] == 'I') {
            $sql .= ' JOIN sfpc.tbcentrocustoportal centroCusto ';
            $sql .= ' ON centroCusto.corglicodi = arpi.corglicodi ';
        }
        

        // $sql .= ' join sfpc.tbservicoportal sp';
        // $sql .= ' on sp.cservpsequ = iarpn.cservpsequ';
       

       //if ($identificadorGrupo == 'M' || $identificadorGrupo == 'S') {
        //    $sql .= ' and cms.cgrumscodi = ' . $_REQUEST['grupo'];
        //}

        //if ($identificadorGrupo == 'M') {
        //    $sql .= ' left join sfpc.tbmaterialportal mp';
        //    $sql .= ' on mp.cmatepsequ = iarpn.cmatepsequ';
        //}

        //     if ($identificadorGrupo == 'M') {
        //         $sql .= ' join sfpc.tbsubclassematerial scm';
        //         $sql .= ' on scm.csubclsequ = mp.csubclsequ';
        //         $sql .= ' JOIN sfpc.tbclassematerialservico cms';
        //         $sql .= ' ON (cms.cclamscodi = scm.cclamscodi)';
        //     }
        // }
        // if (! empty($servicoInformado)) {
        //     $sql .= ' join sfpc.tbservicoportal sp';
        //     $sql .= ' on sp.cservpsequ = iarpn.cservpsequ';
        //     if ($identificadorGrupo == 'S') {
        //         $sql .= ' join sfpc.tbsubclassematerial scm';
        //         $sql .= ' on scm.csubclsequ = mp.csubclsequ';
        //     }
        // }

        if ($_REQUEST['tipoAta'] == 'I' || $dto['tipoAta'] == 'I') {
            $sql .= ' JOIN sfpc.tbcomissaolicitacao cl';
            $sql .= ' on cl.ccomlicodi = arpi.ccomlicodi';
            $sql .= ' JOIN sfpc.tborgaolicitante ol';
            $sql .= ' on ol.corglicodi = arpi.corglicodi';
        } elseif($_REQUEST['tipoAta'] == 'E' || $dto['tipoAta'] == 'E') {
            $sql .= ' LEFT JOIN sfpc.tbfornecedorcredenciado fc1';
            $sql .= ' on fc1.aforcrsequ = arpi.aforcrsequ';
        }
        
        $sql .= ' where 1 = 1';
        
        if (!empty($dto['tipoAta'])) {
            $sql .= " and arpn.carpnotiat = '" . $dto['tipoAta'] . "'";
        }

        if($_POST['fornecedorRaz']){
            $sql .= " AND (fc.nforcrrazs ILIKE '".$_POST['fornecedorRaz']."%' OR fc.nforcrrazs ILIKE '%".$_POST['fornecedorRaz']."%')";
        }  

        if (! empty($dto['numeroAta'])) {            
            if ($dto['tipoAta'] == 'I') {

                $ccenpocorg = ltrim(substr($dto['numeroAta'], 0,2), "0");
                $ccenpounid = ltrim(substr($dto['numeroAta'], 2,2), "0");
                $carpincodn = ltrim(substr($dto['numeroAta'], 5,4), "0");
                $aarpinanon = substr($dto['numeroAta'], 10,4);

                $sql .= ' and arpi.corglicodi = ( ';
            
                $sql .= '     select centroCusto.corglicodi from sfpc.tbcentrocustoportal centroCusto where ';
                $sql .= '     centroCusto.ccenpocorg =  ' . $ccenpocorg;
                $sql .= '     and centroCusto.ccenpounid =  ' . $ccenpounid;;
                $sql .= '     limit 1           ';  
                $sql .= ' ) ';

                $sql .= ' and arpi.carpincodn =' . $carpincodn;
                $sql .= ' and arpi.aarpinanon =' . $aarpinanon;
            }
        }
            
            
        if ($dto['tipoAta'] == 'E') {                
            if(!empty($dto['codigoAtaE'])) {
                $carpexcodn = $dto['codigoAtaE'];
                $sql .= ' and arpi.carpexcodn = ' . $carpexcodn;                    
            }

            if(!empty($dto['anoAtaE'])) {
                $aarpexanon = $dto['anoAtaE'];
                $sql .= ' and arpi.aarpexanon = ' . $aarpexanon;
            }

            if (! empty($dto['processo_ano'])) {
                $sql .= " and arpi.earpexproc = '" . $dto['processo_ano']."'";
            }        
        }            

        if ($dto['tipoAta'] == 'I') {
            if(!empty($comissaoLicitacao)){
                $sql .= '  and  arpi.ccomlicodi =' . $comissaoLicitacao;
            }
            if (! empty($dto['processo'])) {
                $sql .= ' and arpi.clicpoproc =' . $dto['processo'];
            }

            if (! empty($dto['ano'])) {
                $sql .= ' and arpi.alicpoanop =' . $dto['ano'];
            }

            if (! empty($dto['orgaoParticipante'])) {
                $sql .= ' and parp.corglicodi =' . $dto['orgaoParticipante'];
            }
        }                   
        
        if (! empty($dto['orgaoGerenciador'])) {            
            $sql .= ' and arpi.corglicodi =' . $dto['orgaoGerenciador'];
        }        

        if (! empty($dto['fornecedorCod'])) {
            $sql .= ' and arpi.aforcrsequ = ' . $dto['fornecedorCod'];
        }

        // itens inativos
        /*if(empty($dto['inativos']) || $dto['inativos'] != 'I') {
            if($dto['tipoAta'] == 'I') {
                $sql .= " and arpi.farpinsitu = 'A'";
            } else {
                $sql .= " and arpi.farpexsitu = 'A'";
            }

            $sql .= " and iarpn.fitarpsitu = 'A'";
        }*/

        // Atas ativas
        if(!empty($dto['situacao_ata']) && $dto['situacao_ata'] == 'A') {
            $sql .= " and ".$situacao." = 'A'";
        }

        if(!empty($objeto)){
            $sql .= "  and ".$field_objeto." like '%".strtoupper2($objeto)."%' ";
        }      

        $encoding = 'UTF-8';

        if (! empty($materialInformado)) {                        
            $pesquisaMaterial = isset($_REQUEST['pesquisaMaterial']) ? $_REQUEST['pesquisaMaterial'] : $dto['pesquisaMaterial'];
            switch ($pesquisaMaterial) {
                case '0':
                    if(!is_numeric($materialInformado)){
                        return null;
                    }

                    $sql .= ' and iarpn.cmatepsequ =' . $materialInformado;
                    break;
                case '1':
                    //descricao Contendo
                    $sql .= " AND (mp.ematepdesc LIKE '%".$materialInformado."%' OR mp.ematepdesc LIKE '%".$materialInformadoComAcento."%')";        
                    break;
                
                case '2':
                    //descricao iniciada por
                    $sql .= " AND (mp.ematepdesc LIKE '".$materialInformado."%' OR mp.ematepdesc LIKE '".$materialInformadoComAcento."%')";
                                      
                    break;
                
                default:
                    # code...
                    break;
            }
        }

        if (! empty($servicoInformado)) {        
            $pesquisaServico = isset($_REQUEST['pesquisaServico']) ? $_REQUEST['pesquisaServico'] : $dto['pesquisaServico'];
            switch ($pesquisaServico) {
                case '0':
                    if(!is_numeric($servicoInformado)){
                        return null;
                    }                    
                    $sql .= ' and iarpn.cservpsequ =' . $servicoInformado;
                    break;
                case '1':
                    //descricao Contendo
                    $sql .= " AND (sp.eservpdesc LIKE '%".$servicoInformado."%' OR sp.eservpdesc LIKE '%".$servicoInformadoComAcento."%')";
                    break;                
                case '2':
                    //descricao iniciada por
                    $sql .= " AND (sp.eservpdesc LIKE '".$servicoInformado."%' OR sp.eservpdesc LIKE '".$servicoInformadoComAcento."%')";                   
                    break;                
                default:
                    # code...
                    break;
            }
        }
    
        //if ($identificadorGrupo == 'M' || $identificadorGrupo == 'S') {
        //    $sql .= ' and cms.cgrumscodi = ' . $_REQUEST['grupo'];
        //}       
       
        if (!isset($dto['inativos']) && ! empty($materialInformado)) {
            $sql .= " and mp.cmatepsitu = 'A'";
        }

        if (!isset($dto['inativos']) && ! empty($servicoInformado)) {
            $sql .= " and sp.cservpsitu = 'A'";
        }

        if (isset($dto['vigentes']) && $dto['vigentes'] == 'V') {
            //$sql .= " and cast((extract(day from now() - arpn.tarpnoincl)/365)*12 as int) < $vigencia";
            $sql .= " and now() between $campoVigencia and $campoVigencia + ($vigencia::text || 'month'):: interval";
        }

        if ($dto['tipoAta'] == 'I') {
            $sql .= ' group by arpi.corglicodi,ol.eorglidesc,arpn.carpnotiat,arpi.clicpoproc,arpi.farpinsitu,vigencia,arpi.alicpoanop,arpi.ccomlicodi,arpi.earpinobje,arpn.carpnosequ,cl.ecomlidesc, arpi.cgrempcodi, arpi.cusupocodi, arpi.aarpinanon, arpi.carpincodn, fc.nforcrrazs, fc.aforcrccgc, fc.aforcrccpf';
        } else {
            $sql .= " group by arpi.carpexcodn, arpi.earpexorgg,arpi.earpexproc, arpi.tarpexdini, arpn.carpnotiat,arpi.aarpexanon,arpi.earpexobje,vigencia,arpn.carpnosequ,$situacao, fc.nforcrrazs, fc.aforcrccgc, fc.aforcrccpf, fc1.nforcrrazs, fc1.aforcrccgc, fc1.aforcrccpf";
        }        

        if ($dto['tipoAta'] == 'I') {
            $sql .= ' order by arpi.aarpinanon DESC, arpi.carpincodn ASC, ol.eorglidesc, arpi.corglicodi ';
        }else{
            $sql .= ' order by arpi.aarpexanon DESC, arpi.tarpexdini DESC, arpi.earpexorgg, arpi.carpexcodn ';
        }
        // print_r($sql);exit;
        
        $divEscondida = '<div style="display:none;">';
        $divEscondida .= $sql;
        $divEscondida .= '</div>';
        print($divEscondida);
        

        $res = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($res);
        //print_r($sql);die;
        return $res;
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
class RegistroPreco_Negocio_ConsAtaRegistroPrecoExtratoAta extends Negocio_Abstrata
{
    public function __construct()
    {
        $this->setDados(new RegistroPreco_Dados_ConsAtaRegistroPrecoExtratoAta());
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
        return $this->getDados()->consultarExtratoAta($dto);
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
class RegistroPreco_Adaptacao_ConsAtaRegistroPrecoExtratoAta extends Adaptacao_Abstrata
{
    
    /**
     *
     * {@inheritdoc}
     *
     * @see Adaptacao_Abstrata::getNegocio()
     */
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_ConsAtaRegistroPrecoExtratoAta());
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
        
        $cpfCNPJ = preg_replace('/[^0-9]/', '', $_POST['fornecedor']);
        
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

    public function consultarGrupo($tipo)
    {
        return $this->getNegocio()->consultarGrupo($tipo);
        //$this->plotarBlocoGrupo($grupo);
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
class RegistroPreco_UI_ConsAtaRegistroPrecoExtratoAta extends UI_Abstrata
{
    private function carregaCheckbox()
    {
        $_SESSION['tipoAta'] = ($_REQUEST['tipoAta']) ? $_REQUEST['tipoAta']: $_SESSION['tipoAta'];
        $_SESSION['cpfcnpj'] = ($_REQUEST['cpfcnpj'])?$_REQUEST['cpfcnpj']: $_SESSION['cpfcnpj'];
        $_SESSION['identificadorGrupo'] = ($_REQUEST['identificadorGrupo'])?$_REQUEST['identificadorGrupo']:$_SESSION['identificadorGrupo'];
        $_SESSION['inativos'] = ($_REQUEST['inativos'])?$_REQUEST['inativos']: $_SESSION['inativos'];
        $_SESSION['vigentes'] = ($_REQUEST['vigentes']) ? $_REQUEST['vigentes']:$_SESSION['vigentes'];
        $_SESSION['situacao_ata'] = ($_REQUEST['situacao_ata'])?$_REQUEST['situacao_ata']:$_SESSION['situacao_ata'];
    
        $this->getTemplate()->CHECK_ATA_INTERNA = (isset($_SESSION['tipoAta']) && $_SESSION['tipoAta'] == 'I') ? 'CHECKED' : '';
        $this->getTemplate()->CHECK_ATA_EXTERNA = (isset($_SESSION['tipoAta']) && $_SESSION['tipoAta'] == 'E') ? 'CHECKED' : '';
        $this->getTemplate()->CHECK_CNPJ = (isset($_SESSION['cpfcnpj']) && $_SESSION['cpfcnpj'] == 'cnpj') ? 'CHECKED' : '';
        $this->getTemplate()->CHECK_CPF = (isset($_SESSION['cpfcnpj']) && $_SESSION['cpfcnpj'] == 'cpf') ? 'CHECKED' : '';
        $this->getTemplate()->CHECK_M = (isset( $_SESSION['identificadorGrupo']) &&  $_SESSION['identificadorGrupo'] == 'M') ? 'CHECKED' : '';
        $this->getTemplate()->CHECK_S = (isset( $_SESSION['identificadorGrupo']) &&  $_SESSION['identificadorGrupo'] == 'S') ? 'CHECKED' : '';
        $this->getTemplate()->CHECK_INATIVOS = (isset( $_SESSION['inativos']) && $_SESSION['inativos'] == 'I') ? 'CHECKED' : '';
        $this->getTemplate()->CHECK_VIGENTES = (!isset($_SESSION['tipoAta']) || (isset( $_SESSION['vigentes']) &&  $_SESSION['vigentes'] == 'V') ) ? 'CHECKED' : '';
        $this->getTemplate()->CHECK_ATIVAS = (!isset($_SESSION['tipoAta']) || (isset($ $_SESSION['situacao_ata']) &&  $_SESSION['situacao_ata'] == 'A') ) ? 'CHECKED' : '';

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
    
        $this->getTemplate()->CHECK_COD_M = (isset($_SESSION['pesquisaMaterial']) && $_SESSION['pesquisaMaterial'] == '0') ? 'selected' : '';
        $this->getTemplate()->CHECK_DEC_M = (isset($_SESSION['pesquisaMaterial']) && $_SESSION['pesquisaMaterial'] == '1') ? 'selected' : '';
        $this->getTemplate()->CHECK_DI_M = (isset($_SESSION['pesquisaMaterial']) && $_SESSION['pesquisaMaterial'] == '2') ? 'selected' : '';
        $this->getTemplate()->CHECK_COD_S = (isset($_SESSION['pesquisaServico']) && $_SESSION['pesquisaServico'] == '0') ? 'selected' : '';
        $this->getTemplate()->CHECK_DEC_S = (isset($_SESSION['pesquisaServico']) && $_SESSION['pesquisaServico'] == '1') ? 'selected' : '';
        $this->getTemplate()->CHECK_DI_S = (isset($_SESSION['pesquisaServico']) && $_SESSION['pesquisaServico'] == '2') ? 'selected' : '';
        $this->getTemplate()->CHECK_S = (isset($_SESSION['identificadorGrupo']) && $_SESSION['identificadorGrupo'] == 'S') ? 'CHECKED' : '';
        $this->getTemplate()->CHECK_INATIVOS = (isset($_SESSION['inativos']) && $_SESSION['inativos'] == 'I') ? 'CHECKED' : '';
        $this->getTemplate()->CHECK_VIGENTES = (!isset($_SESSION['tipoAta']) || (isset($_SESSION['vigentes']) && $_SESSION['vigentes'] == 'V') ) ? 'CHECKED' : '';
        $this->getTemplate()->CHECK_ATIVAS = (!isset($_SESSION['tipoAta']) || (isset($_SESSION['situacao_ata']) && $_SESSION['situacao_ata'] == 'A') ) ? 'CHECKED' : '';

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
        $_SESSION['numeroAta'] = ($_REQUEST['numeroAta']) ? $_REQUEST['numeroAta'] : $_SESSION['numeroAta'];
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

        $this->getTemplate()->NRATA                 = isset($_SESSION['numeroAta']) ? $_SESSION['numeroAta'] : '';
        $this->getTemplate()->PROCESSO              = isset($_SESSION['processo']) ? $_SESSION['processo'] : '';
        $this->getTemplate()->PROCESSO_ANO_EXTERNO  = isset($_SESSION['processo_ano']) ? $_SESSION['processo_ano'] : '';
        $this->getTemplate()->CODIGO_EXTERNO        = isset($_SESSION['codigoAtaE']) ? $_SESSION['codigoAtaE'] : '';
        $this->getTemplate()->ANO_EXTERNO           = isset($_SESSION['anoAtaE']) ? $_SESSION['anoAtaE'] : '';
        $this->getTemplate()->LINK_ATA              = isset($_SESSION['linkAta']) ? $_SESSION['linkAta'] : '';
        $this->getTemplate()->ANO                   = isset($_SESSION['ano']) ? $_SESSION['ano'] : '';
        $this->getTemplate()->FORNECEDOR            = isset($_SESSION['fornecedor']) ? $_SESSION['fornecedor'] : '';
        $this->getTemplate()->MATERIAL              = isset($_SESSION['material']) ? $_SESSION['material'] : '';
        $this->getTemplate()->SERVICO               = isset($_SESSION['servico']) ? $_SESSION['servico'] : '';
        $this->getTemplate()->OBJETO                = isset($_SESSION['objeto']) ? $_SESSION['objeto'] : '';
        $this->getTemplate()->FORNECEDOR_RAZ        = isset($_SESSION['fornecedorRaz']) ? $_SESSION['fornecedorRaz'] : '';
         
        $_SESSION['numeroAta'] = ($_REQUEST['numeroAta']) ? $_REQUEST['numeroAta'] : '';
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
        
        
        $this->getTemplate()->TIPO_ATA_SESSAO = 'interna';

        if(isset($_POST['tipoAta'])) {
            $_tipo = $_POST['tipoAta'];
            $this->getTemplate()->TIPO_ATA_SESSAO = ($_tipo == 'I') ? 'interna' : 'externa';
        }

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
    private function plotarBlocoOrgao($orgaos)
    {
        $orgaoNumeracao = filter_input(INPUT_POST, 'orgaoParticipante', FILTER_VALIDATE_INT);
        if ($orgaos == null) {
            return;
        }

        foreach ($orgaos as $orgao) {
            $this->getTemplate()->ORGAO_VALUE = $orgao->corglicodi;

            $this->getTemplate()->ORGAO_TEXT = $orgao->eorglidesc;

            // Vendo se a opção atual deve ter o atributo "selected"
            if ($orgaoNumeracao == $orgao->corglicodi) {
                $this->getTemplate()->ORGAO_SELECTED = 'selected';
            } else {
                $this->getTemplate()->clear('ORGAO_SELECTED');
            }

            $this->getTemplate()->block('BLOCO_ORGAO_PARTICIPANTE');
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
    private function plotarBlocoOrgaoGestorAtaExterna($orgaos)
    {
        $orgaoNumeracao = filter_input(INPUT_POST, 'orgaoGerenciadorExterno', FILTER_VALIDATE_INT);
        if ($orgaos == null) {
            return;
        }
        foreach ($orgaos as $orgao) {

            $this->getTemplate()->ORGAO_VALUE_GERENCIADOR_EXTERNA = $orgao->earpexorgg;
            $this->getTemplate()->ORGAO_TEXT_GERENCIADOR_EXTERNA = $orgao->earpexorgg;

            // Vendo se a opção atual deve ter o atributo "selected"
            if ($orgaoNumeracao == $orgao->earpexorgg) {
                $this->getTemplate()->ORGAO_SELECTED_GERENCIADOR_EXTERNA = 'selected';
            } else {
                $this->getTemplate()->clear('ORGAO_EXTERNO_SELECTED_GERENCIADOR');
            }

            $this->getTemplate()->block('BLOCO_ORGAO_EXTERNO_GERENCIADOR');
        }
    }
	


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

    private function plotarBlocoResultadoAta($atas, $itensInativos = false)
    {
        $ultimoOrgaoPlotado = '';
        $ultimoTipoAtaPlotado = '';
        $atasOrgaos = array();

        $this->getTemplate()->TIPO_ATA = $atas[0]->carpnotiat == 'I' ? 'INTERNAS' : 'EXTERNAS';
        
        // Organizar por orgão
        foreach ($atas as $value) {
            $atasOrgaos[$value->eorglidesc][] = $value;
        }

        foreach ($atasOrgaos as $key => $value) {
            foreach($value as $ata) {
                if ($ultimoTipoAtaPlotado != $ata->carpnotiat) {
                    $this->getTemplate()->block('bloco_tipo_ata');
                    $ultimoTipoAtaPlotado = $ata->carpnotiat;
                }

                if ($ata->carpnotiat == 'I' && $ultimoOrgaoPlotado != $ata->corglicodi) {
                    $this->getTemplate()->ORGAO_ATA = $ata->eorglidesc;
                    $this->getTemplate()->block('bloco_orgao_ata');
                    $this->getTemplate()->block('bloco_titulo_resultado');
                    $ultimoOrgaoPlotado = $ata->corglicodi;
                }

                if ($ata->carpnotiat == 'E' && $ultimoOrgaoPlotado != $ata->earpexorgg) {                
                    $this->getTemplate()->ORGAO_ATA = strtoupper($ata->earpexorgg);
                    $this->getTemplate()->block('bloco_orgao_ata');
                    $this->getTemplate()->block('bloco_titulo_resultado_externo');        
                    $ultimoOrgaoPlotado = $ata->earpexorgg;        
                }

                $mes = substr($ata->vigencia, 5, 2);
                $dia = substr($ata->vigencia, 8, 2);
                $ano = substr($ata->vigencia, 0, 4);

                $valoUnidadeOrcamentaria = '008';
                $link_ata = 'ConsAtaRegistroPrecoExtratoAtaDetalhe.php?carpnosequ=' . $ata->carpnosequ;

                // itens inativos
                if($itensInativos) {
                    $link_ata .= '&inativos=1';
                }

                $this->getTemplate()->LINK_ATA = $link_ata;                

                if ($ata->carpnotiat == 'I') {
                    $consultaAta    = $this->getAdaptacao()->getNegocio()->consultarAtaPorChave($ata->aarpinanon, $ata->clicpoproc, $ata->corglicodi, $ata->carpnosequ);
                    $dto            = $this->getAdaptacao()->consultarDCentroDeCustoUsuario($consultaAta->cgrempcodi, $consultaAta->cusupocodi, $consultaAta->corglicodi);
                    $objeto         = current($dto);
                    $numeroAta      = $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);
                    $numeroAta      .= "." . str_pad($ata->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ata->aarpinanon;
                    $numeroProcesso = str_pad($ata->clicpoproc, 4, '0', STR_PAD_LEFT) . '/' . $ata->alicpoanop;
                    $objeto         = $ata->earpinobje;
                    $situacao       = $ata->farpinsitu == 'A' ? 'ATIVO' : 'INATIVO';
                    $fornecedor     = $ata->nforcrrazs . ' - ';
                    $fornecedor     .= !empty($ata->aforcrccgc) ? FormataCNPJ($ata->aforcrccgc) : FormataCPF($ata->aforcrccpf);
                } else {
                    $numeroAta      = $ata->carpexcodn . '/' . $ata->aarpexanon;
                    $numeroProcesso = $ata->earpexproc;
                    $objeto         = $ata->earpexobje;
                    $situacao       = $ata->farpexsitu == 'A' ? 'ATIVO' : 'INATIVO';
                    if(!empty($ata->nforcrrazs1)) {
                        $fornecedor     = $ata->nforcrrazs1 . ' - ';
                        $fornecedor     .= !empty($ata->aforcrccgc1) ? FormataCNPJ($ata->aforcrccgc1) : FormataCPF($ata->aforcrccpf1);
                    } else {
                        $fornecedor     = $ata->nforcrrazs . ' - ';
                        $fornecedor     .= !empty($ata->aforcrccgc) ? FormataCNPJ($ata->aforcrccgc) : FormataCPF($ata->aforcrccpf);
                    }
                }

                $this->getTemplate()->VALOR_NUMERO_ATA  = $numeroAta;
                $this->getTemplate()->VALOR_VIGENCIA    = $dia . '/' . $mes . '/' . $ano;
                $this->getTemplate()->VALOR_PROCESSO    = $numeroProcesso;
                $this->getTemplate()->VALOR_OBJETO      = $objeto;
                $this->getTemplate()->VALOR_SITUACAO    = $situacao;
                $this->getTemplate()->VALOR_FORNECEDOR  = $fornecedor;
                $this->getTemplate()->block('bloco_resultado');
                $this->getTemplate()->block('bloco_sub_titulo');
            }
        }
        $this->getTemplate()->block('bloco_resultado_ata');
    }

    /**
     * [plotarBlocoOrgao description].
     *
     * @param [type] $orgaos
     *            [description]
     *
     * @return [type] [description]
     */
    private function plotarBlocoGrupo($grupos)
    {
        $grupoNumeracao = filter_input(INPUT_POST, 'grupo', FILTER_VALIDATE_INT);
        if ($grupos == null) {
            return;
        }       

        foreach ($grupos as $grupo) {

            $this->getTemplate()->GRUPO_VALUE = $grupo->cgrumscodi;

            $this->getTemplate()->GRUPO_TEXT = $grupo->egrumsdesc;

            // Vendo se a opção atual deve ter o atributo "selected"
            if ($grupoNumeracao == $grupo->cgrumscodi) {
                $this->getTemplate()->GRUPO_SELECTED = 'selected';
            } else {
                $this->getTemplate()->clear('GRUPO_SELECTED');
            }

            $this->getTemplate()->block('BLOCO_GRUPO');
        }
    }

    /**
     * [__construct description].
     */
    public function __construct()
    {        
        $template = new TemplatePaginaPadrao("templates/ConsAtaRegistroPrecoExtratoAta.html", "Registro de Preço > Extrato Atas");
        $template->NOME_PROGRAMA = 'ConsAtaRegistroPrecoExtratoAta';
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
        $this->setAdaptacao(new RegistroPreco_Adaptacao_ConsAtaRegistroPrecoExtratoAta());
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
        $this->recuperarDadosTela();
        $this->plotarBlocoOrgao($this->getAdaptacao()->consultarOrgaoParticipantes());
        $this->plotarBlocoOrgaoGerenciador($this->getAdaptacao()->consultarOrgaoGerenciado());
		$this->plotarBlocoOrgaoGestorAtaExterna($this->getAdaptacao()->consultarOrgaoGestorAtaExterna());
        $this->plotarBlocoComissaoLicitacao($this->getAdaptacao()->consultarComissaoLicitacao());

        if(count($_REQUEST) <=1){
            $_SESSION['COD_MATERIAL_HIDDEN'] = '';
            $_SESSION['COD_SERVICO_HIDDEN'] = '';
            $_SESSION['COD_FORNECEDOR_HIDDEN'] = '';
            $_SESSION['FORNECEDOR_COMPLETO'] = '';
            $_SESSION['grupos_plotados'] = '';
        }
        
        $this->getTemplate()->COD_MATERIAL                  = $_SESSION['COD_MATERIAL_HIDDEN'];
        $this->getTemplate()->COD_SERVICO                   = $_SESSION['COD_SERVICO_HIDDEN'];
        $this->getTemplate()->COD_FORNECEDOR                = $_SESSION['COD_FORNECEDOR_HIDDEN'];
        $this->getTemplate()->VALORES_AUXILIARES_FORNECEDOR = $this->montarforn($_SESSION['FORNECEDOR_COMPLETO']);    
         
    }

    public function processGrupos()
    {
        $tipoGrupo = $_POST['identificadorGrupo'];
        $grupos = $this->getAdaptacao()->consultarGrupo($tipoGrupo);
        $_SESSION['grupos_plotados'] = $grupos;        
        //if(isset($_SESSION['grupos_plotados'])){
        $this->plotarBlocoGrupo($grupos);
        //}
       
    }

    public function processVoltar()
    {
        header('Location: ' . 'ConsAtaRegistroPrecoExtratoAta.php');
        exit();
       
    }

    public function consultarExtratoAta()
    {
        $_SESSION['hpesquisaK'] = $_POST;
        $extratoAtas = $this->getAdaptacao()->consultarExtratoAta();
        $this->recuperarDadosTela();
        $this->plotarBlocoOrgao($this->getAdaptacao()
            ->consultarOrgaoParticipantes());
        $this->plotarBlocoOrgaoGerenciador($this->getAdaptacao()
            ->consultarOrgaoGerenciado());
        $this->plotarBlocoOrgaoGestorAtaExterna($this->getAdaptacao()
			->consultarOrgaoGestorAtaExterna());
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
$app = new RegistroPreco_UI_ConsAtaRegistroPrecoExtratoAta();

$acao = isset($_POST['Botao']) ? filter_var($_POST['Botao'], FILTER_SANITIZE_STRING) : null;
if(empty($acao) && !empty($_SESSION['hpesquisaK'])){
    $acao = $_SESSION['hpesquisaK']["Botao"];
    $_POST =$_SESSION['hpesquisaK'];
}

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
