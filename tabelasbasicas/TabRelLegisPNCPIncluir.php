<?php

/*
Arquivo: TabRelLegisPNCPIncluir.php
Nome: Lucas Vicente
Data: 01/03/2023
Tarefa: 279688 

*/

// Acesso ao arquivo de funções #
include "../funcoes.php";

// Executa o controle de segurança #
session_start();
Seguranca();

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Incluir 				= $_POST['Incluir'];
    $Lei 	                = $_POST['Lei'];
    $Artigo 	            = $_POST['Artigo'];
    $Inciso 	            = $_POST['Inciso'];
    $CodigoPNCP 	        = $_POST['CodigoPNCP'];
}

if ($Incluir == 'Incluir') {
    // Critica dos Campos #
    $Mens = 0;
    $Mensagem = "Informe: ";
    if ($Lei == '') {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<class=\"titulo2\">Lei, ";
    } 
    if ($Artigo == '') {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<class=\"titulo2\">Artigo, ";
    } 
    if ($Inciso == '') {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<class=\"titulo2\">Inciso, ";
    } 
    if ($CodigoPNCP == '') {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<class=\"titulo2\">Codigo";
    } 
    
    if ($Mens == 0) {
		$CodigoUsuario = $_SESSION['_cusupocodi_'];
        // Verifica a Duplicidade de Natureza Jurídica #
		$db = Conexao();

        $sqlValidaCod = "SELECT clcpnccodi FROM SFPC.tblegislacaocompraspncp WHERE clcpnccodi = $CodigoPNCP";
        $resultCod = $db->query($sqlValidaCod);
        $LinhaCod = $resultCod->fetchRow();

        if(!empty($LinhaCod[0])){
            $Mens = 1;
            $Tipo = 2;
            $Mensagem = "<class=\"titulo2\">O código informado já esta sendo utilizado</a>";
        }else{

            $sql = "SELECT COUNT(clcpnccodi) FROM SFPC.tblegislacaocompraspncp WHERE cleiponume = $Lei 
                    AND cartpoarti = $Artigo AND cincpainci = $Inciso";
            $result = $db->query($sql);
            
            if (db::isError($result)) {
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
            
            }else{
                $Linha = $result->fetchRow();
                $Qtd = $Linha[0];
                if ($Qtd > 0) {
                    $Mens = 1;
                    $Tipo = 2;
                    $Mensagem = "<class=\"titulo2\">Os parametros informados já estão sendo utilizados</a>";
                }else{    
                    // Insere os dados informados #
                    $Data = date("Y-m-d H:i:s");
                    $db->query("BEGIN TRANSACTION");
                    $sql = "INSERT INTO SFPC.tblegislacaocompraspncp ( ";
                    $sql .= "clcpnccodi,ctpleitipo,cleiponume,cartpoarti,cincpainci,cusupocodi,tlcpnculat ";
                    $sql .= ") VALUES ( ";
                    $sql .= "'$CodigoPNCP', '1', '$Lei', '$Artigo', '$Inciso', '$CodigoUsuario', '$Data')";
                    $result = $db->query($sql);
                    if (db::isError($result)) {
                        $db->query("ROLLBACK");
                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                    } else {
                        $db->query("COMMIT");
                        $db->query("END TRANSACTION");
                        
                        $Mens = 1;
                        $Tipo = 1;
                        $Mensagem = "Inclusão realizada com sucesso!";
                        
                        // Limpando Variáveis #
                        $CodigoPNCP = "";
                        $Lei = "";
                        $Artigo = "";
                        $Inciso = "";
                        
                    }
                }
            }
        }
    }
}
        
        
    

?>
<html>
<?php
// Carrega o layout padrão
layout();
?>
<script
    language="javascript"
    type=""
>

<?php MenuAcesso(); ?>

</script>
<link
    rel="stylesheet"
    type="text/css"
    href="../estilo.css"
>
<body
    background="../midia/bg.gif"
    marginwidth="0"
    marginheight="0"
>
    <script
        language="JavaScript"
        src="../menu.js"
    ></script>
    <script language="JavaScript">Init();</script>
    <form
        action="TabRelLegisPNCPIncluir.php"
        method="post"
        name="TabRelLegisPNCPIncluir"
    >
        <br> <br> <br> <br> <br>
        <table
            cellpadding="3"
            border="0"
        >
            <!-- Caminho -->
            <tr>
                <td width="100"><img
                    border="0"
                    src="../midia/linha.gif"
                    alt=""
                ></td>
                <td
                    align="left"
                    class="textonormal"
                ><font class="titulo2">|</font> <a href="../index.php"><font
                        color="#000000"
                    >Página Principal</font></a> > Tabelas > Planejamento > PNCP > Incluir</td>
            </tr>
            <!-- Fim do Caminho-->
            <!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
                <td width="100"></td>
                <td
                    align="left"
                    colspan="2"
                ><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
            </tr>
	<?php } ?>
	<!-- Fim do Erro -->
            <!-- Corpo -->
            <tr>
                <td width="100"></td>
                <td class="textonormal">
                    <table
                        border="0"
                        cellspacing="0"
                        cellpadding="3"
                    >
                        <tr>
                            <td class="textonormal">
                                <table
                                    border="1"
                                    cellpadding="3"
                                    cellspacing="0"
                                    bordercolor="#75ADE6"
                                    summary=""
                                    class="textonormal"
                                    bgcolor="#FFFFFF"
                                >
                                    <tr>
                                        <td
                                            align="center"
                                            bgcolor="#75ADE6"
                                            valign="middle"
                                            class="titulo3"
                                        >INCLUIR - PNCP</td>
                                    </tr>
                                    <tr>
                                        <td class="textonormal">
                                            <p align="justify">Para incluir um
                                                novo código para Legislação de Compras, preencha os campo abaixo e clique em "Incluir".</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <table
                                                class="textonormal"
                                                border="0"
                                                align="left"
                                                class="caixa"
                                            >
                                                <tr>
                                                    <td>
                                                    Lei:
                                                    <td class="textonormal">
                                                        <?php
                                                        $db = Conexao();

                                                        $sqlLei = 'SELECT CLEIPONUME FROM SFPC.TBLEIPORTAL';
                                                       
                                                        $resLei = executarTransacao($db, $sqlLei);
                                                        ?>
                                                        <select class="textonormal" name="Lei" id="Lei" onChange="atualizar()">
                                                        <option value="">Selecione uma Lei...</option>
                                                        <?php 
                                                        
                                                        
                                                        while ($LinhaLei = $resLei->fetchRow()) {
                                                            $leiItem = $LinhaLei[0];
                                                        ?>
                                                        <option value="<?php echo  $leiItem ?>" <?php if ($leiItem == $Lei) { echo 'selected'; } ?>><?php echo  $leiItem ?></option>
                                                        <?php       
                                                        }   
                                                        
                                                        ?>
                                                        </select>
                                                    Artigo: 
                                                    <select class="textonormal" name="Artigo" id="Artigo" onChange="atualizar()">
                                                    <option value="">Selecione um Artigo...</option>  
                                                        <?php
                                                            if(!is_null($Lei) and $Lei!=''){
                                                                $db = Conexao();
                                                                $sqlArtigo = 'SELECT CARTPOARTI FROM SFPC.TBARTIGOPORTAL WHERE CLEIPONUME = ' . $Lei . ' ';
                                                                
                                                                $resArtigo = executarTransacao($db, $sqlArtigo);
                                                                ?>
                                                                
                                                                <?php   
                                                    
                                                                while ($LinhaArtigo = $resArtigo->fetchRow()) {
                                                                    $ArtigoItem = $LinhaArtigo[0];
                                                                ?>
                                                                <option value="<?php echo  $ArtigoItem ?>" <?php if ($ArtigoItem == $Artigo) { echo 'selected'; } ?>><?php echo  $ArtigoItem ?></option>
                                                                <?php       
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    Inciso/Parágrafo:
                                                    <select class="textonormal" name="Inciso" id="Inciso" onChange="atualizar()">
                                                    <option value="">Selecione um Inciso/Parágrafo...</option>
                                                        <?php
                                                            if(!is_null($Lei) and $Lei!='' and !is_null($Artigo) and $Artigo!=''){
                                                                $db = Conexao();
                                                                $sqlInciso = 'SELECT CINCPAINCI, NINCPANUME FROM SFPC.TBINCISOPARAGRAFOPORTAL WHERE CLEIPONUME = '.$Lei.' AND CARTPOARTI = '.$Artigo.'';
                                                                
                                                                $resInciso = executarTransacao($db, $sqlInciso);
                                                                ?>
                                                                
                                                                <?php   
                                                    
                                                                while ($LinhaInciso = $resInciso->fetchRow()) {
                                                                    $IncisoItem     = $LinhaInciso[0];
                                                                    $IncisoNumero   = $LinhaInciso[1];
                                                                ?>
                                                                <option value="<?php echo  $IncisoItem ?>" <?php if ($IncisoItem == $Inciso) { echo 'selected'; } ?>><?php echo  $IncisoNumero ?></option>
                                                                <?php       
                                                                }
                                                            }
                                                            ?>
                                                    </select>
                                                    </td>
                                                    <tr>
                                                    <td>
                                                    Código:  
                                                    </td>
                                                    <td class="textonormal">
                                                        <input type="text" class="textonormal" name="CodigoPNCP" id="CodigoPNCP" value="<?php echo empty($CodigoPNCP)?$_POST['CodigoPCNP']:$CodigoPNCP; ?>">
                                                    </td>
                                                    </tr>
                                                </tr>
                                                
                                            </table>
                                        </td>
                                    </tr>
                                    <script>
                                    function atualizar(){
                                        $('form').submit();
                                    }                     
                                    </script>
                                    <tr>
                                        <td 
                                            class="textonormal" align="right"><input type="submit" name="Incluir" value="Incluir" class="botao">
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
</body>
</html>

