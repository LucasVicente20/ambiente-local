<!-- Conteudo do Modal -->
<?php
require_once "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();
if($_POST['Botao']=='Incluir'){
    $sucesso = false;
    function maxValueIndice(){
        $sql = "select max(cinrprcodi) from sfpc.tbindicereajusteprecos ";
        // print_r($sql);die;
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
    $data = DataInvertida($_POST['data']);
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
        var incluir = window.confirm('Voce deseja incluir');
        if(incluir){
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
<div class="modal-title textonormal" >
    <?php if($sucesso){?>
        <span class="textonormal success">Indexação Incluida com Sucesso!</span>
    <?php }?>
<div class="modal-title textonormal" >
INCLUIR - INDEXAÇÃO DE PREÇOS
</div>
<div class="modal-body">
    <form action="" method="post" id="JanelaIncluirPreco" name="JanelaIncluirPreco">
    <table class="textonormal" width="70%">
        <tr>
            <td align="left" colspan="2" id="tdmensagemM">
                <div class="mensagemM">
                    <span id="mensagemErroModal" class="mensagem-textoM">
                    </span>
                </div>
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
        <table width="100%" bordercolor="#75ADE6" border="1" cellspacing="0">
            <tbody>
                <tr>
                    <td colspan="3">
                        <div>
                            <input class="btn-fecha-modal botao"  name="botao_voltar" value="Incluir" onclick="enviar('Incluir')" type="button" style="float:right">
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </tr>
    <tr>
        <td colspan="3" style="border:none">
<!-- loading -->
            <div class="load" id="LoadPesqScc" style="display:none">
                <div class="load-content" >
                <img src="../midia/loading.gif" alt="Carregando">
                    <spam>Carregando...</spam>
            </div>
        </div>
<!-- Fim do loading -->
        <div id="pesqDivModal">
        </div>
        </td>
    </tr>
        <input type="hidden" name="Botao" value="" />
        </table>
    </form>
    </div>
</div>     
