<?php
session_start();
require_once dirname(__FILE__) . '/../funcoes.php';
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: ClassApostilamento.php
# Autor:    Eliakim Ramos | João Madson | Jorge Eduardo | Edson Dionisio
# Data:     03/04/2020
# -------------------------------------------------------------------------
# Autor:    Marcello Albuquerque
# Data:     09/11/2021
# CR #251686 
# -------------------------------------------------------------------------

    class ClassApostilamento
    {
        public $conexaoDb;

        public function __construct() {
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

        function floatvalue($val){
            $val = str_replace(",",".",$val);
            $val = preg_replace('/\.(?=.*\.)/', '', $val);
            return floatval($val);
        }

        function mask($val, $mask)
        {
        $maskared = '';
        $k = 0;
        for($i = 0; $i < strlen($mask); $i++)
        {
            if($mask[$i] == '#')
            {
            if(isset($val[$k]))
                $maskared .= $val[$k++];
            }
            else
            {
                if(isset($mask[$i]))
                    $maskared .= $mask[$i];
                }
            }
        return $maskared;
        }

        public function getContrato($codCont){
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
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        public function getTiposAditivoPorCod($cod){
            $sql = "SELECT etpadidesc FROM sfpc.tbtipoaditivo where ctpadisequ = ". $cod ." and ftpadisitu = 'ATIVO'";
            $resultado = executarSQL($this->conexaoDb, $sql);
            
            $dadosRetorno = array();
            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        public function getTiposAditivo(){
            $sql = "SELECT ctpadisequ, etpadidesc FROM sfpc.tbtipoaditivo where ftpadisitu = 'ATIVO'";
            $resultado = executarSQL($this->conexaoDb, $sql);
            
            $dadosRetorno = array();
            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
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

        // Função para incluir o número máximo do documento para um apostilamento
        public function insereDocumentoAditivo($numMaxDoc){
            $cusupocodi = $_SESSION['_cusupocodi_'];
            $db = $this->conexaoDb;
            //Insere Documento
            $sqlD = "insert into sfpc.tbdocumentosfpc (cdocpcsequ, ctidocsequ, ctipgasequ, cfluxosequ, csitdcsequ, cfasedsequ, cmodocsequ, cusupocodi, tdocpculat, ctidocseq1, cfuchcsequ, cchelisequ)";
            $sqlD .= "values (".$numMaxDoc.", 2, null, null, 1, 3, null, ".$cusupocodi.", now(), null, null, null)";
            $resD = $db->query($sqlD);
            if(empty($resD)){
                return 1;
            }

            return true;
        }

        // Função para incluir o número máximo do documento para um apostilamento
        public function alteraDocumentoAditivo($codigo, $fase){
            $cusupocodi = $_SESSION['_cusupocodi_'];
            $db = $this->conexaoDb;
            //Insere Documento
            $sqlD = "UPDATE SFPC.tbdocumentosfpc SET cfasedsequ = ". $fase ." WHERE cdocpcsequ = " . $codigo;

            $resD = $db->query($sqlD);
            if(empty($resD)){
                return 1;
            }

            return true;
        }

        public function situacaoAditivo(){
            $sql = "select cfasedsequ, csitdcsequ, esitdcdesc FROM sfpc.tbsituacaodocumento where cfasedsequ in (1, 4) and csitdcsequ = 1";
            $resultado = executarSQL($this->conexaoDb, $sql);
            
            $dadosRetorno = array();
            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        public function insereDadosAditivo($dadosSalvar){
            $cusupocodi = $_SESSION['_cusupocodi_'];

            $num_max_documento = $this->maxCodDocumento();

            //PEGANDO O VALOR MÁXIMO DO AADITINUAD
            $sqlMax = "SELECT MAX(AADITINUAD) from SFPC.TBADITIVO WHERE CDOCPCSEQ1 = " .$dadosSalvar['CDOCPCSEQ1'];
           
            $resultadoMax = executarSQL($this->conexaoDb, $sqlMax);
            $resultadoMax->fetchInto($resultadoMax, DB_FETCHMODE_OBJECT);

            if (!empty($resultadoMax)) {
                $numMaxAditivo = intval($resultadoMax->max) + 1;
            }

            $docAditivo = $this->insereDocumentoAditivo($num_max_documento);

            if(empty($dadosSalvar['AADITIAPEA'])){
                $dadosSalvar['AADITIAPEA'] = 0;
            }

            if(empty($dadosSalvar['VADITIVTAD'])){
                $dadosSalvar['VADITIVTAD'] = 0;
            }

            if(empty($dadosSalvar['VADITIREQC'])){
                $dadosSalvar['VADITIREQC'] = 0;
            }

            if(empty($dadosSalvar['DADITIINVG'])){
                $dadosSalvar['DADITIINVG'] = "NULL";
            }else{
                $dadosSalvar['DADITIINVG'] = "'" . $dadosSalvar['DADITIINVG'] ."'";
            }

            if(empty($dadosSalvar['DADITIFIVG'])){
                $dadosSalvar['DADITIFIVG'] = "NULL";
            }else{
                $dadosSalvar['DADITIFIVG'] = "'" . $dadosSalvar['DADITIFIVG'] ."'";
            }

            if(empty($dadosSalvar['DADITIINEX'])){
                $dadosSalvar['DADITIINEX'] = "NULL";
            }else{
                $dadosSalvar['DADITIINEX'] = "'" . $dadosSalvar['DADITIINEX'] ."'";
            }

            if(empty($dadosSalvar['DADITIFIEX'])){
                $dadosSalvar['DADITIFIEX'] = "NULL";
            }else{
                $dadosSalvar['DADITIFIEX'] = "'" . $dadosSalvar['DADITIFIEX'] ."'";
            }

            $sql = "INSERT INTO SFPC.TBADITIVO(
                    CDOCPCSEQU, CDOCPCSEQ1, CTPADISEQU, AADITINUAD, XADITIJUST, DADITICADA, FADITIALPZ,
                    AADITIAPEA, FADITIALVL, FADITIALCT, CADITITALV, VADITIREQC, VADITIVTAD, DADITIINVG, 
                    DADITIFIVG, DADITIINEX, XADITIOBSE, NADITINMRL, EADITICGRL, EADITICPFR, EADITIIDRL, 
                    NADITIOERL, NADITIUFRL, NADITICDRL, NADITIEDRL, NADITINARL, CADITIECRL, 
                    NADITIPRRL, NADITIMLRL, EADITITLRL, CUSUPOCODI, TADITIULAT, NADITIRAZS, EADITILOGR, EADITICOMP, EADITIBAIR, NADITICIDA, CADITIESTA, EADITICPFC, EADITICGCC, DADITIFIEX) ";
            $sql .= "VALUES ("; 
            $sql .= $num_max_documento.", ".$dadosSalvar['CDOCPCSEQ1'].", ".$dadosSalvar['CTPADISEQU'].", ".$numMaxAditivo.", '".$dadosSalvar['XADITIJUST']."', now(), '".$dadosSalvar['FADITIALPZ']."', ";
            $sql .= $dadosSalvar['AADITIAPEA'].", '".$dadosSalvar['FADITIALVL']."', '".$dadosSalvar['FADITIALCT']."', '".$dadosSalvar['CADITITALV']."', '".$this->floatvalue($dadosSalvar['VADITIREQC'])."', '".$this->floatvalue($dadosSalvar['VADITIVTAD'])."', ".$dadosSalvar['DADITIINVG'].", ".$dadosSalvar['DADITIFIVG'].", ". $dadosSalvar['DADITIINEX'].", '". $dadosSalvar['XADITIOBSE']."', '";
            $sql .= $dadosSalvar['NADITINMRL']."', '".$dadosSalvar['EADITICGRL']."', '".$dadosSalvar['EADITICPFR']."', '".$dadosSalvar['EADITIIDRL']."', '".$dadosSalvar['NADITIOERL']."', '".$dadosSalvar['NADITIUFRL']."', '". $dadosSalvar['NADITICDRL']."', '". $dadosSalvar['NADITIEDRL']."', '";
            $sql .= $dadosSalvar['NADITINARL']."', '".$dadosSalvar['CADITIECRL']."', '".$dadosSalvar['NADITIPRRL']."', '".$dadosSalvar['NADITIMLRL']."', '".$dadosSalvar['EADITITLRL']."', ".$cusupocodi .", now(), '".$dadosSalvar['NADITIRAZS']."', '".$dadosSalvar['EADITILOGR']."', '".$dadosSalvar['EADITICOMP']."', '".$dadosSalvar['EADITIBAIR']."', '".$dadosSalvar['NADITICIDA']."', '".$dadosSalvar['CADITIESTA']."', '".$dadosSalvar['EADITICPFC']."', '".$dadosSalvar['EADITICGCC']."', ".$dadosSalvar['DADITIFIEX'].")";
            // print_r($sql);die;
            $resultado = executarSQL($this->conexaoDb, $sql);
      
            if (!empty($resultado)) {
                return $resultado;
            }
            return false;
        }

        public function get_situacaoApostilamento(){
            $sql = "select cfasedsequ, csitdcsequ, esitdcdesc from sfpc.tbsituacaodocumento as sd where csitdcsequ = 1 and cfasedsequ in (1, 6)";
            $resultado = executarSql($this->conexaoDb, $sql);
            $dadosRetorno = array();

            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        public function atualizaDadosAditivo($dadosSalvar){
            $cusupocodi = $_SESSION['_cusupocodi_'];

            if(empty($dadosSalvar['AADITIAPEA'])){
                $dadosSalvar['AADITIAPEA'] = 0;
            }

            if(empty($dadosSalvar['VADITIVTAD'])){
                $dadosSalvar['VADITIVTAD'] = 0;
            }

            if(empty($dadosSalvar['VADITIREQC'])){
                $dadosSalvar['VADITIREQC'] = 0;
            }

            if(empty($dadosSalvar['DADITIINVG'])){
                $dadosSalvar['DADITIINVG'] = "NULL";
            }else{
                $dadosSalvar['DADITIINVG'] = "'" . $dadosSalvar['DADITIINVG'] ."'";
            }

            if(empty($dadosSalvar['DADITIFIVG'])){
                $dadosSalvar['DADITIFIVG'] = "NULL";
            }else{
                $dadosSalvar['DADITIFIVG'] = "'" . $dadosSalvar['DADITIFIVG'] ."'";
            }

            if(empty($dadosSalvar['DADITIINEX'])){
                $dadosSalvar['DADITIINEX'] = "NULL";
            }else{
                $dadosSalvar['DADITIINEX'] = "'" . $dadosSalvar['DADITIINEX'] ."'";
            }

            if(empty($dadosSalvar['DADITIFIEX'])){
                $dadosSalvar['DADITIFIEX'] = "NULL";
            }else{
                $dadosSalvar['DADITIFIEX'] = "'" . $dadosSalvar['DADITIFIEX'] ."'";
            }

            $docAditivo = $this->alteraDocumentoAditivo($dadosSalvar['CDOCPCSEQU'], $dadosSalvar['CFASEDSEQU']);
            
            $sql  = "UPDATE SFPC.TBADITIVO SET".
                    "   CTPADISEQU = ". $dadosSalvar['CTPADISEQU']. ", AADITIAPEA = ". $dadosSalvar['AADITIAPEA'].
                    ",  AADITINUAD = '". $dadosSalvar['AADITINUAD']."', XADITIJUST = '". $dadosSalvar['XADITIJUST']. 
                    "', FADITIALPZ = '". $dadosSalvar['FADITIALPZ']."', FADITIALVL = '". $dadosSalvar['FADITIALVL'].
                    "', FADITIALCT= '".  $dadosSalvar['FADITIALCT']."', CADITITALV = '". $dadosSalvar['CADITITALV']. 
                    "', VADITIREQC = '". $this->floatvalue($dadosSalvar['VADITIREQC'])."', VADITIVTAD = '". $this->floatvalue($dadosSalvar['VADITIVTAD']).
                    "', DADITIINVG = ". $dadosSalvar['DADITIINVG'].", DADITIFIVG = ". $dadosSalvar['DADITIFIVG'].
                    ", DADITIINEX = ". $dadosSalvar['DADITIINEX'].", XADITIOBSE = '". $dadosSalvar['XADITIOBSE'].
                    "', NADITINMRL = '". $dadosSalvar['NADITINMRL']."', EADITICGRL = '". $dadosSalvar['EADITICGRL'].
                    "', EADITICPFR = '". $dadosSalvar['EADITICPFR']."', EADITIIDRL = '". $dadosSalvar['EADITIIDRL'].
                    "', NADITIOERL = '". $dadosSalvar['NADITIOERL']."', NADITIUFRL = '". $dadosSalvar['NADITIUFRL'].
                    "', NADITICDRL = '". $dadosSalvar['NADITICDRL']."', NADITIEDRL = '". $dadosSalvar['NADITIEDRL'].
                    "', NADITINARL = '". $dadosSalvar['NADITINARL']."', CADITIECRL = '". $dadosSalvar['CADITIECRL'].
                    "', NADITIPRRL = '". $dadosSalvar['NADITIPRRL']."', NADITIMLRL = '". $dadosSalvar['NADITIMLRL'].
                    "', EADITITLRL = '". $dadosSalvar['EADITITLRL']."', CUSUPOCODI= ". $cusupocodi .", TADITIULAT = now()".
                    ", NADITIRAZS = '". $dadosSalvar['NADITIRAZS']."', EADITILOGR = '". $dadosSalvar['EADITILOGR'].
                    "', EADITICOMP = '". $dadosSalvar['EADITICOMP']."', EADITIBAIR = '". $dadosSalvar['EADITIBAIR'].
                    "', NADITICIDA = '". $dadosSalvar['NADITICIDA']."', CADITIESTA = '". $dadosSalvar['CADITIESTA'].
                    "', EADITICPFC = '". $dadosSalvar['EADITICPFC']."', EADITICGCC = '". $dadosSalvar['EADITICGCC']."', DADITIFIEX = ".$dadosSalvar['DADITIFIEX'];
         
            $sql .= " WHERE CDOCPCSEQU = ". $dadosSalvar['CDOCPCSEQU']." AND CDOCPCSEQ1 = ". $dadosSalvar['CDOCPCSEQ1'];
           
            $resultado = executarSQL($this->conexaoDb, $sql);
            
            if($dadosSalvar['CTPADISEQU'] == 13){
                $updateForn = $this->updatefornecedorPorAditivo($dadosSalvar);
            }
            // Madson
            if (!empty($resultado)) {
                return $resultado;
            }
            return false;
        }

        public function getAditivoContrato($codCont, $codRegistro)
        {
            //to_char((daditiinex + aaditiapea), 'DD/MM/YYYY') as 
            $sql  = "SELECT DOC.CFASEDSEQU as fase, cctrpcopex, CASE WHEN cctrpcopex = 'M' AND DADITIFIEX IS NULL THEN to_char(cast(daditiinex AS DATE) + ((aaditiapea)::text || ' ' || 'MONTHS')::interval, 'DD/MM/YYYY') WHEN cctrpcopex = 'M' AND DADITIFIEX IS NOT NULL THEN to_char(DADITIFIEX, 'DD/MM/YYYY') WHEN cctrpcopex = 'D' AND DADITIFIEX IS NULL THEN to_char(daditiinex + ((aaditiapea)::text || ' ' || 'DAYS')::interval, 'DD/MM/YYYY') WHEN cctrpcopex = 'D' THEN to_char(DADITIFIEX, 'DD/MM/YYYY') END AS data_fim, CONT.CDOCPCSEQU, CDOCPCSEQ1, CTPADISEQU, AADITINUAD, XADITIJUST, DADITICADA, AADITIAPEA, ";
            $sql .= "FADITIALPZ, FADITIALVL, FADITIALCT, CADITITALV, VADITIREQC, VADITIVTAD, DADITIINVG, ";
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
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
                $dadosRetorno = $retorno;
            }
            return $dadosRetorno;
        }

        public function getDadosFiscal($cod){
            $sql = "select cfiscdcpff from sfpc.tbdocumentofiscalsfpc where cdocpcsequ = ". $cod;
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        public function getApostilamento($codCont){
            $sql = "SELECT cdocpcseq2, ctpaposequ, dapostcada, vapostvtap, aapostnuap, DOC.cdocpcsequ, napostnmgt, napostcpfg, eaposttlgt, napostmtgt, napostmlgt, eapostmemc, iapostmemc, eapostnagt, iapostaqgt, vapostretr, SIT.CFASEDSEQU FROM SFPC.tbapostilamento apost, SFPC.TBDOCUMENTOSFPC DOC, SFPC.TBSITUACAODOCUMENTO SIT WHERE apost.cdocpcsequ = DOC.cdocpcsequ AND DOC.cdocpcsequ = ".$codCont;
            $sql .= " AND DOC.CFASEDSEQU = SIT.CFASEDSEQU ";
            $sql .= " AND DOC.CSITDCSEQU = SIT.CSITDCSEQU ";
            //var_dump($sql);die;
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        public function ValidarSeNumeroMedicaoExiste($dadosMedicao){
            $contrato = $dadosMedicao['cdocpcsequ'];
            $numeroMedicao = $dadosMedicao['amedconume'];

            $sql = "SELECT EXISTS (SELECT MEDI.AMEDCONUME as num_medicao ";
            $sql .= "FROM SFPC.TBMEDICAOCONTRATO MEDI ";
            $sql .= "WHERE MEDI.CDOCPCSEQU = " . $contrato . " and MEDI.AMEDCONUME = " . $numeroMedicao . ")::int";
            $resultado = executarSQL($this->conexaoDb, $sql);
            $resultado->fetchInto($retorno, DB_FETCHMODE_ROW);
                      
            if (!empty($retorno)) {
                return $retorno;
            }
            return false;
        }

        public function getValorGlobalContrato($codCont){
            $sql = "SELECT CASE  WHEN CONT.VCTRPCGLAA IS NULL THEN vctrpcvlor  ";
            $sql .= " WHEN CONT.VCTRPCGLAA IS NOT NULL THEN CONT.VCTRPCGLAA ";
            $sql .= " END as valor_global ";
            $sql .= "FROM SFPC.TBCONTRATOSFPC CONT ";
            $sql .= "WHERE CONT.CDOCPCSEQU = " .$codCont;

            $resultado = executarSQL($this->conexaoDb, $sql);
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);

            if(!empty($retorno->valor_global)){
                return number_format((floatval($retorno->valor_global)),4,',','.');
            }else{
                return number_format((floatval('0.0000')),4,',','.');
            }
        }

        public function getDatasAditivosContrato($codCont){

            $sql = "SELECT DOC.CFASEDSEQU as fase, to_char((daditiinex + aaditiapea), 'DD/MM/YYYY') as data_fim, ADIT.CDOCPCSEQU, ADIT.CDOCPCSEQ1, ECTRPCNUMF, ADIT.XADITIJUST, ADIT.AADITINUAD, SIT.ESITDCDESC,ADIT.DADITIINVG, ADIT.DADITIFIVG, ADIT.DADITIINEX, ADIT.DADITIFIEX, ADIT.VADITIVALR, ADIT.AADITIAPEA, ADIT.vaditireqc, ADIT.vaditivtad, CONT.cctrpcopex ";
            $sql .= "FROM SFPC.TBCONTRATOSFPC CONT, SFPC.TBADITIVO ADIT, SFPC.TBDOCUMENTOSFPC DOC, SFPC.TBSITUACAODOCUMENTO SIT ";
            $sql .= "WHERE CONT.CDOCPCSEQU = ADIT.CDOCPCSEQ1 ";
            $sql .= "AND ADIT.CDOCPCSEQU = DOC.CDOCPCSEQU ";
            $sql .= "AND DOC.CFASEDSEQU = SIT.CFASEDSEQU ";
            $sql .= "AND DOC.CSITDCSEQU = SIT.CSITDCSEQU ";
            $sql .= "AND ADIT.FADITIALPZ = 'SIM' AND CONT.CDOCPCSEQU = " .$codCont . " ORDER BY AADITINUAD ASC";
            
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        public function getAditivosContrato($codCont){

            $sql = "SELECT DOC.CFASEDSEQU as fase, CASE WHEN cctrpcopex = 'M' AND FADITIALPZ = 'SIM' AND DADITIFIEX IS NULL THEN to_char(cast(daditiinex AS DATE) + ((aaditiapea)::text || ' ' || 'MONTHS')::interval, 'DD/MM/YYYY') WHEN cctrpcopex = 'M' AND FADITIALPZ = 'SIM' AND DADITIFIEX IS NOT NULL THEN to_char(DADITIFIEX, 'DD/MM/YYYY') WHEN cctrpcopex = 'D' AND FADITIALPZ = 'SIM' AND DADITIFIEX IS NULL THEN to_char(daditiinex + ((aaditiapea)::text || ' ' || 'DAYS')::interval, 'DD/MM/YYYY') WHEN cctrpcopex = 'D' AND FADITIALPZ = 'SIM' THEN to_char(DADITIFIEX, 'DD/MM/YYYY') END AS data_fim, ADIT.CDOCPCSEQU, ADIT.CDOCPCSEQ1, ECTRPCNUMF, ADIT.XADITIJUST, ADIT.AADITINUAD, SIT.ESITDCDESC,ADIT.DADITIINVG, ADIT.DADITIFIVG, ADIT.DADITIINEX, ADIT.VADITIVALR, ADIT.AADITIAPEA, ADIT.vaditireqc, ADIT.vaditivtad ";
            $sql .= "FROM SFPC.TBCONTRATOSFPC CONT, SFPC.TBADITIVO ADIT, SFPC.TBDOCUMENTOSFPC DOC, SFPC.TBSITUACAODOCUMENTO SIT ";
            $sql .= "WHERE CONT.CDOCPCSEQU = ADIT.CDOCPCSEQ1 ";
            $sql .= "AND ADIT.CDOCPCSEQU = DOC.CDOCPCSEQU ";
            $sql .= "AND DOC.CFASEDSEQU = SIT.CFASEDSEQU ";
            $sql .= "AND DOC.CSITDCSEQU = SIT.CSITDCSEQU ";
            $sql .= "AND CONT.CDOCPCSEQU = " .$codCont . " ORDER BY AADITINUAD ASC";
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        public function AlterarAditivoContrato($dadosSalvar){

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

        public function GetValorTotalApostilamento($codDoc){
            $sql = "select sum(vapostvtap) as vtapost from sfpc.tbapostilamento apost left join sfpc.tbdocumentosfpc doc "; 
            $sql .=" on apost.cdocpcsequ = doc.cdocpcsequ where apost.CDOCPCSEQ2 =".$codDoc . " and cfasedsequ = 6 and doc.csitdcsequ = 1";
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        public function codigoTipoApostilamento($idtipoapostilamento){
            $sql = "select ctpaposequ from sfpc.tbtipoapostilamento where etpapodesc like '".$idtipoapostilamento."'";
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        public function VerificaSeExisteDocumentoFiscal($cdocpcsequ, $cfiscdcpff){
            $sql = " SELECT * FROM sfpc.tbdocumentofiscalsfpc WHERE cdocpcsequ =".$cdocpcsequ." AND cfiscdcpff='".$cfiscdcpff."'";
            //var_dump($sql);
            $resultado = executarSQL($this->conexaoDb, $sql);
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            if(!empty($retorno)){
                return $retorno;
            }else{
                return false;
            }
        }

        public function DeletaDocumentoAnexo($cdocpcsequ, $cdcanxsequ){
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

        public function VerificaSeJaExisteDocumentoAnexo($codDoc, $sequdocanexo, $edcanxnome){
            $sql  = " SELECT cdocpcsequ AS sequdocumento ";
            $sql .= " FROM sfpc.tbdocumentoanexo WHERE cdocpcsequ=".$codDoc;
            $sql .=" AND cdcanxsequ =".$sequdocanexo;
            $sql .=" AND edcanxnome ='".$edcanxnome."'";
            
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            return $retorno;
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

        public function InsertDocumentoFiscal($dadosDocFiscal){
            $sql = " INSERT INTO sfpc.tbdocumentofiscalsfpc (cfiscdcpff,cdocpcsequ,cusupocodi,tdocfiulat) ";
            $sql .= " VALUES ('".$dadosDocFiscal['cfiscdcpff']."','".$dadosDocFiscal['cdocpcsequ']."','".$dadosDocFiscal['cusupocodi']."','".$dadosDocFiscal['tdocfiulat']."')";
            //var_dump($sql);die;
            $resultado = executarSQL($this->conexaoDb, $sql);
            if(!empty($resultado)){
                return $resultado;
            }else{
                return false;
            }
        }

        public function RemoveFiscaldoContrato($cfiscdcpff, $idRegistro){
            $sql = "DELETE FROM sfpc.tbdocumentofiscalsfpc  WHERE cdocpcsequ=".$idRegistro." AND  cfiscdcpff='".$cfiscdcpff."'";
            //var_dump($sql);die;
            $resultado = executarSQL($this->conexaoDb, $sql);
            if(!empty($resultado)){
                return $resultado;
            }else{
                return false;
            }
        }

        public function GetSequencialDocAnexo($codContrato){
            
            $sqlMax = "SELECT max(cdcanxsequ) FROM sfpc.tbdocumentoanexo WHERE cdocpcsequ = " .$codContrato;
             
            $resultadoMax = executarSQL($this->conexaoDb, $sqlMax);
            $resultadoMax->fetchInto($resultadoMax, DB_FETCHMODE_OBJECT);
            
            if (!empty($resultadoMax)) {
                return $numMaxApostilamento = intval($resultadoMax->max) + 1;
            }else{
                return $numMaxApostilamento = 1;
            }
        }

        public function InsereDocumentosAnexos($dadosDocumentosAnexos){

            $num_max_documento = $this->GetSequencialDocAnexo($dadosDocumentosAnexos['cdocpcsequ']);
            
            $sql  = " INSERT INTO sfpc.tbdocumentoanexo (cdocpcsequ,edcanxnome,idcanxarqu,tdcanxcada,cusupocodi,tdcanxulat,cdcanxsequ) ";
            $sql .=" VALUES('".$dadosDocumentosAnexos['cdocpcsequ']."','".$dadosDocumentosAnexos['edcanxnome']."', decode('".$dadosDocumentosAnexos['idcanxarqu']."','hex'),'".DATE('Y-m-d H:i:s.u')."',";
            $sql .= " '".$dadosDocumentosAnexos['cusupocodi']."','".DATE('Y-m-d H:i:s.u')."','".$num_max_documento."')";
            
            $resultado = executarSQL($this->conexaoDb, $sql);

            //$resultado = $this->conexaoDb->query($sql);
            if(!empty($resultado)){
                return $resultado;
            }else{
                return false;
            }
        }

        // Função para incluir o número máximo do documento para um apostilamento
        public function insereDocumentoApostilamento($numMaxDoc){
            $cusupocodi = $_SESSION['_cusupocodi_'];
            $db = $this->conexaoDb;
            //Insere Documento
            $sqlD = "insert into sfpc.tbdocumentosfpc (cdocpcsequ, ctidocsequ, ctipgasequ, cfluxosequ, csitdcsequ, cfasedsequ, cmodocsequ, cusupocodi, tdocpculat, ctidocseq1, cfuchcsequ, cchelisequ)";
            $sqlD .= "values (".$numMaxDoc.", 3, null, null, 1, 5, null, ".$cusupocodi.", now(), null, null, null)";
            $resD = $db->query($sqlD);
            if(empty($resD)){
                return 1;
            }

            return true;
        }

        // Função para incluir o número máximo do documento para um apostilamento
        public function AtualizaDocumentoApostilamento($numMaxDoc, $situacao){
            $cusupocodi = $_SESSION['_cusupocodi_'];
            $db = $this->conexaoDb;
            //Insere Documento
            $sqlD = "update sfpc.tbdocumentosfpc set cfasedsequ = ". $situacao. " where cdocpcsequ = ". $numMaxDoc;
            
            $resD = $db->query($sqlD);
            if(empty($resD)){
                return 1;
            }

            return true;
        }

        // função para pegar o número máximo do documento
        function maxCodDocumento($condicao = null, $valor = null){
            $sql =  "select max(cdocpcsequ) from sfpc.tbdocumentosfpc";
            if(!empty($condicao) && !empty($valor)){
                $sql .= " where '".$condicao . "' = ". $valor; // Considerando que o $valor seja um inteiro
            }

            $resultado = executarSQL($this->conexaoDb, $sql);
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            if(!empty($retorno)){
                $cdocpcsequNovo = intval($retorno->max) + 1;
                return $cdocpcsequNovo;
            }
        }

        public function alteraDadosApostilamento($dadosSalvar)
        {
            
            $cusupocodi = $_SESSION['_cusupocodi_'];

            if(!empty($dadosSalvar['EAPOSTNAGT'])){
                $nome_arquivo_gestor = $dadosSalvar['EAPOSTNAGT'];
            }else{
                $nome_arquivo_gestor = null;
            }

            if(!empty($dadosSalvar['IAPOSTAQGT'])){
                $arquivo_gestor = $dadosSalvar['IAPOSTAQGT'];
            }else{
                $arquivo_gestor = null;
            }

            if(!empty($dadosSalvar['EAPOSTMEMC'])){
                $nome_arquivo = $dadosSalvar['EAPOSTMEMC'];
            }else{
                $nome_arquivo = null;
            }

            if(!empty($dadosSalvar['IAPOSTMEMC'])){
                $arquivo = $dadosSalvar['IAPOSTMEMC'];
            }else{
                $arquivo = null;
            }
            
            if(!empty($dadosSalvar['VAPOSTVTAP'])){
                $valorApostilamento = $this->floatvalue($dadosSalvar['VAPOSTVTAP']);
            }else{
                $valorApostilamento = "0.00";
            }

            if(!empty($dadosSalvar['VAPOSTRETR'])){
                $valorRetroativo = $this->floatvalue($dadosSalvar['VAPOSTRETR']);
            }else{
                $valorRetroativo = "0.00";
            }
// var_dump($dadosSalvar['situacao_apost']);die;
            $atualiza_apostilamento_doc = $this->AtualizaDocumentoApostilamento($dadosSalvar['CDOCPCSEQU'], $dadosSalvar['situacao_apost']);

            $sql = "UPDATE SFPC.TBAPOSTILAMENTO ";
            $sql .= " SET CTPAPOSEQU = ".$dadosSalvar['CTPAPOSEQU'].", AAPOSTNUAP= ". $dadosSalvar['AAPOSTNUAP'].
                        ", DAPOSTCADA= '".$dadosSalvar['DAPOSTCADA']."', EAPOSTMEMC= '". $nome_arquivo. "', IAPOSTMEMC = '".$arquivo."', EAPOSTNAGT= '". $nome_arquivo_gestor. "', IAPOSTAQGT = '".$arquivo_gestor.
                        "', NAPOSTNMGT = '". $dadosSalvar['NAPOSTNMGT'] ."', NAPOSTCPFG = '".$dadosSalvar['NAPOSTCPFG'].
                        "', NAPOSTMTGT = '".$dadosSalvar['NAPOSTMTGT']."', CUSUPOCODI= '".$cusupocodi."', NAPOSTMLGT = '".$dadosSalvar['NAPOSTMLGT'].
                        "', EAPOSTTLGT = '".$dadosSalvar['EAPOSTTLGT']."',  VAPOSTVTAP = '".$valorApostilamento.
                        "', TAPOSTULAT = now(), VAPOSTRETR = '".$valorRetroativo;
         
            $sql .= "' WHERE CDOCPCSEQU = ".$dadosSalvar['CDOCPCSEQU']." AND CDOCPCSEQ2 = ".$dadosSalvar['CDOCPCSEQ2'];
            
            $resultado = executarSQL($this->conexaoDb, $sql);
            
            if (!empty($resultado)) {
                return $resultado;
            }
            return false;
        }

        public function insereDadosApostilamento($dadosSalvar)
        {
            //var_dump($dadosSalvar);die;
            $cusupocodi = $_SESSION['_cusupocodi_'];

            $num_max_documento = $this->maxCodDocumento();

            //PEGANDO O VALOR MÁXIMO DO AAPOSTNUAP
            $sqlMax = "SELECT MAX(AAPOSTNUAP) from SFPC.TBAPOSTILAMENTO WHERE CDOCPCSEQ2 = " .$dadosSalvar['CDOCPCSEQ2'];
           
            $resultadoMax = executarSQL($this->conexaoDb, $sqlMax);
            $resultadoMax->fetchInto($resultadoMax, DB_FETCHMODE_OBJECT);
            
            if (!empty($resultadoMax)) {
                $numMaxApostilamento = intval($resultadoMax->max) + 1;
            }

            $docApostilamento = $this->insereDocumentoApostilamento($num_max_documento);
            
           if(!empty($dadosSalvar['EAPOSTNAGT'])){
                $nome_arquivo_gestor = $dadosSalvar['EAPOSTNAGT'];
            }else{
                $nome_arquivo_gestor = null;
            }

            if(!empty($dadosSalvar['IAPOSTAQGT'])){
                $arquivo_gestor = $dadosSalvar['IAPOSTAQGT'];
            }else{
                $arquivo_gestor = null;
            }

            if(!empty($dadosSalvar['EAPOSTMEMC'])){
                $nome_arquivo = $dadosSalvar['EAPOSTMEMC'];
            }else{
                $nome_arquivo = null;
            }

            if(!empty($dadosSalvar['IAPOSTMEMC'])){
                $arquivo = $dadosSalvar['IAPOSTMEMC'];
            }else{
                $arquivo = null;
            }
            
            if(!empty($dadosSalvar['VAPOSTVTAP'])){
                $valorApostilamento = $dadosSalvar['VAPOSTVTAP'];
            }else{
                $valorApostilamento = "0.00";
            }

            if(!empty($dadosSalvar['VAPOSTRETR'])){
                $valorRetroativo = $dadosSalvar['VAPOSTRETR'];
            }else{
                $valorRetroativo = "0.00";
            }

            if(!empty($dadosSalvar['AFORCRSEQU'])){
                $cod_fornecedor = $dadosSalvar['AFORCRSEQU'];
            }else{
                $cod_fornecedor = 'null';
            }

            //ADD INSERT OU UPDATE?

            /*
            $sql = "INSERT INTO SFPC.TBAPOSTILAMENTO(
                    CDOCPCSEQU, CDOCPCSEQ2, CTPAPOSEQU, AAPOSTNUAP, DAPOSTCADA, EAPOSTMEMC, IAPOSTMEMC, NAPOSTNMGT, NAPOSTCPFG, 
                    NAPOSTMTGT, NAPOSTMLGT, EAPOSTTLGT, VAPOSTVTAP, CUSUPOCODI, TAPOSTULAT, VAPOSTRETR, AFORCRSEQU) ";
            $sql .= "VALUES ("; 
            $sql .= $num_max_documento.", ".$dadosSalvar['CDOCPCSEQ2'].", ".$dadosSalvar['CTPAPOSEQU'].", ".$numMaxApostilamento.", '".$dadosSalvar['DAPOSTCADA']."', '".$nome_arquivo."', '".$arquivo."', '";
            $sql .= $dadosSalvar['NAPOSTNMGT']."', '".$dadosSalvar['NAPOSTCPFG']."', '".$dadosSalvar['NAPOSTMTGT']."', '".$dadosSalvar['NAPOSTMLGT']."', '".$dadosSalvar['EAPOSTTLGT']."', '".$valorApostilamento."', ".$cusupocodi .", now(), '";
            $sql .= $valorRetroativo."', $cod_fornecedor )";
            */
            
            $sql = "INSERT INTO SFPC.TBAPOSTILAMENTO(
                    CDOCPCSEQU, CDOCPCSEQ2, CTPAPOSEQU, AAPOSTNUAP, DAPOSTCADA, EAPOSTMEMC, IAPOSTMEMC, NAPOSTNMGT, NAPOSTCPFG, 
                    NAPOSTMTGT, NAPOSTMLGT, EAPOSTTLGT, VAPOSTVTAP, CUSUPOCODI, TAPOSTULAT, VAPOSTRETR, AFORCRSEQU) ";
            $sql .= "VALUES ("; 
            $sql .= $num_max_documento.", ".$dadosSalvar['CDOCPCSEQ2'].", ".$dadosSalvar['CTPAPOSEQU'].", ".$numMaxApostilamento.", '".$dadosSalvar['DAPOSTCADA']."', '".$nome_arquivo."', '".$arquivo."', '";
            $sql .= $dadosSalvar['NAPOSTNMGT']."', '".$dadosSalvar['NAPOSTCPFG']."', '".$dadosSalvar['NAPOSTMTGT']."', '".$dadosSalvar['NAPOSTMLGT']."', '".$dadosSalvar['EAPOSTTLGT']."', '".$valorApostilamento."', ".$cusupocodi .", now(), '";
            $sql .= $valorRetroativo."', ".$cod_fornecedor." )";
            //var_dump($sql);die.
            $resultado = executarSQL($this->conexaoDb, $sql);
            //var_dump($resultado);die;
            if (!empty($resultado)) {
                return $resultado;
            }
            return false;
        }

        public function verificarSeExisteNumeroMedicaoContrato($contrato, $num_medicao){
            $sql = "SELECT CDOCPCSEQU, amedconume from SFPC.TBMEDICAOCONTRATO WHERE CDOCPCSEQU = " . $contrato . " and cmedcosequ = " . $num_medicao;
            //var_dump($sql);die;
            $resultado = executarSQL($this->conexaoDb, $sql);
            
            $dadosRetorno = array();
            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
                $dadosRetorno[] = $retorno;
            }
            
            return $dadosRetorno;
        }

        public function verificarSeExisteNumeroApostilamentoContrato($contrato, $num_apostilamento){
            $sql = "SELECT CDOCPCSEQU, AAPOSTNUAP from SFPC.TBAPOSTILAMENTO WHERE CDOCPCSEQ2 = " . $contrato . " and AAPOSTNUAP = " . $num_apostilamento;
            $resultado = executarSQL($this->conexaoDb, $sql);
            
            $dadosRetorno = array();
            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
                $dadosRetorno[] = $retorno;
            }
            
            return $dadosRetorno;
        }

        //Essa função foi criada para verificar se o fornecedor adicionado no apostilamento ja existe no contrato
        public function verificarSeExisteFornecedorNoContrato($contrato){
            $sql = "SELECT AFORCRSEQ1 from SFPC.TBCONTRATOSFPC WHERE cdocpcsequ = " . $contrato ;
            $resultado = executarSQL($this->conexaoDb, $sql);
            
            $dadosRetorno = array();
            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
                $dadosRetorno[] = $retorno;
            }
            
            return $dadosRetorno;
        }

        //essa função é para  atualizar o contrato o campo do fornecedor 
        public function UpdateContrato($dadosContratos){
            
            
            $aforcrseq1=!empty($dadosContratos['aforcrseq1'])?" aforcrseq1='".$dadosContratos['aforcrseq1']."', ":" "; // campo de fornecedor original
            
            $sql  =" UPDATE sfpc.tbcontratosfpc SET ".$aforcrseq1."'  where cdocpcsequ = ".$dadosContratos['cdocpcsequ'];
            $resultado = executarSQL($this->conexaoDb, $sql);
            if(!empty($resultado)){
                return $resultado;
            }else{
                return false;
            }
        }

        public function verificarSeExisteNumeroAditivoContrato($contrato, $num_aditivo){
            $sql = "SELECT cdocpcsequ, aaditinuad from SFPC.TBADITIVO WHERE CDOCPCSEQ1 = " . $contrato . " and AADITINUAD = " . $num_aditivo;
            $resultado = executarSQL($this->conexaoDb, $sql);
            
            $dadosRetorno = array();
            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
                $dadosRetorno[] = $retorno;
            }
            
            return $dadosRetorno;
        }

        // Pega o código do último aditivo geral e adiciona 1
        public function getUltimoCodAditivoGeral($codigoContrato){
            $sqlMax = "SELECT MAX(aaditinuad) from SFPC.TBADITIVO WHERE CDOCPCSEQ1 = " . $codigoContrato;
            $resultado = executarSQL($this->conexaoDb, $sqlMax);
            
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            if(!empty($retorno)){
                $cdocpcsequNovo = intval($retorno->max) + 1;
                return $cdocpcsequNovo;
            }
        }

        // Pega o código da última Medição geral e adiciona 1
        public function getUltimoCodMedicaoGeral($codigoContrato){
            
            $sqlMax = "SELECT MAX(amedconume) from SFPC.TBMEDICAOCONTRATO WHERE CDOCPCSEQU = " . $codigoContrato;
            $resultado = executarSQL($this->conexaoDb, $sqlMax);
            
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            if(!empty($retorno)){
                $cdocpcsequNovo = intval($retorno->max) + 1;
                return $cdocpcsequNovo;
            }
        }

        // Pega o código do último apostilamento geral e adiciona 1
        public function getUltimoCodApostilamentoGeral($codigoContrato){
            
            $sqlMax = "SELECT MAX(aapostnuap) from SFPC.TBAPOSTILAMENTO WHERE CDOCPCSEQ2 = " . $codigoContrato;
            $resultado = executarSQL($this->conexaoDb, $sqlMax);
            
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            if(!empty($retorno)){
                $cdocpcsequNovo = intval($retorno->max) + 1;
                return $cdocpcsequNovo;
            }
        }

        // Pega o código do último apostilamento inserido para o contrato
        public function getUltimoAditivoCod($codigoContrato){
            $sqlMax = "SELECT MAX(cdocpcsequ) from SFPC.TBADITIVO WHERE CDOCPCSEQ1 = " . $codigoContrato;
            $resultado = executarSQL($this->conexaoDb, $sqlMax);
            
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            if(!empty($retorno)){
                $cdocpcsequNovo = intval($retorno->max);
                return $cdocpcsequNovo;
            }
        }

        // Pega o código do último apostilamento inserido para o contrato
        public function getUltimoApostilamentoCod($codigoContrato){
            $sqlMax = "SELECT MAX(cdocpcsequ) from SFPC.TBAPOSTILAMENTO WHERE CDOCPCSEQ2 = " . $codigoContrato;
            $resultado = executarSQL($this->conexaoDb, $sqlMax);
            
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            if(!empty($retorno)){
                $cdocpcsequNovo = intval($retorno->max);
                return $cdocpcsequNovo;
            }
        }

        public function getApostilamentoNome($idApostilamento){
            $sql = "SELECT etpapodesc FROM sfpc.tbtipoapostilamento WHERE ctpaposequ = " . $idApostilamento;
            $resultado = executarSQL($this->conexaoDb, $sql);
            
            $dadosRetorno = array();
            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        

        public function ExisteDocumentoAditivo($dadosArquivo){

            $sql = "SELECT edcanxnome FROM sfpc.tbdocumentoanexo WHERE cdocpcsequ = ".$dadosArquivo['cdocpcsequ'] . " AND edcanxnome = '". $dadosArquivo['edcanxnome']."'";
            
            $resultado = executarSQL($this->conexaoDb, $sql);

            $dadosRetorno = array();
            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
                $dadosRetorno[] = $retorno;
            }

            return $dadosRetorno;
        }

        public function getApostilamentosContrato($codCont)
        {
            $sql = "SELECT apost.cdocpcsequ, ctpaposequ, aapostnuap, dapostcada, vapostvtap, SIT.esitdcdesc as situacao ";
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

        public function getMedicoesContrato($codCont)
        {
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

        public function getMedicaoContrato($codCont, $codRegistro)
        {
            $sql = "SELECT MEDI.CDOCPCSEQU, CONT.ECTRPCNUMF, MEDI.AMEDCONUME, MEDI.CMEDCOSEQU, MEDI.DMEDCOINIC, MEDI.DMEDCOFINL, MEDI.CMEDCONANE, encode(MEDI.imedcoanex,'base64') as arquivo, MEDI.VMEDCOVALM, MEDI.EMEDCOOBSE ";
            $sql .= "FROM SFPC.TBCONTRATOSFPC CONT ";
            $sql .= "INNER JOIN SFPC.TBMEDICAOCONTRATO MEDI ";
            $sql .= "ON CONT.CDOCPCSEQU = MEDI.CDOCPCSEQU WHERE CONT.CDOCPCSEQU = " .$codCont. " AND MEDI.CMEDCOSEQU = " .$codRegistro;

            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        public function insertsMedicaoIncluir($dadosSalvar)
        {
            //var_dump($dadosSalvar);die;
            $cusupocodi = $_SESSION['_cusupocodi_'];

            //PEGANDO O VALOR MÁXIMO DO CMEDCOSEQU
            $sqlMax = "SELECT MAX(CMEDCOSEQU) from SFPC.TBMEDICAOCONTRATO WHERE CDOCPCSEQU = " .$dadosSalvar['cdocpcsequ'];
          
            $resultadoMax = executarSQL($this->conexaoDb, $sqlMax);
            $resultadoMax->fetchInto($retornoMax, DB_FETCHMODE_OBJECT);

            if (!empty($retornoMax)) {
                $cmedcosequMax = intval($retornoMax->max) + 1;
            }

            if(!empty($dadosSalvar['cmedconane'])){
                $cmedconane = $dadosSalvar['cmedconane'];
            }else{
                $cmedconane = null;
            }

            if(!empty($dadosSalvar['imedcoanex'])){
                $imedcoanex = $dadosSalvar['imedcoanex'];
            }else{
                $imedcoanex = null;
            }

            $sql = "INSERT INTO SFPC.TBMEDICAOCONTRATO (CMEDCOSEQU, CDOCPCSEQU, CFISCDCPFF, DMEDCOINIC,
                                                        DMEDCOFINL, CMEDCONANE, IMEDCOANEX, EMEDCOOBSE,
                                                        DMEDCOAPRT, EMEDCOTECO, AMEDCONUME, CUSUPOCODI,
                                                        TMEDCOULAT,  CUSUPOCOD1, CDOCPCSEQ1, VMEDCOVALM) ";
            $sql .= "VALUES ("; 
            $sql .= $cmedcosequMax.", ".$dadosSalvar['cdocpcsequ'].", null, '". $dadosSalvar['dmedcoinic'] ."', '".$dadosSalvar['dmedcofinl']."', '".$dadosSalvar['cmedconane']."', decode('".$dadosSalvar['imedcoanex']."','hex'), '";
            $sql .= $dadosSalvar['emedcoobse']."', '".DATE('Y-m-d')."', null, ".$cmedcosequMax.", ".$cusupocodi .", '";
            $sql .= DATE('Y-m-d H:i:s.u')."', null, null, '".$this->floatvalue($dadosSalvar['vmedcovalm']);
            $sql .= "' ) "; 
            // var_dump($sql);die;
            $resultado = executarSQL($this->conexaoDb, $sql);
            unset($_SESSION['documento_medicao']);
            if (!empty($resultado)) {
                return $resultado;
            }
            return false;
        }

        public function AlterarMedicao($dadosSalvar){
            //var_dump($dadosSalvar);die;
         
            $cusupocodi = $_SESSION['_cusupocodi_'];
            
            $sql = "UPDATE SFPC.TBMEDICAOCONTRATO ";
            $sql .= " SET DMEDCOINIC = '".$dadosSalvar['dmedcoinic']."', DMEDCOFINL= '". $dadosSalvar['dmedcofinl'].
                        "', CMEDCONANE = '".$dadosSalvar['cmedconane']."', IMEDCOANEX = decode('".$dadosSalvar['imedcoanex']."','hex')".
                        ", AMEDCONUME = '". $dadosSalvar['amedconume'] ."', EMEDCOOBSE = '".$dadosSalvar['emedcoobse'].
                        "', DMEDCOAPRT = '".DATE('Y-m-d')."', CUSUPOCODI= ".$cusupocodi.", TMEDCOULAT = '".DATE('Y-m-d H:i:s.u').
                        "', CDOCPCSEQ1 = '".$dadosSalvar['cdocpcsequ'].
                        "', VMEDCOVALM = '".$this->floatvalue($dadosSalvar['vmedcovalm']);
            
            $sql .= "' WHERE CDOCPCSEQU = ".$dadosSalvar['cdocpcsequ']." AND CMEDCOSEQU = ".$dadosSalvar['cmedcosequ'];
         
            $resultado = executarSQL($this->conexaoDb, $sql);
            unset($_SESSION['documento_medicao']);
            if (!empty($resultado)) {
                return $resultado;
            }
            return false;
        }

        public function GetDocumentoMedicao($codCont, $codRegistro){
            $sql = "SELECT MEDI.CDOCPCSEQU, CONT.ECTRPCNUMF, MEDI.AMEDCONUME, MEDI.DMEDCOINIC, MEDI.DMEDCOFINL, MEDI.CMEDCONANE, encode(MEDI.imedcoanex,'base64') as imedcoanex , MEDI.VMEDCOVALM, MEDI.EMEDCOOBSE, MEDI.TMEDCOULAT ";
            $sql .= "FROM SFPC.TBCONTRATOSFPC CONT ";
            $sql .= "INNER JOIN SFPC.TBMEDICAOCONTRATO MEDI ";
            $sql .= "ON CONT.CDOCPCSEQU = MEDI.CDOCPCSEQU WHERE CONT.CDOCPCSEQU = " .$codCont. " AND MEDI.CMEDCOSEQU = " .$codRegistro;
         //   var_dump($sql);
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        public function GetDocumentosAnexos($codDoc, $sequdocanexo=null, $edcanxnome=null){
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

        public function SaldoAExecutarSemMascara($codDoc,$totalcontrato){
            // $sql = "SELECT sum(vmedcovalm) AS totalMedicao FROM sfpc.tbmedicaocontrato WHERE cdocpcsequ =".$codDoc." AND dmedcoaprt IS NOT NULL";
        //     $sql = "SELECT ";
        //     $sql .= " CASE ";
        //     $sql .= "  WHEN sum(vmedcovalm) IS NULL THEN sum(vimedcvalr) ";
        //     $sql .=  "  WHEN sum(vmedcovalm) IS NOT NULL THEN sum(vmedcovalm) ";
        //    $sql .= "  END ";
        //     $sql .= " AS totalmedicao ";
        //     $sql .= "FROM sfpc.tbitemmedicaocontrato as it right join sfpc.tbmedicaocontrato as m on (m.cmedcosequ = it.cmedcosequ and m.cdocpcsequ = it.cdocpcsequ ) ";
        //     $sql .= "where m.cdocpcsequ =".$codDoc;
        $sql = "select COALESCE(sum(vmedcovalm),0.000) AS totalmedicao from sfpc.tbmedicaocontrato where cdocpcsequ = $codDoc";
            $resultado = executarSQL($this->conexaoDb,$sql);
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            if(!empty($retorno->totalmedicao)){
                $valor = floatval($totalcontrato) - floatval($retorno->totalmedicao);
               
                return $valor;
            }else{
                return floatval($totalcontrato) - floatval('0');
            }
        }

        public function SaldoAExecutar($codDoc,$valorGlobal){
            // $sql = "SELECT sum(vmedcovalm) AS totalMedicao FROM sfpc.tbmedicaocontrato WHERE cdocpcsequ =".$codDoc." AND dmedcoaprt IS NOT NULL";
        //     $sql = "SELECT ";
        //     $sql .= " CASE ";
        //     $sql .= "  WHEN sum(vmedcovalm) IS NULL THEN sum(vimedcvalr) ";
        //     $sql .=  "  WHEN sum(vmedcovalm) IS NOT NULL THEN sum(vmedcovalm) ";
        //    $sql .= "  END ";
        //     $sql .= " AS totalmedicao ";
        //     $sql .= "FROM sfpc.tbitemmedicaocontrato as it right join sfpc.tbmedicaocontrato as m on (m.cmedcosequ = it.cmedcosequ and m.cdocpcsequ = it.cdocpcsequ ) ";
        //     $sql .= "where m.cdocpcsequ =".$codDoc;
            $sql = "select COALESCE(sum(vmedcovalm),0.000) AS totalmedicao from sfpc.tbmedicaocontrato where cdocpcsequ = $codDoc";
            $resultado = executarSQL($this->conexaoDb,$sql);
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            
            if(!empty($retorno->totalmedicao)){
                if(is_float($valorGlobal)){
                    $valor = number_format(( $valorGlobal - floatval($retorno->totalmedicao)),4,',','.');
                }else{
                    $valor = number_format(( floatval($valorGlobal) - floatval($retorno->totalmedicao)),4,',','.');
                }
                return $valor;
            }else{
                return number_format((floatval($valorGlobal) - floatval('0.0000')),4,',','.');
            }
        }

        public function getValorTotalMedicao($codDoc){
        //     $sql = "SELECT ";
        //     $sql .= " CASE ";
        //     $sql .= "  WHEN sum(vmedcovalm) IS NULL THEN sum(vimedcvalr) ";
        //     $sql .=  "  WHEN sum(vmedcovalm) IS NOT NULL THEN sum(vmedcovalm) ";
        //    $sql .= "  END ";
        //     $sql .= " AS totalmedicao ";
        //     $sql .= "FROM sfpc.tbitemmedicaocontrato as it right join sfpc.tbmedicaocontrato as m on (m.cmedcosequ = it.cmedcosequ and m.cdocpcsequ = it.cdocpcsequ ) ";
        //     $sql .= "where m.cdocpcsequ =".$codDoc;
            $sql = "select COALESCE(sum(vmedcovalm),0.000) AS totalmedicao from sfpc.tbmedicaocontrato where cdocpcsequ = $codDoc";
            $resultado = executarSQL($this->conexaoDb,$sql);
            
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            if(!empty($retorno->totalmedicao)){
                return number_format((floatval($retorno->totalmedicao)),4,',','.');
            }else{
                return number_format((floatval('0.0000')),4,',','.');
            }
        }

        public function ExcluirApostilamento($registro){
            
            $sql = "DELETE FROM SFPC.TBAPOSTILAMENTO WHERE CDOCPCSEQU = ".$registro;
            
            $resultado = executarSQL($this->conexaoDb, $sql);
            
            if(!empty($resultado)){
                return $resultado;
            }else{
                return false;
            }
        }
        
        public function ContarItemMedicao($contrato, $registro){
            $sql_item = "SELECT COUNT(*) FROM SFPC.TBITEMMEDICAOCONTRATO WHERE  CDOCPCSEQU = ".$contrato." AND CMEDCOSEQU = ".$registro;
            
            $resultadoItem = executarSQL($this->conexaoDb, $sql_item);
            $resultadoItem->fetchInto($retornoItem, DB_FETCHMODE_OBJECT);
            
            if(!empty($retornoItem)){
                return $retornoItem;
            }
        }

        public function ExcluirItemMedicao($contrato, $registro){

            $sql_item = "DELETE FROM SFPC.TBITEMMEDICAOCONTRATO WHERE  CDOCPCSEQU = ".$contrato." AND CMEDCOSEQU = ".$registro;

            $resultado = executarSQL($this->conexaoDb, $sql_item);
            if(!empty($resultado)){
                return $resultado;
            }else{
                return false;
            }
        }

        public function ExcluirMedicao($contrato, $registro){
            
            $sql = "DELETE FROM SFPC.TBMEDICAOCONTRATO WHERE  CDOCPCSEQU =".$contrato." AND CMEDCOSEQU = ".$registro;
            //var_dump($sql);die;
            $resultado = executarSQL($this->conexaoDb, $sql);
            
            if(!empty($resultado)){
                return $resultado;
            }else{
                return false;
            }
        }

        public function ExcluirAditivo($contrato, $registro){

            $sql = "DELETE FROM SFPC.TBADITIVO WHERE  CDOCPCSEQ1 = ".$contrato." AND CDOCPCSEQU = ".$registro;
            
            $resultado = executarSQL($this->conexaoDb, $sql);
            
            if(!empty($resultado)){
                $dadosSalvar['CDOCPCSEQ1'] = $contrato; 
                $corrigeFornContrato = $this->updatefornecedorPorAditivo($dadosSalvar);
                return $resultado;
            }else{
                return false;
            }
        }

    // ===========================================================================================================================================================//
    // A partir daqui so funções para tratamento de dados                                                                                                         //
    //============================================================================================================================================================// 
        public function limpaCPF_CNPJ($valor){
            $valor = trim($valor);
            $valor = str_replace(".", "", $valor);
            $valor = str_replace(",", "", $valor);
            $valor = str_replace("-", "", $valor);
            $valor = str_replace("/", "", $valor);
            return $valor;
        }
        
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
        
        function unserializeForm($str) {
            $returndata = array();
            $strArray = explode("&", $str);
            $i = 0;
            foreach ($strArray as $item) {
                $array = explode("=", $item);
                $returndata[$array[0]] = $array[1];
            }
             return $returndata;
        }

        public function validarDadosAditivo($dados){
            //6409.0033/2019
           
            if(!($dados['AADITINUAD'] == "Em Cadastramento")){
                if(empty($dados['AADITINUAD']) || $dados['AADITINUAD'] == NULL){
                    $resp = array("status" => false,"msm" => "Informe: Número do Aditivo.");
                    print(json_encode($resp));
                    return false;
                }
            }

            if((empty($dados['arquivo']) || $dados['arquivo'] == NULL) && (empty($dados['nome_arquivo']) || $dados['nome_arquivo'] == NULL) ){
                $resp = array("status" => false,"msm" => "Informe: Documento anexo.");
                print(json_encode($resp));
                return false;
            }


            if(empty($dados['CTPADISEQU']) || $dados['CTPADISEQU'] == NULL || $dados['CTPADISEQU'] == "Selecione um tipo de Aditivo"){
                $resp = array("status" => false,"msm" => "Informe: Tipo de Aditivo.");
                print(json_encode($resp));
                return false;
            }

            if(empty($dados['XADITIJUST']) || $dados['XADITIJUST'] == NULL){
                $resp = array("status" => false,"msm" => "Informe: Justificativa do Aditivo.");
                print(json_encode($resp));
                return false;
            }       
           
            if(empty($dados['FADITIALPZ']) || $dados['FADITIALPZ'] == NULL){
                $resp = array("status" => false,"msm" => " Informe: Há Alteração do Prazo.");
                print(json_encode($resp));
                return false;
            }

            if(empty($dados['FADITIALVL']) || $dados['FADITIALVL'] == NULL){
                $resp = array("status" => false,"msm" => "Informe: Há Alteração do Valor.");
                print(json_encode($resp));
                return false;
            }
 
            if($dados['FADITIALPZ'] == "SIM"){

                if(empty($dados['AADITIAPEA']) || $dados['AADITIAPEA'] == NULL){
                    $resp = array("status" => false,"msm" => "Informe: Acréscimo de Prazo de Execução.");
                    print(json_encode($resp));
                    return false;
                }
                
                if(empty($dados['DADITIINVG']) || $dados['DADITIINVG'] == NULL){
                    $resp = array("status" => false,"msm" => "Informe: Data de Início de vigência.");
                    print(json_encode($resp));
                    return false;
                }

                if(empty($dados['DADITIFIVG']) || $dados['DADITIFIVG'] == NULL){
                    $resp = array("status" => false,"msm" => "Informe: Data de Fim de vigência.");
                    print(json_encode($resp));
                    return false;
                }

                if(empty($dados['DADITIINEX']) || $dados['DADITIINEX'] == NULL){
                    $resp = array("status" => false,"msm" => "Informe: Data de Início de Execução.");
                    print(json_encode($resp));
                    return false;
                }

                if(empty($dados['DADITIFIEX']) || $dados['DADITIFIEX'] == NULL){
                    $resp = array("status" => false,"msm" => "Informe: Data de Fim de Execução.");
                    print(json_encode($resp));
                    return false;
                }
                
            }

            if($dados['FADITIALVL'] == "SIM"){
                /*
                if(empty($dados['VADITIREQC']) || $dados['VADITIREQC'] == NULL){
                    $resp = array("status" => false,"msm" => "Informe: Valor Retroativo.");
                    print(json_encode($resp));
                    return false;
                }
                */
                if(empty($dados['VADITIVTAD']) || $dados['VADITIVTAD'] == NULL){
                    $resp = array("status" => false,"msm" => "Informe: Valor Total do Aditivo.");
                    print(json_encode($resp));
                    return false;
                }

                if(empty($dados['CADITITALV']) || $dados['CADITITALV'] == NULL){
                    $resp = array("status" => false,"msm" => "Informe: Tipo de Alteração de Valor.");
                    print(json_encode($resp));
                    return false;
                }
            }

            return true;
        }

        function validarDados($dados){

            //var_dump($dados);die;
            if((empty($dados['arquivo']) || $dados['arquivo'] == NULL) && (empty($dados['nome_arquivo']) || $dados['nome_arquivo'] == NULL)){
            
                $resp = array("status" => false,"msm" => "Informe: Documentos anexos. Devem ser anexados o apostilamento assinado e a cópia do empenho.");
                print(json_encode($resp));
                return false;
                
            }else if($dados['contaAnexos'] <= 2 && $dados['validaAnexos'] == false){
                $resp = array("status" => false,"msm" => "Informe: É necessário informar mais de um documento anexo. Devem ser anexados o apostilamento assinado e a cópia do empenho.");
                print(json_encode($resp));
                return false;
            }else if($dados['contaAnexos'] < 2){
                $resp = array("status" => false,"msm" => "Informe: É necessário informar mais de um documento anexo. Devem ser anexados o apostilamento assinado e a cópia do empenho.");
                print(json_encode($resp));
                return false;

            }
           
            if(intval($dados['tipo_apostilamento']) == 1 || intval($dados['tipo_apostilamento'] == 4)){
        
                if(empty($dados['valor_apostilamento']) || $dados['valor_apostilamento'] == NULL){
                    $resp = array("status" => false,"msm" => " Informe: Valor do apostilamento.");
                    print(json_encode($resp));
                    return false;
                }

                if(empty($dados['data']) || $dados['data'] == NULL){
                    $resp = array("status" => false,"msm" => "Informe: Data do apostilamento.2");
                    print(json_encode($resp));
                    return false;
                }
            }

            if(intval($dados['tipo_apostilamento']) == 2 || intval($dados['tipo_apostilamento'] == 3)){

                if(empty($dados['data']) || $dados['data'] == NULL){
                    $resp = array("status" => false,"msm" => "Informe: Data do apostilamento.1");
                    print(json_encode($resp));
                    return false;
                }
            }

            //var_dump($dados);
         /*   if(intval($dados['tipo_apostilamento']) == 5){
                // var_dump("teste fornece");die;
                if(empty($dados['razao_social'])){ 
                    $response = array("status"=>false,"msm"=>"Informe: Fornecedor");
                    print(json_encode($response));
                    break false;
                }
            }
            */
            return true;
        }
        //Madson
        public function updatefornecedorPorAditivo($dadosSalvar){
            $cdocpcseq1 = $dadosSalvar['CDOCPCSEQ1'];
            $sqlForn = "SELECT forn.aforcrsequ as seqfornecedor, forn.nforcrrazs as razao, forn.eforcrlogr as rua, forn.aforcrnume as numero, forn.eforcrcomp as complemento, 
                        forn.cforcresta as estado, forn.eforcrbair as bairro, forn.nforcrcida as cidade, forn.cceppocodi as cep, forn.aforcrccpf as cpf, forn.aforcrccgc as cnpj, aforcrtels as telefone
                        FROM sfpc.tbaditivo as ad 
                        inner join SFPC.TBFORNECEDORCREDENCIADO as forn on  (forn.AFORCRCCGC = ad.eaditicgcc or forn.AFORCRCCPF = ad.eaditicpfc)
                        left join sfpc.tbdocumentosfpc doc on ad.cdocpcsequ = doc.cdocpcsequ 
                        where ad.CDOCPCSEQ1 = $cdocpcseq1 and ad.ctpadisequ = 13 and doc.cfasedsequ = 4 and doc.ctidocsequ = 2 and doc.csitdcsequ = 1 
                        and ad.aaditinuad = (select MAX(adit.aaditinuad) from sfpc.tbaditivo adit left join sfpc.tbdocumentosfpc docm on adit.cdocpcsequ = docm.cdocpcsequ 
                                                    where adit.cdocpcseq1 = ad.cdocpcseq1 and adit.ctpadisequ = 13 and docm.cfasedsequ = 4 and docm.ctidocsequ = 2 and docm.csitdcsequ = 1 )";

            $resultado = executarSQL($this->conexaoDb, $sqlForn);
            $resultado->fetchInto($dadosFornAdit, DB_FETCHMODE_OBJECT);

            if(is_null($dadosFornAdit)){
                $sqlfornOriginal = "SELECT forn.aforcrsequ as seqfornecedor, forn.nforcrrazs as razao, forn.eforcrlogr as rua, forn.aforcrnume as numero, forn.eforcrcomp as complemento, 
                forn.cforcresta as estado, forn.eforcrbair as bairro, forn.nforcrcida as cidade, forn.cceppocodi as cep, forn.aforcrccpf as cpf, forn.aforcrccgc as cnpj, aforcrtels as telefone
                from sfpc.tbcontratosfpc as con
                inner join SFPC.TBFORNECEDORCREDENCIADO as forn on  (forn.aforcrsequ = con.aforcrseq1)
                where con.cdocpcsequ = $cdocpcseq1";
                $resultado = executarSQL($this->conexaoDb, $sqlfornOriginal);
                $resultado->fetchInto($dadosFornOriginal, DB_FETCHMODE_OBJECT);

                $updateFC = "update sfpc.tbcontratosfpc set aforcrsequ = aforcrseq1,
                ectrpcraza = '".$dadosFornOriginal->razao."', cctrpcccep = $dadosFornOriginal->cep, ectrpclogr = '".$dadosFornOriginal->rua."', 
                actrpcnuen = $dadosFornOriginal->numero, ectrpccomp = '".$dadosFornOriginal->complemento."', ectrpcbair = '".$dadosFornOriginal->bairro."', 
                nctrpccida = '".$dadosFornOriginal->cidade."', cctrpcesta = '".$dadosFornOriginal->estado."', ectrpctlct = '".$dadosFornOriginal->telefone."'
                where cdocpcsequ = $cdocpcseq1";

                $resultado = executarSQL($this->conexaoDb, $updateFC);
                if (!empty($resultado)) {
                    return $resultado;
                }
            }else{
                $updateFC = "update sfpc.tbcontratosfpc set aforcrsequ = $dadosFornAdit->seqfornecedor,
                ectrpcraza = '".$dadosFornAdit->razao."', cctrpcccep = $dadosFornAdit->cep, ectrpclogr = '".$dadosFornAdit->rua."', 
                actrpcnuen = $dadosFornAdit->numero, ectrpccomp = '".$dadosFornAdit->complemento."', ectrpcbair = '".$dadosFornAdit->bairro."', 
                nctrpccida = '".$dadosFornAdit->cidade."', cctrpcesta = '".$dadosFornAdit->estado."', ectrpctlct = '".$dadosFornAdit->telefone."'
                where cdocpcsequ = $cdocpcseq1";

                $resultado = executarSQL($this->conexaoDb, $updateFC);
                if (!empty($resultado)) {
                    return $resultado;
                }
            }
            return false;

        }
        public function getFornecedorAditivo($aaditinuad, $cdocpcseq1){
            $sqlforn = "SELECT forn.aforcrsequ as seqfornecedor, forn.nforcrrazs as razao, forn.eforcrlogr as rua, forn.aforcrnume as numero, forn.eforcrcomp as complemento, 
            forn.cforcresta as estado, forn.eforcrbair as bairro, forn.nforcrcida as cidade, forn.cceppocodi as cep, forn.aforcrccpf as cpf, forn.aforcrccgc as cnpj, aforcrtels as telefone
            FROM sfpc.tbaditivo as ad 
            inner join SFPC.TBFORNECEDORCREDENCIADO as forn on  (forn.AFORCRCCGC = ad.eaditicgcc or forn.AFORCRCCPF = ad.eaditicpfc or forn.nforcrrazs = ad.naditirazs)
            where ad.CDOCPCSEQ1 = $cdocpcseq1 and ad.aaditinuad = $aaditinuad";
            
            $resultado = executarSQL($this->conexaoDb, $sqlforn);
            $resultado->fetchInto($dadosForn, DB_FETCHMODE_OBJECT);
            
            return $dadosForn;
        }

        //função para pegar os tipos de apostilamento
        public function getTiposApostilamentos()
        {
            $sql = "select * from sfpc.tbtipoapostilamento where ftpapositu = 'ATIVO'";
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            while ($resultado->fetchInto($dadosTAP, DB_FETCHMODE_OBJECT)) {
                $dadosRetorno[] = $dadosTAP;
            }
            return $dadosRetorno;
        }


        public function MontaSelectBoxTipoApostilamento($dados, $idComparativo = null)
        {
            $selectHtml = '<option  value="">Selecione o tipo do apostilamento</option>';
            foreach($dados as $item)
            {
                $selected = ($item->ctpaposequ == $idComparativo) ? "selected": "";
                $selectHtml .= '<option '.$selected.' value="'.$item->ctpaposequ.'">'.$item->etpapodesc.'</option>';
            }
            return $selectHtml;
        }
    }