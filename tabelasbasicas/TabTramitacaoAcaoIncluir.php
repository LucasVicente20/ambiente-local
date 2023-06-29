<?php
# ------------------------------------------------------------------
# Portal de Compras
# Programa: TabTramitacaoAcaoIncluir.php
# Autor:    Lucas Baracho
# Data:     11/07/2018
# Objetivo: Tarefa Redmine 199049
# ------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();


# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Critica          = $_POST['Critica'];
    $grupo            = trim($_POST['GrupoCodigo']);
    $acaoDescricao    = strtoupper2(trim($_POST['AcaoDescricao']));
    $ordem            = trim($_POST['Ordem']);
    $prazo            = trim($_POST['Prazo']);
    $situacao         = $_POST['Situacao'];
    $acaoInicial      = $_POST['AcaoInicial'];
    $acaoFinal        = $_POST['AcaoFinal'];
    $informarComissao = $_POST['InformarComissao'];
    $obrigarAnexo     = $_POST['ObrigarAnexo'];
    $enviarTodos      = $_POST['EnviarTodos'];
    $dadosChecklist      = $_POST['dados'];
}


//carrega o nome do usuário
$db = Conexao();

$sqlUsuario = "SELECT u.eusuporesp
FROM sfpc.tbusuarioportal u
where u.cusupocodi =". $_SESSION['_cusupocodi_'];

$usuarioDesc = '';

$res = $db->query($sqlUsuario);

if (PEAR::isError($res)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlUsuario");
} else {
    $Linha = $res->fetchRow();
    $usuarioDesc = $Linha[0];

}





if ($Critica == 1) {
    $Mens = 0;
    
    # Crítica dos campos #
    $Mensagem = "Informe: ";

    if ($grupo == "") {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.Acao.GrupoCodigo.focus();\" class=\"titulo2\">Grupo</a>";    
    }

    if ($acaoDescricao == "") {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
    
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.Acao.AcaoDescricao.focus();\" class=\"titulo2\">Ação</a>";
    }

    if ($ordem == "") {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
    
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.Acao.Ordem.focus();\" class=\"titulo2\">Ordem de exibição</a>";
    } else {
        if (!SoNumeros($ordem)) {
            $Mens = 1;
            $Tipo = 2;
            $Mensagem = "<a href=\"javascript:document.Acao.Ordem.focus();\" class=\"titulo2\">Ordem de exibição inválida</a>";
        }
    }

    if ($prazo == "") {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
    
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.Acao.Prazo.focus();\" class=\"titulo2\">Prazo</a>";
    } else {
        if (!SoNumeros($prazo)) {
            $Mens = 1;
            $Tipo = 2;
            $Mensagem = "<a href=\"javascript:document.Acao.Prazo.focus();\" class=\"titulo2\">Prazo inválido</a>";
        }
    }



    # Verifica duplicidade da ação para o grupo #
    if ($Mens == 0) {
        $sql = "SELECT  COUNT(CTACAOSEQU)
                FROM    SFPC.TBTRAMITACAOACAO
                WHERE   RTRIM(LTRIM(ETACAODESC)) = '$acaoDescricao'
                        AND CGREMPCODI = $grupo";
    
        $res = $db->query($sql);

        if (PEAR::isError($res)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $Linha = $res->fetchRow();
            $qtd = $Linha[0];
        
            if ($qtd > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = "<a href=\"javascript:document.Acao.AcaoDescricao.focus();\" class=\"titulo2\">Ação já cadastrada</a>";
            }
        }
    }

    # Verifica duplicidade da ordem para o grupo #
    if ($Mens == 0) {
        $sql = "SELECT  COUNT(ATACAOORDE)
                FROM    SFPC.TBTRAMITACAOACAO
                WHERE   ATACAOORDE = $ordem AND CGREMPCODI = $grupo";
    
        $res = $db->query($sql);

        if (PEAR::isError($res)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $Linha = $res->fetchRow();
            $qtd = $Linha[0];
        
            if ($qtd > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = "<a href=\"javascript:document.Acao.Ordem.focus();\" class=\"titulo2\">Ordem de exibição já cadastrada</a>";
            }
        }
    }

    # Verifica duplicidade de ação inicial para o grupo #
    if ($Mens == 0 && $acaoInicial == 'S') {
        $sql = "SELECT  COUNT(FTACAOINIC)
                FROM    SFPC.TBTRAMITACAOACAO
                WHERE   CGREMPCODI = $grupo
                        AND FTACAOINIC = 'S' ";
    
        $res = $db->query($sql);

        if (PEAR::isError($res)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $Linha = $res->fetchRow();
            $qtd = $Linha[0];
        
            if ($qtd > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = "<a href=\"javascript:document.Acao.AcaoInicial.focus();\" class=\"titulo2\">Este grupo já possui ação inicial cadastrada</a>";
            }
        }
    }

    # Verifica duplicidade de ação final para o grupo #
    if ($Mens == 0 && $acaoFinal == 'S') {
        $sql = "SELECT  COUNT(FTACAOFINA)
                FROM    SFPC.TBTRAMITACAOACAO
                WHERE   CGREMPCODI = $grupo
                        AND FTACAOFINA = 'S' ";
    
        $res = $db->query($sql);

        if (PEAR::isError($res)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $Linha = $res->fetchRow();
            $qtd = $Linha[0];
        
            if ($qtd > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = "<a href=\"javascript:document.Acao.AcaoFinal.focus();\" class=\"titulo2\">Este grupo já possui ação final cadastrada</a>";
            }
        }
    }

    # Insere a ação #
    if ($Mens == 0) {
        $sql = "SELECT  MAX(CTACAOSEQU)
                FROM    SFPC.TBTRAMITACAOACAO";
    
        $res = $db->query($sql);

        if (PEAR::isError($res)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $Linha = $res->fetchRow();
            $codigo = $Linha[0] + 1;

            $codUsuario = $_SESSION['_cusupocodi_'];
            $data = date("Y-m-d H:i:s");
        
            $db->query("BEGIN TRANSACTION");
        
            $sql  = "INSERT INTO SFPC.TBTRAMITACAOACAO ( ";
            $sql .= "CTACAOSEQU, CGREMPCODI, ETACAODESC, ATACAOORDE, ";
            $sql .= "ATACAOPRAZ, FTACAOSITU, CUSUPOCODI, TTACAOULAT, ";
            $sql .= "FTACAOINIC, FTACAOFINA, FTACAOCOMI, FTACAOANEX, ";
            $sql .= "FTACAOTUSU ";
            $sql .= ") VALUES ( ";
            $sql .= "$codigo, $grupo, '$acaoDescricao', $ordem, ";
            $sql .= "$prazo, '$situacao', $codUsuario, '$data', ";
            $sql .= "'$acaoInicial', '$acaoFinal', '$informarComissao', '$obrigarAnexo', ";
            $sql .= "'$enviarTodos') ";
                           
            $res = $db->query($sql);
        
            if (PEAR::isError($res)) {
                $db->query("ROLLBACK");
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
            } else {
                $db->query("COMMIT");
                $db->query("END TRANSACTION");
                
                
                //incluir CHECKLIST DE ACOES
                $arrChecklist = json_decode($dadosChecklist); 

                for($i = 0; $i < count($arrChecklist); $i++){

                    $objChecklist = $arrChecklist[$i];

                    $sql = "SELECT ctacacsequ
                            FROM sfpc.tbtramitacaoacaochecklist order by ctacacsequ DESC limit 1";
                
                    $res = $db->query($sql);


                    if (PEAR::isError($res)) {
                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                    }else{

                        $Linha = $res->fetchRow();
                        $seqchecklist = $Linha[0] + 1;
                      

                        $db->query("BEGIN TRANSACTION");
            
                        $sql  = "INSERT INTO sfpc.tbtramitacaoacaochecklist ( ";
                        $sql .= "ctacaosequ, ctacacsequ, etacacdesc, atacacorde, ";
                        $sql .= " ftacacsitu, cusupocodi, ttacaculat ";
                        $sql .= ") VALUES ( ";
                        $sql .= "$codigo, $seqchecklist, '$objChecklist->descricao', $objChecklist->ordem, 'A', $objChecklist->codUsuario, now() ); ";
                        
                        $res = $db->query($sql);
                    
                        if (PEAR::isError($res)) {
                            $db->query("ROLLBACK");
                            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                        }else{
                            $db->query("COMMIT");
                            $db->query("END TRANSACTION");
                        }

                    }
                }





                $Mens       = 1;
                $Tipo       = 1;
                $Mensagem   = "Ação incluída com sucesso";
                unset($dadosChecklist);
                $acaoDescricao    = "";
                $grupo            = "";
                $ordem            = "";
                $prazo            = "";
                $situacao         = "";
                $acaoInicial      = "";
                $acaoFinal        = "";
                $informarComissao = "";
                $obrigarAnexo     = "";
                $enviarTodos      = "";
            }
        }
    }
    $db->disconnect();
}
?>
<html>
<?php
# Carrega o layout padrão @
layout();
?>
<script language="javascript" type="">
    //<!--
    <?php MenuAcesso(); ?>

    function CaracteresObjeto(text,campo){
        input = document.getElementById(campo);
        input.value = text.value.length;
    }


    //-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="TabTramitacaoAcaoIncluir.php" method="post" name="Acao">
        <br> <br> <br> <br> <br>
        <table cellpadding="3" border="0">
            <!-- Caminho -->
            <tr>
                <td width="100">
                    <img border="0" src="../midia/linha.gif" alt="">
                </td>
                <td align="left" class="textonormal">
                    <font class="titulo2">|</font>
                    <a href="../index.php">
                        <font color="#000000">Página Principal</font>
                    </a>
                    > Tabelas > Licitações > Tramitação > Ação > Incluir
                </td>
            </tr>
            <!-- Fim do Caminho-->
            <!-- Erro -->

	        <tr>
                <td width="100"></td>
                <td align="left" colspan="2" id="erroTd">
                    <?php 
                    if ($Mens == 1) {
                        ExibeMens($Mensagem,$Tipo,1);
                    }  
                    ?>
                </td>
            </tr>

	        <!-- Fim do Erro -->
            <!-- Corpo -->
            <tr>
                <td width="100"></td>
                <td class="textonormal">
                    <table border="0" cellspacing="0" cellpadding="3">
                        <tr>
                            <td class="textonormal">
                                <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
                                    <tr>
                                        <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">INCLUIR - AÇÃO</td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal">
                                            <p align="justify">Para incluir uma nova ação, informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.</p>
                                        </td>
                                    </tr>
                                   
                                    <tr>
                                        <td>
                                            <table class="textonormal" border="0" align="left" class="caixa">
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Grupo*</td>
                                                    <td class="textonormal">
                                                        <select name="GrupoCodigo" class="textonormal">
                                                            <option value="<?php $grupo; ?>">Selecione um grupo...</option>
                                                            <?php 
                                                            # Mostra os grupos #
                		                                    $db = Conexao();
                                                            
                                                            $sql = "SELECT  CGREMPCODI, EGREMPDESC
                                                                    FROM    SFPC.TBGRUPOEMPRESA
                                                                    WHERE   CGREMPCODI <> 0
                                                                    ORDER BY EGREMPDESC ASC";
                                                        
                                                            $result = $db->query($sql);

                                                            if (PEAR::isError($result)){
										                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										                    } else {
												                while ($Linha = $result->fetchRow()) {
		        	      				                            echo"<option value=\"$Linha[0]\">$Linha[1]</option>\n";
			                	                                }
			                                                }
                                                            $db->disconnect();
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Ação*</td>
                                                    <td class="textonormal">

                                                        <textarea id="AcaoDescricao" name="AcaoDescricao"
                                                        cols="50"
                                                        rows="4"
                                                        maxlength="400"
                                                        onkeyup="javascript:CaracteresObjeto(this,'NCaracteresObjeto')" onblur="javascript:CaracteresObjeto(this,'NCaracteresObjeto')"
                                                        onselect="javascript:CaracteresObjeto(this,'NCaracteresObjeto')"
                                                        class="textonormal"><?php echo (!empty($acaoDescricao)) ? $acaoDescricao : ''; ?></textarea>
                                                        <br><font class="textonormal">máximo de 400 caracteres</font>
                                                        <input type="text" id="NCaracteresObjeto" disabled name="NCaracteresObjeto" readonly="" size="3" value="0" class="textonormal"><br>
             
                                                        <!--<input type="text" name="AcaoDescricao" value="<?php echo $acaoDescricao; ?>" size="45" maxlength="400" class="textonormal"> -->
                                                        <input type="hidden" name="Critica" value="1">
                                                    </td>
                                                </tr>                                                
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Ordem de exibição*</td>
                                                    <td class="textonormal">
                                                        <input type="text" name="Ordem" value="<?php echo $ordem; ?>" size="3" maxlength="3" class="textonormal">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Prazo estimado em dias úteis*</td>
                                                    <td class="textonormal">
                                                        <input type="text" name="Prazo" value="<?php echo $prazo; ?>" size="3" maxlength="3" class="textonormal">
                                                        <input type="hidden" name="Critica" value="1">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Situação</td>
                                                    <td class="textonormal">
                                                        <select name="Situacao" size="1" value="<?php $situacao; ?>" class="textonormal">
                                                            <option value="A">ATIVO</option>
                                                            <option value="I">INATIVO</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Ação Inicial</td>
                                                    <td class="textonormal">
                                                        <select name="AcaoInicial" size="1" value="<?php= $acaoInicial; ?>" class="textonormal">
                                                            <option value="N">NÃO</option>
                                                            <option value="S">SIM</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Ação Final</td>
                                                    <td class="textonormal">
                                                        <select name="AcaoFinal" size="1" value="<?php $acaoFinal; ?>" class="textonormal">
                                                            <option value="N">NÃO</option>
                                                            <option value="S">SIM</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Informar Comissão</td>
                                                    <td class="textonormal">
                                                        <select name="InformarComissao" size="1" value="<?php $informarComissao; ?>" class="textonormal">
                                                            <option value="N">NÃO</option>
                                                            <option value="S">SIM</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Obrigatoriedade de anexação de documento</td>
                                                    <td class="textonormal">
                                                        <select name="ObrigarAnexo" size="1" value="<?php $obrigarAnexo; ?>" class="textonormal">
                                                            <option value="N">NÃO</option>
                                                            <option value="S">SIM</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" bgcolor="#DCEDF7">Enviar para todos os usuários do agente</td>
                                                    <td class="textonormal">
                                                        <select name="EnviarTodos" size="1" value="<?php $enviarTodos; ?>" class="textonormal">
                                                            <option value="N">NÃO</option>
                                                            <option value="S">SIM</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>  
                                                    <td class="textonormal" bgcolor="#DCEDF7">Item Checklist</td>
                                                    <td class="textonormal">
                                                        <input type="text" name="txtChecklist" id="txtChecklist"  value="<?php echo $txtChecklist; ?>" size="45" maxlength="100" class="textonormal"> 
                                                        <input type="button" name="Incluir" value="Incluir Item" class="botao" onClick="addItemChecklist()">

                                                    </td>
                                                </tr>
                                                <tr>
                                                    <table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" summary="" class="textonormal" id="tbChecklist" name="tbChecklist" width="100%">
                                                    </table>   
                                                    <input type="hidden" name="dados" id="dados" value="">         
                                                </tr>
                                                <tr>
                                                    <td class="textonormal" align="right">
                                                        <input type="button" name="Incluir" value="Incluir" class="botao" onClick="enviar()">
                                                    </td>
                                                </tr>
                                            </table>

                                        </td>
                                    </tr>

                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!-- Fim do Corpo -->
        </table>
    </form>
<script language="javascript" type="">

<?php 

if($dadosChecklist){
    echo 'CHECKLIST = '.$dadosChecklist;
}else{
    echo 'CHECKLIST = []';
}

?>
 
usuarioCod = <?php echo $_SESSION['_cusupocodi_']?>;                                                       
usuarioDesc = '<?php echo $usuarioDesc ?>';
dataAtual = '<?php echo date('d/m/Y') ?>';       
$('#tbChecklist').hide();

function showErro(str){

    htmlErro = '<div id="divErro" ><table border="0" width="100%">';
        htmlErro += '<tbody>';
        htmlErro += '<tr>';
        htmlErro += '<td bgcolor="DCEDF7" class="titulo1">';
        htmlErro += '<blink><font class="titulo1">Erro!</font></blink>';
        htmlErro += '</td>';
        htmlErro += '</tr>';
        htmlErro += '<tr>';
        htmlErro += '<td class="titulo2">Informe: <b>'+str+'</b>.</td>';
        htmlErro += '</tr>';
        htmlErro += '</tbody>';
        htmlErro += '</table></div>';

        $('#erroTd').html('');
        $('#erroTd').html(htmlErro);
    $('#divErro').show();
}

function hideErro(){
    $('#divErro').html('');
    $('#divErro').hide();
}

function addItemChecklist(){

    strDesc = $('#txtChecklist').val().toUpperCase();

    if(strDesc != "" && strDesc.length > 0){
        objChecklist = {};
        objChecklist.descricao = strDesc;
        objChecklist.ordem = (CHECKLIST.length + 1);
        objChecklist.data = dataAtual; 
        objChecklist.codUsuario = usuarioCod;   
        objChecklist.usuario = usuarioDesc;
        
        CHECKLIST.push(objChecklist);
            
        $('#txtChecklist').val('');

        listarItemChecklist();
    }else{
        showErro("Preencha o campo item de checklist antes de inserir");
    }
}


function removeItemChecklist(){

    
    for (var i = CHECKLIST.length; i >= 0 ; i--) { 

        //console.log( $('#chkItem'+i).checked );

        if($('#chkItem'+i).prop('checked')){

            CHECKLIST.splice(i, 1);
        }

    }

    listarItemChecklist();

}

function gerarSelectOrdem(ordem, id){

    htmlSelectOrdem = '<select id="selOrd'+id+'" style="width:80px;text-align-last:center;">';
    for (var i = 0; i < CHECKLIST.length; i++) { 
        cont = i+1;
        if(ordem == cont ){
            htmlSelectOrdem += '<option selected>'+cont+'</option>';
        }else{
            htmlSelectOrdem += '<option>'+cont+'</option>';
        }
        
    }
    htmlSelectOrdem += '</select>';
    return htmlSelectOrdem;

}


function listarItemChecklist(){

    if(CHECKLIST.length > 0){

        htmlChecklist = '';
        htmlChecklist += '<tr>';
        htmlChecklist += '<td bgcolor="#75ADE6" class="textoabasoff" colspan="5" align="center">CHECKLIST - ITEM</td>';
        htmlChecklist += '</tr>';
        htmlChecklist += '<tr>';
        htmlChecklist += '<td bgcolor="#bfdaf2" align="center"><b>  </b></td>';
        htmlChecklist += '<td bgcolor="#bfdaf2" align="center"><b> DESCRIÇÃO</b></td>';
        htmlChecklist += '<td bgcolor="#bfdaf2" align="center"><b> ORDEM</b></td>';
        htmlChecklist += '<td bgcolor="#bfdaf2" align="center"><b> DATA INCLUSÃO</b></td>';
        htmlChecklist += '<td bgcolor="#bfdaf2" align="center"><b> USUÁRIO RESPONSÁVEL</b></td>';
        htmlChecklist += '</tr>';



        for (var i = 0; i < CHECKLIST.length; i++) { 
            cont = i + 1;
            objChecklist = CHECKLIST[i];

            htmlChecklist +=  '<tr>';
            htmlChecklist += '<td align="center" width="5%" bgcolor="#ffffff">';
            htmlChecklist += '<input type="checkbox" name="chkItem'+i+'" id="chkItem'+i+'" value="" >  ';
            htmlChecklist += '</td>';


            htmlChecklist += '<td class="textonormal" bgcolor="#ffffff">'+objChecklist.descricao+'</td>';
            htmlChecklist += '<td class="textonormal" align="center" bgcolor="#ffffff">'+gerarSelectOrdem(objChecklist.ordem, i)+'</td>';
            htmlChecklist += '<td class="textonormal" align="center" bgcolor="#ffffff">'+objChecklist.data+'</td>';
            htmlChecklist += '<td class="textonormal" align="center" bgcolor="#ffffff">'+objChecklist.usuario+'</td>';
            htmlChecklist += '</tr>';

        }

        htmlChecklist += '<tr>';
        htmlChecklist += '<td bgcolor="#bfdaf2" class="textoabasoff" colspan="5" align="right">';
        htmlChecklist += '<input type="button" name="Retirar" value="Retirar Item" class="botao" onclick="javascript:removeItemChecklist()">';
        htmlChecklist += '</td>';
        htmlChecklist += '</tr>';
        
        $('#tbChecklist').html(htmlChecklist);
        hideErro();
        $('#tbChecklist').show();
    }else{
        $('#tbChecklist').hide();
        hideErro();
    }

}

function enviar(){

    for (var i = 0; i < CHECKLIST.length; i++) { 
        cont = i + 1;

        CHECKLIST[i].ordem = $('#selOrd'+i).val();
    }
    
    arrCheck = JSON.stringify(CHECKLIST);

    $('#dados').val(arrCheck);
    document.Acao.submit();

}

listarItemChecklist();
//document.Acao.Acao.focus();
</script>    
</body>
</html>