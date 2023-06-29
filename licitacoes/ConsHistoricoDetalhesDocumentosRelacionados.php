<?php
    #-------------------------------------------------------------------------
    # Portal da DGCO
    # Programa: ConsHistoricoDetalhesDocumentosRelacionados.php
    # Autor:    Pitang
    # Data:     18/07/14
    # Objetivo: Programa quem exibe os documentos relacionados da licitação na tela de histórico
	#-------------------------------------------------------------------------
	# Alterado:	Pitang
	# Data:		19/08/2014 - Adiciona botão voltar
    #-------------------------------------------------------------------------

    # Acesso ao arquivo de funções #
    include "funcoesLicitacoes.php";
    require_once("../compras/funcoesCompras.php");

    # Executa o controle de segurança #
    session_start();
    Seguranca();
    
    $Selecao              = $_SESSION['Selecao'];
    $GrupoCodigo          = $_SESSION['GrupoCodigoDet'];
    $Processo             = $_SESSION['ProcessoDet'];
    $ProcessoAno          = $_SESSION['ProcessoAnoDet'];
    $ComissaoCodigo       = $_SESSION['ComissaoCodigoDet'];
    $OrgaoLicitanteCodigo = $_SESSION['OrgaoLicitanteCodigoDet'];
    $Lote                 = $_SESSION['Lote'];
    $Ordem                = $_SESSION['Ordem'];
    
    $_SESSION['PermitirAuditoria'] = 'N'; //Variável de sessão que permite fazer download de arquivos excluídos e armazenados.

    # Identifica o Programa para Erro de Banco de Dados #
    $ErroPrograma = __FILE__;

    $Processo = filter_input(INPUT_GET, 'processo');
    $ProcessoAno = filter_input(INPUT_GET, 'ano');
    $ComissaoCodigo = filter_input(INPUT_GET, 'comissao');
    $GrupoCodigo = filter_input(INPUT_GET, 'grupo');
?>

<html>
    <link rel="stylesheet" type="text/css" href="../estilo.css">
    <body>
        <table border="0" cellspacing="0" cellpadding="3" bgcolor="#FFFFFF">
            <tbody>
                <tr>
                    <td class="textonormal">                    	                    	
                        <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
                            <tbody>
                                <tr>
                                    <td align="center" bgcolor="#75ADE6" valign="middle" colspan="5" class="titulo3">
                                        DOCUMENTOS RELACIONADOS
                                    </td>
                                </tr>
                                        
                                <tr>
                                    <td class="textonormal" colspan="4" style="padding: 0; border:0px;">
                                        <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" style="width:100%;  border:1px;">
                                            <tbody>
                                                <?php
                                                    # Pega os documentos da Licitação #
                                                    $sql  = "SELECT CDOCLICODI, EDOCLINOME, EDOCLIOBSE, FDOCLIEXCL, U.EUSUPORESP, tdocliulat ";
                                                    $sql .= "  FROM SFPC.TBDOCUMENTOLICITACAO D, SFPC.TBUSUARIOPORTAL U";
                                                    $sql .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
                                                    $sql .= "   AND CCOMLICODI = $ComissaoCodigo AND D.CGREMPCODI = $GrupoCodigo AND D.CUSUPOCODI = U.CUSUPOCODI";
                                                    
                                                    # Exibir as planilhas ORCAMENTO_9999_99_99_99_9999.XLS <ANO+CODORGÃO+CODUNIDADE+CODCOMISSAO+CODPROCESSO>
                                                    # e RESULTADO_9999_99_99_99_9999.XLS <ANO+CODORGÃO+CODUNIDADE+CODCOMISSAO+CODPROCESSO> APENAS
                                                    # para os usuários que possuem os perfis COMISSAO LICITACAO (7) ou COMISS LICITACAO-REQUISITANTE (18)
                                                    # VER ALTERAÇÃO: 01/09/2010 - CR: 5210
                                                    #Em caso de dúvidas na expressão regular consultar o seguinte site:
                                                    #http://www.postgresql.org/docs/8.1/interactive/functions-matching.html#FUNCTIONS-POSIX-REGEXP
                                                    
                                                    if ($_SESSION['_cperficodi_'] == null or ($_SESSION['_cperficodi_'] != 7 and $_SESSION['_cperficodi_'] != 18)) {
                                                        $sql .= " AND ( NOT ( (edoclinome ~* '^RESULTADO_') OR (edoclinome ~* '^ORCAMENTO_') ) 	) ";
                                                    }
                                                    
                                                    $db     = Conexao();
                                                    $result = $db->query($sql);
                                                     
                                                    if (PEAR::isError($result)) {
                                                        ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                    } else {
                                                        $Rows = $result->numRows();
                                                    
                                                        while ($cols = $result->fetchRow()) {
                                                            $cont++;
                                                            $dados[$cont-1] = "$cols[0];$cols[1];$cols[2];$cols[3];$cols[4];$cols[5]";
                                                        }
                                                    
                                                        # Mostra os Documentos relacionados com a Licitação #
                                                        if ($Rows > 0) {
                                                ?>
                                            
                                                <tr>
                                                    <td valign="top" bgcolor="#F7F7F7" class="textonegrito">&nbsp;</td>
    												<td valign="top" bgcolor="#F7F7F7" class="textonegrito">DOCUMENTO</td>
    												<td valign="top" bgcolor="#F7F7F7" class="textonegrito">TAMANHO</td>
    												<td valign="top" bgcolor="#F7F7F7" class="textonegrito">RESPONSÁVEL</td>
    												<td valign="top" bgcolor="#F7F7F7" class="textonegrito">OBSERVAÇÃO/<br>JUSTIFICATIVA</td>
    											</tr>
    											
    											<?php
                                                    for ($Row = 0 ; $Row < $Rows ; $Row++) {
                                                        $Linha = explode(";",$dados[$Row]);
														$ArqUpload="licitacoes/"."DOC".$GrupoCodigo."_".$Processo."_".$ProcessoAno."_".$ComissaoCodigo."_".$OrgaoLicitanteCodigo."_".$Linha[0];
														$Arq = $GLOBALS["CAMINHO_UPLOADS"].$ArqUpload;
														addArquivoAcesso($ArqUpload);
														$itemNome = $Linha[1];
														$itemObservacao = $Linha[2]."&nbsp;";
														$itemExcluido = $Linha[3];
														$itemAutor = $Linha[4];
														$itemDataAlteracao = $Linha[5];
														
														if (file_exists($Arq)) {
                                                            $tamanho = filesize($Arq)/1024;
                                                            $Url = "ConsAcompDownloadDoc.php?GrupoCodigo=$GrupoCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&DocCodigo=$Linha[0]";
														}
														
														if ($itemExcluido == "S") {
															$itemNome = "<s style='text-decoration:line-through;'>".$itemNome."</s> <b>(excluído)</b>";
														} else if ( ! file_exists($Arq)) {
															$itemNome = "".$itemNome." <b>(arquivo não armazenado)</b>";
														} else {
															$itemNome = "<a href='".$Url."' target='_blank' class='textonormal'>".$itemNome."</a>";
														}
														
														# Autor e observação de documentos de antes da melhoria não devem ser mostrados
														if ($itemDataAlteracao < "2011-03-23") {
															$itemAutor="---";
															$itemObservacao="---";
														}
                                                ?>
											                                                        
                                                        <tr>
                                                            <td valign="top" bgcolor="#F7F7F7" class="textonegrito">
                                                                <?php if (file_exists($Arq) and $itemExcluido != "S") { ?>
                                                                    <a href="<?=$Url?>" target="_blank" class="textonormal"><img src="../midia/disquete.gif" border="0"></a>
                                                                <?php } else { ?>
                                                                    <img src="../midia/disqueteInexistente.gif" border="0">
                                                                <?php } ?>
                                                            </td>
                                                            <td valign="top" bgcolor="#F7F7F7" class="textonormal"><?=$itemNome?></td>
        													<td valign="top" bgcolor="#F7F7F7" class="textonormal">
        														<?php
        															if (file_exists($Arq)) {
                                                                        echo printf("%01.1f",$tamanho);
        															} else {
        																echo "&nbsp;";
        															}
        														?>
        													</td>
        													<td valign="top" bgcolor="#F7F7F7" class="textonormal"><?=$itemAutor?></td>
        													<td valign="top" bgcolor="#F7F7F7" class="textonormal"><?=$itemObservacao?></td>
                                                        </tr>
                                                
                                                <?php	
                                                    } // endfor
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
                <td>
                    <a href="#" onclick="javascript:window.close()">
                        <input type="button" name="Voltar" value="Voltar" class="botao" style="float: right;">
                    </a>
                </td>
            </tr>
            </tbody>
        </table>
    </body>
</html>
<?php $db->disconnect(); ?>