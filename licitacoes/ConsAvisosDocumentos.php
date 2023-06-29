<?php
// -------------------------------------------------------------------------
// Portal da DGCO
// Programa: ConsAvisosDocumentos.php
// Autor: Roberta Costa
// Data: 30/04/03
// Objetivo: Programa de Pesquisa de Avisos de Licitação
// -------------------
// Alterado: Carlos Abreu
// Data: 25/08/2006 - Mudança de Variáveis GET para POST

// Alterado: Ariston Cordeiro
// Data: 29/08/2008 - Checar caso alguma variável em post requerida não esteja sendo fornecida

// Alterado: Ariston Cordeiro
// Data: 06/05/2009 - Quando apertar o botão voltar, voltar para mesma pesquisa da página anterior

// Alterado: Rodrigo Melo
// Data: 01/09/2010 - Alteração para permitir a visualização das planilhas RESULTADO_9999_99_99_99_9999.XLS e
// ORCAMENTO_9999_99_99_99_9999.XLS APENAS para os usuários que possuem
// os perfis COMISSAO LICITACAO (7) ou COMISS LICITACAO-REQUISITANTE (18)- CR: 5210.
// Alterado: Ariston Cordeiro
// Data: 02/03/2011 - Mostrar Documentos marcados como excluídos
//
// Alterado: Ariston Cordeiro
// Data: 23/03/2011 - não mostrar responsáveis e observações de documentos alterados antes da data em que a melhoria foi colocada
//
// Alterado: Heraldo Botelho
// Data: 31/10/2011 - exibir grid com itens de serviços e itens de materiais se encontrar pelo menos 1(um)
// registro
//
// ------------------
// OBS.: Tabulação 2 espaços
// -------------------------------------------------------------------------

// Acesso ao arquivo de funções #
include "../funcoes.php";
include "funcoesLicitacoes.php";

// Executa o controle de segurança #
session_start();
Seguranca();

// Adiciona páginas no MenuAcesso #
AddMenuAcesso('/licitacoes/ConsAvisosDownload.php');
AddMenuAcesso('/licitacoes/ConsAvisosPesquisar.php');

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao = $_POST['Botao'];
    $Critica = $_POST['Critica'];
    $Mensagem = $_POST['Mensagem'];
    $Mens = $_POST['Mens'];
    $Tipo = $_POST['Tipo'];
    $Objeto = $_POST['Objeto'];
    $OrgaoLicitanteCodigo = $_POST['OrgaoLicitanteCodigo'];
    $ComissaoCodigo = $_POST['ComissaoCodigo'];
    $ModalidadeCodigo = $_POST['ModalidadeCodigo'];
    $GrupoCodigo = $_POST['GrupoCodigo'];
    $LicitacaoProcesso = $_POST['LicitacaoProcesso'];
    $LicitacaoAno = $_POST['LicitacaoAno'];
    $LicitacaoAnoAux = $_POST['LicitacaoAno'];
    
    /*
     * if(is_null($Objeto)){
     * $Objeto="X";
     * }
     */
} else {
    $Acesso = $_GET['Acesso'];
    if ($Acesso == "INTERNET") {
        TiraSeguranca();
    }
}

// redirecionar para Pesquisar, caso dados necessários para renderizar a página não forem especificados
if ((is_null($GrupoCodigo)) && (is_null($ComissaoCodigo)) && (is_null($LicitacaoProcesso)) && (is_null($LicitacaoAno)) && (is_null($OrgaoLicitanteCodigo))) {
    header("Location: ConsAvisosPesquisar.php");
    exit();
}

// Adiciona páginas no MenuAcesso #
AddMenuAcesso('/licitacoes/ConsAvisosResultado.php');

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "ConsAvisosDocumentos.php";

if ($Botao != "Voltar") {
    $db = Conexao();
    $sql = "SELECT A.EGREMPDESC, B.EMODLIDESC, C.ECOMLIDESC, D.CLICPOCODL, ";
    $sql .= "       D.ALICPOANOL, D.XLICPOOBJE, E.EORGLIDESC, D.TLICPODHAB ";
    $sql .= "  FROM SFPC.TBGRUPOEMPRESA A, SFPC.TBMODALIDADELICITACAO B, SFPC.TBCOMISSAOLICITACAO C, ";
    $sql .= "       SFPC.TBLICITACAOPORTAL D, SFPC.TBORGAOLICITANTE E ";
    $sql .= " WHERE A.CGREMPCODI = D.CGREMPCODI    AND D.CGREMPCODI = $GrupoCodigo  ";
    $sql .= "   AND D.CMODLICODI = B.CMODLICODI    AND C.CCOMLICODI = D.CCOMLICODI ";
    $sql .= "   AND D.CCOMLICODI = $ComissaoCodigo AND D.CLICPOPROC = $LicitacaoProcesso ";
    $sql .= "   AND D.ALICPOANOP = $LicitacaoAno   AND E.CORGLICODI = D.CORGLICODI ";
    $sql .= "   AND D.CORGLICODI = $OrgaoLicitanteCodigo";
    $result = $db->query($sql); var_dump($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\nIP: " . $_SERVER["REMOTE_ADDR"]);
    } else {
        $Rows = $result->numRows();
        while ($Linha = $result->fetchRow()) {
            $GrupoDescricao = $Linha[0];
            $ModalidadeDescricao = $Linha[1];
            $ComissaoDescricao = $Linha[2];
            $NLicitacao = substr($Linha[3] + 10000, 1);
            $LicitacaoAno = $Linha[4];
            $ObjetoLicitacao = $Linha[5];
            $OrgaoLicitanteDescricao = $Linha[6];
            $LicitacaoDtAbertura = substr($Linha[7], 8, 2) . "/" . substr($Linha[7], 5, 2) . "/" . substr($Linha[7], 0, 4);
            $LicitacaoHoraAbertura = substr($Linha[7], 11, 5);
        }
    }
}
?>
<html>
<?
// Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.Avisos.Botao.value=valor;
	document.Avisos.submit();
}
function AbreDocumentos(ObjetoParametro,OrgaoLicitanteCodigo,ComissaoCodigo,ModalidadeCodigo,GrupoCodigo,LicitacaoProcesso,LicitacaoAno,DocumentoCodigo){
	//alert(document.Avisos);
	document.Avisos.Objetox.value=ObjetoParametro;
	document.Avisos.OrgaoLicitanteCodigo.value=OrgaoLicitanteCodigo;
	document.Avisos.ComissaoCodigo.value=ComissaoCodigo;
	document.Avisos.ModalidadeCodigo.value=ModalidadeCodigo;
	document.Avisos.GrupoCodigo.value=GrupoCodigo;
	document.Avisos.LicitacaoProcesso.value=LicitacaoProcesso;
	document.Avisos.LicitacaoAno.value=LicitacaoAno;
	document.Avisos.DocumentoCodigo.value=DocumentoCodigo;
	document.Avisos.submit();
}
<?
MenuAcesso();
?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" bgcolor="#FFFFFF" text="#000000"
	marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="ConsAvisosDownload.php" method="post" name="Avisos">
		<br> <br> <br> <br>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2"><br> <font
					class="titulo2">|</font> <a href="../index.php"><font
						color="#000000">Página Principal</font></a> > Licitações > Avisos
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
						summary="">
						<tr>
							<td class="textonormal">
								<table border="1" cellpadding="3" cellspacing="0"
									bordercolor="#75ADE6" summary="" class="textonormal">
									<tr>
										<td align="center" bgcolor="#75ADE6" valign="middle"
											class="titulo3">AVISOS DE LICITAÇÕES - DOWNLOAD DE DOCUMENTOS
										</td>
									</tr>
									<tr>
										<td class="textonormal">
											<p align="justify">Selecione o documento desejado.</p>
										</td>
									</tr>
									<tr>
										<td>
											<table width=100% class="textonormal" border="0"
												align="center" summary="">
												<tr>
													<td class="textonegrito" bgcolor="#DCEDF7" colspan="5">
	        	      			<?php echo "$GrupoDescricao > $ModalidadeDescricao > $ComissaoDescricao<br>"; ?>
	        	      		</td>
												</tr>
												<tr>
													<td valign="top" bgcolor="#F7F7F7" class="textonegrito">PROCESSO</td>
													<td valign="top" bgcolor="#F7F7F7" class="textonegrito">LICITAÇÃO</td>
													<td valign="top" bgcolor="#F7F7F7" class="textonegrito">OBJETO</td>
													<td valign="top" bgcolor="#F7F7F7" class="textonegrito">DATA/HORA<br>ABERTURA
													</td>
													<td valign="top" bgcolor="#F7F7F7" class="textonegrito">ÓRGÃO
														LICITANTE</td>
												</tr>
										<?php
        $LicitacaoProcesso = substr($LicitacaoProcesso + 10000, 1);
        echo "<tr><td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$LicitacaoProcesso/$LicitacaoAno</td>\n";
        echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$NLicitacao/$LicitacaoAno</td>\n";
        echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$ObjetoLicitacao</td>\n";
        echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$LicitacaoDtAbertura<br>$LicitacaoHoraAbertura h</b></td>\n";
        echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$OrgaoLicitanteDescricao</td></tr>\n";
        
        // --------------------------------------------
        // Verificar se Licitação tem resultado
        // ---------------------------------------------
        $sql = " select flicporesu as resultado ";
        $sql .= " from sfpc.tblicitacaoportal ";
        $sql .= " where ";
        $sql .= " clicpoproc = $LicitacaoProcesso";
        $sql .= " and alicpoanop = " . $LicitacaoAnoAux;
        $sql .= " and cgrempcodi = " . $GrupoCodigo;
        $sql .= " and ccomlicodi = " . $ComissaoCodigo;
        $sql .= " and corglicodi = " . $OrgaoLicitanteCodigo;
        
        $result = executarTransacao($db, $sql);
        $row = $result->fetchRow(DB_FETCHMODE_OBJECT);
        
        $licitacaoComResultado = false;
        if ($row->resultado == 'S') {
            $licitacaoComResultado = true;
        }
        
        // --------------------------------------------
        // Verificar ultim afase da licitação
        // ---------------------------------------------
        $ultimaFase = ultimaFase($LicitacaoProcesso, $LicitacaoAno, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $db);
        
        // --------------------------------------------------------
        // Inserido por Heraldo
        // para exibir itens de materiais e de serviços
        // ---------------------------------------------------------
        
        // --------------------------------------------------------
        // SQL para capturar os itens de material da licitação
        // ---------------------------------------------------------
        $sql = " select a.aitelporde, b.ematepdesc, a.cmatepsequ, c.eunidmdesc, a.aitelpqtso, a.citelpnuml, ";
        $sql .= " d.aforcrsequ, d.nforcrrazs, d.nforcrfant, d.aforcrccgc ";
        $sql .= " from ";
        $sql .= " sfpc.tbitemlicitacaoportal a left join sfpc.tbfornecedorcredenciado d ";
        $sql .= " ON a.aforcrsequ = d.aforcrsequ, ";
        $sql .= " sfpc.tbmaterialportal b, sfpc.tbunidadedemedida c ";
        $sql .= " where ";
        $sql .= " a.cmatepsequ = b.cmatepsequ  ";
        $sql .= " and b.cunidmcodi = c.cunidmcodi  ";
        $sql .= " and  a.clicpoproc=" . $LicitacaoProcesso;
        $sql .= " and  a.alicpoanop=" . $LicitacaoAnoAux;
        $sql .= " and a.cgrempcodi=" . $GrupoCodigo;
        $sql .= " and a.ccomlicodi=" . $ComissaoCodigo;
        $sql .= " and a.corglicodi=" . $OrgaoLicitanteCodigo;
        $sql .= " order by 6,1 ";
        
        // echo $sql;
        // exit;
        
        $result = $db->query($sql);
        
        $Rows = $result->numRows();
        
        // echo $sql;
        // exit;
        
        // include_once("includeLixo.php");
        // ------------------------------------------------------------
        // - Se encontrar pelo menos uma linha exibir grade com Itens
        // ------------------------------------------------------------
        if ($Rows > 0) {
            echo "<tr  class=\"textonegrito\" bgcolor=\"#75ADE6\"   > ";
            echo "<td colspan=5 align=\"center\"   valign=\"middle\" >ITENS DE MATERIAIS DA LICITAÇÃO</td>";
            echo "</tr>";
            
            echo "<tr>";
            echo "<td colspan=5>";
            
            echo "<table width=\"100%\" border=\"1\" cellpadding=\"3\" cellspacing=\"0\" bordercolor=\"#75ADE6\" summary=\"\" class=\"textonormal\" style=\"width:100%;  border:1px;\"  >";
            echo "<tr class=\"textonegrito\" bgcolor=\"#DCEDF7\" >";
            // echo "<td valign=top colspan=5> LOTE ".($numLoteMat)."</td>";
            // echo "<td width=30px>ORD.</td><td >DESC. ITEM</td><td >CÓD</td><td>UNIDADE</td><td>QUANTIDADE</td>";
            echo "</tr>";
            
            $numLoteMatAntes = "999";
            
            while ($Linha = $result->fetchRow()) {
                $ordMaterial = $Linha[0];
                $descMaterial = $Linha[1];
                $seqMaterial = $Linha[2];
                $unidMaterial = $Linha[3];
                $qtdMaterial = $Linha[4];
                $numLoteMat = $Linha[5];
                $codForCredMat = $Linha[6];
                $razaoSocForMat = $Linha[7];
                $nomeFantForMat = $Linha[8];
                $cgcForCredMat = $Linha[9];
                
                if ($numLoteMat != $numLoteMatAntes) {
                    
                    $numLoteMatAntes = $numLoteMat;
                    
                    if ($licitacaoComResultado and $ultimaFase == 13 and ! empty($razaoSocForMat)) {
                        // if( true ){
                        $soma = getTotalValorLogrado($db, $LicitacaoProcesso, $LicitacaoAnoAux, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $numLoteMat);
                        
                        // if($codForCredMat != "" && $razaoSocFor != ""){
                        echo "<tr class=\"textonegrito\" bgcolor=\"#75ADE6\">";
                        echo "<td valign=top colspan=5> LOTE " . ($numLoteMat) . " FORNECEDOR VENCEDOR: " . FormataCpfCnpj($cgcForCredMat) . " - " . ($razaoSocForMat) . " - " . "R$ " . (number_format((float) $soma, 2, ",", ".")) . " </td>";
                        echo "</tr>";
                    } else {
                        echo "<tr class=\"textonegrito\" bgcolor=\"#75ADE6\">";
                        echo "<td valign=top colspan=5> LOTE " . ($numLoteMat) . " </td>";
                        echo "</tr>";
                    }
                    echo "<tr class=\"textonegrito\" bgcolor=\"#DCEDF7\" >";
                    echo "<td width=30px>ORD.</td><td >DESC. ITEM</td><td >CÓD</td><td>UNIDADE</td><td>QUANTIDADE</td>";
                    echo "</tr>";
                }
                
                echo "<td valign=top>" . $ordMaterial . "</td>";
                echo "<td valign=top>" . ($descMaterial) . "</td>";
                echo "<td valign=top>" . ($seqMaterial) . "</td>";
                echo "<td valign=top>" . $unidMaterial . "</td>";
                echo "<td valign=rigth   align=\"rigth\" > " . number_format($qtdMaterial, "4", ",", ".") . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</td>";
            echo "</tr>";
        }
        
        // --------------------------------------------------------
        // SQL para capturar os itens de serviço da licitação
        // ---------------------------------------------------------
        $sql = " select a.aitelporde, b.eservpdesc, a.cservpsequ, a.citelpnuml, c.aforcrsequ, ";
        $sql .= " c.nforcrrazs, c.nforcrfant, c.aforcrccgc ";
        $sql .= " from sfpc.tbitemlicitacaoportal a left join sfpc.tbfornecedorcredenciado c ";
        $sql .= " ON a.aforcrsequ = c.aforcrsequ, ";
        $sql .= " sfpc.tbservicoportal b ";
        $sql .= " where ";
        $sql .= " a.cservpsequ = b.cservpsequ   ";
        $sql .= " and  a.clicpoproc=" . $LicitacaoProcesso;
        $sql .= " and  a.alicpoanop=" . $LicitacaoAnoAux;
        $sql .= " and a.cgrempcodi=" . $GrupoCodigo;
        $sql .= " and a.ccomlicodi=" . $ComissaoCodigo;
        $sql .= " and a.corglicodi=" . $OrgaoLicitanteCodigo;
        $sql .= " order by 4,1 ";
        $result = $db->query($sql);
        // print_r($sql);
        // exit;
        
        $Rows = $result->numRows();
        
        // ------------------------------------------------------------
        // - Se encontrar pelo menos uma linha exibir grade com Itens
        // ------------------------------------------------------------
        if ($Rows > 0) {
            echo "<tr  class=\"textonegrito\" bgcolor=\"#75ADE6\"   > ";
            echo "<td colspan=5 align=\"center\"   valign=\"middle\" >ITENS DE SERVIÇO DA LICITAÇÃO</td>";
            echo "</tr>";
            
            echo "<tr>";
            echo "<td colspan=5>";
            
            echo "<table  border=\"1\" cellpadding=\"3\" cellspacing=\"0\" bordercolor=\"#75ADE6\" summary=\"\" class=\"textonormal\" style=\"width:100%;  border:1px;\">";
            echo "<tr class=\"textonegrito\" bgcolor=\"#DCEDF7\" >";
            // echo "<tr><td colspan=5> LOTE </td></tr>";
            // echo "<td width=30px>ORD.</td><td >DESC. ITEM</td><td >CÓD</td> ";
            echo "</tr>";
            
            $numLoteServAntes = "999";
            
            while ($Linha = $result->fetchRow()) {
                $ordServico = $Linha[0];
                $descServico = $Linha[1];
                $seqServico = $Linha[2];
                $numLoteServico = $Linha[3];
                $codForCredServ = $Linha[4];
                $razaoSocForServ = $Linha[5];
                $nomeFantFornServ = $Linha[6];
                $cgcForCredServ = $Linha[7];
                
                if ($numLoteServico != $numLoteServAntes) {
                    
                    $numLoteServAntes = $numLoteServico;
                    
                    // if($codForCredServ != "" && $razaoSocForServ != ""){
                    if ($licitacaoComResultado and $ultimaFase == 13 and ! empty($razaoSocForServ)) {
                        $soma = getTotalValorServico($db, $LicitacaoProcesso, $LicitacaoAnoAux, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $numLoteServico);
                        
                        echo "<tr class=\"textonegrito\" bgcolor=\"#75ADE6\">";
                        // echo "<td valign=top colspan=5> LOTE ".($numLoteServico)." FORNECEDOR ".($nomeFantFornServ)." : ".($cgcForCredServ)." - ".($razaoSocForServ)." - ".($Somatório[$i])." </td>";
                        echo "<td valign=top colspan=5> LOTE " . ($numLoteServico) . " FORNECEDOR VENCEDOR: " . FormataCpfCnpj($cgcForCredServ) . " - " . ($razaoSocForServ) . " - " . ($razaoSocForServ) . " - " . "R$ " . (number_format((float) $soma, 2, ",", ".")) . " </td>";
                        echo "</tr>";
                    } else {
                        echo "<tr class=\"textonegrito\" bgcolor=\"#75ADE6\">";
                        echo "<td valign=top colspan=3> LOTE " . ($numLoteServico) . "</td>";
                        echo "</tr>";
                    }
                    echo "<tr class=\"textonegrito\" bgcolor=\"#DCEDF7\" >";
                    echo "<td width=30px>ORD.</td><td >DESC. ITEM</td><td colspan=2>CÓD</td> ";
                    echo "</tr>";
                }
                
                echo "<tr>";
                echo "<td valign=top>" . ($ordServico) . "</td>";
                echo "<td valign=top>" . ($descServico) . "</td>";
                echo "<td valign=top>" . ($seqServico) . "</td>";
                // echo "<td valign=top>".($seqMaterial)."</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</td>";
            echo "</tr>";
        }
        
        // --------------------------------------------------------
        // Final Trecho de código inserido por Heraldo
        // ---------------------------------------------------------
        
        ?>
										<tr>
												<?php
            if ($Mens2 == 1) {
                ExibeMens($Mensagem, $Tipo);
            }
            $sql = "SELECT CDOCLICODI, EDOCLINOME, EDOCLIOBSE, fdocliexcl, u.eusuporesp, tdocliulat  ";
            $sql .= "  FROM SFPC.TBDOCUMENTOLICITACAO d, sfpc.tbusuarioportal u ";
            $sql .= " WHERE CLICPOPROC = $LicitacaoProcesso AND ALICPOANOP = $LicitacaoAno ";
            $sql .= "   AND CCOMLICODI = $ComissaoCodigo    AND d.CGREMPCODI = $GrupoCodigo AND u.cusupocodi = d.cusupocodi";
            
            // Exibir as planilhas ORCAMENTO_9999_99_99_99_9999.XLS <ANO+CODORGÃO+CODUNIDADE+CODCOMISSAO+CODPROCESSO>
            // e RESULTADO_9999_99_99_99_9999.XLS <ANO+CODORGÃO+CODUNIDADE+CODCOMISSAO+CODPROCESSO> APENAS
            // para os usuários que possuem os perfis COMISSAO LICITACAO (7) ou COMISS LICITACAO-REQUISITANTE (18)
            
            // VER ALTERAÇÃO: 01/09/2010 - CR: 5210
            
            // Em caso de dúvidas na expressão regular consultar o seguinte site:
            // http://www.postgresql.org/docs/8.1/interactive/functions-matching.html#FUNCTIONS-POSIX-REGEXP
            
            if ($_SESSION['_cperficodi_'] == null or ($_SESSION['_cperficodi_'] != 7 and $_SESSION['_cperficodi_'] != 18)) {
                $sql .= " AND ( NOT ( (edoclinome ~* '^RESULTADO_') OR (edoclinome ~* '^ORCAMENTO_') ) 	) ";
            }
            
            $result = $db->query($sql);
            if (PEAR::isError($result)) {
                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql\n\nIP: " . $_SERVER["REMOTE_ADDR"]);
            } else {
                $Rows = $result->numRows();
                if ($Rows > 0) {
                    
                    ?>
															<td class="textonegrito" bgcolor="#DCEDF7" colspan="5">DOCUMENTOS
														RELACIONADOS</td>
													<table border="1" cellpadding="3" cellspacing="0"
														bordercolor="#75ADE6" summary="" class="textonormal"
														style="width: 100%; border: 1px;">
														<tr>
															<td valign="top" bgcolor="#F7F7F7" class="textonegrito">&nbsp;</td>
															<td valign="top" bgcolor="#F7F7F7" class="textonegrito">DOCUMENTO</td>
															<td valign="top" bgcolor="#F7F7F7" class="textonegrito">TAMANHO</td>
															<td valign="top" bgcolor="#F7F7F7" class="textonegrito">RESPONSÁVEL</td>
															<td valign="top" bgcolor="#F7F7F7" class="textonegrito">OBSERVAÇÃO/<br />JUSTIFICATIVA
															</td>
														</tr>
																<?php
                    resetArquivoAcesso();
                    while ($Linha = $result->fetchRow()) {
                        
                        $itemCodigo = $Linha[0];
                        $itemNome = $Linha[1];
                        $itemObservacao = $Linha[2];
                        $itemExcluido = $Linha[3];
                        $itemAutor = $Linha[4];
                        $itemDataAlteracao = $Linha[5];
                        $ArqUpload = "licitacoes/" . "DOC" . $GrupoCodigo . "_" . $LicitacaoProcesso . "_" . $LicitacaoAno . "_" . $ComissaoCodigo . "_" . $OrgaoLicitanteCodigo . "_" . $itemCodigo;
                        $Arq = $GLOBALS["CAMINHO_UPLOADS"] . $ArqUpload;
                        addArquivoAcesso($ArqUpload);
                        $itemArquivoExiste = TRUE;
                        $tamanho = "";
                        if (! file_exists($Arq)) {
                            $itemArquivoExiste = FALSE;
                        } else {
                            $tamanho = filesize($Arq) / 1024;
                        }
                        if ($itemExcluido == "S") {
                            $itemNome = "<s style='text-decoration:line-through;'>" . $itemNome . "</s> <b>(excluído)</b>";
                        } else 
                            if (! $itemArquivoExiste) {
                                $itemNome = "" . $itemNome . " <b>(arquivo não armazenado)</b>";
                            } else {
                                $itemNome = "<a href=\"#\" onclick=\"AbreDocumentos('$Objeto', '$OrgaoLicitanteCodigo','$ComissaoCodigo','$ModalidadeCodigo','$GrupoCodigo','$LicitacaoProcesso','$LicitacaoAno','$itemCodigo');\" class=\"textonormal\">" . $itemNome . "</a>";
                            }
                        // Autor e observação de documentos de antes da melhoria não devem ser mostrados
                        if ($itemDataAlteracao < "2011-03-23") {
                            $itemAutor = "---";
                            $itemObservacao = "---";
                        }
                        ?>
																			<tr>
															<td class="textonormal">
																					<?php if( $itemExcluido != "S" AND $itemArquivoExiste ){ ?>
																						<a href='#'
																onclick="AbreDocumentos('<?=$Objeto?>', '<?=$OrgaoLicitanteCodigo?>','<?=$ComissaoCodigo?>','<?=$ModalidadeCodigo?>','<?=$GrupoCodigo?>','<?=$LicitacaoProcesso?>','<?=$LicitacaoAno?>','<?=$itemCodigo?>');"
																class='textonormal'><img src='../midia/disquete.gif'
																	border='0' /></a>
																					<?php } else { ?>
																						<img src='../midia/disqueteInexistente.gif'
																border='0' />
																					<?php } ?>
																				</td>
															<td class="textonormal" bgcolor="#F7F7F7"><?=$itemNome?>&nbsp;</td>
															<td class="textonormal" bgcolor="#F7F7F7">
																					<?php if($itemArquivoExiste){printf("%01.1f",$tamanho);echo " K";}?>
																					&nbsp;
																				</td>
															<td class="textonormal" bgcolor="#F7F7F7"><?=$itemAutor?>&nbsp;</td>
															<td class="textonormal" bgcolor="#F7F7F7"><?=$itemObservacao?>&nbsp;</td>
														</tr>

																		<?php
                        /*
                         *
                         * if( file_exists($Arq) ){
                         * if( is_file($Arq)){
                         * $tamanho = filesize($Arq)/1024;
                         * $Objeto = urlencode($Objeto);
                         * echo "<a href=\"#\" onclick=\"AbreDocumentos('$Objeto', '$OrgaoLicitanteCodigo','$ComissaoCodigo','$ModalidadeCodigo','$GrupoCodigo','$LicitacaoProcesso','$LicitacaoAno','$itemCodigo');\" class=\"textonormal\"><img src=\"../midia/disquete.gif\" border=\"0\" alt=\"\"> $itemNome</a> - ";
                         * printf("%01.1f",$tamanho);
                         * echo " k";
                         * if( $itemObservacao != "" ){ echo " - $itemObservacao"; }
                         *
                         * }
                         * }else{
                         * //echo "<br><font class=\"textonegrito\">O arquivo $Arq não existe</font><br>&nbsp;\n";
                         * echo "<img src=\"../midia/disquete.gif\" border=\"0\"> $itemNome - <b>Arquivo não armazenado</b>";
                         * }
                         *
                         * echo "<br>\n";
                         */
                    }
                    ?>
																</table>
																<?php
                } else {
                    echo "<td><br><font class=\"textonegrito\">Nenhum Documento Relacionado!</font><br>&nbsp;\n</td>";
                }
            }
            ?>
										</tr>
												<input type="hidden" name="Objetox" value="<?echo $Objeto?>">
												<input type="hidden" name="OrgaoLicitanteCodigo"
													value="<?echo $OrgaoLicitanteCodigo?>">
												<input type="hidden" name="ComissaoCodigo"
													value="<?echo $ComissaoCodigo?>">
												<input type="hidden" name="ModalidadeCodigo"
													value="<?echo $ModalidadeCodigo?>">
												<input type="hidden" name="Botao" value="">
												<input type="hidden" name="GrupoCodigo"
													value="<?=$GrupoCodigo;?>">
												<input type="hidden" name="LicitacaoProcesso"
													value="<?=$LicitacaoProcesso;?>">
												<input type="hidden" name="LicitacaoAno"
													value="<?=$LicitacaoAno;?>">
												<input type="hidden" name="DocumentoCodigo"
													value="<?=$DocumentoCodigo;?>">

												</form>
												<tr>
													<form method="post" action="ConsAvisosPesquisar.php">
														<td class="textonormal" colspan="4" align="right"><input
															type="submit" name="Voltar" value="Voltar" class="botao">
														</td>
													</form>
												</tr>







											</table> <!--/form-->
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

</body>
</html>
