<?php
# ----------------------------------------------------------------------------------------------------------------------
// Portal da DGCO
// Programa: funcoesContrato.php
// Objetivo: funções com regras do módulo Contrato
// Autores: João Madson Felix || Eliakim Ramos || Edson || Marcello
# ----------------------------------------------------------------------------------------------------------------------
// arquivo geral de funcoes
// O arquivo vai diferenciar as funções através de classes específicas.
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: CadContratoManter.php
# Autor:    Marcello Calvalcanti
# Data:     26/05/2021
# CR:       248617
# -------------------------------------------------------------------------
# Portal da Compras
# Autor:    João Madson
# Data:     19/10/2021
# CR:       254713
# -------------------------------------------------------------------------
# Autor : Osmar Celestino - Lucas Vicente
# Data: 12/05/2022
# Objetivo: Corrigir fiscal nulo
#---------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 23/05/2022
# Objetivo: CR #244323
#---------------------------------------------------------------------------
# Autor: Osmar Celestino
# Data : 14/06/2022
# CR 264543
#-----------------------------------------------------------------------------
# Autor: Lucas Vicente
# Data:  15/02/2023
# Objetivo: CR #275671
# -------------------------------------------------------------------------
require_once dirname(__FILE__) . '/../funcoes.php';

class funcoesContrato{
    public $conexaoDb;

        public function __construct(){
            $this->conexaoDb = conexao();
        }

        public function DesconectaBanco(){
            //$this->conexaoDb->disconnect();
        }

    function anti_injection($sql){
        // remove palavras que contenham sintaxe sql
        preg_match("/(from|select|insert|delete|where|drop table|show tables|#|\*|--|\\\\)/",$sql,$matches);
        $sql = @preg_replace($matches,"",$sql);
        $sql = trim($sql);//limpa espaços vazio
        $sql = strip_tags($sql);//tira tags html e php
        $sql = addslashes($sql);//Adiciona barras invertidas a uma string
        return $sql;
    }
    
    function validaCPF($cpf = null){

        // Verifica se um número foi informado
        if(empty($cpf)) {
            return false;
        }
    
        // Elimina possivel mascara
        $cpf = preg_replace("/[^0-9]/", "", $cpf);
        $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
        
        // Verifica se o numero de digitos informados é igual a 11 
        if (strlen($cpf) != 11) {
            return false;
        }
        // Verifica se nenhuma das sequências invalidas abaixo 
        // foi digitada. Caso afirmativo, retorna falso
        else if ($cpf == '00000000000' || 
            $cpf == '11111111111' || 
            $cpf == '22222222222' || 
            $cpf == '33333333333' || 
            $cpf == '44444444444' || 
            $cpf == '55555555555' || 
            $cpf == '66666666666' || 
            $cpf == '77777777777' || 
            $cpf == '88888888888' || 
            $cpf == '99999999999') {
            return false;
         // Calcula os digitos verificadores para verificar se o
         // CPF é válido
         } else {   
            
            for ($t = 9; $t < 11; $t++) {
                
                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf{$c} * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cpf{$c} != $d) {
                    return false;
                }
            }
    
            return true;
        }
    }

    function valida_cnpj ( $cnpj ){
        $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);

            // Valida tamanho
            if (strlen($cnpj) != 14)
                return false;

            // Verifica se todos os digitos são iguais
            if (preg_match('/(\d)\1{13}/', $cnpj))
                return false;	
            // Lista de CNPJs inválidos
            $invalidos = array(
                '00000000000000',
                '11111111111111',
                '22222222222222',
                '33333333333333',
                '44444444444444',
                '55555555555555',
                '66666666666666',
                '77777777777777',
                '88888888888888',
                '99999999999999'
            );

            // Verifica se o CNPJ está na lista de inválidos
            if (in_array($cnpj, $invalidos)) {	
                return false;
            }
            // Valida primeiro dígito verificador
            for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++)
            {
                $soma += $cnpj{$i} * $j;
                $j = ($j == 2) ? 9 : $j - 1;
            }

            $resto = $soma % 11;

            if ($cnpj{12} != ($resto < 2 ? 0 : 11 - $resto))
                return false;

            // Valida segundo dígito verificador
            for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++)
            {
                $soma += $cnpj{$i} * $j;
                $j = ($j == 2) ? 9 : $j - 1;
            }

            $resto = $soma % 11;

            return $cnpj{13} == ($resto < 2 ? 0 : 11 - $resto);
    }

    function GetFornecedor($CPF,$CNPJ){
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
        $sql .= " where fc.aforcrccgc ='$CNPJ'";
        $sql .= " or fc.aforcrccpf ='$CPF'";
        $resultado = executarSQL($this->conexaoDb, $sql);
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
        return $retorno;
    }

    public function getFornecedorCredenciado($codigoFornecedor){
        $db = conexao();
        $sql  = " SELECT forn.aforcrccgc, forn.aforcrccpf, forn.nforcrrazs, forn.eforcrlogr, forn.eforcrbair, forn.nforcrcida, forn.cforcresta, forn.eforcrcomp ";
        $sql .= " FROM sfpc.tbfornecedorcredenciado AS forn ";
        $sql .= " WHERE forn.aforcrsequ=".$codigoFornecedor;
        
        $resultado = executarSQL($db, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function GetFornecedorByCod($codFornecedor){
        $db = Conexao();
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
        $sql .= " where fc.aforcrsequ =".$codFornecedor;
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
        return $retorno;
    }
    
    public function GetOrgao($internet = null){
        $cusupocodi = $_SESSION['_cusupocodi_'];
        $sql  = "SELECT DISTINCT	org.corglicodi, org.eorglidesc ";
        $sql .= " FROM	sfpc.tborgaolicitante org "; 
        if(!empty($cusupocodi)){
            $especial = $this->checaUsuarioManterEspecial($cusupocodi);
        }else{
            $cusupocodi = false;
        }
        if($especial == true){
            $sql .= " WHERE	org.forglisitu = 'A' ";
        }else{
            if(empty($internet)){
                $sql .= " INNER JOIN sfpc.tbcentrocustoportal AS CentroCusto ON (org.corglicodi = CentroCusto.corglicodi) ";
                $sql .= " INNER JOIN      sfpc.tbusuariocentrocusto AS UsuarioCusto ON (UsuarioCusto.ccenposequ = CentroCusto.ccenposequ) ";
            }
            $sql .= " WHERE			org.forglisitu = 'A' ";
            if(empty($internet) && !empty($cusupocodi)){
                $sql .= " AND UsuarioCusto.cusupocodi = $cusupocodi AND UsuarioCusto.fusucctipo = 'C' ";
            }
        }
        $sql .= " ORDER BY		org.eorglidesc ASC";
        //var_dump($sql);
        $resultado = executarSQL($this->conexaoDb, $sql);
        $dados = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dados[] = $retorno;
        }
        return $dados;
    }
    public function checaUsuarioManterEspecial($codUsuario){
        $sql  = " SELECT cperficodi as perfil
                  from sfpc.tbusuarioperfil
                  WHERE cusupocodi = $codUsuario";
        
        $resultado = executarSQL($this->conexaoDb, $sql);
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
        if($retorno->perfil == 2 || $retorno->perfil == 6 || $retorno->perfil == 39){
            return true;
        } 
        return false;
    }

   /* function GetOrgao($internet = null){
        $cusupocodi = $_SESSION['_cusupocodi_'];
        $sql  = "SELECT DISTINCT	org.corglicodi, org.eorglidesc ";
        $sql .= " FROM	sfpc.tborgaolicitante org "; 
        // if(empty($internet)){
        //     $sql .= " INNER JOIN sfpc.tbcentrocustoportal AS CentroCusto ON (org.corglicodi = CentroCusto.corglicodi) ";
        //     $sql .= " INNER JOIN      sfpc.tbusuariocentrocusto AS UsuarioCusto ON (UsuarioCusto.ccenposequ = CentroCusto.ccenposequ) ";
        // }
        $sql .= " WHERE			org.forglisitu = 'A' ";
        // if(empty($internet)){
        //     $sql .= " AND UsuarioCusto.cusupocodi = $cusupocodi AND UsuarioCusto.fusucctipo = 'C' ";
        // }
        $sql .= " ORDER BY		org.eorglidesc ASC";
        // var_dump($sql);die
         $resultado = executarSQL($this->conexaoDb, $sql);
        $dados = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dados[] = $retorno;
        }
        return $dados;
    }
    */

    function PesquisaRelContConsolidado($dados){
        $db = conexao();
        $cusupocodi = $_SESSION['_cusupocodi_'];
        $sql  = "select orlic.eorglidesc, con.cdocpcsequ,  doc.cfasedsequ, doc.csitdcsequ as codsequsituacaodoc,  con.ectrpcnumf,  con.aforcrsequ, con.ectrpcobje, con.ctpcomcodi,  forn.aforcrsequ, ";
        $sql .= " forn.aforcrccgc, forn.aforcrccpf, forn.nforcrrazs,  SCC.csolcocodi, SCC.asolcoanos, CC.ccenpocorg, CC.ccenpounid, ";
        $sql .= " CASE WHEN SCC.ctpcomcodi IS NULL THEN con.ctpcomcodi  WHEN SCC.ctpcomcodi IS NOT NULL THEN SCC.ctpcomcodi END AS ctpcomcodi ";
        $sql .= " from sfpc.tbcontratosfpc as con inner join sfpc.tbdocumentosfpc as doc on ( con.cdocpcsequ=doc.cdocpcsequ ) ";
        $sql .= " inner join sfpc.tbfornecedorcredenciado forn on ( con.aforcrsequ=forn.aforcrsequ ) ";
        $sql .="  left outer join SFPC.TBSOLICITACAOCOMPRA SCC on ( con.csolcosequ = SCC.csolcosequ ) ";
        $sql .="  left outer join SFPC.tbcentrocustoportal CC on CC.ccenposequ = SCC.ccenposequ ";
        $sql .= " left outer join sfpc.tborgaolicitante orlic on ( con.corglicodi= orlic.corglicodi ) ";
        $sql .= "  where 1=1 ";
        
        if(!empty($dados['numerocontratoano'])){
            $sql.=" and con.ectrpcnumf ='".$dados['numerocontratoano']."' ";
        }
       
        if(!empty($dados['cnpj']) || !empty($dados['cpf'])){
            $cnpj = (!empty($dados['cnpj']))?$dados['cnpj']:'';
            $cpf  =  (!empty($dados['cpf']))?$dados['cpf']:'';
            $sql.=" AND (forn.aforcrccgc ='".$cnpj."' OR forn.aforcrccpf ='".$cpf."')";
        }
        if(!empty($dados['numeroScc'])){
            $dadosSccAux = explode(".",$dados['numeroScc']);
            $ccenpocorg = substr($dadosSccAux[0],0,2);
            $ccenpounid = substr($dadosSccAux[0],2,2);
            $csolcocodi = substr($dadosSccAux[0],4,4);
            $asolcoanos = substr($dadosSccAux[0],8,4);
            $sql .= " AND CC.ccenpocorg=".$ccenpocorg;
            $sql .= " AND CC.ccenpounid=".$ccenpounid;
            $sql .= " AND SCC.csolcocodi=".$csolcocodi;
            $sql .= " AND SCC.asolcoanos=".$asolcoanos;
        }
        if(!empty($dados['Orgao'])){
           $sql.=" AND con.corglicodi  in (".$dados['Orgao'].")  ";
        }else{
            //$sql.=" AND con.corglicodi = CC.corglicodi  ";
        }
        if(!empty($dados['razao'])){
            if($dados['tipop'] == "iniciado"){
                $sql .=" AND forn.nforcrrazs like '".$dados['razao']."%'";
            }else if($dados['tipop'] == "contendo"){
                $sql .=" AND forn.nforcrrazs like '%".$dados['razao']."%'";
            }
            
        }
        
        if(!empty($dados['situacao'])){
            $sitArray = explode('-',$dados['situacao']);
            $sql .=" AND doc.cfasedsequ = ".$sitArray[0]." AND doc.csitdcsequ =".$sitArray[1];
        }
        
        if(!empty($dados['objeto'])){
            $sql .=" AND con.ectrpcobje like '%".$dados['objeto']."%'";
        }

        if(!empty($dados['origem'])){
            $sql .=" AND con.ctpcomcodi in (".$dados['origem'].")";
        }
        $sql .= " GROUP BY orlic.eorglidesc, con.actrpcnumc, con.cdocpcsequ,  doc.cfasedsequ,  con.ectrpcnumf,  con.aforcrsequ, con.ectrpcobje, con.ctpcomcodi,  forn.aforcrsequ, ";
        $sql .= " forn.aforcrccgc, forn.aforcrccpf, forn.nforcrrazs,  SCC.csolcocodi, SCC.asolcoanos, CC.ccenpocorg, CC.ccenpounid, SCC.ctpcomcodi, doc.csitdcsequ ";
        $sql .= " order by SUBSTRING(con.ectrpcnumf, position('/' in con.ectrpcnumf)+1, 4) desc, SUBSTRING(con.ectrpcnumf, position('' in con.ectrpcnumf), 4) desc, SUBSTRING(con.ectrpcnumf, position('.' in con.ectrpcnumf)+1, 4) desc, ";
        $sql .= " CC.ccenpocorg desc,  SCC.asolcoanos desc,SCC.csolcocodi desc";
        $resultado = executarSQL($db, $sql);
        $dadosPesquisa = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosPesquisa[] = $retorno;
        }
        return $dadosPesquisa;
    }

    function PesquisaAltDatasAditivos($dados){
        $cusupocodi = $_SESSION['_cusupocodi_'];
        $sql  = "select orlic.eorglidesc, con.cdocpcsequ,  doc.cfasedsequ, doc.csitdcsequ as codsequsituacaodoc,  con.ectrpcnumf,  con.aforcrsequ, con.ectrpcobje, con.ctpcomcodi,  forn.aforcrsequ, ";
        $sql .= " forn.aforcrccgc, forn.aforcrccpf, forn.nforcrrazs,  SCC.csolcocodi, SCC.asolcoanos, CC.ccenpocorg, CC.ccenpounid, SCC.ctpcomcodi ";
        $sql .= " from sfpc.tbcontratosfpc as con inner join sfpc.tbdocumentosfpc as doc on ( con.cdocpcsequ=doc.cdocpcsequ ) ";
        $sql .= " inner join sfpc.tbfornecedorcredenciado forn on ( con.aforcrsequ=forn.aforcrsequ ) ";
        $sql .="  left outer join SFPC.TBSOLICITACAOCOMPRA SCC on ( con.csolcosequ = SCC.csolcosequ ) ";
        $sql .="  left outer join SFPC.tbcentrocustoportal CC on CC.ccenposequ = SCC.ccenposequ ";
        $sql .= " left outer join sfpc.tborgaolicitante orlic on ( con.corglicodi= orlic.corglicodi ) ";
        $sql .= " inner join sfpc.tbaditivo as adit on (adit.cdocpcseq1 = con.cdocpcsequ and adit.faditialpz = 'SIM') ";
        $sql .= "  where doc.csitdcsequ = 1 AND orlic.corglicodi = con.corglicodi ";
        
        if(!empty($dados['numerocontratoano'])){
            $sql.=" and con.ectrpcnumf ='".$dados['numerocontratoano']."' ";
        }
        
        if(!empty($dados['cnpj']) || !empty($dados['cpf'])){
            $cnpj = (!empty($dados['cnpj']))?$dados['cnpj']:'';
            $cpf  =  (!empty($dados['cpf']))?$dados['cpf']:'';
            $sql.=" AND (forn.aforcrccgc ='".$cnpj."' OR forn.aforcrccpf ='".$cpf."')";
        }
        if(!empty($dados['numeroScc'])){
            $dadosSccAux = explode(".",$dados['numeroScc']);
            $ccenpocorg = substr($dadosSccAux[0],0,2);
            $ccenpounid = substr($dadosSccAux[0],2,2);
            $csolcocodi = substr($dadosSccAux[0],4,4);
            $asolcoanos = substr($dadosSccAux[0],8,4);
            $sql .= " AND CC.ccenpocorg=".$ccenpocorg;
            $sql .= " AND CC.ccenpounid=".$ccenpounid;
            $sql .= " AND SCC.csolcocodi=".$csolcocodi;
            $sql .= " AND SCC.asolcoanos=".$asolcoanos;
        }

        if(!empty($dados['Orgao'])){
            $sql.=" AND con.corglicodi  in (".$dados['Orgao'].")  ";
        }else{
            $sql.=" AND con.corglicodi = CC.corglicodi  ";
        }

        $sql .= " GROUP BY orlic.eorglidesc, con.actrpcnumc, con.cdocpcsequ,  doc.cfasedsequ,  con.ectrpcnumf,  con.aforcrsequ, con.ectrpcobje, con.ctpcomcodi,  forn.aforcrsequ, ";
        $sql .= " forn.aforcrccgc, forn.aforcrccpf, forn.nforcrrazs,  SCC.csolcocodi, SCC.asolcoanos, CC.ccenpocorg, CC.ccenpounid, SCC.ctpcomcodi, doc.csitdcsequ ";
        $sql .= " order by SUBSTRING(con.ectrpcnumf, position('/' in con.ectrpcnumf)+1, 4) desc, SUBSTRING(con.ectrpcnumf, position('' in con.ectrpcnumf), 4) desc, SUBSTRING(con.ectrpcnumf, position('.' in con.ectrpcnumf)+1, 4) desc ";
        // $sql .= " order by orlic.eorglidesc asc, SUBSTRING(con.ectrpcnumf, position('/' in con.ectrpcnumf)+1, 4) desc, con.actrpcnumc desc ";
        
        $resultado = executarSQL($this->conexaoDb, $sql);
        $dadosPesquisa = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosPesquisa[] = $retorno;
        }
        return $dadosPesquisa;
    }

    function GetOrgaoById($dados, $internet = null){
        $db = conexao();
        $cusupocodi = $_SESSION['_cusupocodi_'];
        $sql  = "SELECT DISTINCT	org.corglicodi, org.eorglidesc ";
        $sql .= " FROM	sfpc.tborgaolicitante org "; 
        $sql .= " INNER JOIN sfpc.tbcentrocustoportal AS CentroCusto ON (org.corglicodi = CentroCusto.corglicodi) ";
        $sql .= " INNER JOIN      sfpc.tbusuariocentrocusto AS UsuarioCusto ON (UsuarioCusto.ccenposequ = CentroCusto.ccenposequ) ";
        $sql .= " WHERE	org.forglisitu = 'A' ";
        if(!empty($dados['Orgao'])){
            $sql.=" AND org.corglicodi  in (".$dados['Orgao'].")  ";
        }
        $sql .= " ORDER BY		org.eorglidesc ASC";
         $resultado = executarSQL($db, $sql);
        $dados = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dados[] = $retorno;
        }
        return $dados;
    }

    function GetSituacaoFornecedor($codFornecedor){
        $db = conexao();
       if(!empty($codFornecedor)){
        $sql       = "SELECT	B.EFORTSDESC ";
        $sql      .=	" FROM SFPC.TBFORNSITUACAO A, SFPC.TBFORNECEDORTIPOSITUACAO B ";
        $sql      .=	" WHERE  A.CFORTSCODI = B.CFORTSCODI ";			
        $sql      .= " AND A.AFORCRSEQU = ".$codFornecedor." ";
        $sql      .=	" ORDER BY A.TFORSIULAT DESC --Garantir que a última modificação da data de situação mais recente esteja na 1a linha ";
        $resultado = executarSql($db, $sql);
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
        return $retorno;
       }else{
           return (object) array('efortsdesc'=> 'Codigo do fornecedor é invalido.');
       }
    }

    function GetTipoCompra($codCompra){
        $db = conexao();
        if(!empty($codCompra)){
            $sql = "select tc.etpcomnome from SFPC.tbtipocompra as tc where tc.ctpcomcodi =".$codCompra;
            $resultado = executarSql($db, $sql);
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            return $retorno;
           }else{
               return (object) array('etpcomnome'=> '');
           }
    }

    function GetSituacaoContrato($faseDocumento,$codSitucao){
        $db = conexao();
       if($faseDocumento != null){
            if(!empty($codSitucao)){
                $sql = "select sitdoc.esitdcdesc from sfpc.tbsituacaodocumento as sitdoc where sitdoc.cfasedsequ =".$faseDocumento." and csitdcsequ =".$codSitucao;
                $resultado = executarSql($db, $sql);
                $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
                return $retorno;
            }else{
               return (object) array('esitdcdesc'=> '');
            }
       }else{
            if(!empty($codSitucao)){
                $sql = "select sitdoc.esitdcdesc from sfpc.tbsituacaodocumento as sitdoc where sitdoc.cfasedsequ =".$codSitucao;
                $resultado = executarSql($db, $sql);
                $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
                return $retorno;
            }else{
               return (object) array('esitdcdesc'=> '');
            }
       }        
    }

    function MascarasCPFCNPJ($valor){
        if(strlen($valor) == 11){
            $mascara = "###.###.###-##";
            for($i =0; $i <= strlen($mascara)-1; $i++){
                if($mascara[$i] == "#"){
                    if(isset($valor[$k])){
                       $maskared .= $valor[$k++];
                    }
                }else{
                    $maskared .= $mascara[$i];
                }
            }
            return $maskared;
        }
        if(strlen($valor) == 14){
            $mascara = "##.###.###/####-##";
            for($i =0; $i <= strlen($mascara)-1; $i++){
                if($mascara[$i] == "#"){
                    if(isset($valor[$k])){
                       $maskared .= $valor[$k++];
                    }
                }else{
                    $maskared .= $mascara[$i];
                }
            }
            return $maskared;
        }
    }

    //Usadas primariamente em Relatório de Contrato Consolidado PDF

    function GetDadosContratoSelecionado($CodSequContrato){
        $sql =  " SELECT CON.cdocpcsequ as seqdocumento, DOC.ctidocsequ as coditipodoc, DOC.cfasedsequ as codsequfasedoc, DOC.csitdcsequ as codsequsituacaodoc, DOC.cmodocsequ as codmodeldoc, ";
        $sql .= " DOC.ctipgasequ as codisequtipogarantia, DOC.cusupocodi as userrespatualizacao, DOC.ctidocseq1 as codisequtipodoc, DOC.cfuchcsequ as codisequfuncao, DOC.cchelisequ as codisequckecklist, ";
        $sql .= " DOC.tdocpculat as dataultmaatualizacao, CON.ectrpcnumf as ncontrato, CON.actrpcanoc as anocontrato, CON.csolcosequ as seqscc, CON.aforcrsequ as codfornecedororig, CON.aforcrseq1, ";
        $sql .= " CON.corglicodi as codorgao, CON.ectrpcobje as objetivoContrato, CON.ectrpcobse as obsenceramento, forn.nforcrrazs as razao, CON.cctrpcccep as cep, forn.eforcrlogr as endereco, ";
        $sql .= " forn.aforcrccgc as cnpj, forn.aforcrccpf as cpf, forn.cceppocodi as cepfornecedor, forn.aforcrnume as numerofornecedor, forn.eforcrcomp as complementofornecedor, CON.fctrpcobra as obra, ";
        $sql .= " forn.eforcrbair as bairrofornecedor, CON.fctrpcserc as econtinuo, forn.nforcrcida as cidadefornecedor, forn.cforcresta as uffornecedor, forn.aforcrtels as tel1fornecedor, ";
        $sql .= " CON.fctrpccons as consocio, forn.dforcrultb as datavalidadebalaco, forn.dforcrcnfc as datacertidaonegativafaleciaconcordata, forn.nforcrentp as nomeentidadeproficompetente, ";
        $sql .= " CON.fctrpcremf as execoufornec, CON.ectrpcremf as regexecoumodfornec, CON.actrpcpzec as prazoexec, CON.dctrpcinvg as datainivige, forn.tforcrulat as dataultimaateracaofornecedor, ";
        $sql .= " forn.fforcrtipo as tipofornecedor, CON.dctrpcfivg as datafimvige, CON.dctrpcpbdm as datapublic, CON.dctrpcinex as datainiexec, CON.dctrpcfiex as datafimexec, ";
        $sql .= " CON.cctrpctpfr as tipoespfontrecur, CON.actrpcnucv as nconvenio, CON.actrpcnuoc as noperacaocredito, CON.nctrpcnmrl as nomerepresenlegal, CON.nctrpccgrl as cargorepresenlegal, ";
        $sql .= " CON.ectrpccpfr as cpfrepresenlegal, CON.nctrpcmlrl as emailrepresenlegal, CON.ectrpctlrl as telrepresenlegal, CON.ectrpcidrl as identidaderepreslegal, CON.nctrpcoerl as orgaoexpedrepreselegal, ";
        $sql .= " CON.nctrpcufrl as ufrgrepresenlegal, CON.nctrpccdrl as cidadedomrepresenlegal, CON.nctrpcedrl as estdomicrepresenlegal, CON.nctrpcnarl as naciorepresenlegal, CON.cctrpcecrl as estacivilrepresenlegal, ";
        $sql .= " CON.nctrpcprrl as profirepresenlegal, CON.nctrpcnmgt as nomegestor, CON.nctrpccpfg as cpfgestor, CON.nctrpcmtgt as matgestor, CON.nctrpcmlgt as emailgestor, CON.ectrpctlgt as fonegestor, ";
        $sql .= " CON.dctrpccada as dtcadastrocontrato,  CON.cctrpciden as seecpfcnpj, CON.ictrpcgrnt as arqgarantiacontratoantigo, ";
        $sql .= " CASE WHEN CON.vctrpceant  IS NULL THEN  CON.vctrpcvlor  WHEN CON.vctrpceant  IS NOT NULL THEN  CON.vctrpceant END AS valortotalcontrato, ";
        $sql .= " CASE WHEN CON.vctrpcvlor  IS NULL THEN  CON.vctrpceant  WHEN CON.vctrpcvlor  IS NOT NULL THEN  CON.vctrpcvlor END AS valororiginal, ";
        $sql .= " CON.nctrpcnmgr as nomearquigarantiacontratoanti, CON.cusupocodi as codeuserresposavelatualizacao, CON.tctrpculat as dtultimaatualizacao, CON.actrpcivie as numdiasentreinivigeneexec, ";
        $sql .= " CON.actrpcfvfe as numdiasentrefimvigeneexec, CON.fctrpcosem as temosemitidacontratoantigo,  CC.ccenpocorg as orgao, orlic.eorglidesc as orgaocontratante, ";
        $sql .= " CASE WHEN CON.ctpcomcodi IS NULL THEN SCC.ctpcomcodi WHEN CON.ctpcomcodi IS NOT NULL THEN CON.ctpcomcodi END as codicompra, CON.ctpcomcodi as origemcontratoantigo, ";
        $sql .= " CC.ccenpounid as unidade, SCC.csolcocodi as codisolicitacao, SCC.asolcoanos as anos, CON.cctrpcopex as opexeccontrato, CON.fctrpcanti as econtratoantigo, ";
        $sql .= " CON.ctipencodi as codiseqtipoencerramento, CON.vctrpcsean as saldoexeccontratoantigo, CON.actrpcnuad as numultimoaditivocontratoantigo, ";
        $sql .= " CON.actrpcnuap as numultimoapostilamentocontratoantigo, CON.nctrpcnmos as nomearquivoanexoos, CON.ictrpcanos as arquivoanexoos, SUBSTRING(CON.ectrpcnumf, position('/' in CON.ectrpcnumf)+1,4)";
        $sql .= " as formula, DADCONT.cdocpcsequ as codisequdoc, DADCONT.edadcosefin as dadossefin, DADCONT.edadcosaj as dadossaj, DADCONT.edadcopref as dadosprefeito, DADCONT.edadcodenca1 as denominacaocargo1, ";
        $sql .= " DADCONT.edadcodesag1 as descagente1, DADCONT.edadcodenca2 as denominacaocargo2, DADCONT.edadcodesag2 as descagente2, DADCONT.cusupocodi as userrespatualizacao, ";
        $sql .= " DADCONT.tdadcoulat as datahoraultimaalteracao, CON.adocpcnupa as numerodeparcelas, CON.adocpcvapa as valordaparcela, cpnccpcodi as categoriaprocesso FROM sfpc.tbcontratosfpc CON inner join sfpc.tbdocumentosfpc DOC on CON.cdocpcsequ=DOC.cdocpcsequ ";
        $sql .= " left outer join SFPC.TBSOLICITACAOCOMPRA SCC on ( con.csolcosequ = SCC.csolcosequ ) ";
        $sql .="  left outer join SFPC.tbcentrocustoportal CC on ( CC.ccenposequ = SCC.ccenposequ ) ";
        $sql .="  left outer join sfpc.tborgaolicitante orlic on ( con.corglicodi= orlic.corglicodi ) ";
        $sql .= " inner join sfpc.tbfornecedorcredenciado forn on ( con.aforcrseq1=forn.aforcrsequ ) ";
        $sql .= " left outer join sfpc.tbdadoscontratantes DADCONT on ( CON.cdocpcsequ=DADCONT.cdocpcsequ ) ";
        $sql .= " where CON.cdocpcsequ=".$CodSequContrato;
        // var_dump($sql);die;
        $resultado = executarSQL($this->conexaoDb, $sql);
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
        return $retorno;
    }

    function GetDadosCategoriaProcesso($cpnccpcodi)
    {
        $sql = "select cp.cpnccpcodi, cp.epnccpnome from sfpc.tbpncpdominiocategoriaprocesso cp where cp.cpnccpcodi = ".$cpnccpcodi." order by cp.cpnccpcodi";
        $resultado = executarSQL($this->conexaoDb, $sql);
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
        return $retorno;
    }

    function GetGestorAlteradoApostilamento($codDoc){
        $sql = "SELECT apost.napostnmgt as nomegestor, apost.napostcpfg as cpfgestor, apost.napostmtgt as matgestor, apost.napostmlgt as emailgestor, apost.eaposttlgt as fonegestor   
                FROM sfpc.tbapostilamento as apost left join sfpc.tbdocumentosfpc doc on apost.cdocpcsequ = doc.cdocpcsequ 
                where apost.CDOCPCSEQ2 = $codDoc and doc.cfasedsequ = 6 and doc.csitdcsequ = 1";

        $resultado = executarSQL($this->conexaoDb, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }
   
    function GetRepresentateAlteradoAdtivo($codDoc){
        $sql = "SELECT ad.naditinmrl, ad.eaditicgrl, ad.eaditicpfr, ad.eaditiidrl, ad.naditioerl, ad.naditiufrl, ad.naditicdrl, ad.naditiedrl, ad.naditinarl, ad.caditiecrl, ad.naditiprrl, ad.naditimlrl, ad.eadititlrl FROM sfpc.tbaditivo as ad left join sfpc.tbdocumentosfpc doc on ad.cdocpcsequ = doc.cdocpcsequ where ad.CDOCPCSEQ1 = $codDoc and doc.cfasedsequ = 4 and doc.ctidocsequ = 2 and doc.csitdcsequ = 1 
        and ad.aaditinuad = (select MAX(adit.aaditinuad) from sfpc.tbaditivo adit left join sfpc.tbdocumentosfpc docm on adit.cdocpcsequ = docm.cdocpcsequ
                                where adit.cdocpcseq1 = ad.cdocpcseq1 and docm.cfasedsequ = 4 and docm.ctidocsequ = 2 and docm.csitdcsequ = 1 )";

        $resultado = executarSQL($this->conexaoDb, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function GetFornecedorAlteradoAdtivo($codDoc){
        $sql = "SELECT forn.nforcrrazs as razao, forn.eforcrlogr as rua, forn.aforcrnume as numero, forn.eforcrcomp as complemento, forn.cforcresta as estado,
                        forn.eforcrbair as bairro, forn.nforcrcida as cidade, forn.cceppocodi as cep, forn.aforcrccpf as cpf, forn.aforcrccgc as cnpj
                FROM sfpc.tbaditivo as ad 
                inner join SFPC.TBFORNECEDORCREDENCIADO as forn on  (forn.AFORCRCCGC = ad.eaditicgcc or forn.AFORCRCCPF = ad.eaditicpfc)
                left join sfpc.tbdocumentosfpc doc on ad.cdocpcsequ = doc.cdocpcsequ 
                where ad.CDOCPCSEQ1 = $codDoc and ad.ctpadisequ = 13 and doc.cfasedsequ = 4 and doc.ctidocsequ = 2 and doc.csitdcsequ = 1 
                    and ad.aaditinuad = (select MAX(adit.aaditinuad) from sfpc.tbaditivo adit left join sfpc.tbdocumentosfpc docm on adit.cdocpcsequ = docm.cdocpcsequ
                                            where adit.cdocpcseq1 = ad.cdocpcseq1 and adit.ctpadisequ = 13 and docm.cfasedsequ = 4 and docm.ctidocsequ = 2 and docm.csitdcsequ = 1 )";

       $resultado = executarSQL($this->conexaoDb, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function GetFornecedorContrato($codDoc){
        $sql  =" SELECT FORCRED.aforcrsequ, FORCRED.nforcrrazs, FORCRED.eforcrlogr,FORCRED.aforcrnume,FORCRED.eforcrcomp,FORCRED.eforcrbair,FORCRED.nforcrcida,FORCRED.aforcrccpf, ";
        $sql .=" FORCRED.aforcrccgc, FORCRED.cforcresta FROM sfpc.tbcontratofornecedor AS CF INNER JOIN sfpc.tbfornecedorcredenciado AS FORCRED ON ( FORCRED.aforcrsequ = CF.aforcrsequ) ";
        $sql .=" WHERE CF.cdocpcsequ =".$codDoc;
        $resultado = executarSQL($this->conexaoDb, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function GetListaGarantiaDocumento(){
        $sql = " SELECT ctipgasequ AS codgarantia, etipgadesc AS descricaogarantia FROM sfpc.tbtipogarantiadocumento WHERE ftipgasitu = 'ATIVO' ";
        $resultado = executarSql($this->conexaoDb, $sql);
        $dadosRetorno = array();
        while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function GetItensContrato($codDoc){
        $sql  = " SELECT citedoorde as ord, eitedoserv as descitem, cservpsequ as codreduzidoserv, cmatepsequ as codreduzidomat, aitedoqtso as qtd, vitedovlun as valorunitario, ";
        $sql .= " vitedovlde as valortotal, eitedomarc as marca, eitedomode as modelo FROM sfpc.tbitemdocumento WHERE cdocpcsequ =".$codDoc;
        $resultado = executarSQL($this->conexaoDb, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function PegaAlteracaoDataContratoPorAditivo($cdocpcseq1){
        $sql ="select ad.daditiinvg, ad.daditifivg, ad.daditiinex, ad.aaditinfev, ad.daditifiex as data_fim_execucao, con.cctrpcopex, ad.aaditiapea as prazo from sfpc.tbaditivo as ad ";
        $sql .=" inner join sfpc.tbcontratosfpc as con on (con.cdocpcsequ = ad.cdocpcseq1) ";
        $sql .= "left join sfpc.tbdocumentosfpc doc on (ad.cdocpcsequ = doc.cdocpcsequ) ";
        $sql .= "where ad.cdocpcseq1 = ".$cdocpcseq1."and  (ad.ctpadisequ = 11 or ad.faditialpz ='SIM')";
        $sql .= "and doc.cfasedsequ = 4 and doc.ctidocsequ = 2 and doc.csitdcsequ = 1 ";
        $sql .= "and ad.aaditinuad = (select MAX(adit.aaditinuad) from sfpc.tbaditivo as adit
        left join sfpc.tbdocumentosfpc docm on (adit.cdocpcsequ = docm.cdocpcsequ)
        where adit.cdocpcseq1 = ad.cdocpcseq1 and (adit.ctpadisequ = 11 or adit.faditialpz ='SIM')
        and docm.cfasedsequ = 4 and docm.ctidocsequ = 2 and docm.csitdcsequ = 1) ";
        $sql .= "order  by ad.cdocpcsequ desc limit 1";

        $resultado = executarSQL($this->conexaoDb, $sql);
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
        if(!empty($retorno)){
            return $retorno;
        }else{
            return false;
        }
    }

    function GetApostilamento($codDoc){
        $sql = " SELECT * FROM sfpc.tbapostilamento WHERE cdocpcseq2=".$codDoc;
        $resultado = executarSQL($this->conexaoDb, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function NumeroApostilamento($codDoc){
        $db = Conexao();
        $sql= "SELECT aapostnuap as numeroapostilamento FROM sfpc.tbapostilamento WHERE cdocpcseq2=".$codDoc;
        $resultado = executarSQL($db, $sql);
        $row = $resultado->fetchRow(DB_FETCHMODE_OBJECT);
        return $row->numeroapostilamento;
    }

    function NumeroAditivo($codDoc){
        $db = Conexao();
        $sql= "SELECT aaditinuad as numeroaditivo FROM sfpc.tbaditivo WHERE cdocpcseq1=".$codDoc;
        $resultado = executarSQL($db, $sql);
        $row = $resultado->fetchRow(DB_FETCHMODE_OBJECT);
        return $row->numeroaditivo;
    }

    function GetMedicao($codDoc){
        $sql = " SELECT * FROM sfpc.tbmedicaocontrato WHERE cdocpcsequ =".$codDoc;
        $resultado = executarSQL($this->conexaoDb, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    #A funcao difere da anterior pode dar problemas
    // function GetSituacaoContrato($faseDocumento,$codSitucao){
    //     if(!empty($codSitucao)){
    //         $sql = "select sitdoc.esitdcdesc from sfpc.tbsituacaodocumento as sitdoc where sitdoc.cfasedsequ =".$faseDocumento." and csitdcsequ =".$codSitucao;
    //         $resultado = executarSql($this->conexaoDb, $sql);
    //         $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
    //         return $retorno;
    //        }else{
    //            return (object) array('esitdcdesc'=> '');
    //        }
    // }

    function GetDocumentosAnexos($codDoc,$sequdocanexo=null,$edcanxnome=null){
        $sql  = " SELECT cdocpcsequ AS sequdocumento, cdcanxsequ AS sequdocanexo, edcanxnome AS nomearquivo, tdcanxcada AS datacadasarquivo, tdcanxulat AS ultimasatualizacao, ";
        $sql .= " cusupocodi as usermod, encode(idcanxarqu,'base64') as arquivo FROM sfpc.tbdocumentoanexo WHERE cdocpcsequ=".$codDoc;
        if(!empty($sequdocanexo)){
            $sql .=" AND cdcanxsequ =".$sequdocanexo;
        }
        if(!empty($edcanxnome)){
            $sql .=" AND edcanxnome ='".$edcanxnome."'";
        }
        $resultado = executarSQL($this->conexaoDb, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function GetDocumentosAnexosAdtivo($codDoc,$sequdocanexo=null,$edcanxnome=null){
        $sql  = " select co.cdocpcsequ AS sequdocumento, cdcanxsequ AS sequdocanexo, edcanxnome AS nomearquivo, ";
        $sql .= " tdcanxcada AS datacadasarquivo, tdcanxulat AS ultimasatualizacao,co.cusupocodi as usermod, ";
        $sql .= " encode(idcanxarqu,'base64') as arquivo from sfpc.tbdocumentoanexo as da inner join sfpc.tbaditivo as ad on ";
        $sql .= " (da.cdocpcsequ = ad.cdocpcsequ) inner join sfpc.tbcontratosfpc as co on (co.cdocpcsequ = ad.cdocpcseq1) ";
        $sql .= " left join sfpc.tbdocumentosfpc doc on (ad.cdocpcsequ = doc.cdocpcsequ) ";
        $sql .= " where co.cdocpcsequ =".$codDoc;
        if(!empty($sequdocanexo)){
            $sql .=" AND cdcanxsequ =".$sequdocanexo;
        }
        if(!empty($edcanxnome)){
            $sql .=" AND edcanxnome ='".$edcanxnome."'";
        }
        $sql .=" and doc.cfasedsequ = 4 and doc.ctidocsequ = 2 and doc.csitdcsequ = 1";
        $resultado = executarSQL($this->conexaoDb, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function GetDocumentosAnexosApostilamentoAlterado($codDoc, $sequdocanexo = null, $edcanxnome = null){
        $sql  = " select ap.cdocpcsequ AS sequdocumento, cdcanxsequ AS sequdocanexo, edcanxnome AS nomearquivo, ";
        $sql .= " tdcanxcada AS datacadasarquivo, tdcanxulat AS ultimasatualizacao, ap.cusupocodi as usermod, ";
        $sql .= " encode(idcanxarqu,'base64') as arquivo from sfpc.tbdocumentoanexo as da inner join sfpc.tbapostilamento as ap on ";
        $sql .= " (da.cdocpcsequ = ap.cdocpcsequ) left join sfpc.tbdocumentosfpc doc on ap.cdocpcsequ = doc.cdocpcsequ ";
        $sql .= "  where ap.cdocpcseq2 =".$codDoc. " and doc.cfasedsequ = 6 and doc.csitdcsequ = 1";
        if(!empty($sequdocanexo)){
            $sql .=" AND cdcanxsequ =".$sequdocanexo;
        }
        if(!empty($edcanxnome)){
            $sql .=" AND edcanxnome ='".$edcanxnome."'";
        }
        
        $resultado = executarSQL($this->conexaoDb, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function GetDocumentosAnexosApostilamento($codDoc,$sequdocanexo=null,$edcanxnome=null){
        $sql  = " select ap.cdocpcsequ AS sequdocumento, cdcanxsequ AS sequdocanexo, edcanxnome AS nomearquivo, ";
        $sql .= " tdcanxcada AS datacadasarquivo, tdcanxulat AS ultimasatualizacao, ap.cusupocodi as usermod, ";
        $sql .= " encode(idcanxarqu,'base64') as arquivo from sfpc.tbdocumentoanexo as da inner join sfpc.tbapostilamento as ap on ";
        $sql .= " (da.cdocpcsequ = ap.cdocpcsequ) left join sfpc.tbdocumentosfpc doc on ap.cdocpcsequ = doc.cdocpcsequ ";
        $sql .= "  where ap.cdocpcseq2 =".$codDoc."and doc.cfasedsequ = 6 and doc.csitdcsequ = 1";
        if(!empty($sequdocanexo)){
            $sql .=" AND cdcanxsequ =".$sequdocanexo;
        }
        if(!empty($edcanxnome)){
            $sql .=" AND edcanxnome ='".$edcanxnome."'";
        }
        $resultado = executarSQL($this->conexaoDb, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }
    
    function getDocumentosFicaisEFical($codDoc){
        $sql  = " SELECT fisc.nfiscdtipo as tipofiscal, fisc.nfiscdnmfs as fiscalnome, fisc.efiscdmtfs as fiscalmatricula, fisc.cfiscdcpff as fiscalcpf, fisc.nfiscdmlfs as fiscalemail, ";
        $sql .= " fisc.efiscdtlfs as fiscaltel, docfi.cdocpcsequ as docsequ, docfi.fdocfisitu as docsituacao, fisc.nfiscdencp as  ent, fisc.efiscdrgic as registro FROM sfpc.tbdocumentofiscalsfpc AS docfi ";
        $sql .= " INNER JOIN sfpc.tbfiscaldocumento AS fisc ON (fisc.cfiscdcpff = docfi.cfiscdcpff)  ";
        $sql .= " WHERE docfi.cdocpcsequ=".$codDoc;
        $resultado = executarSQL($this->conexaoDb, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function getDocumentosFiscaisEFiscalAlterado($codDoc){
        $sql  = " SELECT fisc.nfiscdtipo as tipofiscal, fisc.nfiscdnmfs as fiscalnome, fisc.efiscdmtfs as fiscalmatricula, fisc.cfiscdcpff as fiscalcpf, fisc.nfiscdmlfs as fiscalemail, ";
        $sql .= " fisc.efiscdtlfs as fiscaltel, docfi.cdocpcsequ as docsequ, docfi.fdocfisitu as docsituacao, fisc.nfiscdencp as  ent, fisc.efiscdrgic as registro FROM sfpc.tbdocumentofiscalsfpc AS docfi ";
        $sql .= " INNER JOIN sfpc.tbfiscaldocumento AS fisc ON (fisc.cfiscdcpff = docfi.cfiscdcpff)  ";
        $sql .= " INNER JOIN sfpc.tbdocumentosfpc doc on docfi.cdocpcsequ = doc.cdocpcsequ ";
        $sql .= " WHERE docfi.cdocpcsequ=".$codDoc . " and doc.cfasedsequ = 6 and doc.csitdcsequ = 1";
        
        $resultado = executarSQL($this->conexaoDb, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function GetDocumentosAnexosMedicao($codDoc,$sequdocanexo=null,$edcanxnome=null){
        $sql  = " select cdocpcsequ as sequdocumento, cmedconane as nomearquivo, encode(imedcoanex,'base64') as arquivo,";
        $sql .= "  tmedcoulat as datacadasarquivo, cusupocodi as usermod from sfpc.tbmedicaocontrato where cdocpcsequ =".$codDoc;
        if(!empty($sequdocanexo)){
            $sql .=" AND cdcanxsequ =".$sequdocanexo;
        }
        if(!empty($edcanxnome)){
            $sql .=" AND edcanxnome ='".$edcanxnome."'";
        }
        $resultado = executarSQL($this->conexaoDb, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function getContrato($codCont){
        $db = Conexao();
        $sql = "SELECT CONT.CDOCPCSEQU, CONT.csolcosequ as scc, CONT.vctrpcvlor as valororiginal, CONT.ECTRPCNUMF, CONT.vctrpcvlor as valortotalcontrato, CONT.ECTRPCOBJE, CONT.cctrpcopex, 
            CASE 
                WHEN cont.csolcosequ IS NULL THEN (COALESCE(cont.vctrpcglaa, 0.000) - COALESCE(cont.vctrpceant, 0.000)) 
                WHEN cont.csolcosequ IS NOT NULL THEN (COALESCE(cont.vctrpcvlor, 0.000)) 
            END AS valor_contrato_antigo, 
            CASE 
                WHEN cont.csolcosequ IS NULL THEN (COALESCE(cont.vctrpcglaa, 0.000))
                WHEN cont.csolcosequ IS NOT NULL THEN (COALESCE(cont.vctrpcvlor, 0.000)) 
            END AS VCTRPCGLAA 
            FROM SFPC.TBCONTRATOSFPC CONT WHERE CONT.CDOCPCSEQU = ".$codCont;
        // print_r(($sql));die;
        $resultado = executarSQL($db , $sql);
        $dadosRetorno = array();
        while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function getAditivosContrato($codCont){
        $db = Conexao();
        $sql = "SELECT DOC.CFASEDSEQU as fase, CASE WHEN cctrpcopex = 'M' AND FADITIALPZ = 'SIM' AND DADITIFIEX IS NULL THEN to_char(cast(daditiinex AS DATE) + ((aaditiapea)::text || ' ' || 'MONTHS')::interval, 'DD/MM/YYYY') WHEN cctrpcopex = 'M' AND FADITIALPZ = 'SIM' AND DADITIFIEX IS NOT NULL THEN to_char(DADITIFIEX, 'DD/MM/YYYY') WHEN cctrpcopex = 'D' AND FADITIALPZ = 'SIM' AND DADITIFIEX IS NULL THEN to_char(daditiinex + ((aaditiapea)::text || ' ' || 'DAYS')::interval, 'DD/MM/YYYY') WHEN cctrpcopex = 'D' AND FADITIALPZ = 'SIM' THEN to_char(DADITIFIEX, 'DD/MM/YYYY') END AS data_fim, ADIT.CDOCPCSEQU, ADIT.CDOCPCSEQ1, ECTRPCNUMF, ADIT.XADITIJUST, ADIT.AADITINUAD, SIT.ESITDCDESC,ADIT.DADITIINVG, ADIT.DADITIFIVG, ADIT.DADITIINEX, ADIT.VADITIVALR, ADIT.AADITIAPEA, ADIT.vaditireqc, ADIT.vaditivtad ";
        $sql .= "FROM SFPC.TBCONTRATOSFPC CONT, SFPC.TBADITIVO ADIT, SFPC.TBDOCUMENTOSFPC DOC, SFPC.TBSITUACAODOCUMENTO SIT ";
        $sql .= "WHERE CONT.CDOCPCSEQU = ADIT.CDOCPCSEQ1 ";
        $sql .= "AND ADIT.CDOCPCSEQU = DOC.CDOCPCSEQU ";
        $sql .= "AND DOC.CFASEDSEQU = SIT.CFASEDSEQU ";
        $sql .= "AND DOC.CSITDCSEQU = SIT.CSITDCSEQU ";
        $sql .= "AND CONT.CDOCPCSEQU = " .$codCont . " ORDER BY AADITINUAD ASC";
        $resultado = executarSQL($db, $sql);
        $dadosRetorno = array();
        while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function getApostilamentosContrato($codCont){
            $sql = "SELECT apost.cdocpcsequ, ctpaposequ, aapostnuap, dapostcada, vapostvtap, apost.aforcrsequ, SIT.esitdcdesc as situacao ";
            $sql .= "FROM SFPC.TBAPOSTILAMENTO apost, SFPC.TBDOCUMENTOSFPC DOC, SFPC.TBSITUACAODOCUMENTO SIT ";
            $sql .= "WHERE apost.cdocpcsequ = DOC.cdocpcsequ and CDOCPCSEQ2 = " .$codCont . "  AND DOC.CFASEDSEQU = SIT.CFASEDSEQU AND DOC.CSITDCSEQU = SIT.CSITDCSEQU ORDER BY AAPOSTNUAP ASC";
            // var_dump($sql);die;
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
    }

    function getApostilamentoRCCPDF($codCont){
        $db = Conexao();
        $sql = "SELECT cdocpcseq2, ctpaposequ, dapostcada, vapostvtap, aapostnuap, DOC.cdocpcsequ, napostnmgt, napostcpfg, eaposttlgt, napostmtgt, napostmlgt, eapostmemc, iapostmemc, eapostnagt, iapostaqgt, vapostretr, SIT.CFASEDSEQU FROM SFPC.tbapostilamento apost, SFPC.TBDOCUMENTOSFPC DOC, SFPC.TBSITUACAODOCUMENTO SIT WHERE apost.cdocpcsequ = DOC.cdocpcsequ AND DOC.cdocpcsequ = ".$codCont;
        $sql .= " AND DOC.CFASEDSEQU = SIT.CFASEDSEQU ";
        $sql .= " AND DOC.CSITDCSEQU = SIT.CSITDCSEQU ";
        
        $resultado = executarSQL($db, $sql);
        $dadosRetorno = array();
        while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function getApostilamentoNome($idApostilamento){
        $db = Conexao();
        $sql = "SELECT etpapodesc FROM sfpc.tbtipoapostilamento WHERE ctpaposequ = " . $idApostilamento;
        $resultado = executarSQL($db, $sql);
        
        $dadosRetorno = array();
        while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function getMedicaoContrato($codCont, $codRegistro){
        $db = Conexao();
        $sql = "SELECT MEDI.CDOCPCSEQU, CONT.ECTRPCNUMF, MEDI.AMEDCONUME, MEDI.CMEDCOSEQU, MEDI.DMEDCOINIC, MEDI.DMEDCOFINL, MEDI.CMEDCONANE, encode(MEDI.imedcoanex,'base64') as arquivo, MEDI.VMEDCOVALM, MEDI.EMEDCOOBSE ";
        $sql .= "FROM SFPC.TBCONTRATOSFPC CONT ";
        $sql .= "INNER JOIN SFPC.TBMEDICAOCONTRATO MEDI ";
        $sql .= "ON CONT.CDOCPCSEQU = MEDI.CDOCPCSEQU WHERE CONT.CDOCPCSEQU = " .$codCont. " AND MEDI.CMEDCOSEQU = " .$codRegistro;

        $resultado = executarSQL($db, $sql);
        $dadosRetorno = array();
        while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function getMedicoesContrato($codCont){
        $sql = "SELECT CONT.ECTRPCNUMF, MEDI.AMEDCONUME, MEDI.CMEDCOSEQU, MEDI.DMEDCOINIC, MEDI.DMEDCOFINL, MEDI.VMEDCOVALM "; //, MEDI.DMEDCOPAPR "; Retirado pela CR #244301
        $sql .= "FROM SFPC.TBCONTRATOSFPC CONT ";
        $sql .= "INNER JOIN SFPC.TBMEDICAOCONTRATO MEDI ";
        $sql .= "ON CONT.CDOCPCSEQU = MEDI.CDOCPCSEQU WHERE CONT.CDOCPCSEQU = " .$codCont . " ORDER BY MEDI.AMEDCONUME ASC";
        $resultado = executarSQL($this->conexaoDb, $sql);
        $dadosRetorno = array();
        while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function GetDescricaoMaterial($codMaterial){
        $db = Conexao();
        $sql = " SELECT ematepdesc FROM sfpc.tbmaterialportal WHERE cmatepsequ =".$codMaterial;
        $resultado = executarSQL($db, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function GetDescricaoServicos($codServico){
        $db = Conexao();
        $sql = " SELECT eservpdesc FROM sfpc.tbservicoportal WHERE cservpsequ =".$codServico;
        $resultado = executarSQL($db, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function getAditivoContrato($codCont, $codRegistro){
        $db = Conexao();
        //to_char((daditiinex + aaditiapea), 'DD/MM/YYYY') as 
        $sql  = "SELECT DOC.CFASEDSEQU as fase, cctrpcopex, CASE WHEN cctrpcopex = 'M' AND DADITIFIEX IS NULL THEN to_char(cast(daditiinex AS DATE) + ((aaditiapea)::text || ' ' || 'MONTHS')::interval, 'DD/MM/YYYY') WHEN cctrpcopex = 'M' AND DADITIFIEX IS NOT NULL THEN to_char(DADITIFIEX, 'DD/MM/YYYY') WHEN cctrpcopex = 'D' AND DADITIFIEX IS NULL THEN to_char(daditiinex + ((aaditiapea)::text || ' ' || 'DAYS')::interval, 'DD/MM/YYYY') WHEN cctrpcopex = 'D' THEN to_char(DADITIFIEX, 'DD/MM/YYYY') END AS data_fim, CONT.CDOCPCSEQU, CDOCPCSEQ1, CTPADISEQU, AADITINUAD, XADITIJUST, DADITICADA, AADITIAPEA, ";
        $sql .= "FADITIALPZ, FADITIALVL, FADITIALCT, CADITITALV, VADITIREQC, VADITIVTAD, DADITIINVG, ADIT.AFORCRSEQU, ";
        $sql .= "DADITIFIVG, DADITIINEX, DADITIFIEX, XADITIOBSE, NADITINMRL, EADITICGRL, EADITICPFR, EADITIIDRL, ";
        $sql .= "NADITIOERL, NADITIUFRL, NADITICDRL, NADITIEDRL, NADITINARL, CADITIECRL, ";
        $sql .= "NADITIPRRL, NADITIMLRL, EADITITLRL, NADITIRAZS, EADITILOGR, EADITICOMP, EADITIBAIR, NADITICIDA, CADITIESTA, EADITICPFC, EADITICGCC ";
        $sql .= "FROM SFPC.TBCONTRATOSFPC CONT, SFPC.TBADITIVO ADIT, SFPC.TBDOCUMENTOSFPC DOC, SFPC.TBSITUACAODOCUMENTO SIT ";
        $sql .= "WHERE CONT.CDOCPCSEQU = ADIT.CDOCPCSEQ1 ";
        $sql .= "AND ADIT.CDOCPCSEQU = DOC.CDOCPCSEQU ";
        $sql .= "AND DOC.CFASEDSEQU = SIT.CFASEDSEQU ";
        $sql .= "AND DOC.CSITDCSEQU = SIT.CSITDCSEQU ";
        $sql .= "AND CDOCPCSEQ1 = " .$codCont . " AND ADIT.CDOCPCSEQU = " .$codRegistro . " ORDER BY 3";
        // print_r($sql);die;
        $resultado = executarSQL($db, $sql);
        $dadosRetorno = array();
        while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
            $dadosRetorno = $retorno;
        }
        return $dadosRetorno;
    }

    function situacaoAditivo(){
        $db = Conexao();
        $sql = "select cfasedsequ, csitdcsequ, esitdcdesc FROM sfpc.tbsituacaodocumento where cfasedsequ in (1, 4) and csitdcsequ = 1";
        $resultado = executarSQL($db, $sql);
        
        $dadosRetorno = array();
        while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function getTiposAditivoPorCod($cod){
        $db = Conexao();
        $sql = "SELECT etpadidesc FROM sfpc.tbtipoaditivo where ctpadisequ = ". $cod ." and ftpadisitu = 'ATIVO'";
        $resultado = executarSQL($db, $sql);
        
        $dadosRetorno = array();
        while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function getValorGlobalContrato($codCont){
        $db = Conexao();
        $sql = "SELECT CASE  WHEN CONT.VCTRPCGLAA IS NULL THEN vctrpcvlor  ";
        $sql .= " WHEN CONT.VCTRPCGLAA IS NOT NULL THEN CONT.VCTRPCGLAA ";
        $sql .= " END as valor_global ";
        $sql .= "FROM SFPC.TBCONTRATOSFPC CONT ";
        $sql .= "WHERE CONT.CDOCPCSEQU = " .$codCont;

        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);

        if(!empty($retorno->valor_global)){
            return number_format((floatval($retorno->valor_global)),4,',','.');
        }else{
            return number_format((floatval('0.0000')),4,',','.');
        }
    }

    function GetValorTotalAdtivo($codDoc){
        $db = Conexao();
        $sql = "select sum(case when adit.vaditivalr is null  then adit.vaditivtad else ";
        $sql .= "adit.vaditivalr end) as vtaditivo  from sfpc.tbaditivo adit ";
        $sql .= "left join sfpc.tbdocumentosfpc doc on adit.cdocpcsequ = doc.cdocpcsequ ";
        $sql .= "where adit.CDOCPCSEQ1 =".$codDoc;
        $sql .= " and doc.cfasedsequ = 4 and doc.ctidocsequ = 2 and doc.csitdcsequ = 1";
        $resultado = executarSQL($db, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function GetValorTotalApostilamento($codDoc){
        $db = Conexao();
        $sql = "select sum(vapostvtap) as vtapost from sfpc.tbapostilamento apost left join sfpc.tbdocumentosfpc doc "; 
        $sql .=" on apost.cdocpcsequ = doc.cdocpcsequ where apost.CDOCPCSEQ2 =".$codDoc ."and cfasedsequ = 6 and doc.csitdcsequ = 1";
        $resultado = executarSQL($db, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    function getValorTotalMedicao($codDoc){
        $db = Conexao();
        $sql = "select COALESCE(sum(vmedcovalm),0.000) AS totalmedicao from sfpc.tbmedicaocontrato where cdocpcsequ = $codDoc";
        $resultado = executarSQL($db,$sql);
        
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
        if(!empty($retorno->totalmedicao)){
            return number_format((floatval($retorno->totalmedicao)),4,',','.');
        }else{
            return number_format((floatval('0.0000')),4,',','.');
        }
    }
    
    function date_transform($data,$today = false,$separador="/"){
        $dataBr = '/^(0[1-9]|[1-2][0-9]|3[0-1])[\/](0[1-9]|1[0-2])[\/](19|20)[0-9]{2}$/';
        $dataSql = '/^(19|20)[0-9]{2}[\-](0[1-9]|1[0-2])[\-](0[1-9]|[1-2][0-9]|3[0-1])$/';
        if(preg_match($dataSql,$data,$retorno)){
            $date = explode('-', $retorno[0]);
            if($separador == ""){
                $date_transform = $date[2].'/'.$date[1].'/'.$date[0];
            }else{
                $date_transform = $date[2].$separador.$date[1].$separador.$date[0];
            }
            return $date_transform;
        }else if(preg_match($dataBr,$data,$retorno)){
            $date = explode('/', $retorno[0]);
            $date_transform = $date[2].'-'.$date[1].'-'.$date[0];
            return $date_transform;
        }elseif($data == "" && $today == true){
            return date("d/m/Y");
        }else{
            return $data;
        }
    }

    function AlterarAditivoContrato($dadosSalvar){

        $cusupocodi = $_SESSION['_cusupocodi_'];

        $sql = "UPDATE SFPC.TBADITIVO ";
        $sql .= "SET DADITIINVG = '".$dadosSalvar['daditiinvg']."', DADITIFIVG = '" . $dadosSalvar['daditifivg'] ."', DADITIINEX = '".$dadosSalvar['daditiinex']."', DADITIFIEX = '".$dadosSalvar['daditifiex'];
        $sql .= "' FROM SFPC.TBCONTRATOSFPC CONT WHERE CONT.CDOCPCSEQU = CDOCPCSEQ1 ";
        $sql .= " AND  CONT.ECTRPCNUMF = '" . $dadosSalvar['ectrpcnumf'] ."' AND AADITINUAD = '".$dadosSalvar['aaditinuad']."'";

        $resultado = executarSQL($this->conexaoDb, $sql);
        
        if (!empty($resultado)) {
            return $resultado;
        }
        return false;
    }

    //Madson| Funções de Fiscal de contrato, Usados em Contrato: inserir e Manter; Apostilamento: Incluir e Manter;
    public function GetFiscal($cpf,$tipo=null,$ordenaAlfabetico = null){
        $sql="SELECT * FROM sfpc.tbfiscaldocumento WHERE 1=1 ";
        if(!empty($cpf)){
            $sql .=" AND cfiscdcpff ='".$cpf."'";
        }
        if(!empty($tipo)){
            $sql .=" AND nfiscdtipo='".$tipo."'";
        }
        if($ordenaAlfabetico == true){
            $sql .=" Order by nfiscdnmfs";
        }
        $resultado = executarSQL($this->conexaoDb, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;

    }
    
    public function insertFiscal($dados){

		// var_dump($dados);die;
		 $sql = " INSERT INTO sfpc.tbfiscaldocumento (cfiscdcpff,nfiscdnmfs,ffiscdsitu,efiscdmtfs,nfiscdtipo,nfiscdencp,efiscdrgic,nfiscdmlfs,efiscdtlfs,cusupocodi,tfiscdulat) ";
		 $sql .= " VALUES ('".$dados['cfiscdcpff']."','".$dados['nfiscdnmfs']."','ATIVO','','".$dados['nfiscdtipo']."','".$dados['nfiscdencp']."','".$dados['efiscdrgic']."','".$dados['nfiscdmlfs']."','".$dados['efiscdtlfs']."',".$dados['cusupocodi'].",'".DATE('Y-m-d')."')";
		 $resultado = executarSQL($this->conexaoDb, $sql);
		 if(!empty($resultado)){
			 return $resultado;
		 }else{
			 return false;
		 }
	}

    public function VerificaSeFiscalEstaEmAlgumDocumento($cfiscdcpff){
        $sql = " SELECT * FROM sfpc.tbdocumentofiscalsfpc WHERE  cfiscdcpff='".$cfiscdcpff."'";
        $resultado = executarSQL($this->conexaoDb, $sql);
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
        if(!empty($retorno)){
            return $retorno;
        }else{
            return false;
        }
    }

    public function DeletaFiscal($cfiscdcpff){
        $sql = "DELETE FROM sfpc.tbfiscaldocumento  WHERE  cfiscdcpff='".$cfiscdcpff."'";
        $resultado = executarSQL($this->conexaoDb, $sql);
        if(!empty($resultado)){
            return $resultado;
        }else{
            return false;
        }
    }

    public function UpdateFiscal($dados){
        $sql = "UPDATE sfpc.tbfiscaldocumento SET nfiscdnmfs='".$dados['nfiscdnmfs']."',";
        $sql .= " nfiscdtipo='".$dados['nfiscdtipo']."', nfiscdencp='".$dados['nfiscdencp']."', efiscdrgic='".$dados['efiscdrgic']."', nfiscdmlfs='".$dados['nfiscdmlfs']."', ";
        $sql .= " efiscdtlfs='".$dados['efiscdtlfs']."', cusupocodi=".$dados['cusupocodi'].",tfiscdulat='".DATE('Y-m-d')."' WHERE cfiscdcpff = '".$dados['cfiscdcpff']."'";
        $resultado = executarSQL($this->conexaoDb, $sql);
        if(!empty($resultado)){
            return $resultado;
        }else{
            return false;
        }
    }
     //======================================================================================================================
    //essa função é para pesquisar qual fornecedor deve ser colocado a execução do aditivo do tipo alteração de fornecedor 
    //======================================================================================================================
    public function ValidaOFornecedorUpdateContratoadtivo($cdocpcsequ) {
       
        $sql = "SELECT forn.aforcrsequ AS seqfornecedor, forn.nforcrrazs AS razao, forn.eforcrlogr AS rua, forn.aforcrnume AS numero, ";
        $sql .= "forn.eforcrcomp AS complemento, forn.cforcresta AS estado, forn.eforcrbair AS bairro, forn.nforcrcida AS cidade, ";
        $sql .= "forn.cceppocodi AS cep, forn.aforcrccpf AS cpf, forn.aforcrccgc AS cnpj, aforcrtels AS telefone, ad.daditicada as data_cadastro ";
        $sql .= "FROM sfpc.tbaditivo AS ad INNER JOIN SFPC.TBFORNECEDORCREDENCIADO AS forn ON ( forn.aforcrsequ = ad.aforcrsequ ) ";
        $sql .= "LEFT JOIN sfpc.tbdocumentosfpc doc ON ad.cdocpcsequ = doc.cdocpcsequ WHERE ad.CDOCPCSEQ1 =".$cdocpcsequ." AND ad.ctpadisequ = 13 ";
        $sql .= "AND doc.cfasedsequ = 4 AND doc.ctidocsequ = 2 AND doc.csitdcsequ = 1 AND ad.aaditinuad = ( SELECT MAX( adit.aaditinuad ) FROM ";
        $sql .= "sfpc.tbaditivo adit LEFT JOIN sfpc.tbdocumentosfpc docm ON adit.cdocpcsequ = docm.cdocpcsequ WHERE adit.cdocpcseq1 = ad.cdocpcseq1 ";
        $sql .= "AND adit.ctpadisequ = 13 AND docm.cfasedsequ = 4 AND docm.ctidocsequ = 2 AND docm.csitdcsequ = 1 )";

        $resultado = executarSQL($this->conexaoDb, $sql);
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
        if(!empty($retorno)){
            return $retorno;
        }else{
            return false;
        }
    }

     //================================================================================================================================
    //essa função é para pesquisar qual fornecedor deve ser colocado na implantação do apostilamento do tipo alteração de fornecedor 
    //=================================================================================================================================

    public function ValidaOFornecedorUpdateContratoapostilamento($cdocpcsequ) {

        $sql = "SELECT forn.aforcrsequ AS seqfornecedor, forn.nforcrrazs AS razao, forn.eforcrlogr AS rua, forn.aforcrnume AS numero, ";
        $sql .= "forn.eforcrcomp AS complemento, forn.cforcresta AS estado, forn.eforcrbair AS bairro, forn.nforcrcida AS cidade, ";
        $sql .= "forn.cceppocodi AS cep, forn.aforcrccpf AS cpf, forn.aforcrccgc AS cnpj, aforcrtels AS telefone, apost.dapostcada as data_cadastro ";
        $sql .= "FROM sfpc.tbapostilamento as apost INNER JOIN SFPC.TBFORNECEDORCREDENCIADO AS forn ON ( forn.aforcrsequ = apost.aforcrsequ ) ";
        $sql .= "LEFT JOIN sfpc.tbdocumentosfpc doc ON doc.cdocpcsequ = apost.cdocpcsequ WHERE apost.cdocpcseq2 =".$cdocpcsequ." AND apost.ctpaposequ = 5 ";
        $sql .= "AND doc.cfasedsequ = 6 AND doc.ctidocsequ = 3 AND doc.csitdcsequ = 1 AND apost.aapostnuap = ( SELECT MAX( apo.aapostnuap ) ";
        $sql .= "FROM sfpc.tbapostilamento as apo LEFT JOIN sfpc.tbdocumentosfpc docm ON apo.cdocpcsequ = docm.cdocpcsequ WHERE apo.cdocpcseq2 = apost.cdocpcseq2 ";
        $sql .= "AND apo.ctpaposequ = 5 AND docm.cfasedsequ = 6 AND docm.ctidocsequ = 3 AND docm.csitdcsequ = 1 )";
        
        $resultado = executarSQL($this->conexaoDb, $sql);
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
        if(!empty($retorno)){
            return $retorno;
        }else{
            return false;
        }
    }

    //================================================================================================================================
    // Atualiza os dados do fornecedor no contrato
    //=================================================================================================================================

    public function updateFornecedorContrato($dados) {
        if(!empty($dados['cdocpcseq2'])){
            $aforcrsequ = !empty($dados['aforcrsequ']) ? "aforcrsequ = ".$dados['aforcrsequ'] : '';
            $ectrpcraza = !empty($dados['ectrpcraza']) ? ", ectrpcraza ='".$dados['ectrpcraza']."' " : '';
            $cctrpcccep = !empty($dados['cctrpcccep']) ? ", cctrpcccep =".$dados['cctrpcccep']."" : '';
            $ectrpclogr = !empty($dados['ectrpclogr']) ? ", ectrpclogr = '".$dados['ectrpclogr']."' " : '';
            $actrpcnuen = !empty($dados['actrpcnuen']) ? ", actrpcnuen =".$dados['actrpcnuen']." " : '';
            $ectrpccomp = !empty($dados['ectrpccomp']) ? ", ectrpccomp ='".$dados['ectrpccomp']."' " : '';
            $ectrpcbair = !empty($dados['ectrpcbair']) ? ", ectrpcbair ='".$dados['ectrpcbair']."' " : '';
            $nctrpccida = !empty($dados['nctrpccida']) ? ", nctrpccida ='".$dados['nctrpccida']."' " : '';
            $cctrpcesta = !empty($dados['cctrpcesta']) ? ", cctrpcesta ='".$dados['cctrpcesta']."' " : '';
            $ectrpctlct = !empty($dados['ectrpctlct']) ? ", ectrpctlct ='".$dados['ectrpctlct']."' " : '';
            $sql = "UPDATE sfpc.tbcontratosfpc SET ".$aforcrsequ.$ectrpcraza.$cctrpcccep ;
            $sql .= $ectrpclogr.$actrpcnuen.$ectrpccomp.$ectrpcbair;
            $sql .= $nctrpccida.$cctrpcesta.$ectrpctlct;
            $sql .= " where cdocpcsequ =".$dados['cdocpcseq2'];

            $resultado = executarSQL($this->conexaoDb, $sql);
            if(!empty($resultado)){
                return $resultado;
            }else{
                return false;
            }
        } else {
            return false;
        }
    }
}