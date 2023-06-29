<!-- Conteudo do Modal -->
<?php
require_once "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

if($_POST['Incluir']=='Incluir'){
    $Mens = '';
    $Tipo = '';
    $Mensagem = "Informe: ";
    if(empty($_POST['Valor'])){
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<class=\"titulo2\">Índice de Reajuste, </a>";
    }
    if(empty($_POST['DataPreco'])){
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<class=\"titulo2\">Data do Reajuste</a>";
    }

    $sucesso = false;
    function maxValueIndice(){
        $sql = "select max(cinrprcodi) from sfpc.tbindicereajusteprecos ";

        $resultado = executarPGSQL($sql);
        $linha  = $resultado->fetchRow();
        return  $linha[0] + 1;
    }
    
    function validaDados($maxValue, $data, $valor){
       if($maxValue and $data and $valor){
            return true;
       }else{
           return false;
       }
    }

    function incluirPreco($maxValue, $data, $valorConvertido){
        $codigoUsuario = $_SESSION['_cusupocodi_'];
        $sql = "insert into sfpc.tbindicereajusteprecos(cinrprcodi, cusupocodi, tinrprulat, vinrprrepr) ";
       
        $sql .="values($maxValue, $codigoUsuario, '$data', $valorConvertido)";
        $resultado = executarPGSQL($sql);
    }


    $maxValue = maxValueIndice();
    $data = DataInvertida($_POST['DataPreco']);
    $data = str_replace('-','',$data);
    $valor = explode(",", $_POST['Valor']);
    $valorConvertido = str_replace(".", "", $valor[0]);
    $valida = validaDados($maxValue, $data, $valorConvertido);
    
    if($valida){
        $inclusao = incluirPreco($maxValue, $data, $valorConvertido);
        $sucesso = true;
        echo "<script>
        opener.document.CadIndexacaoPrecos.submit()
        window.close();
        </script>";
    }


    
}
?>
<script>
    function enviar(valor) {
        var incluir = window.confirm('Voce deseja incluir?');
        if(incluir==true){
            document.janelaIncluirPreco.Botao.value = valor;
            document.janelaIncluirPreco.submit();
        }

    } 

</script>
<script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>​
<script language="javascript" src="../funcoes.js" type="text/javascript"></script>
<div class="modal-content">
<div class="modal-title">
    <span align="center"></span>
</div>
<div>
    <td class="error" bgcolor="#75ADE6" >
        <?php if ($Mens == 1){?>
            <span class="mensagem-texto"><?php ExibeMens($Mensagem,$Tipo,1) ?></span>
        <?php }?>
    </td>

</div>

<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">

<div class="modal-body">
    <form action="" method="post" id="JanelaIncluirPreco" name="JanelaIncluirPreco">
    
        <tr>
            <td align="center" bgcolor="#75ADE6" valign="middle" colspan="4" class="titulo3">
                <font color="black"><b>INCLUIR - INDEXAÇÃO DE PREÇOS.</b></font>
            </td>
        </tr>
    <tr>
        <td class="textonormal" bgcolor="#DCEDF7" width="31%">Índice de Reajuste
        </td>
        <td class="textonormal" colspan="5">
            <input type="text" class="dinheiro4casas" id="Valor" name="Valor" value="" size="15" maxlength="30" class="textonormal">
        </td>

    </tr>
    <tr>
        <td class="textonormal" bgcolor="#DCEDF7">Data do Reajuste
        </td>
         <td class="textonormal" colspan="5">
            <input type="text" id="DataPreco" name="DataPreco" value="" size="10" maxlength="10" class="textonormal" >
            <a id="calendarioVig" style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=JanelaIncluirPreco&Campo=DataPreco','Calendario',220,170,1,0)">
            <img src="../midia/calendario.gif" border="0" alt="">
            </a>
        </td>
    </tr>
    <tr>    
        <td colspan="3">
            <div>
                <input type="submit" class="botao" name="Incluir" value="Incluir" onclick="enviar('Incluir')" style="float:right">
            </div>
        </td>
    </tr>
        </table>
    </form>
    </div>
</div>     
