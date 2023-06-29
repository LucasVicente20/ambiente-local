<?php 
 session_start(); 
 require_once dirname(__FILE__) . '/../funcoes.php';
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: ClassContratoConsolidado.php
# Autor:    Eliakim Ramos | João Madson
# Data:     13/10/2020
# -------------------------------------------------------------------------
# -------------------------------------------------------------------------
# Portal da DGCO
# Programa: abaContratoConsolidado.html
# Autor:    Marcello Albuquerque
# Data:     19/07/2021
# Objetivo: Ajuste no download dos arquivos do aditivo CR #249865
#--------------------------------------------------------------------------->
# Autor:    Osmar Celestino | Lucas Vicente
# Data:     07/06/2022
# CR #244295
# -------------------------------------------------------------------------
Class ContratoConsolidado {

    public function getAditivosByContratoConsolidado($cdocpseq1){
        $db = conexao();
        $sql = "select DOC.CFASEDSEQU as fase, * from sfpc.tbaditivo as adv, SFPC.TBDOCUMENTOSFPC DOC where adv.CDOCPCSEQU = DOC.CDOCPCSEQU and DOC.cfasedsequ = 4 and DOC.ctidocsequ = 2 AND adv.cdocpcseq1 =".$cdocpseq1;
        $resultado = executarSQL($db, $sql);
        $dados = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dados[] = $retorno;
        }
        return $dados;
    }
    public function GetDescricaoTipoAditivo($codAditivo){
        $db = conexao();
        $sql = "select * from sfpc.tbtipoaditivo where ctpadisequ =".$codAditivo;
        $resultado = executarSQL($db, $sql);
        $dados = array();
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
        return $retorno;
    }
    public function GetDescricaoTipoApostilamento($codApostilamento){
        $db = conexao();
        $sql = "select * from sfpc.tbtipoapostilamento where ctpaposequ =".$codApostilamento;
        $resultado = executarSQL($db, $sql);
        $dados = array();
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
        return $retorno;
    }
    public function GetDocumentosAnexosAdtivo($codDoc,$numeroaditivo,$sequdocanexo=null,$edcanxnome=null){
        $db = conexao();
        $sql  = " select aaditinuad, co.cdocpcsequ AS sequdocumento, cdcanxsequ AS sequdocanexo, edcanxnome AS nomearquivo, ";
        $sql .= " tdcanxcada AS datacadasarquivo, tdcanxulat AS ultimasatualizacao,co.cusupocodi as usermod, ";
        $sql .= " encode(idcanxarqu,'base64') as arquivo from sfpc.tbdocumentoanexo as da inner join sfpc.tbaditivo as ad on ";
        $sql .= " (da.cdocpcsequ = ad.cdocpcsequ) inner join sfpc.tbcontratosfpc as co on (co.cdocpcsequ = ad.cdocpcseq1) ";
        $sql .= " where co.cdocpcsequ =".$codDoc. "AND ad.aaditinuad =".$numeroaditivo;
        
        if(!empty($sequdocanexo)){
            $sql .=" AND cdcanxsequ =".$sequdocanexo;
        }
        if(!empty($edcanxnome)){
            $sql .=" AND edcanxnome ='".$edcanxnome."'";
        }
        
        $resultado = executarSQL($db, $sql);
        //var_dump($resultado);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    public function getApostilamentosContrato($codCont) 
        {
            $db = conexao();
            $sql = "SELECT * FROM SFPC.TBAPOSTILAMENTO as APOS inner join sfpc.tbdocumentosfpc as DOC  on (APOS.cdocpcsequ = DOC.cdocpcsequ) ";
            $sql .= " inner join sfpc.tbsituacaodocumento as SD on (DOC.cfasedsequ = SD.cfasedsequ and DOC.csitdcsequ = SD.csitdcsequ)";
            $sql .= " WHERE CDOCPCSEQ2 = " .$codCont . " and SD.cfasedsequ = 6 and SD.csitdcsequ = 1 ORDER BY AAPOSTNUAP ASC ";
            $resultado = executarSQL($db, $sql);
            $dadosRetorno = array();
            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

    public function getMedicoesContrato($codCont)
        {
            $db = conexao();
            $sql = "SELECT CONT.ECTRPCNUMF, MEDI.AMEDCONUME, MEDI.CMEDCOSEQU, MEDI.DMEDCOINIC, MEDI.DMEDCOFINL, MEDI.VMEDCOVALM ";// -- MEDI.DMEDCOPAPR "; CR#244301
            $sql .= "FROM SFPC.TBCONTRATOSFPC CONT ";
            $sql .= "INNER JOIN SFPC.TBMEDICAOCONTRATO MEDI ";
            $sql .= "ON CONT.CDOCPCSEQU = MEDI.CDOCPCSEQU WHERE CONT.CDOCPCSEQU = " .$codCont . " ORDER BY MEDI.AMEDCONUME ASC";
            $resultado = executarSQL($db, $sql);
            $dadosRetorno = array();
            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

    public function GetDocumentosAnexosApostilamento($codDoc,$aapostnuap,$sequdocanexo=null,$edcanxnome=null){
        $db = conexao();
        $sql  = " select ap.aapostnuap, ap.cdocpcsequ AS sequdocumento, cdcanxsequ AS sequdocanexo, edcanxnome AS nomearquivo, ";
        $sql .= " tdcanxcada AS datacadasarquivo, tdcanxulat AS ultimasatualizacao, ap.cusupocodi as usermod, ";
        $sql .= " encode(idcanxarqu,'base64') as arquivo from sfpc.tbdocumentoanexo as da inner join sfpc.tbapostilamento as ap on ";
        $sql .= " (da.cdocpcsequ = ap.cdocpcsequ)  where ap.cdocpcseq2 =".$codDoc." and ap.aapostnuap=".$aapostnuap ;
        if(!empty($sequdocanexo)){
            $sql .=" AND cdcanxsequ =".$sequdocanexo;
        }
        if(!empty($edcanxnome)){
            $sql .=" AND edcanxnome ='".$edcanxnome."'";
        }
        $resultado = executarSQL($db, $sql);
        //var_dump($resultado);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }
    public function getContrato($codCont){
        $db = conexao();
        $sql = "SELECT CONT.CDOCPCSEQU, CONT.csolcosequ as scc, CONT.vctrpcvlor as valororiginal, CONT.ECTRPCNUMF, CONT.vctrpcvlor as valortotalcontrato, CONT.ECTRPCOBJE, CONT.cctrpcopex, 
                    CONT.vctrpcsean as saldoaexecutarcontantico, CONT.vctrpceant as saldoexecutadocontantico, CONT.vctrpcglaa, CONT.vctrpcvlor  FROM SFPC.TBCONTRATOSFPC CONT WHERE CONT.CDOCPCSEQU = ".$codCont;
        
        $resultado = executarSQL($db, $sql);
        $dadosRetorno = array();
        while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    public function getValorTotalMedicao($codDoc){
        $db = conexao();
        $sql = "SELECT ";
        $sql .= " CASE ";
        $sql .= "  WHEN sum(vmedcovalm) IS NULL THEN sum(vimedcvalr) ";
        $sql .=  "  WHEN sum(vmedcovalm) IS NOT NULL THEN sum(vmedcovalm) ";
       $sql .= "  END ";
        $sql .= " AS totalmedicao ";
        $sql .= "FROM sfpc.tbitemmedicaocontrato as it right join sfpc.tbmedicaocontrato as m on (m.cmedcosequ = it.cmedcosequ and m.cdocpcsequ = it.cdocpcsequ ) ";
        $sql .= "where m.cdocpcsequ =".$codDoc;
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
        if(!empty($retorno->totalmedicao)){
            return number_format((floatval($retorno->totalmedicao)),4,',','.');
        }else{
            return number_format((floatval('0.0000')),4,',','.');
        }
    }

    public function GetValorTotalAdtivo($codDoc){
        $db = conexao();
        $sql = "select case when sum(vaditivalr) is null  then sum(vaditivtad) when sum(vaditivalr) is not null ";
        $sql .= " then sum(vaditivalr) end as vtaditivo  from sfpc.tbaditivo adit left join sfpc.tbdocumentosfpc ";
        $sql .= " doc on adit.cdocpcsequ = doc.cdocpcsequ ";
        $sql .= " where  doc.csitdcsequ = 1 and doc.cfasedsequ = 4 and adit.CDOCPCSEQ1 =".$codDoc;
        $resultado = executarSQL($db, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    public function situacaoAditivo(){
        $db = conexao();
        $sql = "select cfasedsequ, csitdcsequ, esitdcdesc FROM sfpc.tbsituacaodocumento where cfasedsequ in (1, 4) and csitdcsequ = 1";
        $resultado = executarSQL($db, $sql);
        
        $dadosRetorno = array();
        while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    public function GetValorTotalApostilamento($codDoc){
        $db = conexao();
        $sql = "select sum(vapostvtap) as vtapost from sfpc.tbapostilamento apost left join sfpc.tbdocumentosfpc doc "; 
        $sql .=" on apost.cdocpcsequ = doc.cdocpcsequ  where apost.CDOCPCSEQ2 = $codDoc and doc.cfasedsequ = 6 and doc.csitdcsequ = 1";
        $resultado = executarSQL($db, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
    }

    public function SaldoAExecutar($codDoc,$totalcontrato){
        $db = conexao();
        $sql = "SELECT ";
        $sql .= " CASE ";
        $sql .= "  WHEN sum(vmedcovalm) IS NULL THEN sum(vimedcvalr) ";
        $sql .=  "  WHEN sum(vmedcovalm) IS NOT NULL THEN sum(vmedcovalm) ";
       $sql .= "  END ";
        $sql .= " AS totalmedicao ";
        $sql .= "FROM sfpc.tbitemmedicaocontrato as it right join sfpc.tbmedicaocontrato as m on (m.cmedcosequ = it.cmedcosequ and m.cdocpcsequ = it.cdocpcsequ ) ";
        $sql .= "where m.cdocpcsequ =".$codDoc;
        $resultado = executarSQL($db, $sql);
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
        if(!empty($retorno->totalmedicao)){
            if(is_float($totalcontrato)){
                $valor = number_format(( $totalcontrato - floatval($retorno->totalmedicao)),4,',','.');
            }else{
                $valor = number_format(( floatval($totalcontrato) - floatval($retorno->totalmedicao)),4,',','.');
            }
            return $valor;
        }else{
            return number_format((floatval($totalcontrato) - floatval('0.0000')),4,',','.');
        }
    }

    public function getDocumentosFicaisEFical($codDoc){
        $db = conexao();
        $sql  = " SELECT fisc.nfiscdtipo as tipofiscal, fisc.nfiscdnmfs as fiscalnome, fisc.efiscdmtfs as fiscalmatricula, fisc.cfiscdcpff as fiscalcpf, fisc.nfiscdmlfs as fiscalemail, ";
        $sql .= " fisc.efiscdtlfs as fiscaltel, docfi.cdocpcsequ as docsequ, docfi.fdocfisitu as docsituacao, fisc.nfiscdencp as entidade FROM sfpc.tbdocumentofiscalsfpc AS docfi ";
        $sql .= " INNER JOIN sfpc.tbfiscaldocumento AS fisc ON (fisc.cfiscdcpff = docfi.cfiscdcpff) INNER JOIN sfpc.tbdocumentoanexo AS doca ON (doca.cdocpcsequ = docfi.cdocpcsequ) ";
        $sql .= " WHERE docfi.cdocpcsequ=".$codDoc;
        
        $resultado = executarSQL($db, $sql);
        $dadosRetorno = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
            $dadosRetorno[] = $retorno;
        }
        return $dadosRetorno;
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

    public function GetDocumentosAnexosApostilamentoAlterado($codDoc, $sequdocanexo = null, $edcanxnome = null){
        $db = conexao();
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
        
        $resultado = executarSQL($db, $sql);
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

    
}
?>