<?php
// -----------------------------------------------------------------------------
// Portal da DGCO
// Programa: RelTermoAutuacaoPreencher.php
// Autor: Roberta Costa
// Data: 27/12/2004
// Alterado: Álvaro Faria
// Data: 19/06/2006
// Objetivo: Programa de Preechimento do Termo de Autuação
// OBS.: Tabulação 2 espaços
// -----------------------------------------------------------------------------
// Alterado: Pitang Agile IT
// Data: 27/04/2016
// Objetivo: Requisito #129505 - Relatório Termo de Autuação
// Versão: v1.34.0
// -----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		04/07/2018
# Objetivo:	Tarefa Redmine 95885
// -----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     27/12/2018
# Objetivo: Tarefa Redmine 208783
// -----------------------------------------------------------------------------
# Alterado: Lucas Vicente
# Data:     10/10/2022
# Objetivo: Tarefa Redmine 206442
// -----------------------------------------------------------------------------

// Acesso ao arquivo de funções #
include "../funcoes.php";

// Executa o controle de segurança #
session_start();
Seguranca();

// Adiciona páginas no MenuAcesso #
AddMenuAcesso('/licitacoes/RelTermoAutuacaoPdf.php');

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao = $_POST['Botao'];
    $LicitacaoProcesso = $_POST['LicitacaoProcesso'];
    $Processo = $_POST['Processo'];
    $Ano = $_POST['Ano'];
    $Comissao = $_POST['Comissao'];
    $ProcessoNrAnterior = $_POST['ProcessoNrAnterior'];
    $Orgao = $_POST['Orgao'];
    $NumPortaria1 = $_POST['NumPortaria1'];
    $NumPortaria2 = $_POST['NumPortaria2'];
    $NumPortaria3 = $_POST['NumPortaria3'];
    $NumPortaria4 = $_POST['NumPortaria4'];
    $DataPublicacao1 = $_POST['DataPublicacao1'];
    if ($DataPublicacao1 != "") {
        $DataPublicacao1 = FormataData($DataPublicacao1);
    }
    $DataPublicacao2 = $_POST['DataPublicacao2'];
    if ($DataPublicacao2 != "") {
        $DataPublicacao2 = FormataData($DataPublicacao2);
    }
    $DataPublicacao3 = $_POST['DataPublicacao3'];
    if ($DataPublicacao3 != "") {
        $DataPublicacao3 = FormataData($DataPublicacao3);
    }
    $DataPublicacao4 = $_POST['DataPublicacao4'];
    if ($DataPublicacao4 != "") {
        $DataPublicacao4 = FormataData($DataPublicacao4);
    }
    $Publicacao = $_POST['Publicacao'];
    $Responsavel = $_POST['Responsavel'];
    $Presidente = $_POST['Presidente'];
    $Membro1 = $_POST['Membro1'];
    $Membro2 = $_POST['Membro2'];
    $Membro3 = $_POST['Membro3'];
    $Membro4 = $_POST['Membro4'];
    $DataTermo = $_POST['DataTermo'];
    if ($DataTermo != "") {
        $DataTermo = FormataData($DataTermo);
    }
    $CheckPendencias = $_POST['CheckPendencias'];
} else {
    $Tipo = $_GET['Tipo'];
    $Mens = $_GET['Mens'];
    $Mensagem = urldecode($_GET['Mensagem']);
}

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

if ($Botao == "Gerar") {
    $Mens = 0;
    $Mensagem = "Informe: ";
    $NProcesso = explode("_", $LicitacaoProcesso);
    $Processo = substr($NProcesso[0] + 10000, 1);
    $Ano = $NProcesso[1];
    $Comissao = $NProcesso[2];
    $Orgao = $NProcesso[3];
    if ($LicitacaoProcesso == "") {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.RelTermoAutuacaoPreencher.LicitacaoProcesso.focus();\" class=\"titulo2\">Processo Licitatório</a>";
    }
    if ($NumPortaria1 == "" and $NumPortaria2 == "" and $NumPortaria3 == "" and $NumPortaria4 == "") {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.RelTermoAutuacaoPreencher.NumPortaria1.focus();\" class=\"titulo2\">Pelo menos um Número da Portaria</a>";
    }
    if ($NumPortaria1 != "") {
        if (! SoNumeros($NumPortaria1)) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.RelTermoAutuacaoPreencher.NumPortaria1.focus();\" class=\"titulo2\">Primeiro Número da Portaria Válido</a>";
        }
    }
    if ($NumPortaria2 != "") {
        if (! SoNumeros($NumPortaria2)) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.RelTermoAutuacaoPreencher.NumPortaria2.focus();\" class=\"titulo2\">Segundo Número da Portaria Válido</a>";
        }
    }
    if ($NumPortaria3 != "") {
        if (! SoNumeros($NumPortaria3)) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.RelTermoAutuacaoPreencher.NumPortaria3.focus();\" class=\"titulo2\">Terceiro Número da Portaria Válido</a>";
        }
    }
    if ($NumPortaria4 != "") {
        if (! SoNumeros($NumPortaria4)) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.RelTermoAutuacaoPreencher.NumPortaria4.focus();\" class=\"titulo2\">Quarto Número da Portaria Válido</a>";
        }
    }
    if ($DataPublicacao1 == "" and $DataPublicacao2 == "" and $DataPublicacao3 == "" and $DataPublicacao4 == "") {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.RelTermoAutuacaoPreencher.DataPublicacao1.focus();\" class=\"titulo2\">Pelo menos uma Data de Publicação no DOM</a>";
    }
    if ($DataPublicacao1 != "") {
        $ValidaData = ValidaData($DataPublicacao1);
        if ($ValidaData != "") {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.RelTermoAutuacaoPreencher.DataPublicacao1.focus();\" class=\"titulo2\">Primeira Data de Publicação no DOM Válida</a>";
        }
    }
    if ($DataPublicacao2 != "") {
        $ValidaData = ValidaData($DataPublicacao2);
        if ($ValidaData != "") {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.RelTermoAutuacaoPreencher.DataPublicacao2.focus();\" class=\"titulo2\">Segunda Data de Publicação no DOM Válida</a>";
        }
    }
    if ($DataPublicacao3 != "") {
        $ValidaData = ValidaData($DataPublicacao3);
        if ($ValidaData != "") {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.RelTermoAutuacaoPreencher.DataPublicacao3.focus();\" class=\"titulo2\">Terceira Data de Publicação no DOM Válida</a>";
        }
    }
    if ($DataPublicacao4 != "") {
        $ValidaData = ValidaData($DataPublicacao4);
        if ($ValidaData != "") {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.RelTermoAutuacaoPreencher.DataPublicacao4.focus();\" class=\"titulo2\">Quarta Data de Publicação no DOM Válida</a>";
        }
    }
    if ($Publicacao == "") {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "Publicação no DOM";
    }
    if ($Responsavel == "") {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "Responsável pela Nomeação";
    }
    if ($DataTermo != "") {
        $ValidaData = ValidaData($DataTermo);
        if ($ValidaData != "") {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.RelTermoAutuacaoPreencher.DataTermo.focus();\" class=\"titulo2\">Data do Termo de Autuação Válida</a>";
        }
    }
    if ($Mens == 0) {
        // Insere as Pendências #
        
        $db = Conexao();
        $db->query("BEGIN TRANSACTION");
        $sql = "DELETE FROM SFPC.TBLICITACAOPENDENCIAS";
        $sql .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $Ano ";
        $sql .= "   AND CCOMLICODI = $Comissao AND CORGLICODI = $Orgao";
        $sql .= "   AND CGREMPCODI = " . $_SESSION['_cgrempcodi_'] . " ";
        $result = $db->query($sql);
        if (PEAR::isError($result)) {
            $db->query("ROLLBACK");
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $db->query("COMMIT");
            if (count($CheckPendencias) > 0) {
                for ($i = 0; $i < count($CheckPendencias); $i ++) {
                    // Insere Pendênca #
                    $sql = "INSERT INTO SFPC.TBLICITACAOPENDENCIAS (";
                    $sql .= "clicpoproc, alicpoanop, cgrempcodi, ccomlicodi, ";
                    $sql .= "corglicodi, ctippecodi, tlicpeulat ";
                    $sql .= ") VALUES ( ";
                    $sql .= "$Processo, $Ano," . $_SESSION['_cgrempcodi_'] . " , $Comissao, ";
                    $sql .= "$Orgao, $CheckPendencias[$i], '" . date("Y-m-d") . "')";
                    $result = $db->query($sql);
                    if (PEAR::isError($result)) {
                        $db->query("ROLLBACK");
                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                    }
                    $db->query("COMMIT");
                }
            }
        }
        $db->query("END TRANSACTION");
        $db->disconnect();
        
        // Codifica dados para serem enviados por GET #
        $Presidente = urlencode($Presidente);
        $Membro1 = urlencode($Membro1);
        $Membro2 = urlencode($Membro2);
        $Membro3 = urlencode($Membro3);
        $Membro4 = urlencode($Membro4);
        // Redireciona para a página de Impressão #
        $Url = "RelTermoAutuacaoPdf.php?Processo=$Processo&Ano=$Ano&Comissao=$Comissao&Orgao=$Orgao&NumPortaria1=$NumPortaria1&NumPortaria2=$NumPortaria2&NumPortaria3=$NumPortaria3&NumPortaria4=$NumPortaria4&DataPublicacao1=$DataPublicacao1&DataPublicacao2=$DataPublicacao2&DataPublicacao3=$DataPublicacao3&DataPublicacao4=$DataPublicacao4&Publicacao=$Publicacao&Responsavel=$Responsavel&Presidente=$Presidente&Membro1=$Membro1&Membro2=$Membro2&Membro3=$Membro3&Membro4=$Membro4&DataTermo=$DataTermo&" . mktime();
        if (! in_array($Url, $_SESSION['GetUrl'])) {
            $_SESSION['GetUrl'][] = $Url;
        }
        header("location: " . $Url);
        exit();
    }
} elseif ($Botao == "Processo") {
    $NProcesso = explode("_", $LicitacaoProcesso);
    $Processo = substr($NProcesso[0] + 10000, 1);
    $Ano = $NProcesso[1];
    $Comissao = $NProcesso[2];
    $Orgao = $NProcesso[3];
} elseif ($Botao == "Limpar") {
    $Processo = "";
    $LicitacaoProcesso = "";
    $NumPortaria1 = "";
    $NumPortaria2 = "";
    $NumPortaria3 = "";
    $NumPortaria4 = "";
    $DataPublicacao1 = "";
    $DataPublicacao2 = "";
    $DataPublicacao3 = "";
    $DataPublicacao4 = "";
    $Publicacao = "";
    $Membro1 = "";
    $Membro2 = "";
    $Membro3 = "";
    $Membro4 = "";
    $DataTermo = "";
}
?>
<html>
<?php
// Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.RelTermoAutuacaoPreencher.Botao.value=valor;
	document.RelTermoAutuacaoPreencher.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
 <script language="JavaScript" src="../menu.js"></script>
 <script language="JavaScript">Init();</script>
 <form action="RelTermoAutuacaoPreencher.php" method="post"
  name="RelTermoAutuacaoPreencher"
 >
  <br>
  <br>
  <br>
  <br>
  <br>
  <table cellpadding="3" border="0" summary="">
   <!-- Caminho -->
   <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2"><font
     class="titulo2"
    >|</font> <a href="../index.php"><font color="#000000">Página
       Principal</font></a> > Licitações > Relatório > Termo de Autuação
    </td>
   </tr>
   <!-- Fim do Caminho-->
   <!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
    <td width="100"></td>
    <td align="left" colspan="2"><?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?></td>
   </tr>
	<?php } ?>
	<!-- Fim do Erro -->
   <!-- Corpo -->
   <tr>
    <td width="100"></td>
    <td class="textonormal">
     <table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff"
      summary=""
     >
      <tr>
       <td class="textonormal">
        <table border="1" cellpadding="3" cellspacing="0"
         bordercolor="#75ADE6" summary="" class="textonormal"
        >
         <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle"
           class="titulo3"
          >TERMO DE AUTUAÇÃO</td>
         </tr>
         <tr>
          <td class="textonormal">
           <p align="justify">
            Para gerar o Termo de Autuação, preencha os campos abaixo e
            clique no botão "Gerar". Para limpar os campos, clique no
            botão "Limpar". Os campos com * são obrigatórios.<br>
            <br> Se você não possui o Acrobat Reader, clique <a
             href="javascript:janela('../pdf.php','Relatorio',400,400,1,0)"
             class="titulo2"
            >AQUI</a> para fazer o download.
           </p>
          </td>
         </tr>
         <tr>
          <td>
           <table class="textonormal" border="0" width="100%" summary="">
            <tr>
             <td class="textonormal" bgcolor="#DCEDF7" width="32%"
              valign="top"
             >Processo*</td>
             <td class="textonormal"><select name="LicitacaoProcesso"
              value="" class="textonormal"
              onChange="javascritp:enviar('Processo');"
             >
               <option value="">Selecione um Processo Licitatório...</option>
			                  	<?php
                    $db = Conexao();
                    $sql = "SELECT A.CLICPOPROC, A.ALICPOANOP, A.CCOMLICODI, B.ECOMLIDESC, C.EGREMPDESC, A.CORGLICODI ";
                    $sql .= "  FROM SFPC.TBLICITACAOPORTAL A, SFPC.TBCOMISSAOLICITACAO B, SFPC.TBGRUPOEMPRESA C, SFPC.TBUSUARIOCOMIS D ";
                    $sql .= " WHERE D.CGREMPCODI = " . $_SESSION['_cgrempcodi_'] . " AND D.CUSUPOCODI = " . $_SESSION['_cusupocodi_'] . " ";
                    $sql .= "   AND D.CCOMLICODI = A.CCOMLICODI AND  A.CGREMPCODI = D.CGREMPCODI ";
                    $sql .= "   AND A.CCOMLICODI = B.CCOMLICODI AND B.CGREMPCODI = C.CGREMPCODI ";
                    $sql .= "   AND MAKE_DATE(A.ALICPOANOP,1,1) > CURRENT_DATE - INTERVAL '5 YEARS' "; //CR 206442 MAKE_DATE
                    $sql .= " ORDER BY B.ECOMLIDESC ASC, A.ALICPOANOP DESC, A.CLICPOPROC DESC";
                    $result = $db->query($sql);
                    if (PEAR::isError($result)) {
                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                    } else {
                        while ($Linha = $result->fetchRow()) {
                            if ($Linha[2] != $ComissaoCodigoAnt) {
                                $ComissaoCodigoAnt = $Linha[2];
                                echo "<option value=\"\">$Linha[3]</option>\n";
                            }
                            if ($LicitacaoProcesso == "$Linha[0]_$Linha[1]_$Linha[2]_$Linha[5]") {
                                echo "<option value=\"$Linha[0]_$Linha[1]_$Linha[2]_$Linha[5]\" selected>&nbsp;&nbsp;&nbsp;" . substr($Linha[0] + 10000, 1) . "/$Linha[1]</option>\n";
                            } else {
                                echo "<option value=\"$Linha[0]_$Linha[1]_$Linha[2]_$Linha[5]\">&nbsp;&nbsp;&nbsp;" . substr($Linha[0] + 10000, 1) . "/$Linha[1]</option>\n";
                            }
                        }
                    }
                    $db->disconnect();
                    ?>
			                  </select></td>
            </tr>
            <tr>
             <td class="textonormal" bgcolor="#DCEDF7" valign="top">Nº
              da(s) Portaria(s)/Data(s) das designações para composição
              da Comissão de Licitação*</td>
             <td class="textonormal"><input type="text"
              name="NumPortaria1" size="6" maxlength="5"
              value="<?php echo $NumPortaria1; ?>" class="textonormal"
             >
		      							<?php $URL1 = "../calendario.php?Formulario=RelTermoAutuacaoPreencher&Campo=DataPublicacao1";?>
					        			<input type="text" name="DataPublicacao1" size="10"
              maxlength="10" value="<?php echo $DataPublicacao1; ?>"
              class="textonormal"
             > <a
              href="javascript:janela('<?php echo $URL1 ?>','Calendario',220,170,1,0)"
             ><img src="../midia/calendario.gif" border="0" alt=""></a>
              <font class="textonormal">dd/mm/aaaa</font><br> <input
              type="text" name="NumPortaria2" size="6" maxlength="5"
              value="<?php echo $NumPortaria2; ?>" class="textonormal"
             >
		      							<?php $URL2 = "../calendario.php?Formulario=RelTermoAutuacaoPreencher&Campo=DataPublicacao2";?>
					        			<input type="text" name="DataPublicacao2" size="10"
              maxlength="10" value="<?php echo $DataPublicacao2; ?>"
              class="textonormal"
             > <a
              href="javascript:janela('<?php echo $URL2 ?>','Calendario',220,170,1,0)"
             ><img src="../midia/calendario.gif" border="0" alt=""></a>
              <font class="textonormal">dd/mm/aaaa</font><br> <input
              type="text" name="NumPortaria3" size="6" maxlength="5"
              value="<?php echo $NumPortaria3; ?>" class="textonormal"
             >
		      							<?php $URL3 = "../calendario.php?Formulario=RelTermoAutuacaoPreencher&Campo=DataPublicacao3";?>
					        			<input type="text" name="DataPublicacao3" size="10"
              maxlength="10" value="<?php echo $DataPublicacao3; ?>"
              class="textonormal"
             > <a
              href="javascript:janela('<?php echo $URL3 ?>','Calendario',220,170,1,0)"
             ><img src="../midia/calendario.gif" border="0" alt=""></a>
              <font class="textonormal">dd/mm/aaaa</font><br> <input
              type="text" name="NumPortaria4" size="6" maxlength="5"
              value="<?php echo $NumPortaria4; ?>" class="textonormal"
             >
		      							<?php $URL4 = "../calendario.php?Formulario=RelTermoAutuacaoPreencher&Campo=DataPublicacao4";?>
					        			<input type="text" name="DataPublicacao4" size="10"
              maxlength="10" value="<?php echo $DataPublicacao4; ?>"
              class="textonormal"
             > <a
              href="javascript:janela('<?php echo $URL4 ?>','Calendario',220,170,1,0)"
             ><img src="../midia/calendario.gif" border="0" alt=""></a>
              <font class="textonormal">dd/mm/aaaa</font></td>
            </tr>
            <tr>
             <td class="textonormal" bgcolor="#DCEDF7" valign="top">Publicação
              no DOM *</td>
             <td class="textonormal"><input type="radio"
              name="Publicacao" value="S"
              <?php if( $Publicacao == "S" or $Publicacao == ""){ echo "checked"; } ?>
             >Sim <input type="radio" name="Publicacao" value="N"
              <?php if( $Publicacao == "N" ){ echo "checked"; } ?>
             >Não</td>
            </tr>
            <tr>
             <td class="textonormal" bgcolor="#DCEDF7" valign="top">Responsável
              pela Nomeação*</td>
             <td class="textonormal"><input type="radio"
              name="Responsavel" value="1"
              <?php if( $Responsavel == 1 ){ echo "checked"; } ?>
             >Prefeito <input type="radio" name="Responsavel" value="2"
              <?php if( $Responsavel == 2 ){ echo "checked"; } ?>
             >Presidente do Órgão</td>
            </tr>
										<?php
        $NProcesso = explode("_", $LicitacaoProcesso);
        $ProcessoNr = $NProcesso[0];
        if (($ProcessoNrAnterior != $ProcessoNr) and $ProcessoNr) {
            // Busca o nome do presidente da comissão #
            $db = Conexao();
            $sqlpres = "SELECT NCOMLIPRES ";
            $sqlpres .= "  FROM SFPC.TBCOMISSAOLICITACAO ";
            $sqlpres .= " WHERE CCOMLICODI = $Comissao ";
            $respres = $db->query($sqlpres);
            if (PEAR::isError($respres)) {
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlpres");
            } else {
                $LinhaPres = $respres->fetchRow();
                $Presidente = $LinhaPres[0];
            }
            $db->disconnect();
        }
        ?>
	            			<tr>
             <td class="textonormal" bgcolor="#DCEDF7" valign="top">Presidente/Pregoeiro</td>
             <td class="textonormal"><input type="text"
              name="Presidente" size="45" maxlength="60"
              value="<?php echo $Presidente; ?>" class="textonormal"
             ></td>
            </tr>
            <tr>
             <td class="textonormal" bgcolor="#DCEDF7" valign="top">Nome
              dos Membros</td>
             <td class="textonormal"><input type="text" name="Membro1"
              size="45" maxlength="60" value="<?php echo $Membro1; ?>"
              class="textonormal"
             > <input type="text" name="Membro2" size="45"
              maxlength="60" value="<?php echo $Membro2; ?>"
              class="textonormal"
             > <input type="text" name="Membro3" size="45"
              maxlength="60" value="<?php echo $Membro3; ?>"
              class="textonormal"
             > <input type="text" name="Membro4" size="45"
              maxlength="60" value="<?php echo $Membro4; ?>"
              class="textonormal"
             ></td>
            </tr>
            <tr>
             <td class="textonormal" bgcolor="#DCEDF7" valign="top">Data
              do Termo de Autuação*</td>
             <td class="textonormal">
		      							<?php
            $URL = "../calendario.php?Formulario=RelTermoAutuacaoPreencher&Campo=DataTermo";
            if ($DataTermo == "") {
                $DataTermo = date("d/m/Y");
            }
            ?>
					        			<input type="text" name="DataTermo" size="10"
              maxlength="10" value="<?php echo $DataTermo; ?>"
              class="textonormal"
             > <a
              href="javascript:janela('<?php echo $URL ?>','Calendario',220,170,1,0)"
             ><img src="../midia/calendario.gif" border="0" alt=""></a>
             </td>
            </tr>
            <tr>
             <td class="textonormal" bgcolor="#DCEDF7" valign="top">Pendências</td>
             <td class="textonormal">
												<?php
            $db = Conexao();
            $sql = "SELECT CTIPPECODI, ETIPPEDESC FROM SFPC.TBTIPOPENDENCIA ORDER BY ETIPPEDESC";
            $result = $db->query($sql);
            if (PEAR::isError($result)) {
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
            } else {
                $Rows = $result->numRows();
                for ($i = 0; $i < $Rows; $i ++) {
                    $Linha = $result->fetchRow();
                    if ($LicitacaoProcesso != "") {
                        // Verifica se a pendência já foi escolhida #
                        $sqlpen = "SELECT COUNT(CTIPPECODI) ";
                        $sqlpen .= "  FROM SFPC.TBLICITACAOPENDENCIAS";
                        $sqlpen .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $Ano ";
                        $sqlpen .= "   AND CCOMLICODI = $Comissao AND CORGLICODI = $Orgao";
                        $sqlpen .= "   AND CGREMPCODI = " . $_SESSION['_cgrempcodi_'] . " ";
                        $sqlpen .= "   AND CTIPPECODI = $Linha[0]";
                        $respen = $db->query($sqlpen);
                        echo "<input type=\"checkbox\" name=\"CheckPendencias[]\" value=\"$Linha[0]\"";
                        if (PEAR::isError($respen)) {
                            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sqlpen");
                        } else {
                            $Cont = $respen->fetchRow();
                            if ($Cont[0] != 0) {
                                echo " checked";
                            }
                        }
                        echo ">$Linha[1]<br>\n";
                    } else {
                        echo "<input type=\"checkbox\" name=\"CheckPendencias[]\" value=\"$Linha[0]\">$Linha[1]<br>\n";
                    }
                }
            }
            $db->disconnect();
            ?>
											</td>
            </tr>
           </table>
          </td>
         </tr>
         <tr>
          <td class="textonormal" align="right"><input type="hidden"
           name="Processo" value="<?php echo $Processo; ?>"
          > <input type="hidden" name="Ano" value="<?php echo $Ano; ?>">
           <input type="hidden" name="Comissao"
           value="<?php echo $Comissao; ?>"
          > <input type="hidden" name="ProcessoNrAnterior"
           value="<?php echo $ProcessoNr; ?>"
          > <input type="hidden" name="Orgao"
           value="<?php echo $Orgao; ?>"
          > <input type="button" value="Gerar" class="botao"
           onclick="javascript:enviar('Gerar');"
          > <input type="button" value="Limpar" class="botao"
           onclick="javascript:enviar('Limpar');"
          > <input type="hidden" name="Botao" value=""></td>
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
document.RelTermoAutuacaoPreencher.LicitacaoProcesso.focus();
//-->
</script>
