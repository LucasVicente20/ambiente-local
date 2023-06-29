  <?php
  session_start();
  require_once dirname(__FILE__) . '/../funcoes.php';
# -------------------------------------------------------------------------
# Portal da Compras
# Programa: CadAdesoesDeAtaPesquisa.php
# Autor:    Eliakim Ramos
# Data:     11/07/2022
# -------------------------------------------------------------------------
# Autor:    João Madson
# Data:     06/09/2022
# CR:		268479
# -------------------------------------------------------------------------
    
    Class ClassAdesaoAtaPesquisa {
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

        public function checaUsuarioManterEspecial(){
            $codUsuario = $_SESSION['_cusupocodi_'];
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

        public function GetOrgao(){
             $cusupocodi = $_SESSION['_cusupocodi_'];
             $perfilUser = $this->checaUsuarioManterEspecial();
            $sql  = "SELECT DISTINCT	org.corglicodi, org.eorglidesc ";
            $sql .= " FROM	sfpc.tborgaolicitante org "; 
            $sql .= " INNER JOIN sfpc.tbcentrocustoportal AS CentroCusto ON (org.corglicodi = CentroCusto.corglicodi) ";
            $sql .= " INNER JOIN      sfpc.tbusuariocentrocusto AS UsuarioCusto ON (UsuarioCusto.ccenposequ = CentroCusto.ccenposequ) ";
            //$sql .= " WHERE	1 = 1 ";
            $sql .= " WHERE	org.forglisitu = 'A' ";
            if(!$perfilUser){
                $sql .= " AND UsuarioCusto.cusupocodi = $cusupocodi  "; //AND UsuarioCusto.fusucctipo = 'C'
            }
            $sql .= " ORDER BY		org.eorglidesc ASC";
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dados = array();
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dados[] = $retorno;
            }
            return $dados;
        }

        public function GetOrgaoById($corglicodi){
            $sql  = "SELECT DISTINCT	org.corglicodi, org.eorglidesc ";
            $sql .= " FROM	sfpc.tborgaolicitante org "; 
            $sql .= " INNER JOIN sfpc.tbcentrocustoportal AS CentroCusto ON (org.corglicodi = CentroCusto.corglicodi) ";
            $sql .= " INNER JOIN      sfpc.tbusuariocentrocusto AS UsuarioCusto ON (UsuarioCusto.ccenposequ = CentroCusto.ccenposequ) ";
            $sql .= " WHERE	org.corglicodi =".$corglicodi;
            $sql .= " ORDER BY		org.eorglidesc ASC";
             $resultado = executarSQL($this->conexaoDb, $sql);
            $dados = array();
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dados = $retorno;
            }
            return $dados;
        }

        public function GetDadosScc($scc)
        {
            $sql =  "SELECT DISTINCT scc.csolcosequ, scc.ctpcomcodi, scc.esolcoobje, scc.asolcoanos, scc.csolcocodi, cc.ccenpocorg, cc.ccenpounid, scc.corglicodi, scc.carpnosequ ";
            $sql .= " FROM sfpc.tbsolicitacaocompra AS scc INNER JOIN sfpc.tbcentrocustoportal AS cc ON scc.ccenposequ = cc.ccenposequ ";
            $sql .= " where scc.csitsocodi IN (3,4) AND scc.ctpcomcodi = 5 ";
            $asolcoanos = substr($scc, -4);   //valores a partir da barra
            $csolcocodi = substr($scc, -8 , -4);
            $ccenpocorg = substr($scc, 0, 2);
            $ccenpounid = substr($scc, -10, -8);

             $sql .= " and scc.asolcoanos = $asolcoanos
                      and scc.csolcocodi = $csolcocodi
                      and cc.ccenpocorg = $ccenpocorg
                      and cc.ccenpounid = $ccenpounid";

            $resultado = executarSQL($this->conexaoDb, $sql);
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
                                'corglicodi'=> $retorno->corglicodi,
                                'carpnosequ'=> $retorno->carpnosequ
                        );
            }
            return $dadosPesquisa;
        }

        public function GetAtaExterna($carpnosequ)
        {
               $sql = " SELECT fc.nforcrrazs FROM sfpc.tbfornecedorcredenciado AS fc INNER JOIN sfpc.tbataregistroprecoexterna ON ";
               $sql .=" fc.aforcrsequ = sfpc.tbataregistroprecoexterna.aforcrsequ WHERE sfpc.tbataregistroprecoexterna.carpnosequ = ".$carpnosequ;
               $resultado = executarSQL($this->conexaoDb, $sql);
               $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
               return $retorno;
        }


        public function GetAtaInterna($carpnosequ)
        {
            $sql = "SELECT fc.nforcrrazs FROM sfpc.tbfornecedorcredenciado AS fc INNER JOIN sfpc.tbataregistroprecointerna ON fc.aforcrsequ = tbataregistroprecointerna.aforcrsequ ";
            $sql .= " WHERE tbataregistroprecointerna.carpnosequ =".$carpnosequ;
            $resultado = executarSQL($this->conexaoDb, $sql);
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            return $retorno;
        }

        public function GetSemAtaNova($csolcosequ) {
            $sql = "SELECT fc.nforcrrazs FROM sfpc.tbfornecedorcredenciado AS fc INNER JOIN sfpc.tbitemsolicitacaocompra AS isc ON isc.aforcrsequ = fc.aforcrsequ ";
            $sql .= " where isc.csolcosequ = ".$csolcosequ;
            $resultado = executarSQL($this->conexaoDb, $sql);
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            return $retorno;
        }

        public function PesquisarSCC($dados){

            $sql =  "SELECT DISTINCT scc.csolcosequ, scc.ctpcomcodi, scc.clicpoproc, scc.ccomlicodi, scc.alicpoanop, scc.esolcoobje, scc.fsolcorpcp, scc.asolcoanos, scc.csolcocodi, cc.ccenpocorg, cc.ccenpounid, fc.nforcrrazs, scc.corglicodi, scc.carpnosequ, scc.tsolcodata  ";
            $sql .= " FROM sfpc.tbsolicitacaocompra AS scc INNER JOIN sfpc.tbcentrocustoportal AS cc ON scc.ccenposequ = cc.ccenposequ ";
            $sql .= " INNER JOIN sfpc.tbitemsolicitacaocompra AS isc ON scc.csolcosequ = isc.csolcosequ ";
            $sql .= " INNER JOIN sfpc.tbfornecedorcredenciado AS fc ON isc.aforcrsequ = fc.aforcrsequ ";
            $sql .= " where scc.csitsocodi IN (3,4) AND scc.ctpcomcodi = 5 ";
            if($dados["tipo_ata"] == "EXTERNA"){
                $sql .= "AND ( ( scc.clicpoproc = 1 AND scc.ccomlicodi = 41 AND scc.alicpoanop = 2012 ) OR ( scc.carpnosequ IS NOT NULL AND scc.ccomlicodi IS NULL ) ) ";
            }elseif($dados["tipo_ata"] == "INTERNA"){
                $sql .= "AND ( scc.clicpoproc <> 1 AND scc.ccomlicodi <> 41 AND scc.alicpoanop <> 2012 )";
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
            }else {

                if(!empty($dados['orgao'])){
                    $sql .= " and scc.corglicodi in (".$dados['orgao'].")";
                }
                if(!empty($dados['tipo_sarp'])){
                    $sql .= " and scc.fsolcorpcp	 = '".$dados['tipo_sarp']."'";
                }

                if(!empty($dados['data_inicio']) && !empty($dados['data_fim'])){
                    $sql .= " AND scc.tsolcodata BETWEEN '".$dados['data_inicio']."' AND '".$dados['data_fim']."' ";
                }
            }
                $sql .= " ORDER BY scc.asolcoanos DESC, scc.csolcocodi ASC, cc.ccenpounid ASC, cc.ccenpocorg ASC";

                $resultado = executarSQL($this->conexaoDb, $sql);
                $dadosPesquisa = array();
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){

                if($retorno->clicpoproc != "1" && $retorno->ccomlicodi != "41" && $retorno->alicpoanop!= "2012"){
                    $tipoAta = "INTERNA";
                }else{
                    $tipoAta = "EXTERNA";
                }
                $dadosPesquisa[] = (object) array(
                                'csolcosequ'=> $retorno->csolcosequ,
                                'ctpcomcodi'=> $retorno->ctpcomcodi,
                                'esolcoobje'=> $retorno->esolcoobje,
                                'asolcoanos'=> $retorno->asolcoanos,
                                'csolcocodi'=> $retorno->csolcocodi,
                                'ccenpocorg'=> $retorno->ccenpocorg,
                                'ccenpounid'=> $retorno->ccenpounid,
                                'nforcrrazs'=> $retorno->nforcrrazs,
                                'corglicodi'=> $retorno->corglicodi,
                                'carpnosequ'=> $retorno->carpnosequ,
                                'fsolcorpcp' =>$retorno->fsolcorpcp,
                                'tipo_ata' =>$tipoAta,
                        );
            }
            return $dadosPesquisa;
        }

        public function GetParametrosGerais(){
            $sql="SELECT * FROM sfpc.tbparametrosgerais WHERE 1=1 ";
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            $dadosRetorno = $retorno;
            return $dadosRetorno;
        }

        public function insertAdsaoAta($dados) {
            $sql = " INSERT INTO sfpc.tbregistroprecoadesaodoc (crpaddcodi,csolcosequ,erpaddnome,trpadddata,cusupocodi,frpaddexcl,trpaddulat) ";
            $sql .= " VALUES (".$dados['crpaddcodi'].",".$dados['csolcosequ'].",'".$dados['erpaddnome']."',now(),".$_SESSION['_cusupocodi_'].",'N',now())";
            // var_dump($sql);die;
            $resultado = executarSQL($this->conexaoDb, $sql);
            return $resultado;
        }

        public function GetSeqArquivo()
        {
            $sql = 'SELECT MAX(sfpc.tbregistroprecoadesaodoc.crpaddcodi) AS oldseq FROM sfpc.tbregistroprecoadesaodoc';
            $resultado = executarSQL($this->conexaoDb, $sql);
            $dadosRetorno = array();
            $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
            $dadosRetorno = $retorno;
            return $dadosRetorno;
        }

        public function GetAllArquivo($csolcosequ)
        {
            $sql = "SELECT crpaddcodi, csolcosequ, erpaddnome FROM sfpc.tbregistroprecoadesaodoc WHERE sfpc.tbregistroprecoadesaodoc.frpaddexcl <> 'S' and sfpc.tbregistroprecoadesaodoc.csolcosequ =".$csolcosequ;
            $dadosRetorno = array();
            $resultado = executarSQL($this->conexaoDb, $sql);
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosRetorno[] = $retorno;
            }
            return $dadosRetorno;
        }

        public function ExcluirArquivo($dados)
        {
            $sql = "UPDATE sfpc.tbregistroprecoadesaodoc set frpaddexcl = 'S' where csolcosequ =".$dados['csolcosequ']." and crpaddcodi =".$dados['crpaddcodi'];
            $resultado = executarSQL($this->conexaoDb, $sql);
            return $resultado;
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

        public function DesconectaBanco(){
            $this->conexaoDb->disconnect();
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