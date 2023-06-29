  <?php
  session_start(); 
  require_once dirname(__FILE__) . '/../funcoes.php';
  ini_set( 'display_errors', 0 );
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: CadContratoManter.php
# Autor:    Eliakim Ramos | João Madson
# Data:     11/12/2019
# -------------------------------------------------------------------------
# Autor: João Madson
# Data:  20/05/2021
# Objetivo: CR #248279
# -------------------------------------------------------------------------
# Autor: Marcello Albuquerque
# Data:  31/08/2021
# Objetivo: CR #252420
# -------------------------------------------------------------------------
# Autor: Lucas Vicente
# Data:  15/02/2023
# Objetivo: CR #275671
# -------------------------------------------------------------------------
    
    Class Contrato {
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
        public function corrigeString($string){
            $comAcentos = array('à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ü', 'ú', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'O', 'Ù', 'Ü', 'Ú', '´', '~', '^','`','¨');
            $semAcentos = array('a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'y', 'A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', '', '', '', '', '');
            // retira acento
            $string = str_replace($comAcentos, $semAcentos, $string);
            // converte a entrada para maiusculo
            $string = strtoupper($string);
            // remove palavras que contenham sintaxe sql
            preg_match("/(from|select|insert|delete|where|drop table|show tables|#|\*|--|\\\\)/",strtolower($string),$matches);
            $string = @preg_replace($matches,"",$string);
            $string = trim($string);//limpa espaços vazio
            $string = strip_tags($string);//tira tags html e php
            $string = addslashes($string);//Adiciona barras invertidas a uma string
            return $string;
        }

        public function floatvalue($val){
            $val = str_replace(",",".",$val);
            $val = preg_replace('/\.(?=.*\.)/', '', $val);
           // var_dump($val);
            return floatval($val);
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

        public function getOrgaosLicitantes(){
            $sql = "select corglicodi, eorglidesc from sfpc.tborgaolicitante where forglisitu = 'A'";
            $resultado = executarSql($this->conexaoDb, $sql);
            $dadosRetorno = array();

            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        public function get_tipoCompraSemParametro(){
            $sql = "select tc.ctpcomcodi, tc.etpcomnome from SFPC.tbtipocompra as tc";
            $resultado = executarSql($this->conexaoDb, $sql);
            $dadosRetorno = array();

            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
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
            $sql .= " order by orlic.eorglidesc asc, SUBSTRING(con.ectrpcnumf, position('/' in con.ectrpcnumf)+1, 4) desc, con.actrpcnumc desc ";
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
                    from  sfpc.tbcentrocustoportal as cc inner join sfpc.tbsolicitacaocompra as scc on (cc.corglicodi = scc.corglicodi)
                    where scc.csitsocodi = 4";

            $corglicodi = $this->getOrgaoUsuarioLogadoContratos($this->conexaoDb);
            if(!empty($corglicodi)){
                $sql .= " and scc.corglicodi in (".implode(",", $corglicodi).")";
            }
            if(empty($dados['numeroScc'])){
                if(!empty($dados['dataIni'])){
                    $sql .= " and scc.tsolcodata >= ".$dados['dataIni'];
                }
                if(!empty($dados['dataFim'])){
                    $sql .= " and scc.tsolcodata <= ".$dados['dataFim'];
                }
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
            // print_r($sql);die;
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
                                'corglicodi'=> $dadosScc[$i]->corglicodi,
                                'citelpnuml'=> $dadosScc[$i]->citelpnuml

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
                forn.aforcrsequ, scc.corglicodi,
                iteml.citelpnuml
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
                and scc.csolcosequ = $csolcosequ
                order by iteml.citelpnuml";
            }else{
                $sql  =    "select distinct scc.csolcosequ, scc.csolcocodi, scc.asolcoanos, CC.ccenpocorg, CC.ccenpounid, forn.nforcrrazs, 
                            forn.aforcrccgc, forn.aforcrccpf, scc.ctpcomcodi, forn.aforcrsequ, scc.corglicodi
                            from sfpc.tbsolicitacaocompra as scc 
                            inner join sfpc.tbitemsolicitacaocompra as iscc on iscc.csolcosequ = scc.csolcosequ
                            inner join SFPC.tbcentrocustoportal as CC on CC.ccenposequ = scc.ccenposequ
                            inner join sfpc.tbfornecedorcredenciado as forn on forn.aforcrsequ = iscc.aforcrsequ
                            where scc.csolcosequ = $csolcosequ";        
            }
            //var_dump($sql);
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
            $sqlChecaApost = "SELECT apost.cdocpcsequ 
                            FROM sfpc.tbapostilamento as apost
                            INNER JOIN sfpc.tbdocumentosfpc AS doc ON (apost.cdocpcsequ = doc.cdocpcsequ)
                            WHERE apost.cdocpcseq2 = $codDoc and apost.ctpaposequ in (2,3) and doc.ctidocsequ = 3 
                            and doc.cfasedsequ = 6 and doc.csitdcsequ = 1 and (
                                select max(a2.aapostnuap) from sfpc.tbapostilamento as a2 
                                inner join sfpc.tbdocumentosfpc as doc2 on (doc2.cdocpcsequ = a2.cdocpcsequ) 
                                where a2.cdocpcseq2 = apost.cdocpcseq2 and a2.ctpaposequ in (2,3) and doc2.ctidocsequ = 3 
                                and doc2.cfasedsequ = 6 and doc2.csitdcsequ = 1 
                            ) = apost.aapostnuap";
            $resultApost = executarSQL($this->conexaoDb, $sqlChecaApost);
            $Apost = array();
            while($resultApost->fetchInto($retornoApost, DB_FETCHMODE_OBJECT)){
                $Apost[] = $retornoApost;
            }
            if(empty($Apost)){
                $sql  = " SELECT fisc.nfiscdtipo as tipofiscal, fisc.nfiscdnmfs as fiscalnome, fisc.efiscdmtfs as fiscalmatricula, fisc.cfiscdcpff as fiscalcpf, fisc.nfiscdmlfs as fiscalemail, ";
                $sql .= " fisc.efiscdtlfs as fiscaltel, docfi.cdocpcsequ as docsequ, docfi.fdocfisitu as docsituacao, fisc.nfiscdencp as entidade FROM sfpc.tbdocumentofiscalsfpc AS docfi ";
                $sql .= " INNER JOIN sfpc.tbfiscaldocumento AS fisc ON (fisc.cfiscdcpff = docfi.cfiscdcpff) INNER JOIN sfpc.tbdocumentoanexo AS doca ON (doca.cdocpcsequ = docfi.cdocpcsequ) ";
                $sql .= " WHERE docfi.cdocpcsequ=".$codDoc;
            }else{
                $sql  = " SELECT fisc.nfiscdtipo as tipofiscal, fisc.nfiscdnmfs as fiscalnome, fisc.efiscdmtfs as fiscalmatricula, 
                fisc.cfiscdcpff as fiscalcpf, fisc.nfiscdmlfs as fiscalemail,  fisc.efiscdtlfs as fiscaltel, apost.cdocpcsequ as docsequ, 
                docfi.fdocfisitu as docsituacao, fisc.nfiscdencp as entidade 
                FROM sfpc.tbfiscaldocumento AS fisc  
                INNER JOIN sfpc.tbdocumentofiscalsfpc AS docfi ON (fisc.cfiscdcpff = docfi.cfiscdcpff) 
                inner join sfpc.tbapostilamento as apost on (docfi.cdocpcsequ = apost.cdocpcsequ)
                INNER JOIN sfpc.tbdocumentosfpc AS doc ON (apost.cdocpcsequ = doc.cdocpcsequ)
                WHERE apost.cdocpcseq2 = $codDoc and apost.ctpaposequ in (2,3) and doc.ctidocsequ = 3 
                and doc.cfasedsequ = 6 and doc.csitdcsequ = 1 and (
                    select max(a2.aapostnuap) from sfpc.tbapostilamento as a2 
                    inner join sfpc.tbdocumentosfpc as doc2 on (doc2.cdocpcsequ = a2.cdocpcsequ) 
                    where a2.cdocpcseq2 = apost.cdocpcseq2 and a2.ctpaposequ in (2,3) and doc2.ctidocsequ = 3 
                    and doc2.cfasedsequ = 6 and doc2.csitdcsequ = 1 
                ) = apost.aapostnuap";
            }
            
            
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        public function getDocumentosFicaisEFicalApost($codDoc){
            
            $sql  = "SELECT fisc.nfiscdtipo as tipofiscal, fisc.nfiscdnmfs as fiscalnome, fisc.efiscdmtfs as fiscalmatricula, 
            fisc.cfiscdcpff as fiscalcpf, fisc.nfiscdmlfs as fiscalemail,  fisc.efiscdtlfs as fiscaltel, docfi.cdocpcsequ as docsequ, 
            docfi.fdocfisitu as docsituacao, fisc.nfiscdencp as entidade 
            FROM sfpc.tbfiscaldocumento AS fisc
            INNER JOIN sfpc.tbdocumentofiscalsfpc AS docfi  ON (fisc.cfiscdcpff = docfi.cfiscdcpff) 
            inner join sfpc.tbapostilamento as apost on (docfi.cdocpcsequ = apost.cdocpcsequ)
            WHERE apost.cdocpcsequ = $codDoc";
            
            
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
            $sql .=" VALUES('".$dadosDocumentosAnexos['cdocpcsequ']."','".$dadosDocumentosAnexos['edcanxnome']."', decode('".$dadosDocumentosAnexos['idcanxarqu']."','hex'),'".DATE('Y-m-d H:i:s.u')."',";
            $sql .= " '".$dadosDocumentosAnexos['cusupocodi']."','".DATE('Y-m-d H:i:s.u')."','".$dadosDocumentosAnexos['cdcanxsequ']."')";
            // $resultado = executarSQL($this->conexaoDb, $sql);
            $resultado = $this->conexaoDb->query($sql);
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
            $sql  = " SELECT cdocpcsequ, aaditinuad, daditicada, faditialpz, faditialct, faditialvl, daditiinvg, daditifivg, daditiinex FROM sfpc.tbaditivo as ad where ad.cdocpcseq1 =".$codDoc;
            //var_dump($sql);die;
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
            if(empty($dados['cusupocodi'])){
                $dados['cusupocodi'] = 'NULL';
            }
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
            // $resultado = $this->conexaoDb->query($sql);
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
        public function UpdateGestorContrato($dadosContratos){
            $sql  =" UPDATE sfpc.tbcontratosfpc SET  nctrpcnmgt='".$dadosContratos['nctrpcnmgt']."', nctrpcmtgt='".$dadosContratos['nctrpcmtgt']."', nctrpccpfg='".$dadosContratos['nctrpccpfg']."', ";
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
        // verifica se existe fornecedor para esse contrato em caso de consocio 
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

        public function selectsContratoIncluir($dadosSalvar){
            switch($dadosSalvar['identificador']){

                case'Documento':

                    $sql1 =  "select max(cdocpcsequ) from sfpc.tbdocumentosfpc";
                    $resultado = executarSQL($this->conexaoDb, $sql1);
                    $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
                    if(!empty($retorno)){
                        $cdocpcsequNovo = intval($retorno->max) +1;
                        return $cdocpcsequNovo;
                    }
                    
                break;
                case'itemDocumento':

                    if($dadosSalvar['origemScc'] == "LICITACAO"){
                        $csolcosequ = $dadosSalvar['csolcosequ'];
                        $aforcrsequ = $dadosSalvar['aforcrsequ'];
                        
                        $sql = "select iteml.* from sfpc.tbitemlicitacaoportal as iteml
                        inner join sfpc.tbsolicitacaolicitacaoportal as scclic on scclic.clicpoproc = iteml.clicpoproc 
                                        and iteml.alicpoanop = scclic.alicpoanop 
                                        and iteml.cgrempcodi = scclic.cgrempcodi
                                        and iteml.ccomlicodi = scclic.ccomlicodi 
                                        and iteml.corglicodi = scclic.corglicodi 
                        inner join sfpc.tbsolicitacaocompra as scc on scc.csolcosequ = scclic.csolcosequ
                        where scc.csolcosequ = $csolcosequ
                        and iteml.aforcrsequ = $aforcrsequ";
                        if($dadosSalvar['citelpnuml'][0] != '0'){
                            $citelpnuml = $dadosSalvar['citelpnuml'];
                            $sqlcitelpnuml ="";
                            for($i=0;$i<(count($citelpnuml)-1);$i++){
                                    $sqlcitelpnuml .= "'$citelpnuml[$i]', ";
                            }
                            $sqlcitelpnuml .= "'$citelpnuml[$i]'";
                            $sql .= " and iteml.citelpnuml in ($sqlcitelpnuml)";
                        }
                        $sql .= " order by aitelporde";

                        // $sql = "select iteml.* from sfpc.tbitemlicitacaoportal as iteml
                        //         inner join sfpc.tbsolicitacaolicitacaoportal as scclic on scclic.clicpoproc = iteml.clicpoproc 
                        //                         and iteml.alicpoanop = scclic.alicpoanop 
                        //                         and iteml.cgrempcodi = scclic.cgrempcodi
                        //                         and iteml.ccomlicodi = scclic.ccomlicodi 
                        //                         and iteml.corglicodi = scclic.corglicodi 
                        //         inner join sfpc.tbsolicitacaocompra as scc on scc.csolcosequ = scclic.csolcosequ
                        //         where scc.csolcosequ = ".$dadosSalvar['csolcosequ']."
                        //         and iteml.aforcrsequ = ".$dadosSalvar['aforcrsequ']."
                        //         order by aitelporde";       
                        $resultado = executarSQL($this->conexaoDb, $sql);
                        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                            $dbDados[] = $retorno;
                        }
                        
                        return $dbDados;

                    }elseif($dadosSalvar['origemScc'] != "LICITACAO"){
                        // $dbDados[] = $this->PesqusiaItensSCC($dadosSalvar['csolcosequ'],$dadosSalvar['aforcrsequ'],$dadosSalvar['origemScc'],$dadosSalvar['citelpsequ']);
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
                        //var_dump($dbDados);die;
                        return $dbDados;

                    }

                break;
                case'valorItens':
                    if($dadosSalvar['origemScc'] == "LICITACAO"){    
                        
                        $sql = " Select distinct iteml.aitelpqtso as aitelpqtso, iteml.vitelpvlog as vitelpvlog
                        from sfpc.tbsolicitacaocompra as scc 
                        inner join sfpc.tbsolicitacaolicitacaoportal as scclic on scclic.csolcosequ = scc.csolcosequ 
                        inner join SFPC.tbcentrocustoportal as CC on CC.ccenposequ = scc.ccenposequ
                        inner join sfpc.tbitemlicitacaoportal as iteml on iteml.clicpoproc = scclic.clicpoproc 
                        and iteml.alicpoanop = scclic.alicpoanop 
                        and iteml.cgrempcodi = scclic.cgrempcodi
                        and iteml.ccomlicodi = scclic.ccomlicodi 
                        and iteml.corglicodi = scclic.corglicodi 
                        inner join sfpc.tbfornecedorcredenciado as forn on forn.aforcrsequ = iteml.aforcrsequ 
                        Where scc.ctpcomcodi = 2
                        and scc.csolcosequ = ".$dadosSalvar['csolcosequ']."
                        and iteml.aforcrsequ = ".$dadosSalvar['aforcrsequ'];
                        if($dadosSalvar['citelpnuml'][0] != '0'){
                            $citelpnuml = $dadosSalvar['citelpnuml'];
                            $sqlcitelpnuml ="";
                            for($i=0;$i<(count($citelpnuml)-1);$i++){
                                    $sqlcitelpnuml .= "'$citelpnuml[$i]', ";
                            }
                            $sqlcitelpnuml .= "'$citelpnuml[$i]'";
                            $sql .= " and iteml.citelpnuml in ($sqlcitelpnuml)";
                        }
                    }else{          //"select distinct iscc.vitescunit, iscc.aitescqtso Query anterior CR #252420
                        $sql  =    "select iscc.vitescunit, iscc.aitescqtso
                                    from sfpc.tbsolicitacaocompra as scc 
                                    inner join sfpc.tbitemsolicitacaocompra as iscc on iscc.csolcosequ = scc.csolcosequ
                                    inner join SFPC.tbcentrocustoportal as CC on CC.ccenposequ = scc.ccenposequ
                                    inner join sfpc.tbfornecedorcredenciado as forn on forn.aforcrsequ = iscc.aforcrsequ
                                    where scc.csolcosequ = ".$dadosSalvar['csolcosequ'];          
                    }
                    
                    $resultado = executarSQL($this->conexaoDb, $sql);
                    while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){;
                        
                        $dadosResultado[] = $retorno;
        
                    }
                    return $dadosResultado;
                break;
            }
        }

        public function insertsContratoAntigoIncluir($dadosSalvar){
            $db = $this->conexaoDb;
            
            //Insere Documento
            $sqlD = "insert into sfpc.tbdocumentosfpc (cdocpcsequ, ctidocsequ, ctipgasequ, csitdcsequ, cfasedsequ, cusupocodi, tdocpculat)";
            $sqlD .= "values  (".$dadosSalvar['cdocpcsequ'].", 1, ";
            $sqlD .= !empty($dadosSalvar['ctipgasequ']) ? $dadosSalvar['ctipgasequ'].", " : 'null'.", "; 
            $sqlD .= "1, 1, ".$dadosSalvar['cusupocodi'].", now())";
            $resD = $db->query($sqlD);
            if(empty($resD)){
                return 1;
            }

            $dadosSalvar['identificador'] = 'itemDocumento';
            
            $itens_material = $dadosSalvar['itens_material'];
            $itens_servico = $dadosSalvar['itens_servico'];

           // var_dump($itens_material);
           // var_dump($itens_servico);die;
        

            $materiais = $dadosSalvar['materiais'];
            $servicos = $dadosSalvar['servicos'];

            // var_dump($servicos);die;
           
            //Insere Itens Documento
            $cont_Material = 1;
            $cont_Servico = 1;
            $contador_geral = 1;

            if(($dadosSalvar['ctpcomcodi'] == TIPO_COMPRA_LICITACAO)){
                
                for($i = 0; $i < count($materiais); $i++){
                    $contador = $contador_geral++;
                    //number_format(str_replace(",",".",str_replace(".","","1.089,90")), 2, '.', '');
                    $materiais[$i]['quantidade'] = number_format(str_replace(",",".",str_replace(".","",$itens_material[$i]['mat_qtd'])), 4, '.', '');
                    $materiais[$i]['valorEstimado'] = number_format(str_replace(",",".",str_replace(".","",$itens_material[$i]['mat_valor'])), 4, '.', '');
                    $materiais[$i]['valorItem'] = number_format(str_replace(",",".",str_replace(".","",$itens_material[$i]['mat_valor'])), 4, ',','.');
                    //number_format($itens_material[$i]['mat_valor'], 2 , ".", ",");

                    $sqlI = "insert into sfpc.tbitemdocumento (cdocpcsequ, citedosequ, citedoorde, cmatepsequ,  aitedoqtso, vitedovlun, vitedovexe, cusupocodi, titedoulat ) ";
                    $sqlI .= "values  (".strtoupper($dadosSalvar['cdocpcsequ']).", ".$contador.",  ".$materiais[$i]['posicaoItem'].", "; 
                    $sqlI .= !empty($materiais[$i]['codigo']) ? strtoupper($materiais[$i]['codigo']).", '": "null, '";
                    $sqlI .= $materiais[$i]['quantidade']."', '";
                    $sqlI .= !empty($materiais[$i]['valorEstimado']) ? $materiais[$i]['valorEstimado']."', '" : "null, '";
                    $sqlI .= !empty($materiais[$i]['valorItem']) ? $materiais[$i]['valorItem']."', " : "null, ";
                    $sqlI .= $_SESSION['_cusupocodi_'].", now())";
           
                    $resI = $db->query($sqlI);
                    if(empty($resI)){
                        var_dump('retorno vazio 1');
                        return 2;
                    }
                }
                
                for($i = 0; $i < count($servicos); $i++){
                    $ordem_serv = $cont_Servico++;
                    $contador = $contador_geral++;

                    $servicos[$i]['quantidade'] = number_format(str_replace(",",".",str_replace(".","",$itens_servico[$i]['qtd_valor'])), 4, '.', '');
                    $servicos[$i]['valorEstimado'] = number_format(str_replace(",",".",str_replace(".","",$itens_servico[$i]['valor_qtd'])), 4, '.', '');
                   // $servicos[$i]['descricaoDetalhada'] = $itens_servico[$i]['desc_valor'];

                    $sqlI = "insert into sfpc.tbitemdocumento (cdocpcsequ, citedosequ, citedoorde, cservpsequ, aitedoqtso, vitedovlun, vitedovexe, eitedoserv,  cusupocodi, titedoulat ) ";
                    $sqlI .= "values  (".strtoupper($dadosSalvar['cdocpcsequ']).", ".$contador.",  ".$servicos[$i]['posicaoItem'].", ";
                    $sqlI .= !empty($servicos[$i]['codigo']) ? strtoupper($servicos[$i]['codigo']).", '": "null, '";
                    $sqlI .= $servicos[$i]['quantidade']."', '";
                    $sqlI .= !empty($servicos[$i]['valorEstimado']) ? $servicos[$i]['valorEstimado']."', '" : "null, '";
                    $sqlI .= !empty($servicos[$i]['valorEstimado']) ? $servicos[$i]['valorEstimado']."', " : "null, ";
                   // $sqlI .= !empty($servicos[$i]['descricaoDetalhada']) ? " '".strtoupper($servicos[$i]['descricaoDetalhada'])."', " : " '', ";
                    $sqlI .= $_SESSION['_cusupocodi_'].", now())";
                //  var_dump( $sqlI);die;
                    $resI = $db->query($sqlI);
                    if(empty($resI)){
                        var_dump('retorno vazio 2');
                        return 2;
                    }
                }

                $contador_geral++;
            }else{
                for($i = 0; $i < count($materiais); $i++){
                    $contador = $contador_geral++;

                    $materiais[$i]['quantidade'] = number_format(str_replace(",",".",str_replace(".","",$itens_material[$i]['mat_qtd'])), 4, '.', '');
                    $materiais[$i]['valorEstimado'] = number_format(str_replace(",",".",str_replace(".","",$itens_material[$i]['mat_valor'])), 4, '.', '');
                    $materiais[$i]['valorItem'] = number_format(str_replace(",",".",str_replace(".","",$itens_material[$i]['mat_valor'])), 4, '.', '');

                    $sqlI = "insert into sfpc.tbitemdocumento (cdocpcsequ, citedosequ, citedoorde, cmatepsequ,  aitedoqtso, vitedovlun, vitedovexe, cusupocodi, titedoulat ) ";
                    $sqlI .= "values  (".strtoupper($dadosSalvar['cdocpcsequ']).", ".$contador.",  ".$materiais[$i]['posicaoItem'].", "; 
                    $sqlI .= !empty($materiais[$i]['codigo']) ? strtoupper($materiais[$i]['codigo']).", '": "null, '";
                    $sqlI .= $materiais[$i]['quantidade']."', '";
                    $sqlI .= !empty($materiais[$i]['valorEstimado']) ? $materiais[$i]['valorEstimado']."', '" : "null, '";
                    $sqlI .= !empty($materiais[$i]['valorItem']) ? $materiais[$i]['valorItem']."', " : "null, ";
                    $sqlI .= $_SESSION['_cusupocodi_'].", now())";
                   

                    $resI = $db->query($sqlI);
                    if(empty($resI)){
                        var_dump('retorno vazio 1');
                        return 2;
                    }
                }
                
                for($i = 0; $i < count($servicos); $i++){
                    // var_dump($i);
                    $ordem_serv = $cont_Servico++;
                    $contador = $contador_geral++;

                    $servicos[$i]['quantidade'] = number_format(str_replace(",",".",str_replace(".","",$itens_servico[$i]['qtd_valor'])), 4, '.', '');
                    $servicos[$i]['valorEstimado'] = number_format(str_replace(",",".",str_replace(".","",$itens_servico[$i]['valor_qtd'])), 4, '.', '');
                    //$servicos[$i]['descricaoDetalhada'] = $itens_servico[$i]['desc_valor'];

                    $sqlI = "insert into sfpc.tbitemdocumento (cdocpcsequ, citedosequ, citedoorde, cservpsequ, aitedoqtso, vitedovlun, vitedovexe, cusupocodi, titedoulat ) ";
                    $sqlI .= "values  (".strtoupper($dadosSalvar['cdocpcsequ']).", ".$contador.",  ".$servicos[$i]['posicaoItem'].", ";
                    $sqlI .= !empty($servicos[$i]['codigo']) ? strtoupper($servicos[$i]['codigo']).", '": "null, '";
                    $sqlI .= $servicos[$i]['quantidade']."', '";
                    $sqlI .= !empty($servicos[$i]['valorEstimado']) ? $servicos[$i]['valorEstimado']."', '" : "null, '";
                    $sqlI .= !empty($servicos[$i]['valorEstimado']) ? $servicos[$i]['valorEstimado']."', " : "null, ";
                   // $sqlI .= !empty($servicos[$i]['descricaoDetalhada']) ? " '".strtoupper($servicos[$i]['descricaoDetalhada'])."', " : " '', ";
                    $sqlI .= $_SESSION['_cusupocodi_'].", now())";
                        // var_dump( $sqlI);
                        // die;
                    $resI = $db->query($sqlI);
                    if(empty($resI)){
                        var_dump('retorno vazio 2');
                        return 2;
                    }
                }

                $contador_geral++;
            }
         
            //var_dump(!is_null($dadosSalvar['dctrpcpbdm']));
            // insert contratos 
            $cep = !empty($dadosSalvar['cctrpcccep'])?$dadosSalvar['cctrpcccep']:'null';
            $numero = !empty($dadosSalvar['actrpcnuen'])?$dadosSalvar['actrpcnuen']:'null';
            $csolcosequ = !empty($dadosSalvar['csolcosequ']) ? $dadosSalvar['csolcosequ'] : 'null';
            $ultimo_aditivo = !empty($dadosSalvar['actrpcnuad']) ? $dadosSalvar['actrpcnuad'] : 'null';
            $ultimo_apostilamento = !empty($dadosSalvar['actrpcnuap']) ? $dadosSalvar['actrpcnuap'] : 'null';
            $consorcio = $dadosSalvar['fctrpccons'];
            $dctrpcpbdm = !is_null($dadosSalvar['dctrpcpbdm']) ? "'".$dadosSalvar['dctrpcpbdm']."'" : 'null';

            $sqlC  = "insert into sfpc.tbcontratosfpc ";
            $sqlC .= "(cdocpcsequ, csolcosequ, aforcrsequ, aforcrseq1, corglicodi, ";
            $sqlC .= "actrpcnumc, ectrpcnumf, actrpcanoc, ectrpcobje, ectrpcraza, ";
            $sqlC .= "cctrpcccep, ectrpclogr, actrpcnuen, ectrpccomp, ectrpcbair, ";
            $sqlC .= "nctrpccida, cctrpcesta, ectrpctlct, "; //Campos retirados
            $sqlC .= "cctrpcopex, actrpcpzec, dctrpcinvg, dctrpcfivg, dctrpcinex, ";
            $sqlC .= "dctrpcfiex, nctrpcnmrl, nctrpccgrl, ectrpccpfr, ectrpcidrl, ";
            $sqlC .= "nctrpcoerl, nctrpcufrl, nctrpccdrl, nctrpcedrl, nctrpcnarl, ";
            $sqlC .= "cctrpcecrl, nctrpcprrl, nctrpcmlrl, ectrpctlrl, nctrpcnmgt, ";
            $sqlC .= "nctrpccpfg, nctrpcmtgt, nctrpcmlgt, ectrpctlgt, vctrpcvlor, vctrpceant, vctrpcsean, actrpcnuad, actrpcnuap, dctrpccada, ";
            $sqlC .= "cusupocodi, ectrpcremf, ";
            $sqlC .= "fctrpcserc, dctrpcpbdm, fctrpcobra, tctrpculat, ctpcomcodi, fctrpccons, adocpcnupa, adocpcvapa, ectrpnuf2, cpnccpcodi) ";
            $sqlC .= "values (";
            $sqlC .= strtoupper($dadosSalvar['cdocpcsequ']).", ".$csolcosequ.", ".strtoupper($dadosSalvar['aforcrsequ']).", ".strtoupper($dadosSalvar['aforcrsequ']).", ".strtoupper($dadosSalvar['corglicodi']).", "; 
            $sqlC .= strtoupper($dadosSalvar['actrpcnumc']).", '".strtoupper($dadosSalvar['ectrpcnumf'])."', ".strtoupper($dadosSalvar['actrpcanoc']).", '".strtoupper($dadosSalvar['ectrpcobje'])."', '".strtoupper($dadosSalvar['ectrpcraza'])."', ";
            $sqlC .=  $cep.", '".strtoupper($dadosSalvar['ectrpclogr'])."', ".$numero.", '".strtoupper($dadosSalvar['ectrpccomp'])."', '".strtoupper($dadosSalvar['ectrpcbair'])."', ";
            $sqlC .= "'".strtoupper($dadosSalvar['nctrpccida'])."', '".strtoupper($dadosSalvar['cctrpcesta'])."', '".strtoupper($dadosSalvar['ectrpctlct'])."', '"; //Campos retirados
            $sqlC .= strtoupper($dadosSalvar['cctrpcopex'])."', ".strtoupper($dadosSalvar['actrpcpzec']).", ".strtoupper($dadosSalvar['dctrpcinvg']).", ".strtoupper($dadosSalvar['dctrpcfivg']).", ".strtoupper($dadosSalvar['dctrpcinex']).", ";
            $sqlC .= strtoupper($dadosSalvar['dctrpcfiex']).", '".strtoupper($dadosSalvar['nctrpcnmrl'])."', '".strtoupper($dadosSalvar['nctrpccgrl'])."', '".strtoupper($dadosSalvar['ectrpccpfr'])."', '".strtoupper($dadosSalvar['ectrpcidrl'])."', ";
            $sqlC .= "'".strtoupper($dadosSalvar['nctrpcoerl'])."', '".strtoupper($dadosSalvar['nctrpcufrl'])."', '".strtoupper($dadosSalvar['nctrpccdrl'])."', '".strtoupper($dadosSalvar['nctrpcedrl'])."', '".strtoupper($dadosSalvar['nctrpcnarl'])."', ";
            $sqlC .= "'".strtoupper($dadosSalvar['cctrpcecrl'])."', '".strtoupper($dadosSalvar['nctrpcprrl'])."', '".$dadosSalvar['nctrpcmlrl']."', '".strtoupper($dadosSalvar['ectrpctlrl'])."', '".strtoupper($dadosSalvar['nctrpcnmgt'])."', ";
            $sqlC .= "'".strtoupper($dadosSalvar['nctrpccpfg'])."', '".strtoupper($dadosSalvar['nctrpcmtgt'])."', '".$dadosSalvar['nctrpcmlgt']."', '".strtoupper($dadosSalvar['ectrpctlgt'])."', ". floatvalue($dadosSalvar['vctrpcvlor']) .",  ". floatvalue($dadosSalvar['vctrpceant']) .", '". floatvalue($dadosSalvar['vctrpcsean']) ."', ". $ultimo_aditivo .", ". $ultimo_apostilamento .", now(), '";
            $sqlC .= $dadosSalvar['cusupocodi']."', '".$dadosSalvar['ectrpcremf']."', ";
            $sqlC .= "'".strtoupper($dadosSalvar['fctrpcserc'])."', ".$dctrpcpbdm.", '".strtoupper($dadosSalvar['fctrpcobra'])."', now(), ".$dadosSalvar['ctpcomcodi'].", '".$consorcio."', ".$dadosSalvar['adocpcnupa'].", ".$dadosSalvar['adocpcvapa'].", '".$dadosSalvar['ectrpnuf2']."', ".$dadosSalvar['cpnccpcodi'].")";
           
            $resC = $db->query($sqlC);
           if(empty($resC)){
           
               return 4;
           }

            //insere forncedor em caso de Consórcio
            if(!empty($dadosSalvar['fornecedor'])){
                for($i=0; $i<count($dadosSalvar['fornecedor']); $i++){
                //var_dump($dadosSalvar['fornecedor'][$i]->aforcrsequ);die;
                    $sqlf = " INSERT INTO sfpc.tbcontratofornecedor (cdocpcsequ, aforcrsequ, cusupocodi, tconfrulat) ";
                    $sqlf .= " VALUES (".$dadosSalvar['cdocpcsequ'].", ".$dadosSalvar['fornecedor'][$i]->aforcrsequ.", ".$dadosSalvar['cusupocodi'].", now())";
               //      var_dump($sqlf);die('1');
                    $resf = $db->query($sqlf);
                    if(empty($resf)){
                        // var_dump('erro 3');die;
                        return 3;
                    }
                }
            }

          
                //die;
                //die;
            return true;
        }

        public function insertsContratoIncluir($dadosSalvar){
            $db = $this->conexaoDb;
            //Insere Documento
            $sqlD = "insert into sfpc.tbdocumentosfpc (cdocpcsequ, ctidocsequ, ctipgasequ, csitdcsequ, cfasedsequ, cusupocodi, tdocpculat)";
            $sqlD .= "values  (".$dadosSalvar['cdocpcsequ'].", 1, ";
            $sqlD .= !empty($dadosSalvar['ctipgasequ']) ? $dadosSalvar['ctipgasequ'].", " : 'null'.", "; 
            $sqlD .= "1, 1, ".$dadosSalvar['cusupocodi'].", now())";
            $resD = $db->query($sqlD);
            if(empty($resD)){
                return 1;
            }

            $dadosSalvar['identificador'] = 'itemDocumento';
            
            $dbDados = $this->selectsContratoIncluir($dadosSalvar);
            //Insere Itens Documento
            if(!empty($dbDados) && ($dadosSalvar['origemScc'] == "LICITACAO")){
                for($i=0; $i < count($dbDados); $i++){
                    $sqlI = "insert into sfpc.tbitemdocumento (cdocpcsequ, citedosequ, citedoorde, cmatepsequ, cservpsequ, vitedovlun, vitedovexe, eitedoserv, eitedomarc, eitedomode, aitedoqtso, aitedoqtex, cusupocodi, titedoulat ) ";
                    $sqlI .= "values  (".strtoupper($dadosSalvar['cdocpcsequ']).", ".strtoupper($dbDados[$i]->citelpsequ).",  ".strtoupper($dbDados[$i]->aitelporde).", "; 
                    $sqlI .= !empty($dbDados[$i]->cmatepsequ)? strtoupper($dbDados[$i]->cmatepsequ).", ": "null, "; 
                    $sqlI .= !empty($dbDados[$i]->cservpsequ) ? strtoupper($dbDados[$i]->cservpsequ).", ": "null, ";                           
                    $sqlI .= !empty($dbDados[$i]->vitelpvlog)? strtoupper($dbDados[$i]->vitelpvlog).", "         : "null, ";                            
                    $sqlI .= !empty($dbDados[$i]->vitelpvexe)? strtoupper($dbDados[$i]->vitelpvexe).", "         : "null, "; 
                    $sqlI .= !empty($dbDados[$i]->eitelpdescse)   ? "'".strtoupper($dbDados[$i]->eitelpdescse)."', "    : "'', "; 
                    $sqlI .= !empty($dbDados[$i]->eitelpmarc)     ? "'".strtoupper($dbDados[$i]->eitelpmarc)."', "    : "'', "; 
                    $sqlI .= !empty($dbDados[$i]->eitelpmode)     ? "'".strtoupper($dbDados[$i]->eitelpmode)."', "   : "'', "; 
                    $sqlI .= strtoupper($dbDados[$i]->aitelpqtso).", ".strtoupper($dbDados[$i]->aitelpqtex).", ".$_SESSION['_cusupocodi_'].", now())";
                    $resI = $db->query($sqlI);
                    if(empty($resI)){
                        return 2;
                    }
                }
            }elseif(!empty($dbDados) && ($dadosSalvar['origemScc'] != "LICITACAO")){
                for($i=0; $i < count($dbDados); $i++){
                    $sqlI = "insert into sfpc.tbitemdocumento (cdocpcsequ, citedosequ, citedoorde, cmatepsequ, cservpsequ, aitedoqtso, vitedovlun, aitedoqtex, vitedovexe, eitedoserv, eitedomarc, eitedomode, cusupocodi, titedoulat )"; 
                    $sqlI .= "values  (".$dadosSalvar['cdocpcsequ'].", ".strtoupper($dbDados[$i]->citescsequ).", ".strtoupper($dbDados[$i]->aitescorde).", ";
                    $sqlI .= !empty($dbDados[$i]->cmatepsequ)   ? strtoupper($dbDados[$i]->cmatepsequ).", " : "null, "; 
                    $sqlI .= !empty($dbDados[$i]->cservpsequ)   ? strtoupper($dbDados[$i]->cservpsequ).", "         : "null, "; 
                    $sqlI .= !empty($dbDados[$i]->aitescqtso)   ? strtoupper($dbDados[$i]->aitescqtso).", "         : "null, "; 
                    $sqlI .= !empty($dbDados[$i]->vitescunit)   ? strtoupper($dbDados[$i]->vitescunit).", "         : "null, "; 
                    $sqlI .= !empty($dbDados[$i]->aitescqtex)   ? strtoupper($dbDados[$i]->aitescqtex).", "         : "null, "; 
                    $sqlI .= !empty($dbDados[$i]->vitescvexe)   ? strtoupper($dbDados[$i]->vitescvexe).", "         : "null, "; 
                    $sqlI .= !empty($dbDados[$i]->eitescdescse) ? "'".strtoupper($dbDados[$i]->eitescdescse)."', "  : "'', "; 
                    $sqlI .= !empty($dbDados[$i]->eitescmarc)   ? "'".strtoupper($dbDados[$i]->eitescmarc)."', "    : "'', "; 
                    $sqlI .= !empty($dbDados[$i]->eitescmode)   ? "'".strtoupper($dbDados[$i]->eitescmode)."', "   : "'', "; 
                    $sqlI .= $_SESSION['_cusupocodi_'].", now())";
                    $resI = $db->query($sqlI);
                    if(empty($resI)){
                        return 2;
                    }
                }
            }
            $dadosSalvar['ectrpcobje'] = RetiraAcentos(removeCaracteresEspeciais($dadosSalvar['ectrpcobje']));
            // insert contratos 
            $sqlC  = "insert into sfpc.tbcontratosfpc ";
            $sqlC .= "(cdocpcsequ, csolcosequ, aforcrsequ, aforcrseq1, corglicodi, ";
            $sqlC .= "actrpcnumc, ectrpcnumf, actrpcanoc, ectrpcobje, ectrpcraza, ";
            $sqlC .= "cctrpcccep, ectrpclogr, actrpcnuen, ectrpccomp, ectrpcbair, ";
            $sqlC .= "nctrpccida, cctrpcesta, ectrpctlct, "; //Campos retirados
            $sqlC .= "cctrpcopex, actrpcpzec, dctrpcinvg, dctrpcfivg, dctrpcinex, ";
            $sqlC .= "dctrpcfiex, nctrpcnmrl, nctrpccgrl, ectrpccpfr, ectrpcidrl, ";
            $sqlC .= "nctrpcoerl, nctrpcufrl, nctrpccdrl, nctrpcedrl, nctrpcnarl, ";
            $sqlC .= "cctrpcecrl, nctrpcprrl, nctrpcmlrl, ectrpctlrl, nctrpcnmgt, ";
            $sqlC .= "nctrpccpfg, nctrpcmtgt, nctrpcmlgt, ectrpctlgt, dctrpccada, ";
            $sqlC .= "vctrpcvlor, cusupocodi, ectrpcremf, fctrpcserc, ";
            $sqlC .= !empty($dadosSalvar['dctrpcpbdm']) ? "dctrpcpbdm, " : "";
            $sqlC .= "fctrpcobra, tctrpculat, fctrpccons, ctpcomcodi, adocpcnupa, adocpcvapa, ectrpnuf2, cpnccpcodi) ";
            
            $cep = !empty($dadosSalvar['cctrpcccep'])?$dadosSalvar['cctrpcccep']:'null';
            $numero = !empty($dadosSalvar['actrpcnuen'])?$dadosSalvar['actrpcnuen']:'null';
            $sqlC .= "values (";
            $sqlC .= strtoupper($dadosSalvar['cdocpcsequ']).", ".strtoupper($dadosSalvar['csolcosequ']).", ".strtoupper($dadosSalvar['aforcrsequ']).", ".strtoupper($dadosSalvar['aforcrsequ']).", ".strtoupper($dadosSalvar['corglicodi']).", "; 
            $sqlC .= strtoupper($dadosSalvar['actrpcnumc']).", '".strtoupper($dadosSalvar['ectrpcnumf'])."', ".strtoupper($dadosSalvar['actrpcanoc']).", '".strtoupper($dadosSalvar['ectrpcobje'])."', '".strtoupper($dadosSalvar['ectrpcraza'])."', ";
            $sqlC .=  $cep.", '".strtoupper($dadosSalvar['ectrpclogr'])."', ".$numero.", '".strtoupper($dadosSalvar['ectrpccomp'])."', '".strtoupper($dadosSalvar['ectrpcbair'])."', ";
            $sqlC .= "'".strtoupper($dadosSalvar['nctrpccida'])."', '".strtoupper($dadosSalvar['cctrpcesta'])."', '".strtoupper($dadosSalvar['ectrpctlct'])."', '"; //Campos retirados
            $sqlC .= strtoupper($dadosSalvar['cctrpcopex'])."', ".strtoupper($dadosSalvar['actrpcpzec']).", ".strtoupper($dadosSalvar['dctrpcinvg']).", ".strtoupper($dadosSalvar['dctrpcfivg']).", ".strtoupper($dadosSalvar['dctrpcinex']).", ";
            $sqlC .= strtoupper($dadosSalvar['dctrpcfiex']).", '".strtoupper($dadosSalvar['nctrpcnmrl'])."', '".strtoupper($dadosSalvar['nctrpccgrl'])."', '".strtoupper($dadosSalvar['ectrpccpfr'])."', '".strtoupper($dadosSalvar['ectrpcidrl'])."', ";
            $sqlC .= "'".strtoupper($dadosSalvar['nctrpcoerl'])."', '".strtoupper($dadosSalvar['nctrpcufrl'])."', '".strtoupper($dadosSalvar['nctrpccdrl'])."', '".strtoupper($dadosSalvar['nctrpcedrl'])."', '".strtoupper($dadosSalvar['nctrpcnarl'])."', ";
            $sqlC .= "'".strtoupper($dadosSalvar['cctrpcecrl'])."', '".strtoupper($dadosSalvar['nctrpcprrl'])."', '".$dadosSalvar['nctrpcmlrl']."', '".strtoupper($dadosSalvar['ectrpctlrl'])."', '".strtoupper($dadosSalvar['nctrpcnmgt'])."', ";
            $sqlC .= "'".strtoupper($dadosSalvar['nctrpccpfg'])."', '".strtoupper($dadosSalvar['nctrpcmtgt'])."', '".$dadosSalvar['nctrpcmlgt']."', '".strtoupper($dadosSalvar['ectrpctlgt'])."', now(), ";
            $sqlC .= $dadosSalvar['vctrpcvlor'].", '".$dadosSalvar['cusupocodi']."', '".$dadosSalvar['ectrpcremf']."', '".strtoupper($dadosSalvar['fctrpcserc'])."', ";
            $sqlC .= !empty($dadosSalvar['dctrpcpbdm']) ? "'".$dadosSalvar['dctrpcpbdm']."', " : "";
            $sqlC .= "'".strtoupper($dadosSalvar['fctrpcobra'])."', now(), '".strtoupper($dadosSalvar['fctrpccons'])."', '".$dadosSalvar['ctpcomcodi']."', ".$dadosSalvar['adocpcnupa'].", ".$dadosSalvar['adocpcvapa'].", '".$dadosSalvar['ectrpnuf2']."', ";
            $sqlC .= $dadosSalvar['cpnccpcodi'].")";
            
            $resC = executarSQL($db, $sqlC);

            if (db::isError($resC)) {
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $resC");
            } else {
                if(empty($resC)){
                    return 4;
                }
             }

            
             if(!empty($dadosSalvar['fornecedor'])){
                for($i=0; $i>count($dadosSalvar['fornecedor']); $i++){
                    $sqlf = " INSERT INTO sfpc.tbcontratofornecedor (cdocpcsequ, aforcrsequ, cusupocodi, tconfrulat) ";
                    $sqlf .= " VALUES (".$dadosSalvar['cdocpcsequ'].", ".$_SESSION['dadosFornecedor'][$i]->aforcrsequ.", ".$dadosSalvar['cusupocodi'].", now())";
                    var_dump($sqlf);die;
                    $resf = $db->query($sqlf);
                    if (db::isError($resf)) {
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $resf");
                    } else {
                        if(empty($resf)){
                            return 3;
                        }
                    }
                }

            }
            return true;
        }
        //Esta função vai ser usada caso encontre erro em algum dos  procedimentos acima
        public function deletsContratoIncluir($caso, $cdocpcsequ){
            $db = $this->conexaoDb;
            switch($caso){
                case 2: 
                    $delete = "delete from sfpc.tbitemdocumento where cdocpcsequ = $cdocpcsequ";
                    $resultado = $db->query($delete);
                    $delete = "delete from sfpc.tbdocumentosfpc where cdocpcsequ = $cdocpcsequ";
                    $resultado = $db->query($delete);
                break;
                case 3:
                    $delete = "delete from sfpc.tbitemdocumento where cdocpcsequ = $cdocpcsequ";
                    $resultado = $db->query($delete);
                    $delete = "delete from sfpc.tbdocumentosfpc where cdocpcsequ = $cdocpcsequ";
                    $resultado = $db->query($delete);
                break;
                case 4:
                    $delete = "delete from sfpc.tbcontratofornecedor where cdocpcsequ = $cdocpcsequ";
                    $resultado = $db->query($delete);
                    $delete1 = "delete from sfpc.tbitemdocumento where cdocpcsequ = $cdocpcsequ";
                    $resultado = $db->query($delete1);
                    $delete = "delete from sfpc.tbdocumentosfpc where cdocpcsequ = $cdocpcsequ";
                    $resultado = $db->query($delete);
                break;
            }
        }

        public function PesqusiaItensSCC($csolcosequ, $aforcrsequ, $tipoCompra, $citelpnuml){
            for($i=0;$i<count($citelpnuml);$i++){
                if($citelpnuml[$i]=='0'){
                    $checaLotesInvalidos = true;
                }
            }
            if($tipoCompra == "LICITACAO"){
                $sql = "select iteml.* from sfpc.tbitemlicitacaoportal as iteml
                        inner join sfpc.tbsolicitacaolicitacaoportal as scclic on scclic.clicpoproc = iteml.clicpoproc 
                                        and iteml.alicpoanop = scclic.alicpoanop 
                                        and iteml.cgrempcodi = scclic.cgrempcodi
                                        and iteml.ccomlicodi = scclic.ccomlicodi 
                                        and iteml.corglicodi = scclic.corglicodi 
                        inner join sfpc.tbsolicitacaocompra as scc on scc.csolcosequ = scclic.csolcosequ
                        where scc.csolcosequ = ".$csolcosequ."
                        and iteml.aforcrsequ = ".$aforcrsequ;
                if(is_null($citelpnuml[1]) && !$checaLotesInvalidos){
                    $sql .= " and iteml.citelpnuml = $citelpnuml[0]";
                }
                else if(!is_null($citelpnuml[1]) && !$checaLotesInvalidos){  //Madson Consertar.
                    $sqlcitelpnuml ="";
                    for($i=0;$i<(count($citelpnuml)-1);$i++){
                            $sqlcitelpnuml .= "'$citelpnuml[$i]', ";
                    }
                    $sqlcitelpnuml .= "'$citelpnuml[$i]'";
                    $sql .= " and iteml.citelpnuml in ($sqlcitelpnuml)";
                }
                $sql .= " order by aitelporde";
                
                $resultado = executarSQL($this->conexaoDb, $sql);
                while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                    $dbDados[] = $retorno;
                }
                
                return $dbDados;

            }elseif($dadosSalvar['origemScc'] != "LICITACAO"){

                $sql = "select iscc.* 
                        from sfpc.tbitemsolicitacaocompra as iscc 
                        inner join sfpc.tbsolicitacaocompra as scc on scc.csolcosequ = iscc.csolcosequ
                        where scc.csolcosequ = ".$csolcosequ."
                        and iscc.aforcrsequ = ".$aforcrsequ."
                        order by aitescorde";
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

        public function VerificaSeExisteContrato($NumContrato){
              $sql = "SELECT CDOCPCSEQU FROM SFPC.TBCONTRATOSFPC WHERE ECTRPCNUMF = '".$NumContrato."'";
              $resultado = executarSQL($this->conexaoDb, $sql);
              $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
                if(!empty($retorno)){
                    return $retorno;
                }else{
                    return false;
                }
        }
        public function VerificaSeExisteContratoComEssaSCC($NumSCC,$FornecedorScc,$OrigemScc,$citelpnuml){
            $sql = "SELECT CDOCPCSEQU FROM SFPC.TBCONTRATOSFPC WHERE csolcosequ = '".$NumSCC."'";
            
              $resultado = executarSQL($this->conexaoDb, $sql);
              $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
        

                if(!empty($retorno)){
                    if($OrigemScc == "LICITACAO"){
                        $sqlcitelpnuml ="";
                        for($i=0;$i<(count($citelpnuml)-1);$i++){
                                $sqlcitelpnuml .= "'$citelpnuml[$i]', ";
                        }
                        $sqlcitelpnuml .= "'$citelpnuml[$i]'";
                        // $sqlItem = "select count(c.cdocpcsequ) as totalItens
                        // from sfpc.tbitemdocumento as tid 
                        // inner join sfpc.tbcontratosfpc as c on (c.cdocpcsequ = tid.cdocpcsequ) 
                        // where c.aforcrsequ = $FornecedorScc and c.csolcosequ = $NumSCC
                        // and tid.citedosequ in (select iteml.citelpsequ from sfpc.tbsolicitacaocompra scc 
                        //                     inner join sfpc.tbsolicitacaolicitacaoportal solic on solic.csolcosequ = scc.csolcosequ 
                        //                     inner join sfpc.tbitemlicitacaoportal iteml on solic.clicpoproc = iteml.clicpoproc 
                        //                     and solic.alicpoanop = iteml.alicpoanop 
                        //                     and solic.cgrempcodi = iteml.cgrempcodi
                        //                     and solic.ccomlicodi = iteml.ccomlicodi 
                        //                     where scc.csolcosequ = $NumSCC 
                        //                     and iteml.aforcrsequ = $FornecedorScc ";
                        // if($citelpnuml[0] != 0){                        
                        //     $sqlItem .= "and iteml.citelpnuml in ($sqlcitelpnuml) ";
                        // }           
                        $sqlItem = "select scc.csolcosequ, iteml.citelpsequ, iteml.citelpnuml from  sfpc.tbsolicitacaocompra scc
                        inner join sfpc.tbsolicitacaolicitacaoportal solic on solic.csolcosequ = scc.csolcosequ
                        inner join sfpc.tbitemlicitacaoportal iteml on solic.clicpoproc = iteml.clicpoproc
                        and solic.alicpoanop = iteml.alicpoanop and solic.cgrempcodi = iteml.cgrempcodi
                        and solic.ccomlicodi = iteml.ccomlicodi
                        where scc.csolcosequ = $NumSCC and iteml.aforcrsequ = $FornecedorScc and fitelplogr = 'S'
                        and iteml.citelpsequ not in (select itd.citedosequ from sfpc.tbcontratosfpc con
                        inner join sfpc.tbitemdocumento itd on itd.cdocpcsequ = con.cdocpcsequ
                        where con.csolcosequ = scc.csolcosequ and con.aforcrsequ = iteml.aforcrsequ)";
                       
                        $resultadoItem = executarSQL($this->conexaoDb, $sqlItem);
                        $dadosScc = array();
                        while($resultadoItem->fetchInto($retornoItem, DB_FETCHMODE_OBJECT)){
                            $dadosScc[] = $retornoItem;
                        }
                        // Juntar todos os lotes
                        for($i=0; $i<count($dadosScc); $i++){
                            $lotesSContrato = $dadosScc[$i]->citelpnuml;
                        }
                        //Comparar se algum selecionado não está na lista do banco~
                        $lotePossuiContrato = false;
                        if($citelpnuml[0] != 0){ //Quando a opção todos não é selecionada
                            for($i=0; $i<count($citelpnuml); $i++){
                                if(count($lotesSContrato)>1){
                                    if(!in_array($citelpnuml[$i], $lotesSContrato)){
                                        $lotePossuiContrato = true;
                                    }
                                }else{
                                    if($citelpnuml[$i] != $lotesSContrato){
                                        $lotePossuiContrato = true;
                                    }
                                }
                            }
                        }else{ // quando a opção 'TODOS' é selecionada
                            if($citelpnuml>1){
                                for($i=1; $i<count($citelpnuml); $i++){
                                    if(count($lotesSContrato)>1){
                                        if(!in_array($citelpnuml[$i], $lotesSContrato)){
                                            $lotePossuiContrato = true;
                                        }
                                    }else{
                                        if($citelpnuml[$i] != $lotesSContrato){
                                            $lotePossuiContrato = true;
                                        }
                                    }
                                    
                                }
                            }
                        }
                        if(empty($retornoItem) or $lotePossuiContrato == true){ 
                            return true;
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
                            return true;
                        }else{
                            return false;
                        }
                    }
                }else{
                    return false;
                }
        }

        public function getCategoriaProcesso(){
            $sql = "select cp.cpnccpcodi, cp.epnccpnome from sfpc.tbpncpdominiocategoriaprocesso cp order by cp.cpnccpcodi";
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
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
                for($i =0; $i <= strlen($mascara); $i++){
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
                for($i =0; $i <= strlen($mascara); $i++){
                    if($mascara[$i] == "#"){
                        if(isset($valor[$k])){
                           $maskared .= $valor[$k++];
                        }
                    }else{
                        $maskared .= $mascara[$i];
                    }
                }
                // var_dump($maskared);
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