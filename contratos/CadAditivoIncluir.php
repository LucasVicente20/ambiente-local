<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAditivoIncluir.php
# Autor:    Edson Dionisio
# Data:     27/06/2020
# Objetivo: Programa de incluir Aditivo
#-------------------------------------------------------------------------
#Alterado : Osmar Celestino
# Data: 29/03/2021
# Objetivo: CR CR #245212  Correção da  transformação da string e-mail pra Upper, manter como o cliente digitou.
#---------------------------------------------------------------------------
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

$ObjClassAditivoGeral = new ClassAditivoGeral();
$ObjClassAditivo = new ClassAditivo();

$cod_contrato = $_POST['idregistro'];
if(!empty($_POST['idregistro'])){
    $_SESSION['idregistro'] = $_POST['idregistro'];
}else {
    $cod_contrato = $_SESSION['idregistro'];
}

$contrato = $ObjClassAditivo->getContrato($cod_contrato);
$codigo_empresa = $contrato[0]->cdocpcsequ;
$empresa = $contrato[0]->ectrpcnumf;

$mes_dia = $contrato[0]->cctrpcopex;

$codAditivo = $ObjClassAditivo->getUltimoCodAditivoGeral($codigo_empresa);

$tipos_aditivo = $ObjClassAditivo->getTiposAditivo();
//$medicoes = $ObjClassAditivo->getMedicoesContrato($cod_contrato);

unset($_SESSION['documento_anexo_incluir']);

$dados_fornecedor = $_SESSION['dadosFornecedor'];
    $dadosFornecedor = array();

    if(!empty($dados_fornecedor)){
        foreach($dados_fornecedor as $key => $f){
               $fornecedorselecionado[] = (object)  array(
                   'aforcrsequ'      => strtoupper( $f->aforcrsequ),//codigo
                   'nforcrrazs'      => strtoupper($f->nforcrrazs),//razão social
                   'eforcrlogr'      => strtoupper($f->eforcrlogr),//rua
                   'eforcrcomp'      => strtoupper($f->eforcrcomp),//complemento
                   'eforcrbair'      => strtoupper($f->eforcrbair),//bairro
                   'nforcrcida'      => strtoupper( $f->nforcrcida),//cidade
                   'aforcrccpf'      => strtoupper( $f->aforcrccpf),//cpf
                   'aforcrccgc'      => strtoupper($f->aforcrccgc),//cnpj
                   'cforcresta'      => strtoupper( $f->cforcresta),//uf
                   'remover'         => 'N'
           );                      
       }
    
     //  unset( $_SESSION['dados_fornecedor_incluir']);
       $_SESSION['dados_fornecedor_incluir'] = $fornecedorselecionado;
       $dadosFornecedor = $_SESSION['dados_fornecedor_incluir'];
    }

    unset($_SESSION['dadosFornecedor']);

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    unset($_SESSION['dadosTabela']);
}
$obejtoLen = strlen($_POST['objeto']);
if ($_SERVER['REQUEST_METHOD'] == "POST") {
   
    // Busca de dados do fornecedor
    $Botao = $_POST['Botao'];

    $_SESSION['razao_social'] = null;
    
    if(!empty($_SESSION['codigo_forn'])){
        $_SESSION['dadosContratado']  = $ObjClassAditivoGeral->getFornecedorDados($_SESSION['codigo_forn']);
        $dadosSalvar['aforcrsequ'] = $_SESSION['codigo_forn'];
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

    if (!empty($_SESSION['documento_anexo_incluir'])) {
        foreach ($_SESSION['documento_anexo_incluir'] as $doc) {
            $DadosDocAnexo[]  =  (object) array(
                'nomearquivo'       => $doc['nome_arquivo'],
                'arquivo'           => $doc['arquivo'],
                'sequdocumento'     => $doc['sequdoc'],
                'datacadasarquivo'  => $doc['data_inclusao'],
                'usermod'           => $doc['usermod'],
                'arquivo'           => $doc['arquivo'],
                'ativo'             => 'S'
            );
        }
    }
}

$tipoadt = $_POST['tipo_aditivo'];

if($tipoadt != 13){
    $RazaoSocial = '';
    $logradouro = '';
    $compl = '';
    $bairro = '';
    $cidade = '';
    $estado = '';
    $cpf = '';
    $CnpjCpf_forn = '';
}else{
    $RazaoSocial = $aditivo[0]->naditirazs;
    $logradouro = $aditivo[0]->eaditilogr;
    $compl = $aditivo[0]->eaditicomp;
    $bairro = $aditivo[0]->eaditibair;
    $cidade = $aditivo[0]->naditicida;
    $estado = $aditivo[0]->caditiesta;
    $cpf = $aditivo[0]->eaditicpfc;
    $CnpjCpf_forn = $aditivo[0]->eaditicgcc;
}

//var_dump($_POST);
$CNPJ_CPF            = $_POST['CNPJ_CPF'];

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
			$Mensagem .= "<a href=\"javascript:document.CadAditivoIncluir.CnpjCpf.focus();\" class=\"titulo2\">$TipoDocumento</a>";
		} else {
			if ($CNPJ_CPF == 1) {
               
				$valida_cnpj = valida_CNPJ($CnpjCpf);
                
				if ($valida_cnpj === false) {
					$RazaoSocial = null;
					
					if ($Mens == 1) {
						$Mensagem.=", ";
					}
					
					$Mens      = 3;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.CadAditivoIncluir.CnpjCpf.focus();\" class=\"titulo2\">CNPJ Válido</a>";
				}
			} else {
				$valida_cpf = valida_CPF($CnpjCpf);
				
				if ($valida_cpf === false) {
					$RazaoSocial = null;
					
					$Mens      = 3;
					$Tipo      = 2;
					$Mensagem .= "<a href=\"javascript:document.CadAditivoIncluir.CnpjCpf.focus();\" class=\"titulo2\">CPF Válido</a>";
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
            
            if (($CNPJ_CPF == 1 && $valida_cnpj === true) || ($CNPJ_CPF == 2 && $valida_cpf === true)) {
                
                # Verifica se o CNPJ Existe no cadastro de Fornecedores Credenciados #
                $db = Conexao();
                
                $sql  = "SELECT AFORCRSEQU, NFORCRRAZS, AFORCRCCGC, CCEPPOCODI, EFORCRLOGR, AFORCRNUME, EFORCRCOMP, EFORCRBAIR, NFORCRCIDA, CFORCRESTA, AFORCRCDDD, AFORCRTELS, AFORCRCCPF FROM SFPC.TBFORNECEDORCREDENCIADO ";
                $sql .= " WHERE ";
                    
                    if ($CNPJ_CPF == 1) {
                        $sql .= " AFORCRCCGC = '$CnpjCpf' ";
                    } else {
                        $sql .= " AFORCRCCPF = '$CnpjCpf' ";
                    }

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
                        
                        if(!empty($linha[12])){
                            $CnpjCpf_forn = preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $linha[12]);
                            $cpfCnpj = $linha[12];
                            $_SESSION['cpfForn'] = $linha[12];
                        }else{
                            $CnpjCpf_forn = preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $linha[2]);
                            $cpfCnpj = $linha[2];
                            $_SESSION['cnpjForn'] = $linha[2];
                        }  
                       // $CnpjCpf_forn = $linha[2];
                        $cep = $linha[3];
                        $logradouro = $linha[4];
                        $numero = $linha[5];
                        $compl = $linha[6];
                        $bairro = $linha[7];
                        $cidade = $linha[8];
                        $estado = $linha[9];
                        $codFornecedor = $linha[0];
                        //$ddd = $linha[10];
                    } else {
                        if ($Mens == 1) {
                            $Mensagem.=", ";
                        }
                        
                        $Mens     = 3;
                        $Tipo     = 1;
                        $Mensagem = "Fornecedor Não Cadastrado no SICREF";
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

        function enviar(valor){
            console.log(valor);
            
            //alert(valor);
            //console.log( document.formContratoIncluir);return false;
            document.CadAditivoIncluir.Botao.value = valor;
            document.CadAditivoIncluir.submit();
        }

        function somaDias(dt,qtd)
        {
            var dt1 = dt.split("/");
            var hj1 = dt1[2]+"-"+dt1[1]+"-"+dt1[0];
            var dtat = new Date(hj1);
            dtat.setDate(dtat.getDate());
            var myDate = new Date(hj1);
            myDate.setDate(myDate.getDate() + (qtd+1));
            var ano = myDate.getFullYear();
            var dia = myDate.getDate(); 
            var mes = (myDate.getMonth()+1); 
            
            if(dia < 10){
                dia='0' + dia
            };
            
            
            if(mes<10){
                mes='0'+mes
            }

            return (dia+"/"+mes+"/"+ano);
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

            datafinal = new Date(datainicial);

            datafinal.setDate(datafinal.getDate() + dias);
            var dd = ("0" + datafinal.getDate()).slice(-2);
            var mm = ("0" + (datafinal.getMonth()+1)).slice(-2);
            var y = datafinal.getFullYear();

            var dataformatada = dd + '/' + mm + '/' + y; //y + '-' + mm + '-' + dd;
            return dataformatada;
        }

        function somarDataExecucaofinal(){
            var tipo_dias = "<?php echo $mes_dia; ?>";
            var prazo = $("#prazo").val();
            
            if(prazo == null  || prazo == '' || prazo == 'undefined'){
                aviso("Informe: Acréscimo de prazo.");
                $("#prazo").focus();
                return false;
            }

            var def;
            def = $("#execucaoDataInicio").val();
            
            dataExecucaoFim    = def.split('/');
            novaData       = new Date(parseInt(dataExecucaoFim[2]),parseInt(dataExecucaoFim[1]-1),parseInt(dataExecucaoFim[0]));

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
                console.log(retorno);
                datafinalExecucaoConvertida = retorno;
            }

            document.getElementById('execucaoDataFinal').value = datafinalExecucaoConvertida;
            
        }
       
        $(document).ready(function() {

            var tipo_aditivo = "<?php echo $tipoadt; ?>";
            console.log(tipo_aditivo);
              //  document.getElementById('tipo_aditivo').value = tipoAditivo;
            if(tipo_aditivo != 13){
                $("#linhasForn").hide();
                $("#linhasresForn").hide();
            }
            
            $("#tipo_aditivo").on('change', function() {
                let tipoAditivo = $('#tipo_aditivo option:selected').val();
              //  document.getElementById('tipo_aditivo').value = tipoAditivo;
                if(tipoAditivo == 13){
                    $("#linhasForn").show();
                    $("#linhasresForn").show();
                }else{
                    $("#linhasForn").hide();
                    $("#linhasresForn").hide();
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
                $("#prazo").removeAttr("disabled");
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
                window.history.back();
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
                    $("#tdmensagem").show();
                }
            });

            $("#alteracao_prazo0").on('change', function(){ // define se o campo de prazo é alteravel ou não
                var opExec = $("#alteracao_prazo0").val();
                
                if(opExec == 'SIM'){
                    
                    $("#prazo").prop("disabled", false);
                    $("#vigenciaDataInicio").prop("disabled", false);
                    $("#vigenciaDataTermino").prop("disabled", false);
                    $("#execucaoDataInicio").prop("disabled", false);
                    $("#execucaoDataFinal").prop("disabled", false);
                }
            });

            $("#alteracao_prazo1").on('change', function(){ // define se o campo de prazo é alteravel ou não
                var opExec = $("#alteracao_prazo1").val();
                
                if(opExec == 'NAO'){
                    
                    $("#prazo").prop("disabled", true);
                    $("#vigenciaDataInicio").prop("disabled", true);
                    $("#vigenciaDataTermino").prop("disabled", true);
                    $("#execucaoDataInicio").prop("disabled", true);
                    $("#execucaoDataFinal").prop("disabled", true);
                    
                    $("#prazo").val("");
                    $("#vigenciaDataInicio").val("");
                    $("#vigenciaDataTermino").val("");
                    $("#execucaoDataInicio").val("");
                    $("#execucaoDataFinal").val("");
                }
            });

            $("#alteracao_valor0").on('change', function(){ // define se o campo de prazo é alteravel ou não
                var opExec = $("#alteracao_valor0").val();
                
                if(opExec == 'SIM'){
                    
                    $("#tipoAlteracaoValor").prop("disabled", false);
                    $("#valor_total").prop("disabled", false);
                    $("#valor_retroativo").prop("disabled", false);
                }
            });

            $("#alteracao_valor1").on('change', function(){ // define se o campo de prazo é alteravel ou não
                var opExec = $("#alteracao_valor1").val();
                
                if(opExec == 'NAO'){
                    $("#tipoAlteracaoValor").prop("disabled", true);
                    $("#valor_total").prop("disabled", true);
                    $("#valor_retroativo").prop("disabled", true);

                    //$("#tipoAlteracaoValor").val("");
                    $("#valor_total").val("");
                    $("#valor_retroativo").val("");
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
                $.post("postDadosAditivo.php",{op:"RemoveDocAnex", marcador:docanexselec},function(data){
                     $("#FootDOcFiscal").html(data);
                });
            });

            $("#salvaContrato").on('click', function(){
                $('#tdload').show();
                let podesalvar = true;
                var cpf_Representante = "";
                if(!TestaCPF($("#repCPF").val()) && cpf_Representante != ""){
                    aviso("Informe: Um CPF  válido para o representante.");
                    $("#salvaContrato").prop("disabled", false);
                  //  podesalvar= false;
                }

                var valida = true;
                var tipo_aditivo = $("#tipo_aditivo").val();
                var cpf_cnpj = $("#cpfcnpjhid").val();
                if(tipo_aditivo != 13){
                    cpf_cnpj = '3';
                }
                if(cpf_cnpj == "" && tipo_aditivo == 13 ){
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $(".mensagem-texto").html("Informe: Fornecedor Cadastrado no SICREF.");
                    $(".error").html("Atenção!");
                    $("#tdmensagem").show();
                    $('#tdload').hide();
                    return false;
                }                var valida_arquivo = window.top.frames['frameArquivo'].validaAnexo();
                radioDoc = $("input[name='docanex']");
                //console.log(radioDoc.length);
                //return false;

                  
                if(!valida_arquivo || radioDoc.length == 0){
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $(".mensagem-texto").html("Informe: Documento anexo.");
                    $(".error").html("Atenção!");
                    $("#tdmensagem").show();
                    $('#tdload').hide();
                    return false;
                }
                
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
                                    $("#salvaContrato").prop("disabled", false);
                                }else{
                                    $('html, body').animate({scrollTop:0}, 'slow');
                                    $(".mensagem-texto").html(response.msm);
                                    $(".error").html("Atenção!");
                                    $(".error").css("color","#007fff");
                                    $("#tdmensagem").show();
                                    $('#tdload').hide();
                                    setTimeout(function(){ 
                                        window.location.href = "./CadAditivoPesquisar.php";
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
    <form action="CadAditivoIncluir.php" method="post" id="formContratoIncluir" name="CadAditivoIncluir">
        <input type="hidden" name="op" value="IncluirAditivo">
        <input type="hidden" name="idregistro" value="<?php echo $cod_contrato; ?>">
        
        <br><br><br><br>
        <table cellpadding="3" border="0" summary="">
            <!-- Caminho -->
            <tr>
                <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                <td align="left" class="textonormal" colspan="2">
                    <font class="titulo2">|</font>
                    <a href="../index.php">
                        <font color="#000000">Página Principal</font>
                    </a> > Aditivos > Incluir
                </td>
            </tr>
            <!-- Fim do Caminho-->

            <!-- Erro -->
            <tr>
                <td width="150"></td>
                <td align="left" colspan="2" id="tdmensagem" >
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
<?php $Mens = 0;} ?>            <!-- Corpo -->
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
                                                            <td class="titulo3" colspan="17" align="center" bgcolor="#75ADE6" valign="middle"> <b>INCLUIR ADITIVO</b></td>
                                                        </thead>
                                                        <tbody>

                                                            <tr>
                                                                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Número do Contrato/Ano
                                                                <td bgcolor="White"  style="font-size: 10.6667px;">
                                                                    <?php
                                                                        echo $empresa;
                                                                    ?>
                                                                </td>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td bgcolor="#DCEDF7" class="textonormal" width="225px">Número do Aditivo*
                                        <td class="textonormal">
                                            <input id="numero_registro" type="hidden" name="numero_registro"  style="font-size: 10.6667px;" value="<?php echo $codAditivo; ?>">
                                            <?php echo $codAditivo; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#DCEDF7" class="textonormal" width="225px">Tipo de Aditivo (SAGRES TCE)*</td>
                                        <td class="inputs">
                                            <select class="selectContrato" id="tipo_aditivo" name="tipo_aditivo" size="1" style="width:485px;" style="font-size: 10.6667px;">
                                                <option>Selecione o tipo de Aditivo</option>

                                                <?php foreach($tipos_aditivo as $tipo){ 
                                                     $selecionadoTipo = ($tipo->ctpadisequ == $_POST['tipo_aditivo']) ? 'selected="selected"' : '';
                                                ?>
                                                    <option class="textonormal" style="font-size: 10.6667px;" value="<?php echo $tipo->ctpadisequ;?>" <?php echo $selecionadoTipo; ?> >
                                                        <?php echo $tipo->etpadidesc; ?>
                                                    </option>
                                                <?php } ?>

                                                <?php
                                                /*
                                                foreach ($tipos_aditivo as $key => $tipo) {
                                                    //   var_dump($tipo);
                                                ?>
                                                    <option <?php if ($_POST['tipo_aditivo'] == $tipo->ctpadisequ) { ?> selected="true" <?php }; ?> name="tipoaditivo" id="tipoaditivo" value="<?php echo $tipo->ctpadisequ; ?>"><?php echo $tipo->etpadidesc; ?></option>
                                                <?php
                                                }
                                                */
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    
                                    <tr id="linhasForn">
                                            <td bgcolor="#DCEDF7" class="textonormal"  width="225px">Contratado* 
                                            
                                            <td class="textonormal" width="60px">
                                                <input type="radio" name="CNPJ_CPF" id="CNPJ_forn" value="1" <?php if( $CNPJ_CPF == 1 ){ echo "checked"; }?>>CNPJ*
                                                <input type="radio" name="CNPJ_CPF" id="CPF_forn" value="2" <?php if( $CNPJ_CPF == 2 ){ echo "checked"; }?>>CPF*
                                                <input class="textonormal" type="text" name="CnpjCpf" id="CnpjCpf_forn" size="18" style="font-size: 10.6667px;" value="<?php echo !empty($cpfCnpj) ? $cpfCnpj : ''; ?>">    
                                                
                                                <a href="javascript:enviar('Verificar');"><img src="../midia/lupa.gif" border="0"></a>
                                            </td>
                                        
                                            </tr>
                                        </tr>
                                        <tr id="linhasresForn">
                                            <td bgcolor="#DCEDF7" class="textonormal"  width="225px">
                                            Fornecedor*
                                            <td class="textonormal">
                                                <table id="_gridContratadoNovo">
                                                    <tbody><tr><td class="labels">
                                                        <span id="_panelLblCpfCnpj">
                                                        <label for="" style=";" class="textonormal" ><?php echo (strlen($CnpjCpf_forn) == 18) ? "CNPJ do Contratado :" : "CPF do Contratado :"; ?></label></span></td><td class="textonormal" colspan="3"><div id="_panelInputCpfCnpj" name="_panelInputCpfCnpj"><?php if(!is_null($CnpjCpf_forn)){  echo $CnpjCpf_forn; } ?><label>
                                                        <input type="hidden" id="cpfcnpjhid" name="cpfcnpjhid" value="<?php if(!is_null($CnpjCpf_forn)){  echo $CnpjCpf_forn; } ?>">
                                                            </label></div></td></tr>
                                                            <tr><td class="labels"><label for="" style=";" class="textonormal">Razão Social :</label></td><td class="textonormal" colspan="3" ><div id="_panelGroupRazao"><span id="_razaoSocialfornecedor"  style="font-size: 10.6667px;" name="razao"><?php if(!is_null($RazaoSocial)){ echo "$RazaoSocial"; } ?></span></div></td></tr>
                                                            <tr><td class="labels"><label for="" style=";" class="textonormal">Logradouro :</label></td><td class="textonormal" colspan="3"><span id="_logradourofornecedor" style="font-size: 10.6667px;"><?php if(!is_null($logradouro)){  echo "$logradouro"; } ?></span></td></tr>
                                                            <tr><td class="labels"><label for="" style=";" class="textonormal">Número :</label></td><td class="textonormal" colspan="3"><span id="_numerofornecedor" style="font-size: 10.6667px;"><?php if(!is_null($numero)){  echo "$numero"; } ?></span></td></tr>
                                                            <tr><td class="labels"><label for="" style=";" class="textonormal">Complemento :</label></td><td class="textonormal"><span id="_complementoLogradourofornecedor" style="font-size: 10.6667px;"><?php if(!is_null($compl)){  echo "$compl"; } ?></span></td><td class="labels"><label for="" style=";" class="textonormal">Bairro:</label></td><td class="textonormal"><span id="_bairrofornecedor"><?php if(!is_null($bairro)){  echo "$bairro"; } ?></span></td></tr>
                                                            <tr><td class="labels"><label for="" style=";" class="textonormal">Cidade :</label></td><td class="textonormal"><span id="_cidadefornecedor" style="font-size: 10.6667px;"><?php if(!is_null($cidade)){  echo "$cidade"; } ?></span></td><td class="labels"><label for="" style=";" class="textonormal">UF:</label></td><td class="textonormal"><span id="_estadofornecedor"><?php if(!is_null($estado)){  echo "$estado"; } ?></span></td></tr>
                                                            <tr><input type="hidden" name="aforcrsequ" value="<?php echo $codFornecedor?>"></tr>
                                                            </tbody></table>
                                            </td>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#DCEDF7" class="textonormal" width="225px">Justificativa do Aditivo *
                                        <td class="textonormal" style="font-size: 10.6667px;">
                                            <textarea id="objeto" name="objeto" cols="63" rows="4" maxlength="500"  rows="8"><?php if (!is_null($objetoDesc)) { echo $objetoDesc; } echo !empty($_POST['objeto']) ? $_POST['objeto'] : $objetoDesc; ?></textarea>
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
                                                <input type="radio" name="alteracao_prazo" id="alteracao_prazo0" <?php echo $_POST['alteracao_prazo'] == 'SIM' ? 'checked="checked"' : ''; ?> value="SIM" title="Há alteração de Prazo*"><label for="alteracao_prazo0"> Sim</label></td>
                                            <td style="font-size:10.6667px;">
                                                <input type="radio" name="alteracao_prazo" id="alteracao_prazo1" value="NAO" <?php echo $_POST['alteracao_prazo'] == 'NAO' ? 'checked="checked"' : ''; ?> title="Há alteração de Prazo*"><label for="alteracao_prazo1"> Não</label></td>
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
                                    <input type="radio" name="alteracao_valor" id="alteracao_valor0" value="SIM" <?php echo $_POST['alteracao_valor'] == 'SIM' ? 'checked="checked"' : ''; ?> title="Há alteração de Valor*"><label for="alteracao_valor0"> Sim</label></td>
                                <td style="font-size:10.6667px;">
                                    <input type="radio" name="alteracao_valor" id="alteracao_valor1" value="NAO" <?php echo $_POST['alteracao_valor'] == 'NAO' ? 'checked="checked"' : ''; ?> title="Há alteração de Valor*"><label for="alteracao_valor1"> Não</label></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Tipo de Alteração de Valor *
                <td bgcolor="White">
                    <select class="selectContrato" id="tipoAlteracaoValor" name="tipoAlteracaoValor" size="1" style="width:223px;">
                        <option>Selecione o tipo de Alteração...</option>
                        <option style="font-size: 10.6667px;" <?php echo $_POST['tipoAlteracaoValor'] == 'RP' ? 'selected="selected"' : ''; ?> value="RP">REPACTUAÇÃO</option>
                        <option style="font-size: 10.6667px;" <?php echo $_POST['tipoAlteracaoValor'] == 'RQ' ? 'selected="selected"' : ''; ?> value="RQ">REEQUILÍBRIO</option>
                        <option style="font-size: 10.6667px;" <?php echo $_POST['tipoAlteracaoValor'] == 'QT' ? 'selected="selected"' : ''; ?> value="QT">QUANTITATIVO</option>
                        <option style="font-size: 10.6667px;" <?php echo $_POST['tipoAlteracaoValor'] == 'QRP' ? 'selected="selected"' : ''; ?> value="QRP">QUANTITATIVO COM REPACTUAÇÃO</option>
                        <option style="font-size: 10.6667px;" <?php echo $_POST['tipoAlteracaoValor'] == 'QRQ' ? 'selected="selected"' : ''; ?> value="QRQ">QUANTITATIVO COM REEQUILÍBRIO</option>
                        <option style="font-size: 10.6667px;" <?php echo $_POST['tipoAlteracaoValor'] == 'QRQ' ? 'selected="selected"' : ''; ?> value="PP">PRORROGAÇÃO DE PRAZO</option>

                    </select>
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Valor Retroativo Repactuação /Reequilíbrio</td>
                <td class="textonormal">
                    <input id="valor_retroativo" class = "dinheiroNegativo" style="font-size: 10.6667px;" type="text" name="valor_retroativo" style="width:70px;">
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Valor Total do Aditivo</td>
                <td class="textonormal">
                    <input id="valor_total" class = "dinheiroNegativo" type="text" style="font-size: 10.6667px;" name="valor_total" style="width:70px;">
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Acréscimo do Prazo de Execução do Aditivo*</td>
                <td class="textonormal">
                    <input id="prazo" type="text" name="prazo" style="font-size: 10.6667px;" class="inteiroPositivo" maxlength="4" style="width:70px;">
                </td>
            </tr>

            <tr id="linhaTabelaOS">
                <td id="colunaVaziaOS" width="225px" bgcolor="#bfdaf2"></td>
                <td id="colunaDataInicioOS" bgcolor="#bfdaf2" style="width:415px;">
                    <table id="panelDataInicioOrdemServico" class="colorBlue">
                        <thead>
                            <tr>
                                <th class="titulo3" colspan="1" scope="colgroup">
                                    <span id="labelDataInicioOrdemServico">Data de Início</span></th>
                            </tr>
                        </thead>
                    </table>
                </td>
                <td id="colunaDataTerminoOS" bgcolor="#bfdaf2" style="width: 256px;">
                    <table id="panelDataTerminoOrdemServico" class="colorBlue">
                        <thead>
                            <tr>
                                <th class="titulo3" colspan="1" scope="colgroup">
                                    <span id="labelDataTerminoOrdemServico">Data de Término</span></th>
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
                        <input style="font-size: 10.6667px;" id="vigenciaDataInicio" type="text" name="vigenciaDataInicio" value="<?php echo !empty($_POST['vigenciaDataInicio']) ? $_POST['vigenciaDataInicio'] : ''; ?>" class="data" maxlength="10" size="12" title="">
                        <a id="calendarioVig" style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=CadAditivoIncluir&amp;Campo=vigenciaDataInicio','Calendario',220,170,1,0)">
                            <img src="../midia/calendario.gif" border="0" alt="">
                        </a>
                <td>
                    <input style="font-size: 10.6667px;" id="vigenciaDataTermino" type="text" name="vigenciaDataTermino" value="<?php echo !empty($_POST['vigenciaDataTermino']) ? $_POST['vigenciaDataTermino'] : ''; ?>" class="data" maxlength="10" size="12">
                    <a id="calendarioVig" style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=CadAditivoIncluir&amp;Campo=vigenciaDataTermino','Calendario',220,170,1,0)">
                        <img src="../midia/calendario.gif" border="0" alt="">
                    </a>
                </td>
                </span>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Execução*</td>
                    <td class="textonormal">
                        <span id="execucaoGroup">
                            <input style="font-size: 10.6667px;" id="execucaoDataInicio" type="text" name="execucaoDataInicio" value="<?php echo !empty($_POST['execucaoDataInicio']) ? $_POST['execucaoDataInicio'] : ''; ?>" class="data" maxlength="10" size="12">
                            <a id="calendarioExecIni" style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=CadAditivoIncluir&amp;Campo=execucaoDataInicio','Calendario',220,170,1,0)">
                                <img src="../midia/calendario.gif" border="0" alt="">
                            </a>
                        </span>
                    </td>                        
                    <td class="textonormal">
                        <span id="execucaoGroup">
                            <input style="font-size: 10.6667px;" id="execucaoDataFinal" type="text" name="execucaoDataFinal" class="data" value="<?php echo !empty($_POST['execucaoDataFinal']) ? $_POST['execucaoDataFinal'] : ''; ?>" maxlength="10" size="12">
                            <a id="calendarioExecFim" style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=CadAditivoIncluir&amp;Campo=execucaoDataFinal','Calendario',220,170,1,0)">
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
                    <textarea id="observacao" name="observacao" cols="50" rows="4" maxlength="1000"  rows="8"><?php if (!is_null($objetoDesc)) { echo $objetoDesc;} echo !empty($_POST['objeto']) ? $_POST['objeto'] : $objetoDesc; ?></textarea>
                </td>
            </tr>
            <!-- <tr id="linhaTabelaOS" bgcolor="#bfdaf2"> -->
            <!-- <td id="colunaDataInicioOS"><table id="panelDataInicioOrdemServico" class="colorBlue"> -->

            <tr>
                <thead bgcolor="#bfdaf2">
                    <tr>
                        <th class="titulo3" colspan="3" <?php echo $bloqueiacampo ? 'disabled="disabled"' : ''; ?> scope="colgroup">REPRESENTANTE LEGAL  (Só preencher se houver alteração)</th>
                    </tr>
                </thead>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Nome
                <td bgcolor="White">
                    <input style="font-size: 10.6667px; width:315px;" type="text" name="repNome" value="<?php echo !empty($_POST['repNome']) ? $_POST['repNome'] : ''; ?>" maxlength="120">
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">CPF
                <td bgcolor="White">
                    <input style="font-size: 10.6667px;" class="CPF" type="text" name="repCPF" value="<?php echo !empty($_POST['repCPF']) ? $_POST['repCPF'] : ''; ?>" id="repCPF" size="10">
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Cargo
                <td bgcolor="White">
                    <input style="font-size: 10.6667px; width:173px;" type="text" name="repCargo" value="<?php echo !empty($_POST['repCargo']) ? $_POST['repCargo'] : ''; ?>" maxlength="100">
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Identidade
                <td bgcolor="White">
                    <input style="font-size: 10.6667px;" type="text" name="repRG" value="<?php echo !empty($_POST['repRG']) ? $_POST['repRG'] : ''; ?>" maxlength="9" size="10">
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Órgão Emissor
                <td bgcolor="White">
                    <input style="font-size: 10.6667px;" type="text" name="repRgOrgao" value="<?php echo !empty($_POST['repRgOrgao']) ? $_POST['repRgOrgao'] : ''; ?>" maxlength="3" size="1">
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">UF da Identidade
                <td bgcolor="White">
                    <input style="font-size: 10.6667px;" type="text" name="repRgUF" value="<?php echo !empty($_POST['repRgUF']) ? $_POST['repRgUF'] : ''; ?>" maxlength="2" size="1" style="text-transform: uppercase">
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Cidade de Domicílio
                <td bgcolor="White">
                    <input style="font-size: 10.6667px; width:173px;" type="text" name="repCidade" value="<?php echo !empty($_POST['repCidade']) ? $_POST['repCidade'] : ''; ?>" maxlength="30">
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Estado de Domicílio
                <td bgcolor="White">
                    <input style="font-size: 10.6667px;" type="text" name="repEstado" value="<?php echo !empty($_POST['repEstado']) ? $_POST['repEstado'] : ''; ?>" maxlength="2" size="1" style="text-transform: uppercase">
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Nacionalidade
                <td bgcolor="White">
                    <input style="font-size: 10.6667px; width:173px;" type="text" name="repNacionalidade" value="<?php echo !empty($_POST['repNacionalidade']) ? $_POST['repNacionalidade'] : ''; ?>" maxlength="50">
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Estado Civil
                <td bgcolor="White">
                    <select class="selectContrato" id="repEstCiv" name="repEstCiv" size="1" style="width:173px;">
                        <option style="font-size: 10.6667px;" <?php echo $_POST['repEstCiv'] == 'Z' ? 'selected="selected"' : ''; ?> value="Z">Selecione o estado civil...</option>
                        <option style="font-size: 10.6667px;" <?php echo $_POST['repEstCiv'] == 'S' ? 'selected="selected"' : ''; ?> value="S">SOLTEIRO</option>
                        <option style="font-size: 10.6667px;" <?php echo $_POST['repEstCiv'] == 'C' ? 'selected="selected"' : ''; ?> value="C">CASADO</option>
                        <option style="font-size: 10.6667px;" <?php echo $_POST['repEstCiv'] == 'C' ? 'selected="selected"' : ''; ?> value="D">DIVORCIADO</option>
                        <option style="font-size: 10.6667px;" <?php echo $_POST['repEstCiv'] == 'V' ? 'selected="selected"' : ''; ?> value="V">VIÚVO</option>
                        <option style="font-size: 10.6667px;" <?php echo $_POST['repEstCiv'] == 'O' ? 'selected="selected"' : ''; ?> value="O">OUTROS</option>
                    </select>
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Profissão
                <td class="textonormal">
                    <input style="font-size: 10.6667px; width:173px;" id="Profissao" type="text" name="repProfissao" value="<?php echo !empty($_POST['repProfissao']) ? $_POST['repProfissao'] : ''; ?>" maxlength="50">
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">E-mail
                <td bgcolor="White" class="textonormal">
                    <input style="font-size: 10.6667px; width:173px; text-transform: none;" type="text" name="repEmail" value="<?php echo !empty($_POST['repEmail']) ? $_POST['repEmail'] : ''; ?>" maxlength="60" >
                </td>
                </td>
            </tr>
            <tr>
                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Telefone(s)
                <td bgcolor="White" class="textonormal">
                    <input style="font-size: 10.6667px;" class="telefone" type="text" name="repTelefone" value="<?php echo !empty($_POST['repTelefone']) ? $_POST['repTelefone'] : ''; ?>" size="13">
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
                                foreach ($DadosDocAnexo as $anexo) { ?>
                                    <tr bgcolor="#ffffff">
                                        <td><input type="radio" name="docanex" value="<?php echo $anexo->sequdocumento . '*' . $anexo->nomearquivo; ?>"></td>
                                        <td colspan="4"> <?php echo $anexo->nomearquivo; ?></td>
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
                <input type="hidden" name="cod_fornecedor" value="<?php echo $codFornecedor; ?>">
                
                <!-- <input type="hidden" name="tipo_aditivo" value="<?php //echo $_POST['tipo_aditivo']; ?>"> -->
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
