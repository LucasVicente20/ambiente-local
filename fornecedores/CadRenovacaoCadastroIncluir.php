<?php
# -------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadRenovacaoCadastroIncluir.php
# Autor:    João Batista Brito
# Data:     06/02/13
# Objetivo: Programa de Registro de Pedido de Renovação de Cadastro
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     29/04/2019
# Objetivo: Tarefa Redmine 215590
# -------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso ('/fornecedores/ConsAcompFornecedorSenha.php');

if ($_SESSION["_eusupologi_"] == 'INTERNET') {
	$Origem = 'F';
} else {
	$Origem = 'U';
}

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] != "") {
	$Sequencial      = @$_REQUEST['Sequencial'];
	$Botao 		     = @$_REQUEST['Botao'];
	$Critica 	     = @$_REQUEST['Critica'];
	$Certidao        = @$_REQUEST['Certidao'];
	$Certidao_Motivo = @$_REQUEST['Certidao_Motivo'];
}

if (trim($Sequencial) == "") {
	header("location: ConsAcompFornecedorSenha.php?Desvio=CadRenovacaoCadastroIncluir");
	exit;
}

if ($Botao == "Voltar") {
	if ($_SESSION["_eusupologi_"] == 'INTERNET') {
   	  	header("location: ConsAcompFornecedorSenha.php?Desvio=CadRenovacaoCadastroIncluir");
   	  	exit;
	} else {
		header("location: ConsAcompFornecedorSelecionar.php?Desvio=CadRenovacaoCadastroIncluir");
		exit;
	}
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadRenovacaoCadastroIncluir.php";

if ($Critica == 1) {
	$Mens     = 0;
	$Mensagem = "Informe: ";

	# Critica dos Campos #
	if ($Certidao == null) {
        $Mens = 1;
        $Tipo = 2;
		$Mensagem .= "<a href=\"javascript:document.getElementById('Certidao_0').focus();\" class=\"titulo2\">Selecione uma Certidão</a>";
		$Virgula = 1;
	}
    
    foreach ($Certidao as $i => $valor) {
		if ($Certidao[$i] != "" && $Certidao_Motivo[$i] == "") {
            $Mens = 1;
            $Tipo = 2;
            
            if ($Virgula == 1) {
                $Mensagem .= ", ";
            }
            
            $Mensagem .= "<a href=\"javascript:document.getElementById('Certidao_Motivo_".$i."').focus();\" class=\"titulo2\">uma descrição para a certidão</a>";
			$Virgula = 1;
		}
	}

	//Se OK
	if ($Mens == 0) {
		$db = Conexao();

		# Recupera a última comissão e incrementa mais um #
		$db->query("BEGIN TRANSACTION");

		# Bloqueio da tabela #
		$sql = "LOCK TABLE sfpc.tbrenovacaocertidoesforn ";
        
        $result = $db->query($sql);
        
        if (PEAR::isError($result)) {
			ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}

		foreach ($Certidao as $codCertidao => $valor) {
			# Chave #
		  	$sql = "SELECT MAX(crecefcodi) FROM sfpc.tbrenovacaocertidoesforn ";
            
            $result = $db->query($sql);
            
            if (PEAR::isError($result)) {
			    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}
            
            while ($Linha = $result->fetchRow()) {
				$Codigo = $Linha[0] + 1;
			}

		    # Insere #
			$usuario_aux =	$_SESSION['_cusupocodi_'];
		    $Data = date("Y-m-d H:i:s");
            
            $sql = "INSERT INTO sfpc.tbrenovacaocertidoesforn (";
		    $sql.= "crecefcodi, aforcrsequ, drecefdreg, ctipcecodi, arecefmotc, ";
		    $sql.= "freceforig, cusupocod1, crecefulat ";
		    $sql.= ") VALUES (";
		    $sql.= "$Codigo, $Sequencial, '$Data', $codCertidao, '".$Certidao_Motivo[$codCertidao]."', ";
		    $sql.= "'$Origem', $usuario_aux, '$Data')";
            
            $result = $db->query($sql);
            
            if (PEAR::isError($result)) {
				$db->query("ROLLBACK");
				EmailErroDB("Erro de banco", "Ocorreu erro em banco", $result);
                $Mens = 1;
                $Tipo = 2;
				$Mensagem = "Erro ao cadastrar .....";
			}
            
            if ($Mens == 0) {
                $Mens = 1;
                $Tipo = 1;
				$Mensagem = ' Atenção! Intenção de Renovação de Certidões Registrada com Sucesso. <br> Seu pedido será analisado e será enviado o resultado por e-mail';
			}
		}
        
        $db->query("COMMIT");
		$db->query("END TRANSACTION");
        
        $Certidao="";
		$Certidao_Motivo="";

		$db->disconnect();
	}
}
?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
    <!--
    <?php MenuAcesso(); ?>
    //-->
    
    function exibir(chk, campo) {
	    if (chk.checked) {
		    document.getElementById(campo).style.display = 'block';
	    } else {
		    document.getElementById(campo).value = '';
		    document.getElementById(campo).style.display = 'none';
	    }
    }
    
    function ocultar(campo) {
	    document.getElementById(campo).style.display = 'none';
    }

    function enviar(valor){
	    document.Comissao.Botao.value = valor;
	    document.Comissao.submit();
    }
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="CadRenovacaoCadastroIncluir.php" method="post" name="Comissao">
        <input type="hidden" name="Critica" id="Critica" value="1">
        <input type="hidden" name="Sequencial" id="Sequencial" value="<?=$Sequencial?>">
        <table cellpadding="3" border="0" summary="">
            <!-- Caminho -->
            <tr>
                <td height="100"></td> 
            </tr>
            <tr>
                <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                <td align="left" class="textonormal" colspan="2">
                    <font class="titulo2">|</font>
                    <a href="../index.php"><font color="#000000">Página Principal</font></a> >  Fornecedores > Cadastro e Gestão > Registro de Renovação.
                </td>
            </tr>
	        <!-- Fim do Caminho-->
	        
            <!-- Erro -->
            <?
            if ($Mens == 1) {
                ?>
	            <tr>
	                <td width="100"></td>
	                <td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	            </tr>	 
                <?
            }
            ?>
	        <!-- Fim do Erro -->
	        <!-- Corpo -->
	        <tr>
		        <td width="100"></td>
		        <td class="textonormal">
                    <table border="0" cellspacing="0" cellpadding="3" summary="">
                        <tr>
	      	                <td class="textonormal">
	        	                <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
	          	                    <tr>
	            	                    <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					            REGISTRO DE INTENÇÃO DE RENOVAÇÃO DE CERTIDÕES
		          	                    </td>
		        	                </tr>
		  	      	                <tr>
	    	      	                    <td class="textonormal">
	      	    		                    <p align="justify">
	        	    		                    Para realizar o pedido de renovação assinalar o(s) documento(s) a serem atualizado(s), informar o(s) <br>
	        	    		                    motivo(s) e clicar em "Registrar".<br>
                                                A Renovação de Cadastro será efetivada após envio e análise da documentação comprobatória.<br>
                                                A documentação deverá ser enviada ao Protocolo da Gerência de Credenciamento de Fornecedores - GSCF<br>
                                                (Cais do Apolo, 925, 11° andar, Sala 22. CEP:50.030-903, Bairro do Recife, Recife/PE - telefone: 3355-8235)<br>
                                                ou pelo endereço eletrônico <a href="mailto:sicref@recife.pe.gov.br">sicref@recife.pe.gov.br</a>, para o caso das certidões emitidas pela Internet.
						                    </p>
	          		                    </td>
		        	                </tr>
		        	                <tr>
	  	        	                    <td>
	    	      		                    <table class="textonormal" border="0" align="left" summary="">
	    	      		                        <?php
	    	      		                        $db = Conexao();
                                                  
                                                $sql = "SELECT CTIPCECODI, ETIPCEDESC FROM SFPC.TBTIPOCERTIDAO WHERE FTIPCEOBRI = 'S' ORDER BY CTIPCECODI ";
                                                
                                                $result = $db->query($sql);
                                    
                                                if (PEAR::isError($result)) {
										            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									            }
                                    
                                                $i = 0;
                                        
                                                while ($Linha = $result->fetchRow()) {
									                $i = $Linha[0];
										            ?>
	      	      		                            <tr>
										 	            <td>
										  	                <input type="checkbox" name="Certidao[<?=$i?>]" id="Certidao_<?=$i?>" value="<?=$i?>"
										  				    <?=($Certidao[$i]==''.$i ? "checked" : '')?> onClick="javascript:exibir(this, 'Certidao_Motivo_<?=$i?>');">
										 	            </td>
											            <td class="textonormal" bgcolor="#DCEDF7">
												            <?=$Linha[1]?>												 
												            <br/>
												            <!-- <input type="text" maxlength="20" style="display: <?=($Certidao[$i]==''.$i ? 'block' : 'none')?>" name="Certidao_Motivo[<?=$i?>]" id="Certidao_Motivo_<?=$i?>" value="<?=$Certidao_Motivo[$i]?>" /> -->  
				 							                <!-- onblur="javascript:ocultar('Certidao_Motivo');" -->
												            <textarea maxlength="200" rows="3" cols="50" name="Certidao_Motivo[<?=$i?>]" style="display: <?=($Certidao[$i]==''.$i ? 'block' : 'none')?>" id="Certidao_Motivo_<?=$i?>">
                                                                <?=$Certidao_Motivo[$i]?>
                                                            </textarea>
											            </td>
										            </tr>
										            <?php
									            }
                                            
                                                $db->disconnect();
									            ?>
	            		                    </table>
		          	                    </td>
		        	                </tr>
	  	      	                    <tr>
       	      	                        <td class="textonormal" align="right">
       	      		                        <input type="submit" name="RegistrarPedido" value="Registrar Pedido" class="botao">
					                        <input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
					                        <input type="hidden" name="Botao" value="">
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
<script language="javascript" type="">
<!--
document.Comissao.ComissaoDescricao.focus();
//-->
</script>