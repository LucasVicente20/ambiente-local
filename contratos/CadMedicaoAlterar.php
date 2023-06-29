<?php
#Programa: CadMedicaoAlterar.php
# Objetivo: CR 245752 - Corrigir erro de SQL por estourar o limite da variavél
# Alterado: Osmar Lucas Celestino 31/03/2021
#----------------------------------------------------------------------
# Autor:    Madson Felix
# Data:     28/04/2021
# CR #246939
# -------------------------------------------------------------------------
# Autor:    Marcello Albuquerque
# Data:     28/07/2021
# CR #251288
# -------------------------------------------------------------------------
# Autor:    João Madson Felix
# Data:     04/11/2022
# CR #274878
# -------------------------------------------------------------------------



# Acesso ao arquivo de funções #
include "../funcoes.php";
require_once "ClassMedicao.php";
require_once "ClassContratos.php";
require_once "ClassContratosFuncoesGerais.php";

if($_SESSION['documento_anexo'] == NULL){
    unset($_SESSION['documento_medicao']);
    unset($_SESSION['nome_arquivo']);
    unset($_SESSION['arquivo_download']);
    unset($_SESSION['documento']);
}

# Executa o controle de segurança	#
session_start();
unset($_SESSION['documento']);
$cusupocodi = $_SESSION['_cusupocodi_'];

$objContrato = new ClassMedicao();
$objFuncGerais = new ContratosFuncoesGerais();
$ObjContrato2 = new Contrato();
$dadosContratos = (object) array();

$contrato = $_GET['codcontrato'];
$registro = $_GET['rg'];

$contrato_pesq = $objContrato->getContrato($contrato);

$medicoes = $objContrato->getMedicaoContrato($contrato, $registro);
$valorContrato = $objFuncGerais->valorGlobal($contrato);
$saldoAExecutar = $objFuncGerais->saldoAExecutar($contrato);
$valorTotalMedicao = $objContrato->getValorTotalMedicao($contrato);


$ectrpcnumf = $contrato_pesq[0]->ectrpcnumf;
$ectrpcobj = $contrato_pesq[0]->ectrpcobje;
$cdocpcsequ = $contrato_pesq[0]->cdocpcsequ;
$cmedconane = $medicoes[0]->cmedconane;
//$imedcoanex = $medicoes[0]->imedcoanex;
$cusupocodi = $medicoes[0]->cusupocodi;
$tmedcoulat = $medicoes[0]->tmedcoulat;
$valor_base = $contrato_pesq[0]->vctrpcglaa ? $contrato_pesq[0]->vctrpcglaa : 0;

$doc = (object) $_SESSION['documento_medicao'];

if($doc->nome_arquivo != NULL){
        
            $DadosDocAnexo[0]  =  (object) array(
                                        'nome_arquivo'       =>$doc->nome_arquivo,
                                        'arquivo'           => $doc->arquivo,
                                        'sequdoc'     => 1,
                                        'sequarquivo'     =>  1,
                                        'data_inclusao'  => $doc->data_inclusao,
                                        'usermod'           => $doc->usermod,
                                       // 'arquivo'           => $doc['arquivo'],
                                        'ativo'             => 'S'
                                    );
            //  }
    }else{
        var_dump('2');
        unset($_SESSION['documento_medicao']);
        $DadosDocAnexo = $objContrato->GetDocumentoMedicao($cdocpcsequ, $registro);
        
        $k = 0;
        $_SESSION['arquivo_download'][$k] = $DadosDocAnexo[0]->imedcoanex;
        
        //var_dump( $_SESSION['arquivo_download']);
        
        $_SESSION['documento_medicao'][0]  = (object) array(
                                        'nome_arquivo'       =>$DadosDocAnexo[0]->cmedconane,
                                        'arquivo'           => bin2hex(base64_decode($DadosDocAnexo[0]->imedcoanex)),
                                        'sequdoc'     => 1,
                                        'sequarquivo'     => 1,
                                        'data_inclusao'  => $DadosDocAnexo[0]->tmedcoulat,
                                        'usermod'           => $cusupocodi,
                                        // 'arquivo'           => $doc->arquivo,
                                        'ativo'             => 'S'
                                    );
                $DadosDocAnexo = $_SESSION['documento_medicao'];
    }
    
//    die;
?>
<html>
<?php

# Carrega o layout padrão #
layout();
unset($_SESSION['documento_anexo']);
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>​
<script language="javascript" type="">

var sessao_file = <?php $_SESSION['documento_medicao']; ?>

        function Submete(Destino){
            document.CadMedicaoIncluir.Destino.value = Destino;
            document.CadMedicaoIncluir.submit();
        }

        function Subirarquivo(){
            window.top.frames['frameArquivo'].subirArquivo();
        }
        
        function validarValorMedicao(){
            var valorMedicao;
            var valorFormatado;
            var valorContrato;
            var saldoExecutar;

                valor_base = <?php echo $valor_base; ?>;
                // valorContrato = <?php echo $objFuncGerais->valorGlobal($contrato); ?>;
                saldoExecutar = <?php echo $objFuncGerais->saldoAExecutar($contrato, false); ?>;
                valorMedicao = $("#vmedcovalm").val().replace( '.', '' ).replace( ',', '.' );
                // valorMedicaoAtual = '<?php echo $medicoes[0]->vmedcovalm; ?>';
                // valorFormatado = parseFloat(valorMedicaoAtual) + parseFloat(valorContrato);

                // valorFormatado2 = valorFormatado.toLocaleString('pt-br',{style: 'currency', currency: 'BRL'});

                if(parseFloat(saldoExecutar) < parseFloat(valorMedicao)){
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $(".mensagem-texto").html("O valor da medição não pode ser maior que o saldo a executar.");
                    $(".error").html("Erro!");
                    $("#tdmensagem").show();
                    // $("#salvaMedicao").attr('disabled', "disabled");
                    return false;
                }else{
                    $("#salvaMedicao").removeAttr('disabled');
                    $('html, body').animate({scrollTop:0}, 'slow');
                    
                    $(".error").html("Erro!");
                    $("#tdmensagem").hide();
                    return true;
                }
                
          //return true;
        }

        $(document).ready(function() { 
            
            $("#vmedcovalm").on('blur', function(){
               validarValorMedicao();
            });

            var retorno = "./CadMedicaoPesquisar.php";

            $('#numcontrato').mask('9999.9999/9999');
            $('.data').mask('99/99/9999');
            
            var teste = $('#vmedcovalm').val().toLocaleString('pt-BR');
          
            $("#btnvoltar").on('click', function(){
                contrato = <?php echo $contrato; ?>;
                $("#formMedicaoAlterar").attr("action","CadMedicaoManter.php");
                $("#formMedicaoAlterar").submit();
            });

            $("#file").on('change', function(){
                var file = $("#file").val();
                $.post("postDados.php",{op:"InsereArquivo", arquivo:file}, function(data){
                });
            });

            $('#btnIncluirAnexo').live("click",()=>{
                $('#loadArquivo').show();
            })
            $("#btnRemoveAnexo").live("click",function(){
                const docanexselec = $("input[name='docanex']:checked").val();
                $.post("postDadosMedicao.php",{op:"RemoveDocAnex",marcador:docanexselec},function(data){
                     $("#FootDOcFiscal").html(data);
                });
            });

            $("#execucaoDataInicio").on("blur", function(){
                dei = $("#execucaoDataInicio").val();
                def = $("#execucaoDataTermino").val();
                dataExecucaoInicio = dei.split('/');
                dataExecucaoFim    = def.split('/');
                novaData = new Date(parseInt(dataExecucaoInicio[2]),parseInt(dataExecucaoInicio[1]-1),parseInt(dataExecucaoInicio[0]));
                novaDataFim = new Date(parseInt(dataExecucaoFim[2]),parseInt(dataExecucaoFim[1]-1),parseInt(dataExecucaoFim[0]));
                if(novaData > novaDataFim){
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $(".mensagem-texto").html("A data final não pode ser menor que a data inicial");
                    $(".error").html("Atenção!");
                    $("#tdmensagem").show();
                }
            });
          
            $("#execucaoDataTermino").on("blur", function(){
                dei = $("#execucaoDataInicio").val();
                def = $("#execucaoDataTermino").val();
                dataExecucaoInicio = dei.split('/');
                dataExecucaoFim    = def.split('/');
                novaData = new Date(parseInt(dataExecucaoInicio[2]),parseInt(dataExecucaoInicio[1]-1),parseInt(dataExecucaoInicio[0]));
                data_fim = novaDataFim = new Date(parseInt(dataExecucaoFim[2]),parseInt(dataExecucaoFim[1]-1),parseInt(dataExecucaoFim[0]));
                if(novaData > novaDataFim){
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $(".mensagem-texto").html("A data final não pode ser menor que a data inicial");
                    $(".error").html("Atenção!");
                    $("#tdmensagem").show();
                }
            });

            $("#salvaMedicao").on('click', function(){
                $('html, body').animate({scrollTop:0}, 'slow');
                $('#tdload').show();
                contrato = <?php echo $contrato; ?>;
                registro = <?php echo $registro; ?>;
                
                dei = $("#execucaoDataInicio").val();
                def = $("#execucaoDataTermino").val();
                dataExecucaoInicio = dei.split('/');
                dataExecucaoFim    = def.split('/');
                
                numMedicao = $("#amedconume").val();

                valor = $("#vmedcovalm").val();
                radioDoc = $("input[name='docanex']");


                novaData = new Date(parseInt(dataExecucaoInicio[2]),parseInt(dataExecucaoInicio[1]-1),parseInt(dataExecucaoInicio[0]));
                novaDataFim = new Date(parseInt(dataExecucaoFim[2]),parseInt(dataExecucaoFim[1]-1),parseInt(dataExecucaoFim[0]));
               retornoValidacao =  validarValorMedicao();
               
               if(!retornoValidacao){
                   console.log("Entrou na validação");
                   $('#tdload').hide();
                   return false;
               }
               
                var strMensagem = "";
                if(numMedicao == ""){
                    strMensagem += " Número da medição,";
                }
                if(valor == ""){
                    strMensagem += " Valor da medição,";
                }
                if(dei == "" || def == ""){
                    strMensagem += " Período da medição,";
                }
                if(radioDoc.length == 0){
                    strMensagem += " Documento anexo,";
                }
                if(radioDoc.length > 100){
                    strMensagem += " Nome do Documento deve ser menor que 100,";
                }
                
                strMensagem = strMensagem.replace(/,*$/, "");
                if(strMensagem != ""){
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $(".mensagem-texto").html("Informe:"+strMensagem+".");
                    $(".error").html("Atenção!");
                    $("#tdmensagem").show();
                    $('#tdload').hide();
                    return false;
                }

                if(novaData <= novaDataFim){ 
                    $.post("postDadosMedicao.php",$("#formMedicaoAlterar").serialize(), function(data){
                        const response = JSON.parse(data);

                        if(!response.status){
                            $('html, body').animate({scrollTop:0}, 'slow');
                            $(".mensagem-texto").html(response.msm);
                            $(".error").html("Erro!");
                            $("#tdmensagem").show();
                            $('#tdload').hide();
                        }else{
                            $("#mensagem").val("Medição alterada com sucesso.");
                            $("#formMedicaoAlterar").attr("action","CadMedicaoManter.php");
                            $("#formMedicaoAlterar").submit();
                           // alert("Medição alterada com sucesso!");
                            $('html, body').animate({scrollTop:0}, 'slow');
                            //$("#tdmensagem").show();
                            $('#tdload').hide();
                        }
                    
                    })
                }else{
                    $('html, body').animate({scrollTop:0}, 'slow');
                    $(".mensagem-texto").html("Informe: A data final não pode ser menor que a data inicial.");
                    $(".error").html("Atenção!");
                    $("#tdmensagem").show();
                    $('#tdload').hide();
                }
            });
        });

        <?php MenuAcesso(); unset($_SESSION['documento_anexo']);?>
       
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css?v=<?php echo time(); ?>">
    <style>
        #tabelaficais thead tr td {
            align-items: center;
            white-space: nowrap;
            -webkit-user-modify: read-write-plaintext-only;
        }

        #tabelaficais tbody tr td {
            align-items: center;
            white-space: nowrap;
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
    <form action="CadMedicaoAlterar.php" method="post" id="formMedicaoAlterar" name="CadMedicaoIncluir">
        <input type="hidden" name="op" value="AlterarMedicao">
        <input type="hidden" name="contrato" value="<?php echo $contrato; ?>">
        <input type="hidden" name="idregistro" value="<?php echo $contrato; ?>">
        <input type="hidden" name="registro" value="<?php echo $registro; ?>">
        <input type="hidden" name="mensagem"  id="mensagem" value="">
        
        <br><br><br><br>
        <table cellpadding="3" border="0" summary="">
            <!-- Caminho -->
            <tr>
                <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                <td align="left" class="textonormal" colspan="2">
                    <font class="titulo2">|</font>
                    <a href="../index.php">
                        <font color="#000000">Página Principal</font>
                    </a> > Contratos > Medição
                </td>
            </tr>
            <!-- Fim do Caminho-->

            <!-- Erro -->
            <tr>
                <td width="150"></td>
                <td align="left" colspan="2" id="tdmensagem">
                    <div class="mensagem">
                        <div class="error">
                        </div>
                        <span class="mensagem-texto">
                        </span>
                    </div>
                </td>
            </tr>
            <!-- Fim do Erro -->
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
            <!-- Corpo -->
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
                                                    <table class="textonormal" id="scc_material" summary="" bordercolor="#75ADE6" style="border: 1px solid #75ade6; border-radius: 4px;" width="100%">
                                                        <thead>
                                                            <td class="titulo3" colspan="17" align="center" bgcolor="#75ADE6" valign="middle"> <b>ALTERAR MEDIÇÃO</b></td>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Número do Contrato/Ano
                                                                    <td bgcolor="White">
                                                                        <?php
                                                                            echo $ectrpcnumf;
                                                                        ?>
                                                                    </td>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td bgcolor="#DCEDF7" class="textonormal" width="225px">Objeto
                                                                <td class="textonormal">
                                                                    <?php
                                                                    echo $ectrpcobj;
                                                                    ?>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                        <td bgcolor="#DCEDF7" class="textonormal" width="225px">Saldo a Executar</td>
                                        <td class="textonormal">
                                            R$ <?php 
                                                echo $saldoAExecutar;
//                                                echo number_format($vctrpcglaa,2,",",".");
                                            ?>                                              
                                            <input type="hidden" name="saldoexecutar" id="saldoexecutarK" value="<?php  echo $saldoAExecutar;?>">  
                                        </td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#DCEDF7" class="textonormal" width="225px">Número de Medição*</td>
                                        <td class="textonormal">
                                            <input style="font-size: 10.6667px;" id="amedconume" type="text" name="amedconume" class="inteiroPositivo" maxlength="6" value="<?php echo $medicoes[0]->amedconume; ?>" style="width:70px;" >
                                        </td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#DCEDF7" class="textonormal" width="225px">Valor da Medição*</td>
                                        <td class="textonormal">
                                            <input id="vmedcovalm" type="text" style="font-size: 10.6667px;" name="vmedcovalm" onchange="return validarValorMedicao()" value="<?php echo number_format((floatval($medicoes[0]->vmedcovalm) - floatval('0.0000')),4,',','.'); ?>" style="width:121px;" class="dinheiro4casas" >
                                        </td>
                                    </tr>
                                    <tr>
                                        <td bgcolor="#DCEDF7" class="textonormal" width="225px">Período da Medição*</td>
                                        <td class="textonormal">
                                            <span id="execucaoGroup">
                                                <input id="execucaoDataInicio" style="font-size: 10.6667px;" type="text" name="execucaoDataInicio" value="<?php echo date('d/m/Y', strtotime($medicoes[0]->dmedcoinic)); ?>" class="data" maxlength="10" size="12">
                                                <a id="calendarioExecIni" style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=CadMedicaoIncluir&amp;Campo=execucaoDataInicio','Calendario',220,170,1,0)">
                                                    <img src="../midia/calendario.gif" border="0" alt="">
                                                </a>
                                                A
                                                <input id="execucaoDataTermino" style="font-size: 10.6667px;" type="text" name="execucaoDataTermino" value="<?php echo date('d/m/Y', strtotime($medicoes[0]->dmedcofinl)); ?>" class="data" maxlength="10" size="12">
                                                <a id="calendarioExecTerm" style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=CadMedicaoIncluir&amp;Campo=execucaoDataTermino','Calendario',220,170,1,0)">
                                                    <img src="../midia/calendario.gif" border="0" alt="">
                                                </a>
                                        </td>
                                        </span>
                                        </span>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#DCEDF7" class="textonormal" width="225px">Observação
                                <td class="textonormal">
                                    <textarea id="emedcoobse" name="emedcoobse" cols="50" rows="4" maxlength="500" onkeyup="return limitChars(this,500)" rows="8"><?php if (!is_null($medicoes[0]->emedcoobse)) {echo trim($medicoes[0]->emedcoobse);} ?></textarea>
                                </td>
                            </td>
                        </tr>

            <tr>
                <thead class="titulo3" bgcolor="#bfdaf2">
                    <tr>
                        <th colspan="14" scope="colgroup">ANEXAR DOCUMENTOS</th>
                    </tr>
                </thead>
            </tr>
            <tr>
                <td colspan="14">
                <table id="tabelaficais" bgcolor="#bfdaf2" class="textonormal" width="100%">
                    <tbody >
                    <tr >
                        <td bgcolor="#DCEDF7">Anexação de Documentos</td>
                        <td colspan="1" style="border:none"> 
                            <iframe src="formuploadMedicao.php" id="frameArquivo" height="39" width="520"  name="frameArquivo" frameborder="0"></iframe>
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
                    <tfoot id="FootDOcFiscal" >
                        <tr class="FootFiscaisDoc">
                            <td></td>
                            <td colspan="4">ARQUIVO</td>
                            <td colspan="4">DATA DA INCLUSÃO</td>
                        </tr>
                        <?php 
                        if(!empty($DadosDocAnexo[0]->arquivo)){
                            
                             ?>
                            <tr bgcolor="#ffffff">
                                <td>
                                    <input type="radio" name="docanex">
                                    <input type="hidden" name="nomedoc" value="<?php echo $DadosDocAnexo[0]->sequdocumento . '*' . $DadosDocAnexo[0]->nome_arquivo; ?>">
                                </td>
                                <td colspan="4">
                                    <a class="" href="downloadDocContratoConsolidado.php?arquivo=<?php echo $k;?>&nome=<?php echo $DadosDocAnexo[0]->nome_arquivo; ?>" id="documento<?php echo $k;?>" rel="<?php echo $DadosDocAnexo[0]->nome_arquivo; ?>"><?php echo $DadosDocAnexo[0]->nome_arquivo; ?></a>
                                    <?php //echo $DadosDocAnexo[0]->nomearquivo;?>
                                </td>
                                <td colspan="4"> <?php echo $DadosDocAnexo[0]->datacadasarquivo;?></td>
                                
                            </tr>
                            <?php //}
                                }else{
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
                <!-- disabled="true" -->
                    <button type="button" name="salvaMedicao" class="botao"  id="salvaMedicao">Salvar</button> 
                    <button type="button" name="btnvoltar" class="botao" id="btnvoltar">Voltar</button>
                </td>
                </tr>
                    <input type="hidden" id="Destino" name="Destino">
                </td>
            </tr>


        </table>
    </form>

</body>

</html>