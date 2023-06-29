<?php
  session_start(); 
  require_once dirname(__FILE__) . '/../funcoes.php';
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: CadContratoManterEspecial.php
# Autor:    Eliakim Ramos | João Madson
# Data:     11/12/2019
# -------------------------------------------------------------------------
    
    Class ContratoManterEspecial {
        public $conexaoDb;

        public function __construct(){
            $this->conexaoDb = conexao();
        }
       
          /**
         * Função que impede scripts maliciosos de sql
         * @param object $str
         * @return
         */
        public function anti_injection($sql)
        {
            // remove palavras que contenham sintaxe sql
            preg_match("/(from|select|insert|delete|where|drop table|show tables|#|\*|--|\\\\)/",$sql,$matches);
            $sql = @preg_replace($matches,"",$sql);
            $sql = trim($sql);//limpa espaços vazio
            $sql = strip_tags($sql);//tira tags html e php
            $sql = addslashes($sql);//Adiciona barras invertidas a uma string
            return $sql;
        }
        public function GetFornecedor($CPF,$CNPJ){
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

        public function GetSituacaoFornecedor($codFornecedor){
           if(!empty($codFornecedor)){
            $sql       = "SELECT	B.EFORTSDESC ";
			$sql      .=	" FROM SFPC.TBFORNSITUACAO A, SFPC.TBFORNECEDORTIPOSITUACAO B ";
			$sql      .=	" WHERE  A.CFORTSCODI = B.CFORTSCODI ";			
			$sql      .= " AND A.AFORCRSEQU = ".$codFornecedor." ";
            $sql      .=	" ORDER BY A.TFORSIULAT DESC --Garantir que a última modificação da data de situação mais recente esteja na 1a linha ";
            $resultado = executarSql($this->conexaoDb, $sql);
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            return $retorno;
           }else{
               return (object) array('efortsdesc'=> 'Codigo do fornecedor é invalido.');
           }
        }

        public function GetOrgao(){
            $cusupocodi = $_SESSION['_cusupocodi_'];
            $sql  = "SELECT DISTINCT	org.corglicodi, org.eorglidesc ";
            $sql .= " FROM	sfpc.tborgaolicitante org "; 
            $sql .= " INNER JOIN sfpc.tbcentrocustoportal AS CentroCusto ON (org.corglicodi = CentroCusto.corglicodi) ";
            $sql .= " INNER JOIN      sfpc.tbusuariocentrocusto AS UsuarioCusto ON (UsuarioCusto.ccenposequ = CentroCusto.ccenposequ) ";
            $sql .= " WHERE			org.forglisitu = 'A' ";
            $sql .= " AND UsuarioCusto.cusupocodi = $cusupocodi AND UsuarioCusto.fusucctipo = 'C' ";
            $sql .= " ORDER BY		org.eorglidesc ASC";
             $resultado = executarSQL($this->conexaoDb, $sql);
            $dados = array();
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dados[] = $retorno;
            }
            return $dados;
        }

        public function GetTipoCompra($codCompra){
            if(!empty($codCompra)){
                $sql = "select tc.etpcomnome from SFPC.tbtipocompra as tc where tc.ctpcomcodi =".$codCompra;
                $resultado = executarSql($this->conexaoDb, $sql);
                $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
                return $retorno;
               }else{
                   return (object) array('etpcomnome'=> '');
               }
        }
        public function ListTipoCompra(){
                $sql = "select ctpcomcodi,tc.etpcomnome from SFPC.tbtipocompra as tc ";
                $resultado = executarSql($this->conexaoDb, $sql);
                $dadosRetorno = array();
                while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                    $dadosRetorno[] = $retorno;
                }
                return $dadosRetorno;
        }

        public function GetSituacaoContrato($faseDocumento,$codSitucao){
            if(!empty($codSitucao)){
                $sql = "select sitdoc.esitdcdesc from sfpc.tbsituacaodocumento as sitdoc where sitdoc.cfasedsequ =".$faseDocumento." and csitdcsequ =".$codSitucao;
                $resultado = executarSql($this->conexaoDb, $sql);
                $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
                return $retorno;
               }else{
                   return (object) array('esitdcdesc'=> '');
               }
        }

        public function Pesquisar($dados){ //orlic.eorglidesc
            $cusupocodi = $_SESSION['_cusupocodi_'];
            $sql  = "select con.cdocpcsequ,  doc.cfasedsequ,  con.ectrpcnumf,  con.aforcrsequ, con.ectrpcobje, con.ctpcomcodi,  forn.aforcrsequ, ";
            $sql .= " forn.aforcrccgc, forn.aforcrccpf, forn.nforcrrazs,  SCC.csolcocodi, SCC.asolcoanos, CC.ccenpocorg, CC.ccenpounid, SCC.ctpcomcodi ";
            $sql .= " from sfpc.tbcontratosfpc as con inner join sfpc.tbdocumentosfpc as doc on ( con.cdocpcsequ=doc.cdocpcsequ ) ";
            $sql .= " inner join sfpc.tbfornecedorcredenciado forn on ( con.aforcrsequ=forn.aforcrsequ ) ";
            $sql .="  left outer join SFPC.TBSOLICITACAOCOMPRA SCC on ( con.csolcosequ = SCC.csolcosequ ) ";
            $sql .="  left outer join SFPC.tbcentrocustoportal CC on CC.ccenposequ = SCC.ccenposequ ";
            // $sql .= " INNER JOIN      sfpc.tbusuariocentrocusto AS UsuarioCusto ON (UsuarioCusto.ccenposequ = CC.ccenposequ) ";
            $sql .= " left outer join sfpc.tborgaolicitante orlic on ( con.corglicodi= orlic.corglicodi ) ";
            $sql .= "  where orlic.corglicodi = CC.corglicodi ";
            // $sql .= " AND UsuarioCusto.cusupocodi = $cusupocodi AND UsuarioCusto.fusucctipo = 'C' ";
            if(!empty($dados['numerocontratoano'])){
                $sql.=" and con.ectrpcnumf ='".$dados['numerocontratoano']."' ";
            }
            if(!empty($dados['Orgao'])){
                $sql.=" AND con.corglicodi =".$dados['Orgao']."  ";
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
            // $sql .= " order by orlic.eorglidesc asc, SUBSTRING(con.ectrpcnumf, position('/' in con.ectrpcnumf)+1, 4) desc, con.actrpcnumc desc ";
            $sql .= " order by  SUBSTRING(con.ectrpcnumf, position('/' in con.ectrpcnumf)+1, 4) desc, con.actrpcnumc desc ";
            //   print($sql);die;
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosPesquisa = array();
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosPesquisa[] = $retorno;
            }
            return $dadosPesquisa;
        }
        
        function getOrgaoUsuarioLogadoContratos($db)
        {
            $cusupocodi = $_SESSION['_cusupocodi_'];
            $orgaos = array();
        
            $sql = " SELECT distinct Orgao.corglicodi FROM sfpc.tbusuariocentrocusto AS UsuarioCusto
                INNER JOIN sfpc.tbcentrocustoportal AS CentroCusto ON UsuarioCusto.ccenposequ = CentroCusto.ccenposequ
                INNER JOIN sfpc.tborgaolicitante AS Orgao ON Orgao.corglicodi = CentroCusto.corglicodi
                WHERE UsuarioCusto.cusupocodi = $cusupocodi AND UsuarioCusto.fusucctipo = 'C' ";
            
            $resultado = executarSQL($db, $sql);
            
            while ($resultado->fetchInto($orgao, DB_FETCHMODE_OBJECT)) {
                $orgaos[] = $orgao->corglicodi;
            }
        
            return $orgaos;
        }
        public function PesquisarSCC($dados){
            
            $sql =  "select distinct scc.csolcosequ, scc.ctpcomcodi
                    from  sfpc.tbcentrocustoportal as cc, sfpc.tbsolicitacaocompra as scc
                    where scc.csitsocodi = 4";

            $corglicodi = $this->getOrgaoUsuarioLogadoContratos($this->conexaoDb);
            if(!empty($corglicodi)){
                $sql .= " and scc.corglicodi in (".implode(",", $corglicodi).")";
            }
            if(!empty($dados['dataIni'])){
                $sql .= " and scc.tsolcodata >= ".$dados['dataIni'];
            }
            if(!empty($dados['dataFim'])){
                $sql .= " and scc.tsolcodata <= ".$dados['dataFim'];
            }
            if(!empty($dados['CodTipoCompra'])){
                $sql .= " and scc.ctpcomcodi = ".$dados['CodTipoCompra'];
            }
            if(!empty($dados['numeroScc'])){
                $asolcoanos = substr($dados['numeroScc'], -4);   //valores a partir da barra
                $csolcocodi = substr($dados['numeroScc'], -8 , -4);
                $ccenpocorg = substr($dados['numeroScc'], 0, 2);
                $ccenpounid = substr($dados['numeroScc'], -10, -8);
                   
                $sql .= " and scc.asolcoanos = $asolcoanos
                          and scc.csolcocodi = $csolcocodi
                          and cc.ccenpocorg = $ccenpocorg
                          and cc.ccenpounid = $ccenpounid";
            }
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosPesquisa = array();
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosScc = $this->getDadosScc($retorno->csolcosequ, $retorno->ctpcomcodi);          
                if($retorno->ctpcomcodi == 2){ //mais de um fornecedor
                    for($i=0; $i < count($dadosScc); $i++){
                        $dadosPesquisa[] = (object) array(
                                'csolcosequ'=> $dadosScc[$i]->csolcosequ,
                                'csolcocodi'=> $dadosScc[$i]->csolcocodi,
                                'asolcoanos'=> $dadosScc[$i]->asolcoanos,
                                'ctpcomcodi'=> $dadosScc[$i]->ctpcomcodi,
                                'ccenpocorg'=> $dadosScc[$i]->ccenpocorg,
                                'ccenpounid'=> $dadosScc[$i]->ccenpounid,
                                'nforcrrazs'=> $dadosScc[$i]->nforcrrazs,
                                'aforcrccgc'=> $dadosScc[$i]->aforcrccgc,
                                'aforcrccpf'=> $dadosScc[$i]->aforcrccpf,
                                'aforcrsequ'=> $dadosScc[$i]->aforcrsequ,
                                'esolcoobje'=> $dadosScc[$i]->esolcoobje,
                                'corglicodi'=> $dadosScc[$i]->corglicodi

                        );
                    }
                }else{ //Quando tem apenas um fornecedor para a scc
                    $dadosPesquisa[] = (object) array(
                            'csolcosequ'=> $dadosScc[0]->csolcosequ,
                            'csolcocodi'=> $dadosScc[0]->csolcocodi,
                            'asolcoanos'=> $dadosScc[0]->asolcoanos,
                            'ccenpocorg'=> $dadosScc[0]->ccenpocorg,
                            'ccenpounid'=> $dadosScc[0]->ccenpounid,
                            'nforcrrazs'=> $dadosScc[0]->nforcrrazs,
                            'aforcrccgc'=> $dadosScc[0]->aforcrccgc,
                            'aforcrccpf'=> $dadosScc[0]->aforcrccpf,
                            'ctpcomcodi'=> $dadosScc[0]->ctpcomcodi,
                            'aforcrsequ'=> $dadosScc[0]->aforcrsequ,
                            'esolcoobje'=> $dadosScc[0]->esolcoobje,
                            'corglicodi'=> $dadosScc[0]->corglicodi

                    );
                }               
            }
           
            return $dadosPesquisa;
        }

        function getDadosScc($csolcosequ, $ctpcomcodi){
            
            if($ctpcomcodi == 2){    
                $sql = " Select distinct forn.aforcrccgc, 
                forn.aforcrccpf, CC.ccenpocorg, 
                CC.ccenpounid, forn.nforcrrazs,
                scc.csolcosequ, scc.csolcocodi, 
                scc.asolcoanos, scc.ctpcomcodi, 
                forn.aforcrsequ, scc.corglicodi
                from sfpc.tbsolicitacaocompra as scc 
                inner join sfpc.tbsolicitacaolicitacaoportal as scclic on scclic.csolcosequ = scc.csolcosequ 
                inner join SFPC.tbcentrocustoportal as CC on CC.ccenposequ = scc.ccenposequ
                inner join sfpc.tbitemlicitacaoportal as iteml on iteml.clicpoproc = scclic.clicpoproc 
                and iteml.alicpoanop = scclic.alicpoanop 
                and iteml.cgrempcodi = scclic.cgrempcodi
                and iteml.ccomlicodi = scclic.ccomlicodi 
                and iteml.corglicodi = scclic.corglicodi 
                inner join sfpc.tbfornecedorcredenciado as forn on forn.aforcrsequ = iteml.aforcrsequ 
                Where scc.ctpcomcodi = $ctpcomcodi
                and scc.csolcosequ = $csolcosequ";
            }else{
                $sql  =    "select distinct scc.csolcosequ, scc.csolcocodi, scc.asolcoanos, CC.ccenpocorg, CC.ccenpounid, forn.nforcrrazs, 
                            forn.aforcrccgc, forn.aforcrccpf, scc.ctpcomcodi, forn.aforcrsequ, scc.corglicodi
                            from sfpc.tbsolicitacaocompra as scc 
                            inner join sfpc.tbitemsolicitacaocompra as iscc on iscc.csolcosequ = scc.csolcosequ
                            inner join SFPC.tbcentrocustoportal as CC on CC.ccenposequ = scc.ccenposequ
                            inner join sfpc.tbfornecedorcredenciado as forn on forn.aforcrsequ = iscc.aforcrsequ
                            where scc.csolcosequ = $csolcosequ";          
            }
            $resultado = executarSQL($this->conexaoDb, $sql);
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){;
                
                $dadosResultado[] = $retorno;

            }
            return $dadosResultado;
        }
        //Função de busca dos dados do fornecedor através do cód sequencial da scc.
        function getFornecedorSeq($csolcosequ){
            $db = conexao();
                $sql = "select nforcrrazs, forn.eforcrlogr, forn.aforcrnume, forn.eforcrcomp, forn.eforcrbair,
                forn.nforcrcida, forn.cforcresta
                from sfpc.tbfornecedorcredenciado as forn 
                inner join sfpc.tbitemsolicitacaocompra as iscc 
                on iscc.aforcrsequ = forn.aforcrsequ              
                where iscc.csolcosequ =  $csolcosequ";
            
            $resultado = executarSQL($db, $sql);
                    while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                        $dadosPesquisa = $retorno;
                        
                    }
                    return $dadosPesquisa;
        }
        
        //Função de busca dos dados do fornecedor através do cód sequencial.
        function getFornecedorDados($aforcrsequ){
            $db = conexao();
            $sql = "select forn.nforcrrazs, forn.eforcrlogr, forn.aforcrnume, forn.aforcrtels,
                    forn.eforcrcomp, forn.eforcrbair, forn.nforcrcida, forn.cforcresta, forn.cceppocodi 
                    from sfpc.tbfornecedorcredenciado as forn           
                    where aforcrsequ =  $aforcrsequ";
                    
            $resultado = executarSQL($db, $sql);
                    while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                        $dadosPesquisa = $retorno;
                        
                    }
                    return $dadosPesquisa;
        }

        //Função de busca dos dados do objeto e orgão licitante através do cód sequencial da scc.
        function GetOrgaoEDescObj($csolcosequ){
            $db = conexao();
                $sql = "select scc.esolcoobje, orgli.eorglidesc
                        from sfpc.tbsolicitacaocompra as scc
                        inner join sfpc.tborgaolicitante as orgli 
                        on orgli.corglicodi = scc.corglicodi
                        where scc.csolcosequ =  $csolcosequ";
                        
            $resultado = executarSQL($db, $sql);
                    while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                        $dadosPesquisa = $retorno;
                        
                    }
                    return $dadosPesquisa;
        }

        public function GetListaGarantiaDocumento(){
            $sql = " SELECT ctipgasequ AS codgarantia, etipgadesc AS descricaogarantia FROM sfpc.tbtipogarantiadocumento WHERE ftipgasitu = 'ATIVO' ";
            $resultado = executarSql($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        public function SaldoAExecutar($codDoc,$totalcontrato){
            $sql = "SELECT sum(vmedcovalm) AS totalMedicao FROM sfpc.tbmedicaocontrato WHERE cdocpcsequ =".$codDoc." AND dmedcoaprt <> NULL";
            $resultado = executarSQL($this->conexaoDb,$sql);
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            if(!empty($retorno->totalmedicao)){
                return number_format(( floatval($totalcontrato) - floatval($retorno->totalmedicao)),4,',','.');
            }else{
                return number_format((floatval($totalcontrato) - floatval('0.0000')),4,',','.');
            }
        }

        public function getDocumentosFicaisEFical($codDoc){
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

        public function getDocumentosFiscaisEFiscalAlterado($codDoc){
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

        public function GetFornecedorContrato($codDoc){
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

        public function GetDocumentosAnexos($codDoc,$sequdocanexo=null,$edcanxnome=null){
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
        public function GetDocumentosAnexosAdtivo($codDoc,$sequdocanexo=null,$edcanxnome=null){
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

        public function GetDocumentosAnexosApostilamento($codDoc,$sequdocanexo=null,$edcanxnome=null){
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

        public function GetDocumentosAnexosApostilamentoAlterado($codDoc, $sequdocanexo = null, $edcanxnome = null){
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

        public function GetDocumentosAnexosMedicao($codDoc,$sequdocanexo=null,$edcanxnome=null){
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

        public function VerificaSeJaExisteDocumentoAnexo($codDoc,$sequdocanexo,$edcanxnome){
            $sql  = " SELECT cdocpcsequ AS sequdocumento ";
            $sql .= " FROM sfpc.tbdocumentoanexo WHERE cdocpcsequ=".$codDoc;
             $sql .=" AND cdcanxsequ =".$sequdocanexo;
             $sql .=" AND edcanxnome ='".$edcanxnome."'";
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            return $retorno;
        }

        public function InsertDocumentosAnexos($dadosDocumentosAnexos){
            $sql  = " INSERT INTO sfpc.tbdocumentoanexo (cdocpcsequ,edcanxnome,idcanxarqu,tdcanxcada,cusupocodi,tdcanxulat,cdcanxsequ) ";
            $sql .=" VALUES('".$dadosDocumentosAnexos['cdocpcsequ']."','".$dadosDocumentosAnexos['edcanxnome']."',decode('".$dadosDocumentosAnexos['idcanxarqu']."','hex'),'".DATE('Y-m-d H:i:s.u')."',";
            $sql .= " '".$dadosDocumentosAnexos['cusupocodi']."','".DATE('Y-m-d H:i:s.u')."','".$dadosDocumentosAnexos['cdcanxsequ']."')";
            $resultado = executarSQL($this->conexaoDb, $sql);
            if(!empty($resultado)){
                return $resultado;
            }else{
                return false;
            }
        }

        public function GetSequencialDocAnexo($codContrato){
            $sql =" SELECT max(cdcanxsequ) AS ultimosequncial FROM sfpc.tbdocumentoanexo WHERE cdocpcsequ =".$codContrato;
            $resultado = executarSQL($this->conexaoDb,$sql);
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            if(!empty($retorno)){
                return $retorno;
            }else{
                return false;
            }
        }

        public function GetItensContrato($codDoc){
            $sql  = " SELECT citedoorde as ord, eitedoserv as descitem, cservpsequ as codreduzidoserv, cmatepsequ as codreduzidomat, aitedoqtso as qtd, vitedovlun as valorunitario, ";
            $sql .= " vitedovlde as valortotal, eitedomarc as marca, eitedomode as modelo FROM sfpc.tbitemdocumento WHERE cdocpcsequ =".$codDoc;
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        public function GetAditivos($codDoc){
            $sql  = " SELECT cdocpcsequ, aaditinuad, daditicada, faditialpz, faditialct, faditialvl FROM sfpc.tbaditivo as ad where ad.cdocpcseq1 =".$codDoc;
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        public function GetApostilamento($codDoc){
            $sql = " SELECT * FROM sfpc.tbapostilamento WHERE cdocpcseq2=".$codDoc;
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        public function GetMedicao($codDoc){
            $sql = " SELECT * FROM sfpc.tbmedicaocontrato WHERE cdocpcsequ =".$codDoc;
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }
            // con = contratos | doc = documentos | SCC = tbsolicitacaocompra | cc = tbcentrodecustoportal | forn = tbfornecedorcredenciado | dadcont = tbdadoscontratantes
        public function GetDadosContratoSelecionado($CodSequContrato){
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
            $sql .= " CASE WHEN CON.vctrpcglaa  IS NULL THEN  CON.vctrpcvlor  WHEN CON.vctrpcglaa  IS NOT NULL THEN  CON.vctrpcglaa END AS valortotalcontrato, ";
            $sql .= " CASE WHEN CON.vctrpcvlor  IS NULL THEN  CON.vctrpcglaa  WHEN CON.vctrpcvlor  IS NOT NULL THEN  CON.vctrpcvlor END AS valororiginal, ";
            $sql .= " CON.nctrpcnmgr as nomearquigarantiacontratoanti, CON.cusupocodi as codeuserresposavelatualizacao, CON.tctrpculat as dtultimaatualizacao, CON.actrpcivie as numdiasentreinivigeneexec, ";
            $sql .= " CON.actrpcfvfe as numdiasentrefimvigeneexec, CON.fctrpcosem as temosemitidacontratoantigo,  CC.ccenpocorg as orgao, orlic.eorglidesc as orgaocontratante, ";
            $sql .= " CASE WHEN CON.ctpcomcodi IS NULL THEN SCC.ctpcomcodi WHEN CON.ctpcomcodi IS NOT NULL THEN CON.ctpcomcodi END as codicompra, CON.ctpcomcodi as origemcontratoantigo, ";
            $sql .= " CC.ccenpounid as unidade, SCC.csolcocodi as codisolicitacao, SCC.asolcoanos as anos, CON.cctrpcopex as opexeccontrato, CON.fctrpcanti as econtratoantigo, ";
            $sql .= " CON.ctipencodi as codiseqtipoencerramento, CON.vctrpceant as valorexecacumuladocontratoantigo, CON.vctrpcsean as saldoexeccontratoantigo, CON.actrpcnuad as numultimoaditivocontratoantigo, CON.vctrpcglaa,";
            $sql .= " CON.actrpcnuap as numultimoapostilamentocontratoantigo, CON.nctrpcnmos as nomearquivoanexoos, CON.ictrpcanos as arquivoanexoos, SUBSTRING(CON.ectrpcnumf, position('/' in CON.ectrpcnumf)+1,4)";
            $sql .= " as formula, DADCONT.cdocpcsequ as codisequdoc, DADCONT.edadcosefin as dadossefin, DADCONT.edadcosaj as dadossaj, DADCONT.edadcopref as dadosprefeito, DADCONT.edadcodenca1 as denominacaocargo1, ";
            $sql .= " DADCONT.edadcodesag1 as descagente1, DADCONT.edadcodenca2 as denominacaocargo2, DADCONT.edadcodesag2 as descagente2, DADCONT.cusupocodi as userrespatualizacao, ";
            $sql .= " DADCONT.tdadcoulat as datahoraultimaalteracao FROM sfpc.tbcontratosfpc CON inner join sfpc.tbdocumentosfpc DOC on CON.cdocpcsequ=DOC.cdocpcsequ ";
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

        public function GetFiscal($cpf,$tipo=null){
            $sql="SELECT * FROM sfpc.tbfiscaldocumento WHERE 1=1 ";
            if(!empty($cpf)){
                $sql .=" AND cfiscdcpff ='".$cpf."'";
            }
            if(!empty($tipo)){
                $sql .=" AND nfiscdtipo='".$tipo."'";
            }
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;

        }

        public function GetParametrosGerais(){
            $sql="SELECT * FROM sfpc.tbparametrosgerais WHERE 1=1 ";
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            $dadosRetorno = $retorno;
            return $dadosRetorno;
        }

        public function insertFiscal($dados){
            $sql = " INSERT INTO sfpc.tbfiscaldocumento (cfiscdcpff,nfiscdnmfs,efiscdmtfs,ffiscdsitu,nfiscdtipo,nfiscdencp,efiscdrgic,nfiscdmlfs,efiscdtlfs,cusupocodi,tfiscdulat) ";
            $sql .= " VALUES ('".$dados['cfiscdcpff']."','".$dados['nfiscdnmfs']."','".$dados['efiscdmtfs']."','ATIVO','".$dados['nfiscdtipo']."','".$dados['nfiscdencp']."','".$dados['efiscdrgic']."','".$dados['nfiscdmlfs']."','".$dados['efiscdtlfs']."',".$dados['cusupocodi'].",'".DATE('Y-m-d')."')";
            $resultado = executarSQL($this->conexaoDb, $sql);
            if(!empty($resultado)){
                return $resultado;
            }else{
                return false;
            }
        }

        public function UpdateFiscal($dados){
            $sql = "UPDATE sfpc.tbfiscaldocumento SET nfiscdnmfs='".$dados['nfiscdnmfs']."', efiscdmtfs='".$dados['efiscdmtfs']."', ";
            $sql .= " nfiscdtipo='".$dados['nfiscdtipo']."', nfiscdencp='".$dados['nfiscdencp']."', efiscdrgic='".$dados['efiscdrgic']."', nfiscdmlfs='".$dados['nfiscdmlfs']."', ";
            $sql .= " efiscdtlfs='".$dados['efiscdtlfs']."', cusupocodi=".$dados['cusupocodi'].",tfiscdulat='".DATE('Y-m-d')."' WHERE cfiscdcpff = '".$dados['cfiscdcpff']."'";
            $resultado = executarSQL($this->conexaoDb, $sql);
            if(!empty($resultado)){
                return $resultado;
            }else{
                return false;
            }
        }
        public function InsertDocumentoFiscal($dadosDocFiscal){
            $sql = " INSERT INTO sfpc.tbdocumentofiscalsfpc (cfiscdcpff,cdocpcsequ,cusupocodi,tdocfiulat) ";
            $sql .= " VALUES ('".$dadosDocFiscal['cfiscdcpff']."','".$dadosDocFiscal['cdocpcsequ']."','".$dadosDocFiscal['cusupocodi']."','".$dadosDocFiscal['tdocfiulat']."')";
            $resultado = executarSQL($this->conexaoDb, $sql);
            if(!empty($resultado)){
                return $resultado;
            }else{
                return false;
            }
        }

        public function VerificaSeExisteDocumentoFiscal($cdocpcsequ,$cfiscdcpff){
            $sql = " SELECT * FROM sfpc.tbdocumentofiscalsfpc WHERE cdocpcsequ =".$cdocpcsequ." AND cfiscdcpff='".$cfiscdcpff."'";
            $resultado = executarSQL($this->conexaoDb, $sql);
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            if(!empty($retorno)){
                return $retorno;
            }else{
                return false;
            }
        }

        public function VerificaSeTemPendenciaEmBloqueio($cdocpcsequ){
            $sql = "SELECT * FROM sfpc.tbitemdocumentobloqueio where cdocpcsequ = ".$cdocpcsequ;
            $resultado = executarSQL($this->conexaoDb, $sql);
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            if(!empty($retorno)){
                return $retorno;
            }else{
                return false;
            }
        }

        public function DeletaItemDocumentoBloqueio($cdocpcsequ){
            $sql = "DELETE FROM sfpc.tbitemdocumentobloqueio  WHERE  cdocpcsequ=".$cdocpcsequ;
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

        public function RemoveFiscaldoContrato($cfiscdcpff,$idRegistro){
            $sql = "DELETE FROM sfpc.tbdocumentofiscalsfpc  WHERE cdocpcsequ=".$idRegistro." AND  cfiscdcpff='".$cfiscdcpff."'";
            $resultado = executarSQL($this->conexaoDb, $sql);
            if(!empty($resultado)){
                return $resultado;
            }else{
                return false;
            }
        }

        public function RemoveAllFiscaldoContrato($idRegistro){
            $sql = "DELETE FROM sfpc.tbdocumentofiscalsfpc  WHERE cdocpcsequ=".$idRegistro;
            $resultado = executarSQL($this->conexaoDb, $sql);
            if(!empty($resultado)){
                return $resultado;
            }else{
                return false;
            }
        }

        public function UpdateContrato($dadosContratos){

            $sql  =" UPDATE sfpc.tbcontratosfpc SET actrpcnumc =".$dadosContratos['actrpcnumc'].", ectrpcnumf= '".$ectrpcnumf."', ";
            $sql .="corglicodi=".$dadosContratos['corglicodi'].", ";
            $sql .=" tctrpculat='".$dadosContratos['tctrpculat']."', where cdocpcsequ = ".$dadosContratos['cdocpcsequ'];
            $resultado = executarSQL($this->conexaoDb, $sql);
            if(!empty($resultado)){
                return $resultado;
            }else{
                return false;
            }
        }

        public function UpdateDocumento($dadosDocumento){
           $ctipgasequ = !empty($dadosDocumento['ctipgasequ'])? " ctipgasequ =".$dadosDocumento['ctipgasequ'].", ":'';
           $ctidocsequ = !empty($dadosDocumento['ctidocsequ'])? " ctidocsequ =".$dadosDocumento['ctidocsequ'].", ":'';
           $csitdcsequ = !empty($dadosDocumento['csitdcsequ'])? " csitdcsequ =".$dadosDocumento['csitdcsequ'].", ":'';
           $cfasedsequ = !empty($dadosDocumento['cfasedsequ'])? " cfasedsequ =".$dadosDocumento['cfasedsequ'].", ":'';
           $cmodocsequ = !empty($dadosDocumento['cmodocsequ'])? " cmodocsequ =".$dadosDocumento['cmodocsequ'].", ":'';
           $cusupocodi = !empty($dadosDocumento['cusupocodi'])? " cusupocodi =".$dadosDocumento['cusupocodi']." ":'';
           $tdocpculat = !empty($dadosDocumento['tdocpculat'])? " tdocpculat='".$dadosDocumento['tdocpculat']."', ":'';
           $ctidocseq1 = !empty($dadosDocumento['ctidocseq1'])? " ctidocseq1 =".$dadosDocumento['ctidocseq1'].", ":'';
           $cfuchcsequ = !empty($dadosDocumento['cfuchcsequ'])? " cfuchcsequ =".$dadosDocumento['cfuchcsequ'].", ":'';
           $cchelisequ = !empty($dadosDocumento['cchelisequ'])? " cchelisequ =".$dadosDocumento['cchelisequ'].", ":'';
           $sql  = "UPDATE sfpc.tbdocumentosfpc SET ".$ctipgasequ.$ctidocsequ.$csitdcsequ.$cfasedsequ.$cmodocsequ.$tdocpculat.$ctidocseq1.$cfuchcsequ.$cchelisequ.$cusupocodi ;
           $sql .= " where cdocpcsequ =".$dadosDocumento['cdocpcsequ'];
            $resultado = executarSQL($this->conexaoDb, $sql);
            if(!empty($resultado)){
                return $resultado;
            }else{
                return false;
            }
        }

        public function DeleteAllDocumentos($CodContrato){
            $sql = "DELETE FROM sfpc.tbdocumentosfpc WHERE cdocpcsequ =".$CodContrato;
            $resultado = executarSQL($this->conexaoDb, $sql);
            if(is_object($resultado)){
                $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
                if(!empty($retorno)){
                    return $retorno;
                }else{
                    return false;
                }
            }else{
                return true;
            }
        }
        public function DeleteAllItemDocumento($CodContrato){
            $sql = "DELETE FROM sfpc.tbitemdocumento WHERE cdocpcsequ =".$CodContrato;
            $resultado = executarSQL($this->conexaoDb, $sql);
            if(is_object($resultado)){
                $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
                if(!empty($retorno)){
                    return $retorno;
                }else{
                    return false;
                }
            }else{
                return true;
            }
        }

        public function InsertFornecedorContrato($dadosFornecedorContrato){
            $sql = " INSERT INTO sfpc.tbcontratofornecedor (cdocpcsequ, aforcrsequ, cusupocodi, tconfrulat) ";
            $sql .= " VALUES (".$dadosFornecedorContrato['cdocpcsequ'].", ".$dadosFornecedorContrato['aforcrsequ'].", ".$dadosFornecedorContrato['cusupocodi'].", '".date('Y-m-d')."' )";
            $resultado = executarSQL($this->conexaoDb, $sql);
            if(!empty($resultado)){
                return $resultado;
            }else{
                return false;
            }
        }

        public function VerificarSeExisteFornecedorContrato($cdocpcsequ,$aforcrsequ = null){
            $sql = " SELECT * FROM sfpc.tbcontratofornecedor WHERE cdocpcsequ =".$cdocpcsequ;
            if(!empty($aforcrsequ)){
                $sql .=" AND aforcrsequ=".$aforcrsequ;
            }
            $resultado = executarSQL($this->conexaoDb, $sql);
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            if(!empty($retorno)){
                return $retorno;
            }else{
                return false;
            }
        }
        
        public function DeleteFornecedorContrato($cdocpcsequ,$aforcrsequ){
            $sql="DELETE FROM sfpc.tbcontratofornecedor WHERE cdocpcsequ =".$cdocpcsequ." AND aforcrsequ=".$aforcrsequ;
            $resultado = executarSQL($this->conexaoDb, $sql);
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            if(!empty($retorno)){
                return $retorno;
            }else{
                return false;
            }
        }

        public function DeleteAllFornecedorContrato($cdocpcsequ){
            $sql="DELETE FROM sfpc.tbcontratofornecedor WHERE cdocpcsequ =".$cdocpcsequ;
            $resultado = executarSQL($this->conexaoDb, $sql);
            if(is_object($resultado)){
                $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
                if(!empty($retorno)){
                    return $retorno;
                }else{
                    return false;
                }
            }else{
                return true;
            }
        }

        public function DeletaDocumentoAnexo($cdocpcsequ,$cdcanxsequ){
                if(!empty($cdocpcsequ) && !empty($cdcanxsequ)){
                        $sql = " DELETE FROM sfpc.tbdocumentoanexo WHERE cdocpcsequ =".$cdocpcsequ." AND cdcanxsequ =".$cdcanxsequ;
                        $resultado = executarSQL($this->conexaoDb, $sql);
                        if(is_object($resultado)){
                            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
                            if(!empty($retorno)){
                                return $retorno;
                            }else{
                                return false;
                            }
                        }else{
                            return true;
                        }
                    }else{
                        return true;
                    }

        }

        public function DeletaAllDocumentoAnexo($cdocpcsequ){
                if(!empty($cdocpcsequ)){
                        $sql = " DELETE FROM sfpc.tbdocumentoanexo WHERE cdocpcsequ =".$cdocpcsequ;
                        $resultado = executarSQL($this->conexaoDb, $sql);
                        if(is_object($resultado)){
                            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
                            if(!empty($retorno)){
                                return $retorno;
                            }else{
                                return false;
                            }
                        }else{
                            return true;
                        }
                    }else{
                        return true;
                    }

        }

        public function DeleteContrato ($CodContrato){
            $sql = "DELETE FROM sfpc.tbcontratosfpc WHERE cdocpcsequ =".$CodContrato;
            $resultado = executarSQL($this->conexaoDb, $sql);
            if(is_object($resultado)){
                $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
                if(!empty($retorno)){
                    return $retorno;
                }else{
                    return false;
                }
            }else{
                return true;
            }

        }

        public function selectsContratoIncluir($dadosSalvar){
                    if($dadosSalvar['origemScc'] == "LICITAÇÃO"){
                        $sql = "select iteml.* from sfpc.tbitemlicitacaoportal as iteml
                                inner join sfpc.tbsolicitacaolicitacaoportal as scclic on scclic.clicpoproc = iteml.clicpoproc 
                                                and iteml.alicpoanop = scclic.alicpoanop 
                                                and iteml.cgrempcodi = scclic.cgrempcodi
                                                and iteml.ccomlicodi = scclic.ccomlicodi 
                                                and iteml.corglicodi = scclic.corglicodi 
                                inner join sfpc.tbsolicitacaocompra as scc on scc.csolcosequ = scclic.csolcosequ
                                where scc.csolcosequ = ".$dadosSalvar['csolcosequ']."
                                and iteml.aforcrsequ = ".$dadosSalvar['aforcrsequ']."
                                order by aitelporde";
                        $resultado = executarSQL($this->conexaoDb, $sql);
                        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                            $dbDados[] = $retorno;
                        }
                        
                        return $dbDados;

                    }elseif($dadosSalvar['origemScc'] != "LICITAÇÃO"){

                        $sql = "select iscc.* 
                                from sfpc.tbitemsolicitacaocompra as iscc 
                                inner join sfpc.tbsolicitacaocompra as scc on scc.csolcosequ = iscc.csolcosequ
                                where scc.csolcosequ = ".$dadosSalvar['csolcosequ']."
                                and iscc.aforcrsequ = ".$dadosSalvar['aforcrsequ']."
                                order by aitescorde ";
                        $resultado = executarSQL($this->conexaoDb, $sql);
                        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                            $dbDados[] = $retorno;
                        }
                        return $dbDados;

                    }
        }

        public function GetDescricaoMaterial($codMaterial){
            $sql = " SELECT ematepdesc FROM sfpc.tbmaterialportal WHERE cmatepsequ =".$codMaterial;
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }
        public function GetDescricaoServicos($codServico){
            $sql = " SELECT eservpdesc FROM sfpc.tbservicoportal WHERE cservpsequ =".$codServico;
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        public function insertItemContrato($dadosSalvar){
            $db = $this->conexaoDb;
            $dbDados = $this->selectsContratoIncluir($dadosSalvar);
            //Insere Itens Documento
            if(!empty($dbDados) && ($dadosSalvar['origemScc'] == "LICITAÇÃO")){
                for($i=0; $i < count($dbDados); $i++){
                    $sqlI = "insert into sfpc.tbitemdocumento (cdocpcsequ, citedosequ, citedoorde, cmatepsequ, cservpsequ, vitedovlun, vitedovexe, eitedoserv, eitedomarc, eitedomode, aitedoqtso, aitedoqtex, cusupocodi, titedoulat ) ";
                    $sqlI .= "values  (".$dadosSalvar['cdocpcsequ'].", ".$dbDados[$i]->citelpsequ.",  ".$dbDados[$i]->aitelporde.", "; 
                    $sqlI .= !empty($dbDados[$i]->cmatepsequ)     ? $dbDados[$i]->cmatepsequ.", "         : "null, "; 
                    $sqlI .= !empty($dbDados[$i]->cservpsequ)     ? $dbDados[$i]->cservpsequ.", "         : "null, ";                           
                    $sqlI .= !empty($dbDados[$i]->vitelpvlog)     ? $dbDados[$i]->vitelpvlog.", "         : "null, ";                            
                    $sqlI .= !empty($dbDados[$i]->vitelpvexe)     ? $dbDados[$i]->vitelpvexe.", "         : "null, "; 
                    $sqlI .= !empty($dbDados[$i]->eitelpdescse)   ? "'".$dbDados[$i]->eitelpdescse."', "    : "'', "; 
                    $sqlI .= !empty($dbDados[$i]->eitelpmarc)     ? "'".$dbDados[$i]->eitelpmarc."', "    : "'', "; 
                    $sqlI .= !empty($dbDados[$i]->eitelpmode)     ? "'".$dbDados[$i]->eitelpmode."', "   : "'', "; 
                    $sqlI .= $dbDados[$i]->aitelpqtso.", ".$dbDados[$i]->aitelpqtex.", ".$_SESSION['_cusupocodi_'].", now())";
                    $resI = $db->query($sqlI);
                    if(empty($resI)){
                        return false;
                    }
                }
                    return true;
            }elseif(!empty($dbDados) && ($dadosSalvar['origemScc'] != "LICITAÇÃO")){
                for($i=0; $i < count($dbDados); $i++){
                    $sqlI = "insert into sfpc.tbitemdocumento (cdocpcsequ, citedosequ, citedoorde, cmatepsequ, cservpsequ, aitedoqtso, vitedovlun, aitedoqtex, vitedovexe, eitedoserv, eitedomarc, eitedomode, cusupocodi, titedoulat )"; 
                    $sqlI .= "values  (".$dadosSalvar['cdocpcsequ'].", ".$dbDados[$i]->citescsequ.", ".$dbDados[$i]->aitescorde.", ";
                    $sqlI .= !empty($dbDados[$i]->cmatepsequ)   ? $dbDados[$i]->cmatepsequ.", "         : "null, "; 
                    $sqlI .= !empty($dbDados[$i]->cservpsequ)   ? $dbDados[$i]->cservpsequ.", "         : "null, "; 
                    $sqlI .= !empty($dbDados[$i]->aitescqtso)   ? $dbDados[$i]->aitescqtso.", "         : "null, "; 
                    $sqlI .= !empty($dbDados[$i]->vitescunit)   ? $dbDados[$i]->vitescunit.", "         : "null, "; 
                    $sqlI .= !empty($dbDados[$i]->aitescqtex)   ? $dbDados[$i]->aitescqtex.", "         : "null, "; 
                    $sqlI .= !empty($dbDados[$i]->vitescvexe)   ? $dbDados[$i]->vitescvexe.", "         : "null, "; 
                    $sqlI .= !empty($dbDados[$i]->eitescdescse) ? "'".$dbDados[$i]->eitescdescse."', "  : "'', "; 
                    $sqlI .= !empty($dbDados[$i]->eitescmarc)   ? "'".$dbDados[$i]->eitescmarc."', "    : "'', "; 
                    $sqlI .= !empty($dbDados[$i]->eitescmode)   ? "'".$dbDados[$i]->eitescmode."', "   : "'', "; 
                    $sqlI .= $_SESSION['_cusupocodi_'].", now())";
                    $resI = $db->query($sqlI);
                    if(empty($resI)){
                        return false;
                    }
                }
                return true;
            }
        }
        public function insertItemContratoAntigo($dadosItens, $cdocpcsequ, $dadosContrato){
            $db = $this->conexaoDb;
            for($i=0; $i<count($dadosItens); $i++){
                if(!is_null($dadosItens[$i]->codreduzidoserv)){
                    //checagem de dados do item
                    $sql = "SELECT * FROM sfpc.tbservicoportal WHERE cservpsequ =".$dadosItens[$i]->codreduzidoserv;
                }else{
                    $sql = "Select * FROM sfpc.tbmaterialportal WHERE cmatepsequ =".$dadosItens[$i]->codreduzidomat;
                }
                $resultado = executarSQL($this->conexaoDb, $sql);
                while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                    $dbDados[$i] = $retorno;
                }
            }
            // var_dump($dadosContrato);
            // echo "-------------------------------------------------------------";
            // var_dump($dadosItens);
            // //Insere Itens Documento
            if(!empty($dbDados) && ($dadosSalvar['origemScc'] == "LICITAÇÃO")){
                for($i=0; $i < count($dbDados); $i++){
                    $sqlI = "insert into sfpc.tbitemdocumento (cdocpcsequ, citedosequ, citedoorde, cmatepsequ, cservpsequ, vitedovlun, vitedovexe, eitedoserv, aitedoqtso, cusupocodi, titedoulat ) ";
                    $sqlI .= "values  (".$cdocpcsequ.", ".$i.",  ".$dadosItens[$i]->ord.", "; 
                    $sqlI .= !empty($dadosItens[$i]->codreduzidomat)     ? $dadosItens[$i]->codreduzidomat.", "         : "null, "; 
                    $sqlI .= !empty($dadosItens[$i]->codreduzidoserv)     ? $dadosItens[$i]->codreduzidoserv.", "         : "null, ";                           
                    $sqlI .= !empty($dadosItens[$i]->valorunitario)     ? $dadosItens[$i]->valorunitario.", "         : "null, ";                            
                    $sqlI .= !empty($dadosItens[$i]->valorunitario)     ? $dadosItens[$i]->valorunitario.", "         : "null, "; 
                    $sqlI .= !empty($dbDados[$i]->ematepdesc)   ? "'".$dbDados[$i]->ematepdesc."', "    : "'', "; 
                    $sqlI .= $dadosItens[$i]->qtd.", ".$_SESSION['_cusupocodi_'].", now())";
                    $resI = $db->query($sqlI);
                    if(empty($resI)){
                        return false;
                    }
                }
                    return true;
            }elseif(!empty($dbDados) && ($dadosSalvar['origemScc'] != "LICITAÇÃO")){
                for($i=0; $i < count($dbDados); $i++){
                    $sqlI = "insert into sfpc.tbitemdocumento (cdocpcsequ, citedosequ, citedoorde, cmatepsequ, cservpsequ, aitedoqtso, vitedovlun, vitedovexe, eitedoserv, cusupocodi, titedoulat )"; 
                    $sqlI .= "values  (".$cdocpcsequ.", ".$dadosItens[$i]->ord.", ".$dadosItens[$i]->ord.", ";
                    $sqlI .= !empty($dadosItens[$i]->codreduzidomat)   ? $dadosItens[$i]->codreduzidomat.", "         : "null, "; 
                    $sqlI .= !empty($dadosItens[$i]->codreduzidoserv)   ? $dadosItens[$i]->codreduzidoserv.", "         : "null, "; 
                    $sqlI .= !empty($dadosItens[$i]->qtd)   ? $dadosItens[$i]->qtd.", "         : "null, "; 
                    $sqlI .= !empty($dadosItens[$i]->valorunitario)   ? $dadosItens[$i]->valorunitario.", "         : "null, "; 
                    $sqlI .= !empty($dadosItens[$i]->valorunitario)   ? $dadosItens[$i]->valorunitario.", "         : "null, "; 
                    $sqlI .= !empty($dbDados[$i]->eservpdesc) ? "'".$dbDados[$i]->eservpdesc."', "  : "'', "; 
                    $sqlI .= $_SESSION['_cusupocodi_'].", now())";
                    $resI = $db->query($sqlI);
                    if(empty($resI)){
                        return false;
                    }
                }
                return true;
            }
        }

        public function DesconectaBanco(){
            $this->conexaoDb->disconnect();
        }

        public function DeletaAllItemDocumentoBloqueio ($codDoc){
           $sqlSelect = "SELECT * FROM sfpc.tbitemdocumentobloqueio WHERE cdocpcsequ=".$codDoc;
           $resultado = executarSQL($this->conexaoDb, $sqlSelect);
            if(is_object($resultado)){
                $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
                if(!empty($retorno)){
                     $sql = "DELETE FROM sfpc.tbitemdocumentobloqueio WHERE cdocpcsequ =".$codDoc;
                    $resultado = executarSQL($this->conexaoDb, $sql);
                    if(is_object($resultado)){
                       $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
                        if(!empty($retorno)){
                            return $retorno;
                        }else{
                            return false;
                        }
                    }else{
                        return true;
                    }
                }
            }
        }
        
        public function GetValorTotalAdtivo($codDoc){
            $sql = "select sum(case when adit.vaditivalr is null  then adit.vaditivtad else ";
            $sql .= "adit.vaditivalr end) as vtaditivo  from sfpc.tbaditivo adit ";
            $sql .= "left join sfpc.tbdocumentosfpc doc on adit.cdocpcsequ = doc.cdocpcsequ ";
            $sql .= "where adit.CDOCPCSEQ1 =".$codDoc;
            $sql .= " and doc.cfasedsequ = 4 and doc.ctidocsequ = 2 and doc.csitdcsequ = 1";
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }
        public function GetRepresentateAlteradoAdtivo($codDoc){
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

        public function GetGestorAlteradoApostilamento($codDoc){
            $sql = "SELECT apost.napostnmgt as nomegestor, apost.napostcpfg as cpfgestor, apost.napostmtgt as matgestor, apost.napostmlgt as emailgestor, apost.eaposttlgt as fonegestor   
                    FROM sfpc.tbapostilamento as apost left join sfpc.tbdocumentosfpc doc on apost.cdocpcsequ = doc.cdocpcsequ 
                    where apost.CDOCPCSEQ2 = $codDoc and doc.cfasedsequ = 6 and doc.csitdcsequ = 1";
// print_r($sql);die;
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        public function GetFornecedorAlteradoAdtivo($codDoc){
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
        public function GetValorTotalApostilamento($codDoc){
            $sql = "select sum(vapostvtap) as vtapost from sfpc.tbapostilamento apost left join sfpc.tbdocumentosfpc doc "; 
            $sql .=" on apost.cdocpcsequ = doc.cdocpcsequ where apost.CDOCPCSEQ2 =".$codDoc ."and cfasedsequ = 6 and doc.csitdcsequ = 1";
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        public function CancelaContrato($codDoc){
            $sql  = "UPDATE sfpc.tbdocumentosfpc SET csitdcsequ=2, cfasedsequ=1";
            $sql .= " where cdocpcsequ =".$codDoc;
             $resultado = executarSQL($this->conexaoDb, $sql);
             if(!empty($resultado)){
                 return $resultado;
             }else{
                 return false;
             }
        }
        public function EncerrarContrato($codDoc){
            $sql  = "UPDATE sfpc.tbdocumentosfpc SET csitdcsequ=1, cfasedsequ=2";
            $sql .= " where cdocpcsequ =".$codDoc;
             $resultado = executarSQL($this->conexaoDb, $sql);
             if(!empty($resultado)){
                 return $resultado;
             }else{
                 return false;
             }
        }
        public function DesfazerEncerramentoContrato($codDoc){
            $sql  = "UPDATE sfpc.tbdocumentosfpc SET csitdcsequ=1, cfasedsequ=1";
            $sql .= " where cdocpcsequ =".$codDoc;
             $resultado = executarSQL($this->conexaoDb, $sql);
             if(!empty($resultado)){
                 return $resultado;
             }else{
                 return false;
             }
        }

        public function PegaAlteracaoDataContratoPorAditivo($cdocpcseq1){
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

        public function CalculaDataFinalDeExecucao ($dataIniExe,$diaOrMes,$prazo){
            $resultData = explode("/",$this->date_transform($dataIniExe));
            if($diaOrMes == "M"){
                return date("d/m/Y",mktime(0,0,0,intval($resultData[1])+intval($prazo),$resultData[0],$resultData[2]));
            }else if($diaOrMes == "D"){
                return date("d/m/Y",mktime(0,0,0,$resultData[1],intval($resultData[0])+intval($prazo),$resultData[2]));
            }
        }

        // ===========================================================================================================================================================//
        // A partir daqui so funções para tratamento de dados                                                                                                         //
        //============================================================================================================================================================// 
        public function MascarasCPFCNPJ($valor){
            $checaSeFormatado = strripos($valor, "-");
            if($checaSeFormatado == true){
                return $valor;
            }
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

        /**
         * Valida CNPJ
         * @param string $cnpj 
         * @return bool true para CNPJ correto
         *
        */
        function valida_cnpj ( $cnpj ) {
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

        // função que valida cpf
        public function validaCPF($cpf = null) {

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

        // função que tranforma data  de 03/02/2020 para 2020-02-03 ou de 2020-02-03  para 03/02/2020
        public function date_transform($data,$today = false,$separador="/"){
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

        // função que tranforma data  de 03/02/2020 10:50:00 para 2020-02-03 10:50:00 ou de 2020-02-03 10:50:00  para 03/02/2020 10:50:00
        public function datetime_transform($datetime) {
            $dataBr ="/^(\d{1,2})\/(\d{1,2})\/(\d{4}) (\d{1,2}):(\d{2}):(\d{2})$/";
            $dataSql = "/^(\d{4})-(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{2}):(\d{2})$/";
            if(preg_match($dataSql, $datetime, $dt)){
                $new = date("d/m/Y H:i:s", mktime($dt[4], $dt[5], $dt[6], $dt[2], $dt[3], $dt[1]));
            }else if(preg_match($dataBr, $datetime, $dt)){
                $new = date("Y-m-d H:i:s", mktime($dt[4], $dt[5], $dt[6], $dt[2], $dt[1], $dt[3]));
            }
            return $new;
    
        }

            public function VerificaSeExisteContrato($NumContrato, $idcontrato){
                    $sql = "SELECT CDOCPCSEQU FROM SFPC.TBCONTRATOSFPC WHERE ECTRPCNUMF = '".$NumContrato."' AND CDOCPCSEQU <>".$idcontrato;
                    $resultado = executarSQL($this->conexaoDb, $sql);
                    $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
                    if(!empty($retorno)){
                        return $retorno;
                    }else{
                        return false;
                    }
            }
            public function VerificaSeExisteContratoComEssaSCC($NumSCC,$FornecedorScc,$OrigemScc, $idcontrato){
                    $sql = "SELECT CDOCPCSEQU FROM SFPC.TBCONTRATOSFPC WHERE csolcosequ = '".$NumSCC."' AND CDOCPCSEQU <>".$idcontrato;
                    $resultado = executarSQL($this->conexaoDb, $sql);
                    $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
                    if(!empty($retorno)){
                        if($OrigemScc == "LICITACAO"){
                                        $sqlItem = "select count(c.cdocpcsequ) as totalItens  from sfpc.tbitemdocumento as tid inner join sfpc.tbcontratosfpc as c on (c.cdocpcsequ = tid.cdocpcsequ) where  c.aforcrsequ = ".$FornecedorScc." and c.csolcosequ = ".$NumSCC." and  cmatepsequ in (select iteml.cmatepsequ from sfpc.tbsolicitacaocompra scc ";
                                        $sqlItem .= " inner join sfpc.tbsolicitacaolicitacaoportal solic on solic.csolcosequ = scc.csolcosequ inner join sfpc.tbitemlicitacaoportal iteml ";
                                        $sqlItem .= " on solic.clicpoproc = iteml.clicpoproc and solic.alicpoanop = iteml.alicpoanop and solic.cgrempcodi = iteml.cgrempcodi and ";
                                        $sqlItem .= " solic.ccomlicodi = iteml.ccomlicodi where scc.csolcosequ = ".$NumSCC." and iteml.aforcrsequ = ".$FornecedorScc." order by iteml.aitelporde) ";
                                        $sqlItem .= " or  cservpsequ in (select iteml.cservpsequ from sfpc.tbsolicitacaocompra scc inner join sfpc.tbsolicitacaolicitacaoportal solic on ";
                                        $sqlItem .=" solic.csolcosequ = scc.csolcosequ inner join sfpc.tbitemlicitacaoportal iteml on solic.clicpoproc = iteml.clicpoproc and ";
                                        $sqlItem .= " solic.alicpoanop = iteml.alicpoanop and solic.cgrempcodi = iteml.cgrempcodi and solic.ccomlicodi = iteml.ccomlicodi where ";
                                        $sqlItem .= " scc.csolcosequ = ".$NumSCC." and iteml.aforcrsequ = ".$FornecedorScc." order by iteml.aitelporde) and c.aforcrsequ = ".$FornecedorScc." and c.csolcosequ = ".$NumSCC." ";
                                        // echo $sqlItem;
                                        $resultadoItem = executarSQL($this->conexaoDb, $sqlItem);
                                        $resultadoItem->fetchInto($retornoItem, DB_FETCHMODE_OBJECT);
                                        if(!empty($retornoItem)){
                                            return $retornoItem;
                                        }else{
                                            return false;
                                        }
                        }elseif ($OrigemScc != "LICITACAO") {
                                        $sqlItem = "select count(c.cdocpcsequ) as totalItens from sfpc.tbitemdocumento as tid inner join sfpc.tbcontratosfpc as c on (c.cdocpcsequ = tid.cdocpcsequ) where c.aforcrsequ = ".$FornecedorScc." and c.csolcosequ = ".$NumSCC." and cmatepsequ in (select items.cmatepsequ from sfpc.tbsolicitacaocompra scc ";
                                        $sqlItem .= " inner join sfpc.tbitemsolicitacaocompra items on scc.csolcosequ = items.csolcosequ where ";
                                        $sqlItem .= " scc.csolcosequ = ".$NumSCC." and items.aforcrsequ = ".$FornecedorScc." ) ";
                                        $sqlItem .= " or  cservpsequ in (select items.cservpsequ from sfpc.tbsolicitacaocompra scc inner join sfpc.tbitemsolicitacaocompra items ";
                                        $sqlItem .=" on scc.csolcosequ = items.csolcosequ where scc.csolcosequ = ".$NumSCC." and items.aforcrsequ = ".$FornecedorScc.") and c.aforcrsequ = ".$FornecedorScc." and c.csolcosequ = ".$NumSCC." ";
                                        $resultadoItem = executarSQL($this->conexaoDb, $sqlItem);
                                        $resultadoItem->fetchInto($retornoItem, DB_FETCHMODE_OBJECT);
                                        if(!empty($retornoItem)){
                                            return $retornoItem;
                                        }else{
                                            return false;
                                        }
                        }
                    }else{
                        return false;
                    }
            } 
        public function buscaFornecedorContAnt($CPFCNPJ, $flagCpfCnpj){
            $db = Conexao();
            
            $sql  = "SELECT AFORCRSEQU, NFORCRRAZS, AFORCRCCGC, CCEPPOCODI, EFORCRLOGR, AFORCRNUME, EFORCRCOMP, EFORCRBAIR, NFORCRCIDA, CFORCRESTA, AFORCRCDDD, AFORCRTELS FROM SFPC.TBFORNECEDORCREDENCIADO ";
            $sql .= " WHERE ";
            
            if ($flagCpfCnpj == 1) {
                $sql .= " AFORCRCCGC = '$CPFCNPJ' ";
            } else {
                $sql .= " AFORCRCCPF = '$CPFCNPJ' ";
            }
        
            $resultadoForn = executarSQL($db, $sql);
            $resultadoForn->fetchInto($retornoForn, DB_FETCHMODE_OBJECT);
            $FornAnt['status']      = true; //para o json
            $FornAnt['RazaoSocial'] = $retornoForn->nforcrrazs;
            $FornAnt['aforcrsequ']  = $retornoForn->aforcrsequ;
            $FornAnt['cep']         = $retornoForn->cceppocodi;
            $FornAnt['logradouro']  = $retornoForn->eforcrlogr;
            $FornAnt['numero']      = $retornoForn->aforcrnume;
            $FornAnt['complemento'] = $retornoForn->eforcrcomp;
            $FornAnt['bairro']      = $retornoForn->eforcrbair;
            $FornAnt['cidade']      = $retornoForn->nforcrcida;
            $FornAnt['estado']      = $retornoForn->cforcresta;
            $FornAnt['telefone']      = $retornoForn->aforcrtels;
            return $FornAnt;
        }    
        function floatvalue($val){
            $val = str_replace(",",".",$val);
            $val = preg_replace('/\.(?=.*\.)/', '', $val);
            //var_dump($val);
            return floatval($val);
        }
    }
    