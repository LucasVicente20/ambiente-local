<?php 
 /**
 * Portal de Compras
 * Programa: CadIncluirDFD.php
 * Autor: Diógenes Dantas | Madson Felix
 * Data: 17/11/2022
 * Objetivo: Programa para inclusão de DFD
 * Tarefa Redmine: #275243
 * -------------------------------------------------------------------
  * Alterado: Osmar Celestino
  * Data: 20/12/2022
  * Tarefa: 276459
  * -------------------------------------------------------------------
  * Alterado: Lucas Vicente
  * Data: 05/01/2023
  * Tarefa: #277231
  * -------------------------------------------------------------------
  * Alterado: João Madson
  * Data: 05/01/2023
  * Tarefa: 276691 e 276711
  * -------------------------------------------------------------------
 * Alterado:    Lucas Vicente e João Madson 
 * Data:        06/01/2023
 * Tarefa:      CR 277232
 * -------------------------------------------------------------------
 * Alterado: Lucas Vicente
 * Data:09/01/2023
 * Tarefa: Ajuste na regra do Configurador DFD
 * -------------------------------------------------------------------
 * Alterado: João Madson   
 * Data: 09/01/2023
 * Tarefa: #277372
 * -------------------------------------------------------------------
* Alterado: João Madson | Lucas Vicente  
* Data: 16/01/2023
* Tarefa: Relatório de correções Nº3 Incluir DFD
*/

# Executa o controle de segurança	#
session_start();

# Acesso ao arquivo de funções #
require_once "../funcoes.php";

Class Planejamento {

    

    public $conexaoDb; 

    public function __construct() {
        $this->conexaoDb = conexao();
    }

    //Função utilizada em CadIncluirDFD.php
    //Verifica se não é SQL Injection
    public function anti_injection($sql)
    {
        // remove palavras que contenham sintaxe sql
        preg_match("/(from|select|insert|delete|where|drop table|show tables|\*|--|\\\\)/",$sql,$matches);
        $sql = @preg_replace($matches,"",$sql);
        $sql = trim($sql);//limpa espaços vazio
        $sql = strip_tags($sql);//tira tags html e php
        $sql = addslashes($sql);//Adiciona barras invertidas a uma string
        return $sql;
    }

    //Função utilizada em CadIncluirDFD.php
    //Busca os Orgãos de centro de custo vinculado ao usuário
    public function getOrgao()
    {
        $cusupocodi = $_SESSION['_cusupocodi_'];
        $sql  = "SELECT DISTINCT	org.corglicodi, org.eorglidesc, org.aorglicnpj  ";
        $sql .= " FROM	sfpc.tborgaolicitante org "; 
        $sql .= " INNER JOIN sfpc.tbcentrocustoportal AS CentroCusto ON (org.corglicodi = CentroCusto.corglicodi) ";
        $sql .= " INNER JOIN      sfpc.tbusuariocentrocusto AS UsuarioCusto ON (UsuarioCusto.ccenposequ = CentroCusto.ccenposequ) ";
        $sql .= " WHERE			org.forglisitu = 'A' ";
        $sql .= " AND UsuarioCusto.cusupocodi = $cusupocodi AND UsuarioCusto.fusucctipo = 'C' ";
        $sql .= " ORDER BY		org.eorglidesc ASC";
        $resultado = executarSQL($this->conexaoDb, $sql);
        $dados = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
            $dados[] = $retorno;
        }
        return $dados;
    }
    //Busca os Orgãos de centro de custo vinculado ao usuário
    public function getDescOrg($corglicodi)
    {
        $cusupocodi = $_SESSION['_cusupocodi_'];
        $sql  = "select distinct org.corglicodi, org.eorglidesc FROM sfpc.tborgaolicitante as org
        inner join SFPC.TBPLANEJAMENTODFD as dfd on dfd.corglicodi = org.corglicodi
        WHERE dfd.cpldfdsequ in ($corglicodi) ORDER BY org.eorglidesc ASC";
        
        $resultado = executarSQL($this->conexaoDb, $sql);
        $dados = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
            $dados[] = $retorno;
        }
        return $dados;
    }
    //Função utilizada em ConsPesquisarDFDgetOrgaoConsultar.php
    //Busca os Orgãos independente do usuário
    public function getOrgaoConsultar()
    {
        $cusupocodi = $_SESSION['_cusupocodi_'];
        $sql  = "SELECT DISTINCT	org.corglicodi, org.eorglidesc, org.aorglicnpj  ";
        $sql .= " FROM	sfpc.tborgaolicitante org "; 
        $sql .= " WHERE			org.forglisitu = 'A' ";
        $sql .= " ORDER BY		org.eorglidesc ASC";
        $resultado = executarSQL($this->conexaoDb, $sql);
        $dados = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
            $dados[] = $retorno;
        }
        return $dados;
    }
    //Função utilizada em ConsPesquisarDFDgetOrgaoConsultar.php
    //Busca os Orgãos independente do usuário
    public function getOrgaoDesc($corglicodi)
    {
        $sql  = "SELECT DISTINCT eorglidesc, aorglicnpj";
        $sql .= " FROM	sfpc.tborgaolicitante "; 
        $sql .= " WHERE	corglicodi = $corglicodi ";
        $resultado = executarSQL($this->conexaoDb, $sql);
        $dados = array();
        $resultado->fetchInto($dados, DB_FETCHMODE_OBJECT);
        return $dados;
    }
    //Função utilizada em CadIncluirDFD.php
    //traz Centro de custo especifico
    public function getCenCustoUsuario($corglicodi)
    {
        $cusupocodi = $_SESSION['_cusupocodi_'];
        $sql  = " SELECT DISTINCT CentroCusto.ccenposequ, CentroCusto.ccenpocorg, CentroCusto.ccenpounid ";
        $sql .= " FROM	sfpc.tborgaolicitante org "; 
        $sql .= " INNER JOIN sfpc.tbcentrocustoportal AS CentroCusto ON (org.corglicodi = CentroCusto.corglicodi) ";
        $sql .= " INNER JOIN      sfpc.tbusuariocentrocusto AS UsuarioCusto ON (UsuarioCusto.ccenposequ = CentroCusto.ccenposequ) ";
        $sql .= " WHERE			org.forglisitu = 'A' ";
        $sql .= " AND UsuarioCusto.cusupocodi = $cusupocodi AND org.corglicodi = $corglicodi AND UsuarioCusto.fusucctipo = 'C' ";
        $sql .= " ORDER BY	CentroCusto.ccenposequ, CentroCusto.ccenpocorg, CentroCusto.ccenpounid  ASC";
        
        $resultado = executarSQL($this->conexaoDb, $sql);
        $dados = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
            $dados[] = $retorno;
        }
        
        return $dados;
    }

    //Função utilizada em CadIncluirDFD.php
    //Busca a classe de material ou serviço
    public function pesquisaClasseMatServ($tipoDePesquisa, $opcaoDePesquisa, $dadoAPesquisar)
    {
        $vogaisAcuentuaDas = "'ÁÃÂáãâÉÊêéÍÎîíÓÔÕóôõÚÛûúÇçÑñ'";
        $vogaisSemAcento   = "'AAAaaaEEeeIIiiOOOoooUUuuCcNn'";
        switch($tipoDePesquisa) {
            case "ClasseDescricaoDireta":
                $sqlwhere = "";
                $_SESSION['servico'] = "C";
                if($opcaoDePesquisa == "0") {
                    $dadoAPesquisar = intval($dadoAPesquisar);
                    $sqlwhere .= "and cms.cclamscodi = $dadoAPesquisar";
                }else if($opcaoDePesquisa == "1") {
                    $sqlwhere .= "and translate (cms.eclamsdesc, $vogaisAcuentuaDas, $vogaisSemAcento) ilike 
                            translate ('%$dadoAPesquisar%', $vogaisAcuentuaDas, $vogaisSemAcento)";
                }else if($opcaoDePesquisa == "2") {
                    $sqlwhere .= "and translate (cms.eclamsdesc, $vogaisAcuentuaDas, $vogaisSemAcento) ilike 
                            translate ('$dadoAPesquisar%', $vogaisAcuentuaDas, $vogaisSemAcento)";
                }
                $sql = "
                    select cms.cclamscodi, cms.eclamsdesc, cms.cgrumscodi, mat.cmatepsequ as sequencialmatserv , mat.ematepdesc as descricaomatserv, unid.eunidmsigl 
                    from sfpc.tbclassematerialservico cms 
                    left join sfpc.tbsubclassematerial sub on cms.cclamscodi = sub.cclamscodi and cms.cgrumscodi = sub.cgrumscodi 
                    inner join sfpc.tbmaterialportal mat on sub.csubclsequ = mat.csubclsequ 
                    inner join sfpc.tbunidadedemedida unid on unid.cunidmcodi = mat.cunidmcodi
                    where cms.fclamssitu = 'A' and mat.cmatepsitu = 'A' $sqlwhere
                    union all
                    select cms.cclamscodi, cms.eclamsdesc, cms.cgrumscodi, serv.cservpsequ, serv.eservpdesc, '-'
                    from sfpc.tbclassematerialservico cms 
                    left join sfpc.tbservicoportal serv on cms.cclamscodi = serv.cclamscodi and cms.cgrumscodi = serv.cgrumscodi 
                    where cms.fclamssitu = 'A'  and serv.cservpsitu = 'A' $sqlwhere
                    order by eclamsdesc, descricaomatserv
                ";
                
                break;
            case "MaterialDescricaoDireta":
                $_SESSION['servico'] = "M";
                $sql = "
                    select cms.cclamscodi, cms.eclamsdesc, cms.cgrumscodi, mat.cmatepsequ, mat.ematepdesc, unid.eunidmsigl 
                    from sfpc.tbclassematerialservico cms
                    left join sfpc.tbsubclassematerial sub on cms.cclamscodi = sub.cclamscodi and cms.cgrumscodi = sub.cgrumscodi 
                    inner join sfpc.tbmaterialportal mat on sub.csubclsequ = mat.csubclsequ
                    inner join sfpc.tbunidadedemedida unid on unid.cunidmcodi = mat.cunidmcodi
                    where
                    cms.fclamssitu = 'A' and mat.cmatepsitu = 'A'
                ";
                if($opcaoDePesquisa == "0") {
                    $dadoAPesquisar = intval($dadoAPesquisar);
                    $sql .= "and mat.cmatepsequ = $dadoAPesquisar"; 
                }else if($opcaoDePesquisa == "1") {
                    $sql .= "and translate (mat.ematepdesc, $vogaisAcuentuaDas, $vogaisSemAcento) ilike 
                            translate ('%$dadoAPesquisar%', $vogaisAcuentuaDas, $vogaisSemAcento)";
                }else if($opcaoDePesquisa == "2") {
                    $sql .= "and translate (mat.ematepdesc, $vogaisAcuentuaDas, $vogaisSemAcento) ilike 
                            translate ('$dadoAPesquisar%', $vogaisAcuentuaDas, $vogaisSemAcento)";
                }   
                $sql .= "order by cms.cclamscodi, mat.ematepdesc";   
                break;
            case "OpcaoPesquisaServico":
                $sql = "
                    select cms.cclamscodi, cms.eclamsdesc, cms.cgrumscodi, serv.cservpsequ, serv.eservpdesc 
                    from sfpc.tbclassematerialservico cms
                    left join sfpc.tbservicoportal serv on cms.cclamscodi = serv.cclamscodi and cms.cgrumscodi = serv.cgrumscodi
                    where
                    cms.fclamssitu = 'A' and serv.cservpsitu = 'A'
                ";
                $_SESSION['servico'] = "S";
                if($opcaoDePesquisa == "0") {
                    $dadoAPesquisar = intval($dadoAPesquisar);
                   $sql .= "and serv.cservpsequ = $dadoAPesquisar";
                }else if($opcaoDePesquisa == "1") {
                     $sql .= "and translate (serv.eservpdesc, $vogaisAcuentuaDas, $vogaisSemAcento) ilike 
                            translate ('%$dadoAPesquisar%', $vogaisAcuentuaDas, $vogaisSemAcento)";
                }else if($opcaoDePesquisa == "2") {
                    $sql .= "and translate (serv.eservpdesc, $vogaisAcuentuaDas, $vogaisSemAcento) ilike 
                            translate ('$dadoAPesquisar%', $vogaisAcuentuaDas, $vogaisSemAcento)";
                }
                $sql .= "order by cms.cclamscodi, serv.eservpdesc";   
                break;
        }
        
        $resultado = executarSQL($this->conexaoDb, $sql);
        $dados = array();
        while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
            if($_SESSION['servico'] == "C"){
                if(!empty($retorno->sequencialmatserv) || !empty($retorno->descricaomatserv)){
                    $dados[] = $retorno;
                }
            }else{
                $dados[] = $retorno;
            }
            
        }
        
        return $dados;
    }
    //Função utilizada em CadIncluirDFD.php
    //Busca a classe de material ou serviço
    public function consultaClasseMaterial($cclamscodi, $cgrumscodi)
    {
        $sql = "
            select clm.cclamscodi, clm.eclamsdesc, clm.cgrumscodi, gru.fgrumstipo 
            from sfpc.tbclassematerialservico as clm
            inner join sfpc.tbgrupomaterialservico as gru on (gru.cgrumscodi = clm.cgrumscodi) 
            where clm.cclamscodi = $cclamscodi and clm.cgrumscodi = $cgrumscodi
        ";
                
        $resultado = executarSQL($this->conexaoDb, $sql);
        $dados = array();
        $resultado->fetchInto($dados, DB_FETCHMODE_OBJECT);
        
        return $dados;
    }

    /**
     * Função utilizada em ConsPesquisarDFD.php;
     * Lista um array com os status da situação do DFD;
     */
    public function getSituacaoDFD()
    {
        $sqlSitDFD = "
            SELECT CPLSITCODI, EPLSITNOME
            FROM SFPC.TBPLANEJAMENTOSITUACAODFD
            ORDER BY EPLSITNOME ASC 
        ";

        $resultado = executarSQL($this->conexaoDb, $sqlSitDFD);
        $dadosSit = array();

        while ($resultado->fetchInto($retornoSit, DB_FETCHMODE_OBJECT)) {
            $dadosSit[] = $retornoSit;
        }

        return $dadosSit;
    }
    /**
     * Função utilizada em ConsPesquisarDFD.php;
     * Adiciona os traços e pontos em CPF ou CNPJ
     */
    public function MascarasCPFCNPJ($valor) {
        $checaSeFormatado = strripos($valor, "-");
        if($checaSeFormatado == true) {
            return $valor;
        }
        if(strlen($valor) == 11) {
            $mascara = "###.###.###-##";
            for($i =0; $i <= strlen($mascara); $i++) {
                if($mascara[$i] == "#") {
                    if(isset($valor[$k])){
                       $maskared .= $valor[$k++];
                    }
                }else {
                    $maskared .= $mascara[$i];
                }
            }

            return $maskared;
        }
        if(strlen($valor) == 14) {
            $mascara = "##.###.###/####-##";
            for($i =0; $i <= strlen($mascara); $i++) {
                if($mascara[$i] == "#") {
                    if(isset($valor[$k])){
                       $maskared .= $valor[$k++];
                    }
                }else {
                    $maskared .= $mascara[$i];
                }
            }

            // var_dump($maskared);
            return $maskared;
        }
    }
    /**
     * Função utilizada em ConsPesquisarDFD.php;
     * Gera um novo sequencial do DFD;
     */
    function novoSequencialPlanejamento() {
        $sql = "select max(cpldfdsequ) from sfpc.tbplanejamentodfd";
        $retorno = executarSQL($this->conexaoDb, $sql);
        $result = 0;
        $retorno->fetchInto($result, DB_FETCHMODE_OBJECT);
        $cpldfdsequ = $result->max + 1;

        return $cpldfdsequ;
    }
    function novoSequencialPlanejamentoAgrupamento() {
        $sql = "select max(cplagdsequ) from sfpc.tbplanejamentoagrupamentodfd";
        $retorno = executarSQL($this->conexaoDb, $sql);
        $result = 0;
        $retorno->fetchInto($result, DB_FETCHMODE_OBJECT);
        $cpldfdsequ = $result->max + 1;

        return $cpldfdsequ;
    }
    /**
     * Função utilizada em CadManterDFD.php;
     * Gera um novo sequencial do historico da situação do DFD;
     */
    function novoSequencialHistoricoSituacao() {
        $sql = "select max(cplhsisequ) from sfpc.tbplanejamentohistoricosituacaodfd";
        $retorno = executarSQL($this->conexaoDb, $sql);
        $result = 0;
        $retorno->fetchInto($result, DB_FETCHMODE_OBJECT);
        $cpldfdsequ = $result->max + 1;

        return $cpldfdsequ;
    }
    /**
     * Função utilizada em ConsPesquisarDFD.php;
     * Gera um novo numero sequencial do DFD para orgão e ano;
     */
    function novoSequencialDFD($corglicodi, $apldfdanod) {
        $sql = "select max(cpldfdnumd) from sfpc.tbplanejamentodfd where corglicodi = $corglicodi and apldfdanod = $apldfdanod";
        $retorno = executarSQL($this->conexaoDb, $sql);
        $result = 0;
        $retorno->fetchInto($result, DB_FETCHMODE_OBJECT);
        $cpldfdnumd = $result->max + 1;

        return $cpldfdnumd;
    }
    /**
     * Função utilizada em ConsPesquisarDFD.php;
     * Gera um novo numero sequencial do Histórico da sitação DFD;
     */
    function novoSequencialHistoricoDFD() {
        $sql = "select max(cplhsisequ) from sfpc.tbplanejamentohistoricosituacaodfd";
        $retorno = executarSQL($this->conexaoDb, $sql);
        $result = 0;
        $retorno->fetchInto($result, DB_FETCHMODE_OBJECT);
        $cplhsisequ = $result->max + 1;

        return $cplhsisequ;
    }
    /**
     * Função utilizada em ConsPesquisarDFD.php;
     * Valida os dados a serem inseridos no banco, se houver algo faltando sera montada a reclamação;
     */
    function validarInclusaoDFD($dados) {
        $mensagemErro = "";
        if(empty($dados['selectAnoPCA'])) {
            $mensagemErro .= "Ano PCA, ";
            $dados['selectAnoPCA'] = 'null';
        }
        if($dados['selectAreaReq'] == -1) {
            $mensagemErro .= "Área Requisitante, ";
            $dados['selectAreaReq'] = 'null';
        }

        if(empty($dados['cclamscodi'])) {
            $mensagemErro .= "Classe, ";
        }

        if(empty($dados['descSuDemanda'])) {
            $mensagemErro .= "Descrição Sucinta da Demanda, ";
        }

        if(empty($dados['justContratacao'])) {
            $mensagemErro .= "Justificativa da necessidade de contratação, ";
        }

        if(empty($dados['estValorContratacao'])) {
            $mensagemErro .= "Estimativa preliminar de valor contratação, ";
        }

        if(empty($dados['tpProcContratacao'])) {
            $mensagemErro .= "Tipo de Processo de contratação, ";
        }

        if(empty($dados['dtPretendidaConc'])) {
            $mensagemErro .= "Data estimada para conclusão, ";
        }else{
            $dtpretConc  = explode("/", $dados['dtPretendidaConc']);
            if($dtpretConc[2] != $dados['selectAnoPCA']){
                $mensagemErro .= "Ano da Data pretendida de conclusão igual ao ano do PCA, ";
            }else{
                $checaData = checkdate($dtpretConc[1], $dtpretConc[0], $dtpretConc[2]);
                if($checaData == false){
                    $mensagemErro .= "Data estimada para conclusão, ";
                }
            }
        }

        if(empty($dados['grauPrioridade'])) {
            $mensagemErro .= "Grau de Prioridade, ";
        }

        if(empty($dados['justPriAlta']) && $dados['grauPrioridade'] == "1") {
            $mensagemErro .= "Justificativa para prioridade alta, ";
        }

        // if(empty($dados['vincOutroDFD'])) {
        //     $mensagemErro .= "Vinculação outro DFD";
        // }

        if(empty($dados['contratCorp']) && $dados['selectAreaReq'] == 18) {
            $mensagemErro .= "Compra Corporativa, ";
        }
        //A validação da COnfiguração vai ficar em Standby por enquanto. NÃO REMOVER!
        // Realiza a checagem nas configurações antes da inclusão
        // $db = Conexao();
        // $sql = "SELECT cplcontpmd, fplcontpmd, tplcondtin, tplcondtfi 
        //         from sfpc.tbplanejamentoconfiguracao 
        //         where cplconcodi = (select max(cplconcodi) 
        //                             from sfpc.tbplanejamentoconfiguracao 
        //                             where corglicodi = ".$dados['selectAreaReq']." and aplconanop = ".$dados['selectAnoPCA']." and cplcontpmd = 1)";
        
        // $result = $db->query($sql);
        // $count = $result->numRows();

        // $Linha = $result->fetchRow();
        // $opcaoModificacao   =    $Linha[0];
        // $tipoModificacao    =    $Linha[1];
        // $dataIni            =    $Linha[2];
        // $dataFim            =    $Linha[3];
 
        // if ($count == 0 || $count == NULL){
        //     $mensagemErro .= "A Área requisitante informada não possui cadastro para o Ano PCA informado, ";
        // }

        // if ($opcaoModificacao != 1 && $opcaoModificacao!=NULL){
        //     $mensagemErro .= "A Área requisitante informada não possui cadastro para inclusão no Ano PCA informado, ";
        // }

        // if ($tipoModificacao != 1 && $tipoModificacao!=NULL){
        //     $mensagemErro .= "A Área requisitante informada está bloqueada para inclusão no Ano PCA informado, ";
        // }

        // $dataRegistro = date('Y-m-d');
        // if ($dataIni <= $dataRegistro && $dataFim >= $dataRegistro){
            
        // }else{

        //     $dataIniConvertida = date('d/m/Y',strtotime($dataIni));
        //     $dataFimConvertida = date('d/m/Y',strtotime($dataFim));
        //     if($dataIniConvertida != '01/01/1970' && $dataFimConvertida !='01/01/1970'){
        //         $mensagemErro .= "Cadastro não realizado. Período para cadastro: De $dataIniConvertida a $dataFimConvertida, ";
        //     }
        // }

        // Organiza mensagem caso algo esteja faltando;
        if($mensagemErro == "") {
            $retorno["erro"]     = false;
            $retorno["informe"]  = "";
        }else {
            $retorno["erro"] = true;
            $mensagemErro = substr_replace($mensagemErro, '.', strrpos($mensagemErro, ", ")); // Remove a ultima virgula e adiciona o ponto final
            $retorno["informe"]  = "Informe: ".$mensagemErro;
        }

        return $retorno;
    }
    function validarRascunhoDFD($dados) {
        $mensagemErro = "";
        if(empty($dados['selectAnoPCA'])) {
            $mensagemErro .= "Ano PCA, ";
        }
        if(empty($dados['selectAreaReq']) || $dados['selectAreaReq'] == -1) {
            $mensagemErro .= "Área Requisitante, ";
        }

        if(empty($dados['cclamscodi'])) {
            $mensagemErro .= "Classe, ";
        }        

        if(!empty($dados['dtPretendidaConc'])) {// O campo não é obrigatório mas a checagem baixo é caso venha algo
            $dtpretConc  = explode("/", $dados['dtPretendidaConc']);
            if($dtpretConc[2] != $dados['selectAnoPCA']){
                $mensagemErro .= "Data pretendida de conclusão não pode ter ano diferente do Ano do PCA, ";
            }
        }

        // Organiza mensagem caso algo esteja faltando;
        if($mensagemErro == "") {
            $retorno["erro"]     = false;
            $retorno["informe"]  = "";
        }else {
            $retorno["erro"] = true;
            $mensagemErro = substr_replace($mensagemErro, '.', strrpos($mensagemErro, ", ")); // Remove a ultima virgula e adiciona o ponto final
            $retorno["informe"]  = "Informe: ".$mensagemErro;
        }

        return $retorno;
    }
    function validarManterDFD($dados) {
        //Pega os campos alteraveis e valida os obrigatórios
        $informe = "";
        
        if($_POST['chaveNovaClasse'] == "true"){
            if(empty($dados->cclamscodi) && empty($dados->cclamscodi)) {
                $informe .= "Classe, ";
            }
        }

        if($dados->epldfddesc=='' || empty($dados->epldfddesc)){
            $informe .= "Descrição Sucinta da Demanda, "; 
        }

        if(empty($dados->epldfdjust)){
            $informe .= "Justificativa da necessidade de contratação, ";
        }
        
        if($dados->cpldfdvest == '0,0000' || $dados->cpldfdvest == '' || $dados->cpldfdvest == '0.0000'){
            $informe .= "Estimativa preliminar de valor contratação, ";
        }
        if(empty($dados->fpldfdtpct)){
            $informe .= "Tipo de Processo de contratação, ";
        }        
        if(empty($dados->dpldfdpret)) {
            $informe .= "Data estimada para conclusão, ";
        }else{
            $dtpretConc  = explode("/", $_POST['dtPretendidaConc']);
            if($dtpretConc[2] != $_SESSION['DFD']->apldfdanod){
                $informe .= "Ano da Data estimada de conclusão igual ao ano do PCA, ";
            }
        }

        if(empty($dados->fpldfdgrau)){
            $informe .= "Grau de Prioridade, ";  
        }
        
        if($dados->fpldfdgrau == 1 && empty($dados->epldfdjusp)){
            $informe .= "Justificativa para prioridade alta, ";
        }
        
        //O codigo abaixo é da configuração, retorna na V2
        // $db = Conexao();
        // $sql = "SELECT cplcontpmd, fplcontpmd, tplcondtin, tplcondtfi 
        //         from sfpc.tbplanejamentoconfiguracao 
        //         where cplconcodi = (select max(cplconcodi) 
        //                             from sfpc.tbplanejamentoconfiguracao 
        //                             where corglicodi = ".$dadosDFD->corglicodi." and aplconanop = ".$dadosDFD->apldfdanod." and cplcontpmd = 2)";
        
        // $result = $db->query($sql);
        // $count = $result->numRows();

        // $Linha = $result->fetchRow();
        // $opcaoModificacao   =    $Linha[0];
        // $tipoModificacao    =    $Linha[1];
        // $dataIni            =    $Linha[2];
        // $dataFim            =    $Linha[3];

        // if ($tipoModificacao != 1 && $tipoModificacao!=NULL){
        //     $informe .= "A Área requisitante informada está bloqueada para alteração no Ano PCA informado, ";
        // }

        
        if($informe != "") {
            $retorno['valida'] = false;
            $retorno["status"] = false;
            $informe = substr_replace($informe, '.', strrpos($informe, ", ")); // Remove a ultima virgula e adiciona o ponto final
            $retorno["msm"]  = "Informe: ".$informe;
        }else{
            $retorno['valida'] = true;
        }

        return $retorno;
    }

    function validarManterRascunhoDFD($dados) {
        $informe = "";
        
        //verifica se tem alteração na classe
        if($_POST['chaveNovaClasse'] == "true"){
            if(empty($dados->cclamscodi) && empty($dados->cclamscodi)) {
                $informe .= "Classe, ";
            }
        }

        if($informe != "") {
            $retorno['valida'] = false;
            $retorno["status"] = false;
            $informe = substr_replace($informe, '.', strrpos($informe, ", ")); // Remove a ultima virgula e adiciona o ponto final
            $retorno["msm"]  = "Informe: ".$informe;
        }else{
            $retorno['valida'] = true;
        }

        return $retorno;
    }
    /**
     * Função utilizada em ConsPesquisarDFD.php;
     * Trata os dados do Rascunho antes de colocar na query;
     */
    function trataDadosRascunhoDFDParaInsert($dados) {

        //Gera numero DFD
        $novoSeqDFD = $this->novoSequencialDFD($dados['selectAreaReq'], $dados["selectAnoPCA"]);
        if(strlen($novoSeqDFD) < 4){
            $quantosZeros = 4 - strlen($novoSeqDFD);
            $cpldfdnumd = "";
            for($i=0; $i<$quantosZeros; $i++){
                $cpldfdnumd .= "0";
            }
            $cpldfdnumd .= $novoSeqDFD;
        }else{
            $cpldfdnumd = $novoSeqDFD;
        }
        $cpldfdnumf ="'".$dados['numDFDParteUm'].".".$cpldfdnumd."/".$dados["selectAnoPCA"]."'";

        //Gera numero Historico
        $cplhsisequ = $this->novoSequencialHistoricoDFD();

        //organizar a data
        $dtpretConc  = explode("/", $dados['dtPretendidaConc']);
        $dpldfdpret = mktime(00,00,00, $dtpretConc[1], $dtpretConc[0], $dtpretConc[2]);        

        // organiza o valor estimado
        $cpldfdvest = moeda2float($dados['estValorContratacao']);

        $dadosTratados['cpldfdsequ'] = $dados['cpldfdsequ']; //Sequencial do DFD vem do banco
        $dadosTratados['apldfdanod'] = $dados["selectAnoPCA"];
        $dadosTratados['cpldfdnumd'] = $novoSeqDFD;
        $dadosTratados['cpldfdnumf'] = $cpldfdnumf; 
        $dadosTratados['corglicodi'] = $dados['selectAreaReq'];
        $dadosTratados['fpldfdcorp'] = !empty($dados['contratCorp'])? "'".$dados['contratCorp']."'" : "null";
        // $dadosTratados['apldfdcnpj'] = $dados['cnpjAreaReq'];
        $dadosTratados['cgrumscodi'] = !empty($dados['cgrumscodi']) ? $dados['cgrumscodi'] : "null"; 
        $dadosTratados['cclamscodi'] = !empty($dados['cclamscodi']) ? $dados['cclamscodi'] : "null";
        $dadosTratados['eclamsdesc'] = !empty($dados['eclamsdesc']) ? "'".$dados['eclamsdesc']."'":"null";//verificar dinamica com servico/material eclamsdesc
        $dadosTratados['epldfddesc'] = !empty($dados['descSuDemanda']) ? "'".strtoupper($this->anti_injection($dados['descSuDemanda']))."'":"null";
        $dadosTratados['epldfdjust'] = !empty($dados['justContratacao']) ? "'".strtoupper($this->anti_injection($dados['justContratacao']))."'":"null";
        $dadosTratados['cpldfdvest'] = !empty($cpldfdvest)?$cpldfdvest:"null";
        $dadosTratados['fpldfdtpct'] = !empty($dados['tpProcContratacao'])?"'".$dados['tpProcContratacao']."'":"null";
        $dadosTratados['dpldfdpret'] = !empty($dpldfdpret)?"'".date("Y-m-d", $dpldfdpret)." 00:00:00'":"null"; // data tratada para o tipo Typestamp
        $dadosTratados['fpldfdgrau'] = !empty($dados['grauPrioridade'])?$dados['grauPrioridade']:"null";
        $dadosTratados['epldfdjusp'] = !empty($dados['justPriAlta'])?"'".strtoupper($this->anti_injection($dados['justPriAlta']))."'":"null";
        $dadosTratados['fpldfdagru'] = "'"."N"."'"; // concatenei para que as aspas entrem na query
        $dadosTratados['tpldfdincl'] = "now()";
        $dadosTratados['cusupocodi'] = !empty($_SESSION['_cusupocodi_'])?$_SESSION['_cusupocodi_']:"null";
        $dadosTratados['tpldfdulat'] = "now()";
        $dadosTratados['cplsitcodi'] = ($dados['rascunho'] == true)? 1:2; // 1 é para rascunho e 0 para Cadastrado;
        $dadosTratados['cplvincodi'] = !empty($dados['cplvincodi'])?$dados['cplvincodi']:"null";

        //Para o insert do item
        // for($i=0;$i<count($dados['cplitecodi']);$i++){
        //     $dadosTratados['cplitecodi'][] = $dados['cplitecodi'][$i];
        //     $dadosTratados['cmatepsequ'][] = (!empty($dados['cmatepsequ'][$i]))? $dados['cmatepsequ'][$i]:"null";
        //     $dadosTratados['cservpsequ'][] = (!empty($dados['cservpsequ'][$i]))? $dados['cservpsequ'][$i]:"null";
        // }"'".
        // $dadosTratados['tpliteincl'] = "now()";
        // $dadosTratados['tpliteulat'] = "now()";
        $i=0;
        foreach($dados['itens'] as $item){
            $dadosTratados['itens'][$i]->cplitecodi = (!empty($item->cplitecodi))? $item->cplitecodi: "null";
            $dadosTratados['itens'][$i]->cmatepsequ = (!empty($item->cmatepsequ))? $item->cmatepsequ: "null";
            $dadosTratados['itens'][$i]->cservpsequ = (!empty($item->cservpsequ))? $item->cservpsequ: "null";
            $i++;
        }

        $dadosTratados['tpliteincl'] = "now()";
        $dadosTratados['tpliteulat'] = "now()";

        //Para insert do Histórico
        $dadosTratados['cplhsisequ'] = $cplhsisequ;
        $dadosTratados['eplhsijust'] = "''";
        $dadosTratados['tplhsiincl'] = "now()";
        $dadosTratados['tplhsiulat'] = "now()";
        

        return $dadosTratados;
    }
    /**
     * Função utilizada em ConsPesquisarDFD.php;
     * Trata os dados antes de colocar na query;
     */
    function trataDadosDFDParaInsert($dados) {

        //Gera numero DFD
        $novoSeqDFD = $this->novoSequencialDFD($dados['selectAreaReq'], $dados["selectAnoPCA"]);
        if(strlen($novoSeqDFD) < 4){
            $quantosZeros = 4 - strlen($novoSeqDFD);
            $cpldfdnumd = "";
            for($i=0; $i<$quantosZeros; $i++){
                $cpldfdnumd .= "0";
            }
            $cpldfdnumd .= $novoSeqDFD;
        }else{
            $cpldfdnumd = $novoSeqDFD;
        }
        $cpldfdnumf ="'".$dados['numDFDParteUm'].".".$cpldfdnumd."/".$dados["selectAnoPCA"]."'";

        //Gera numero Historico
        $cplhsisequ = $this->novoSequencialHistoricoDFD();

        //organizar a data
        $dtpretConc  = explode("/", $dados['dtPretendidaConc']);
        $dpldfdpret = mktime(00,00,00, $dtpretConc[1], $dtpretConc[0], $dtpretConc[2]);        

        // organiza o valor estimado
        $cpldfdvest = moeda2float($dados['estValorContratacao']);

        $dadosTratados['cpldfdsequ'] = $dados['cpldfdsequ']; //Sequencial do DFD vem do banco
        $dadosTratados['apldfdanod'] = $dados["selectAnoPCA"];
        $dadosTratados['cpldfdnumd'] = $novoSeqDFD;
        $dadosTratados['cpldfdnumf'] = $cpldfdnumf; 
        $dadosTratados['corglicodi'] = $dados['selectAreaReq'];
        $dadosTratados['fpldfdcorp'] = "'".$dados['contratCorp']."'";
        // $dadosTratados['apldfdcnpj'] = $dados['cnpjAreaReq'];
        $dadosTratados['cgrumscodi'] = $dados['cgrumscodi']; 
        $dadosTratados['cclamscodi'] = $dados['cclamscodi'];
        $dadosTratados['eclamsdesc'] = "'".$dados['eclamsdesc']."'";//verificar dinamica com servico/material eclamsdesc
        $dadosTratados['epldfddesc'] = "'".strtoupper($this->anti_injection($dados['descSuDemanda']))."'";
        $dadosTratados['epldfdjust'] = "'".strtoupper($this->anti_injection($dados['justContratacao']))."'";
        $dadosTratados['cpldfdvest'] = !empty($cpldfdvest)?$cpldfdvest:"null";
        $dadosTratados['fpldfdtpct'] = "'".$dados['tpProcContratacao']."'";
        $dadosTratados['dpldfdpret'] = "'".date("Y-m-d", $dpldfdpret)." 00:00:00'"; // data tratada para o tipo Typestamp
        $dadosTratados['fpldfdgrau'] = $dados['grauPrioridade'];
        $dadosTratados['epldfdjusp'] = "'".strtoupper($this->anti_injection($dados['justPriAlta']))."'";
        $dadosTratados['fpldfdagru'] = "'"."N"."'"; // concatenei para que as aspas entrem na query
        $dadosTratados['tpldfdincl'] = "now()";
        $dadosTratados['cusupocodi'] = $_SESSION['_cusupocodi_'];
        $dadosTratados['tpldfdulat'] = "now()";
        $dadosTratados['cplsitcodi'] = 2; // 1 é para rascunho e 2 para PARA ENCAMINHAMENTO;
        $dadosTratados['cplvincodi'] = !empty($dados['cplvincodi'])?$dados['cplvincodi']:"null";

        //Para o insert do item
        // for($i=0;$i<count($dados['cplitecodi']);$i++){
        //     $dadosTratados['cplitecodi'][] = $dados['cplitecodi'][$i];
        //     $dadosTratados['cmatepsequ'][] = (!empty($dados['cmatepsequ'][$i]))? $dados['cmatepsequ'][$i]:"null";
        //     $dadosTratados['cservpsequ'][] = (!empty($dados['cservpsequ'][$i]))? $dados['cservpsequ'][$i]:"null";
        // }"'".
        $i=0;
        foreach($dados['itens'] as $item){
            $dadosTratados['itens'][$i]->cplitecodi = (!empty($item->cplitecodi))? $item->cplitecodi: "null";
            $dadosTratados['itens'][$i]->cmatepsequ = (!empty($item->cmatepsequ))? $item->cmatepsequ: "null";
            $dadosTratados['itens'][$i]->cservpsequ = (!empty($item->cservpsequ))? $item->cservpsequ: "null";
            $i++;
        }

        $dadosTratados['tpliteincl'] = "now()";
        $dadosTratados['tpliteulat'] = "now()";

        //Para insert do Histórico
        $dadosTratados['cplhsisequ'] = $cplhsisequ;
        $dadosTratados['eplhsijust'] = "''";
        $dadosTratados['tplhsiincl'] = "now()";
        $dadosTratados['tplhsiulat'] = "now()";

        return $dadosTratados;
    }
    /**
     * Função utilizada em ConsPesquisarDFD.php;
     * Monta a query e insere nobanco;
     */
    function insereDFD($dadosDFD) {

        $sqlInsertDFD = "
        insert into sfpc.tbplanejamentodfd (
            cpldfdsequ,
            apldfdanod,
            cpldfdnumd,
            cpldfdnumf,
            corglicodi,
            fpldfdcorp,
            cgrumscodi,
            cclamscodi,
            epldfddesc,
            epldfdjust,
            cpldfdvest,
            fpldfdtpct,
            dpldfdpret,
            fpldfdgrau,
            epldfdjusp,
            fpldfdagru,
            tpldfdincl,
            cusupocodi,
            tpldfdulat,
            cplsitcodi,
            cplvincodi
        ) values(
            ".$dadosDFD['cpldfdsequ'].", 
            ".$dadosDFD['apldfdanod'].", 
            ".$dadosDFD['cpldfdnumd'].", 
            ".$dadosDFD['cpldfdnumf'].", 
            ".$dadosDFD['corglicodi'].", 
            ".$dadosDFD['fpldfdcorp'].", 
            ".$dadosDFD['cgrumscodi'].", 
            ".$dadosDFD['cclamscodi'].", 
            ".$dadosDFD['epldfddesc'].", 
            ".$dadosDFD['epldfdjust'].", 
            ".$dadosDFD['cpldfdvest'].", 
            ".$dadosDFD['fpldfdtpct'].", 
            ".$dadosDFD['dpldfdpret'].", 
            ".$dadosDFD['fpldfdgrau'].", 
            ".$dadosDFD['epldfdjusp'].", 
            ".$dadosDFD['fpldfdagru'].", 
            ".$dadosDFD['tpldfdincl'].", 
            ".$dadosDFD['cusupocodi'].", 
            ".$dadosDFD['tpldfdulat'].", 
            ".$dadosDFD['cplsitcodi'].",
            ".$dadosDFD['cplvincodi']."
            )
        ";
        $resultadoDFD = executarSQL($this->conexaoDb, $sqlInsertDFD);

        foreach($dadosDFD['itens'] as $item){
            $sqlInsertItem ="
                insert into sfpc.tbitemplanejamentodfd (
                cpldfdsequ,
                cplitecodi,
                cmatepsequ,
                cservpsequ,
                tpliteincl,
                cusupocodi,
                tpliteulat
                ) values(
                ".$dadosDFD['cpldfdsequ'].",
                ".$item->cplitecodi.",
                ".$item->cmatepsequ.",
                ".$item->cservpsequ.",
                ".$dadosDFD['tpliteincl'].",
                ".$dadosDFD['cusupocodi'].",
                ".$dadosDFD['tpliteulat']."
                )
            ";
            
            $resultadoItemDFD = executarSQL($this->conexaoDb, $sqlInsertItem);
        }
        
        $sqlInsertHistorico ="
            insert into sfpc.tbplanejamentohistoricosituacaodfd 
            (
                cplhsisequ, 
                cpldfdsequ,	
                cplsitcodi,	
                eplhsijust,	
                tplhsiincl,	
                cusupocodi,	
                tplhsiulat
            ) values ( 
                ".$dadosDFD['cplhsisequ'].",
                ".$dadosDFD['cpldfdsequ'].",
                ".$dadosDFD['cplsitcodi'].",
                ".$dadosDFD['eplhsijust'].",
                ".$dadosDFD['tplhsiincl'].",
                ".$dadosDFD['cusupocodi'].",
                ".$dadosDFD['tplhsiulat']."
            )
        ";

        $resultadoItemDFD = executarSQL($this->conexaoDb, $sqlInsertHistorico);
       

        return true;


    }
    function gerarSequencialVincularDFD (){
        $sql = "select max(cplvinsequ) from sfpc.tbplanejamentovinculodfd";
        $retorno = executarSQL($this->conexaoDb, $sql);
        $result = 0;
        $retorno->fetchInto($result, DB_FETCHMODE_OBJECT);
        $cpldfdsequ = $result->max + 1;

        return $cpldfdsequ;
    }
    function gerarCodigoGrupolVincularDFD(){
        $sql = "select max(cplvincodi) from sfpc.tbplanejamentovinculodfd";
        $retorno = executarSQL($this->conexaoDb, $sql);
        $result = 0;
        $retorno->fetchInto($result, DB_FETCHMODE_OBJECT);
        $cpldfdsequ = $result->max + 1;

        return $cpldfdsequ;
    }
    function insertDadosVinculo($sequencialVinculo,$codgrupo,$sequencialDFD,$codUsuario){
        $sql = "
        insert into sfpc.tbplanejamentovinculodfd (
            cplvinsequ,
            cplvincodi,
            cpldfdsequ,
            tplvinincl,
            cusupocodi,
            tplvinulat
        ) values(
            ".$sequencialVinculo.", 
            ".$codgrupo.", 
            ".$sequencialDFD.", 
            now(), 
            $codUsuario, 
            now() 
            )
        ";
        
        $resultadoDFD = executarSQL($this->conexaoDb, $sql);
        return true;
    }
    function consultaCodigoVinculo($sequencial){
        $sql ="select distinct (cplvincodi) from sfpc.tbplanejamentodfd";
        $sql .=" where cpldfdsequ = $sequencial";
        $retorno = executarSQL($this->conexaoDb, $sql);
        $retorno->fetchInto($result, DB_FETCHMODE_OBJECT);
        return $result;
    }
    function consultaCompraCorporativa(){
        $sql ="select corglicodi from sfpc.tbparametrosgerais";
        $retorno = executarSQL($this->conexaoDb, $sql);
        $retorno->fetchInto($result, DB_FETCHMODE_OBJECT);
        return $result;
    }

    function montaHTMLVincular($dadosDFD)
    {
        if(empty($dadosDFD) || $dadosDFD[0]->cplsitcodi == 1){
            $html = '<tr>
                    <td align="center" bgcolor="#75ADE6" colspan="8" class="titulo3" width="900px">
                            RESULTADO DA PESQUISA
                        </td>
                    </tr>
                    <tr>
                        <td align="left" colspan="8" class="textonormal" width="900px">Pesquisa sem Ocorrências.</td>
                    </tr>';
        }else{
            $html='
                        <tr>
                            <td align="center" bgcolor="#75ADE6" colspan="8" class="titulo3">
                                RESULTADO DA PESQUISA
                            </td>
                        </tr>';


            $html.='<tr><td align="center" bgcolor="#BFDAF2" colspan="8" class="titulo3">'.$dadosDFD[0]->descorgao.'</td></tr>';

            $html.='<tr>
                        <td>
                        <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
                        <thead>
                            <tr id="cabecalhos">
                            <td class="tdResultTitulo" id="cabIdDFD">NÚMERO DO DFD</td>
                            <td class="tdResultTitulo" id="cabAno">ANO DO PCA</td>
                            <td class="tdResultTitulo" id="cabCodClasse">CÓDIGO DA CLASSE</td>
                            <td class="tdResultTitulo" id="cabDescClasse">DESCRIÇÃO DA CLASSE</td>
                            <td class="tdResultTitulo" id="cabDataPrevistaConclusao">DATA PRESVISTA PARA CONCLUSÃO</td>
                            <td class="tdResultTitulo" id="cabTpProcesso">TIPO DE PROCESSO</td>
                            <td class="tdResultTitulo" id="cabGrauPrioridade">GRAU DE PRIORIDADE</td>
                            <td class="tdResultTitulo" id="cabSituacao">SITUAÇÃO DO DFD</td>
                            </tr>
                        </thead>
                        <tbody>';


            foreach($dadosDFD as $dado){
                $dataPrevConclusao = date('d/m/Y', strtotime($dado->dpldfdpret));
                $tpProcesso = ($dado->fpldfdtpct=="D")? "CONTRATAÇÃO DIRETA" : "LICITAÇÃO";
                $urlDFD = "ConsDFD.php?dfdSelected=$dado->cpldfdnumf";

                if($dado->fpldfdgrau == 1){
                    $grauprioridade = "ALTO";
                }else if($dado->fpldfdgrau == 2){
                    $grauprioridade = "MÉDIO";
                }else if($dado->fpldfdgrau == 3){
                    $grauprioridade = "BAIXO";
                }

                $cclamscodi = empty($dado->cclamscodi)? "-": $dado->cclamscodi;
                $descclasse = empty($dado->descclasse)? "-": $dado->descclasse;
                $dataPrevConclusao = ($dataPrevConclusao == "01/01/1970")? "-": $dataPrevConclusao;
                $tpProcesso = empty($tpProcesso)? "-": $tpProcesso;
                $grauprioridade = empty($grauprioridade)? "-": $grauprioridade;
                
                $html.='<tr id="resultados">
                        <td class="tdresult" id="resIdDFD"><input type="checkbox" name="sequencialVincularDFD[]" value="'.$dado->cpldfdsequ.'" style="text-transform: capitalize">'.$dado->cpldfdnumf.'</input></td>
                        <td class="tdresult" id="resAno">'.$dado->apldfdanod.'</td>
                        <td class="tdresult" id="resCodClasse">'.$cclamscodi.'</td>
                        <td class="tdresult" id="resDescClasse">'.$descclasse.'</td>
                        <td class="tdresult" id="resDataPrevistaConclusao">'.$dataPrevConclusao.'</td>
                        <td class="tdresult" id="resTpProcesso">'.$tpProcesso.'</td>
                        <td class="tdresult" id="resGrauPrioridade">'.$grauprioridade.'</td>
                        <td class="tdresult" id="resSituacao">'.$dado->eplsitnome.'</td>
                    </tr>';

            }
            $html .='  <tr height = "40px;">
                                <td colspan="8">
                                    <button type="button" name="JanelaVincular" class="botao" id="JanelaVincular";">Vincular</button>
                                </td>
                            </tr>';
            $html .= '</tbody></table></td></tr>';
        }
        return $html;
    }
   
    function montaHTMLVincularManter($dadosDFD)
    {
        $dfdOriginal = $_POST['dfdsequ'];
        if(empty($dadosDFD)){
            $html = '<tr>
                    <td align="center" bgcolor="#75ADE6" colspan="8" class="titulo3" width="900px">
                            RESULTADO DA PESQUISA
                        </td>
                    </tr>
                    <tr>
                        <td align="left" colspan="8" class="textonormal" width="900px">Pesquisa sem Ocorrências.</td>
                    </tr>';
        }else{
            $html='
                        <tr>
                            <td align="center" bgcolor="#75ADE6" colspan="8" class="titulo3">
                                Resultado da pesquisa
                            </td>
                        </tr>';


            $html.='<tr><td align="center" bgcolor="#BFDAF2" colspan="8" class="titulo3">'.$dadosDFD[0]->descorgao.'</td></tr>';

            $html.='<tr>
                        <td>
                        <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
                        <thead>
                            <tr id="cabecalhos">
                                <td class="tdResultTitulo" id="cabIdDFD">NÚMERO DO DFD</td>
                                <td class="tdResultTitulo" id="cabAno">ANO DO PCA</td>
                                <td class="tdResultTitulo" id="cabCodClasse">CÓDIGO DA CLASSE</td>
                                <td class="tdResultTitulo" id="cabDescClasse">DESCRIÇÃO DA CLASSE</td>
                                <td class="tdResultTitulo" id="cabDataPrevistaConclusao">DATA PRESVISTA PARA CONCLUSÃO</td>
                                <td class="tdResultTitulo" id="cabTpProcesso">TIPO DE PROCESSO</td>
                                <td class="tdResultTitulo" id="cabGrauPrioridade">GRAU DE PRIORIDADE</td>
                                <td class="tdResultTitulo" id="cabSituacao">SITUAÇÃO DO DFD</td>
                            </tr>
                        </thead>
                        <tbody>';


            foreach($dadosDFD as $dado){
                if($dado->cpldfdsequ != $dfdOriginal){
                    $dataPrevConclusao = date('d/m/Y', strtotime($dado->dpldfdpret));
                    $tpProcesso = ($dado->fpldfdtpct=="D")? "CONTRATAÇÃO" : "LICITAÇÃO";
                    $urlDFD = "ConsDFD.php?dfdSelected=$dado->cpldfdnumf";

                    if($dado->fpldfdgrau == 1){
                        $grauprioridade = "ALTO";
                    }else if($dado->fpldfdgrau == 2){
                        $grauprioridade = "MÉDIO";
                    }else if($dado->fpldfdgrau == 3){
                        $grauprioridade = "BAIXO";
                    }

                    $html.='<tr id="resultados">
                            <td class="tdresult" id="resIdDFD"><input type="checkbox" name="sequencialVincularDFD[]" value="'.$dado->cpldfdsequ.'" style="text-transform: capitalize">'.$dado->cpldfdnumf.'</input></td>
                            <td class="tdresult" id="resAno">'.$dado->apldfdanod.'</td>
                            <td class="tdresult" id="resCodClasse">'.$dado->cclamscodi.'</td>
                            <td class="tdresult" id="resDescClasse">'.$dado->descclasse.'</td>
                            <td class="tdresult" id="resDataPrevistaConclusao">'.$dataPrevConclusao.'</td>
                            <td class="tdresult" id="resTpProcesso">'.$tpProcesso.'</td>
                            <td class="tdresult" id="resGrauPrioridade">'.$grauprioridade.'</td>
                            <td class="tdresult" id="resSituacao">'.$dado->eplsitnome.'</td>
                        </tr>';
                }

            }
            $html .='  <tr>
                                <td colspan="8">
                                    <button type="button" name="janelaVincular" class="botao" id="janelaVincularManter";">Vincular</button>
                                </td>
                            </tr>';
            $html .= '</tbody></table></td></tr>';
        }
        return $html;
    }

    function consultaDFDcodigoVinculo($sequencial,$codigoVinculo){
        $sql = "SELECT dfd.*, org.eorglidesc as descorgao, org.aorglicnpj as cnpjorgao, classe.eclamsdesc as descclasse, sitdfd.eplsitnome
                FROM sfpc.tbplanejamentodfd as dfd
                inner join sfpc.tborgaolicitante as org on org.corglicodi = dfd.corglicodi
                inner join sfpc.tbplanejamentovinculodfd as vinc on vinc.cpldfdsequ = dfd.cpldfdsequ
                inner join sfpc.tbclassematerialservico as classe on (classe.cclamscodi = dfd.cclamscodi and classe.cgrumscodi = dfd.cgrumscodi) 
                inner join sfpc.tbplanejamentosituacaodfd as sitdfd on sitdfd.cplsitcodi = dfd.cplsitcodi
                where vinc.cplvincodi = '$codigoVinculo'";
        if($sequencial){
            $sql .= "and dfd.cpldfdsequ != '$sequencial'";
        }

        $resultado = executarSql($this->conexaoDb, $sql);
        while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
            $dadosSelectDFD[] = $retorno;
        }
        return $dadosSelectDFD;
    }
    function consultaDFDcodigoVinculoDiferente($sequencial, $codigoVinculo, $areaReq){
        $sql = "SELECT dfd.*, org.eorglidesc as descorgao, org.aorglicnpj as cnpjorgao, classe.eclamsdesc as descclasse, sitdfd.eplsitnome
                FROM sfpc.tbplanejamentodfd as dfd
                inner join sfpc.tborgaolicitante as org on org.corglicodi = dfd.corglicodi
                inner join sfpc.tbclassematerialservico as classe on (classe.cclamscodi = dfd.cclamscodi and classe.cgrumscodi = dfd.cgrumscodi) 
                inner join sfpc.tbplanejamentosituacaodfd as sitdfd on sitdfd.cplsitcodi = dfd.cplsitcodi
                where dfd.cplsitcodi not in (1, 5)";
                if($codigoVinculo->cplvincodi){
                    $sql .= " and vinc.cplvincodi != '$codigoVinculo->cplvincodi'";
                }
                if($areaReq){
                    $sql .= "  and dfd.corglicodi = $areaReq";
                }
        if($sequencial){
                    $sql .= " and dfd.cpldfdsequ != $sequencial";
        }

        $resultado = executarSql($this->conexaoDb, $sql);
        while ($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
                $dadosSelectDFD[] = $retorno;
            }
        return $dadosSelectDFD;
    }

    function getDadosDFDbySequ($dados)
    {
        $dadosSelectDFD = array();
        // fazer dessa fforma vai dar uma sobrecarga na pesquisa, é mais rápido fazer uma sql modulável;
        foreach ($dados as $dado) {
            $sql = "
            SELECT distinct plandfd.*, org.eorglidesc as descorgao, classe.eclamsdesc as descclasse, sitdfd.eplsitnome
            FROM sfpc.tbplanejamentodfd AS plandfd
            inner join sfpc.tborgaolicitante as org on org.corglicodi = plandfd.corglicodi 
            left join sfpc.tbclassematerialservico as classe on (classe.cclamscodi = plandfd.cclamscodi and classe.cgrumscodi = plandfd.cgrumscodi) 
            inner join sfpc.tbplanejamentosituacaodfd as sitdfd on sitdfd.cplsitcodi = plandfd.cplsitcodi
        ";
            $sql .= "where plandfd.cpldfdsequ = $dado";
            $sql .= " ORDER BY cpldfdsequ ASC";

            $resultado = executarSql($this->conexaoDb, $sql);
            $countDFD = 0;
            while ($resultado->fetchInto($countDFD, DB_FETCHMODE_OBJECT)) {
                $dadosSelectDFD[] = $countDFD;
            }
        }

        return $dadosSelectDFD;
    }
    function getSequenciaisOrgao($dados)
    {
        $dadosSelectDFD = array();
        // fazer dessa fforma vai dar uma sobrecarga na pesquisa, é mais rápido fazer uma sql modulável;
        foreach ($dados as $dado) {
            $sql = "
            SELECT distinct plandfd.cpldfdsequ, plandfd.corglicodi
            FROM sfpc.tbplanejamentodfd AS plandfd
            inner join sfpc.tborgaolicitante as org on org.corglicodi = plandfd.corglicodi 
            left join sfpc.tbclassematerialservico as classe on (classe.cclamscodi = plandfd.cclamscodi and classe.cgrumscodi = plandfd.cgrumscodi) 
            inner join sfpc.tbplanejamentosituacaodfd as sitdfd on sitdfd.cplsitcodi = plandfd.cplsitcodi
        ";
            $sql .= "where plandfd.cpldfdsequ = $dado";
            $sql .= " ORDER BY cpldfdsequ ASC";

            $resultado = executarSql($this->conexaoDb, $sql);
            $countDFD = 0;
            while ($resultado->fetchInto($countDFD, DB_FETCHMODE_OBJECT)) {
                $dadosSelectDFD['Orgao'] .= $countDFD->corglicodi.',';
                $dadosSelectDFD['Sequencial'] .= $countDFD->cpldfdsequ.',';
            }
        }

        return $dadosSelectDFD;
    }

    function getDadosValorAgrupar($sequenciais, $orgaos)
    {
        $dadosSelectDFD = array();
        // fazer dessa fforma vai dar uma sobrecarga na pesquisa, é mais rápido fazer uma sql modulável;
        
            $sql = "select sum(cpldfdvest) as totalvalor from sfpc.tbplanejamentodfd ";
            $sql .= " where cpldfdsequ in ($sequenciais)";
            $sql .= " and corglicodi in ($orgaos)";

            $resultado = executarSql($this->conexaoDb, $sql);
            $countDFD = 0;
            while ($resultado->fetchInto($countDFD, DB_FETCHMODE_OBJECT)) {
                $dadosSelectDFD[] = $countDFD;
            }
        

        return $dadosSelectDFD;
    }
    function getDadosDFDbySequVincularManter($dados, $dadosManter)
    {
        $dadosSelectDFD = array();
        // fazer dessa fforma vai dar uma sobrecarga na pesquisa, é mais rápido fazer uma sql modulável;
        foreach ($dados as $dado) {
            $sql = "
            SELECT distinct plandfd.*, org.eorglidesc as descorgao, classe.eclamsdesc as descclasse, sitdfd.eplsitnome
            FROM sfpc.tbplanejamentodfd AS plandfd
            inner join sfpc.tborgaolicitante as org on org.corglicodi = plandfd.corglicodi 
            left join sfpc.tbclassematerialservico as classe on (classe.cclamscodi = plandfd.cclamscodi and classe.cgrumscodi = plandfd.cgrumscodi) 
            inner join sfpc.tbplanejamentosituacaodfd as sitdfd on sitdfd.cplsitcodi = plandfd.cplsitcodi
        ";
            $sql .= "where plandfd.cpldfdsequ = $dado";
            $sql .= " ORDER BY cpldfdsequ ASC";

            $resultado = executarSql($this->conexaoDb, $sql);
            $countDFD = 0;
                while ($resultado->fetchInto($countDFD, DB_FETCHMODE_OBJECT)) {
                    $dadosSelectDFD[] = $countDFD;
                }
                foreach($dadosManter as $dadosVinculo){
                    foreach($dadosSelectDFD as $key=>$countDFD ){
                        if($countDFD->cpldfdsequ == $dadosVinculo->cpldfdsequ){
                            unset($dadosSelectDFD[$key]);
                        }
                    }
                    
                }
           
        }

        return $dadosSelectDFD;
    }

    /**
     * Função utilizada em ConsPesquisarDFD.php, CadManterDFD;
     * Pega todos os dados do DFD;
     */
    public function getDadosDFD($dados)
    {
        // fazer dessa fforma vai dar uma sobrecarga na pesquisa, é mais rápido fazer uma sql modulável;
        $sql = "
            SELECT distinct plandfd.*, org.eorglidesc as descorgao, classe.eclamsdesc as descclasse, sitdfd.eplsitnome
            FROM sfpc.tbplanejamentodfd AS plandfd
            inner join sfpc.tborgaolicitante as org on org.corglicodi = plandfd.corglicodi
            left join sfpc.tbclassematerialservico as classe on (classe.cclamscodi = plandfd.cclamscodi and classe.cgrumscodi = plandfd.cgrumscodi) 
            inner join sfpc.tbplanejamentosituacaodfd as sitdfd on sitdfd.cplsitcodi = plandfd.cplsitcodi
        ";
        
        if($dados['DfdAgrupador'] == 1){
            //Vai trazer tudo que esteja na tabela agrup
            $sql .= " inner join sfpc.tbplanejamentoagrupamentodfd as agrup on (agrup.cpldfdsequ = plandfd.cpldfdsequ)
                    ";
        }else if($dados['DfdAgrupador'] == "2"){
            //Vai trazer tudo que não esteja na tabela agrup mas esteja na tabela plandfd
            $sql .= " left outer join sfpc.tbplanejamentoagrupamentodfd as agrup on (agrup.cpldfdsequ = plandfd.cpldfdsequ)
                    ";
        }

        $sqlWhere = "";
        $checaWhere = false;//se for falso nas verificações precisa incluir o where
        if(!empty($dados['selectAreaReq']) && $dados['selectSitDFD']!='RASCUNHO'){
            if(count($dados['selectAreaReq']) > 1){
                $corglicodi ="";
                $aux = 1;
                for($i=0; $i<count($dados['selectAreaReq']); $i++){
                    $corglicodi .= $dados['selectAreaReq'][$i]->corglicodi;
                    if($aux < count($dados['selectAreaReq'])){
                        $corglicodi .= ", ";
                    }
                    $aux++;
                }
                $sqlWhere .= " where"; // não precisa verificar pois não vem nada antes
                $sqlWhere .= " plandfd.corglicodi in (".$corglicodi.") and";//não pode ter espaço após para garantir na checagem para não faltar where
                $checaWhere = true;
            }else if($dados['selectAreaReq'] != -1){
                $sqlWhere .= " where"; // não precisa verificar pois não vem nada antes
                $sqlWhere .= " plandfd.corglicodi = '".$dados['selectAreaReq']."' and";//não pode ter espaço após para garantir na checagem para não faltar where
                $checaWhere = true;
            }

        }

        //Verifica e implenta se vier o id da DFD
        if(!empty($dados['idDFD'])) {
            if($checaWhere == false){
                $sqlWhere .= " where"; // não recebe a chave pois caso venha não vem mais nada após
            }
            $sqlWhere .=" plandfd.cpldfdnumf = '".$dados['idDFD']."'";
        } else { //Caso não venha as outras informações serão tratadas
            
            if ($dados['DfdAgrupador'] == "2") {
                // Limita o agrupamento a tudo que não esteja agrupado trazendo tudo não nulo que não esteja em agrup
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " agrup.cpldfdsequ IS NULL and";
            }

            if (!empty($dados["cclamscodi"]) && !empty($dados["cgrumscodi"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.cclamscodi = '".$dados['cclamscodi']."' and plandfd.cgrumscodi = '".$dados['cgrumscodi']."' and";
            }
          
            if (!empty($dados["selectAnoPCA"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.apldfdanod = '".$dados['selectAnoPCA']."' and";
            }

            if (!empty($dados["grauPrioridade"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.fpldfdgrau = '".$dados['grauPrioridade']."' and";
            }

            if (!empty($dados["descDemanda"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.epldfddesc ilike '%".$dados['descDemanda']."%' and";
            }
            
            if (!empty($dados["DataIni"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.dpldfdpret >= '".$dados['DataIni']."' and";
            }

            if (!empty($dados["DataFim"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                }
                $sqlWhere .= " plandfd.dpldfdpret <= '".$dados['DataFim']."' and";
            }
            //limpa o ultimo and para não quebrar a query
            $sqlWhere = substr_replace($sqlWhere, ' ', strrpos($sqlWhere, " and"));
            
        }
        $sql .= $sqlWhere;
        if (!empty($dados["selectSitDFD"])) {
            $sql .= " and plandfd.cplsitcodi = '".$dados['selectSitDFD']."'";
        }else{
            $sql .= " and plandfd.cplsitcodi in (1, 2, 4)";
        }
        $sql .= " ORDER BY corglicodi, cpldfdsequ ASC";
        
        $resultado = executarSql($this->conexaoDb, $sql);
        $dadosSelectDFD = array();
        $countDFD = 0;
        
        while ($resultado->fetchInto($countDFD, DB_FETCHMODE_OBJECT)) {
            $dadosSelectDFD[] = $countDFD;
        }
        
        return $dadosSelectDFD;
    }
    public function getDadosDFDAgrupar($ano)
    {
        
        // A query abaixo conta as repetições de classe e grupoMaterial por dfd e conta a quantidade de diferentes orgãos.
        $situacao = trim($situacao);
        $sql = "
        select b.cgrumscodi, b.eclamsdesc, a.cclamscodi,  count(distinct(a.corglicodi)) as qtdcorglicodi from SFPC.TBPLANEJAMENTODFD a,
        sfpc.tbclassematerialservico b";
        $sql.=" where apldfdanod = $ano ";
        $sql .= " and a.cplsitcodi in (3, 6, 7, 9)";
        $sql .= "   and a.cgrumscodi = b.cgrumscodi
                    and a.cclamscodi = b.cclamscodi
                    group by b.eclamsdesc, b.cgrumscodi, a.cclamscodi
                    order by 1";
                    // print_r($sql);die;
        
        $resultado = executarSql($this->conexaoDb, $sql);
        $dadosSelectDFD = array();
        $countDFD = 0;
        
        while ($resultado->fetchInto($countDFD, DB_FETCHMODE_OBJECT)) {
            if($countDFD->qtdcorglicodi > 1){
            $dadosSelectDFD[] = $countDFD;
        }
        
        }
        
        return $dadosSelectDFD;
    }
    public function buscaOrgaosdeDFDsAgrupaveis($cclamscodi, $cgrumscodi){
        $sql = "select distinct(corglicodi) from sfpc.tbplanejamentodfd 
                where cclamscodi = $cclamscodi and cgrumscodi = $cgrumscodi  and cplsitcodi in (3, 6, 7, 9)";
                
        $resultado = executarSql($this->conexaoDb, $sql);
        $contador = 0;
        while ($resultado->fetchInto($countDFD, DB_FETCHMODE_OBJECT)) {
            $contador++;
        }
        return $contador;
    }
    public function getDadosDFDConsulta($dados)
    {
        // fazer dessa fforma vai dar uma sobrecarga na pesquisa, é mais rápido fazer uma sql modulável;
        $sql = "
            SELECT distinct plandfd.*, org.eorglidesc as descorgao, classe.eclamsdesc as descclasse, sitdfd.eplsitnome
            FROM sfpc.tbplanejamentodfd AS plandfd
            inner join sfpc.tborgaolicitante as org on org.corglicodi = plandfd.corglicodi
            left join sfpc.tbclassematerialservico as classe on (classe.cclamscodi = plandfd.cclamscodi and classe.cgrumscodi = plandfd.cgrumscodi) 
            inner join sfpc.tbplanejamentosituacaodfd as sitdfd on sitdfd.cplsitcodi = plandfd.cplsitcodi
        ";
        
        if($dados['DfdAgrupador'] == 1){
            //Vai trazer tudo que esteja na tabela agrup
            $sql .= " inner join sfpc.tbplanejamentoagrupamentodfd as agrup on (agrup.cpldfdsequ = plandfd.cpldfdsequ)
                    ";
        }else if($dados['DfdAgrupador'] == "2"){
            //Vai trazer tudo que não esteja na tabela agrup mas esteja na tabela plandfd
            $sql .= " left outer join sfpc.tbplanejamentoagrupamentodfd as agrup on (agrup.cpldfdsequ = plandfd.cpldfdsequ)
                    ";
        }
        
        $sqlWhere = "";
        $checaWhere = false;//se for falso nas verificações precisa incluir o where
        if(!empty($dados['selectAreaReq'])){
            if(count($dados['selectAreaReq']) > 1){
                $corglicodi ="";
                $aux = 1;
                for($i=0; $i<count($dados['selectAreaReq']); $i++){
                    $corglicodi .= $dados['selectAreaReq'][$i]->corglicodi;
                    if($aux < count($dados['selectAreaReq'])){
                        $corglicodi .= ", ";
                    }
                    $aux++;
                }
                $sqlWhere .= " where"; // não precisa verificar pois não vem nada antes
                $sqlWhere .= " plandfd.corglicodi in (".$corglicodi.") and";//não pode ter espaço após para garantir na checagem para não faltar where
                $checaWhere = true;
            }else{
                $sqlWhere .= " where"; // não precisa verificar pois não vem nada antes
                $sqlWhere .= " plandfd.corglicodi = '".$dados['selectAreaReq']."' and";//não pode ter espaço após para garantir na checagem para não faltar where
                $checaWhere = true;
            }

        }

        //Verifica e implenta se vier o id da DFD
        if(!empty($dados['idDFD'])) {
            if($checaWhere == false){
                $sqlWhere .= " where"; // não recebe a chave pois caso venha não vem mais nada após
            }
            $sqlWhere .=" plandfd.cpldfdnumf = '".$dados['idDFD']."'";
        } else { //Caso não venha as outras informações serão tratadas
            if ($dados['DfdAgrupador'] == "2") {
                // Limita o agrupamento a tudo que não esteja agrupado trazendo tudo não nulo que não esteja em agrup
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " agrup.cpldfdsequ IS NULL and";
            }
            if (!empty($dados["cclamscodi"]) && !empty($dados["cgrumscodi"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.cclamscodi = '".$dados['cclamscodi']."' and plandfd.cgrumscodi = '".$dados['cgrumscodi']."' and";
            }
          
            if (!empty($dados["selectAnoPCA"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.apldfdanod = '".$dados['selectAnoPCA']."' and";
            }

            if (!empty($dados["selectSitDFD"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.cplsitcodi = '".$dados['selectSitDFD']."' and";
            }else{
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
            }

            if (!empty($dados["grauPrioridade"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.fpldfdgrau = '".$dados['grauPrioridade']."' and";
            }

            if (!empty($dados["descDemanda"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.epldfddesc ilike '%".$dados['descDemanda']."%' and";
            }
            
            if (!empty($dados["DataIni"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.dpldfdpret >= '".$dados['DataIni']."' and";
            }

            if (!empty($dados["DataFim"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                }
                $sqlWhere .= " plandfd.dpldfdpret <= '".$dados['DataFim']."' and";
            }
            //limpa o ultimo and para não quebrar a query
            $sqlWhere = substr_replace($sqlWhere, ' ', strrpos($sqlWhere, " and"));
            
        }
        $sql .= $sqlWhere;
        $sql .= " ORDER BY corglicodi, cpldfdsequ ASC";
        $resultado = executarSql($this->conexaoDb, $sql);
        $dadosSelectDFD = array();
        $countDFD = 0;
        
        while ($resultado->fetchInto($countDFD, DB_FETCHMODE_OBJECT)){
            $centroCusto = $this->getCenCustoUsuario($countDFD->corglicodi);
            if($countDFD->cplsitcodi == 1){
                if(!empty($centroCusto)){
                    $dadosSelectDFD[] = $countDFD;
                }
            }else{
                $dadosSelectDFD[] = $countDFD;
            }
            
            
        }
        
        return $dadosSelectDFD;
    }
    /**
     * Função utilizada em PostDadosGerarPCA.php;
     * Busca os dados do ano do PCA para montar o documento.
     */
    public function insertPCAGerado($anoPCA){
        $sqlSeqPCA ="select max(cplpcasequ) from sfpc.tbplanejamentopca";
        $retorno = executarSQL($this->conexaoDb, $sqlSeqPCA);
        $result = 0;
        $retorno->fetchInto($result, DB_FETCHMODE_OBJECT);
        $cplpcasequ = $result->max + 1;

        $sqlSeqPCA ="select max(chipcasequ) from sfpc.tbplanejamentohistoricosituacaopca";
        $retorno = executarSQL($this->conexaoDb, $sqlSeqPCA);
        $result = 0;
        $retorno->fetchInto($result, DB_FETCHMODE_OBJECT);
        $chipcasequ = $result->max + 1;

        $sqlSeqPCAAno ="select max(cplpcacodi) from sfpc.tbplanejamentopca where aplpcaanop = $anoPCA";
        $retorno = executarSQL($this->conexaoDb, $sqlSeqPCAAno);
        $result = 0;
        $retorno->fetchInto($result, DB_FETCHMODE_OBJECT);
        $cplpcacodi = $result->max + 1;

        $sqlPCAGerado = "
            insert into sfpc.tbplanejamentopca (
                cplpcasequ,
                aplpcaanop,
                cplpcacodi,
                tplpcagera,
                tplpcaenca,
                tplpcacria,
                cusupocodi,
                tplpcaulat
            ) values (
                $cplpcasequ,
                $anoPCA,
                $cplpcacodi,
                now(),
                now(),
                now(),
                ".$_SESSION['_cusupocodi_'].",
                now()
            )
            ";
        $retorno = executarSQL($this->conexaoDb, $sqlPCAGerado);

        $sqlPCAGeradoHistSit ="
            insert into sfpc.tbplanejamentohistoricosituacaopca (
                chipcasequ,
                cplpcasequ,
                csitpccodi,
                thipcaincl,
                cusupocodi,
                thipcaulat
            ) values (
                $chipcasequ,
                $cplpcasequ,
                1,
                now(),
                ".$_SESSION['_cusupocodi_'].",
                now()
            )
            ";
        $retorno = executarSQL($this->conexaoDb, $sqlPCAGeradoHistSit);
        
        return;
    }
    /**
     * Função utilizada em PostDadosGerarPCA.php;
     * Busca os dados do ano do PCA para montar o documento.
     */
    public function getDadosDFDGeraPCACompleto($ano)
    {
        // fazer dessa fforma vai dar uma sobrecarga na pesquisa, é mais rápido fazer uma sql modulável;
        $sql = "
            SELECT distinct plandfd.*, org.eorglidesc as descorgao, ORG.aorglicnpj, classe.eclamsdesc as descclasse, sitdfd.eplsitnome
            FROM sfpc.tbplanejamentodfd AS plandfd
            inner join sfpc.tborgaolicitante as org on org.corglicodi = plandfd.corglicodi
            left join sfpc.tbclassematerialservico as classe on (classe.cclamscodi = plandfd.cclamscodi and classe.cgrumscodi = plandfd.cgrumscodi) 
            inner join sfpc.tbplanejamentosituacaodfd as sitdfd on sitdfd.cplsitcodi = plandfd.cplsitcodi
            where plandfd.cplsitcodi in (8, 9, 10) and plandfd.apldfdanod = $ano
            ORDER BY corglicodi, cpldfdsequ ASC
        ";

        //Verificar com Rossana o código da situação "Em execução"
        
        $resultado = executarSql($this->conexaoDb, $sql);
        $dadosSelectDFD = array();
        $countDFD = 0;
        
        while ($resultado->fetchInto($countDFD, DB_FETCHMODE_OBJECT)){
            $dadosSelectDFD[] = $countDFD;
        }
        
        return $dadosSelectDFD;
    }

    // Usado em GerarPCA.php
    //bUSCA O TIPO DE DESPESA E RETORNA PRONTO PARA ADICIONAR A PLANILHA
    public function DFDGeraPCADespesa($anoDFD, $cgrumscodi){
        $sql = "select cgruseele1 from sfpc.tbgruposubelementodespesa where agruseanoi = $anoDFD and cgrumscodi = $cgrumscodi";
        $resultado = executarSql($this->conexaoDb, $sql);
        $resultado->fetchInto($result, DB_FETCHMODE_OBJECT);
        $despesa = ($result->cgruseele1 == 3)? "CUSTEIO":"INVESTIMENTOS";
        return $despesa;
    }
    // Usado em GerarPCA.php
    //BUSCA O TIPO (mATERIAL OU SERVIÇO) E RETORNA PARA ADICIONAR A PLANILHA
    public function DFDGeraPCATipoMS($cgrumscodi){
        $sql = "select fgrumstipo from sfpc.tbgrupomaterialservico where cgrumscodi = $cgrumscodi";
        $resultado = executarSql($this->conexaoDb, $sql);
        $resultado->fetchInto($result, DB_FETCHMODE_OBJECT);
        $tipoMS = ($result->fgrumstipo == "M")? "MATERIAL":"SERVIÇO";
        return $tipoMS;
    }
    // Usado em GerarPCA.php
    //Busca e Organiza os vinculos do DFD para dispor naplanilha de Gerar PCA
    public function DFDSVinculadasGERAPCA($codVinculo){
        $sql = "select cpldfdnumf  from sfpc.tbplanejamentodfd as dfd
        inner join sfpc.tbplanejamentovinculodfd as vinc on (dfd.cpldfdsequ = vinc.cpldfdsequ)
        where  vinc.cplvincodi = $codVinculo";
        $resultado = executarSql($this->conexaoDb, $sql);
        $countDFD = 0;
        $dfds = "";
        while ($resultado->fetchInto($countDFD, DB_FETCHMODE_OBJECT)){
            $dfds .= "$countDFD->cpldfdnumf, ";
        }
        $dfds = substr_replace($dfds, "", strrpos($dfds, ", "));
        return $dfds;
    }
    // Usado em GerarPCA.php
    //Busca e organiza os itens para dispor na planilha de PCA
    public function DFDSItensGERAPCA($sequ){
        $sql = "SELECT * FROM sfpc.tbitemplanejamentodfd where cpldfdsequ = $sequ";
        $resultado = executarSql($this->conexaoDb, $sql);
        $dadosItens = array();
        while ($resultado->fetchInto($data, DB_FETCHMODE_OBJECT)){
            $dadosItens[] = $data;
        }
        if(!empty($dadosItens[0])){ // Como a posição de um array é considerada !empty mesmoq ue  dado seja nulo dentro dela eu vejo se o campo especifico não está vazio
            $itens = "";
            foreach($dadosItens as $item){
                if(!empty($item->cmatepsequ)){
                    $sqlI = "SELECT ematepcomp FROM sfpc.tbmaterialportal where cmatepsequ = $item->cmatepsequ";
                    $resultadoI = executarSql($this->conexaoDb, $sqlI);
                    $resultadoI->fetchInto($resultI, DB_FETCHMODE_OBJECT);
                    $itens .= "$resultI->ematepcomp, ";
                }
                if(!empty($item->cservpsequ)){
                    $sqlS = "SELECT eservpdesc FROM sfpc.tbservicoportal where cservpsequ = $item->cservpsequ";
                    $resultadoS = executarSql($this->conexaoDb, $sqlS);
                    $resultadoS->fetchInto($resultS, DB_FETCHMODE_OBJECT);
                    $itens .= "$resultS->eservpdesc, ";
                }
            }
            $itens = substr_replace($itens, "", strrpos($itens, ", "));
            return $itens;
        }else{
            return false;
        }
    }
    /**
     * Função utilizada em PostDadosGerarPCA.php;
     * Busca os dados do ano do PCA para montar o documento.
     */
    public function getDadosDFDGeraPCAAprovacao($ano)
    {
        // fazer dessa fforma vai dar uma sobrecarga na pesquisa, é mais rápido fazer uma sql modulável;
        $sql = "
            SELECT distinct plandfd.*, org.eorglidesc as descorgao, classe.eclamsdesc as descclasse, sitdfd.eplsitnome
            FROM sfpc.tbplanejamentodfd AS plandfd
            inner join sfpc.tborgaolicitante as org on org.corglicodi = plandfd.corglicodi
            left join sfpc.tbclassematerialservico as classe on (classe.cclamscodi = plandfd.cclamscodi and classe.cgrumscodi = plandfd.cgrumscodi) 
            inner join sfpc.tbplanejamentosituacaodfd as sitdfd on sitdfd.cplsitcodi = plandfd.cplsitcodi
            where plandfd.cplsitcodi in (8, 9, 10) and plandfd.apldfdanod = $ano
            ORDER BY corglicodi, cpldfdsequ ASC
        ";

        //Verificar com Rossana o código da situação "Em execução"
        
        $resultado = executarSql($this->conexaoDb, $sql);
        $dadosSelectDFD = array();
        $countDFD = 0;
        
        while ($resultado->fetchInto($countDFD, DB_FETCHMODE_OBJECT)){
            $dadosSelectDFD[] = $countDFD;
        }
        
        return $dadosSelectDFD;
    }
    /**
     * Função utilizada em PostDadosGerarPCA.php;
     * Busca os dados do ano do PCA para montar o documento.
     */
    public function getDadosDFDGeraPCAPublicacao($ano)
    {
        // fazer dessa fforma vai dar uma sobrecarga na pesquisa, é mais rápido fazer uma sql modulável;
        $sql = "
            SELECT distinct plandfd.*, org.eorglidesc as descorgao, classe.eclamsdesc as descclasse, sitdfd.eplsitnome
            FROM sfpc.tbplanejamentodfd AS plandfd
            inner join sfpc.tborgaolicitante as org on org.corglicodi = plandfd.corglicodi
            left join sfpc.tbclassematerialservico as classe on (classe.cclamscodi = plandfd.cclamscodi and classe.cgrumscodi = plandfd.cgrumscodi) 
            inner join sfpc.tbplanejamentosituacaodfd as sitdfd on sitdfd.cplsitcodi = plandfd.cplsitcodi
            where plandfd.cplsitcodi in (8, 9, 10) and plandfd.apldfdanod = $ano
            ORDER BY corglicodi, cpldfdsequ ASC
        ";

        //Verificar com Rossana o código da situação "Em execução"
        
        $resultado = executarSql($this->conexaoDb, $sql);
        $dadosSelectDFD = array();
        $countDFD = 0;
        
        while ($resultado->fetchInto($countDFD, DB_FETCHMODE_OBJECT)){
            $dadosSelectDFD[] = $countDFD;
        }
        
        return $dadosSelectDFD;
    }

    public function getDadosDFDEncaminhar($dados)
    {
        // fazer dessa fforma vai dar uma sobrecarga na pesquisa, é mais rápido fazer uma sql modulável;
        $sql = "
            SELECT distinct plandfd.*, org.eorglidesc as descorgao, classe.eclamsdesc as descclasse, sitdfd.eplsitnome
            FROM sfpc.tbplanejamentodfd AS plandfd
            inner join sfpc.tborgaolicitante as org on org.corglicodi = plandfd.corglicodi
            left join sfpc.tbclassematerialservico as classe on (classe.cclamscodi = plandfd.cclamscodi and classe.cgrumscodi = plandfd.cgrumscodi) 
            inner join sfpc.tbplanejamentosituacaodfd as sitdfd on sitdfd.cplsitcodi = plandfd.cplsitcodi
        ";
        
        $sqlWhere = "";
        $checaWhere = false;//se for falso nas verificações precisa incluir o where
        if(!empty($dados['selectAreaReq'])){
            if(count($dados['selectAreaReq']) > 1){
                $corglicodi ="";
                $aux = 1;
                for($i=0; $i<count($dados['selectAreaReq']); $i++){
                    $corglicodi .= $dados['selectAreaReq'][$i]->corglicodi;
                    if($aux < count($dados['selectAreaReq'])){
                        $corglicodi .= ", ";
                    }
                    $aux++;
                }
                $sqlWhere .= " where"; // não precisa verificar pois não vem nada antes
                $sqlWhere .= " plandfd.corglicodi in (".$corglicodi.") and";//não pode ter espaço após para garantir na checagem para não faltar where
                $checaWhere = true;
            }else if($dados['selectAreaReq']){
                $sqlWhere .= " where"; // não precisa verificar pois não vem nada antes
                $sqlWhere .= " plandfd.corglicodi = '".$dados['selectAreaReq']."' and";//não pode ter espaço após para garantir na checagem para não faltar where
                $checaWhere = true;
            }

        }

        //Verifica e implenta se vier o id da DFD
        if(!empty($dados['idDFD'])) {
            if($checaWhere == false){
                $sqlWhere .= " where"; // não recebe a chave pois caso venha não vem mais nada após
            }
            $sqlWhere .=" plandfd.cpldfdnumf = '".$dados['idDFD']."'";
        } else { //Caso não venha as outras informações serão tratadas
            if (!empty($dados["cclamscodi"]) && !empty($dados["cgrumscodi"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.cclamscodi = '".$dados['cclamscodi']."' and plandfd.cgrumscodi = '".$dados['cgrumscodi']."' and";
            }
          
            if (!empty($dados["selectAnoPCA"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.apldfdanod = '".$dados['selectAnoPCA']."' and";
            }

            if (!empty($dados["selectSitDFD"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.cplsitcodi = '".$dados['selectSitDFD']."' and";
            }else{
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.cplsitcodi in (1, 2, 7) and";
            }

            if (!empty($dados["grauPrioridade"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.fpldfdgrau = '".$dados['grauPrioridade']."' and";
            }

            if (!empty($dados["descDemanda"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.epldfddesc ilike '%".$dados['descDemanda']."%' and";
            }
            
            if (!empty($dados["DataIni"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.dpldfdpret >= '".$dados['DataIni']."' and";
            }

            if (!empty($dados["DataFim"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                }
                $sqlWhere .= " plandfd.dpldfdpret <= '".$dados['DataFim']."' and";
            }
            $sqlWhere .= " plandfd.cplsitcodi = 2 and";
            //limpa o ultimo and para não quebrar a query
            $sqlWhere = substr_replace($sqlWhere, ' ', strrpos($sqlWhere, " and"));
            
        }
        $sql .= $sqlWhere;
        $sql .= " ORDER BY corglicodi, cpldfdsequ ASC";
        $resultado = executarSql($this->conexaoDb, $sql);
        $dadosSelectDFD = array();
        $countDFD = 0;
        
        while ($resultado->fetchInto($countDFD, DB_FETCHMODE_OBJECT)) {
            $centroCusto = $this->getCenCustoUsuario($countDFD->corglicodi);
       
            if(!empty($centroCusto)){
                $dadosSelectDFD[] = $countDFD;     
            }
            
        }
        
        return $dadosSelectDFD;
    }
    public function getDadosDFDRealizarAgrupamento($codigoGrupo, $codigoClasse)
    {
        // fazer dessa fforma vai dar uma sobrecarga na pesquisa, é mais rápido fazer uma sql modulável;
        $sql = "
            SELECT distinct plandfd.*, org.eorglidesc as descorgao, classe.eclamsdesc as descclasse, sitdfd.eplsitnome
            FROM sfpc.tbplanejamentodfd AS plandfd
            inner join sfpc.tborgaolicitante as org on org.corglicodi = plandfd.corglicodi
            left join sfpc.tbclassematerialservico as classe on  (classe.cgrumscodi = plandfd.cgrumscodi and classe.cclamscodi = plandfd.cclamscodi)
            inner join sfpc.tbplanejamentosituacaodfd as sitdfd on sitdfd.cplsitcodi = plandfd.cplsitcodi ";  
        $sql .= " where classe.cgrumscodi = $codigoGrupo";
        $sql .= " and classe.cclamscodi = $codigoClasse";
        $sql .= " and plandfd.cplsitcodi in (3, 6, 7, 9)";
        $sql .= " ORDER BY corglicodi, cpldfdsequ ASC";
        $resultado = executarSql($this->conexaoDb, $sql);
        $dadosSelectDFD = array();
        $countDFD = 0;
        
        while ($resultado->fetchInto($countDFD, DB_FETCHMODE_OBJECT)) {
                $dadosSelectDFD[] = $countDFD;             
        }
        
        return $dadosSelectDFD;
    }
    public function getDadosDFDVincular($dados)
    {   
        // fazer dessa fforma vai dar uma sobrecarga na pesquisa, é mais rápido fazer uma sql modulável;
        $sql = "
            SELECT distinct plandfd.*, org.eorglidesc as descorgao, classe.eclamsdesc as descclasse, sitdfd.eplsitnome
            FROM sfpc.tbplanejamentodfd AS plandfd
            inner join sfpc.tborgaolicitante as org on org.corglicodi = plandfd.corglicodi
            left join sfpc.tbclassematerialservico as classe on (classe.cclamscodi = plandfd.cclamscodi and classe.cgrumscodi = plandfd.cgrumscodi) 
            inner join sfpc.tbplanejamentosituacaodfd as sitdfd on sitdfd.cplsitcodi = plandfd.cplsitcodi
        ";
        
        $sqlWhere = "";
        $checaWhere = false;//se for falso nas verificações precisa incluir o where
        if(!empty($dados['selectAreaReq'])){
            if(count($dados['selectAreaReq']) > 1){
                $corglicodi ="";
                $aux = 1;
                for($i=0; $i<count($dados['selectAreaReq']); $i++){
                    $corglicodi .= $dados['selectAreaReq'][$i]->corglicodi;
                    if($aux < count($dados['selectAreaReq'])){
                        $corglicodi .= ", ";
                    }
                    $aux++;
                }
                $sqlWhere .= " where"; // não precisa verificar pois não vem nada antes
                $sqlWhere .= " plandfd.corglicodi in (".$corglicodi.") and";//não pode ter espaço após para garantir na checagem para não faltar where
                $checaWhere = true;
            }else{
                $sqlWhere .= " where"; // não precisa verificar pois não vem nada antes
                $sqlWhere .= " plandfd.corglicodi = '".$dados['selectAreaReq']."' and";//não pode ter espaço após para garantir na checagem para não faltar where
                $checaWhere = true;
            }

        }

        //Verifica e implenta se vier o id da DFD
        if(!empty($dados['idDFD'])) {
            if($checaWhere == false){
                $sqlWhere .= " where"; // não recebe a chave pois caso venha não vem mais nada após
            }
            $sqlWhere .=" plandfd.cpldfdnumf = '".$dados['idDFD']."'";
        } else { //Caso não venha as outras informações serão tratadas
            if (!empty($dados["cclamscodi"]) && !empty($dados["cgrumscodi"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.cclamscodi = '".$dados['cclamscodi']."' and plandfd.cgrumscodi = '".$dados['cgrumscodi']."' and";
            }
          
            if (!empty($dados["selectAnoPCA"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.apldfdanod = '".$dados['selectAnoPCA']."' and";
            }

            // if (!empty($dados["selectSitDFD"])) {
            //     if($checaWhere == false){
            //         $sqlWhere .= " where";
            //         $checaWhere = true;
            //     }
            //     $sqlWhere .= " plandfd.cplsitcodi = '".$dados['selectSitDFD']."' and";
            // }else{
            //     if($checaWhere == false){
            //         $sqlWhere .= " where";
            //         $checaWhere = true;
            //     }
            // }

            if (!empty($dados["grauPrioridade"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.fpldfdgrau = '".$dados['grauPrioridade']."' and";
            }

            if (!empty($dados["descDemanda"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.epldfddesc ilike '%".$dados['descDemanda']."%' and";
            }
            
            if (!empty($dados["DataIni"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.dpldfdpret >= '".$dados['DataIni']."' and";
            }

            if (!empty($dados["DataFim"])) {
                if($checaWhere == false){
                    $sqlWhere .= " where";
                }
                $sqlWhere .= " plandfd.dpldfdpret <= '".$dados['DataFim']."' and";
            }
            //limpa o ultimo and para não quebrar a query
            $sqlWhere = substr_replace($sqlWhere, ' ', strrpos($sqlWhere, " and"));
            
        }
        $sql .= $sqlWhere;
        if($sequencial){
            $sql .= " and plandfd.cpldfdsequ != $sequencial";
        }
        
        if(!empty($dados["selectSitDFD"])){
            if(count($dados["selectSitDFD"]) == 1){
                $sql.=" and plandfd.cplsitcodi = ".$dados["selectSitDFD"][0];
            }else{
                $strSit = "";
                for($i=0; $i<count($dados["selectSitDFD"]); $i++){
                    $strSit .= $dados["selectSitDFD"][$i].', ';
                }
                $strSit = substr_replace($strSit, '', strrpos($strSit, ","));
                $sql.=" and plandfd.cplsitcodi in ($strSit) ";
            }
        }else{
            $sql.=" and plandfd.cplsitcodi not in (1, 5) ";
        }
        
        $sql .= " ORDER BY corglicodi, cpldfdsequ ASC";
        
        $resultado = executarSql($this->conexaoDb, $sql);
        $dadosSelectDFD = array();
        $countDFD = 0;
        
        while ($resultado->fetchInto($countDFD, DB_FETCHMODE_OBJECT)) {
            if($countDFD->cpldfdnumf != $dados['idDFD']){
                $dadosSelectDFD[] = $countDFD;
            }
        }
        
        return $dadosSelectDFD;
    }
    /**
     * Função utilizada em ConsPesquisarDFD.php, 
     * Pega todos os dados do DFD encontrados e monta o sql para a página;
     */
    public function montaHTML($dadosDFD)
    {   
        $aux = 0;
        $secretariasDFD = array();
        $posArray = 0;
        for($i=0; $i<count($dadosDFD); $i++){
            if($dadosDFD[$i]->corglicodi != $aux){ // Assume que as secretarias vem agrupadas
                $aux = $dadosDFD[$i]->corglicodi;
                $secretariasDFD[$posArray]->corglicodi = $dadosDFD[$i]->corglicodi;
                $secretariasDFD[$posArray]->eorglidesc = $dadosDFD[$i]->descorgao;
                $posArray++;
            }
        }
        if(empty($dadosDFD)){
            $html = '<tr>
                    <td align="center" bgcolor="#75ADE6" colspan="8" class="titulo3" width="900px">
                            RESULTADO DA PESQUISA
                        </td>
                    </tr>
                    <tr>
                        <td align="left" colspan="8" class="textonormal" width="900px">Pesquisa sem Ocorrências.</td>
                    </tr>';
        }else{
            $html='
                    <tr>
                        <td align="center" bgcolor="#75ADE6" colspan="8" class="titulo3">
                            Resultado da pesquisa
                        </td>
                    </tr>';
            foreach($secretariasDFD as $secretaria){
            
                $html.='<tr><td align="center" bgcolor="#BFDAF2" colspan="8" class="titulo3">'.$secretaria->eorglidesc.'</td></tr>';
            
                $html.='<tr>
                    <td>
                    <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
                    <thead>
                        <tr id="cabecalhos">
                                    <td width="130px" class="tdResultTitulo"  id="cabIdDFD">Número do DFD</td>
                                    <td width="55px"class="tdResultTitulo" id="cabAno">Ano do PCA</td>
                                    <td width="55px" class="tdResultTitulo" id="cabCodClasse">Código da Classe</td>
                                    <td width="290px" class="tdResultTitulo" id="cabDescClasse">Descrição da Classe</td>
                                    <td width="108px" class="tdResultTitulo" id="cabDataPrevistaConclusao">Data Prevista para Conclusão</td>
                                    <td width="114px" class="tdResultTitulo" id="cabTpProcesso">Tipo de Processo</td>
                                    <td width="75px" class="tdResultTitulo" id="cabGrauPrioridade">Grau de Prioridade</td>
                                    <td width="130px" class="tdResultTitulo" id="cabSituacao">Situação do DFD</td>
                        </tr>
                    </thead>
                    <tbody>';

                    
                foreach($dadosDFD as $dado){
                    if($secretaria->corglicodi == $dado->corglicodi){
                        $dataPrevConclusao = date('d/m/Y', strtotime($dado->dpldfdpret));
                        $tpProcesso = ($dado->fpldfdtpct=="D")? "Contratação Direta" : "Licitação";
                        $urlDFD = "ConsDFD.php?dfdSelected=$dado->cpldfdnumf";

                        if($dado->fpldfdgrau == 1){
                            $grauprioridade = "ALTO";
                        }else if($dado->fpldfdgrau == 2){
                            $grauprioridade = "MÉDIO";
                        }else if($dado->fpldfdgrau == 3){
                            $grauprioridade = "BAIXO";
                        }

                        
                        $html.='<tr id="resultados">
                                        <td width="130px"  class="tdresult" id="resIdDFD"><a href="'.$urlDFD.'" style="text-transform: capitalize">'.$dado->cpldfdnumf.'</a></td>
                                        <td width="55px" class="tdresult" id="resAno">'.$dado->apldfdanod.'</td>
                                        <td width="55px" class="tdresult" id="resCodClasse">'.$dado->cclamscodi.'</td>
                                        <td width="290px" class="tdresult" id="resDescClasse">'.$dado->descclasse.'</td>
                                        <td width="108px" class="tdresult" id="resDataPrevistaConclusao">'.$dataPrevConclusao.'</td>
                                        <td width="114px" class="tdresult" id="resTpProcesso">'.mb_strtoupper($tpProcesso).'</td>
                                        <td width="75px" class="tdresult" id="resGrauPrioridade">'.$grauprioridade.'</td>
                                        <td width="130px" class="tdresult" id="resSituacao">'.$dado->eplsitnome.'</td>
                        </tr>';
                    }
                
                }
                $html .= '</tbody></table></td></tr>';
            }

        }
        return $html;
    }
    public function montaHTMLAgrupar($dadosDFD)
    {
        
        if (empty($dadosDFD)) {
            $html = '<tr>
                        <td align="center" bgcolor="#75ADE6" colspan="8" class="titulo3" width="900px">
                            RESULTADO DA PESQUISA
                        </td>
                    </tr>
                    <tr>
                        <td align="left" colspan="8" class="textonormal" width="900px"><b>Não há DFDs para serem agrupados.</td>
                    </tr>';
        } else {
            $html='
                    <tr>
                        <td align="center" bgcolor="#75ADE6"  width="820px" colspan="8" class="titulo3"">
                            RESULTADO DA PESQUISA
                        </td>
                    </tr>';
            $html.='<tr>
                        <td>
                        <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
                        <thead>
                            <tr id="cabecalhos">
                                <td class="tdresult" width="50px"></td>               
                                <td width="320px" class="tdResultTitulo" id="cabDescClasse">DESCRIÇÃO DA CLASSE</td>
                                <td width="100px" class="tdResultTitulo" id="cabAno">QUANTIDADE ÁREA REQUISITANTE</td>
        
                            </tr>
                        </thead>
                        <tbody>';

            $conta = 0;
            foreach ($dadosDFD as $dado) {
                $quantOrg = $this->buscaOrgaosdeDFDsAgrupaveis($dado->cclamscodi, $dado->cgrumscodi);
                $html.='<tr id="resultados">
                        <td class="tdresult"><input type="radio" class="CBXNumDFD" name="radioPesquAgrupar" value="'.$dado->cgrumscodi.'&CodigoClasse='.$dado->cclamscodi.'"/></td>
                        <td class="tdresult" id="resDescClasse" style="text-transform:uppercase;">'.$dado->eclamsdesc.'</td>
                        <td class="tdresult" id="resAno">'.$quantOrg.'</td>';
                        // <td class="tdresult" id="resAno">'.$dado->qtdcorglicodi.'</td>';
                $conta ++;
            }
            $html .= '</tbody></table>
                        <footer>
                            <button type="button" name="Exportar" class="botao" id="Exportar">Selecionar</button>
        
                        </footer></td></tr>';
        }
        return $html;
    }

    /**
     * Função utilizada em ConsPesquisarDFD.php, 
     * Pega todos os dados do DFD encontrados e monta o sql para a página;
     */
    /**
     * Função utilizada em ConsPesquisarDFD.php, 
     * Pega todos os dados do DFD encontrados e monta o sql para a página;
     */
    public function montaHTMLConsulta($dadosDFD)
    {   
        $aux = 0;
        $secretariasDFD = array();
        $posArray = 0;
        for($i=0; $i<count($dadosDFD); $i++){
            if($dadosDFD[$i]->corglicodi != $aux){ // Assume que as secretarias vem agrupadas
                $aux = $dadosDFD[$i]->corglicodi;
                $secretariasDFD[$posArray]->corglicodi = $dadosDFD[$i]->corglicodi;
                $secretariasDFD[$posArray]->eorglidesc = $dadosDFD[$i]->descorgao;
                $posArray++;
            }
        }
        if(empty($dadosDFD)){
            $html = '<tr>
                    <td align="center" bgcolor="#75ADE6" colspan="8" class="titulo3" width="900px">
                            RESULTADO DA PESQUISA
                        </td>
                    </tr>
                    <tr>
                        <td align="center" colspan="8" class="textonormal" width="900px"><b>Pesquisa sem Ocorrências.</b></td>
                    </tr>';
        }else{
                $html='
                        <tr>
                            <td align="center" bgcolor="#75ADE6" colspan="8" style="text-transform:uppercase;" class="titulo3">
                                Resultado da pesquisa
                            </td>
                        </tr>';
                foreach($secretariasDFD as $secretaria){
                    
                    $html.='<tr><td align="center" bgcolor="#BFDAF2" colspan="8" class="titulo3">'.$secretaria->eorglidesc.'</td></tr>';
                
                    $html.='<tr>
                            <td>
                            <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
                            <thead>
                                <tr id="cabecalhos">
                                    <td width="130px" class="tdResultTitulo" style="text-transform:uppercase;" id="cabIdDFD">Número do DFD</td>
                                    <td width="55px"class="tdResultTitulo" style="text-transform:uppercase;" id="cabAno">Ano do PCA</td>
                                    <td width="290px" class="tdResultTitulo" style="text-transform:uppercase;" id="cabDescClasse">Descrição da Classe</td>
                                    <td width="108px" class="tdResultTitulo" style="text-transform:uppercase;" id="cabDataPrevistaConclusao">Data Prevista para Conclusão</td>
                                    <td width="114px" class="tdResultTitulo" style="text-transform:uppercase;" id="cabTpProcesso">Tipo de Processo</td>
                                    <td width="75px" class="tdResultTitulo" style="text-transform:uppercase;" id="cabGrauPrioridade">Grau de Prioridade</td>
                                    <td width="130px" class="tdResultTitulo" style="text-transform:uppercase;" id="cabSituacao">Situação do DFD</td>
                                </tr>
                            </thead>
                            <tbody>';
    
                    
                    foreach($dadosDFD as $dado){
                        if($secretaria->corglicodi == $dado->corglicodi){
                            if(!is_null($dado->fpldfdtpct) && !empty($dado->fpldfdtpct)){
                                $tpProcesso = ($dado->fpldfdtpct=="D")? "CONTRATAÇÃO DIRETA" : "LICITAÇÃO";
                            }else{
                                $tpProcesso = "-";
                            }
                            
                            $urlDFD = "ConsDFD.php?dfdSelected=$dado->cpldfdnumf";
        
                            if($dado->fpldfdgrau == 1){
                                $grauprioridade = "ALTO";
                            }else if($dado->fpldfdgrau == 2){
                                $grauprioridade = "MÉDIO";
                            }else if($dado->fpldfdgrau == 3){
                                $grauprioridade = "BAIXO";
                            }else{
                                $grauprioridade = "-";
                            }
                            $cclamscodi = empty($dado->cclamscodi)? "-": $dado->cclamscodi;
                            $descclasse = empty($dado->descclasse)? "-": $dado->descclasse;

                            if(!is_null($dado->dpldfdpret)){
                                $dataPrevConclusao = date('d/m/Y', strtotime($dado->dpldfdpret));
                            }else{
                                $dataPrevConclusao = null;
                            }

                            $dataPrevConclusao = ($dataPrevConclusao == null)? "-": $dataPrevConclusao;
                            $tpProcesso = empty($tpProcesso)? "-": $tpProcesso;
                            // $grauprioridade = empty($grauprioridade)? "-": $grauprioridade;
                            
                            $html.='<tr id="resultados">
                                        <td width="130px"  class="tdresult" id="resIdDFD"><a href="'.$urlDFD.'" style="text-transform: capitalize">'.$dado->cpldfdnumf.'</a></td>
                                        <td width="55px" class="tdresult" id="resAno">'.$dado->apldfdanod.'</td>
                                        <td width="290px" class="tdresult" id="resDescClasse">'.$descclasse.'</td>
                                        <td width="108px" class="tdresult" id="resDataPrevistaConclusao">'.$dataPrevConclusao.'</td>
                                        <td width="114px" class="tdresult" id="resTpProcesso">'.$tpProcesso.'</td>
                                        <td width="75px" class="tdresult" id="resGrauPrioridade">'.$grauprioridade.'</td>
                                        <td width="130px" class="tdresult" id="resSituacao">'.$dado->eplsitnome.'</td>
                            </tr>';
                        }
                        
                    }
                    $html .= '</tbody></table></td></tr>';
                }
               
        }
        return $html;
    }
    /**
     * Função utilizada em ConsPesquisarDFD.php, 
     * Pega todos os dados do DFD encontrados e monta o sql para a página;
     */
    public function montaHTMLConsultaPDF($dadosDFD)
    {   
        date_default_timezone_set ("America/Recife");
        $hoje = date('d/m/Y H:i');
        $aux = 0;
        $secretariasDFD = array();
        $posArray = 0;
        for($i=0; $i<count($dadosDFD); $i++){
            if($dadosDFD[$i]->corglicodi != $aux){ // Assume que as secretarias vem agrupadas
                $aux = $dadosDFD[$i]->corglicodi;
                $secretariasDFD[$posArray]->corglicodi = $dadosDFD[$i]->corglicodi;
                $secretariasDFD[$posArray]->eorglidesc = $dadosDFD[$i]->descorgao;
                $posArray++;
            }
        }
        if(empty($dadosDFD)){
            $html = '<style>
                        .pagenum:before {content: counter(page);}
                        footer .pagenum:before {content: counter(page);}
                        td{
                            font-family: Verdana,sans-serif,Arial;
                        }
                        .font{
                                font-family: Verdana,sans-serif,Arial;
                            
                        }
                        .tdResultTitulo{
                            font-size: 10pt;
                            text-transform: uppercase;
                        }
                        .tdresult{
                            font-size: 8pt;
                        }
                    </style>
                    <div>
                        <table>
                            <tr>
                                <td class ="font"width="475px">Prefeitura do Recife</td>
                                <td align-content="center"><img src="../midia/brasao.jpg" alt=""></td>
                                <td align="right" class="font" width="475px">Portal de Compras</td>
                            </tr>
                        </table>
                    </div>
                    <hr size="3">
                    <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
                    <tbody><tr>
                        <td align="center" colspan="8" class="titulo3" width="900px">Pesquisa sem ocorrências.</td>
                    </tr></tbody></table>';
            $html .= '
                    <footer>
                    <hr size="3">
                    <table width="100%" >
                    <tr>
                    <td width="50%">Emissão: '.$hoje.'</td>
                    <td>
                    <div width="50%" align="right" class="pagenum-container">Página <span class="pagenum"></span></div>
                    </td>
                    </tr>
                    </table>
                    </footer>';
        }else{                       
            $html ='<style>
                        .pagenum:before {content: counter(page);}
                        footer .pagenum:before {content: counter(page);}
                        .tdResultTitulo{
                            font-family: Verdana,sans-serif,Arial;
                            font-size: 8pt;
                        }
                        .tdResultTituloOrg{
                            font-family: Verdana,sans-serif,Arial;
                            font-size: 13pt;
                        }
                        .tdresult{
                            font-family: Verdana,sans-serif,Arial;
                            font-size: 8pt;
                        }
                    </style>
                    <div>
                        <table>
                            <tr>
                                <td width="475px" class="font"><b>Prefeitura do Recife<b></td>
                                <td  align-content="center"><img src="../midia/brasao.jpg" alt=""></td>
                                <td class="font" align="right" width="475px"><b>Portal de Compras<b></td>
                            </tr>
                        </table>
                         <hr size="3">
                        <table>
                            <tr align="center" style = "text-transform="uppercase">
                                <td width="1020px" class="font"><b>PLANEJAMENTO DAS CONTRATAÇÕES - RELATÓRIO DE DOCUMENTO DE FORMALIZAÇÃO DE DEMANDA<b></td>
                            </tr> 
                        </table>
                    </div>';
                    foreach($secretariasDFD as $secretaria){
                    $html .='<hr size="3">
                    <table>
                        <tr align="center" style = "text-transform="uppercase" bgcolor="#bfdaf2">
                            <td bgcolor="#D3D3D3" class="tdResultTituloOrg" width="1020px">'.$secretaria->eorglidesc.'</td>
                        </tr> 
                    </table>
                    <hr size="3">
                    <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
                    <thead>
                        <tr id="cabecalhos" bgcolor="#D3D3D3">
                            <td class="tdResultTitulo" id="cabIdDFD">NÚMERO DO DFD</td>
                            <td class="tdResultTitulo" id="cabAno">ANO DO PCA</td>
                            <td class="tdResultTitulo" id="cabDescClasse">DESCRIÇÃO DA CLASSE</td>
                            <td class="tdResultTitulo" id="cabDataPrevistaConclusao">DATA PREVISTA PARA CONCLUSÃO</td>
                            <td class="tdResultTitulo" id="cabTpProcesso">TIPO DE PROCESSO</td>
                            <td class="tdResultTitulo" id="cabGrauPrioridade">GRAU DE PRIORIDADE</td>
                            <td class="tdResultTitulo" id="cabSituacao">SITUAÇÃO DO DFD</td>
                        </tr>
                    </thead>
                    <tbody>';

            // print_r($html);exit;
            foreach($dadosDFD as $dado){
                if($secretaria->corglicodi == $dado->corglicodi){
                
                if(!empty($dado->dpldfdpret)){
                    $dataPrevConclusao = date('d/m/Y', strtotime($dado->dpldfdpret));
                }else{
                    $dataPrevConclusao = "";
                }

                if(!empty($dado->fpldfdtpct)){
                    $tpProcesso = ($dado->fpldfdtpct=="D")? "Contratação Direta" : "Licitação";
                }else{
                    $tpProcesso = "";

                }
                $urlDFD = "ConsDFD.php?dfdSelected=$dado->cpldfdnumf";
                if($dado->fpldfdgrau = ""){
                    if($dado->fpldfdgrau == 1){
                        $grauprioridade = "ALTO";
                    }else if($dado->fpldfdgrau == 2){
                        $grauprioridade = "MÉDIO";
                    }else if($dado->fpldfdgrau == 3){
                        $grauprioridade = "BAIXO";
                    }
                }else{
                    $grauprioridade = "";
                }
                
                $html.='<tr id="resultados">
                    <td class="tdresult" id="resIdDFD">'.$dado->cpldfdnumf.'</td>
                    <td class="tdresult" id="resAno">'.$dado->apldfdanod.'</td>
                    <td class="tdresult" id="resDescClasse">'.$dado->descclasse.'</td>
                    <td class="tdresult" id="resDataPrevistaConclusao">'.$dataPrevConclusao.'</td>
                    <td class="tdresult" style="text-transform: uppercase;" id="resTpProcesso">'.$tpProcesso.'</td>
                    <td class="tdresult" id="resGrauPrioridade">'.$grauprioridade.'</td>
                    <td class="tdresult" id="resSituacao">'.$dado->eplsitnome.'</td>
                </tr>';
            }
        }
            $html .= '</tbody></table><br>';
        }
            $html .= '
            <footer>
            <hr size="3">
            <table width="100%" >
            <tr>
            <td width="50%">Emissão: '.$hoje.'</td>
            <td>
            <div width="50%" align="right" class="pagenum-container">Página <span class="pagenum"></span></div>
            </td>
            </tr>
            </table>
            </footer>';
        }
        return $html;
    }
    // Usado em GerarPCA
    //Monta os dados para o pdf do documento PCA para aprovação
    public function montaHTMLPCAAprovacao($dadosDFD, $anoPCA, $autoridade, $cargo)
    {   
        $anoPCA = $anoPCA;
        date_default_timezone_set ("America/Recife");
        $hoje = date('d/m/Y H:i');
        $totalDFDs = count($dadosDFD);
        $aux = 0;
        $secretariasDFD = array();
        $posArray = -1;
        $valorEstimadoTotal = 0;
        for($i=0; $i<$totalDFDs; $i++){
            if($dadosDFD[$i]->corglicodi != $aux){ // Assume que as secretarias vem agrupadas
                $posArray++;
                $aux = $dadosDFD[$i]->corglicodi;
                $secretariasDFD[$posArray]->corglicodi = $dadosDFD[$i]->corglicodi;
                $secretariasDFD[$posArray]->eorglidesc = $dadosDFD[$i]->descorgao;
                $secretariasDFD[$posArray]->quantDFDs = 1;
                $secretariasDFD[$posArray]->valEstTotal = $dadosDFD[$i]->cpldfdvest;

            }else{
                $secretariasDFD[$posArray]->quantDFDs = $secretariasDFD[$posArray]->quantDFDs+1;
                $secretariasDFD[$posArray]->valEstTotal = $secretariasDFD[$posArray]->valEstTotal + $dadosDFD[$i]->cpldfdvest;
            }
            
            $valorEstimadoTotal =  $valorEstimadoTotal + $dadosDFD[$i]->cpldfdvest;
            //verificações de justificativas para as quatro sessões de resultado
            if($dadosDFD[$i]->cplsitcodi == "8" && $dadosDFD[$i]->fpldfdanal != "S" && $dadosDFD[$i]->fpldfdatua != "S" && !empty($dadosDFD[$i]->epldfdjuae)){
                $justificativaAnalisar = true;
            }
            if($dadosDFD[$i]->cplsitcodi == "8" && $dadosDFD[$i]->fpldfdanal == "S" && !empty($dadosDFD[$i]->epldfdjuae)){
               $justificativaAjustado  = true;
            }
            if($dadosDFD[$i]->cplsitcodi == "8" && $dadosDFD[$i]->fpldfdatua == "S" && $dadosDFD[$i]->fpldfdanal != "S" && !empty($dadosDFD[$i]->epldfdjuae)){
                $justificativaAtualizados = true;
            }
            if($dadosDFD[$i]->cplsitcodi == "9" || $dadosDFD[$i]->cplsitcodi == "10" && !empty($dadosDFD[$i]->epldfdjuae)){
                $justificativaAprovado = true;
            }

        }

        $mensageBuscaVazia = '<table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
                            <tbody><tr>
                                <td align="center"  class="titulo3" width="1024px">Pesquisa sem ocorrências.</td>
                            </tr></tbody></table>';


            $tamanhoQuebra = ($justificativaAnalisar == true)? 6:8;

            $DfdAnalisar='
            <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
            <thead>
                <tr id="cabecalhos" bgcolor="#D3D3D3">
                    <td class="tdResultTitulo thBorda" id="cabSeq"><strong>ORD.</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabAreaReq"><strong>UNIDADE</strong></td>';
            if($justificativaAnalisar == true){
                $DfdAnalisar.='<td class="tdResultTitulo thBorda" id="cabJustAno"><strong>JUSTIFICATIVA PARA MODIFICAÇÃO NO ANO DE EXECUÇÃO</strong></td>';
            }
            $DfdAnalisar.='<td class="tdResultTitulo thBorda" id="cabIdDFD"><strong>NÚMERO DO DFD</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabDescClasse"><strong>DESCRIÇÃO <br>DA CLASSE</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabDescSuc"><strong>DESCRIÇÃO SUCINTA DA DEMANDA</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabJustNes"><strong>JUSTIFICATIVA DA NECESSIDADE</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabEstVal"><strong>ESTIMATIVA DE VALOR</strong></td>';
                $DfdAnalisar.='</tr>
            </thead>
            <tbody>';
            
            $cont = 1;
            $verificadorAnalisar = 0;
            foreach($dadosDFD as $dado){
                if($dado->cplsitcodi == "8" && $dado->fpldfdanal != "S" && $dado->fpldfdatua != "S" ){  
                    if($cont < $tamanhoQuebra){                  
                        $DfdAnalisar.='<tr id="resultados">
                            <td class="tdresult tdBorda" id="resSeq">'.$cont.'</td>
                            <td class="tdresult tdBorda" id="resAreaReq">'.$dado->descorgao.'</td>';
                        if($justificativaAnalisar == true){
                        //     $DfdAnalisar.='<td class="tdresult tdBorda" id="resJustAno">'.$dado->epldfdjuae.'</td>';
                            $DfdAnalisar.='<td class="tdresult tdBorda" id="resJustAno">Texto teste para a justificativa</td>';
                        }
                        $DfdAnalisar.='<td class="tdresult tdBorda" id="resIdDFD">'.$dado->cpldfdnumf.'</td>
                                <td class="tdresult tdBorda" id="resDescClasse">'.$dado->descclasse.'</td>
                                <td class="tdresult tdBorda" id="resDescSuc">R$ '.$dado->epldfddesc.'</td>
                                <td class="tdresult tdBorda" id="resJustNes">'.$dado->epldfdjust.'</td>
                            <td class="tdresult tdBorda" id="resEstVal">'.number_format($dado->cpldfdvest,4,',','.').'</td>';
                    
                        $DfdAnalisar.='</tr>';
                    }
                    if($cont >= $tamanhoQuebra){
                        if($cont == $tamanhoQuebra){
                            $DfdAnalisar .='</tbody></table>';

                            $DfdAnalisar2 ='
                                <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
                                <thead>
                                    <tr id="cabecalhos" bgcolor="#D3D3D3">
                                        <td class="tdResultTitulo thBorda" id="cabSeq"><strong>ORD.</strong></td>
                                        <td class="tdResultTitulo thBorda" id="cabAreaReq"><strong>UNIDADE</strong></td>';
                                if($justificativaAnalisar == true){
                                    $DfdAnalisar2.='<td class="tdResultTitulo thBorda" id="cabJustAno"><strong>JUSTIFICATIVA PARA MODIFICAÇÃO NO ANO DE EXECUÇÃO</strong></td>';
                                }
                                    $DfdAnalisar2.='<td class="tdResultTitulo thBorda" id="cabIdDFD"><strong>NÚMERO DO DFD</strong></td>
                                        <td class="tdResultTitulo thBorda" id="cabDescClasse"><strong>DESCRIÇÃO <br>DA CLASSE</strong></td>
                                        <td class="tdResultTitulo thBorda" id="cabDescSuc"><strong>DESCRIÇÃO SUCINTA DA DEMANDA</strong></td>
                                        <td class="tdResultTitulo thBorda" id="cabJustNes"><strong>JUSTIFICATIVA DA NECESSIDADE</strong></td>
                                        <td class="tdResultTitulo thBorda" id="cabEstVal"><strong>ESTIMATIVA DE VALOR</strong></td>';
                                    $DfdAnalisar2.='</tr>
                                </thead>
                                <tbody>';
                        }
                        $DfdAnalisar2.='<tr id="resultados">
                        <td class="tdresult tdBorda" id="resSeq">'.$cont.'</td>
                        <td class="tdresult tdBorda" id="resAreaReq">'.$dado->descorgao.'</td>';
                        if($justificativaAnalisar == true){
                        //     $DfdAnalisar2.='<td class="tdresult tdBorda" id="resJustAno">'.$dado->epldfdjuae.'</td>';
                            $DfdAnalisar2.='<td class="tdresult tdBorda" id="resJustAno">Texto teste para a justificativa</td>';
                        }
                        $DfdAnalisar2.='<td class="tdresult tdBorda" id="resIdDFD">'.$dado->cpldfdnumf.'</td>
                                <td class="tdresult tdBorda" id="resDescClasse">'.$dado->descclasse.'</td>
                                <td class="tdresult tdBorda" id="resDescSuc">R$ '.$dado->epldfddesc.'</td>
                                <td class="tdresult tdBorda" id="resJustNes">'.$dado->epldfdjust.'</td>
                                <td class="tdresult tdBorda" id="resEstVal">'.number_format($dado->cpldfdvest,4,',','.').'</td>';
                            
                            $DfdAnalisar2.='</tr>';
                    }
                    $verificadorAnalisar++;
                    $cont++;
                }
            }
            $DfdAnalisar .='</tbody></table>';

            if($verificadorAnalisar == 0){ 
                $DfdAnalisar = $mensageBuscaVazia;
            }
            //-----------------------------------------------------------------------Montagem DFDS Ajustados Reanalisar
            $DFDsAjustados='
            <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
            <thead>
                <tr id="cabecalhos" bgcolor="#D3D3D3">
                    <td class="tdResultTitulo thBorda" id="cabSeq"><strong>ORD.</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabAreaReq"><strong>UNIDADE</strong></td>';
                if($justificativaAjustado){
                    $DFDsAjustados.='<td class="tdResultTitulo thBorda" id="cabJustAno"><strong>JUSTIFICATIVA PARA MODIFICAÇÃO NO ANO DE EXECUÇÃO</strong></td>';
                }
                    $DFDsAjustados.='<td class="tdResultTitulo thBorda" id="cabIdDFD"><strong>NÚMERO DO DFD</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabDescClasse"><strong>DESCRIÇÃO <br>DA CLASSE</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabDescSuc"><strong>DESCRIÇÃO SUCINTA DA DEMANDA</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabJustNes"><strong>JUSTIFICATIVA DA NECESSIDADE</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabEstVal"><strong>ESTIMATIVA DE VALOR</strong></td>';
                    
                $DFDsAjustados.='</tr>
            </thead>
            <tbody>';
            $cont = 1;
            $verificadorAjustado = 0;
            foreach($dadosDFD as $dado){
                if($dado->cplsitcodi == "8" && $dado->fpldfdanal == "S"){
                    $DFDsAjustados.='<tr id="resultados">
                        <td class="tdresult tdBorda" id="resSeq">'.$cont.'</td>
                        <td class="tdresult tdBorda" id="resAreaReq">'.$dado->descorgao.'</td>';
                        if($justificativaAjustado){
                            $DFDsAjustados.='<td class="tdresult tdBorda" id="resJustAno">'.$dado->epldfdjuae.'</td>';
                        }
                        $DFDsAjustados.='<td class="tdresult tdBorda" id="resIdDFD">'.$dado->cpldfdnumf.'</td>
                        <td class="tdresult tdBorda" id="resDescClasse">'.$dado->descclasse.'</td>
                        <td class="tdresult tdBorda" id="resDescSuc">R$ '.$dado->epldfddesc.'</td>
                        <td class="tdresult tdBorda" id="resJustNes">'.$dado->epldfdjust.'</td>
                        <td class="tdresult tdBorda" id="resEstVal">'.number_format($dado->cpldfdvest,4,',','.').'</td>';
                    $DFDsAjustados.='</tr>';
                    $verificadorAjustado++;
                    $cont++;
                }
            }
            $DFDsAjustados.='</tbody></table>';
            if($verificadorAjustado == 0){
                $DFDsAjustados = $mensageBuscaVazia;
            }
            // ------------------------------------------------------------------------Começo DFD atualizados
            $DFDsAtualizados='
            <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
            <thead>
                <tr id="cabecalhos" bgcolor="#D3D3D3">
                    <td class="tdResultTitulo thBorda" id="cabSeq"><strong>ORD.</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabAreaReq"><strong>UNIDADE</strong></td>';
                    if($justificativaAtualizados == true){
                        $DFDsAtualizados.='<td class="tdResultTitulo thBorda" id="cabJustAno"><strong>JUSTIFICATIVA PARA MODIFICAÇÃO NO ANO DE EXECUÇÃO</strong></td>';
                    }
                    $DFDsAtualizados.='<td class="tdResultTitulo thBorda" id="cabIdDFD"><strong>NÚMERO DO DFD</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabDescClasse"><strong>DESCRIÇÃO <br>DA CLASSE</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabDescSuc"><strong>DESCRIÇÃO SUCINTA DA DEMANDA</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabJustNes"><strong>JUSTIFICATIVA DA NECESSIDADE</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabEstVal"><strong>ESTIMATIVA DE VALOR</strong></td>';
                    
                $DFDsAtualizados.='</tr>
            </thead>
            <tbody>';
            $cont = 1;
            $verificadorAtualizado= 0;
            foreach($dadosDFD as $dado){
                if($dado->cplsitcodi == "8" && $dado->fpldfdatua == "S" && $dado->fpldfdanal != "S"){
                    $DFDsAtualizados.='<tr id="resultados">
                        <td class="tdresult tdBorda" id="resSeq">'.$cont.'</td>
                        <td class="tdresult tdBorda" id="resAreaReq">'.$dado->descorgao.'</td>';
                        if($justificativaAtualizados == true){
                            $DFDsAtualizados.='<td class="tdresult tdBorda" id="resJustAno">'.$dado->epldfdjuae.'</td>';
                        }
                        $DFDsAtualizados.='<td class="tdresult tdBorda" id="resIdDFD">'.$dado->cpldfdnumf.'</td>
                        <td class="tdresult tdBorda" id="resDescClasse">'.$dado->descclasse.'</td>
                        <td class="tdresult tdBorda" id="resDescSuc">R$ '.$dado->epldfddesc.'</td>
                        <td class="tdresult tdBorda" id="resJustNes">'.$dado->epldfdjust.'</td>
                        <td class="tdresult tdBorda" id="resEstVal">'.number_format($dado->cpldfdvest,4,',','.').'</td>';
                    
                    $DFDsAtualizados.='</tr>';
                    $verificadorAtualizado++;
                    $cont++;
                }
                
            }
            $DFDsAtualizados.='</tbody></table>';
            if($verificadorAtualizado == 0){
                $DFDsAtualizados = $mensageBuscaVazia;
            }
           //--------------------------------------------------------------------------Começo DFDS aprovados
            $DFDsAprovados='
            <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
            <thead>
                <tr id="cabecalhos" bgcolor="#D3D3D3">
                    <td class="tdResultTitulo thBorda" id="cabSeq"><strong>ORD.</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabAreaReq"><strong>UNIDADE</strong></td>';
                    if($justificativaAprovado == true){
                        $DFDsAprovados.='<td class="tdResultTitulo thBorda" id="cabJustAno"><strong>JUSTIFICATIVA PARA MODIFICAÇÃO NO ANO DE EXECUÇÃO</strong></td>';
                    }
                    $DFDsAprovados.='<td class="tdResultTitulo" id="thBorda"><strong>NÚMERO DO DFD</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabDescClasse"><strong>DESCRIÇÃO <br>DA CLASSE</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabDescSuc"><strong>DESCRIÇÃO SUCINTA DA DEMANDA</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabJustNes"><strong>JUSTIFICATIVA DA NECESSIDADE</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabEstVal"><strong>ESTIMATIVA DE VALOR</strong></td>';
                    
                $DFDsAprovados.='</tr>
            </thead>
            <tbody>';
            $cont = 1;
            $verificadorAprovados = 0;
            foreach($dadosDFD as $dado){
                if($dado->cplsitcodi == "9" || $dado->cplsitcodi == "10"){
                    $DFDsAprovados.='<tr id="resultados">
                        <td class="tdresult tdBorda" id="resSeq">'.$cont.'</td>
                        <td class="tdresult tdBorda" id="resAreaReq">'.$dado->descorgao.'</td>';
                        if($justificativaAprovado == true){
                            $DFDsAprovados.='<td class="tdresult" id="resJustAno">'.$dado->epldfdjuae.'</td>';
                        }
                        $DFDsAprovados.='<td class="tdresult tdBorda" id="resIdDFD">'.$dado->cpldfdnumf.'</td>
                        <td class="tdresult tdBorda" id="resDescClasse">'.$dado->descclasse.'</td>
                        <td class="tdresult tdBorda" id="resDescSuc">R$ '.$dado->epldfddesc.'</td>
                        <td class="tdresult tdBorda" id="resJustNes">'.$dado->epldfdjust.'</td>
                        <td class="tdresult tdBorda" id="resEstVal">'.number_format($dado->cpldfdvest,4,',','.').'</td>';
                    
                    $DFDsAprovados.='</tr>';
                    $verificadorAprovados++;
                    $cont++;
                }
                
            }
            $DFDsAprovados.='</tbody></table>';
            if($verificadorAprovados == 0){
                $DFDsAprovados = $mensageBuscaVazia;
            }
        
            //Montagem Geral do HTML do PDF
        $html = "
            <style>
            .pagenum:before {content: counter(page);}
            footer .pagenum:before {content: counter(page);}
            @page{
                margin-bottom: 50px;
                font-family: Verdana,sans-serif,Arial !important;
            }
            footer{
                position: fixed;
                left: 0px;
                right: 0px;
                height: 50px;
                bottom: 0px;
                margin-bottom: -50px;
            }
            table{
                width: 100%;
                text-align: center;
                border-collapse: collapse;
                        }
            .tdBorda {
                border: 1px solid #D3D3D3; 
                padding: 8px;
                }
            .thBorda {
                border: 1px solid #ffffff; 
                }
            #tdHeader{
                text-align:center;
                width: 100%;
                        }
            p{
                text-align: justify;
                font-size: 10.6667px; 
                        }
            #containerSessB{
                border-color: black;
                border-style: solid;
                border-width: thin;
                }
            .tdResultTitulo{
                font-family: Verdana,sans-serif,Arial;
                font-size: 10pt;
            }
            .tdResultTituloOrg{
                font-family: Verdana,sans-serif,Arial;
                font-size: 13pt;
                background-color: #D3D3D3;
            }
            .label, .result, .tdresult{
                font-family: Verdana,sans-serif,Arial;
                font-size: 8pt;
            }
            .page-break {
                page-break-before: always;
                page-break-inside: avoid;
            }
            .page-first{
                position: fixed; 
                top: 140; 
            }
            </style>
            <body>
            <header>
            </header>
            <main>
            <div>
                <table>
                    <tr width='100%'>
                        <td width='455px' class='font' style='text-align: left;'><b>Prefeitura do Recife<b></td>
                        <td class='tdBorda' align-content='center' width='25px'><img src='../midia/brasao.jpg'></td>
                        <td class='font' style='text-align: right;' width='455px'><b>Portal de Compras<b></td>
                    </tr>
                </table>
                <hr size='3'>
                <table>
                    <tr align='center' style = 'text-transform='uppercase'>
                        <td width='1020px' class='font'><b>PLANO DE CONTRATAÇÕES ANUAL (PCA) $anoPCA<b></td>
                    </tr> 
                </table>
            </div>
            <div id='sessB'>
            <table width='100%' >
                <tr>
                    <table id='containerSessB' style='width:100%; text-align:center;'>
                        <tr>
                            <td class='label tdBorda' ><strong>QUANTIDADE DE DOCUMENTOS DE FORMALIZAÇÃO DE DEMANDA (DFDs)</strong></td>
                            <td class='result tdBorda' >".$totalDFDs."</td>
                        </tr>
                        <tr>
                            <td class='label tdBorda' ><strong>VALOR ESTIMADO EM NOVAS CONTRATAÇÕES</strong></td>
                            <td class='result tdBorda' >R$ ".number_format($valorEstimadoTotal,4,',','.')."</td>
                        </tr>
                    </table>
                </tr>
            </table>
            </div>

            <div id='sessD' class='page-first'>
            <table width='100%'>
                <tr><td id='tdHeader' class='tdResultTituloOrg  tdBorda'><strong>DFDs A SEREM ANALISADOS</strong></td></tr>
                <tr><td id='tdHeader'><div width='100%' height='3px' color='#ffffff'></div></td></tr>
                <tr>
                $DfdAnalisar
                </tr>
            </table>
            </div>";
            if(!empty($DfdAnalisar2)){
                $html .= "<div class='page-break'>
                <table width='100%'>
                    <tr>
                    $DfdAnalisar2
                    </tr>
                </table>
                </div>";
            }
            $html .= "<div id='sessD' class='page-break'>
            <table width='100%'>
                <tr><td id='tdHeader' class='tdResultTituloOrg  tdBorda'><strong>DFDs AJUSTADOS A SEREM REANALISADOS</strong></td></tr>
                <tr><td id='tdHeader'><div width='100%' height='3px' color='#ffffff'></div></td></tr>
                <tr>
                $DFDsAjustados
                </tr>
            </table>
            </div>

            <div id='sessE' class='page-break'>
            <table width='100%'>
                <tr><td id='tdHeader' class='tdResultTituloOrg  tdBorda'><strong>DFDs ATUALIZADOS A SEREM ANALISADOS</strong></td></tr>
                <tr><td id='tdHeader'><div width='100%' height='3px' color='#ffffff'></div></td></tr>
                <tr>
                $DFDsAtualizados
                </tr>
            </table>
            </div>         

            <div id='sessAP' class='page-break'>
            <hr size='3'>
            <table width='100%'>
                <tr><td id='tdHeader' class='tdResultTituloOrg  tdBorda'><strong>DFDs APROVADOS</strong></td></tr>
                <tr><td id='tdHeader'><div width='100%' height='3px' color='#ffffff'></div></td></tr>
                <tr>
                $DFDsAprovados
                </tr>
            </table>
            </div>          

            <div >
                </br>
                </br>
                </br>
                <table width='100%' style='page-break-inside: avoid;'>
                    <tr><td style='font-family: Verdana,sans-serif,Arial; font-size: 10pt;'><strong>No âmbito da competência prevista no art. 11 do Decreto Municipal nº 36.089/2022, a autoridade competente aprova o presente Plano de Contratações Anual $anoPCA.</strong></td></tr>
                    </br>
                    </br>
                    </br>
                    <tr>
                        <td style='float:center;font-family: Verdana,sans-serif,Arial; font-size: 10pt; text-transform: uppercase;'><strong>$autoridade</br>$cargo</strong></td>
                    </tr>
                </table>
            </div>
            
            </main>
            <footer>
            <hr size='3'>
            <table width='100%'>
            <tr>
            <td width='50%'>Emissão: ".$hoje."</td>
            <td>
            <div width='50%' align='right' class='pagenum-container'>Página <span class='pagenum'></span></div>
            </td>
            </tr>
            </table>
            </footer>
            </body>
        ";
        return $html;
    }
    // Usado em GerarPCA
    //Monta os dados para o pdf do documento PCA para publicação
    public function montaHTMLPCAPublicacao($dadosDFD, $anoPCA, $autoridade, $cargo)
    {   
        $anoPCA = $anoPCA;
        date_default_timezone_set ("America/Recife");
        $hoje = date('d/m/Y H:i');
        $totalDFDs = count($dadosDFD);
        $aux = 0;
        $secretariasDFD = array();
        $posArray = -1;
        $valorEstimadoTotal = 0;
        for($i=0; $i<$totalDFDs; $i++){
            if($dadosDFD[$i]->corglicodi != $aux){ // Assume que as secretarias vem agrupadas
                $posArray++;
                $aux = $dadosDFD[$i]->corglicodi;
                $secretariasDFD[$posArray]->corglicodi = $dadosDFD[$i]->corglicodi;
                $secretariasDFD[$posArray]->eorglidesc = $dadosDFD[$i]->descorgao;
                $secretariasDFD[$posArray]->quantDFDs = 1;
                $secretariasDFD[$posArray]->valEstTotal = $dadosDFD[$i]->cpldfdvest;

            }else{
                $secretariasDFD[$posArray]->quantDFDs = $secretariasDFD[$posArray]->quantDFDs+1;
                $secretariasDFD[$posArray]->valEstTotal = $secretariasDFD[$posArray]->valEstTotal + $dadosDFD[$i]->cpldfdvest;
            }
            
            $valorEstimadoTotal =  $valorEstimadoTotal + $dadosDFD[$i]->cpldfdvest;

            if($dadosDFD[$i]->cplsitcodi == "9" || $dadosDFD[$i]->cplsitcodi == "10" && !empty($dadosDFD[$i]->epldfdjuae)){
                $justificativaAprovado = true;
            }

        }
            $mensageBuscaVazia = '<table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
            <tbody><tr>
                <td align="center"  class="titulo3" width="1024px">Pesquisa sem ocorrências.</td>
            </tr></tbody></table>';
            $tamanhoQuebra = ($justificativaAprovado == true)? 6:8;

            $DFDSAproExec='
            <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
            <thead>
                <tr id="cabecalhos" bgcolor="#D3D3D3">
                    <td class="tdResultTitulo thBorda" id="cabSeq"><strong>ORD.</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabAreaReq"><strong>UNIDADE</strong></td>';
                    if($justificativaAprovado == true){
                        $DFDSAproExec.='<td class="tdResultTitulo thBorda" id="cabJustAno"><strong>JUSTIFICATIVA PARA MODIFICAÇÃO NO ANO DE EXECUÇÃO</strong></td>';
                    }
                    $DFDSAproExec.='<td class="tdResultTitulo thBorda" id="cabIdDFD"><strong>NÚMERO DO DFD</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabDescClasse"><strong>DESCRIÇÃO <br>DA CLASSE</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabDescSuc"><strong>DESCRIÇÃO SUCINTA DA DEMANDA</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabJustNes"><strong>JUSTIFICATIVA DA NECESSIDADE</strong></td>
                    <td class="tdResultTitulo thBorda" id="cabEstVal"><strong>ESTIMATIVA DE VALOR</strong></td>';
                    
                $DFDSAproExec.='</tr>
            </thead>
            <tbody>';
            
            $cont = 1;
            $verificadorAprovados = 0;
            foreach($dadosDFD as $dado){
                if($dado->cplsitcodi == "9" || $dado->cplsitcodi == "10" ){                    
                    if($cont < $tamanhoQuebra){
                        $DFDSAproExec.='<tr id="resultados">
                        <td class="tdresult tdBorda" id="resSeq">'.$cont.'</td>
                        <td class="tdresult tdBorda" id="resAreaReq">'.$dado->descorgao.'</td>';
                        if($justificativaAprovado == true){
                            $DFDSAproExec.='<td class="tdresult" id="resJustAno">'.$dado->epldfdjuae.'</td>';
                        }
                        $DFDSAproExec.='<td class="tdresult tdBorda" id="resIdDFD">'.$dado->cpldfdnumf.'</td>
                        <td class="tdresult tdBorda" id="resDescClasse">'.$dado->descclasse.'</td>
                        <td class="tdresult tdBorda" id="resDescSuc">R$ '.$dado->epldfddesc.'</td>
                        <td class="tdresult tdBorda" id="resJustNes">'.$dado->epldfdjust.'</td>
                        <td class="tdresult tdBorda" id="resEstVal">'.number_format($dado->cpldfdvest,4,',','.').'</td>';
                    
                        $DFDSAproExec.='</tr>';
                    }
                    if($cont >= $tamanhoQuebra){
                        if($cont == $tamanhoQuebra){
                            $DFDSAproExec .='</tbody></table>';

                            $DFDSAproExec2 ='
                                <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
                                <thead>
                                    <tr id="cabecalhos" bgcolor="#D3D3D3">
                                        <td class="tdResultTitulo thBorda" id="cabSeq"><strong>ORD.</strong></td>
                                        <td class="tdResultTitulo thBorda" id="cabAreaReq"><strong>UNIDADE</strong></td>';
                                if($justificativaAprovado == true){
                                    $DFDSAproExec2.='<td class="tdResultTitulo thBorda" id="cabJustAno"><strong>JUSTIFICATIVA PARA MODIFICAÇÃO NO ANO DE EXECUÇÃO</strong></td>';
                                }
                                $DFDSAproExec2.='<td class="tdResultTitulo thBorda" id="cabIdDFD"><strong>NÚMERO DO DFD</strong></td>
                                        <td class="tdResultTitulo thBorda" id="cabDescClasse"><strong>DESCRIÇÃO <br>DA CLASSE</strong></td>
                                        <td class="tdResultTitulo thBorda" id="cabDescSuc"><strong>DESCRIÇÃO SUCINTA DA DEMANDA</strong></td>
                                        <td class="tdResultTitulo thBorda" id="cabJustNes"><strong>JUSTIFICATIVA DA NECESSIDADE</strong></td>
                                        <td class="tdResultTitulo thBorda" id="cabEstVal"><strong>ESTIMATIVA DE VALOR</strong></td>';
                                    $DFDSAproExec2.='</tr>
                                </thead>
                                <tbody>';
                        }
                        $DFDSAproExec2.='<tr id="resultados">
                        <td class="tdresult tdBorda" id="resSeq">'.$cont.'</td>
                        <td class="tdresult tdBorda" id="resAreaReq">'.$dado->descorgao.'</td>';
                        if($justificativaAprovado == true){
                            $DFDSAproExec2.='<td class="tdresult tdBorda" id="resJustAno">'.$dado->epldfdjuae.'</td>';
                        }
                        $DFDSAproExec2.='<td class="tdresult tdBorda" id="resIdDFD">'.$dado->cpldfdnumf.'</td>
                                <td class="tdresult tdBorda" id="resDescClasse">'.$dado->descclasse.'</td>
                                <td class="tdresult tdBorda" id="resDescSuc">R$ '.$dado->epldfddesc.'</td>
                                <td class="tdresult tdBorda" id="resJustNes">'.$dado->epldfdjust.'</td>
                                <td class="tdresult tdBorda" id="resEstVal">'.number_format($dado->cpldfdvest,4,',','.').'</td>';
                            
                            $DFDSAproExec2.='</tr>';
                    }
                    $verificadorAprovados++;
                    $cont++;
                }
            }
            $DFDSAproExec .='</tbody></table>';
            if($verificadorAprovados == 0){
                $DFDSAproExec = $mensageBuscaVazia;
            }

        $html = "
            <style>
                .pagenum:before {content: counter(page);}
                footer .pagenum:before {content: counter(page);}
                @page{
                    margin-bottom: 50px;
                    font-family: Verdana,sans-serif,Arial !important;
                }
                footer{
                    position: fixed;
                    left: 0px;
                    right: 0px;
                    height: 50px;
                    bottom: 0px;
                    margin-bottom: -50px;
                }
                table{
                    width: 100%;
                    text-align: center;
                    border-collapse: collapse;
                            }
                .tdBorda {
                    border: 1px solid #D3D3D3; 
                    padding: 8px;
                    }
                .thBorda {
                    border: 1px solid #ffffff; 
                    border-top: 3px solid #ffffff;
                    }
                #tdHeader{
                    text-align:center;
                    width: 100%;
                            }
                p{
                    text-align: justify;
                    font-size: 10.6667px; 
                            }
                #containerSessB{
                    border-color: black;
                    border-style: solid;
                    border-width: thin;
                    }
                .tdResultTitulo{
                    font-family: Verdana,sans-serif,Arial;
                    font-size: 10pt;
                }
                .tdResultTituloOrg{
                    font-family: Verdana,sans-serif,Arial;
                    font-size: 13pt;
                    background-color: #D3D3D3;
                }
                .label, .result, .tdresult{
                    font-family: Verdana,sans-serif,Arial;
                    font-size: 8pt;
                }
                .page-break {
                    page-break-before: always;
                    page-break-inside: avoid;
                }
                .page-first{
                    page-break-before: avoid; 
                    position: fixed; 
                    top: 130; 
                }
            </style>
            <body>
            <header>
            </header>
            <main>
            <div>
                <table>
                    <tr>
                <td width='475px' class='font'><b>Prefeitura do Recife<b></td>
                <td  align-content='center' width='25px'><img src='../midia/brasao.jpg'></td>
                <td class='font' align='right' width='475px'><b>Portal de Compras<b></td>
                    </tr>
                </table>
                <hr size='3'>
                <table>
                    <tr align='center' style = 'text-transform='uppercase'>
                        <td width='1020px' class='font'><b>PLANO DE CONTRATAÇÕES ANUAL (PCA) $anoPCA<b></td>
                    </tr> 
                </table>
            </div>
            <div id='sessB'>
            <table width='100%' >
                <tr>
                    <table id='containerSessB' style='width:100%; text-align:center;'>
                        <tr>
                            <td class='label tdBorda'><strong>QUANTIDADE DE DOCUMENTOS DE FORMALIZAÇÃO DE DEMANDA (DFDs)</strong></td>
                            <td class='result tdBorda'>".$totalDFDs."</td>
                        </tr>
                        <tr>
                            <td class='label tdBorda'><strong>VALOR ESTIMADO EM NOVAS CONTRATAÇÕES</strong></td>
                            <td class='result tdBorda'>R$ ".number_format($valorEstimadoTotal,4,',','.')."</td>
                        </tr>
                    </table>
                </tr>
            </table>
            </div>

            <div id='sessD' class='page-first'>
            <table width='100%'>
                <tr><td id='tdHeader' class='tdResultTituloOrg'><strong>DOCUMENTOS DE FORMALIZAÇÃO DE DEMANDAS</strong></td></tr>
                <tr>
                $DFDSAproExec
                </tr>
            </table>
            </div>";            
            if(!empty($DFDSAproExec2)){
                $html .= "<div id='sessD' class='page-break'>
                <table width='100%'>
                    <tr>
                    $DFDSAproExec2
                    </tr>
                </table>
                </div>";     
            }       
            $html .= "
            <div  class='page-break'>
                </br>
                </br>
                </br>
                <table width='100%' style='page-break-inside: avoid;'>
                    <tr><td style='font-family: Verdana,sans-serif,Arial; font-size: 10pt;'><strong>No âmbito da competência prevista no art. 11 do Decreto Municipal nº 36.089/2022, a autoridade competente aprova o presente Plano de Contratações Anual $anoPCA.</strong></td></tr>
                    </br>
                    </br>
                    </br>
                    <tr>
                        <td style='float:center;font-family: Verdana,sans-serif,Arial; font-size: 10pt; text-transform: uppercase;'><strong>$autoridade</br>$cargo</strong></td>
                    </tr>
                </table>
            </div>
           
            </main>
            <footer>
            <hr size='3'>
            <table width='100%'>
            <tr>
            <td width='50%'>Emissão: ".$hoje."</td>
            <td>
            <div width='50%' align='right' class='pagenum-container'>Página <span class='pagenum'></span></div>
            </td>
            </tr>
            </table>
            </footer>
            </body>
        ";
        return $html;
    }
    /**
     * Função utilizada em ConsPesquisarDFD.php, 
     * Pega todos os dados do DFD encontrados e monta o sql para a página;
     */
    public function montaHTMLAnalisarPDF($dadosDFD)
    {   
        date_default_timezone_set ("America/Recife");
        $hoje = date('d/m/Y H:i');
        $aux = 0;
        $secretariasDFD = array();
        $posArray = 0;
        for($i=0; $i<count($dadosDFD); $i++){
            if($dadosDFD[$i]->corglicodi != $aux){ // Assume que as secretarias vem agrupadas
                $aux = $dadosDFD[$i]->corglicodi;
                $secretariasDFD[$posArray]->corglicodi = $dadosDFD[$i]->corglicodi;
                $secretariasDFD[$posArray]->eorglidesc = $dadosDFD[$i]->eorglidesc;
                $posArray++;
            }
        }
        if(empty($dadosDFD)){
            $html = '<style>
                        .pagenum:before {content: counter(page);}
                        footer .pagenum:before {content: counter(page);}
                        td{
                            font-family: Verdana,sans-serif,Arial;
                        }
                        .font{
                                font-family: Verdana,sans-serif,Arial;
                            
                        }
                        .tdResultTitulo{
                            font-size: 10pt;
                            text-transform: uppercase;
                        }
                        .tdresult{
                            font-size: 8pt;
                        }
                    </style>
                    <div>
                        <table>
                            <tr>
                                <td class ="font"width="475px">Prefeitura do Recife</td>
                                <td align-content="center"><img src="../midia/brasao.jpg" alt=""></td>
                                <td align="right" class="font" width="475px">Portal de Compras</td>
                            </tr>
                        </table>
                    </div>
                    <hr size="3">
                    <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
                    <tbody><tr>
                        <td align="center" colspan="8" class="titulo3" width="900px">Pesquisa sem ocorrências.</td>
                    </tr></tbody></table>';
            $html .= '
                    <footer>
                    <hr size="3">
                    <table width="100%" >
                    <tr>
                    <td width="50%">Emissão: '.$hoje.'</td>
                    <td>
                    <div width="50%" align="right" class="pagenum-container">Página <span class="pagenum"></span></div>
                    </td>
                    </tr>
                    </table>
                    </footer>';
        }else{                       
            $html ='<style>
                        .pagenum:before {content: counter(page);}
                        footer .pagenum:before {content: counter(page);}
                        .tdResultTitulo{
                            font-family: Verdana,sans-serif,Arial;
                            font-size: 8pt;
                        }
                        .tdResultTituloOrg{
                            font-family: Verdana,sans-serif,Arial;
                            font-size: 13pt;
                        }
                        .tdresult{
                            font-family: Verdana,sans-serif,Arial;
                            font-size: 8pt;
                        }
                    </style>
                    <div>
                        <table>
                            <tr>
                                <td width="475px" class="font"><b>Prefeitura do Recife<b></td>
                                <td  align-content="center"><img src="../midia/brasao.jpg" alt=""></td>
                                <td class="font" align="right" width="475px"><b>Portal de Compras<b></td>
                            </tr>
                        </table>
                         <hr size="3">
                        <table>
                            <tr align="center" style = "text-transform="uppercase">
                                <td width="1020px" class="font"><b>PLANEJAMENTO DAS CONTRATAÇÕES - RELATÓRIO DE DOCUMENTO DE FORMALIZAÇÃO DE DEMANDA<b></td>
                            </tr> 
                        </table>
                    </div>';
            foreach($secretariasDFD as $secretaria){
                        $html .='<hr size="3">
                        <table>
                            <tr align="center" style = "text-transform="uppercase" bgcolor="#bfdaf2">
                                <td bgcolor="#D3D3D3" class="tdResultTituloOrg" width="1020px">'.$secretaria->eorglidesc.'</td>
                            </tr> 
                        </table>
                    <hr size="3">
                    <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
                    <thead>
                            <tr id="cabecalhos" bgcolor="#D3D3D3">
                                <td class="tdResultTitulo" id="cabIdDFD">NÚMERO DO DFD</td>
                                <td class="tdResultTitulo" id="cabAno">ANO DO PCA</td>
                                <td class="tdResultTitulo" align="center">CNPJ</td>
                                <td class="tdResultTitulo" id="cabDescClasse">CLASSE</td>
                                <td class="tdResultTitulo" align="center">Estimativa de Valor</td>
                                <td class="tdResultTitulo" id="cabDataPrevistaConclusao">DATA PRESVISTA PARA CONCLUSÃO</td>
                                <td class="tdResultTitulo" id="cabTpProcesso">TIPO DE PROCESSO</td>
                                <td class="tdResultTitulo" id="cabGrauPrioridade">GRAU DE PRIORIDADE</td>
                                <td class="tdResultTitulo" id="cabSituacao">SITUAÇÃO DO DFD</td>
                        </tr>
                    </thead>
                    <tbody>';

            foreach($dadosDFD as $dado){
                    if($secretaria->corglicodi == $dado->corglicodi){
                        $descclasse = empty($dado->eclamsdesc)? "-": $dado->eclamsdesc;
                $dataPrevConclusao = date('d/m/Y', strtotime($dado->dpldfdpret));
                        $tpProcesso = ($dado->fpldfdtpct=="D")? "CONTRATAÇÃO DIRETA" : "LICITAÇÃO";

                if($dado->fpldfdgrau == 1){
                    $grauprioridade = "ALTO";
                }else if($dado->fpldfdgrau == 2){
                    $grauprioridade = "MÉDIO";
                }else if($dado->fpldfdgrau == 3){
                    $grauprioridade = "BAIXO";
                }

                        $cnpj = FormataCNPJ($dado->aorglicnpj);
                $html.='<tr id="resultados">
                    <td class="tdresult" id="resIdDFD">'.$dado->cpldfdnumf.'</td>
                    <td class="tdresult" id="resAno">'.$dado->apldfdanod.'</td>
                                    <td class="tdresult" id="cnpj_result" align="center">'.$cnpj.'</td>
                                    <td class="tdresult" id="resDescClasse">'.$descclasse.'</td>
                                    <td class="tdresult" align="center">R$'.number_format($dado->cpldfdvest, 2, ',', '.').'</td>
                    <td class="tdresult" id="resDataPrevistaConclusao">'.$dataPrevConclusao.'</td>
                                    <td class="tdresult" id="resTpProcesso">'.$tpProcesso.'</td>
                    <td class="tdresult" id="resGrauPrioridade">'.$grauprioridade.'</td>
                    <td class="tdresult" id="resSituacao">'.$dado->eplsitnome.'</td>
                </tr>';
            }
                }
            $html .= '</tbody></table><br>';
            }
            $html .= '
            <footer>
            <hr size="3">
            <table width="100%" >
            <tr>
            <td width="50%">Emissão: '.$hoje.'</td>
            <td>
            <div width="50%" align="right" class="pagenum-container">Página <span class="pagenum"></span></div>
            </td>
            </tr>
            </table>
            </footer>';
        }
        return $html;
    }
    /**
     * Função utilizada em ConsSelecionarManterDFD.php, 
     * Pega todos os dados do DFD encontrados e monta o sql para a página;
     */
    public function montaHTMLManter($dadosDFD)
    {   
        $aux = 0;
        $secretariasDFD = array();
        $posArray = 0;
        for($i=0; $i<count($dadosDFD); $i++){
            if($dadosDFD[$i]->corglicodi != $aux){ // Assume que as secretarias vem agrupadas
                $aux = $dadosDFD[$i]->corglicodi;
                $secretariasDFD[$posArray]->corglicodi = $dadosDFD[$i]->corglicodi;
                $secretariasDFD[$posArray]->eorglidesc = $dadosDFD[$i]->descorgao;
                $posArray++;
            }
        }
        if(empty($dadosDFD)){
            $html = '<tr>
                    <td align="center" bgcolor="#75ADE6" colspan="8" class="titulo3" width="900px">
                            RESULTADO DA PESQUISA
                        </td>
                    </tr>
                    <tr>
                        <td align="left" colspan="8" class="textonormal" width="900px">Pesquisa sem Ocorrências.</td>
                    </tr>';
        }else{
                $html='
                        <tr>
                            <td align="center" bgcolor="#75ADE6" colspan="8" class="titulo3">
                                Resultado da pesquisa
                            </td>
                        </tr>';
                foreach($secretariasDFD as $secretaria){
            
                    $html.='<tr><td align="center" bgcolor="#BFDAF2" colspan="8" class="titulo3">'.$secretaria->eorglidesc.'</td></tr>';
                
                $html.='<tr>
                        <td>
                        <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
                        <thead>
                            <tr id="cabecalhos">
                                    <td width="130px" class="tdResultTitulo" id="cabIdDFD">NÚMERO DO DFD</td>
                                    <td width="55px"class="tdResultTitulo" id="cabAno">ANO DO PCA</td>
                                    <td width="55px" class="tdResultTitulo" id="cabCodClasse">CÓDIGO DA CLASSE</td>
                                    <td width="290px" class="tdResultTitulo" id="cabDescClasse">DESCRIÇÃO DA CLASSE</td>
                                    <td width="108px" class="tdResultTitulo" id="cabDataPrevistaConclusao">DATA PREVISTA PARA CONCLUSÃO</td>
                                    <td width="114px" class="tdResultTitulo" id="cabTpProcesso">TIPO DE PROCESSO</td>
                                    <td width="75px" class="tdResultTitulo" id="cabGrauPrioridade">GRAU DE PRIORIDADE</td>
                                    <td width="130px" class="tdResultTitulo" id="cabSituacao">SITUAÇÃO DO DFD</td>
                            </tr>
                        </thead>
                        <tbody>';

                
                foreach($dadosDFD as $dado){
                    if($secretaria->corglicodi == $dado->corglicodi){
                        if($dado->fpldfdtpct=="D"){
                            $tpProcesso = "CONTRATAÇÃO DIRETA";
                        }elseif($dado->fpldfdtpct=="L"){
                            $tpProcesso = "LICITAÇÃO";
                        }else{
                            $tpProcesso = "-";
                        }
                        $urlDFD = "CadManterDFD.php?dfdSelected=$dado->cpldfdnumf";

                        if($dado->fpldfdgrau == 1){
                            $grauprioridade = "ALTO";
                        }else if($dado->fpldfdgrau == 2){
                            $grauprioridade = "MÉDIO";
                        }else if($dado->fpldfdgrau == 3){
                            $grauprioridade = "BAIXO";
                        }else{
                            $grauprioridade = "-";
                        }
                        $cclamscodi = empty($dado->cclamscodi)? "-": $dado->cclamscodi;
                        $descclasse = empty($dado->descclasse)? "-": $dado->descclasse;
                        
                        if(!is_null($dado->dpldfdpret)){
                            $dataPrevConclusao = date('d/m/Y', strtotime($dado->dpldfdpret));
                        }else{
                            $dataPrevConclusao = null;
                        }
                        
                        $dataPrevConclusao = ($dataPrevConclusao == null)? "-": $dataPrevConclusao;
                        $tpProcesso = empty($tpProcesso)? "-": $tpProcesso;
                        $grauprioridade = empty($grauprioridade)? "-": $grauprioridade;
                        
                        $html.='<tr id="resultados">
                                    <td width="130px"  class="tdresult" id="resIdDFD"><a href="'.$urlDFD.'" style="text-transform: capitalize">'.$dado->cpldfdnumf.'</a></td>
                                    <td width="55px" class="tdresult" id="resAno">'.$dado->apldfdanod.'</td>
                                    <td width="55px" class="tdresult" id="resCodClasse">'.$cclamscodi.'</td>
                                    <td width="290px" class="tdresult" id="resDescClasse">'.$descclasse.'</td>
                                    <td width="108px" class="tdresult" id="resDataPrevistaConclusao">'.$dataPrevConclusao.'</td>
                                    <td width="114px" class="tdresult" id="resTpProcesso">'.$tpProcesso.'</td>
                                    <td width="75px" class="tdresult" id="resGrauPrioridade">'.$grauprioridade.'</td>
                                    <td width="130px" class="tdresult" id="resSituacao">'.$dado->eplsitnome.'</td>
                        </tr>';
                    }
                        
                }
                $html .= '</tbody></table></td></tr>';
        }
               
        }
        return $html;
    }

    /**
     * Função utilizada em AbaInformacoesDFD.php
     * Busca os anos da DFD para dispor no select da pesquisa
     */
    function getAnosCadastrados(){
        $sql = "SELECT distinct apldfdanod FROM sfpc.tbplanejamentodfd order by apldfdanod ";
                
        $resultado = executarSql($this->conexaoDb, $sql);
        $dadosSelectDFD = array();
        
        while ($resultado->fetchInto($result, DB_FETCHMODE_OBJECT)) {
            $dadosSelectDFD[] = $result;
        }
        
        return $dadosSelectDFD;
    }
    /**
     * Função utilizada em PostDadosBloqueioConsultar.php
     * Busca os anos de liberações para dispor no select da pesquisa.
     */
    function getAnosCadastradosConsultaLiberacao(){
        $sql = "SELECT distinct apllibapca FROM sfpc.TBPLANEJAMENTOLIBERADFD order by apllibapca";
                
        $resultado = executarSql($this->conexaoDb, $sql);
        $dadosSelectDFD = array();
        
        while ($resultado->fetchInto($result, DB_FETCHMODE_OBJECT)) {
            $dadosSelectDFD[] = $result;
        }
        
        return $dadosSelectDFD;
    }
    /**
     * Função utilizada em AbaInformacoesDFD.php
     * Busca os dados da DFD para dispor em tela
     */
    function consultaDFD($cpldfdnumf){
        $sql = "SELECT dfd.*, org.eorglidesc as descorgao, org.aorglicnpj as cnpjorgao, classe.eclamsdesc as descclasse, sitdfd.eplsitnome, usup.eusuporesp 
                FROM sfpc.tbplanejamentodfd as dfd
                inner join sfpc.tborgaolicitante as org on org.corglicodi = dfd.corglicodi
                left join sfpc.tbclassematerialservico as classe on (classe.cclamscodi = dfd.cclamscodi and classe.cgrumscodi = dfd.cgrumscodi) 
                inner join sfpc.tbplanejamentosituacaodfd as sitdfd on sitdfd.cplsitcodi = dfd.cplsitcodi
                inner join sfpc.tbusuarioportal as usup on usup.cusupocodi = dfd.cusupocodi
                where dfd.cpldfdnumf = '$cpldfdnumf'";
                
        $resultado = executarSql($this->conexaoDb, $sql);
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
        
        return $retorno;
    }
    /**
     * Função utilizada em CadManterDFD.php
     * Busca Motivo e confirma se é agupamento
     */
    function consultaDFDAgrupamento($cpldfdsequ){
        $sql = "SELECT cplagdsequ, eplagdmoti
        FROM sfpc.tbplanejamentoagrupamentodfd
        where cpldfdsequ = '$cpldfdsequ'";
                
        $resultado = executarSql($this->conexaoDb, $sql);
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
        
        return $retorno;
    }
    /**
     * Função utilizada em CadAnalisarDFDSelecionar.php
     * Busca os dados da DFD para dispor em tela
     */
    function consultaDFDAnalisar($cpldfdsequ){
        $sql = "SELECT dfd.*, org.eorglidesc as descorgao, org.aorglicnpj as cnpjorgao, classe.eclamsdesc as descclasse, sitdfd.eplsitnome, usup.eusuporesp 
                FROM sfpc.tbplanejamentodfd as dfd
                inner join sfpc.tborgaolicitante as org on org.corglicodi = dfd.corglicodi
                left join sfpc.tbclassematerialservico as classe on (classe.cclamscodi = dfd.cclamscodi and classe.cgrumscodi = dfd.cgrumscodi) 
                inner join sfpc.tbplanejamentosituacaodfd as sitdfd on sitdfd.cplsitcodi = dfd.cplsitcodi
                inner join sfpc.tbusuarioportal as usup on usup.cusupocodi = dfd.cusupocodi
                where dfd.cpldfdsequ = '$cpldfdsequ'";
                
        $resultado = executarSql($this->conexaoDb, $sql);
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);
        
        return $retorno;
    }
    function consultaDFDVinculoBySequ($cpldfdsequ){
        $sql = "SELECT dfd.*, org.eorglidesc as descorgao, org.aorglicnpj as cnpjorgao, classe.eclamsdesc as descclasse, sitdfd.eplsitnome, usup.eusuporesp, vinc.cplvincodi
                FROM sfpc.tbplanejamentodfd as dfd
                inner join sfpc.tborgaolicitante as org on org.corglicodi = dfd.corglicodi
                inner join sfpc.tbplanejamentovinculodfd as vinc on vinc.cpldfdsequ = dfd.cpldfdsequ   
                inner join sfpc.tbclassematerialservico as classe on (classe.cclamscodi = dfd.cclamscodi and classe.cgrumscodi = dfd.cgrumscodi) 
                inner join sfpc.tbplanejamentosituacaodfd as sitdfd on sitdfd.cplsitcodi = dfd.cplsitcodi
                inner join sfpc.tbusuarioportal as usup on usup.cusupocodi = dfd.cusupocodi
                where dfd.cpldfdsequ = '$cpldfdsequ'";

        $resultado = executarSql($this->conexaoDb, $sql);
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);

        return $retorno;
    }
    function consultaDFDVinculoBySequVinc($cpldfdsequ, $cplvincodi){
        $sql = "SELECT dfd.*, org.eorglidesc as descorgao, org.aorglicnpj as cnpjorgao, classe.eclamsdesc as descclasse, sitdfd.eplsitnome, usup.eusuporesp, vinc.cplvincodi
                FROM sfpc.tbplanejamentodfd as dfd
                inner join sfpc.tborgaolicitante as org on org.corglicodi = dfd.corglicodi
                inner join sfpc.tbplanejamentovinculodfd as vinc on vinc.cpldfdsequ = dfd.cpldfdsequ   
                inner join sfpc.tbclassematerialservico as classe on (classe.cclamscodi = dfd.cclamscodi and classe.cgrumscodi = dfd.cgrumscodi) 
                inner join sfpc.tbplanejamentosituacaodfd as sitdfd on sitdfd.cplsitcodi = dfd.cplsitcodi
                inner join sfpc.tbusuarioportal as usup on usup.cusupocodi = dfd.cusupocodi
                where dfd.cpldfdsequ = $cpldfdsequ and vinc.cplvincodi = $cplvincodi";

        $resultado = executarSql($this->conexaoDb, $sql);
        $resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT);

        return $retorno;
    }
    function deleteDFDVinculoBySequ($cpldfdsequ, $cplvincodi){
        $sql = "DELETE FROM sfpc.tbplanejamentovinculodfd    
                where cpldfdsequ = $cpldfdsequ and cplvincodi = $cplvincodi";
        executarSql($this->conexaoDb, $sql);
        
        return;
    }

    /**
     * Função utilizada em AbaHistoricoDFD.php
     * Busca os dados do histórico do DFD para dispor em tela
     */
    function consultaHistorico($cpldfdnumf){
        $sql = "SELECT hist.*, usup.eusuporesp, sitdfd.eplsitnome
                FROM sfpc.tbplanejamentohistoricosituacaodfd as hist 
                inner join sfpc.tbplanejamentodfd as dfd on dfd.cpldfdsequ = hist.cpldfdsequ
                inner join sfpc.tbusuarioportal as usup on usup.cusupocodi = hist.cusupocodi
                inner join sfpc.tbplanejamentosituacaodfd as sitdfd on sitdfd.cplsitcodi = hist.cplsitcodi
                where dfd.cpldfdnumf =  '$cpldfdnumf'";
                $sql .= " ORDER BY hist.tplhsiulat";
        $resultado = executarSql($this->conexaoDb, $sql);
        $dadosHistDFD = array();
        
        while ($resultado->fetchInto($result, DB_FETCHMODE_OBJECT)) {
            $dadosHistDFD[] = $result;
        }
        return $dadosHistDFD;
    }
    /**
     * Função utilizada em AbaHistoricoDFD.php
     * Busca os dados do histórico do DFD para dispor em tela
     */
    function consultaUltimoHistorico($cpldfdnumf){
        $sql = "select hist1.* from sfpc.tbplanejamentohistoricosituacaodfd as hist1
                inner join sfpc.tbplanejamentodfd as dfd on dfd.cpldfdsequ = hist1.cpldfdsequ
                where dfd.cpldfdnumf = '$cpldfdnumf' and
                hist1.tplhsiincl = (select max(hist2.tplhsiincl) 
                                    from sfpc.tbplanejamentohistoricosituacaodfd as hist2 
                                    where hist2.cpldfdsequ = hist1.cpldfdsequ)";
        $resultado = executarSql($this->conexaoDb, $sql);
        $dadosHistDFD = array();
        
        while ($resultado->fetchInto($result, DB_FETCHMODE_OBJECT)) {
            $dadosHistDFD[] = $result;
        }
        return $dadosHistDFD;
    }
    function consultaUltimoHistUsuResp($cpldfdnumf){
        $sql = "select usup.eusuporesp from sfpc.tbplanejamentohistoricosituacaodfd as hist1
                inner join sfpc.tbplanejamentodfd as dfd on dfd.cpldfdsequ = hist1.cpldfdsequ
                inner join sfpc.tbusuarioportal as usup on usup.cusupocodi = hist1.cusupocodi
                where dfd.cpldfdnumf = '$cpldfdnumf' and
                hist1.tplhsiincl = (select max(hist2.tplhsiincl) 
                                    from sfpc.tbplanejamentohistoricosituacaodfd as hist2 
                                    where hist2.cpldfdsequ = hist1.cpldfdsequ)";
                                    
        $resultado = executarSql($this->conexaoDb, $sql);
        $dadosHistDFD = array();
        
        while ($resultado->fetchInto($result, DB_FETCHMODE_OBJECT)) {
            $dadosHistDFD[] = $result;
        }
        return $dadosHistDFD;
    }
    /**
     * Função utilizada em AbaHistoricoDFD.php
     * Busca os dados dos itens do DFD
     */
    function consultaItens($cpldfdnumf){
        $sql = "SELECT item.*,  mat.ematepdesc, serv.eservpdesc 
                FROM sfpc.tbitemplanejamentodfd item
                inner join sfpc.tbplanejamentodfd as dfd on dfd.cpldfdsequ = item.cpldfdsequ
                left join sfpc.tbmaterialportal as mat on mat.cmatepsequ = item.cmatepsequ
                left join sfpc.tbservicoportal as serv on serv.cservpsequ = item.cservpsequ 
                where dfd.cpldfdnumf =  '$cpldfdnumf' order by item.cplitecodi";
                
        $resultado = executarSql($this->conexaoDb, $sql);
        $dadosItensDFD = array();
        
        while ($resultado->fetchInto($result, DB_FETCHMODE_OBJECT)) {
            $dadosItensDFD[] = $result;
        }
        return $dadosItensDFD;
    }
    /**
     * Função utilizada na função updateitensDFD()
     * Busca o maior seq DFD para gerar um novo
     */
    function maxSeqItem($cpldfdsequ){
        $sql = "SELECT max(cplitecodi) from sfpc.tbitemplanejamentodfd where cpldfdsequ = '$cpldfdsequ'";
                
        $resultado = executarSql($this->conexaoDb, $sql);
        $dadosItensDFD = array();
        
        $resultado->fetchInto($result, DB_FETCHMODE_OBJECT);
        return $result->max;
    }
    /**
     * Função utilizada em CadManterDFD.php
     * Deleta os dados do DFD
     */
    function deleteDadosDFD($cpldfdsequ){
        
        //Verificar se o sequ esta presente nas tabelas
        $sqlVerificaNuloHistorico = "select * FROM sfpc.tbplanejamentohistoricosituacaodfd where cpldfdsequ = $cpldfdsequ";
        $resultadohist = executarSql($this->conexaoDb, $sqlVerificaNuloHistorico);
        $resultadohist->fetchInto($resulthist, DB_FETCHMODE_OBJECT);
        
        $sqlVerificarNuloItem = "select * FROM sfpc.tbitemplanejamentodfd where cpldfdsequ = $cpldfdsequ";
        $resultadoItem = executarSql($this->conexaoDb, $sqlVerificarNuloItem);
        $resultadoItem->fetchInto($resultItem, DB_FETCHMODE_OBJECT);

        $sqlVerificarNuloVinculo = "select * FROM sfpc.tbplanejamentovinculodfd where cpldfdsequ = $cpldfdsequ";
        $resultadoVinculo = executarSql($this->conexaoDb, $sqlVerificarNuloVinculo);
        $resultadoVinculo->fetchInto($resultVinculo, DB_FETCHMODE_OBJECT);

        $sqlVerificarNuloDFD = "select * FROM sfpc.tbplanejamentodfd where cpldfdsequ = $cpldfdsequ";
        $resultadoDFD = executarSql($this->conexaoDb, $sqlVerificarNuloDFD);
        $resultadoDFD->fetchInto($resultDFD, DB_FETCHMODE_OBJECT);

        if(!empty($resulthist->cpldfdsequ)){
        $sql = "Delete FROM sfpc.tbplanejamentohistoricosituacaodfd where cpldfdsequ = $cpldfdsequ";
        executarSql($this->conexaoDb, $sql);
        }
        
        if(!empty($resultItem->cpldfdsequ)){
        $sql = "Delete FROM sfpc.tbitemplanejamentodfd where cpldfdsequ = $cpldfdsequ";
        executarSql($this->conexaoDb, $sql);
        }

        if(!empty($resultVinculo->cpldfdsequ)){
            $sql = "Delete FROM sfpc.tbplanejamentovinculodfd where cpldfdsequ = $cpldfdsequ";
            executarSql($this->conexaoDb, $sql);
        }

        if(!empty($resultDFD->cpldfdsequ)){
        $sql = "Delete FROM sfpc.tbplanejamentodfd where cpldfdsequ = $cpldfdsequ";
        executarSql($this->conexaoDb, $sql);
        }
        
        return true;
    }
    /**
     * Função utilizada em CadManterDFD.php
     * Atualiza os dados do DFD na tabela
     */
    function updateDFD($dadosDFD){
        //Monta sql Update DFD
        $sqlPDFD = " update sfpc.tbplanejamentodfd set ";
        
        if($dadosDFD->cclamscodi != null){
            $sqlPDFD .=" cclamscodi = ".$dadosDFD->cclamscodi.", ";
            $sqlPDFD .=" cgrumscodi = ".$dadosDFD->cgrumscodi.", ";
        }
        
        $dadosDFD->epldfddesc = !empty($dadosDFD->epldfddesc)?$dadosDFD->epldfddesc:"";
        $dadosDFD->epldfdjust = !empty($dadosDFD->epldfdjust)?$dadosDFD->epldfdjust:"";
        $dadosDFD->cpldfdvest = !empty($dadosDFD->cpldfdvest)?$dadosDFD->cpldfdvest:"null";
        $dadosDFD->fpldfdtpct = !empty($dadosDFD->fpldfdtpct)?$dadosDFD->fpldfdtpct:"";
        $dadosDFD->fpldfdgrau = !empty($dadosDFD->fpldfdgrau)?$dadosDFD->fpldfdgrau:"";
        $dadosDFD->epldfdjusp = !empty($dadosDFD->epldfdjusp)?$dadosDFD->epldfdjusp:"";

        $dadosDFD->rascunho = ($dadosDFD->rascunho == 1)? 1 : 2;
        $sqlPDFD .=" epldfddesc = '".$dadosDFD->epldfddesc."', ";
        $sqlPDFD .=" epldfdjust = '".$dadosDFD->epldfdjust."', ";
        $sqlPDFD .=" cpldfdvest =  ".$dadosDFD->cpldfdvest.", ";
        $sqlPDFD .=" fpldfdtpct = '".$dadosDFD->fpldfdtpct."', ";
        $sqlPDFD .=" fpldfdgrau = '".$dadosDFD->fpldfdgrau."', ";
        $sqlPDFD .=" epldfdjusp = '".$dadosDFD->epldfdjusp."', ";
        if(!empty($dadosDFD->dpldfdpret) && $dadosDFD->dpldfdpret != '1970-01-01'){
            $sqlPDFD .=" dpldfdpret =  '".$dadosDFD->dpldfdpret."', "; 
        }else{
            $sqlPDFD .=" dpldfdpret =  null, "; 
        }
        $sqlPDFD .=" fpldfdcorp = '".$dadosDFD->fpldfdcorp."', ";
        $sqlPDFD .=" cplsitcodi = ".$dadosDFD->cplsitcodi.", ";
        $sqlPDFD .=" cusupocodi = ".$_SESSION['_cusupocodi_'].", ";
        $sqlPDFD .=" tpldfdulat = now() ";
        if($dadosDFD->chaveNovoCodVinc == true){ //adiciona o codigo do vinculo caso ainda não exista
            $sqlPDFD .=", cplvincodi = '".$dadosDFD->cplvincodi."'";
        }
        $sqlPDFD .=" where cpldfdsequ = ".$dadosDFD->cpldfdsequ;
        //Executa Query
        executarSql($this->conexaoDb, $sqlPDFD);
        
        return true;
    }
    /**
     * Função utilizada em CadManterDFD.php
     * Atualiza os dados dos itens do DFD na tabela
     */
    function updateitensDFD($itensDFD, $cpldfdsequ){
        $sqlItemDelete = "Delete FROM sfpc.tbitemplanejamentodfd where cpldfdsequ = $cpldfdsequ";
                    executarSql($this->conexaoDb, $sqlItemDelete);
        $i=1;
        foreach($itensDFD as $item){
                    
                $cmatepsequ = !empty($item->cmatepsequ)?$item->cmatepsequ : "null" ;
                $cservpsequ = !empty($item->cservpsequ)?$item->cservpsequ : "null" ;
                $sqlInsertItem = "insert into sfpc.tbitemplanejamentodfd (
                                    cpldfdsequ,
                                    cplitecodi,
                                    cmatepsequ,
                                    cservpsequ,
                                    tpliteincl,
                                    cusupocodi,
                                    tpliteulat
                                    ) values(
                                    $cpldfdsequ,
                                $i,
                                    $cmatepsequ,
                                    $cservpsequ,
                                    now(),
                                    ".$_SESSION['_cusupocodi_'].",
                                    now()
                                    )";

                executarSql($this->conexaoDb, $sqlInsertItem);
            $i++;
        }
        
        return true;
    }
    /**
     * Função utilizada em CadManterDFD.php
     * Insere a situação do DFD na tabela tbplanejamentohistoricosituacaodfd
     */
    function insertHistoricoSituacaoDFD($dadosDFD){
        $cplhsisequ = $this->novoSequencialHistoricoSituacao();
        $sqlSituacaoAtual = " SELECT cplsitcodi FROM sfpc.tbplanejamentodfd WHERE cpldfdsequ = $dadosDFD->cpldfdsequ";
        
        $resultado = executarSql($this->conexaoDb, $sqlSituacaoAtual);
        $resultado->fetchInto($result, DB_FETCHMODE_OBJECT);

        $sqlInsert ="insert into sfpc.tbplanejamentohistoricosituacaodfd (
            cplhsisequ, 
            cpldfdsequ, 
            cplsitcodi, 
            tplhsiincl, 
            cusupocodi, 
            tplhsiulat
            ) values(
                $cplhsisequ,
                $dadosDFD->cpldfdsequ,
                $result->cplsitcodi,
                now(),
                ".$_SESSION["_cusupocodi_"].",
                now()
            )
        ";
        
        executarSql($this->conexaoDb, $sqlInsert);

        return true;
    }

    public function montaHTMLConsolidado($dadosDFD)
    {   
        $aux = 0;
        $secretariasDFD = array();
        $posArray = 0;
        for($i=0; $i<count($dadosDFD); $i++){
            if($dadosDFD[$i]->corglicodi != $aux){ // Assume que as secretarias vem agrupadas
                $aux = $dadosDFD[$i]->corglicodi;
                $secretariasDFD[$posArray]->corglicodi = $dadosDFD[$i]->corglicodi;
                $secretariasDFD[$posArray]->eorglidesc = $dadosDFD[$i]->descorgao;
                $posArray++;
            }
        }
        if(empty($dadosDFD)){
            $html = '<tr>
                    <td align="center" bgcolor="#75ADE6" colspan="8" class="titulo3" width="900px">
                            RESULTADO DA PESQUISA
                        </td>
                    </tr>
                    <tr>
                        <td align="left" colspan="8" class="textonormal" width="900px">Pesquisa sem Ocorrências.</td>
                    </tr>';
        }else{
            $html='
                    <tr>
                        <td align="center" bgcolor="#75ADE6" colspan="8" class="titulo3">
                            Resultado da pesquisa
                        </td>
                    </tr>';
            
            foreach($secretariasDFD as $secretaria){
            $html.='<tr><td align="center" bgcolor="#BFDAF2" colspan="8" class="titulo3">'.$secretaria->eorglidesc.'</td></tr>';
            
            $html.='<tr>
                    <td>
                    <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
                    <thead>
                        <tr id="cabecalhos">
                            <td class="tdresult"><input type="checkbox" id="numDFDAll" name="numDFDAll"/><label for="numDFDAll">Selecionar todas</label></td>
                            <td class="tdResultTitulo" id="cabIdDFD">Número do DFD</td>
                            <td class="tdResultTitulo" id="cabAno">Ano do PCA</td>
                            <td class="tdResultTitulo" id="cabCodClasse">Código da Classe</td>
                            <td class="tdResultTitulo" id="cabDescClasse">Descrição da Classe</td>
                            <td class="tdResultTitulo" id="cabDataPrevistaConclusao">Data Prevista para Conclusão</td>
                            <td class="tdResultTitulo" id="cabTpProcesso">Tipo de Processo</td>
                            <td class="tdResultTitulo" id="cabGrauPrioridade">Grau de Prioridade</td>
                            <td class="tdResultTitulo" id="cabSituacao">Situação do DFD</td>
                        </tr>
                    </thead>
                    <tbody>';

            
            $conta = 0;
            foreach($dadosDFD as $dado){
                if($secretaria->corglicodi == $dado->corglicodi){
                $dataPrevConclusao = date('d/m/Y', strtotime($dado->dpldfdpret));
                $tpProcesso = ($dado->fpldfdtpct=="D")? "CONTRATAÇÃO DIRETA" : "LICITAÇÃO";
                
                if($dado->fpldfdgrau == 1){
                    $grauprioridade = "ALTO";
                }else if($dado->fpldfdgrau == 2){
                    $grauprioridade = "MÉDIO";
                }else if($dado->fpldfdgrau == 3){
                    $grauprioridade = "BAIXO";
                }

                
                $html.='<tr id="resultados">
                    <td class="tdresult"><input type="checkbox" class="CBXNumDFD" name="numDFD[]" value="'.$conta.'"/></td>
                    <td class="tdresult" id="resIdDFD">'.$dado->cpldfdnumf.'</td>
                    <td class="tdresult" id="resAno">'.$dado->apldfdanod.'</td>
                    <td class="tdresult" id="resCodClasse">'.$dado->cclamscodi.'</td>
                    <td class="tdresult" id="resDescClasse">'.$dado->descclasse.'</td>
                    <td class="tdresult" id="resDataPrevistaConclusao">'.$dataPrevConclusao.'</td>
                    <td class="tdresult" id="resTpProcesso">'.$tpProcesso.'</td>
                    <td class="tdresult" id="resGrauPrioridade">'.$grauprioridade.'</td>
                    <td class="tdresult" id="resSituacao">'.$dado->eplsitnome.'</td>
                </tr>';
                $conta++;
                }
            }
            $html .= '</tbody></table>
                        <footer>
                            <button type="button" name="consolidarDFD" class="botao" id="consolidarDFD">Consolidar</button>
                            <button type="button" name="exportarPDF" class="botao" id="exportarPDF">Exportar PDF</button>
                            <button type="button" name="exportarXLS" class="botao" id="exportarXLS">Exportar XLS</button>
                            <button type="button" name="exportarCSV" class="botao" id="exportarCSV">Exportar CSV</button>
                        </footer></td></tr>';
            }
        }
        return $html;
    }

    public function montaHTMLConsolidarPDF($dadosDFD)
    {   
        date_default_timezone_set ("America/Recife");
        $hoje = date('d/m/Y H:i');
        $aux = 0;
        $secretariasDFD = array();
        $posArray = 0;
        for($i=0; $i<count($dadosDFD); $i++){
            if($dadosDFD[$i]->corglicodi != $aux){ // Assume que as secretarias vem agrupadas
                $aux = $dadosDFD[$i]->corglicodi;
                $secretariasDFD[$posArray]->corglicodi = $dadosDFD[$i]->corglicodi;
                $secretariasDFD[$posArray]->eorglidesc = $dadosDFD[$i]->eorglidesc;
                $posArray++;
            }
        }
        if(empty($dadosDFD)){
            $html = '<style>
                        .pagenum:before {content: counter(page);}
                        footer .pagenum:before {content: counter(page);}
                        td{
                            font-family: Verdana,sans-serif,Arial;
                        }
                        .font{
                                font-family: Verdana,sans-serif,Arial;
                            
                        }
                        .tdResultTitulo{
                            font-size: 10pt;
                            text-transform: uppercase;
                        }
                        .tdresult{
                            font-size: 8pt;
                        }
                    </style>
                    <div>
                        <table>
                            <tr>
                                <td class ="font"width="475px">Prefeitura do Recife</td>
                                <td align-content="center"><img src="../midia/brasao.jpg" alt=""></td>
                                <td align="right" class="font" width="475px">Portal de Compras</td>
                            </tr>
                        </table>
                    </div>
                    <hr size="3">
                    <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
                    <tbody><tr>
                        <td align="center" colspan="8" class="titulo3" width="900px">Pesquisa sem ocorrências.</td>
                    </tr></tbody></table>';
            $html .= '
                    <footer>
                    <hr size="3">
                    <table width="100%" >
                    <tr>
                    <td width="50%">Emissão: '.$hoje.'</td>
                    <td>
                    <div width="50%" align="right" class="pagenum-container">Página <span class="pagenum"></span></div>
                    </td>
                    </tr>
                    </table>
                    </footer>';
        }else{                       
            $html ='<style>
                        .pagenum:before {content: counter(page);}
                        footer .pagenum:before {content: counter(page);}
                        .tdResultTitulo{
                            font-family: Verdana,sans-serif,Arial;
                            font-size: 8pt;
                        }
                        .tdResultTituloOrg{
                            font-family: Verdana,sans-serif,Arial;
                            font-size: 13pt;
                        }
                        .tdresult{
                            font-family: Verdana,sans-serif,Arial;
                            font-size: 8pt;
                        }
                    </style>
                    <div>
                        <table>
                            <tr>
                                <td width="475px" class="font"><b>Prefeitura do Recife<b></td>
                                <td  align-content="center"><img src="../midia/brasao.jpg" alt=""></td>
                                <td class="font" align="right" width="475px"><b>Portal de Compras<b></td>
                            </tr>
                        </table>
                         <hr size="3">
                        <table>
                            <tr align="center" style = "text-transform="uppercase">
                                <td width="1020px" class="font"><b>PLANEJAMENTO DAS CONTRATAÇÕES - RELATÓRIO DE DOCUMENTO DE FORMALIZAÇÃO DE DEMANDA<b></td>
                            </tr> 
                        </table>
                    </div>';
            foreach($secretariasDFD as $secretaria){
                        $html .='<hr size="3">
                        <table>
                            <tr align="center" style = "text-transform="uppercase" bgcolor="#bfdaf2">
                                <td bgcolor="#D3D3D3" class="tdResultTituloOrg" width="1020px">'.$secretaria->eorglidesc.'</td>
                            </tr> 
                        </table>
                    <hr size="3">
                    <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
                    <thead>
                            <tr id="cabecalhos" bgcolor="#D3D3D3">
                                <td class="tdResultTitulo" id="cabIdDFD">NÚMERO DO DFD</td>
                                <td class="tdResultTitulo" id="cabAno">ANO DO PCA</td>
                                <td class="tdResultTitulo" align="center">CNPJ</td>
                                <td class="tdResultTitulo" id="cabDescClasse">CLASSE</td>
                                <td class="tdResultTitulo" align="center">Estimativa de Valor</td>
                                <td class="tdResultTitulo" id="cabDataPrevistaConclusao">DATA PRESVISTA PARA CONCLUSÃO</td>
                                <td class="tdResultTitulo" id="cabTpProcesso">TIPO DE PROCESSO</td>
                                <td class="tdResultTitulo" id="cabGrauPrioridade">GRAU DE PRIORIDADE</td>
                                <td class="tdResultTitulo" id="cabSituacao">SITUAÇÃO DO DFD</td>
                        </tr>
                    </thead>
                    <tbody>';

            foreach($dadosDFD as $dado){
                    if($secretaria->corglicodi == $dado->corglicodi){
                        $descclasse = empty($dado->eclamsdesc)? "-": $dado->eclamsdesc;
                $dataPrevConclusao = date('d/m/Y', strtotime($dado->dpldfdpret));
                        $tpProcesso = ($dado->fpldfdtpct=="D")? "CONTRATAÇÃO DIRETA" : "LICITAÇÃO";

                if($dado->fpldfdgrau == 1){
                    $grauprioridade = "ALTO";
                }else if($dado->fpldfdgrau == 2){
                    $grauprioridade = "MÉDIO";
                }else if($dado->fpldfdgrau == 3){
                    $grauprioridade = "BAIXO";
                }

                        $cnpj = FormataCNPJ($dado->aorglicnpj);
                $html.='<tr id="resultados">
                    <td class="tdresult" id="resIdDFD">'.$dado->cpldfdnumf.'</td>
                    <td class="tdresult" id="resAno">'.$dado->apldfdanod.'</td>
                                    <td class="tdresult" id="cnpj_result" align="center">'.$cnpj.'</td>
                                    <td class="tdresult" id="resDescClasse">'.$descclasse.'</td>
                                    <td class="tdresult" align="center">R$'.number_format($dado->cpldfdvest, 2, ',', '.').'</td>
                    <td class="tdresult" id="resDataPrevistaConclusao">'.$dataPrevConclusao.'</td>
                                    <td class="tdresult" id="resTpProcesso">'.$tpProcesso.'</td>
                    <td class="tdresult" id="resGrauPrioridade">'.$grauprioridade.'</td>
                    <td class="tdresult" id="resSituacao">'.$dado->eplsitnome.'</td>
                </tr>';
            }
                }
            $html .= '</tbody></table><br>';
            }
            $html .= '
            <footer>
            <hr size="3">
            <table width="100%" >
            <tr>
            <td width="50%">Emissão: '.$hoje.'</td>
            <td>
            <div width="50%" align="right" class="pagenum-container">Página <span class="pagenum"></span></div>
            </td>
            </tr>
            </table>
            </footer>';
        }
        return $html;
    }

    public function getDadosConsolidarDFD($dados)
    {
        
        // fazer dessa fforma vai dar uma sobrecarga na pesquisa, é mais rápido fazer uma sql modulável;
        $sql = "
        SELECT distinct plandfd.*, org.eorglidesc as descorgao, classe.eclamsdesc as descclasse, sitdfd.eplsitnome
        FROM sfpc.tbplanejamentodfd AS plandfd
        inner join sfpc.tborgaolicitante as org on org.corglicodi = plandfd.corglicodi
        inner join sfpc.tbclassematerialservico as classe on (classe.cclamscodi = plandfd.cclamscodi and classe.cgrumscodi = plandfd.cgrumscodi) 
        inner join sfpc.tbplanejamentosituacaodfd as sitdfd on sitdfd.cplsitcodi = plandfd.cplsitcodi
        ";
        
        $dados['selectSitDFD'] = 6;
        $sqlWhere = "where plandfd.cplsitcodi = '".$dados['selectSitDFD']."' and";
        $checaWhere = false;//se for falso nas verificações precisa incluir o where
        if(!empty($dados['selectAreaReq'][0]->corglicodi)){
            if(count($dados['selectAreaReq']) > 1){
                $corglicodi ="";
                $aux = 1;
                
                for($i=0; $i<count($dados['selectAreaReq']); $i++){
                    $corglicodi .= $dados['selectAreaReq'][$i]->corglicodi;
                    if($aux < count($dados['selectAreaReq'])){
                        $corglicodi .= ", ";
                    }
                    $aux++;
                }
                $sqlWhere .= " plandfd.corglicodi in (".$corglicodi.") and";//não pode ter espaço após para garantir na checagem para não faltar where
                $checaWhere = true;
            }

        }else{
            $corglicodi ="";
            $aux = 1;
            for ($i=0; $i < count($dados['selectAreaReq']); $i++){
                $corglicodi .= $dados['selectAreaReq'][$i];
                    if($aux < count($dados['selectAreaReq'])){
                        $corglicodi .= ", ";
                    }
                $aux++;
            }
            $sqlWhere .= " plandfd.corglicodi in (".$corglicodi.") and";//não pode ter espaço após para garantir na checagem para não faltar where
            $checaWhere = true;
            
        }
        //Verifica e implenta se vier o id da DFD
        if(!empty($dados['idDFD'])) {
            $sqlWhere .=" plandfd.cpldfdnumf = '".$dados['idDFD']."'";
        } else { //Caso não venha as outras informações serão tratadas
            if (!empty($dados["cclamscodi"]) && !empty($dados["cgrumscodi"])) {
                if($checaWhere == false){
                    $checaWhere = true;
                }
                $sqlWhere .= " plandfd.cclamscodi = '".$dados['cclamscodi']."' and plandfd.cgrumscodi = '".$dados['cgrumscodi']."' and";
            }
                if (!empty($dados["selectAnoPCA"])) {
                    $sqlWhere .= " plandfd.apldfdanod = '".$dados['selectAnoPCA']."' and";
                }
                
                if (!empty($dados["grauPrioridade"])) {
                    $sqlWhere .= " plandfd.fpldfdgrau = '".$dados['grauPrioridade']."' and";
                }

                if (!empty($dados["descDemanda"])) {
                    $sqlWhere .= " plandfd.epldfddesc = '".$dados['descDemanda']."' and";
                }

                if (!empty($dados["DataIni"])) {
                    $sqlWhere .= " plandfd.tpldfdincl = '".$dados['DataIni']."' and";
                }
                
                if (!empty($dados["DataFim"])) {
                    $sqlWhere .= " plandfd.dpldfdpret = '".$dados['DataFim']."' and";
                }
            
            //limpa o ultimo and para não quebrar a query
        $sqlWhere = substr_replace($sqlWhere, ' ', strrpos($sqlWhere, " and"));
            
            }
        $sql .= $sqlWhere;
        $sql .= " ORDER BY corglicodi, cpldfdsequ ASC";
        
        $resultado = executarSql($this->conexaoDb, $sql);
        $dadosSelectDFD = array();
        $countDFD = 0;
        
        while ($resultado->fetchInto($countDFD, DB_FETCHMODE_OBJECT)) {
            $dadosSelectDFD[] = $countDFD;
        }
        
        
        
        return $dadosSelectDFD;
    }

    function updateDadosConsolidarDFD($seqDFD){
        for($i=0;$i<count($seqDFD);$i++){
            $sqlConsolidado = "UPDATE sfpc.tbplanejamentodfd 
                              set cplsitcodi = 8
                              where cpldfdsequ = ".$seqDFD[$i];
            executarSql($this->conexaoDb, $sqlConsolidado);
        }

        return true;
    }

    /**
     * Função utilizada em ConsSelecionarAtualizarDFD.php
     * Valida os parâmetros para Atualizar o DFD
     */
    public function getDadosAtualizarDFD($dados)
    {
        $sql = "
            SELECT DISTINCT plandfd.*, org.eorglidesc AS descorgao, classe.eclamsdesc AS descclasse, sitdfd.eplsitnome
            FROM sfpc.tbplanejamentodfd AS plandfd
            INNER JOIN sfpc.tborgaolicitante AS org ON org.corglicodi = plandfd.corglicodi
            INNER JOIN sfpc.tbclassematerialservico AS classe ON (classe.cclamscodi = plandfd.cclamscodi AND classe.cgrumscodi = plandfd.cgrumscodi) 
            INNER JOIN sfpc.tbplanejamentosituacaodfd AS sitdfd ON sitdfd.cplsitcodi = plandfd.cplsitcodi
            ";

        $sqlWhere = "";
        $checaWhere = false;
        if (!empty($dados['idDFD'])) {
            if ($checaWhere == false) {
                $sqlWhere .= " AND";
            }
            $sqlWhere .=" plandfd.cpldfdnumf = '".$dados['idDFD']."'";
        }

        if (!empty($dados["selectAnoPCA"])) {
            if ($checaWhere == false) {
                $sqlWhere .= " AND";
            }
            $sqlWhere .= " plandfd.apldfdanod = '".$dados['selectAnoPCA']."'";
        }

        if (!empty($dados['selectAreaReq'])) {
            if ($checaWhere == false) {
                $sqlWhere .= " AND";
            }
            $sqlWhere .= " plandfd.corglicodi = '".$dados['selectAreaReq']."'";
        }

        if (!empty($dados["grauPrioridade"])) {
            if ($checaWhere == false) {
                $sqlWhere .= " AND";
            }
            $sqlWhere .= " plandfd.fpldfdgrau = '".$dados['grauPrioridade']."'";
        }

        if (!empty($dados["cclamscodi"]) && !empty($dados["cgrumscodi"])) {
            if ($checaWhere == false) {
                $sqlWhere .= " AND";
            }
            $sqlWhere .= " classe.cclamscodi = '".$dados['cclamscodi']."' AND classe.cgrumscodi = '".$dados['cgrumscodi']."'";
        }

        if (!empty($dados["descDemanda"])) {
            if($checaWhere == false){
                $sqlWhere .= " AND";
            }
            $sqlWhere .= " plandfd.epldfddesc = '".$dados['descDemanda']."'";
        }

        if (!empty($dados["DataIni"])) {
            if($checaWhere == false){
                $sqlWhere .= " AND";
            }
            $sqlWhere .= " plandfd.tpldfdincl = '".$dados['DataIni']."'";
        }

        if (!empty($dados["DataFim"])) {
            if($checaWhere == false){
                $sqlWhere .= " AND";
            }
            $sqlWhere .= " plandfd.dpldfdpret = '".$dados['DataFim']."'";
        }

        $sql .= " WHERE plandfd.cplsitcodi = 10";
        $sql .= $sqlWhere;
        $sql .= " ORDER BY plandfd.cpldfdsequ ASC";

        $resultado = executarSql($this->conexaoDb, $sql);
        $dadosSelectDFD = array();
        $countDFD = 0;

        while ($resultado->fetchInto($countDFD, DB_FETCHMODE_OBJECT)) {
            $dadosSelectDFD[] = $countDFD;
        }

        return $dadosSelectDFD;
    }

    /**
     * Função utilizada em ConsSelecionarAtualizarDFD.php
     * Pega todos os dados do DFD encontrados e monta o sql para a página
     */
    public function montaHTMLAtualizar($dadosDFD)
    {
        if (empty($dadosDFD)) {
            $html = '<tr>
                        <td align="center" bgcolor="#75ADE6" colspan="8" class="titulo3" width="900px">
                            RESULTADO DA PESQUISA
                        </td>
                    </tr>
                    <tr>
                        <td align="left" colspan="8" class="textonormal" width="900px">Pesquisa sem Ocorrências.</td>
                    </tr>';
        } else {
            $html='
                    <tr>
                        <td align="center" bgcolor="#75ADE6" colspan="8" class="titulo3">
                            Resultado da pesquisa
                        </td>
                    </tr>';

            $html.='<tr><td align="center" bgcolor="#BFDAF2" colspan="8" class="titulo3">'.$dadosDFD[0]->descorgao.'</td></tr>';

            $html.='<tr>
                        <td>
                        <table class="tablePesquisa textonormal" style=" position:relative;  width : 100%; ">
                        <thead>
                            <tr id="cabecalhos">
                                <td class="tdresult"><input type="checkbox" id="numDFDAll" name="numDFDAll"/><label for="numDFDAll">Selecionar todas</label></td>
                                <td class="tdResultTitulo" id="cabIdDFD">Numero do DFD</td>
                                <td class="tdResultTitulo" id="cabAno">Ano do PCA</td>
                                <td class="tdResultTitulo" id="cabCodClasse">Código da Classe</td>
                                <td class="tdResultTitulo" id="cabDescClasse">Descrição da Classe</td>
                                <td class="tdResultTitulo" id="cabDataPrevistaConclusao">Data Prevista para Conclusão</td>
                                <td class="tdResultTitulo" id="cabTpProcesso">Tipo de Processo</td>
                                <td class="tdResultTitulo" id="cabGrauPrioridade">Grau de Prioridade</td>
                                <td class="tdResultTitulo" id="cabSituacao">Situação do DFD</td>
                            </tr>
                        </thead>
                        <tbody>';

            $conta = 0;
            foreach ($dadosDFD as $dado) {
                $dataPrevConclusao = date('d/m/Y', strtotime($dado->dpldfdpret));
                $tpProcesso = ($dado->fpldfdtpct=="D")? "CONTRATAÇÃO DIRETA" : "LICITAÇÃO";
                $urlDFD = "CadAtualizarDFD.php?dfdSelected=$dado->cpldfdnumf";

                if ($dado->fpldfdgrau == 1) {
                    $grauprioridade = "ALTO";
                } elseif ($dado->fpldfdgrau == 2) {
                    $grauprioridade = "MÉDIO";
                } elseif ($dado->fpldfdgrau == 3) {
                    $grauprioridade = "BAIXO";
                }

                $html.='<tr id="resultados">
                        <td class="tdresult"><input type="checkbox" class="CBXNumDFD" name="numDFD[]" value="'.$dado->cpldfdsequ.'"/></td>
                        <td class="tdresult" id="resIdDFD"><a href="'.$urlDFD.'" style="text-transform: capitalize">'.$dado->cpldfdnumf.'</a></td>
                        <td class="tdresult" id="resAno">'.$dado->apldfdanod.'</td>
                        <td class="tdresult" id="resCodClasse">'.$dado->cclamscodi.'</td>
                        <td class="tdresult" id="resDescClasse">'.$dado->descclasse.'</td>
                        <td class="tdresult" id="resDataPrevistaConclusao">'.$dataPrevConclusao.'</td>
                        <td class="tdresult" id="resTpProcesso">'.$tpProcesso.'</td>
                        <td class="tdresult" id="resGrauPrioridade">'.$grauprioridade.'</td>
                        <td class="tdresult" id="resSituacao">'.$dado->eplsitnome.'</td>
                    </tr>';
                $conta ++;
            }
            $html .= '</tbody></table>
                        <footer>
                            <button type="button" name="excluirDFDResultado" class="botao" id="excluirDFDResultado">Excluir</button>
                            <button type="button" name="exportarXLS" class="botao" id="exportarXLS">Exportar XLS</button>
                            <button type="button" name="exportarCSV" class="botao" id="exportarCSV">Exportar CSV</button>
                            <button type="button" name="exportarPDF" class="botao" id="exportarPDF">Exportar PDF</button>
                        </footer></td></tr>';
        }
        return $html;
    }

    /**
     * Função utilizada em PostDadosAtualizarDFD.php
     * Função seta o status 12(ATUALIZADO NO ANO DE EXECUÇÃO) e não deleta o DFD conforme regra de negócio
     */
    public function updateManterDFD($seqDFD)
    {
        $sql = "UPDATE sfpc.tbplanejamentodfd
                SET cplsitcodi = 12 
                WHERE cpldfdsequ = ".$seqDFD;

        executarSql($this->conexaoDb, $sql);
    }

    /**
     * Função utilizada em PostDadosAtualizarDFD.php
     * Função seta o status 13(EXCLUÍDO NO ANO DE EXECUÇÃO) e não deleta o DFD conforme regra de negócio
     */
    public function updateExcluirDFD($seqDFD)
    {
        $sql = "UPDATE sfpc.tbplanejamentodfd
                SET cplsitcodi = 13 
                WHERE cpldfdsequ = ".$seqDFD;

        executarSql($this->conexaoDb, $sql);
    }
    /**
     * Função utilizada em PostDadosBloqueioIncluir.php
     * Função Insere os dados do Periodo de Bloqueio de DFD 
     */
    public function IserirBloqPeriodo($dados)
    {
        $SqlNovoSequ = "SELECT max(cplblosequ) from sfpc.TBPLANEJAMENTOBLOQUEIODFD"; 
        $resultado = executarSql($this->conexaoDb, $SqlNovoSequ);
        $resultado->fetchInto($result, DB_FETCHMODE_OBJECT);
        $CPLBLOSEQU = $result->max +1;

        $sql = "Insert into sfpc.TBPLANEJAMENTOBLOQUEIODFD (
                CPLBLOSEQU, 
                APLBLOAPCA, 
                DPLBLODINI, 
                DPLBLODFIM, 
                CUSUPOCODI, 
                TPLBLOULAT 
                )values(
                $CPLBLOSEQU, 
                ".$dados['APLBLOAPCA'].", 
                ".$dados['DPLBLODINI'].", 
                ".$dados['DPLBLODFIM'].", 
                ".$_SESSION['_cusupocodi_'].", 
                now()
                )";
                
        executarSql($this->conexaoDb, $sql);
        
        return true;
    }
    /**
     * Função utilizada em PostDadosBloqueioLiberar.php
     * Função Insere os dados do Periodo de Bloqueio de DFD 
     */
    public function IserirLibPeriodo($dados)
    {
        $SqlNovoSequ = "SELECT max(CPLLIBSEQU) from sfpc.TBPLANEJAMENTOLIBERADFD "; 
        $resultado = executarSql($this->conexaoDb, $SqlNovoSequ);
        $resultado->fetchInto($result, DB_FETCHMODE_OBJECT);
        $CPLLIBSEQU = $result->max +1;

        $sql = "Insert into sfpc.tbplanejamentoliberadfd (
                CPLLIBSEQU, 
                APLLIBAPCA, 
                CORGLICODI, 
                DPLLIBDINI, 
                DPLLIBDFIM, 
                CUSUPOCODI, 
                TPLLIBULAT 
                )values(
                $CPLLIBSEQU, 
                ".$dados['APLLIBAPCA'].", 
                ".$dados['CORGLICODI'].", 
                ".$dados['DPLLIBDINI'].", 
                ".$dados['DPLLIBDFIM'].", 
                ".$_SESSION['_cusupocodi_'].", 
                now()
                )";
                
        executarSql($this->conexaoDb, $sql);
        
        return true;
    }
    /**
     * Função utilizada em PostDadosBloqueioLiberar.php
     * Função busca os dados do Periodo de Bloqueio de DFD para garantir que o registro já existe ou não, para decisão de inclusão ou update
     */
    public function existeBloqueio($dados)
    {
        $sql = "SELECT cplblosequ from sfpc.tbplanejamentobloqueiodfd where APLBLOAPCA = ".$dados['APLBLOAPCA']; 
        $resultado =  executarSql($this->conexaoDb, $sql);
        $resultado->fetchInto($result, DB_FETCHMODE_OBJECT);
        
        return $result;
    }
    /**
     * Função utilizada em PostDadosBloqueioLiberar.php 
     * Função busca os dados do Periodo de Liberação do Bloqueio de DFD para garantir que o registro já existe ou não, para decisão de inclusão ou update
     */
    public function existeLiberacao($dados)
    {
        $sql = "SELECT cpllibsequ from sfpc.TBPLANEJAMENTOLIBERADFD where APLLIBAPCA = ".$dados['APLLIBAPCA']." and CORGLICODI = ".$dados['CORGLICODI']; 
        $resultado =  executarSql($this->conexaoDb, $sql);
        $resultado->fetchInto($result, DB_FETCHMODE_OBJECT);
        
        return $result;
    }
    /**
     * Função utilizada em PostDadosBloqueioLiberar.php
     * Função busca os dados do Periodo de Bloqueio de DFD
     */
    public function SelectBloqPeriodo($ano)
    {
        $sql = "SELECT * from sfpc.tbplanejamentobloqueiodfd where APLBLOAPCA = $ano"; 
        
        $resultado =  executarSql($this->conexaoDb, $sql);
        $resultado->fetchInto($result, DB_FETCHMODE_OBJECT);
        
        return $result;
    }
    /**
     * Função utilizada em PostDadosBloqueioLiberar.php
     * Função busca os dados do Periodo de Liberação de Bloqueio de DFD 
     */
    public function SelectLiberacPeriodo($ano, $area)
    {
        $sql = "SELECT * from sfpc.TBPLANEJAMENTOLIBERADFD where APLLIBAPCA = $ano and CORGLICODI = $area"; 
        
        $resultado =  executarSql($this->conexaoDb, $sql);
        $resultado->fetchInto($result, DB_FETCHMODE_OBJECT);
        
        return $result;
    }
    /**
     * Função utilizada em PostDadosBloqueioLiberar.php
     * Função Atualiza os dados do Periodo de Bloqueio de DFD 
     */
    public function updateBloqPeriodo($dados)
    {
        $sql = "UPDATE sfpc.TBPLANEJAMENTOBLOQUEIODFD
                SET
                    DPLBLODINI = ".$dados['DPLBLODINI'].",
                    DPLBLODFIM = ".$dados['DPLBLODFIM'].",
                    CUSUPOCODI = ".$_SESSION['_cusupocodi_'].",
                    TPLBLOULAT = now()
                WHERE
                    CPLBLOSEQU = ".$dados['CPLBLOSEQU']; 
        
        executarSql($this->conexaoDb, $sql);
        return ;
    }
    /**
     * Função utilizada em PostDadosBloqueioLiberar.php
     * Função atualiza os dados da liberação do Periodo de Bloqueio de DFD 
     */
    public function updateLibPeriodo($dados)
    {
        $sql = "UPDATE sfpc.tbplanejamentoliberadfd 
                    SET 
                        DPLLIBDINI = ".$dados['DPLLIBDINI'].", 
                        DPLLIBDFIM = ".$dados['DPLLIBDFIM'].", 
                        CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", 
                        TPLLIBULAT = now()
                    WHERE CPLLIBSEQU = ".$dados['cpllibsequ']." and  CORGLICODI = ".$dados['CORGLICODI']; 
        
        executarSql($this->conexaoDb, $sql);
        return ;
    }
    /**
     * Função utilizada em PostDadosBloqueioConsultar.php
     * Função busca os dados do Periodo de Liberação de Bloqueio de DFD conforme solicitado pelo usuario
     */
    public function getDadosLiberacao($ano, $area)
    {
        $sql = "SELECT * , org.eorglidesc from sfpc.TBPLANEJAMENTOLIBERADFD as lib
                inner join sfpc.tborgaolicitante as org on(lib.corglicodi = org.corglicodi)";

        if(!is_null($ano) || !is_null($area)){
           $sql .= " where ";
        }
        if(!is_null($ano)){
            $sql .= " lib.APLLIBAPCA = $ano ";
        }
        if(!is_null($area)){
            $sql .= " and lib.CORGLICODI = $area"; 
        }
        $sql .= " and lib.dpllibdini IS NOT NULL AND lib.dpllibdfim IS NOT NULL order by lib.CORGLICODI, lib.APLLIBAPCA";

        $resultado =  executarSql($this->conexaoDb, $sql);
        
        $dados = array();
        while($resultado->fetchInto($result, DB_FETCHMODE_OBJECT)) {
            $dados[] = $result;
        }
        return $dados;
    }
    /**
     * Função utilizada em Incluir, Manter e Encaminhar DFD
     * Função busca os dados do Periodo de Liberação de Bloqueio de DFD conforme solicitado pelo usuario
     */
    public function checaLiberacao($ano, $area)
    {
        $sql = "SELECT * from sfpc.TBPLANEJAMENTOLIBERADFD where APLLIBAPCA = $ano and CORGLICODI = $area";
        
        $resultado =  executarSql($this->conexaoDb, $sql);
        
        $resultado->fetchInto($result, DB_FETCHMODE_OBJECT);
        
        return $result;
    }
     /**
     * Função utilizada em Incluir, Manter e Encaminhar DFD 
     * Função busca os dados do Periodo de Bloqueio de DFD conforme solicitado pelo usuario
     */
    public function checaBloqueio($ano)
    {
        $sql = "SELECT * from sfpc.tbplanejamentobloqueiodfd where APLBLOAPCA = $ano";          
        
        $resultado =  executarSql($this->conexaoDb, $sql);
       
        $resultado->fetchInto($result, DB_FETCHMODE_OBJECT);
        
        return $result;
        
    }
     /**
     * Função utilizada em Incluir DFD 
     * Função checa se o orgão é um Fundo
     */
    public function checaFundo($corglicodi)
    {
        $sql = "SELECT eorglidesc from sfpc.tborgaolicitante where corglicodi = $corglicodi";          
        
        $resultado =  executarSql($this->conexaoDb, $sql);
       
        $resultado->fetchInto($result, DB_FETCHMODE_OBJECT);
        $descOrgao = $result->eorglidesc;
        $palavras = explode(" ", $descOrgao);
        $verificador = strtolower($palavras[0]) === "fundo";
        return ($verificador == true)? false: true;
    }
    /**
     * Função utilizada em PostDadosBloqueioConsultar.php
     * Função monta a tabela de resultado usando os dados do Periodo de Liberação de Bloqueio de DFD conforme solicitado pelo usuario
     */
    public function montaHTMLBloqConsulta($dados)
    {   
        // $aux = 0;
        // $secretariasDFD = array();
        // $posArray = 0;
        // for($i=0; $i<count($dados); $i++){
        //     if($dados[$i]->corglicodi != $aux){ // Assume que as secretarias vem agrupadas
        //         $aux = $dados[$i]->corglicodi;
        //         $secretariasDFD[$posArray]->corglicodi = $dados[$i]->corglicodi;
        //         $secretariasDFD[$posArray]->eorglidesc = $dados[$i]->descorgao;
        //         $posArray++;
        //     }
        // }
        if(empty($dados)){
            $html = '<tr>
                    <td align="center" bgcolor="#75ADE6" colspan="8" class="titulo3" width="900px">
                            RESULTADO DA PESQUISA
                        </td>
                    </tr>
                    <tr>
                        <td align="left" colspan="8" class="textonormal" width="900px">Pesquisa sem Ocorrências.</td>
                    </tr>';
        }else{
            $html='
                    <tr>
                        <td align="center" bgcolor="#75ADE6" colspan="10" class="titulo3">
                            Resultado da pesquisa
                        </td>
                    </tr>';
            // foreach($secretariasDFD as $secretaria){
            
                // $html.='<tr><td align="center" bgcolor="#BFDAF2" colspan="8" class="titulo3">'.$secretaria->eorglidesc.'</td></tr>';
            
                $html.='<tr>
                    <td>
                    <table class="tablePesquisa textonormal" style=" position:relative;  width : 900px; ">
                    <thead>
                        <tr id="cabecalhos">
                                    <td class="tdResultTitulo" id="cabAno">Ano do PCA</td>
                                    <td class="tdResultTitulo" id="cabAreaReq">Área Requisitante</td>
                                    <td class="tdResultTitulo" id="cabDataIni">Data Início da Liberação</td>
                                    <td class="tdResultTitulo" id="cabDataFim">Data Fim da Liberação</td>
                        </tr>
                    </thead>
                    <tbody>';

                    
                foreach($dados as $dado){
                    // if($secretaria->corglicodi == $dado->corglicodi){
                        if(!is_null($dado->dpllibdini)){
                            $dataIni = date('d/m/Y', strtotime($dado->dpllibdini));
                        }else{
                            $dataIni = "";
                        }
                        if(!is_null($dado->dpllibdfim)){
                            $dataFim = date('d/m/Y', strtotime($dado->dpllibdfim));
                        }else{
                            $dataFim = "";
                        }
                        
                        
                        
                        $html.='<tr id="resultados">
                                        <td class="tdresult" id="resAno">'.$dado->apllibapca.'</td>
                                        <td class="tdresult" id="resAreaReq">'.$dado->eorglidesc.'</td>
                                        <td class="tdresult" id="resDataIni">'.$dataIni.'</td>
                                        <td class="tdresult" id="resDataFim">'.$dataFim.'</td>
                        </tr>';
                    // }
                
                }
                $html .= '</tbody></table></td></tr>';
            // }

        }
        return $html;
    }
    
}
?>
