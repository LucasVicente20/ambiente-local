<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotEsqeuciSenha.php
# Objetivo: Programa de Recupera Senha
# Data:     22/07/13
# Autor:    Everton Lino
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
    $Critica       = $_POST['Critica'];
    $CPF           = $_POST['CPF'];
    $Botao         = $_POST['Botao'];
}

//Desvio do FluxoCriaSenha
if( $Botao == "retornar" ){
    header("location: index.php");
    exit;
}


# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RotEsqueciSenha.php";

# Critica dos Campos #
if( $Critica == 1 ){
    //	$Mens     = 0;
    //	$Mensagem = "Informe: ";
    if( $CPF == "" || !valida_CPFNovo($CPF) ) {
        //    $Mens = 1; $Tipo = 2; $Troca = 1;
        //  $Mensagem .= "<a href=\"javascript: document.Geracao.CPF.focus();\" class=\"titulo2\">CPF</a>";
        adicionarMensagem("<a href=\"javascript:document.Geracao.CPF.focus();\" class=\"titulo2\">CPF válido</a>", TIPO_MENSAGEM_ERRO);

        // }
        //  if( !valida_CPF($CPF) ){
        //	$Mens = 1; $Tipo = 2; $Troca = 1;
        //	$Mensagem .= "<a href=\"javascript: document.Geracao.CPF.focus();\" class=\"titulo2\">CPF válido</a>";
    }else{
        //if( !valida_CPF($CPF)){
        //	adicionarMensagem("<a href=\"javascript:document.Geracao.CPF.focus();\" class=\"titulo2\">CPF Válido</a>", TIPO_MENSAGEM_ERRO);
        //	}else{

        # Busca E-mail do Usuário e Valida CPF #
        $db     = Conexao();
        $sql    = "SELECT EUSUPOLOGI, EUSUPOMAIL FROM SFPC.TBUSUARIOPORTAL WHERE AUSUPOCCPF = '$CPF' ";
        $result = $db->query($sql);
        $num_rows = $result->numRows();

        if( PEAR::isError($result) ){
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
        }
        // Se número de linhas vier vazio é porque CPF informado não existe
        if (empty($num_rows)) {
            $Mensagem = "CPF não encontrado";
            $Mens = 1;$Tipo = 1;
        }

        else{
            while( $Linha = $result->fetchRow() ){
                $Login  = $Linha[0];
                $Email  = $Linha[1];
            }

            # Cria na nova senha e criptografa #
            $Senha      = CriaSenha();
            $SenhaCript = crypt ($Senha,"P");

            # Atualiza a senha do Usuário #
            $Data   = date("Y-m-d H:i:s");
            $sql    = "UPDATE SFPC.TBUSUARIOPORTAL SET EUSUPOSEN2 = '$SenhaCript', ";
            $sql   .= "TUSUPOULAT = '$Data' WHERE AUSUPOCCPF = '$CPF' ";
            $result = $db->query($sql);
            if( PEAR::isError($result) ){
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
            }else{
                # Envia a senha pelo e-mail do usuário #
                $envioSmtp = EnviaEmail("$Email","Senha temporária para acesso ao Portal de Compras","\t Login: $Login\n\t Senha: $Senha","from: portalcompras@recife.pe.gov.br");
                $Mens = 1;$Tipo = 1;
                if (!$envioSmtp) {
                    $Mensagem = "
                        Envio de email falhou! O servidor de email pode estar apresentando problemas no momento. Tenta mais tarde
                        ou contacte o administrador do sistema";
                }
                else {
                    $Mensagem ="Senha gerada com sucesso. Uma senha temporária foi enviada para o e-mail do usuário";
                }
            }
            $db->disconnect();
        }
    }
}
?>

<html>
<head>
    <title>Portal de Compras - Prefeitura do Recife</title>

    <script language="JavaScript">

        function enviar(valor){

            document.Geracao.Botao.value=valor;
            document.Geracao.submit();
        }


    </script>
</head>

<body>

<form action="RotEsqueciSenha.php" method="post" name="Geracao">



    <img src="midia/portalCompra.jpg"  width="100%"   border="0" alt="">




    <table cellpadding="3" border="0" summary="">
        <!-- Caminho -->
        <!-- Fim do Caminho-->
        <tr>
            <td width="150"></td>
            <td COLSPAN=2>RECUPERAÇÃO DE SENHA DO PORTAL DE COMPRAS</td>
        </tr>

        <!-- Erro -->
        <?php if ( $Mens == 1 ) {?>

            <tr>
                <td width="150"></td>
                <td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
            </tr>
        <?php } ?>
        <!-- Fim do Erro -->

        <!-- Corpo -->
        <tr>
            <td width="150"></td>
            <td class="textonormal"><br>
                <table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
                    <tr>
                        <td>
                            <table border="0" summary="">
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7" width="30%">CPF* </td>
                                    <td class="textonormal">
                                        <input type="text" name="CPF" value="<?php  echo $CPF; ?>" size="11" maxlength="11" class="textonormal"></td>
                                </tr>
                                <tr>
                                    <td class="textonormal">
                                        <input type="hidden" name="Critica" value="1">
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="textonormal" align="right">
                            <input type="submit" value="Gerar Senha" class="botao">
                            <input type="button" value="Retornar" class="botao" onClick="javascript:enviar('retornar');"  >

                            <input type="hidden" name="Botao" value="">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <!-- Fim do Corpo -->
    </table>
</form>
<script language="javascript" type="">
    <!--
    document.Geracao.UsuarioCodigo.focus();
    //-->
</script>
</body>
</html>
