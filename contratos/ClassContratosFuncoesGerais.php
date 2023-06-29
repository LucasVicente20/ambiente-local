<?php
  session_start(); 
  require_once dirname(__FILE__) . '/../funcoes.php';
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: ClassContratosFuncoesGerais.php
# Autor:    João Madson
# Data:     25/02/2021
# -------------------------------------------------------------------------
    
    Class ContratosFuncoesGerais {
        // public $conexaoDb;
        // public function __construct(){
        //     $this->conexaoDb = Conexao();
        // }
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
        //--------------------------------Busca de dados do Contrato-----------------------------------
        public function dadosContrato($idContrato){
            $db = Conexao();
            $sqlCont = "Select csolcosequ, vctrpceant, vctrpcvlor, vctrpcsean FROM SFPC.TBCONTRATOSFPC WHERE CDOCPCSEQU = $idContrato";
            $resultado = executarSQL($db, $sqlCont);
            $resultado->fetchInto($contrato, DB_FETCHMODE_OBJECT);
            
            return $contrato;
        }
        
        public function valorAditivosContrato($idContrato){
            $db = Conexao();
            $sql = "select sum(case when adit.vaditivalr is null or adit.vaditivalr = 0 then adit.vaditivtad else adit.vaditivalr end) as valorAditivos  
                        from sfpc.tbaditivo adit 
                        left join sfpc.tbdocumentosfpc doc on adit.cdocpcsequ = doc.cdocpcsequ 
                        where  doc.csitdcsequ = 1 and doc.cfasedsequ = 4 and adit.CDOCPCSEQ1 = $idContrato";
            $resultado = executarSQL($db, $sql);
            $resultado->fetchInto($aditivo, DB_FETCHMODE_OBJECT);
            
            return $aditivo;
        }

        public function valorApostilamentoContrato($idContrato){
            $db = Conexao();
            $sqlCont = "select sum(vapostvtap) as valorApostilamentos from sfpc.tbapostilamento apost left join sfpc.tbdocumentosfpc doc on apost.cdocpcsequ = doc.cdocpcsequ  
                        where apost.CDOCPCSEQ2 = $idContrato and doc.cfasedsequ = 6 and doc.csitdcsequ = 1";
            $resultado = executarSQL($db, $sqlCont);
            $resultado->fetchInto($apostilamento, DB_FETCHMODE_OBJECT);

            return $apostilamento;
        }

        public function valorMedicaoContrato($idContrato){
            $db = Conexao();
            $sql = "select sum(vmedcovalm) AS valorMedicao from sfpc.tbmedicaocontrato where cdocpcsequ = $idContrato";
            $resultado = executarSQL($db, $sql);
            $resultado->fetchInto($valorMedicao, DB_FETCHMODE_OBJECT);
            
            return $valorMedicao;
        }

        //----------------------------------Calculos de valores----------------------------------------
        public function valorGlobal($idContrato, $formatado = true){
            $dadosCont          = $this->dadosContrato($idContrato);
            $valorAditivo       = $this->valorAditivosContrato($idContrato);
            $valorApostilamento = $this->valorApostilamentoContrato($idContrato);
            $csolcosequ = $dadosCont->csolcosequ;
            $vctrpcsean = floatval($dadosCont->vctrpcsean);
            $vctrpcvlor = floatval($dadosCont->vctrpcvlor);
            $aditivos = floatval($valorAditivo->valoraditivos);
            $apostilamentos = floatval($valorApostilamento->valorapostilamentos);
            
            if(!empty($csolcosequ)){
                $soma = $vctrpcvlor + $aditivos + $apostilamentos;
            }else{
                $soma = $vctrpcsean + $aditivos + $apostilamentos;
            }

            if($formatado == true){
                $retorna = number_format($soma,4,',','.');
            }else{
                $retorna = $soma;
            }       
            
            return $retorna;
        
        }   
        

        public function valorOriginal($idContrato, $formatado = true){
            $db = Conexao();
            $dadosCont          = $this->dadosContrato($idContrato);

            $vctrpcvlor = floatval($dadosCont->vctrpcvlor);
            if($formatado == true){
                return  number_format($vctrpcvlor,4,',','.');
            }else{
                return  $vctrpcvlor;
            }
            
            
        }          

        public function valorExecutado($idContrato, $formatado = true){
            $dadosCont = $this->dadosContrato($idContrato);
            $dadosMed  = $this->valorMedicaoContrato($idContrato);
            $valorMedicao = floatval($dadosMed->valormedicao);
            $csolcosequ = $dadosCont->csolcosequ;
            $valorExecutadoAcumulado = floatval($dadosCont->vctrpceant);
            if(!empty($valorExecutadoAcumulado) && empty($csolcosequ)){
                $retorno = $valorMedicao + $valorExecutadoAcumulado;
            }else{
                $retorno = $valorMedicao;
            }
            if($formatado == true){
                return  number_format($retorno,4,',','.');
            }else{
                return  $retorno;
            }
        
        
        }             
          
        public function saldoAExecutar($idContrato, $formatado = true){
            $valorExecutado = $this->valorExecutado($idContrato, false);
            $valGlobal = $this->valorGlobal($idContrato, false);
            $saldo = $valGlobal - $valorExecutado;
            if($formatado == true){
                return number_format($saldo,4,',','.');
            }else{
                return $saldo;
            }
            

        }               

    }
?>