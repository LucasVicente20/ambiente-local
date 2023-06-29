<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotGeracaoSenha.php
# Objetivo: Programa de Geração de Senha
# Data:     09/04/03
# Autor:    Rossana Lira
# Alterado: Carlos Abreu
# Data:     20/03/2007 - Alteração para não aparecer usuario admin na lista de usuários
#                        para liberar programa a equipe de suporte
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     04/07/2018
# Objetivo: Tarefa Redmine 95887
#-------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     23/08/2018
# Objetivo: Tarefa Redmine 202067
#-------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Critica       = $_POST['Critica'];
    $UsuarioCodigo = $_POST['UsuarioCodigo'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "RotGeracaoSenha.php";

# Critica dos Campos #
if ($Critica == 1) {
    $Mens     = 0;
    $Mensagem = "Informe: ";
    
    if ($UsuarioCodigo == "") {
        $Mens = 1;
        $Tipo = 2;
        $Troca = 1;
        $Mensagem .= "<a href=\"javascript: document.Geracao.UsuarioCodigo.focus();\" class=\"titulo2\">Usuário</a>";
    } else {
        # Busca E-mail do Usuário #
        $db     = Conexao();
        
        $sql    = "SELECT EUSUPOLOGI, EUSUPOMAIL FROM SFPC.TBUSUARIOPORTAL WHERE CUSUPOCODI = $UsuarioCodigo";
        
        $result = $db->query($sql);

        if (PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
        } else {
            while ($Linha = $result->fetchRow()) {
                $Login = $Linha[0];
                $Email = $Linha[1];
            }

            # Cria na nova senha e criptografa #
            $Senha      = CriaSenha();
            $SenhaCript = hash('sha512', $Senha);

            # Atualiza a senha do Usuário #
            $Data   = date("Y-m-d H:i:s");
            $sql    = "UPDATE SFPC.TBUSUARIOPORTAL SET EUSUPOSEN2 = '$SenhaCript', ";
            $sql   .= "TUSUPOULAT = '$Data' WHERE CUSUPOCODI = $UsuarioCodigo";
            $result = $db->query($sql);
            if (PEAR::isError($result)) {
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
            } else {
                # Verifica se houve erro ao enviar, caso contrário envia a senha para o e-mail do usuário #
                $envioSmtp = EnviaEmail("$Email","Senha temporária para acesso ao Portal de Compras","\t Login: $Login\n\t Senha: $Senha","from: portalcompras@recife.pe.gov.br");
                $Mens = 1;$Tipo = 1;
                if (!$envioSmtp) {
                    var_dump($envioSmtp);die;
                    $Mensagem = "Envio de email falhou! O servidor de email pode estar apresentando problemas no momento. Tenta mais tarde ou contacte o administrador do sistema";
                } else {
                    $Mensagem ="Senha gerada com sucesso. Uma senha temporária foi enviada para o e-mail do usuário";
                }
            }
            $db->disconnect();
        }
    }
}

?>
<html>
    <?php
        # Carrega o layout padrão #
        layout();
    ?>
    <script language="javascript" type="">
        <!--
            <?php MenuAcesso(); ?>
        //-->
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css">
    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
        <script language="JavaScript" src="../menu.js"></script>
        <script language="JavaScript">Init();</script>
        <form action="RotGeracaoSenha.php" method="post" name="Geracao">
            <br><br><br><br><br>
            <table cellpadding="3" border="0" summary="">
                <!-- Caminho -->
                <tr>
                    <td width="150">
                        <img border="0" src="../midia/linha.gif" alt="">
                    </td>
                    <td align="left" class="textonormal" colspan="2">
                        <font class="titulo2">|</font><a href="../index.php"><font color="#000000">Página Principal</font></a> > Controles > Geração de Senha
                    </td>
                </tr>
                <!-- Fim do Caminho-->
                <!-- Erro -->
                <?php if ($Mens == 1) {?>
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
                                <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
                                    GERAÇÃO DE SENHA DO USUÁRIO
                                </td>
                            </tr>
                            <tr>
                                <td class="textonormal" bgcolor="#FFFFFF">
                                    <p align="justify">
                                        Para modificar a senha do usuário, selecione o usuário e clique no botão "Gerar Senha".
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <table border="0" summary="">
                                        <tr>
                                            <td class="textonormal" bgcolor="#DCEDF7">Usuário </td>
                                            <td class="textonormal">
                                                <select name="UsuarioCodigo" class="textonormal">
                                                    <option value="">Selecione um Usuário...</option>
                                                    <!-- Mostra os usuários cadastrados -->
                                                    <?php   $db   = Conexao();
                                                            
                                                            $sql  = "SELECT A.CUSUPOCODI, A.EUSUPORESP, B.CGREMPCODI, B.EGREMPDESC ";
                                                            $sql .= "  FROM SFPC.TBUSUARIOPORTAL A, SFPC.TBGRUPOEMPRESA B ";
                                                            if ($_SESSION['_cgrempcodi_'] == 0) {
                                                                $sql .= "WHERE A.CGREMPCODI = B.CGREMPCODI  ";
                                                            } else {
                                                                $sql .= "WHERE A.CGREMPCODI = B.CGREMPCODI AND B.CGREMPCODI <> 0 ";
                                                            }
                                                            $sql .= " AND (A.CGREMPCODI, A.CUSUPOCODI) <> (0,2) "; // RETIRA USUARIO ADMIN
                                                            $sql .= " ORDER BY A.EUSUPORESP ASC";
                                                            $result = $db->query($sql);
                                                            
                                                            if (PEAR::isError($result)) {
                                                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                            } else {
                                                                while ($Linha = $result->fetchRow()) {
                                                                    $DescGrupo = substr($Linha[3],0,40);
                                                                    $UsuarioCodigo = $Linha[0];
                                                                    echo"<option value=\"$Linha[0]\">$Linha[1] - $Linha[3]</option>\n";
                                                                }
                                                            }
                                                            $db->disconnect();
                                                    ?>
                                                </select>
                                                <input type="hidden" name="Critica" value="1">
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td class="textonormal" align="right">
                                    <input type="submit" value="Gerar Senha" class="botao">
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