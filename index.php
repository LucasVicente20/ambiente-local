<?php
/**
 * Portal de Compras
 * 
 * Programa:   index.php
 * Autor:      Luciano Mauro
 * Data:       01/04/2003
 * Objetivo:   Identificação do usuário
 * Observação: Rerirar o for que mostra os caminhos
 * -------------------------------------------------------------------------------------------------------
 * Alterado: Álvaro Faria
 * Data:     26/06/2006
 * -------------------------------------------------------------------------------------------------------
 * Alterado: Rossana Lira
 * Data:     13/03/2007
 * Objetivo: Mudança do link da internet pela operadora (novo link: 189.17.106.5)
 * -------------------------------------------------------------------------------------------------------
 * Alterado: Everton Lino
 * Data:     20/05/2010
 * Objetivo: Inclusão de CPF de usuário com CPF não cadastrado
 * -------------------------------------------------------------------------------------------------------
 * Alterado: Ariston Cordeiro
 * Data:     30/05/2011
 * Objetivo: Mudança de regras para detecção de intranet, devido a mudança de proxy
 * -------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     06/01/2015
 * Objetivo: Adiciona regra de redirecionamento para o novo layout
 * -------------------------------------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     18/06/2015
 * Objetivo: CR 82766
 * Versão:   v1.20.0-21-g0bb0451
 * -------------------------------------------------------------------------------------------------------
 * Alterado: Lucas Baracho
 * Data:     10/05/2019
 * Objetivo: Tarefa Redmine 216521
 * -------------------------------------------------------------------------------------------------------
 */

require_once "vendor/autoload.php";

session_start();
VerificarAmbiante();
error_reporting(0);
AddMenuAcesso('RotEsqueciSenha.php');

# Variáveis globais #
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $Fechar = $_GET['Fechar'];
    $ref    = $_GET['ref'];
} else {
    $Critica                  = $_POST['Critica'];
    $Login                    = $_POST['Login'];
    $Senha                    = $_POST['Senha'];
    $Confirmacao              = strtoupper2($_POST['Confirmacao']);
    $ref                      = $_POST['ref'];
    $Botao                    = $_POST['Botao'];
    $_SESSION['_eacepocami_'] = array();
}

$PrecisaCadastrarCPF = false;

# Desvio do fluxo #
if ($Botao == "esquecisenha") {
    header("location: RotEsqueciSenha.php");
    exit();
}

if ($ref == "transparencia") {
    $_SESSION['_ref_'] = "transparencia";
} else {
    $_SESSION['_ref_'] = "";
}

# Verificando a sessão #
if ($Fechar == 1) {
    echo"destroy Fechar";
    session_destroy();
    session_start();
    TiraSeguranca();
} else {
    $Mens = 0;
    
    if ($Critica == 1) {
        $db = Conexao();
        
        if (! $db) {
            $Mens     = 1;
            $Tipo     = 2;
            $Mod      = 1;
            $Mensagem = "Banco de Dados n&atilde;o Disponível no Momento.<br>Tente Novamente Mais Tarde.<br>Obrigado!";
        } else {
            $sql = "SELECT CGREMPCODI, CUSUPOCODI, EUSUPOSENH FROM SFPC.TBUSUARIOPORTAL WHERE EUSUPOLOGI = '$Login'";
            
            $result = $db->query($sql);
            
            if (PEAR::isError($result)) {
                $CodErroEmail = $result->getCode();
                $DescErroEmail = $result->getMessage();
                ExibeErroBD("index.php\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
            }

            $Rows = $result->numRows();
            $Linha = array();
            
            while ($cols = $result->fetchRow()) {
                $Linha[$Rows] = "$cols[0]_$cols[1]_$cols[2]";
            }
            
            $Linha = explode("_", $Linha[$Rows]);
            $eusuarsenh = $Linha[2];
            $SenhaCript = crypt($Senha, "P");

            # Critica aos campos de login e código #
            if ((strtoupper2($Confirmacao) != $_SESSION['_Combinacao_']) || ($Confirmacao == "")) {
                TiraSeguranca();
                
                if ($Mens == 1) {
                    $Mensagem .= ", ";
                }
                
                $Mens      = 1;
                $Tipo      = 2;
                $Mod       = 1;
                $Mensagem .= "<a href=\"javascript:document.Login.Confirmacao.focus();\" class=\"titulo2\">Código Inválido</a>";
            } else {
                if ($Rows == 0 || $Login == "") {
                    TiraSeguranca();
                    $Mens      = 1;
                    $Tipo      = 2;
                    $Mod       = 1;
                    $Mensagem .= "<a href=\"javascript:document.Login.Login.focus();\" class=\"titulo2\">Login ou Senha Inv&aacute;lidos</a>";
                } else {
                    $Mensagem = "Informe: ";
                    
                    if ($SenhaCript != $eusuarsenh || $Senha == "") {
                        TiraSeguranca();
                        if ($Mens == 1) {
                            $Mensagem .= ", ";
                        }
                        
                        $Mens      = 1;
                        $Tipo      = 2;
                        $Mod       = 1;
                        $Mensagem .= "<a href=\"javascript:document.Login.Senha.focus();\" class=\"titulo2\">Senha V&aacute;lida</a>";
                    }
                }
            }

            # Login e campos válidos #
            if ($Mens == 0) {
                $_SESSION['_cgrempcodi_'] = $Linha[0];
                $_SESSION['_cusupocodi_'] = $Linha[1];
                $_SESSION['_eusupologi_'] = $Login;
                $_SESSION['_eacepocami_'] = array();

                # Pesquisa perfil do usuário #
                $sql  = "SELECT A.CPERFICODI, B.FPERFICORP ";
                $sql .= "FROM   SFPC.TBUSUARIOPERFIL A, SFPC.TBPERFIL B ";
                $sql .= "WHERE  A.CGREMPCODI = ".$_SESSION['_cgrempcodi_'];
                $sql .= "       AND A.CUSUPOCODI = ".$_SESSION['_cusupocodi_'];
                $sql .= "       AND A.CPERFICODI = B.CPERFICODI";
                $sql .= "       AND B.FPERFISITU <> 'I'";

                $result = $db->query($sql);
                
                if (PEAR::isError($result)) {
                    $CodErroEmail = $result->getCode();
                    $DescErroEmail = $result->getMessage();
                    ExibeErroBD("index.php\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                }
                
                $Rows = $result->numRows();
                
                if ($Rows == 1) {
                    $Linha = $result->fetchRow();
                    $_SESSION['_cperficodi_'] = $Linha[0];
                    $_SESSION['_fperficorp_'] = $Linha[1];

                    # Usuários sem CPF
                    $db = Conexao();
                    
                    $sql = "SELECT COUNT(CUSUPOCODI) FROM SFPC.TBUSUARIOPORTAL WHERE AUSUPOCCPF IS NULL AND CUSUPOCODI = ".$_SESSION['_cusupocodi_']."";
                    
                    $result = $db->query($sql);
                    
                    if (PEAR::isError($result)) {
                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                    } else {
                        $Linha = $result->fetchRow();
                        $Qtd   = $Linha[0];
                        
                        if ($Qtd > 0) {
                            $PrecisaCadastrarCPF = true;
                            AddMenuAcesso('/tabelasbasicas/TabUsuarioCPF.php');
                            $redirecionar = "tabelasbasicas/TabUsuarioCPF.php";
                            header("Location: $redirecionar");
                            exit();
                        }
                    }
                } else {
                    $Mens     = 1;
                    $Tipo     = 2;
                    $Mod      = 1;
                    $Mensagem = "Usuário sem perfil definido";
                    
                    TiraSeguranca();
                }
            }

            $db->disconnect();
        }
    } else {
        if (! isset($_SESSION['_cgrempcodi_'])) {
            TiraSeguranca();
        }
    }
}
?>

<?php

/**
 * CUSTOMIZACAO
 *
 * @author Pitang Agile TI (12/01/2015)
 *
 */

if ($_SESSION['_eusupologi_'] == "INTERNET") {
    AddMenuAcesso('app/home.php');
    header('Location: app/home.php');
    exit();
}
?>
<html>
<head>
    <title>Portal de Compras - Prefeitura do Recife</title>
    <script language="JavaScript">
        function abrir(URL) {
            var width  = 200;
            var height = 250;
            var left   = 99;
            var top    = 99;
            
            window.open(URL,'janela', 'width='+width+', height='+height+', top='+top+', left='+left+', scrollbars=yes, status=no, toolbar=no, location=no, directories=no, menubar=no, resizable=no, fullscreen=no');
       }

       function enviar(valor) {
    		document.Login.Botao.value=valor;
    		document.Login.submit();
       }
    </script>
    <script language="javascript" type="">
        <!--
        <?php MenuAcesso(); ?>
        //-->
    </script>
    <style type="text/css">
        #Titulo {
	        position: absolute;
	        z-index: 1;
	        visibility: visible;
	        left: -1;
	        top: 15;
        }

        #BgMenu {
	        position: absolute;
	        z-index: 0;
	        visibility: visible;
	        left: -12;
	        top: -4;
        }
    </style>
    <link rel="Stylesheet" type="Text/Css" href="estilo.css">
</head>
<body>
	<div id="Titulo" style='width: 100%'>
        <img src="midia/portalCompra.jpg" border="0" alt="">
        <?php
        if ($_SESSION['_ref_'] == "transparencia") {
            ?>
            <div style="position: absolute; top: 2px; left: 680px;">
			    <span class="textonormalAmarelo">Você veio de:</span>
		    </div>
		    <div style="position: absolute; top: 17px; left: 1000px;">
                <a href="<?php echo URL_TRANSPARENCIA;?>">
                    <img src="midia/portalTransparencia1.png" border="0" width="130px" alt="" style='margin-left: -400px;'>
			    </a>
		    </div>
            <?php
        }
        ?>
        <?php
        if ($_SESSION['_ref_'] != "transparencia") {
            $strLocalSistema = str_replace('_', ' ', strtoupper2($GLOBALS["LOCAL_SISTEMA"]));
            
            if (strpos($GLOBALS["LOCAL_SISTEMA"], CONST_NOMELOCAL_DESENVOLVIMENTO) !== false) {
                echo "
   				 	<div style='position:absolute;right:0px;top:0px;text-align:middle;background-color:#fff;height:65px;'>
	   					<img src='midia/desenvolver.JPG' border='0' style ='vertical-align:middle;' />\n
	   			        <span style='font-weight: bold;font-size: 20px;'>".$strLocalSistema."</span>
   				 	</div>
                ";
            } elseif ($GLOBALS["LOCAL_SISTEMA"] === CONST_NOMELOCAL_HOMOLOGACAO) {
                echo "
   				 	<div style='position:absolute;right:0px;top:0px;text-align:middle;background-color:#fff;height:65px;'>
	   					<img src='midia/homologa.JPG' border='0' style ='vertical-align:middle;' />\n
	   			        <span style='font-weight: bold;font-size: 20px;'>".$strLocalSistema."</span>
   				 	</div>
                ";
            }
        }
        ?>
    </div>
	<div id="BgMenu">
		<img src="midia/bg_menu.gif" border="0" alt="">
	</div>
    <body background="midia/bg.gif" marginwidth="0" marginheight="0">
	    <script language="JavaScript" src="menu.js"></script>
	    <script language="JavaScript">Init();</script>
	    <form action="index.php" method="post" name="Login">
    		<br> <br> <br> <br>
		    <table cellpadding="3" border="0" summary="">
			    <!-- Caminho -->
			    <tr>
				    <td width="150"><img border="0" src="midia/linha.gif" alt=""></td>
				    <td>
					    <table border="0" summary="">
						    <tr>
							    <td align="left" class="textonormal"><br> <font class="titulo2">|</font>
                                    <a href="index.php"><font color="#000000">Página Principal</font></a>
                                </td>
					            <?php
                                if (strtoupper2($_SESSION['_eusupologi_']) != "INTERNET" and $_SESSION['_cusupocodi_'] != "") {
                                    echo "<td align=\"right\" class=\"textonormal\" width=\"400\"><br>\n";
                                    echo "  <font class=\"titulo2\">|</font>\n";
                                    echo "	Usu&aacute;rio: ".$_SESSION['_eusupologi_']."\n";
                                    echo "</td>\n";
                                }
                                ?>
                            </tr>
					    </table>
				    </td>
			    </tr>
			    <!-- Fim do Caminho-->
			    <!-- Erro -->
			    <tr>
				    <td width="150"></td>
				    <td align="left" colspan="2">
                        <?php
                        if ($Mens == 1) {
                            ExibeMens($Mensagem, $Tipo, $Mod);
                        }
                        ?>
                    </td>
    		    </tr>
			    <!-- Fim do Erro -->
			    <!-- Corpo -->
			    <tr>
				    <td width="150"></td>
				    <td>
					    <table border="0" cellpadding="5" summary="">
						    <tr>
							    <td class="textonormal" width="500px">
								    <p align="justify">
									A Prefeitura do Recife continua a promover uma série de
									alterações em seu portal de compras com o objetivo maior de
									tornar mais eficiente, ágil e transparente a Gestão Pública
									Municipal. <br> <br>Dentro deste conjunto de alterações,
									destaca-se a renovação do cadastro do Sistema de Credenciamento
									Unificado de Fornecedores (SICREF), envolvendo os interessados
									em contatar a Prefeitura, e participar de consultas públicas e
									editais de licitações promovidas pela Administração Municipal.
									<br> <br>Outro passo importante no avanço da qualidade das
									compras municipais foi a assinatura de convênio com o Banco do
									Brasil, viabilizando desta forma o início do uso do Sistema de
									Pregão Eletrônico daquela Instituição, garantindo mais
									celeridade e qualidade aos nossos processos licitatórios. <br>
									<br>Aliado a estas alterações, a Administração Municipal já
									dispõe em seu sítio oficial de um rol completo de informações
									avisos de licitações e consulta a processos já homologados. <br>
									<br>Todo este conjunto de avanços é resultado de uma política
									governamental que prioriza a transparência nos gastos públicos,
									em conjunto com uma equipe capacitada e com o foco na eficácia
									da prestação de serviços de qualidade ao Cidadão Recifense.
								    </p>
						            <?php
                                    // Para exibir os caminhos dos programa da função segurança #
                                    // for ($i=0;$i<count($_SESSION['_eacepocami_']);$i++) {
                                    // echo "Caminho $i = ".$_SESSION['_eacepocami_'][$i]."<br>";
                                    // }	
                                    ?>
					            </td>
							    <td width="100px"></td>
							    <!-- Login -->
							    <td class="textonormal" valign="top">
						            <?php
                                    list($Ip) = explode('[\.]', $_SERVER['REMOTE_ADDR']); // $Ip = 1o número do IP remoto
                                   
                                    if (($Ip == 10 or /* IPs que iniciam com 10 são locais ou da intranet */
                                        /* IPs dos roteadores da intranet
                                        * OBS: Comentado após mudança de proxy
                                        $_SERVER['REMOTE_ADDR'] == '200.151.250.106' or
                                        $_SERVER['REMOTE_ADDR'] == '200.151.250.5' or */
                                        /* range de IPs da intranet */
                                        (preg_match($Ip, "192.168.88.201")) or (preg_match($Ip, "192.168.88.71")) or (preg_match($Ip, "192.168.")) or (preg_match($Ip, "200.151.250.")) or (preg_match($Ip, "192.207.206.")) or (preg_match($Ip, "172.18.7.")) or (preg_match($Ip, "172.18.8.")) or (preg_match($Ip, "172.18.9."))) and ($_SESSION['_cusupocodi_'] == 0) and // não logado (usuário INTERNET)
                                        ($_SESSION['_ref_'] != "transparencia"))
                                    {
                                        ?>
                                        <table border="0" summary="">
									        <tr>
										        <td class="titulo2"><input type="hidden" name="Critica" value="1"> login<br> <input class="titulo2" type="text" name="Login" size="10" maxlength="10" value="<?php echo $Login;?>"></td>
									        </tr>
									        <tr>
                                                <td class="titulo2">senha<br>
                                                    <input class="textonormal" type="password" name="Senha" size="8" maxlength="8" value="<?php echo $Senha;?>">
                                                    <input type="hidden" name="Botao" value="">
										        </td>
									        </tr>
									        <tr>
                                                <td class="titulo2">c&oacute;digo<br>
                                                    <img src="midia/seta_direita.gif" alt="">
                                                    <img src="common/rotinas_php/Gerajpeg/Gerajpeg.php"><br>
                                                    <img src="midia/seta_baixo.gif" alt=""><br>
                                                    <img src="midia/espaco.gif" alt="">
                                                    <input class="textonormal" type="text" name="Confirmacao" size="12" maxlength="5"> <br> <br>
                                                    <img width="35" src="midia/espaco.gif" alt="">
                                                    <input class="botao" type="submit" name="Envia" value="Confirmar">
										        </td>
									            <tr>
										            <td></td>
									            </tr>
									            <tr>
										            <td class="titulo2">
                                                        <a href="javascript:enviar('esquecisenha');">esqueci minha senha</a>
                                                    </td>
									            </tr>
									        </tr>
								        </table>
                                        <?php
                                    }
                                    ?>
					            </td>
							    <!-- Fim do Login -->
						    </tr>
					    </table>
				    </td>
			    </tr>
			    <!-- Fim do Corpo -->
		    </table>
	    </form>
    </body>
</html>
<?php
if ($Ip == 10 and $_SESSION['_cusupocodi_'] == 0) {
    echo "<script language=\"javascript\">\n<!--\ndocument.Login.Login.focus();\n//-->\n</script>\n";
}
?>