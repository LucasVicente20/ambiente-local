<?php
  session_start(); 
  require_once dirname(__FILE__) . '/../funcoes.php';
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: ClassApostilamentoIncluirPesquisar.php
# Autor:    Eliakim Ramos | João Madson
# Data:     11/12/2019
# -------------------------------------------------------------------------
# Autor: João Madson / Marcello Albuquerque
# Data:  26/05/2021
# Objetivo CR #248617
# -------------------------------------------------------------------------
    
    Class ApostilamentoIncluirPesquisar {
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

        public function GetSituacaoContrato($codSitucao){
            if(!empty($codSitucao)){
                $sql = "select sitdoc.esitdcdesc from sfpc.tbsituacaodocumento as sitdoc where sitdoc.cfasedsequ =".$codSitucao;
                $resultado = executarSql($this->conexaoDb, $sql);
                $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
                return $retorno;
               }else{
                   return (object) array('esitdcdesc'=> '');
               }
        }

        public function Pesquisar($dados){
         //   var_dump($dados);die;
            $cusupocodi = $_SESSION['_cusupocodi_'];
            $sql  = "select orlic.eorglidesc, con.cdocpcsequ,  doc.cfasedsequ, doc.csitdcsequ as codsequsituacaodoc,  con.ectrpcnumf,  con.aforcrsequ, con.ectrpcobje, con.ctpcomcodi,  forn.aforcrsequ, ";
            $sql .= " forn.aforcrccgc, forn.aforcrccpf, forn.nforcrrazs,  SCC.csolcocodi, SCC.asolcoanos, CC.ccenpocorg, CC.ccenpounid, SCC.ctpcomcodi ";
            $sql .= " from sfpc.tbcontratosfpc as con inner join sfpc.tbdocumentosfpc as doc on ( con.cdocpcsequ=doc.cdocpcsequ ) ";
            $sql .= " inner join sfpc.tbfornecedorcredenciado forn on ( con.aforcrsequ=forn.aforcrsequ ) ";
            $sql .="  left outer join SFPC.TBSOLICITACAOCOMPRA SCC on ( con.csolcosequ = SCC.csolcosequ ) ";
            $sql .="  left outer join SFPC.tbcentrocustoportal CC on CC.ccenposequ = SCC.ccenposequ ";
            // $sql .= " INNER JOIN      sfpc.tbusuariocentrocusto AS UsuarioCusto ON (UsuarioCusto.ccenposequ = CC.ccenposequ) ";
            $sql .= " left outer join sfpc.tborgaolicitante orlic on ( con.corglicodi= orlic.corglicodi ) ";
            $sql .= "  where doc.csitdcsequ = 1 AND orlic.corglicodi = con.corglicodi and con.ectrpcnumf <> 'Aguardando Numeração'";
            // $sql .= " AND UsuarioCusto.cusupocodi = $cusupocodi AND UsuarioCusto.fusucctipo = 'C' ";
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
            //var_dump($sql);//die;
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
            $sql .= " fisc.efiscdtlfs as fiscaltel, docfi.cdocpcsequ as docsequ, docfi.fdocfisitu as docsituacao FROM sfpc.tbdocumentofiscalsfpc AS docfi ";
            $sql .= " INNER JOIN sfpc.tbfiscaldocumento AS fisc ON (fisc.cfiscdcpff = docfi.cfiscdcpff) INNER JOIN sfpc.tbdocumentoanexo AS doca ON (doca.cdocpcsequ = docfi.cdocpcsequ) ";
            $sql .= " WHERE docfi.cdocpcsequ=".$codDoc;
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
            $sql .= " cusupocodi as usermod, idcanxarqu as arquivo FROM sfpc.tbdocumentoanexo WHERE cdocpcsequ=".$codDoc;
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

        public function InsertDocumentosAnexos($dadosDocumentosAnexos){
            $sql  = " INSERT INTO sfpc.tbdocumentoanexo (cdocpcsequ,edcanxnome,idcanxarqu,tdcanxcada,cusupocodi,tdcanxulat,cdcanxsequ) ";
            $sql .=" VALUES('".$dadosDocumentosAnexos['cdocpcsequ']."','".$dadosDocumentosAnexos['edcanxnome']."','".$dadosDocumentosAnexos['idcanxarqu']."','".DATE('Y-m-d H:i:s.u')."',";
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
            $sql .= " CON.corglicodi as codorgao, CON.ectrpcobje as objetivoContrato, CON.ectrpcobse as obsenceramento, CON.ectrpcraza as razao, CON.cctrpcccep as cep, CON.ectrpclogr as endereco, ";
            $sql .= " forn.aforcrccgc as cnpj, forn.aforcrccpf as cpf, forn.cceppocodi as cepfornecedor, forn.aforcrnume as numerofornecedor, forn.eforcrcomp as complementofornecedor, CON.fctrpcobra as obra, ";
            $sql .= " forn.eforcrbair as bairrofornecedor, CON.fctrpcserc as econtinuo, forn.nforcrcida as cidadefornecedor, forn.cforcresta as uffornecedor, forn.aforcrtels as tel1fornecedor, ";
            $sql .= " CON.fctrpccons as consocio, forn.dforcrultb as datavalidadebalaco, forn.dforcrcnfc as datacertidaonegativafaleciaconcordata, forn.nforcrentp as nomeentidadeproficompetente, ";
            $sql .= " CON.fctrpcremf as execoufornec, CON.ectrpcremf as regexecoumodfornec, CON.actrpcpzec as prazoexec, CON.dctrpcinvg as datainivige, forn.tforcrulat as dataultimaateracaofornecedor, ";
            $sql .= " forn.fforcrtipo as tipofornecedor, CON.dctrpcfivg as datafimvige, CON.dctrpcpbdm as datapublic, CON.dctrpcinex as datainiexec, CON.dctrpcfiex as datafimexec, ";
            $sql .= " CON.cctrpctpfr as tipoespfontrecur, CON.actrpcnucv as nconvenio, CON.actrpcnuoc as noperacaocredito, CON.nctrpcnmrl as nomerepresenlegal, CON.nctrpccgrl as cargorepresenlegal, ";
            $sql .= " CON.ectrpccpfr as cpfrepresenlegal, CON.nctrpcmlrl as emailrepresenlegal, CON.ectrpctlrl as telrepresenlegal, CON.ectrpcidrl as identidaderepreslegal, CON.nctrpcoerl as orgaoexpedrepreselegal, ";
            $sql .= " CON.nctrpcufrl as ufrgrepresenlegal, CON.nctrpccdrl as cidadedomrepresenlegal, CON.nctrpcedrl as estdomicrepresenlegal, CON.nctrpcnarl as naciorepresenlegal, CON.cctrpcecrl as estacivilrepresenlegal, ";
            $sql .= " CON.nctrpcprrl as profirepresenlegal, CON.nctrpcnmgt as nomegestor, CON.nctrpccpfg as cpfgestor, CON.nctrpcmtgt as matgestor, CON.nctrpcmlgt as emailgestor, CON.ectrpctlgt as fonegestor, ";
            $sql .= " CON.dctrpccada as dtcadastrocontrato, CON.vctrpcglaa as valorglobaladtivo, CON.vctrpcvlor as valororiginalcontrato, CON.cctrpciden as seecpfcnpj, CON.ictrpcgrnt as arqgarantiacontratoantigo, ";
            $sql .= " CON.nctrpcnmgr as nomearquigarantiacontratoanti, CON.cusupocodi as codeuserresposavelatualizacao, CON.tctrpculat as dtultimaatualizacao, CON.actrpcivie as numdiasentreinivigeneexec, ";
            $sql .= " CON.actrpcfvfe as numdiasentrefimvigeneexec, CON.fctrpcosem as temosemitidacontratoantigo, CON.ctpcomcodi as codicompra, CC.ccenpocorg as orgao, CC.ecenpodesc as orgaocontratante, ";
            $sql .= " CC.ccenpounid as unidade, SCC.csolcocodi as codisolicitacao, SCC.asolcoanos as anos, CON.cctrpcopex as opexeccontrato, CON.fctrpcanti as econtratoantigo, ";
            $sql .= " CON.ctipencodi as codiseqtipoencerramento, CON.vctrpceant as valorexecacumuladocontratoantigo, CON.vctrpcsean as saldoexeccontratoantigo, CON.actrpcnuad as numultimoaditivocontratoantigo, ";
            $sql .= " CON.actrpcnuap as numultimoapostilamentocontratoantigo, CON.nctrpcnmos as nomearquivoanexoos, CON.ictrpcanos as arquivoanexoos, SUBSTRING(CON.ectrpcnumf, position('/' in CON.ectrpcnumf)+1,4)";
            $sql .= " as formula, DADCONT.cdocpcsequ as codisequdoc, DADCONT.edadcosefin as dadossefin, DADCONT.edadcosaj as dadossaj, DADCONT.edadcopref as dadosprefeito, DADCONT.edadcodenca1 as denominacaocargo1, ";
            $sql .= " DADCONT.edadcodesag1 as descagente1, DADCONT.edadcodenca2 as denominacaocargo2, DADCONT.edadcodesag2 as descagente2, DADCONT.cusupocodi as userrespatualizacao, ";
            $sql .= " DADCONT.tdadcoulat as datahoraultimaalteracao FROM sfpc.tbcontratosfpc CON inner join sfpc.tbdocumentosfpc DOC on CON.cdocpcsequ=DOC.cdocpcsequ ";
            $sql .= " left outer join SFPC.TBSOLICITACAOCOMPRA SCC on ( con.csolcosequ = SCC.csolcosequ ) ";
            $sql .="  left outer join SFPC.tbcentrocustoportal CC on ( CC.ccenposequ = SCC.ccenposequ ) ";
            $sql .= " inner join sfpc.tbfornecedorcredenciado forn on ( con.aforcrsequ=forn.aforcrsequ ) ";
            $sql .= " left outer join sfpc.tbdadoscontratantes DADCONT on ( CON.cdocpcsequ=DADCONT.cdocpcsequ ) ";
            $sql .= " where CON.cdocpcsequ=".$CodSequContrato;
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

        public function UpdateContrato($dadosContratos){
            $dctrpcpbdm = !empty($dadosContratos['dctrpcpbdm'])?", dctrpcpbdm='".$dadosContratos['dctrpcpbdm']."'":', dctrpcpbdm=NULL';
            $dctrpcinvg = $dadosContratos['dctrpcinvg'];
            $sql  =" UPDATE sfpc.tbcontratosfpc SET actrpcnumc =".$dadosContratos['actrpcnumc'].", ectrpcnumf= '".$dadosContratos['ectrpcnumf']."', ectrpcobje='".$dadosContratos['ectrpcobje']."', ";
            $sql .=" fctrpccons='".$dadosContratos['fctrpccons']."', fctrpcserc='".$dadosContratos['fctrpcserc']."', fctrpcobra='".$dadosContratos['fctrpcobra']."', ectrpcremf='".$dadosContratos['ectrpcremf']."', ";
            $sql .=" actrpcanoc=".$dadosContratos['actrpcanoc'].", cctrpcopex='".$dadosContratos['cctrpcopex']."', actrpcpzec=".$dadosContratos['actrpcpzec'].$dctrpcpbdm.", actrpcivie=".$dadosContratos['actrpcivie'].", ";
            $sql .=" actrpcfvfe =".$dadosContratos['actrpcfvfe'].", dctrpcinvg='".$dctrpcinvg."', cctrpctpfr=".$dadosContratos['cctrpctpfr'].", actrpcnucv=0, actrpcnuoc=0, ";
            $sql .=" nctrpcnmrl='".$dadosContratos['nctrpcnmrl']."', ectrpccpfr='".$dadosContratos['ectrpccpfr']."', nctrpccgrl='".$dadosContratos['nctrpccgrl']."', ectrpcidrl='".$dadosContratos['ectrpcidrl']."', ";
            $sql .=" nctrpcoerl='".$dadosContratos['nctrpcoerl']."', nctrpcufrl='".$dadosContratos['nctrpcufrl']."', nctrpccdrl='".$dadosContratos['nctrpccdrl']."', nctrpcedrl='".$dadosContratos['nctrpcedrl']."', ";
            $sql .=" nctrpcnarl='".$dadosContratos['nctrpcnarl']."', cctrpcecrl='".$dadosContratos['cctrpcecrl']."', nctrpcprrl='".$dadosContratos['nctrpcprrl']."', nctrpcmlrl='".$dadosContratos['nctrpcmlrl']."', ";
            $sql .=" ectrpctlrl='".$dadosContratos['ectrpctlrl']."', nctrpcnmgt='".$dadosContratos['nctrpcnmgt']."', nctrpcmtgt='".$dadosContratos['nctrpcmtgt']."', nctrpccpfg='".$dadosContratos['nctrpccpfg']."', ";
            $sql .=" nctrpcmlgt='".$dadosContratos['nctrpcmlgt']."', ectrpctlgt='".$dadosContratos['ectrpctlgt']."' where cdocpcsequ = ".$dadosContratos['cdocpcsequ'];
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

        public function VerificarSeExisteFornecedorContrato($cdocpcsequ,$aforcrsequ){
            $sql = " SELECT * FROM sfpc.tbcontratofornecedor WHERE cdocpcsequ =".$cdocpcsequ." AND aforcrsequ=".$aforcrsequ;
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

        public function DesconectaBanco(){
            $this->conexaoDb->disconnect();
        }

        
        // ===========================================================================================================================================================//
        // A partir daqui so funções para tratamento de dados                                                                                                         //
        //============================================================================================================================================================// 
        public function MascarasCPFCNPJ($valor){
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

        
    }