<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAditivoAlterar.php
# Autor:    Edson Dionisio
# Data:     07/07/2020
# Objetivo: Programa de alterar Aditivo
#-------------------------------------------------------------------------
# Autor:    Madson Felix
# Data:     28/04/2021
# CR #246939
# -------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 22/07/2021
# Objetivo: CR #251265
#---------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 10/09/2021
# Objetivo: CR #253338
#---------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 26/11/2021
# Objetivo: CR #251985
#---------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 14/12/2021
# Objetivo: CR #251985
#---------------------------------------------------------------------------
# Alterado : Lucas Vicente
# Data: 09/05/2022
# Objetivo: CR #263119
#---------------------------------------------------------------------------
# Alterado : Lucas Vicente
# Data: 16/05/2022
# Objetivo: CR #263182
#---------------------------------------------------------------------------
# Alterado : Lucas Vicente
# Data: 24/05/2022
# Objetivo: CR #263119
#---------------------------------------------------------------------------
# Autor:    João Madson Felix
# Data:     04/11/2022
# CR #274878
# -------------------------------------------------------------------------

require_once "./ClassAditivoGeral.php";
require_once "./ClassAditivo.php";

session_start();
Seguranca();

# Exibe Aba Membro de Comissão - Formulário A #
$cusupocodi = $_SESSION['_cusupocodi_'];

$ObjClassAditivoGeral = new ClassAditivoGeral();
$ObjClassAditivo = new ClassAditivo();

$dadosContratos = (object) array();

$codcontrato = $_GET['codcontrato'];
$registro = $_GET['rg'];

$contrato = $ObjClassAditivo->getContrato($codcontrato);

$valor_globasl_aditivo = $contrato[0]->vctrpcglaa;
$empresa = $contrato[0]->ectrpcnumf;
$documento = $aditivo->cdocpcsequ;

$mes_dia = $contrato[0]->cctrpcopex;

$tipos_aditivo = $ObjClassAditivo->getTiposAditivo();
$aditivo = $ObjClassAditivo->getAditivoContrato($codcontrato, $registro);
$fornAdit = $ObjClassAditivo->getFornecedorAditivo($aditivo->aaditinuad, $codcontrato);

$situacaoAditivo = $ObjClassAditivo->situacaoAditivo();

$justificativa = $aditivo->xaditijust;
$registro_base = $aditivo->aaditinuad;
$situacao = $aditivo->esitdcdesc;

$data_inicio_vigencia = $aditivo->daditiinvg;
if(EMPTY($data_inicio_vigencia)){
    $data_inicio_vigencia = "";
}
//var_dump($data_inicio_vigencia);die;
$data_fim_vigencia = $aditivo->daditifivg;
$data_fim = $aditivo->data_fim;
$data_inicio_execucao = $aditivo->daditiinex;
$data_termino_execucao = $aditivo->daditifiex;

$valor_aditivo = $aditivo->vaditivalr;
$prazo = $aditivo->aaditiapea;
$tipo_aditivo = $aditivo->ctpadisequ;
//var_dump($tipo_aditivo);//die;
// var_dump($data_termino_execucao, $data_fim_execucao);die;
//var_dump($aditivo);die;
$se_alteracao_prazo = $aditivo->faditialpz;
$se_alteracao_valor = $aditivo->faditialvl;
$tipo_alteracao_valor = $aditivo->cadititalv;

$valor_retroativo = $aditivo->vaditireqc;
$valor_total_aditivo = $aditivo->vaditivtad;

$observacao = $aditivo->xaditiobse;
$nome_representante = $aditivo->naditinmrl;

$cargo_representante = $aditivo->eaditicgrl;
$cpf_representante = $aditivo->eaditicpfr;
$rg_representante = $aditivo->eaditiidrl;
$orgao_exp_representante = $aditivo->naditioerl;
$uf_nat_representante = $aditivo->naditiufrl;
$cidade_representante = $aditivo->naditicdrl;
$estado_representante = $aditivo->naditiedrl;
$nacionalidade_representante = $aditivo->naditinarl;
$estado_civil_representante = $aditivo->caditiecrl;
$profissao_representante = $aditivo->naditiprrl;
$email_representante = $aditivo->naditimlrl;
$telefone_representante = $aditivo->eadititlrl;
$data_fim_execucao = $aditivo->data_fim;
$fase = $aditivo->fase;
// var_dump($aditivo);die;

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    unset($_SESSION['dadosTabela']);
}

unset($_SESSION['documento_anexo_incluir']);
if(!empty($_SESSION['documento_anexo_incluir'])){
    foreach($_SESSION['documento_anexo_incluir'] as $doc){
        $DadosDocAnexo[]  =  (object) array(
                                    'nomearquivo'       => $doc['nome_arquivo'],
                                    'arquivo'           => $doc['arquivo'],
                                    'sequdocumento'     => $doc['sequdoc'],
                                    'sequdocanexo'      => $doc['sequarquivo'],
                                    'datacadasarquivo'  => $doc['data_inclusao'],
                                    'usermod'           => $doc['usermod'],
                                    'ativo'             => 'S'
                                );
    }
}else{
    unset($_SESSION['documento_anexo_incluir']);
    $DadosDocAnexo = $ObjClassAditivo->GetDocumentosAnexos($registro);
    foreach($DadosDocAnexo as $doc){
        echo  $doc->sequdocanexo;
        $_SESSION['documento_anexo_incluir'][]  =  array(
                                    'nome_arquivo'       =>$doc->nomearquivo,
                                    'arquivo'           => $doc->arquivo,
                                    'sequdoc'     => $doc->sequdocumento,
                                    'sequarquivo'     => $doc->sequdocanexo,
                                    'data_inclusao'  => $doc->datacadasarquivo,
                                    'usermod'           => $doc->usermod,
                                    'ativo'             => 'S'
                                );
    }
} 
$dadosAnexo = $_SESSION['documento_anexo_incluir'];
$obejtoLen = strlen($_POST['objeto']);
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $Botao = $_POST['Botao'];

    if(!empty($_SESSION['codigo_forn'])){
        $_SESSION['dadosContratado']  = $ObjClassAditivoGeral->getFornecedorDados($_SESSION['codigo_forn']);
        $dadosSalvar['AFORCRSEQU'] = $_SESSION['codigo_forn'];
    }

    $orgLicitante = $_SESSION['dadosObjOrgao']->eorglidesc; //Usar para mostrar na tela qual deles é e para a Masc
    $objetoDesc   = $_SESSION['dadosObjOrgao']->esolcoobje;    
    $razSocial    = $_SESSION['dadosContratado']->nforcrrazs;
    $Rua          = $_SESSION['dadosContratado']->eforcrlogr;
    $numEnd       = $_SESSION['dadosContratado']->aforcrnume;
    $complEnd     = $_SESSION['dadosContratado']->eforcrcomp;
    $Bairro       = $_SESSION['dadosContratado']->eforcrbair;
    $UF           = $_SESSION['dadosContratado']->cforcresta;
    $Cidade       = $_SESSION['dadosContratado']->nforcrcida;

    $_SESSION['Botao'] = $_POST['Botao'];
    
}


$tipoadt = $_POST['tipo_aditivo'];
$CNPJ_CPF            = $_POST['CNPJ_CPF'];


if($tipo_aditivo != 13){
    $RazaoSocial = '';
    $logradouro = '';
    $compl = '';
    $bairro = '';
    $cidade = '';
    $estado = '';
    $cpf = '';
    $CnpjCpf_forn = '';
}else{
    $RazaoSocial = $fornAdit->razao;
    $logradouro = $fornAdit->rua;
    $compl = $fornAdit->complemento;
    $bairro = $fornAdit->bairro;
    $cidade = $fornAdit->cidade;
    $estado = $fornAdit->estado;
    $numero = $fornAdit->numero; 
    $_SESSION['dadosContratado']  = $ObjClassAditivoGeral->getFornecedorDados($fornAdit->seqfornecedor);

    
    
    if(!empty($fornAdit->cnpj)){
        $CnpjCpf_forn = $ObjClassAditivo->mask($fornAdit->cnpj, '##.###.###/####-##');
        $CnpjCpf = $CnpjCpf_forn;
    }

    if(!empty($fornAdit->cpf)){
        $CnpjCpf_forn = $ObjClassAditivo->mask($fornAdit->cpf, '###.###.###-##');
        $CnpjCpf = $CnpjCpf_forn;
    }

}


if ($_POST['CnpjCpf'] != "") {
    if ($CNPJ_CPF == 2) {
        $CnpjCpf = str_replace('.', '', str_replace('-', '', $_POST['CnpjCpf']));
       
    } else {
        $CnpjCpf = str_replace('.', '', str_replace('-', '', str_replace('/', '', $_POST['CnpjCpf'])));
       
    }
} else {
    $CnpjCpf = $_POST['CnpjCpf'];
}

$Mensagem = "Informe: ";

if ($Botao == "Verificar" || $_POST['CnpjCpf'] != "") {

	$Mens     = 0;
	
	if ($CNPJ_CPF == "") {
		if ($Mens == 1) {
			$Mensagem.=", ";
		}
		
		$Mens      = 3;
		$Tipo      = 2;
		$Mensagem .= "A opção CNPJ ou CPF";
        $verificaTipoFornecedor = true;
	} else {
		if ($CNPJ_CPF == 1) {
			$TipoDocumento = "Contratado";
		} else {
			$TipoDocumento = "Contratado";
		}
		
		if ($CnpjCpf == "") {
			$RazaoSocial = null;
			
			if ($Mens == 1) {
				$Mensagem.=", ";
			}
			
			$Mens      = 1;
			$Tipo      = 2;
			$Mensagem .= "<a href=\"javascript:document.CadAditivoAlterar.CnpjCpf.focus();\" class=\"titulo2\">$TipoDocumento</a>";
		} else {
			if ($CNPJ_CPF == 1) {
               // $strCPF = $CnpjCpf.str_replace($CnpjCpf,'.','').str_replace($CnpjCpf,'-','').str_replace($CnpjCpf,'/','');
				$valida_cnpj = valida_CNPJ($CnpjCpf);
                
				if ($valida_cnpj === false) {
					$RazaoSocial = null;
					
					if ($Mens == 1) {
						$Mensagem.=", ";
					}
					$verificaTipoFornecedor = true;
					$Mens      = 3;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.CadAditivoAlterar.CnpjCpf.focus();\" class=\"titulo2\">CNPJ Válido</a>";
				}
			} else {
				$valida_cpf = valida_CPF($CnpjCpf);
				
				if ($valida_cpf === false) {
					$RazaoSocial = null;
					$verificaTipoFornecedor = true;
					$Mens      = 3;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.CadAditivoAlterar.CnpjCpf.focus();\" class=\"titulo2\">CPF Válido</a>";
				}
			}
		}
	
        
        if ($CNPJ_CPF == "") {
            $RazaoSocial = null;
            
            if ($Mens == 1) {
                $Mensagem.=", ";
            }
            
            $Mens      = 3;
            $Tipo      = 2;
            $Mensagem .= "A opção CNPJ ou CPF";
        } else {
            if ($CNPJ_CPF == 1) {
                $TipoDocumento = "Contratado";
            } else {
                $TipoDocumento = "Contratado";
            }

            if (($CNPJ_CPF == 1 and $valida_cnpj === true) or ($CNPJ_CPF == 2 and $valida_cpf === true)) {
                # Verifica se o CNPJ Existe no cadastro de Fornecedores Credenciados #
                $db = Conexao();
                
                $sql  = "SELECT AFORCRSEQU, NFORCRRAZS, AFORCRCCGC, CCEPPOCODI, EFORCRLOGR, AFORCRNUME, EFORCRCOMP, EFORCRBAIR, NFORCRCIDA, CFORCRESTA, AFORCRCDDD, AFORCRTELS, AFORCRCCPF FROM SFPC.TBFORNECEDORCREDENCIADO ";
                $sql .= " WHERE ";
                    
                    if ($CNPJ_CPF == 1) {
                        $sql .= " AFORCRCCGC = '$CnpjCpf' ";
                    } else {
                        $sql .= " AFORCRCCPF = '$CnpjCpf' ";
                    }
                $_SESSION['CNPJCPF'] = $CnpjCpf;
                $res  = $db->query($sql);
                
                if (db::isError($res)) {
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                } else {
                    $rows = $res->numRows();
                    
                    if ($rows > 0) {
                        $linha = $res->fetchRow();
                        $RazaoSocial = $linha[1];
                        $_SESSION['razao_social'] = $RazaoSocial;
                        $codFornecedor = $linha[0];
                        $_SESSION['codigo_forn'] = $codFornecedor;
                        $verificaTipoFornecedor  = false;
                        if(!empty($linha[12])){
                            $CnpjCpf_forn = preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $linha[12]);
                            $cpfCnpj = $linha[12];
                            $_SESSION['cpfForn'] = $linha[12];
                            $verificaTipoFornecedor  = true;
                        }else{
                            $CnpjCpf_forn = preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $linha[2]);
                            $cpfCnpj = $linha[2];
                            $_SESSION['cnpjForn'] = $linha[2];
                            $verificaTipoFornecedor  = true;
                        }     
                        $cep = $linha[3];
                        $logradouro = $linha[4];
                        $numero = $linha[5];
                        $compl = $linha[6];
                        $bairro = $linha[7];
                        $cidade = $linha[8];
                        $estado = $linha[9];
                        //$ddd = $linha[10];
                    } else {
                        if ($Mens == 1) {
                            $Mensagem.=", ";
                        }
                        
                        $Mens     = 3;
                        $Tipo     = 1;
                        $Mensagem = "Fornecedor Não Cadastrado no SICREF";
                        $verificaTipoFornecedor = true;
                        echo '2' ;
                    }
                }
            }

        }
	}
}





$teste = $_SESSION['dadosTabela'];

?>
<html>
<?php

$dadosTipoCompra = $ObjClassAditivoGeral->ListTipoCompra();
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>​
<script language="javascript" type="">
        function TestaCPF(strCPF) {
            let Soma;
            let Resto;
            Soma = 0;
            strCPF = strCPF.replace(/[^\d]+/g,'');
            if (strCPF == "00000000000") return false;
            if (strCPF == "11111111111") return false;
            if (strCPF == "22222222222") return false;
            if (strCPF == "33333333333") return false;
            if (strCPF == "44444444444") return false;
            if (strCPF == "55555555555") return false;
            if (strCPF == "66666666666") return false;
            if (strCPF == "77777777777") return false;
            if (strCPF == "88888888888") return false;
            if (strCPF == "99999999999") return false;
                
            for (i=1; i<=9; i++) Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (11 - i);
            Resto = (Soma * 10) % 11;
            
                if ((Resto == 10) || (Resto == 11))  Resto = 0;
                if (Resto != parseInt(strCPF.substring(9, 10)) ) return false;
            
            Soma = 0;
                for (i = 1; i <= 10; i++) Soma = Soma + parseInt(strCPF.substring(i-1, i)) * (12 - i);
                Resto = (Soma * 10) % 11;
            
                if ((Resto == 10) || (Resto == 11))  Resto = 0;
                if (Resto != parseInt(strCPF.substring(10, 11) ) ) return false;
                return true;
        }

        function AbreJanela(url,largura,altura) {
            window.open(url,'detalhe','status=no,scrollbars=yes,left=70,top=130,width='+largura+',height='+altura);
        }

        function limpaMensagem(){
            $("#tdmensagem").hide();
            $("#tdmensagemM").hide();
        }

        function aviso(mensagem){
            $("#tdmensagem").show();
            $('html, body').animate({scrollTop:0}, 'slow');
            $(".mensagem-texto").html(mensagem);
        }

        function avisoModal(mensagem){
            $("#tdmensagemM").show();
            $('html, body').animate({scrollTop:0}, 'slow');
            $(".mensagem-textoM").html(mensagem);
        }

        function Submete(Destino){
            document.CadAditivoIncluir.Destino.value = Destino;
            document.CadAditivoIncluir.submit();
        }        
       
        function Subirarquivo(){
            window.top.frames['frameArquivo'].subirArquivo();
        }

        function verificaContrato(){
            const contrato = $("#numcontrato").val();
            let retorno = true;
            if(contrato !=""){
                $.post("postDadosAditivo.php", {op:"VerificaSeTemNumeroContrato",'numcon':contrato}, function(data){
                    const objJson = JSON.parse(data);
                    if(objJson.status == false){
                        aviso(objJson.msm);
                        retorno = false;
                    }else{
                        limpaMensagem();
                        retorno =  true;
                    }
                });
            }else{
                retorno =false;
            }
            return retorno;
        }

        function calculater(data_ini, prazo) {
            
            var datainicial = data_ini; //document.getElementById("data").value;
            var dias = parseInt(prazo); //parseInt(document.getElementById("dias").value);
            var partes = datainicial.split("/");
            
            var dia = parseInt(partes[0]); // 02
            var mes = partes[1]-1; //01
            var ano = partes[2]; // 2020

            datainicial = new Date(ano,mes,dia);
  //          console.log(dias);
            datafinal = new Date(datainicial);
//console.log(datafinal);
            datafinal.setDate(datafinal.getDate() + dias);
            var dd = ("0" + datafinal.getDate()).slice(-2);
            var mm = ("0" + (datafinal.getMonth()+1)).slice(-2);
            var y = datafinal.getFullYear();

            var dataformatada = dd + '/' + mm + '/' + y; //y + '-' + mm + '-' + dd;
            return dataformatada;
//            console.log(dataformatada);
            //document.getElementById('datafin').value = dataformatada;
        }


        function somarDataExecucaofinal(){
            var tipo_dias = "<?php echo $mes_dia; ?>";

            var selecionado = $("input[name='alteracao_prazo']:checked").val();
            
            if(selecionado == 'SIM'){
                $("#prazo").prop("disabled", false);

                var prazo = $("#prazo").val();
                
                if(prazo == null  || prazo == '' || prazo == 'undefined'){
                    aviso("Informe: Acréscimo de prazo.");
                    $("#prazo").focus();
                    return false;
                }
            }else if(selecionado == 'NAO'){
                $("#prazo").prop("disabled", true);
            }

            var def;
            def = $("#execucaoDataInicio").val();
            
            dataExecucaoFim    = def.split('/');
            
            //novaData       = new Date(parseInt(dataExecucaoFim[2]),parseInt(dataExecucaoFim[1]-1),parseInt(dataExecucaoFim[0]));
            
            var datafinalExecucaoConvertida = '';

            if(tipo_dias == 'M'){
                var somadias = parseInt(dataExecucaoFim[1]-1) + parseInt(prazo);
                novaDataFim = new Date(parseInt(dataExecucaoFim[2]),somadias, parseInt(dataExecucaoFim[0]));

                var dia = novaDataFim.getDate();
                var mes = (novaDataFim.getMonth() + 1);
                var ano = novaDataFim.getFullYear();

                let todayDay;
                if (dia < 10) {
                    todayDay = '0' + dia;
                } else {
                    todayDay = dia.toString();
                }

                let todayMonth;
                if (mes < 10) {
                    todayMonth = '0' + mes;
                } else {
                    todayMonth = mes.toString();
                }


                var data_final = '<?php echo $data_fim; ?>';
                var data_ini_alt = '<?php echo $data_inicio_execucao; ?>';
                datafinalExecucaoConvertida = todayDay + '/' + todayMonth + '/' + ano;

            }else{
                var retorno = calculater(def, prazo);
                datafinalExecucaoConvertida = retorno;
                //console.log(retorno);
                //var somadias = parseInt(dataExecucaoFim[0]) + parseInt(prazo);
                //novaDataFim = new Date(parseInt(dataExecucaoFim[2]),parseInt(dataExecucaoFim[1]), parseInt(somadias));
            }
            


            //dataIniExe  = data_ini_alt.split('-');
            //var nova_data_ini_exec = dataIniExe[2] + '/' + dataIniExe[1] + '/' + dataIniExe[0];


            document.getElementById('execucaoDataFinal').value = datafinalExecucaoConvertida;

        }

        function alterar_prazo(){
            var altera_prazo = '<?php echo $se_alteracao_prazo; ?>';

            var selecionado = $("input[name='alteracao_prazo']:checked").val();
           
            if(selecionado == 'SIM'){
                $("#prazo").prop("disabled", false);
                $("#vigenciaDataInicio").prop("disabled", false);
                $("#vigenciaDataTermino").prop("disabled", false);
                $("#execucaoDataInicio").prop("disabled", false);
                $("#execucaoDataFinal").prop("disabled", false);
            }else if(selecionado == 'NAO'){
                $("#prazo").prop("disabled", true);
                $("#vigenciaDataInicio").prop("disabled", true);
                $("#vigenciaDataTermino").prop("disabled", true);
                $("#execucaoDataInicio").prop("disabled", true);
                $("#execucaoDataFinal").prop("disabled", true);
            }
        }

        function alterar_valor(){

            var altera_valor = '<?php echo $se_alteracao_valor; ?>';
            
            var selecionado = $("input[name='alteracao_valor']:checked").val();

            if(selecionado == 'SIM'){
                $("#tipoAlteracaoValor").prop("disabled", false);
                $("#valor_total").prop("disabled", false);
                $("#valor_retroativo").prop("disabled", false);
            }else{
                $("#tipoAlteracaoValor").prop("disabled", true);
                $("#valor_total").prop("disabled", true);
                $("#valor_retroativo").prop("disabled", true);
            }
        }
       
        function enviar(valor){
            //alert(valor);
            //console.log( document.formContratoIncluir);return false;
            document.formContratoAlterar.Botao.value = valor;
            document.formContratoAlterar.submit();
        }

        $(document).ready(function() {          

            var tipo_aditivo  = $('#tipo_aditivo option:selected').val();
            var data_execucao_fim_preenchida = "<?php echo $data_termino_execucao; ?>";
            if(data_execucao_fim_preenchida == null){
                somarDataExecucaofinal();
            }
              //  document.getElementById('tipo_aditivo').value = tipoAditivo;
            if(tipo_aditivo != 13){
                $("#linhasForn").hide();
                $("#linhasresForn").hide();
            }
            
            $("#tipo_aditivo").on('change', function() {
                let tipoAditivo = $('#tipo_aditivo option:selected').val();
                
                if(tipoAditivo == 13){
                    $("#linhasForn").show();
                    $("#linhasresForn").show();
                }else{
                    $("#linhasForn").hide();
                    $("#linhasresForn").hide();

                    $('#razaosocial').val('');
                  //  alert($('#razaosocial').val(''));
                    $('#logradouro').val('');
                    $('#numero').val('');
                    $('#compl').val('');
                    $('#cidade').val('');
                    $('#bairro').val('');
                    $('#estado').val('');
                }
            });

            $('#CPF_forn').live('click',function(){
                //$(".mostracnpj").hide();
                //$(".mostracpf").show();
                $('#CnpjCpf_forn').mask('999.999.999-99');
            });
            $('#CNPJ_forn').live('click',function(){
                //$(".mostracnpj").show();
                //$(".mostracpf").hide();
                $('#CnpjCpf_forn').mask('99.999.999/9999-99');
            });

            var radio_cnpj_cpf = $("input[name='CNPJ_CPF']:checked").val();
            if(radio_cnpj_cpf == 1){
                $('#CnpjCpf_forn').mask('99.999.999/9999-99');
            }else{
                $('#CnpjCpf_forn').mask('999.999.999-99');
            }

            alterar_prazo();
            alterar_valor();
           
            var altera_prazo = '<?php echo $se_alteracao_prazo; ?>';
            var altera_valor = '<?php echo $se_alteracao_valor; ?>';

            $('#numcontrato').mask('9999.9999/9999');
            $('.data').mask('99/99/9999');
            $('.cnpj').mask('99.999.999/9999-99');
            $('.CPF').mask('999.999.999-99');
            // $('.telefone').mask('(99)99999-9999');

            $("input.telefone")
            .mask("(99) 9999-9999?9")
            .focusout(function (event) {  
                var target, phone, element;  
                target = (event.currentTarget) ? event.currentTarget : event.srcElement;  
                phone = target.value.replace(/\D/g, '');
                element = $(target);  
                element.unmask();  
                if(phone.length > 10) {  
                    console.log(phone.length);
                    element.mask("(99) 99999-999?9");  
                } else {  
                    console.log(phone.length);
                    element.mask("(99) 9999-9999?9");  
                }  
            });

            if($("#addFornecedor").is(':visible')){
                $.post("postDadosAditivo.php",{op: "ExibeFornecedorExtra", idregistro: $("input:[name=idregistro]").val()}, function(data){
                    ObjJson = JSON.parse(data);
                    $("#shownewfornecedores").html(CriatableView(ObjJson));
                });
            }

            if($("#opcaoExecucaoContrato").val() != ""){
               // $("#prazo").removeAttr("disabled");
            }

            $("#repCPF").on("blur",function(){
                if(!TestaCPF($("#repCPF").val())){
                    aviso("Informe: Um CPF válido para o representante.");
                }
            });

            $("#gestorCPF").on("blur",function(){
                if(!TestaCPF($("#gestorCPF").val())){
                    aviso("Informe: Um CPF válido para o gestor.");
                }
            });

            $("#btnvoltar").on('click', function(){
                contrato = <?php echo $codcontrato; ?>;
                $("#formContratoIncluir").attr("action","CadAditivoManter.php");
                $("#formContratoIncluir").submit();
            });
            
            $("#cnpj").live('focus', function(){
                $('#cnpj').mask('99.999.999/9999-99');
            });

            $("#cpffiscal").live('focus', function(){
                $('#cpffiscal').mask('999.999.999-99');
            });
            
            $("#fieldConsorcio0").on('click', function(){
                $("#addFornecedor").show();
            });

            $("#fieldConsorcio1").on('click', function(){
                $("#addFornecedor").hide();
            });

            $("#vigenciaDataInicio").on("blur", function(){
                dei = $("#vigenciaDataInicio").val();
                def = $("#vigenciaDataTermino").val();
                dataExecucaoInicio = dei.split('/');
                dataExecucaoFim    = def.split('/');
                novaData       = new Date(parseInt(dataExecucaoInicio[2]),parseInt(dataExecucaoInicio[1]-1),parseInt(dataExecucaoInicio[0]));
                novaDataFim = new Date(parseInt(dataExecucaoFim[2]),parseInt(dataExecucaoFim[1]-1),parseInt(dataExecucaoFim[0]));
                if(novaData > novaDataFim){
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $(".mensagem-texto").html("A data final não pode ser menor que a data inicial ");
                    $(".error").html("Atenção!");
                    $("#tdmensagem").show();
                }
            });
          
            $("#vigenciaDataTermino").on("blur", function(){
                dei = $("#vigenciaDataInicio").val();
                def = $("#vigenciaDataTermino").val();
                dataExecucaoInicio = dei.split('/');
                dataExecucaoFim    = def.split('/');
                novaData       = new Date(parseInt(dataExecucaoInicio[2]),parseInt(dataExecucaoInicio[1]-1),parseInt(dataExecucaoInicio[0]));
                data_fim = novaDataFim = new Date(parseInt(dataExecucaoFim[2]),parseInt(dataExecucaoFim[1]-1),parseInt(dataExecucaoFim[0]));
                if(novaData > novaDataFim){
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $(".mensagem-texto").html("A data final não pode ser menor que a data inicial");
                    $(".error").html("Atenção!");
                    $("#tdmensagemNova").show();
                }
            });
            

            $("#file").on('change', function(){
                var file = $("#file").val();
                $.post("postDadosAditivo.php", {op:"InsereArquivo", arquivo:file}, function(data){
                });
            });
            $('#btnIncluirAnexo').live("click",()=>{
                $('#loadArquivo').show();
            })
            $("#btnRemoveAnexo").live("click",function(){
                const docanexselec = $("input[name='docanex']:checked").val();
                $.post("postDadosAditivo.php",{op:"RemoveDocAnex",marcador:docanexselec},function(data){
                     $("#FootDOcFiscal").html(data);
                });
            });
                        
            $("#salvaContrato").on('click', function(){
                $('html, body').animate({scrollTop:0}, 'slow');
                $('#tdload').show();
                //$("#salvaContrato").prop("disabled", true);
                let podesalvar = true;
                var cpf_Representante = "";
                if(!TestaCPF($("#repCPF").val()) && cpf_Representante != ""){
                    aviso("Informe: Um CPF  válido para o representante.");
                    $("#salvaContrato").prop("disabled", false);
                  //  podesalvar= false;
                }
                var valida = true;
                var cpf_cnpj = $("#cpfcnpjhid").val();
                var checa_tipo_aditivo = $('#tipo_aditivo option:selected').val();
                if(cpf_cnpj == "" && checa_tipo_aditivo == 13 ){
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $(".mensagem-texto").html("Informe: Fornecedor Cadastrado no SICREF.");
                    $(".error").html("Atenção!");
                    $("#tdmensagem").show();
                    $('#tdload').hide();
                    return false;
                }     

                var valida_arquivo = window.top.frames['frameArquivo'].validaAnexo();
                
                radioDoc = $("input[name='docanex']");
                
                //    return false;    
                
                
                if(verificaContrato()){
                //    if(verificaContratoComSCC()){
                             $.post("postDadosAditivoGeral.php",$("#formContratoIncluir").serialize(), function(data){
                                const response = JSON.parse(data);
                             
                                if(!response.status){
                                    $('html, body').animate({scrollTop:0}, 'slow');
                                    $(".mensagem-texto").html(response.msm);
                                    $(".error").html("Erro!");
                                    $("#tdmensagem").show();
                                    $('#tdload').hide();
                                  //  $("#salvaContrato").prop("disabled", false);
                                }else{
                                    $('html, body').animate({scrollTop:0}, 'slow');
                                    $(".mensagem-texto").html(response.msm);
                                    $(".error").html("Atenção!");
                                    $(".error").css("color","#007fff");
                                    $("#tdmensagem").show();
                                    $('#tdload').hide();
                                    setTimeout(function(){ 
                                        window.location.href = "./CadAditivoManterPesquisar.php";
                                    }, 2000);

                                }
                            });
                            
                  //   }
                }
            });
           
        });

        <?php MenuAcesso(); ?>
       
    </script>
<link rel="stylesheet" type="text/css" href="../estilo.css?v=<?php echo time(); ?>">
<style>
    #tabelaficais thead tr td {
        align-items: center;
        white-space: nowrap;
        -webkit-user-modify: read-write-plaintext-only;
    }

    #tabelaficais tfoot tr td {
        align-items: center;
        white-space: nowrap;
    }

    #tabelaficais tfoot tr.FootFiscaisDoc {
        align-items: center;
        white-space: nowrap;
        text-align: center;
        background-color: #bfdaf2;
    }

    .msg {
        text-align: center;
        font-size: larger;
        font-weight: 600;
        color: #75ade6;
    }

    .input {
        font-size: 10.6667px;
    }
</style>

<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">
        Init();
    </script>
    <form action="CadAditivoAlterar.php?codcontrato=<?= $codcontrato; ?>&rg=<?= $registro; ?>" method="post" id="formContratoIncluir" name="formContratoAlterar">
        <input type="hidden" name="op" value="AlterarAditivo">
        <input type="hidden" name="idregistro" value="<?php echo $codcontrato; ?>">
        <input type="hidden" name="aditivo_sel" value="<?php echo $registro; ?>">
        
        <br><br><br><br>
        <table cellpadding="3" border="0" summary="">
            <!-- Caminho -->
            <tr>
                <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                <td align="left" class="textonormal" colspan="2">
                    <font class="titulo2">|</font>
                    <a href="../index.php">
                        <font color="#000000">Página Principal</font>
                    </a> > Aditivos > Manter > Alterar
                </td>
            </tr>
            <!-- Fim do Caminho-->

            <!-- Erro -->
            <tr>
                <td width="150"></td>
                <td align="left" colspan="2" id="tdmensagem">
                    <div class="mensagem">
                        <div class="error">
                            Erro
                        </div>
                        <span class="mensagem-texto">
                        </span>
                    </div>
                </td>
            </tr>
            <!-- Fim do Erro -->

            <?php 

if($Mens == 3){?> 
        <tr>
            <td width="150"></td>
            <td align="left" colspan="2" >
                <div class="mensagem">
                    <div class="error" >
                        Erro
                    </div>
                    <span class="mensagem-texto" id="mensagemNova"><?php echo (!empty($Mensagem))?$Mensagem:""; ?>
                    </span>
                </div>
            </td>
        </tr>
<?php $Mens = 0;}
 ?>             <!-- Corpo -->
             <!-- loading -->
				<tr>
					<td width="150"></td>
                    <td align="left" colspan="2" id="tdload" style="display:none;">
                        <div class="load" id="load"> 
                            <div class="load-content" >
                            <img src="../midia/loading.gif" alt="Carregando">
                            <spam>Carregando...</spam>
                            </div>
                        </div> 
					</td>
				</tr>
			<!-- Fim do loading -->
            <tr>
                <td width="100"></td>
                <td class="textonormal">
                    <table border="0" cellspacing="0" cellpadding="3" summary="" width="1024px" bgcolor="#FFFFFF">
                        <tr>
                            <td class="textonormal" border="3px" bordercolor="#75ADE6">

                                <table border="1" cellpadding="3" cellspacing="0" bordercolor="white" summary="" class="textonormal" bgcolor="#FFFFFF">
                                    <tr>
                                        <td align="left">
                                            <table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="1024px" summary="">
                                                <tr bgcolor="#bfdaf2">
                                                    <!-- <td colspan="4"> -->
                                                    <table class="textonormal" id="scc_material" summary="" bordercolor="#75ADE6" style="border: 1px solid #75ade6; border-radius: 4px;" width="100%">
                                                        <thead>
                                                            <td class="titulo3" colspan="17" align="center" bgcolor="#75ADE6" valign="middle"> <b>MANTER ADITIVO</b></td>
                                                        </thead>
                                                        <tbody>

                                                            <tr>
                                                                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Número do Contrato/Ano
                                                                <td style="font-size: 10.6667px;" bgcolor="White">
                                                                    <?php
                                                                        echo $empresa;
                                                                    ?>
                                                                </td>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td bgcolor="#DCEDF7" class="textonormal" width="225px">Número do Aditivo*
                                        <td class="textonormal">
                                            <input style="font-size: 10.6667px;" id="numero_registro" type="text" name="numero_registro" value="<?php echo $registro_base; ?>" style="width:70px;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#DCEDF7" class="textonormal" width="225px">Tipo de Aditivo (SAGRES TCE)*</td>
                                        <td class="textonormal">
                                            <select class="selectContrato" id="tipo_aditivo" name="tipo_aditivo" size="1" style="width:485px;" style="font-size: 10.6667px;">
                                                
                                                <?php
                                                    foreach ($tipos_aditivo as $key => $tipo) {
                                                        if(!empty($verificaTipoFornecedor)){
                                                            $aditivoFornecedor = 13;
                                                        }else{
                                                            $aditivoFornecedor = $tipo_aditivo;
                                                        }
                                                        $selecionadoTipo = ($tipo->ctpadisequ == $aditivoFornecedor || $tipo->ctpadisequ == $tipoadt) ? 'selected="selected"' : '';
                                                ?>
                                                    <option class="textonormal" style="font-size: 10.6667px;" value="<?php echo $tipo->ctpadisequ;?>" <?php echo $selecionadoTipo; ?> >
                                                        <?php echo $tipo->etpadidesc; ?>
                                                    </option>
                                                <?php
                                                    }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>

                                    <tr id="linhasForn">
                                        <td bgcolor="#DCEDF7" class="textonormal"  width="225px">Contratado*
                                        
                                        <td class="textonormal" width="60px">
                                            <input type="radio" name="CNPJ_CPF" id="CNPJ_forn" value="1" <?php if( $CNPJ_CPF == 1 ){ echo "checked"; }?>>CNPJ*
                                            <input type="radio" name="CNPJ_CPF" id="CPF_forn" value="2" <?php if( $CNPJ_CPF == 2 ){ echo "checked"; }?>>CPF*
                                            <input style="font-size: 10.6667px;" class="textonormal" type="text" name="CnpjCpf" id="CnpjCpf_forn" size="18" style="font-size: 10.6667px;" value="<?php echo !empty($cpfCnpj) ? $cpfCnpj : ''; ?>">    
                                            
                                            <a href="javascript:enviar('Verificar');"><img src="../midia/lupa.gif" border="0"></a>
                                        </td>
                                    
                                        </tr>
                                    </tr>
                                    <tr id="linhasresForn">
                                        <td bgcolor="#DCEDF7" class="textonormal"  width="225px">
                                        <td class="textonormal">
                                            <table id="_gridContratadoNovo">
                                                <tbody><tr><td class="labels">
                                                    <span id="_panelLblCpfCnpj">
                                                    <label for="" style=";" class="textonormal" ><?php echo (strlen($CnpjCpf_forn) == 18) ? "CNPJ do Contratado :" : "CPF do Contratado :"; ?></label></span></td><td class="textonormal" colspan="3"><div id="_panelInputCpfCnpj" name="_panelInputCpfCnpj"><?php if(!is_null($CnpjCpf_forn)){  echo $CnpjCpf_forn; } ?><label>
                                                        <input type="hidden" id="cpfcnpjhid" name="cpfcnpjhid" value="<?php if(!is_null($CnpjCpf_forn)){  echo $CnpjCpf_forn; } ?>">
                                                        <input type="hidden" id="flagcpfcnpjhid" name="flagcpfcnpjhid" value="<?php echo (strlen($CnpjCpf_forn) == 18) ? 1 : 0; ?>">
                                                        <input type="hidden" id="cpfcnpjhid" name="cpfcnpjhid" value="<?php if(!is_null($CnpjCpf_forn)){  echo $CnpjCpf_forn; } ?>">                   </label></div></td></tr>
                                                        <tr><td class="labels"><label for="" style=";" class="textonormal">Razão Social :</label></td><td class="textonormal" colspan="3" ><div id="_panelGroupRazao"><span style="font-size: 10.6667px;" id="_razaoSocialfornecedor" name="razao"><?php if(!is_null($RazaoSocial)){ echo  "$RazaoSocial"; } ?></span></div></td></tr>
                                                        <tr><td class="labels"><label for="" style=";" class="textonormal">Logradouro :</label></td><td class="textonormal" colspan="3"><span style="font-size: 10.6667px;" id="_logradourofornecedor"><?php if(!is_null($logradouro)){  echo "$logradouro"; } ?></span></td></tr>
                                                        <tr><td class="labels"><label for="" style=";" class="textonormal">Número :</label></td><td class="textonormal" colspan="3"><span style="font-size: 10.6667px;" id="_numerofornecedor"><?php if(!is_null($numero)){  echo "$numero"; } ?></span></td></tr>
                                                        <tr><td class="labels"><label for="" style=";" class="textonormal">Complemento :</label></td><td class="textonormal"><span style="font-size: 10.6667px;" id="_complementoLogradourofornecedor"><?php if(!is_null($compl)){  echo "$compl"; } ?></span></td><td class="labels"><label for="" style=";" class="textonormal">Bairro:</label></td><td class="textonormal"><span id="_bairrofornecedor"><?php if(!is_null($bairro)){  echo "$bairro"; } ?></span></td></tr>
                                                        <tr><td class="labels"><label for="" style=";" class="textonormal">Cidade :</label></td><td class="textonormal"><span style="font-size: 10.6667px;" id="_cidadefornecedor"><?php if(!is_null($cidade)){  echo "$cidade"; } ?></span></td><td class="labels"><label for="" style=";" class="textonormal">UF:</label></td><td class="textonormal"><span id="_estadofornecedor"><?php if(!is_null($estado)){  echo "$estado"; } ?></span></td></tr>
                                                        </tbody></table>
                                        </td>
                                    </td>
                                </tr>

                                    <tr>
                                        <td bgcolor="#DCEDF7" class="textonormal" width="225px">Situação do Aditivo*</td>
                                        <td class="textonormal">
                                            <select class="selectContrato" id="situacao_aditivo" name="situacao_aditivo" size="1" style="width:371px;">
                                                <?php
                                                    foreach ($situacaoAditivo as $key => $situacao) {                                                        
                                                ?>
                                                    <option style="font-size: 10.6667px;" <?php echo intval($situacao->cfasedsequ) == intval($fase) ? 'selected="selected"' : ''; ?> value="<?php echo $situacao->cfasedsequ; ?>"><?php echo $situacao->esitdcdesc; ?></option>
                                                <?php
                                                    }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#DCEDF7" class="textonormal" width="225px">Justificativa do Aditivo*
                                        <td style="font-size: 10.6667px;" class="textonormal">
                                            <textarea id="objeto" style="text-transform: uppercase;" name="objeto" cols="63" rows="4" maxlength="500"  rows="8"><?php echo $justificativa; ?></textarea>
                                        </td>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Há Alteração de Prazo*</td>
                            <td>
                                <table id="alteracao_prazo">
                                    <tbody>
                                        <tr>
                                            <td style="font-size:10.6667px;">
                                                <input type="radio" name="alteracao_prazo" id="alteracao_prazo0" onchange="return alterar_prazo()" <?php echo $se_alteracao_prazo == 'SIM' ? 'checked="checked"' : ''; ?> value="SIM" title="Há alteração de Prazo*"><label for="alteracao_prazo0"> Sim</label></td>
                                            <td style="font-size:10.6667px;">
                                                <input type="radio" name="alteracao_prazo" id="alteracao_prazo1" value="NAO" onchange="return alterar_prazo()" <?php echo $se_alteracao_prazo == 'NAO' ? 'checked="checked"' : ''; ?> title="Há alteração de Prazo*"><label for="alteracao_prazo1"> Não</label></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                </td>
            </tr>

            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Há Alteração de Valor*</td>
                <td class="textonormal">
                    <table id="alteracao_valor">
                        <tbody>
                            <tr>
                                <td style="font-size:10.6667px;">
                                    <input type="radio" name="alteracao_valor" id="alteracao_valor0" value="SIM" onchange="return alterar_valor()" <?php echo $se_alteracao_valor == 'SIM' ? 'checked="checked"' : ''; ?> title="Há alteração de Valor*"><label for="alteracao_valor0"> Sim</label></td>
                                <td style="font-size:10.6667px;">
                                    <input type="radio" name="alteracao_valor" id="alteracao_valor1" value="NAO" onchange="return alterar_valor()" <?php echo $se_alteracao_valor == 'NAO' ? 'checked="checked"' : ''; ?> title="Há alteração de Valor*"><label for="alteracao_valor1"> Não</label></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Tipo de Alteração de Valor*
                <td bgcolor="White">
                    <select class="selectContrato" id="tipoAlteracaoValor" name="tipoAlteracaoValor" size="1" style="width:223px;">
                        
                        <option style="font-size: 10.6667px;" <?php echo trim($tipo_alteracao_valor) == 'RP' ? 'selected="selected"' : ''; ?> value="RP">REPACTUAÇÃO</option>
                        <option style="font-size: 10.6667px;" <?php echo trim($tipo_alteracao_valor) == 'RQ' ? 'selected="selected"' : ''; ?> value="RQ">REEQUILÍBRIO</option>
                        <option style="font-size: 10.6667px;" <?php echo trim($tipo_alteracao_valor) == 'QT' ? 'selected="selected"' : ''; ?> value="QT">QUANTITATIVO</option>
                        <option style="font-size: 10.6667px;" <?php echo trim($tipo_alteracao_valor) == 'QRP' ? 'selected="selected"' : ''; ?> value="QRP">QUANTITATIVO COM REPACTUAÇÃO</option>
                        <option style="font-size: 10.6667px;" <?php echo trim($tipo_alteracao_valor) == 'QRQ' ? 'selected="selected"' : ''; ?> value="QRQ">QUANTITATIVO COM REEQUILÍBRIO</option>
                        <option style="font-size: 10.6667px;" <?php echo $_POST['tipoAlteracaoValor'] == 'QRQ' ? 'selected="selected"' : ''; ?> value="PP">PRORROGAÇÃO DE PRAZO</option>
                    </select>
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Valor Retroativo Repactuação /Reequilíbrio</td>
                <td class="textonormal">
                    <input style="font-size: 10.6667px;" id="valor_retroativo" class = "dinheiroNegativo" type="text" name="valor_retroativo" value="<?php echo $valor_retroativo; ?>" style="width:70px;">
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Valor Total do Aditivo</td>
                <td class="textonormal">
                    <input style="font-size: 10.6667px;" id="valor_total" class = "dinheiroNegativo" type="text" name="valor_total" value="<?php echo $valor_total_aditivo; ?>" style="width:70px;">
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Acréscimo do Prazo de Execução do Aditivo*</td>
                <td class="textonormal">
                    <input style="font-size: 10.6667px;" id="prazo" type="text" name="prazo" class="inteiroPositivo" maxlength="4" value="<?php echo $prazo; ?>" style="width:70px;">
                </td>
            </tr>

            <tr id="linhaTabelaOS">
                <td id="colunaVaziaOS" width="225px" bgcolor="#bfdaf2"></td>
                <td id="colunaDataInicioOS" bgcolor="#bfdaf2" style="width:415px;">
                    <table id="panelDataInicioOrdemServico" class="colorBlue">
                        <thead>
                            <tr>
                                <th class="titulo3" colspan="1" scope="colgroup">
                                    <span style="font-size: 10.6667px;" id="labelDataInicioOrdemServico">Data de Início</span></th>
                            </tr>
                        </thead>
                    </table>
                </td>
                <td id="colunaDataTerminoOS" bgcolor="#bfdaf2" style="width: 256px;">
                    <table id="panelDataTerminoOrdemServico" class="colorBlue">
                        <thead>
                            <tr>
                                <th class="titulo3" colspan="1" scope="colgroup">
                                    <span style="font-size: 10.6667px;" id="labelDataTerminoOrdemServico">Data de Término</span></th>
                            </tr>
                        </thead>
                    </table>
                </td>
            </tr>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Vigência*</td>
                <td class="textonormal">
                    <span id="vigenciaGroup">
                        <input style="font-size: 10.6667px;" id="vigenciaDataInicio" type="text" name="vigenciaDataInicio" value="<?php echo $data_inicio_vigencia != null ? date('d/m/Y', strtotime($data_inicio_vigencia)) : ''; ?>" class="data" maxlength="10" size="12" title="">
                        <a id="calendarioVig" style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=formContratoAlterar&amp;Campo=vigenciaDataInicio','Calendario',220,170,1,0)">
                            <img src="../midia/calendario.gif" border="0" alt="">
                        </a>
                    </spam>
                </td>
                <td class="textonormal">
                    <span id="vigenciaGroup">
                        <input style="font-size: 10.6667px;" id="vigenciaDataTermino" type="text" name="vigenciaDataTermino" value="<?php echo $data_fim_vigencia != null ? date('d/m/Y', strtotime($data_fim_vigencia)) : ''; ?>" class="data" maxlength="10" size="12">
                        <a id="calendarioVig" style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=formContratoAlterar&amp;Campo=vigenciaDataTermino','Calendario',220,170,1,0)">
                            <img src="../midia/calendario.gif" border="0" alt="">
                        </a>
                    </span>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Execução*</td>
                    <td class="textonormal">
                        <span id="execucaoGroup">
                            <input style="font-size: 10.6667px;" id="execucaoDataInicio" type="text" name="execucaoDataInicio" value="<?php echo $data_inicio_execucao != null ?  date('d/m/Y', strtotime($data_inicio_execucao)) : '';  ?>" class="data" maxlength="10" size="12">
                            <a id="calendarioExecIni" style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=formContratoAlterar&amp;Campo=execucaoDataInicio','Calendario',220,170,1,0)">
                                <img src="../midia/calendario.gif" border="0" alt="">
                            </a>
                        </span>
                    </td>
                <!-- </span> -->
                    <td class="textonormal">
                        <span id="execucaoGroup">
                        <input style="font-size: 10.6667px;" id="execucaoDataFinal" type="text" name="execucaoDataFinal" value="<?php echo $data_fim_execucao != null ?  $data_fim_execucao : '';  ?>" class="data" maxlength="10" size="12">
                            <a id="calendarioExecFim" style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=formContratoAlterar&amp;Campo=execucaoDataFinal','Calendario',220,170,1,0)">
                                <img src="../midia/calendario.gif" border="0" alt="">
                            </a>
                        </span>
                    </td>
                <!-- </span> -->
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Observação do Aditivo</td>
                <td style="font-size: 10.6667px;" class="textonormal">
                    <textarea id="observacao" style="text-transform: uppercase;" name="observacao" cols="50" rows="4" maxlength="1000"  rows="8"><?php echo $observacao; ?></textarea>
                </td>
            </tr>

            <tr>
                <thead bgcolor="#bfdaf2">
                    <tr>
                        <th class="titulo3" colspan="3" <?php echo $bloqueiacampo ? 'disabled="disabled"' : ''; ?> scope="colgroup"> REPRESENTANTE LEGAL  (Só preencher se houver alteração)</th>
                    </tr>
                </thead>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Nome
                <td bgcolor="White">
                    <input type="text" name="repNome" value="<?php echo !empty($nome_representante) ? $nome_representante : ''; ?>" maxlength="120" style="width:315px; font-size: 10.6667px;">
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">CPF
                <td bgcolor="White">
                    <input style="font-size: 10.6667px;" class="CPF" type="text" name="repCPF" value="<?php echo !empty($cpf_representante) ? $cpf_representante : ''; ?>" id="repCPF" size="10">
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Cargo
                <td bgcolor="White">
                    <input style="font-size: 10.6667px;width:173px;" type="text" name="repCargo" value="<?php echo !empty($cargo_representante) ? $cargo_representante : ''; ?>" maxlength="100" >
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Identidade
                <td bgcolor="White">
                    <input style="font-size: 10.6667px;" type="text" name="repRG" value="<?php echo !empty($rg_representante) ? $rg_representante : ''; ?>" maxlength="9" size="10">
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Órgão Emissor
                <td bgcolor="White">
                    <input style="font-size: 10.6667px;" type="text" name="repRgOrgao" value="<?php echo !empty($orgao_exp_representante) ? $orgao_exp_representante : ''; ?>" maxlength="3" size="1">
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">UF da Identidade
                <td bgcolor="White">
                    <input style="font-size: 10.6667px;" type="text" name="repRgUF" value="<?php echo !empty($uf_nat_representante) ? $uf_nat_representante : ''; ?>" maxlength="2" size="1" style="text-transform: uppercase">
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="250px">Cidade de Domicílio
                <td bgcolor="White">
                    <input style="font-size: 10.6667px;width:173px;" type="text" name="repCidade" value="<?php echo !empty($cidade_representante) ? $cidade_representante : ''; ?>" maxlength="30">
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Estado de Domicílio
                <td bgcolor="White">
                    <input style="font-size: 10.6667px;" type="text" name="repEstado" value="<?php echo !empty($estado_representante) ? $estado_representante : ''; ?>" maxlength="2" size="1" style="text-transform: uppercase">
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Nacionalidade
                <td bgcolor="White">
                    <input style="font-size: 10.6667px;width:173px;" type="text" name="repNacionalidade" value="<?php echo !empty($nacionalidade_representante) ? $nacionalidade_representante : ''; ?>" maxlength="50" >
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Estado Civil
                <td bgcolor="White">
                    <select class="selectContrato" id="repEstCiv" name="repEstCiv" size="1" style="width:173px;">
                        <option style="font-size: 10.6667px;" <?php echo $estado_civil_representante == 'Z' ? 'selected="selected"' : ''; ?> value="Z">Selecione o estado civil...</option>
                        <option style="font-size: 10.6667px;" <?php echo $estado_civil_representante == 'S' ? 'selected="selected"' : ''; ?> value="S">SOLTEIRO</option>
                        <option style="font-size: 10.6667px;" <?php echo $estado_civil_representante == 'C' ? 'selected="selected"' : ''; ?> value="C">CASADO</option>
                        <option style="font-size: 10.6667px;" <?php echo $estado_civil_representante == 'D' ? 'selected="selected"' : ''; ?> value="D">DIVORCIADO</option>
                        <option style="font-size: 10.6667px;" <?php echo $estado_civil_representante == 'V' ? 'selected="selected"' : ''; ?> value="V">VIÚVO</option>
                        <option style="font-size: 10.6667px;" <?php echo $estado_civil_representante == 'O' ? 'selected="selected"' : ''; ?> value="O">OUTROS</option>
                    </select>
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Profissão
                <td class="textonormal">
                    <input style="font-size: 10.6667px;width:173px;" id="Profissao" type="text" name="repProfissao" value="<?php echo !empty($profissao_representante) ? $profissao_representante : ''; ?>" maxlength="50" >
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">E-mail
                <td bgcolor="White" class="textonormal">
                    <input style="font-size: 10.6667px;width:173px; text-transform: none;" type="text" name="repEmail" value="<?php echo !empty($email_representante) ? $email_representante : ''; ?>" maxlength="60" >
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Telefone(s)
                <td bgcolor="White" class="textonormal">
                    <input style="font-size: 10.6667px;" class="telefone" type="text" name="repTelefone" value="<?php echo !empty($telefone_representante) ? $telefone_representante : ''; ?>" size="13">
                </td>
                </td>
            </tr>
            </tr>
            <tr>
                <thead class="titulo3" bgcolor="#bfdaf2">
                    <tr>
                        <th colspan="3" scope="colgroup">ANEXAR DOCUMENTOS</th>
                    </tr>
                </thead>
            </tr>
            <tr>
                <td colspan="3">
                    <table id="tabelaficais" bgcolor="#bfdaf2" class="textonormal" width="100%">
                        <tbody>
                            <tr>
                                <td bgcolor="#DCEDF7">Anexação de Documentos</td>
                                <td colspan="1" style="border:none">
                                    <iframe src="formupload.php" id="frameArquivo" height="39" width="520" name="frameArquivo" frameborder="0"></iframe>
                                </td>
                            </tr>
                            <!-- Inicio upload carregando  -->
                            <div class="load" id="loadArquivo" style="display: none;"> 
                                <div class="load-content" >
                                <img src="../midia/loading.gif" alt="Carregando">
                                <spam>Carregando...</spam>
                                </div>
                            </div>    
                            <!-- Fim upload carregando  -->  
                        </tbody>
                        <tfoot id="FootDOcFiscal">
                            <tr class="FootFiscaisDoc">
                                <td></td>
                                <td colspan="4">ARQUIVO</td>
                                <td colspan="4">DATA DA INCLUSÃO</td>
                            </tr>
                            <?php
                            if (!empty($DadosDocAnexo)) {
                                $k=0;
                                foreach($DadosDocAnexo as $key => $anexo){
                                    
                                    $_SESSION['arquivo_download'][$k] = $anexo->arquivo;
                            ?>
                                    <tr bgcolor="#ffffff">
                                        <td><input type="radio" name="docanex" value="<?php echo $key; ?>">
                                        <input type="hidden" name="nomedoc" value="<?php echo $anexo->sequdocumento . '*' . $anexo->nomearquivo; ?>"></td>
                                        <td colspan="4">
                                            <a class="" href="downloadDocContratoConsolidado.php?arquivo=<?php echo $k;?>&nome=<?php echo $anexo->nomearquivo;?>" id="documento<?php echo $k;?>" rel="<?php echo $anexo->nomearquivo;?>"><?php echo $anexo->nomearquivo; ?></a>
                                            
                                        </td>
                                        <td colspan="4"> <?php echo $anexo->datacadasarquivo; ?></td>

                                    </tr>
                            <?php $k++; }
                            } else {
                                echo ' <tr bgcolor="#ffffff">';
                                echo ' <td colspan="8" bgcolor="#ffffff">Nenhum documento informado</td>';
                                echo ' </tr>';
                            }
                            ?>
                            <tr bgcolor="#ffffff">
                                <td colspan="8" align="center">
                                    <button type="button" class="botao" id="btnIncluirAnexo" onclick="Subirarquivo()">Incluir Documento</button>
                                    <button type="button" class="botao" id="btnRemoveAnexo">Retirar Documento</button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    </br>
            <tr>
                <td colspan="4" align="right">
                    <button type="button" name="salvaContrato" class="botao" id="salvaContrato">Salvar</button>
                    <input type="button" name="btnVoltar" title="Voltar" value="Voltar" class="botao" id="btnvoltar">
                </td>
            </tr>
            <input type="hidden" id="Destino" name="Destino">
            <input type="hidden" name="Botao" value="" id="Botao">

            <input type="hidden" name="razaosocial" value="<?php echo $RazaoSocial; ?>" id="razaosocial">
            <input type="hidden" name="logradouro" value="<?php echo $logradouro; ?>" id="logradouro">
            <input type="hidden" name="numero" value="<?php echo $numero; ?>" id="numero">
            <input type="hidden" name="compl" value="<?php echo $compl; ?>" id="compl">
            <input type="hidden" name="cidade" value="<?php echo $cidade; ?>" id="cidade">
            <input type="hidden" name="bairro" value="<?php echo $bairro; ?>" id="bairro">
            <input type="hidden" name="estado" value="<?php echo $estado; ?>" id="estado">

            
            </td>
            </tr>
        </table>
        </table>
    </form>
    <div class="modal" id="modal">
        <div class="modal-content" style="min-height: 50%;width: 83%;">

        </div>
    </div>
    <!-- Fim Modal -->
</body>

</html>
<?php
exit;
