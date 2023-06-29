<?php
// -------------------------------------------------------------------------
// Portal da DGCO
// Programa: ConsAcompDetalhesDocumentosResultadoProcessoLicitatorio.php
// Autor: Pitang
// Data: 29/08/14
// Objetivo: [CR123143]: REDMINE 19 (P6)
// -------------------------------------------------------------------------
// Alterado: Pitang Agile TI
// Data:     30/06/2015
// Objetivo: CR Redmine 76836 - Licitações concluidas
// Link:     http://redmine.recife.pe.gov.br/issues/76836
// -------------------------------------------------------------------------
// Acesso ao arquivo de funções #
include 'funcoesLicitacoes.php';
require_once '../compras/funcoesCompras.php';

// Executa o controle de segurança #
session_start();
Seguranca();

$Selecao = $_SESSION['Selecao'];
$GrupoCodigo = $_SESSION['GrupoCodigoDet'];
$Processo = $_SESSION['ProcessoDet'];
$ProcessoAno = $_SESSION['ProcessoAnoDet'];
$ComissaoCodigo = $_SESSION['ComissaoCodigoDet'];
$OrgaoLicitanteCodigo = $_SESSION['OrgaoLicitanteCodigoDet'];
$Lote = $_SESSION['Lote'];
$Ordem = $_SESSION['Ordem'];

$_SESSION['PermitirAuditoria'] = 'N'; // Variável de sessão que permite fazer download de arquivos excluídos e armazenados.

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$Processo = filter_input(INPUT_GET, 'processo');
$ProcessoAno = filter_input(INPUT_GET, 'ano');
$ComissaoCodigo = filter_input(INPUT_GET, 'comissao');
$GrupoCodigo = filter_input(INPUT_GET, 'grupo');
$OrgaoLicitanteCodigo = filter_input(INPUT_GET, 'orgaoLicitante');
?>

<html>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body>
	<table border="0" cellspacing="0" cellpadding="3" bgcolor="#FFFFFF">
		<tbody>
			<tr>
				<td class="textonormal">
					<table border="1" cellpadding="3" cellspacing="0"
						bordercolor="#75ADE6" summary="" class="textonormal">
						<tbody>
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" colspan="5"
									class="titulo3">RESULTADO DO PROCESSO LICITATÓRIO</td>
							</tr>

							<tr>
								<td class="textonormal" colspan="4"
									style="padding: 0; border: 0px;">
									<table border="1" cellpadding="3" cellspacing="0"
										bordercolor="#75ADE6" summary="" class="textonormal"
										style="width: 100%; border: 1px;">
										<tbody>
                                                <?php
                                                // Pega as atas da fase de homologação da licitação #
                                                $sql = 'SELECT A.EFASESDESC, A.AFASESORDE, B.CLICPOPROC, B.ALICPOANOP, ';
                                                $sql .= '       B.CFASESCODI, B.EFASELDETA, B.TFASELDATA, C.CATASFCODI, ';
                                                $sql .= '       C.EATASFNOME, C.eatasfobse, C.fatasfexcl, U.EUSUPORESP, C.TATASFULAT';
                                                $sql .= '  FROM SFPC.TBFASES A, SFPC.TBFASELICITACAO B LEFT OUTER JOIN SFPC.TBATASFASE C ';
                                                $sql .= '    ON B.CLICPOPROC = C.CLICPOPROC AND B.ALICPOANOP = C.ALICPOANOP ';
                                                $sql .= '   AND B.CCOMLICODI = C.CCOMLICODI AND B.CGREMPCODI = C.CGREMPCODI ';
                                                $sql .= '   AND B.CORGLICODI = C.CORGLICODI AND B.CFASESCODI = C.CFASESCODI ';
                                                $sql .= ' 	    LEFT OUTER JOIN SFPC.TBUSUARIOPORTAL U ON C.CUSUPOCODI = U.CUSUPOCODI';
                                                $sql .= " WHERE B.CLICPOPROC = $Processo AND B.ALICPOANOP = $ProcessoAno ";
                                                $sql .= "   AND B.CCOMLICODI = $ComissaoCodigo AND B.CGREMPCODI = $GrupoCodigo ";
                                                $sql .= '   AND B.CFASESCODI = A.CFASESCODI AND A.CFASESCODI = 13 '; // Apenas fase de homologação
                                                $sql .= ' ORDER BY A.AFASESORDE';

                                                $db = Conexao();
                                                $result = $db->query($sql);

                                                if (PEAR::isError($result)) {
                                                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                } else {
                                                    $Rows = $result->numRows();

                                                    while ($cols = $result->fetchRow()) {
                                                        ++$cont;
                                                        $dados[$cont - 1] = "$cols[8];$cols[11];$cols[9];$cols[4];$cols[7];$cols[10]";
                                                    }

                                                    // Mostra os Documentos relacionados com a Licitação #
                                                    if ($Rows > 0) {
                                                        ?>

                                                <tr>
												<td valign="top" bgcolor="#F7F7F7" class="textonegrito">&nbsp;</td>
												<td valign="top" bgcolor="#F7F7F7" class="textonegrito">DOCUMENTO</td>
												<td valign="top" bgcolor="#F7F7F7" class="textonegrito">TAMANHO</td>
												<td valign="top" bgcolor="#F7F7F7" class="textonegrito">AUTOR</td>
												<td valign="top" bgcolor="#F7F7F7" class="textonegrito">OBSERVAÇÃO/<br>JUSTIFICATIVA
												</td>
											</tr>

    											<?php
                                                for ($Row = 0; $Row < $Rows; ++$Row) {
                                                    $Linha = explode(';', $dados[$Row]);

                                                    $nomeAta = $Linha[0];
                                                    $itemAutor = $Linha[1];
                                                    $itemObservacao = $Linha[2].'&nbsp;';
                                                    $FaseCodigo = $Linha[3];
                                                    $codAta = $Linha[4];
                                                    $itemExcluido = $Linha[5];

                                                    $ArqUpload = 'licitacoes/'.'ATASFASE'.$GrupoCodigo.'_'.$Processo.'_'.$ProcessoAno.'_'.$ComissaoCodigo.'_'.$OrgaoLicitanteCodigo.'_'.$FaseCodigo.'_'.$codAta;
                                                    $Arquivo = $GLOBALS['CAMINHO_UPLOADS'].$ArqUpload;
                                                    addArquivoAcesso($ArqUpload);

                                                    if ($itemExcluido == 'S') {
                                                        $itemNome = "<s><font color=\"#000000\"> $nomeAta </font></s><b>(excluído)</b>";
                                                    } elseif (file_exists($Arquivo)) {
                                                        $tamanho = filesize($Arquivo) / 1024;
                                                        $Url = "ConsAcompDownloadAtas.php?GrupoCodigo=$GrupoCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&FaseCodigo=$FaseCodigo&AtaCodigo=$codAta";

                                                        if (!in_array($Url, $_SESSION['GetUrl'])) {
                                                            $_SESSION['GetUrl'][] = $Url;
                                                        }

                                                        $itemNome = "<a href='$Url'><font color='#000000'> $nomeAta </font></a>";
                                                    } else {
                                                        $itemNome = "<font color=\"#000000\"> $nomeAta </font><b>(arquivo não armazenado)</b>";
                                                    }
                                                    //

                                                    // Autor e observação de documentos de antes da melhoria não devem ser mostrados
                                                    if ($itemDataAlteracao < '2011-03-23') {
                                                        $itemAutor = '---';
                                                        $itemObservacao = '---';
                                                    }
                                                    ?>

                                            <tr>
												<td valign="top" bgcolor="#F7F7F7" class="textonegrito">
                                                    <?php

                                                    if (file_exists($Arquivo) and $itemExcluido != 'S') {
                                                        ?>
                                                    <a href="<?=$Url?>" target="_blank" class="textonormal">
                                                        <img src="../midia/disquete.gif" border="0"></a>
                                                    <?php

                                                    } else {
                                                        ?>
                                                        <img src="../midia/disqueteInexistente.gif" border="0">
                                                    <?php

                                                    }

                                                    ?>
                                                </td>
												<td valign="top" bgcolor="#F7F7F7" class="textonormal"><?=$itemNome?></td>
												<td valign="top" bgcolor="#F7F7F7" class="textonormal">
        										<?php
                                                if (file_exists($Arquivo)) {
                                                    echo intval($tamanho).' Kbytes';
                                                } else {
                                                    echo '&nbsp;';
                                                }
                                                    ?>
        													</td>
												<td valign="top" bgcolor="#F7F7F7" class="textonormal"><?=$itemAutor?></td>
												<td valign="top" bgcolor="#F7F7F7" class="textonormal"><?=$itemObservacao?></td>
											</tr>
                                        <?php

                                                }
                                                    } else {
                                                        echo '<tr>
                                                                  <td valign="top" bgcolor="#F7F7F7" class="textonegrito">
                                                                      <font class=\"textonegrito\">Nenhum Documento Relacionado!</font>
                                                                  </td>
                                                              </tr>';
                                                    }
                                                }
                                        ?>

                                            </tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<tr>
				<td><a href="#" onclick="javascript:window.close()"> <input
						type="button" name="Voltar" value="Voltar" class="botao"
						style="float: right;">
				</a></td>
			</tr>
		</tbody>
	</table>
</body>
</html>
<?php $db->disconnect(); ?>
