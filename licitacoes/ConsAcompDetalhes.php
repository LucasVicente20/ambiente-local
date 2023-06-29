<?php
    #-------------------------------------------------------------------------
    # Portal da DGCO
    # Programa: ConsAcompDetalhes.php
    # Autor:    Rossana Lira
    # Data:     06/05/03
    # Objetivo: Programa de Detalhamento (Acompanhamento) da Licitação
    #-----------------------------------------
    # Alterado: Rossana
    # Data:     24/05/2007 - Liberar Permissão Remunerada de Uso para Tomada de Preços
    # Alterado: Carlos Abreu
    # Data:     07/06/2007 - troca de variaveis get para session
    # Alterado: Rodrigo Melo
    # Data:     21/11/2008 - Correção para permitir baixar os arquivos que estão na ATA DA FASE
    # Alterado: Rodrigo Melo
    # Data:     01/09/2010 - Alteração para permitir a visualização das planilhas RESULTADO_9999_99_99_99_9999.XLS e
    #                        ORCAMENTO_9999_99_99_99_9999.XLS APENAS para os usuários que possuem
    #                        os perfis COMISSAO LICITACAO (7) ou COMISS LICITACAO-REQUISITANTE (18)- CR: 5210.
    # Alterado: Ariston Cordeiro
    # Data:     02/03/2011 - Mostrar os Documentos e Atas marcados como excluídos
    # Alterado: Ariston Cordeiro
    # Data:     23/03/2011 - não mostrar responsáveis e observações de documentos alterados antes da data em que a melhoria foi colocada

    # Alterado: Heraldo Botelho
    # Data:     09/11/2011 - Exibir Grids de Itens de Materiais e Serviços quando houver

    # Alterado: Heraldo Botelho
    # Data:     23/04/2013 - O Valor Estimado  (na variável=>$ValorEstimado) passa ser
    #           calculado pela função totalValorEstimado([params])

    # Alterado: Pitang Agile IT
    # Data:     29/08/2014 - [CR123143]: REDMINE 19 (P6)
    #
    # Alterado: Pitang Agile IT
    # Data:     17/09/2014 - [CR123143]: REDMINE 19 (P6)
    #
    # Alterado: Pitang Agile IT
    # Data:     14/11/2014 - CR referente a coluna "Valor Estimado" (que foi retirada na tela de companhamento em produção)
    #
    # Alterado: Pitang Agile IT
    # Data:     25/05/2015
    # Objetivo: [CR remine 74235] Checar por que versão de produção "deixou" de ter as CRs redmine 22 e 23
    # Versão:   v1.16.1-74-g93b87c3
    #-------------------------------------------------------------------------
    # OBS.:     só serão exibidos os processos licitatórios
    #           com situação ativa (tem que ter a fase de publicação ou o
    #						flag de situação ativa
    #-------------------------------------------------------------------------

    # Acesso ao arquivo de funções #
    include "funcoesLicitacoes.php";
    require_once "../compras/funcoesCompras.php";

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

    resetArquivoAcesso();

    AddMenuAcesso('/licitacoes/ConsAcompDownloadDoc.php');
    AddMenuAcesso('/licitacoes/ConsAcompDownloadAtas.php');
    AddMenuAcesso('/licitacoes/ConsAcompDetalhesDocumentosRelacionados.php');
    AddMenuAcesso('/licitacoes/ConsAcompDetalhesDocumentosResultadoProcessoLicitatorio.php');

    $fasesComResultado = array(13, 15); // Fases que podem ter resultado
?>

<html>
    <?php
        # Carrega o layout padrão #
        layout();
    ?>

    <script language="javascript">
        <!--
        <?php MenuAcesso(); ?>
        //-->
    </script>

    <link rel="stylesheet" type="text/css" href="../estilo.css">
    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
        <script language="JavaScript" src="../menu.js"></script>
        <script language="JavaScript">Init();</script>
        <form action="ConsAcompanhamentoPesquisar.php" method="post" name="Acompanhamento">
            <br><br><br><br>
            <table cellpadding="3" border="0">
                <!-- Caminho -->
                <tr>
                    <td width="100"><img border="0" src="../midia/linha.gif"></td>
                    <td align="left" class="textonormal" colspan="5"><br>
                        <font class="titulo2">|</font>
                        <a href="../index.php"><font color="#000000">Página Principal</font></a> > Licitações > Acompanhamento
            		</td>
                </tr>
                <!-- Fim do Caminho-->

            	<!-- Erro -->
            	<?php if ($Mens == 1) {
    ?>
            	<tr>
                    <td width="100"></td>
                    <td align="left" colspan="5"><?php if ($Mens == 1) {
                        ExibeMens($Mensagem, $Tipo, 1);
}
    ?></td>
            	</tr>
            	<?php

} ?>
            	<!-- Fim do Erro -->

                <!-- Corpo -->
                <tr>
                    <td width="100"></td>
                    <td class="textonormal">
                        <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#FFFFFF">
                            <tr>
	      	                    <td class="textonormal">
                                    <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal">
                                        <tr>
                                            <td align="center" bgcolor="#75ADE6" valign="middle" colspan="5" class="titulo3">
                                                ACOMPANHAMENTO DE LICITAÇÕES - DETALHAMENTO
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textonormal" colspan="5">
                                                <p align="justify">
                                                    Para visualizar os documentos e Atas da Licitação, clique no item desejado. Para visualizar todas as Licitações Pesquisadas, clique no botão "Voltar".
                                                </p>
                                            </td>
                                        </tr>
                                        <tr>

                                        <?php
                                            # Resgata as informações da licitação #
                                            /*
                                            $db     = Conexao();
                                            $sql    = "SELECT A.EGREMPDESC, B.EMODLIDESC, C.ECOMLIDESC, D.XLICPOOBJE, ";
                                            $sql   .= "       E.EORGLIDESC, D.TLICPODHAB, D.CLICPOCODL, D.ALICPOANOP, ";
                                            $sql   .= "       D.FLICPOREGP, B.CMODLICODI, D.VLICPOVALE, D.VLICPOVALH, ";
                                            $sql   .= "       D.VLICPOTGES, D.flicpovfor ";
                                            $sql   .= "  FROM SFPC.TBGRUPOEMPRESA A, SFPC.TBMODALIDADELICITACAO B, SFPC.TBCOMISSAOLICITACAO C, ";
                                            $sql   .= "       SFPC.TBLICITACAOPORTAL D, SFPC.TBORGAOLICITANTE E ";
                                            $sql   .= " WHERE A.CGREMPCODI = D.CGREMPCODI AND D.CGREMPCODI = $GrupoCodigo ";
                                            $sql   .= "   AND D.CMODLICODI = B.CMODLICODI AND C.CCOMLICODI = D.CCOMLICODI ";
                                            $sql   .= "   AND D.CCOMLICODI = $ComissaoCodigo AND D.ALICPOANOP = $ProcessoAno ";
                                            $sql   .= "   AND D.CLICPOPROC = $Processo AND E.CORGLICODI = D.CORGLICODI ";
                                            $sql   .= "   AND D.CORGLICODI = $OrgaoLicitanteCodigo";
                                            $result = $db->query($sql);
                                            */

                                            $db     = Conexao();
                                            $sql    = "SELECT A.EGREMPDESC, B.EMODLIDESC, C.ECOMLIDESC, D.XLICPOOBJE, ";
                                            $sql   .= "       E.EORGLIDESC, D.TLICPODHAB, D.CLICPOCODL, D.ALICPOANOP, ";
                                            $sql   .= "       D.FLICPOREGP, B.CMODLICODI, D.VLICPOVALE, D.VLICPOVALH, ";
                                            $sql   .= "       D.VLICPOTGES, D.flicpovfor, D.flicporesu ";
                                            $sql   .= "  FROM SFPC.TBGRUPOEMPRESA A, SFPC.TBMODALIDADELICITACAO B, SFPC.TBCOMISSAOLICITACAO C, ";
                                            $sql   .= "       SFPC.TBLICITACAOPORTAL D, SFPC.TBORGAOLICITANTE E ";
                                            $sql   .= " WHERE A.CGREMPCODI = D.CGREMPCODI AND D.CGREMPCODI = $GrupoCodigo ";
                                            $sql   .= "   AND D.CMODLICODI = B.CMODLICODI AND C.CCOMLICODI = D.CCOMLICODI ";
                                            $sql   .= "   AND D.CCOMLICODI = $ComissaoCodigo AND D.ALICPOANOP = $ProcessoAno ";
                                            $sql   .= "   AND D.CLICPOPROC = $Processo AND E.CORGLICODI = D.CORGLICODI ";
                                            $sql   .= "   AND D.CORGLICODI = $OrgaoLicitanteCodigo";
                                            $result = $db->query($sql);

                                        if (PEAR::isError($result)) {
                                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                        } else {
                                            $Rows = $result->numRows();
                                            while ($Linha = $result->fetchRow()) {
                                                $GrupoDesc             = $Linha[0];
                                                $ModalidadeDesc        = $Linha[1];
                                                $ComissaoDesc          = $Linha[2];
                                                $OrgaoLicitacao        = $Linha[4];
                                                $ObjetoLicitacao       = $Linha[3];
                                                $Licitacao             = substr($Linha[6] + 10000, 1);
                                                $AnoLicitacao          = $Linha[7];
                                                $LicitacaoDtAbertura   = substr($Linha[5], 8, 2)."/".substr($Linha[5], 5, 2)."/".substr($Linha[5], 0, 4);
                                                $LicitacaoHoraAbertura = substr($Linha[5], 11, 5);

                                                $flagResultadoLicitacao = $Linha[14];
                                                $utmFaseLicitacao = ultimaFase($Processo, $ProcessoAno, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $db);

                                                if ($Linha[8] == "S") {
                                                    $RegistroPreco         = "SIM";
                                                } else {
                                                    $RegistroPreco         = "NÃO";
                                                }

                                                $ModalidadeCodigo = $Linha[9];
                                                $ValorEstimado = totalValorEstimado($db, $Processo, $ProcessoAno, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo);

                                                if (empty($ValorEstimado)) {
                                                    $ValorEstimado = "0,00";
                                                } else {
                                                    $ValorEstimado = converte_valor($ValorEstimado);
                                                }

                                                $ValorHomologado       = converte_valor($Linha[11]);
                                                $TotalGeralEstimado    = converte_valor($Linha[12]);

                                                if ($Linha[13] == "S") {
                                                    $validacaoFornecedor         = "SIM";
                                                } else {
                                                    $validacaoFornecedor         = "NÃO";
                                                }
                                            }
                                        }

                                            echo "			<td class=\"textonegrito\" bgcolor=\"#DCEDF7\" colspan=\"5\">\n";
                                            echo "$GrupoDesc<br><br>$ModalidadeDesc<br><br>$ComissaoDesc<br>";
                                            echo "			</td>\n";

                                            $Processo = substr($Processo+10000, 1);

                                            echo "			<tr>\n";
                                            echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">PROCESSO</td>\n";
                                            echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$Processo/$ProcessoAno</td>\n";
                                            echo "			</tr>\n";
                                            echo "			<tr>\n";
                                            echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">LICITAÇÃO</td>\n";
                                            echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$Licitacao/$AnoLicitacao</td>\n";
                                            echo "			</tr>\n";
                                            echo "			<tr>\n";
                                            echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">REGISTRO DE PREÇO";

                                            # Caso a modalidade seja concorrência ou tomada de preços apareça nome Permissão Remunerada de Uso
                                        if ($ModalidadeCodigo == 3 or $ModalidadeCodigo == 2) {
                                            echo "/PERMISSÃO REMUNERADA DE USO";
                                        }

                                            echo "				</td>\n";
                                            echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$RegistroPreco</td>\n";
                                            echo "			</tr>\n";
                                            echo "			<tr>\n";
                                            echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">OBJETO</td>\n";
                                            echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$ObjetoLicitacao</td>\n";
                                            echo "			</tr>\n";
                                            echo "			<tr>\n";
                                            echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">DATA/HORA DE ABERTURA</td>\n";
                                            echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$LicitacaoDtAbertura $LicitacaoHoraAbertura h</b></td>\n";
                                            echo "			</tr>\n";
                                            echo "			<tr>\n";
                                            echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">ÓRGÃO LICITANTE</td>\n";
                                            echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$OrgaoLicitacao</td>\n";
                                            echo "			</tr>\n";

                                            echo "			<tr>\n";
                                            echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">NECESSIDADE DE APRESENTAÇÃO DE DEMONSTRAÇÕES CONTÁBEIS</td>\n";
                                            echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$validacaoFornecedor</td>\n";
                                            echo "			</tr>\n";

                                        if ($flagResultadoLicitacao and in_array($utmFaseLicitacao, $fasesComResultado)) {
                                            if ($ValorHomologado != "0,00") {
                                                if ($ValorEstimado == "") {
                                                    $ValorEstimado = "NÃO INFORMADO";
                                                }

                                                echo "			<tr>\n";
                                                echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">VALOR ESTIMADO</td>\n";
                                                echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$ValorEstimado</td>\n";
                                                echo "			</tr>\n";
                                            }

                                            if ($TotalGeralEstimado != "0,00") {
                                                echo "			<tr>\n";
                                                echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">TOTAL GERAL ESTIMADO<br>(Itens que Lograram Êxito)</td>\n";
                                                echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$TotalGeralEstimado</td>\n";
                                                echo "			</tr>\n";
                                            }

                                            if ($ValorHomologado != "0,00") {
                                                echo "			<tr>\n";
                                                echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\" colspan=\"2\">VALOR HOMOLOGADO<br>(Itens que Lograram Êxito)</td>\n";
                                                echo "				<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"2\">$ValorHomologado</td>\n";
                                                echo "			</tr>\n";
                                            }
                                        }

                                            # Pega os Dados dos do Bloqueio de uma licitação sem SCC #
                                            $sql    = "SELECT TUNIDOEXER, CUNIDOORGA, CUNIDOCODI, ALICBLSEQU, ";
                                            $sql   .= "       CLICBLFUNC, CLICBLSUBF, CLICBLPROG, CLICBLTIPA, ";
                                            $sql   .= "       ALICBLORDT, CLICBLELE1, CLICBLELE2, CLICBLELE3, ";
                                            $sql   .= "       CLICBLELE4, CLICBLFONT ";
                                            $sql   .= "  FROM SFPC.TBLICITACAOBLOQUEIOORCAMENT";
                                            $sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
                                            $sql   .= "   AND CCOMLICODI = $ComissaoCodigo ";
                                            $sql   .= "   AND CGREMPCODI = $GrupoCodigo";
                                            $sql   .= " ORDER BY ALICBLSEQU";

                                            $result = $db->query($sql);
                                        if (PEAR::isError($result)) {
                                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                        } else {
                                            $Rows = $result->numRows();
                                            for ($i = 0; $i < $Rows; $i++) {
                                                $Linha             = $result->fetchRow();
                                                $ExercicioBloq[$i] = $Linha[0];
                                                $Orgao[$i]         = $Linha[1];
                                                $Unidade[$i]       = $Linha[2];
                                                $Bloqueios[$i]     = $Linha[3];
                                                $Funcao[$i]        = $Linha[4];
                                                $Subfuncao[$i]     = $Linha[5];
                                                $Programa[$i]      = $Linha[6];
                                                $TipoProjAtiv[$i]  = $Linha[7];
                                                $ProjAtividade[$i] = $Linha[8];
                                                $Elemento1[$i]     = $Linha[9];
                                                $Elemento2[$i]     = $Linha[10];
                                                $Elemento3[$i]     = $Linha[11];
                                                $Elemento4[$i]     = $Linha[12];
                                                $Fonte[$i]         = $Linha[13];
                                                $Dotacao[$i]       = NumeroDotacao($Funcao[$i], $Subfuncao[$i], $Programa[$i], $Orgao[$i], $Unidade[$i], $TipoProjAtiv[$i], $ProjAtividade[$i], $Elemento1[$i], $Elemento2[$i], $Elemento3[$i], $Elemento4[$i], $Fonte[$i]);
                                            }
                                        }

                                            $dbOracle = ConexaoOracle();
                                            # Pega os Dados dos do Bloqueio de uma licitação com SCC #
                                            $sql    = "
            									select Distinct AITLBLNBLOQ, AITLBLANOB
            									from
            										sfpc.tbitemlicitacaobloqueio
            									WHERE
            										CLICPOPROC = $Processo
            										AND ALICPOANOP = $ProcessoAno
            										AND CCOMLICODI = $ComissaoCodigo
            										AND CGREMPCODI = $GrupoCodigo
            								";

                                            $result = executarSQL($db, $sql);
                                            $i = 0;

                                        while ($bloqueioChave = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
                                            $bloqueioAno = $bloqueioChave->aitlblanob;//AITLBLANOB;
                                            $bloqueioSequencial = $bloqueioChave->aitlblnbloq;//AITLBLNBLOQ;
                                            $bloqueioArray = getDadosBloqueioFromChave($dbOracle, $bloqueioAno, $bloqueioSequencial);

                                            $ExercicioBloq[$i] = $bloqueioArray['ano'];
                                            $Orgao[$i]         = $bloqueioArray['orgao'];
                                            $Unidade[$i]       = $bloqueioArray['unidade'];
                                            $Bloqueios[$i]     = $bloqueioArray['sequencial'];
                                            $Funcao[$i]        = $bloqueioArray['funcao'];
                                            $Subfuncao[$i]     = $bloqueioArray['subfuncao'];
                                            $Programa[$i]      = $bloqueioArray['programa'];
                                            $TipoProjAtiv[$i]  = $bloqueioArray['tipoProjetoAtividade'];
                                            $ProjAtividade[$i] = $bloqueioArray['projetoAtividade'];
                                            $Elemento1[$i]     = $bloqueioArray['elemento1'];
                                            $Elemento2[$i]     = $bloqueioArray['elemento2'];
                                            $Elemento3[$i]     = $bloqueioArray['elemento3'];
                                            $Elemento4[$i]     = $bloqueioArray['elemento4'];
                                            $Fonte[$i]         = $bloqueioArray['fonte'];
                                            $Dotacao[$i]       = NumeroDotacao($Funcao[$i], $Subfuncao[$i], $Programa[$i], $Orgao[$i], $Unidade[$i], $TipoProjAtiv[$i], $ProjAtividade[$i], $Elemento1[$i], $Elemento2[$i], $Elemento3[$i], $Elemento4[$i], $Fonte[$i]);
                                            $i++;
                                        }

                                            # Pega os Dados de dotação de uma licitação com SCC #
                                            $sql    = "
            									select distinct
            										aitldounidoexer, citldounidoorga, citldounidocodi, citldotipa, aitldoordt,
            										citldoele1, citldoele2, citldoele3, citldoele4, citldofont
            									from
            										sfpc.tbitemlicitacaodotacao
            									WHERE
            										CLICPOPROC = $Processo
            										AND ALICPOANOP = $ProcessoAno
            										AND CCOMLICODI = $ComissaoCodigo
            										AND CGREMPCODI = $GrupoCodigo
            								";

                                            $result = executarSQL($db, $sql);
                                            $i = 0;

                                        while ($bloqueioChave = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
                                            $dotacaoAno = $bloqueioChave->aitldounidoexer;
                                            $dotacaoOrgao = $bloqueioChave->citldounidoorga;
                                            $dotacaoUnidade = $bloqueioChave->citldounidocodi;
                                            $dotacaoTipoProjeto = $bloqueioChave->citldotipa;
                                            $dotacaoProjeto = $bloqueioChave->aitldoordt;
                                            $dotacaoE1 = $bloqueioChave->citldoele1;
                                            $dotacaoE2 = $bloqueioChave->citldoele2;
                                            $dotacaoE3 = $bloqueioChave->citldoele3;
                                            $dotacaoE4 = $bloqueioChave->citldoele4;
                                            $dotacaoFonte = $bloqueioChave->citldofont;

                                            $bloqueioArray = getDadosDotacaoOrcamentariaFromChave(
                                                $dbOracle,
                                                $dotacaoAno,
                                                $dotacaoOrgao,
                                                $dotacaoUnidade,
                                                $dotacaoTipoProjeto,
                                                $dotacaoProjeto,
                                                $dotacaoE1,
                                                $dotacaoE2,
                                                $dotacaoE3,
                                                $dotacaoE4,
                                                $dotacaoFonte
                                            );

                                            $ExercicioBloq[$i] = $dotacaoAno;
                                            $Orgao[$i]         = $dotacaoOrgao;
                                            $Unidade[$i]       = $dotacaoUnidade;
                                            $Bloqueios[$i]     = null;
                                            $Funcao[$i]        = null;
                                            $Subfuncao[$i]     = null;
                                            $Programa[$i]      = null;
                                            $TipoProjAtiv[$i]  = $dotacaoTipoProjeto;
                                            $ProjAtividade[$i] = $dotacaoProjeto;
                                            $Elemento1[$i]     = $dotacaoE1;
                                            $Elemento2[$i]     = $dotacaoE2;
                                            $Elemento3[$i]     = $dotacaoE3;
                                            $Elemento4[$i]     = $dotacaoE4;
                                            $Fonte[$i]         = $dotacaoFonte;
                                            $Dotacao[$i]       = $bloqueioArray["dotacao"];
                                            $i++;
                                        }

                                            echo "<tr>\n";
                                            echo "	<td class=\"textonegrito\" bgcolor=\"#DCEDF7\" colspan=\"5\">BLOQUEIOS</td>\n";
                                            echo "</tr>\n";

                                        if (count($Bloqueios) != 0) {
                                            echo "			<tr>\n";
                                            echo "				<td bgcolor=\"#F7F7F7\" class=\"textonegrito\">EXERCÍCIO</td>\n";
                                            echo "				<td bgcolor=\"#F7F7F7\" class=\"textonegrito\">No BLOQUEIO</td>\n";
                                            echo "				<td bgcolor=\"#F7F7F7\" class=\"textonegrito\">UNIDADE ORÇAMENTÁRIA</td>\n";
                                            echo "				<td bgcolor=\"#F7F7F7\" class=\"textonegrito\">DOTAÇÃO</td>\n";
                                            echo "			</tr>\n";

                                            for ($i = 0; $i< count($Bloqueios); $i++) {
                                                $isDotacao = false;

                                                if (is_null($Bloqueios[$i])) {
                                                    $isDotacao = true;
                                                }

                                                echo "			<tr>\n";
                                                echo "				<td class=\"textonormal\" bgcolor=\"#F7F7F7\">$ExercicioBloq[$i]</td>\n";
                                                echo "				<td class=\"textonormal\" bgcolor=\"#F7F7F7\">\n";

                                                if ($isDotacao) {
                                                    echo " (dotação) ";
                                                } else {
                                                    echo "					".$Orgao[$i].".".sprintf("%02d", $Unidade[$i]).".1.".$Bloqueios[$i]."\n";
                                                    echo "					<input type=\"hidden\" name=\"Bloqueios[$i]\" value=\"$Bloqueios[$i]\">\n";
                                                }

                                                echo "				</td>\n";
                                                echo "				<td class=\"textonormal\" bgcolor=\"#F7F7F7\">\n";

                                                # Busca a descrição da Unidade Orçamentaria #
                                                if ($_SERVER['SERVER_NAME'] != 'varzea.recife' and $_SERVER['SERVER_NAME'] != 'www.recife.pe.gov.br') {
                                                    if (empty($ExercicioBloq[$i])) {
                                                        $ExercicioBloq[$i] = '9999/99/99';
                                                    }
                                                    if (empty($Orgao[$i])) {
                                                        $Orgao[$i] = '9999';
                                                    }
                                                    if (empty($Unidade[$i])) {
                                                        $Unidade[$i] = '9999';
                                                    }
                                                }

                                                $sql    = "SELECT EUNIDODESC FROM SFPC.TBUNIDADEORCAMENTPORTAL ";
                                                $sql   .= " WHERE TUNIDOEXER = $ExercicioBloq[$i] AND CUNIDOORGA = $Orgao[$i] ";
                                                $sql   .= "   AND CUNIDOCODI = $Unidade[$i]";
                                                $result = $db->query($sql);

                                                if (PEAR::isError($result)) {
                                                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                                } else {
                                                    $Linha               = $result->fetchRow();
                                                    $UnidadeOrcament[$i] = $Linha[0];
                                                }

                                                echo "					$UnidadeOrcament[$i]\n";
                                                echo "				</td>\n";
                                                echo "				<td class=\"textonormal\" bgcolor=\"#F7F7F7\">\n";
                                                echo "					$Dotacao[$i]\n";
                                                echo "				</td>\n";
                                                echo "			</tr>\n";
                                            }
                                        } else {
                                            echo "<tr>\n";
                                            echo "	<td class=\"textonegrito\" colspan=\"5\">Nenhum Bloqueio Informado.</td>\n";
                                            echo "</tr>\n";
                                        }

                                              //--------------------------------------------
                                              // Verificar se Licitação tem resultado
                                              //---------------------------------------------
                                              $sql = " select flicporesu as resultado ";
                                              $sql .= " from sfpc.tblicitacaoportal ";
                                              $sql .= " where ";
                                              $sql .= " clicpoproc = $Processo";
                                            $sql .= " and alicpoanop = ".$ProcessoAno;
                                            $sql .= " and cgrempcodi = ".$GrupoCodigo;
                                            $sql .= " and ccomlicodi = ".$ComissaoCodigo;
                                            $sql .= " and corglicodi = ".$OrgaoLicitanteCodigo;

                                            $result    = executarTransacao($db, $sql);
                                            $row    = $result->fetchRow(DB_FETCHMODE_OBJECT);

                                            $licitacaoComResultado = false;
                                        if ($row->resultado == 'S') {
                                            $licitacaoComResultado = true;
                                        }

                                            //--------------------------------------------
                                            // Verificar ultim afase da licitação
                                            //---------------------------------------------
                                            $ultimaFase = ultimaFase($Processo, $ProcessoAno, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $db);
                                            $arraySituacoesConcluidas = getIdFasesConcluidas($db); // Array com os ids das situações concluídas

                                            //--------------------------------------------------------
                                            // Inserido por Heraldo
                                            // para exibir itens de materiais e de serviços
                                            //---------------------------------------------------------

                                            //--------------------------------------------------------
                                            // SQL para capturar os itens de material da licitação
                                            //---------------------------------------------------------
                                            $sql  = " select a.aitelporde, b.ematepdesc, a.cmatepsequ, c.eunidmdesc, a.aitelpqtso, a.citelpnuml, ";
                                             $sql .= " d.aforcrsequ, d.nforcrrazs, d.nforcrfant, d.aforcrccgc, a.eitelpdescmat, a.eitelpmarc, a.eitelpmode ";
                                             $sql .= " , a.vitelpunit, a.vitelpvlog ";
                                            $sql .= " from ";
                                            $sql .= " sfpc.tbitemlicitacaoportal a left join sfpc.tbfornecedorcredenciado d ";
                                             $sql .= " ON a.aforcrsequ = d.aforcrsequ, ";
                                            $sql .= " sfpc.tbmaterialportal b, sfpc.tbunidadedemedida c ";
                                            $sql .= " where ";
                                             $sql .= " a.cmatepsequ = b.cmatepsequ  ";
                                             $sql .= " and b.cunidmcodi = c.cunidmcodi  ";
                                             $sql .= " and  a.clicpoproc=".$Processo;
                                             $sql .= " and  a.alicpoanop=".$ProcessoAno;
                                             $sql .= " and a.cgrempcodi=".$GrupoCodigo;
                                            $sql .= " and a.ccomlicodi=".$ComissaoCodigo;
                                             $sql .= " and a.corglicodi=".$OrgaoLicitanteCodigo;
                                             $sql .= " order by 6,1 ";

                                             $resILTmp = $db->query($sql);
                                             $result = $db->query($sql);
                                            $Rows = $result->numRows();

                                             //------------------------------------------------------------
                                             //- Se encontrar pelo menos uma linha exibir grade com Itens
                                             //------------------------------------------------------------
                                        if ($Rows > 0) {
                                            echo "<tr  class=\"textonegrito\" bgcolor=\"#75ADE6\"   > ";
                                            echo "<td colspan=5 align=\"center\"   valign=\"middle\" >ITENS DE MATERIAIS DA LICITAÇÃO</td>";
                                            echo "</tr>";
                                            echo "<tr>";
                                            echo "<td colspan=5>";
                                            echo "<table width=\"100%\" border=\"1\" cellpadding=\"3\" cellspacing=\"0\" bordercolor=\"#75ADE6\" summary=\"\" class=\"textonormal\" style=\"width:100%;  border:1px;\"  >";
                                            echo "<tr class=\"textonegrito\" bgcolor=\"#DCEDF7\" >";
                                            echo "</tr>";

                                            $numLoteMatAntes = "999";
                                            $exibeTd = false;

                                            while ($arrI = $resILTmp->fetchRow()) {
                                                if (!empty($arrI[10])) {
                                                    $exibeTd = true;
                                                    break;
                                                }
                                            }

                                            while ($Linha = $result->fetchRow()) {
                                                $ordMaterial     = $Linha[0];
                                                $descMaterial    = $Linha[1];
                                                $seqMaterial     = $Linha[2];
                                                $unidMaterial    = $Linha[3];
                                                $qtdMaterial     = $Linha[4];
                                                $numLoteMat      = $Linha[5];
                                                $codForCredMat   = $Linha[6];
                                                $razaoSocForMat  = $Linha[7];
                                                $nomeFantForMat  = $Linha[8];
                                                $cgcForCredMat   = $Linha[9];
                                                $descDetalhadaMaterial   = $Linha[10];
                                                $marcaMaterial   = $Linha[11];
                                                $modeloMaterial   = $Linha[12];
                                                $valorEstimadoMaterial = $Linha[13];
                                                $valorHomologadoMaterial = $Linha[14];

                                                if ($numLoteMat != $numLoteMatAntes) {
                                                    $numLoteMatAntes = $numLoteMat;

                                                      //if ($licitacaoComResultado   and  $ultimaFase==13 and  !empty($razaoSocForMat)) {
                                                    if ($licitacaoComResultado and in_array($ultimaFase, $fasesComResultado) and  !empty($razaoSocForMat)) {
                                                        $soma =  getTotalValorLogrado($db, $Processo, $ProcessoAno, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $numLoteMat);

                                                        echo "<tr class=\"textonegrito\" bgcolor=\"#75ADE6\">";
                                                        echo "<td valign=top colspan=10> LOTE ".($numLoteMat)." FORNECEDOR VENCEDOR : ".FormataCpfCnpj($cgcForCredMat)." - ".($razaoSocForMat)." - "."R$ ".(number_format((float) $soma, 2, ",", "."))." </td>";
                                                        echo "</tr>";
                                                    } else {
                                                        echo "<tr class=\"textonegrito\" bgcolor=\"#75ADE6\">";
                                                        echo "<td valign=top colspan=10> LOTE ".($numLoteMat)." </td>";
                                                        echo "</tr>";
                                                    }

                                                    echo "<tr class=\"textonegrito\" bgcolor=\"#DCEDF7\" >";
                                                    echo "<td width=30px>ORD.</td><td >DESC. ITEM</td><td >CÓD</td><td>UNIDADE</td>";

                                                    if ($exibeTd) {
                                                        echo "<td>DESC. DETALHADA ITEM</td>";
                                                    }

                                                    echo "<td>QUANTIDADE</td>";

                                                    if ($licitacaoComResultado and in_array($ultimaFase, $fasesComResultado)) {
                                                        echo "<td>MARCA</td>";
                                                        echo "<td>MODELO</td>";
                                                        echo "<td>VALOR ESTIMADO</td>";
                                                        echo "<td>VALOR HOMOLOGADO</td>";
                                                    }

                                                    echo "</tr>";
                                                }

                                                echo "<td valign=top>".$ordMaterial."</td>";
                                                echo "<td valign=top>".($descMaterial)."</td>";
                                                echo "<td valign=top>".($seqMaterial)."</td>";
                                                echo "<td valign=top>".$unidMaterial."</td>";

                                                if ($exibeTd) {
                                                    echo "<td valign=top>".$descDetalhadaMaterial."</td>";
                                                }

                                                echo "<td valign=rigth   align=\"rigth\" > ".number_format($qtdMaterial, "4", ",", ".")."</td>";

                                                if ($licitacaoComResultado and in_array($ultimaFase, $fasesComResultado)) {
                                                    echo "<td valign=top>".$marcaMaterial."</td>";
                                                    echo "<td valign=top>".$modeloMaterial."</td>";
                                                    echo "<td valign=top>".number_format((float) $valorEstimadoMaterial, 2, ",", ".")."</td>";
                                                    echo "<td valign=top>".number_format((float) $valorHomologadoMaterial, 2, ",", ".")."</td>";
                                                }

                                                echo "</tr>";
                                            }

                                            echo "</table>";
                                            echo "</td>";
                                            echo "</tr>";
                                        }

                                            //--------------------------------------------------------
                                            // SQL para capturar os itens de serviço da licitação
                                            //---------------------------------------------------------
                                            $sql  = " select a.aitelporde, b.eservpdesc, a.cservpsequ, a.citelpnuml, c.aforcrsequ, ";
                                            $sql .= " c.nforcrrazs, c.nforcrfant, c.aforcrccgc, a.eitelpdescse ";
                                            $sql .= " , a.vitelpunit, a.vitelpvlog ";
                                            $sql .= " from sfpc.tbitemlicitacaoportal a left join sfpc.tbfornecedorcredenciado c ";
                                            $sql .= " ON a.aforcrsequ = c.aforcrsequ, ";
                                             $sql .= " sfpc.tbservicoportal b ";
                                              $sql .= " where ";
                                             $sql .= " a.cservpsequ = b.cservpsequ   ";
                                             $sql .= " and  a.clicpoproc=".$Processo;
                                             $sql .= " and  a.alicpoanop=".$ProcessoAno;
                                             $sql .= " and a.cgrempcodi=".$GrupoCodigo;
                                            $sql .= " and a.ccomlicodi=".$ComissaoCodigo;
                                             $sql .= " and a.corglicodi=".$OrgaoLicitanteCodigo;
                                             $sql .= " order by 4,1 ";

                                             $resultTemp = $db->query($sql);
                                             $result = $db->query($sql);
                                             $Rows = $result->numRows();

                                            //------------------------------------------------------------
                                            //- Se encontrar pelo menos uma linha exibir grade com Itens
                                            //------------------------------------------------------------
                                        if ($Rows > 0) {
                                            echo "<tr  class=\"textonegrito\" bgcolor=\"#75ADE6\"   > ";
                                            echo "<td colspan=5 align=\"center\"   valign=\"middle\" >ITENS DE SERVIÇO DA LICITAÇÃO</td>";
                                            echo "</tr>";
                                            echo "<tr>";
                                            echo "<td colspan=5>";
                                            echo "<table  border=\"1\" cellpadding=\"3\" cellspacing=\"0\" bordercolor=\"#75ADE6\" summary=\"\" class=\"textonormal\" style=\"width:100%;  border:1px;\">";
                                            echo "<tr class=\"textonegrito\" bgcolor=\"#DCEDF7\" >";
                                            echo "</tr>";

                                            $numLoteServAntes = "999";

                                            while ($Linha = $result->fetchRow()) {
                                                $ordServico       = $Linha[0];
                                                $descServico      = $Linha[1];
                                                $seqServico       = $Linha[2];
                                                $numLoteServico   = $Linha[3];
                                                $codForCredServ   = $Linha[4];
                                                $razaoSocForServ  = $Linha[5];
                                                $nomeFantFornServ = $Linha[6];
                                                $cgcForCredServ   = $Linha[7];
                                                $descDetalhadaServico = $Linha[8];
                                                $valorEstimadoItem = $Linha[9];
                                                $valorHomologadoItem = $Linha[10];

                                                if ($numLoteServico != $numLoteServAntes) {
                                                    $numLoteServAntes = $numLoteServico;

                                                    //if ($licitacaoComResultado and $ultimaFase==13 and !empty($razaoSocForServ)) {
                                                    if ($licitacaoComResultado and in_array($ultimaFase, $fasesComResultado) and  !empty($razaoSocForServ)) {
                                                        $soma =  getTotalValorServico($db, $Processo, $ProcessoAno, $GrupoCodigo, $ComissaoCodigo, $OrgaoLicitanteCodigo, $numLoteServico);

                                                        echo "<tr class=\"textonegrito\" bgcolor=\"#75ADE6\">";
                                                        echo "<td valign=top colspan=6> LOTE ".($numLoteServico)." FORNECEDOR VENCEDOR: ".FormataCpfCnpj($cgcForCredServ)." - ".($razaoSocForServ)." - "."R$ ".(number_format((float) $soma, 2, ",", "."))."</td>";
                                                        echo "</tr>";
                                                    } else {
                                                        echo "<tr class=\"textonegrito\" bgcolor=\"#75ADE6\">";
                                                        echo "<td valign=top colspan=6> LOTE ".($numLoteServico)."</td>";
                                                        echo "</tr>";
                                                    }

                                                    echo "<tr class=\"textonegrito\" bgcolor=\"#DCEDF7\" >";
                                                    echo "<td width=30px>ORD.</td>";
                                                    echo "<td >DESC. ITEM</td>";
                                                    echo "<td >DESC. DETALHADA ITEM</td>";
                                                    echo "<td>CÓD</td>";

                                                    if ($licitacaoComResultado and in_array($ultimaFase, $fasesComResultado)) {
                                                        echo "<td>VALOR ESTIMADO</td>";
                                                        echo "<td>VALOR HOMOLOGADO</td>";
                                                    }

                                                    echo "</tr>";
                                                }

                                                echo "<tr>";
                                                echo "<td valign=top>".($ordServico)."</td>";
                                                echo "<td valign=top>".($descServico)."</td>";
                                                echo "<td valign=top>".($descDetalhadaServico)."</td>";

                                                echo "<td valign=top>".($seqServico)."</td>";

                                                if ($licitacaoComResultado and in_array($ultimaFase, $fasesComResultado)) {
                                                    echo "<td valign=top>R$ ".number_format((float) $valorEstimadoItem, 2, ",", ".")."</td>";
                                                    echo "<td valign=top>R$ ".number_format((float) $valorHomologadoItem, 2, ",", ".")."</td>";
                                                }

                                                echo "</tr>";
                                            }

                                            echo "</table>";
                                            echo "</td>";
                                            echo "</tr>";
                                        }

                                            //--------------------------------------------------------
                                            // Final Trecho de código inserido por Heraldo
                                            //---------------------------------------------------------
                                            echo "<tr>\n";
                                            echo "	<td class=\"textonegrito\" bgcolor=\"#DCEDF7\" colspan=\"5\">";

                                            $paramentrosConsultaDocumentos = "processo=$Processo&ano=$ProcessoAno&comissao=$ComissaoCodigo&grupo=$GrupoCodigo";
                                            echo '<a href="#"
    							 			         onclick="javascript:AbreJanelaItem(\'../licitacoes/ConsAcompDetalhesDocumentosRelacionados.php?'.$paramentrosConsultaDocumentos.'\', 900, 350);"
                                                   >DOCUMENTOS RELACIONADOS</a>';
                                            echo "</td>";

                                              echo "</tr>\n";
                                        ?>

                                        <?php
                                            # Pega as Fases da Licitação #
                                            $sql    = "SELECT A.EFASESDESC, A.AFASESORDE, B.CLICPOPROC, B.ALICPOANOP, ";
                                            $sql   .= "       B.CFASESCODI, B.EFASELDETA, B.TFASELDATA, C.CATASFCODI, ";
                                            $sql   .= "       C.EATASFNOME, C.eatasfobse, C.fatasfexcl, U.EUSUPORESP, C.TATASFULAT";
                                            $sql   .= "  FROM SFPC.TBFASES A, SFPC.TBFASELICITACAO B LEFT OUTER JOIN SFPC.TBATASFASE C ";
                                            $sql   .= "    ON B.CLICPOPROC = C.CLICPOPROC AND B.ALICPOANOP = C.ALICPOANOP ";
                                            $sql   .= "   AND B.CCOMLICODI = C.CCOMLICODI AND B.CGREMPCODI = C.CGREMPCODI ";
                                            $sql   .= "   AND B.CORGLICODI = C.CORGLICODI AND B.CFASESCODI = C.CFASESCODI ";
                                            $sql   .= " 	    LEFT OUTER JOIN SFPC.TBUSUARIOPORTAL U ON C.CUSUPOCODI = U.CUSUPOCODI";
                                            $sql   .= " WHERE B.CLICPOPROC = $Processo AND B.ALICPOANOP = $ProcessoAno ";
                                            $sql   .= "   AND B.CCOMLICODI = $ComissaoCodigo AND B.CGREMPCODI = $GrupoCodigo ";
                                            $sql   .= "   AND B.CFASESCODI = A.CFASESCODI AND A.CFASESCODI <> 1 "; //Menos a fase Interna
                                            $sql   .= " ORDER BY A.AFASESORDE";
                                            $result = $db->query($sql);

                                        if (PEAR::isError($result)) {
                                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                        }

                                            $resultadoFases        = $db->query($sql);
                                            $totalLinhas            = $resultadoFases->numRows();
                                            $totalAtasNaHomologacao = 0; // Acumulador de total de atas na fase de homologação
                                            $codigoAta                = "";
                                            $faseCod                = "";

                                        if ($totalLinhas > 0) {
                                            while ($linhaFase = $resultadoFases->fetchRow()) {
                                                $descricaoFase    = $linhaFase[0];
                                                $tempCodigoAta    = $linhaFase[7];
                                                $tempNomeAta    = $linhaFase[8];

                                                if ($descricaoFase == "HOMOLOGAÇÃO" && $tempCodigoAta != "" && $tempNomeAta != "") {
                                                    $codigoAta        = $linhaFase[7];
                                                    $nomeAta        = $linhaFase[8];
                                                    $faseCod        = $linhaFase[4];
                                                    $totalAtasNaHomologacao++;
                                                }
                                            }

                                            // Exibe link direto para o único arquivo
                                            if ($totalAtasNaHomologacao == 1) {
                                                $ArqUpload    = "licitacoes/"."ATASFASE".$GrupoCodigo."_".$Processo."_".$ProcessoAno."_".$ComissaoCodigo."_".$OrgaoLicitanteCodigo."_".$faseCod."_".$codigoAta;
                                                $Arquivo    = $GLOBALS["CAMINHO_UPLOADS"].$ArqUpload;
                                                addArquivoAcesso($ArqUpload);

                                                $Url = "ConsAcompDownloadAtas.php?GrupoCodigo=$GrupoCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&FaseCodigo=$faseCod&AtaCodigo=$codigoAta";

                                                if (! in_array($Url, $_SESSION['GetUrl'])) {
                                                    $_SESSION['GetUrl'][] = $Url;
                                                }

                                                echo "<tr>\n";
                                                echo "    <td class=\"textonegrito\" bgcolor=\"#DCEDF7\" colspan=\"5\">";
                                                echo '        <a href="'.$Url.'">RESULTADO DO PROCESSO LICITATÓRIO</a>';
                                                echo "    </td>";
                                                echo "</tr>\n";
                                            }

                                            // Caso exista mais de uma ata na fase de homologação será exibido um link para um popup
                                            if ($totalAtasNaHomologacao > 1) {
                                                echo "<tr>\n";
                                                echo "	<td class=\"textonegrito\" bgcolor=\"#DCEDF7\" colspan=\"5\">";

                                                $paramentrosConsultaDocumentos = "processo=$Processo&ano=$ProcessoAno&comissao=$ComissaoCodigo&grupo=$GrupoCodigo&orgaoLicitante=$OrgaoLicitanteCodigo";
                                                echo '<a href="#"
															 onclick="javascript:AbreJanelaItem(\'../licitacoes/ConsAcompDetalhesDocumentosResultadoProcessoLicitatorio.php?'.$paramentrosConsultaDocumentos.'\', 900, 350);">
    														 RESULTADO DO PROCESSO LICITATÓRIO
    													  </a>';
                                                echo "</td>";
                                                echo "</tr>\n";
                                            }
                                        }
                                        ?>

                                        <tr>
                                            <td class="textonormal" colspan="4"  style="padding: 0; border:0px;" >
                                                <?php
                                                if ($Mens2 == 1) {
                                                    ExibeMens($Mensagem, $Tipo);
                                                }
                                                ?>

                                                <table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" style="width:100%;  border:1px;">
                							<?php
                                                $Rows = $result->numRows();

                                            if ($Rows > 0) {
                                                echo "<tr>\n";
                                                echo "	<td class=\"textonegrito\" bgcolor=\"#DCEDF7\" colspan=\"5\"> HISTÓRICO </td>\n";
                                                echo "</tr>\n";
                                                echo "<tr>\n";
                                                echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\">FASE</td>\n";
                                                echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\">DATA</td>\n";
                                                echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\">DETALHE</td>\n";
                                                echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonegrito\">ATA(S) DA FASE</td>\n";
                                                echo "</tr>\n";

                                                while ($Linha = $result->fetchRow()) {
                                                    $FaseCodigo = $Linha[4];
                                                    $DataFase = substr($Linha[6], 8, 2)."/".substr($Linha[6], 5, 2)."/".substr($Linha[6], 0, 4);
                                                    $FaseDetalhamento = $Linha[5];
                                                    $nomeAta = $Linha[8];
                                                    $itemObservacao = " - <b>Observação/ Justificativa:</b> \"".$Linha[9]."\"";
                                                    $itemExcluido = $Linha[10];
                                                    $itemAutor = " - <b>Responsável:</b> \"".$Linha[11]."\"";
                                                    $itemDataAlteracao = $Linha[12];

                                                    if ($itemDataAlteracao < "2011-03-23") {
                                                        $itemObservacao = "";
                                                        $itemAutor = "";
                                                    }

                                                    if (($CodFaseAnterior != "") and ($Linha[4] != $CodFaseAnterior)) {
                                                        echo "</td>\n</tr>\n";
                                                    }

                                                    if ($Linha[4] == $CodFaseAnterior) {
                                                        $ArqUpload = "licitacoes/"."ATASFASE".$GrupoCodigo."_".$Processo."_".$ProcessoAno."_".$ComissaoCodigo."_".$OrgaoLicitanteCodigo."_".$FaseCodigo."_".$Linha[7];
                                                        $Arquivo = $GLOBALS["CAMINHO_UPLOADS"].$ArqUpload;
                                                        addArquivoAcesso($ArqUpload);

                                                        if ($itemExcluido == "S") {
                                                            echo  "<s><br><img src='../midia/disqueteInexistente.gif' border='0'><font color=\"#000000\"> $nomeAta </font></s> $itemAutor $itemObservacao <b>(excluído)</b><br/>";
                                                        } elseif (file_exists($Arquivo)) {
                                                            $Url = "ConsAcompDownloadAtas.php?GrupoCodigo=$GrupoCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&FaseCodigo=$FaseCodigo&AtaCodigo=$Linha[7]";

                                                            if (! in_array($Url, $_SESSION['GetUrl'])) {
                                                                $_SESSION['GetUrl'][] = $Url;
                                                            }

                                                            echo  "<br><a href='$Url'><img src=../midia/disquete.gif border=0> <font color='#000000'> $nomeAta </font></a> $itemAutor $itemObservacao<br/>";
                                                        } else {
                                                            echo  "<br><img src='../midia/disqueteInexistente.gif' border='0'><font color=\"#000000\"> $nomeAta </font> $itemAutor $itemObservacao <b>(arquivo não armazenado)</b><br/>";
                                                        }
                                                    } else {
                                                        echo "<tr>\n";
                                                        $DataFase = substr($Linha[6], 8, 2)."/".substr($Linha[6], 5, 2)."/".substr($Linha[6], 0, 4);
                                                        echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[0]</td>\n";
                                                        echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$DataFase</td>\n";
                                                        echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[5]&nbsp;</td>\n";

                                                        if ($Linha[7] != 0) {
                                                            $ArqUpload = "licitacoes/"."ATASFASE".$GrupoCodigo."_".$Processo."_".$ProcessoAno."_".$ComissaoCodigo."_".$OrgaoLicitanteCodigo."_".$FaseCodigo."_".$Linha[7];
                                                            $Arquivo = $GLOBALS["CAMINHO_UPLOADS"].$ArqUpload;
                                                            addArquivoAcesso($ArqUpload);

                                                            if ($itemExcluido == "S") {
                                                                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><img src='../midia/disqueteInexistente.gif' border='0'><s><font color=\"#000000\"> $nomeAta</font></s> $itemAutor $itemObservacao <b>(excluído)</b><br/>";
                                                            } elseif (file_exists($Arquivo)) {
                                                                $Url = "ConsAcompDownloadAtas.php?GrupoCodigo=$GrupoCodigo&Processo=$Processo&ProcessoAno=$ProcessoAno&ComissaoCodigo=$ComissaoCodigo&OrgaoLicitanteCodigo=$OrgaoLicitanteCodigo&FaseCodigo=$FaseCodigo&AtaCodigo=$Linha[7]";

                                                                if (! in_array($Url, $_SESSION['GetUrl'])) {
                                                                    $_SESSION['GetUrl'][] = $Url;
                                                                }

                                                                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=$Url><img src='../midia/disquete.gif' border=0> <font color=\"#000000\"> $nomeAta </font></a> $itemAutor $itemObservacao<br/>";
                                                            } else {
                                                                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><img src='../midia/disqueteInexistente.gif' border='0'><font color=\"#000000\"> $nomeAta</font> $itemAutor $itemObservacao <b>(arquivo não armazenado)</b><br/>";
                                                            }
                                                        } else {
                                                            echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">&nbsp;</td>";
                                                        }
                                                    }

                                                    $CodFaseAnterior = $Linha[4];
                                                }

                                                echo "</td>\n</tr>\n";
                                            }

                                                # Busca o(s) resultado(s) da Licitação #
                                                $sql    = "SELECT ERESLIHABI, ERESLIINAB, ERESLIJULG, ERESLIREVO, ERESLIANUL ";
                                                $sql   .= "  FROM SFPC.TBRESULTADOLICITACAO ";
                                                $sql   .= " WHERE CLICPOPROC = $Processo AND ALICPOANOP = $ProcessoAno ";
                                                $sql   .= "   AND CCOMLICODI = $ComissaoCodigo AND CORGLICODI = $OrgaoLicitanteCodigo";
                                                $sql   .= "   AND CGREMPCODI = $GrupoCodigo";
                                                $result = $db->query($sql);

                                            if (PEAR::isError($result)) {
                                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                                            }

                                                $Rows = $result->numRows();

                                            if ($Rows >= 1) {
                                                while ($Linha = $result->fetchRow()) {
                                                    $Resultados    = 1;
                                                    $ResultadoHabi = $Linha[0];
                                                    $ResultadoInab = $Linha[1];
                                                    $ResultadoJulg = $Linha[2];
                                                    $ResultadoRevo = $Linha[3];
                                                    $ResultadoAnul = $Linha[4];
                                                }
                                            } else {
                                                $Resultados = 0;
                                            }

                                                $db->disconnect();

                                            if (($ResultadoHabi != "") or ($ResultadoInab != "") or ($ResultadoJulg != "") or ($ResultadoRevo != "") or ($ResultadoAnul != "")) {
                                                echo "<tr>\n";
                                                echo "<td class=\"textonegrito\" bgcolor=\"#DCEDF7\" colspan=\"5\">RESULTADOS</td>\n";
                                                echo "</tr>\n";
                                            }

                                            if ($ResultadoHabi != "") {
                                                echo "<tr>\n";
                                                echo "  <td class=\"textonegrito\" bgcolor=\"#F7F7F7\" colspan=\"5\" align=\"center\" >EMPRESAS HABILITADAS </td>\n";
                                                echo "  <tr>\n";
                                                echo "  	<td class=\"textonormal\" colspan=\"4\">$ResultadoHabi</td>\n";
                                                echo "  </tr>\n";
                                                echo "</tr>\n";
                                            }

                                            if ($ResultadoInab != "") {
                                                echo "<tr>\n";
                                                echo "  <td class=\"textonegrito\" bgcolor=\"#F7F7F7\" colspan=\"5\" align=\"center\" >EMPRESAS INABILITADAS </td>\n";
                                                echo "  <tr>\n";
                                                echo "  	<td class=\"textonormal\" colspan=\"4\">$ResultadoInab</td>\n";
                                                echo "  </tr>\n";
                                                echo "</tr>\n";
                                            }

                                            if ($ResultadoJulg != "") {
                                                echo "<tr>\n";
                                                echo "  <td class=\"textonegrito\" bgcolor=\"#F7F7F7\" colspan=\"4\" align=\"center\" > JULGAMENTO </td>\n";
                                                echo "  <tr>\n";
                                                echo "  	<td class=\"textonormal\" colspan=\"4\">$ResultadoJulg</td>\n";
                                                echo "  </tr>\n";
                                                echo "</tr>\n";
                                            }

                                            if ($ResultadoRevo != "") {
                                                echo "<tr>\n";
                                                echo "  <td class=\"textonegrito\" bgcolor=\"#F7F7F7\" colspan=\"4\" align=\"center\" >REVOGAÇÃO </td>\n";
                                                echo "  <tr>\n";
                                                echo "  	<td class=\"textonormal\" colspan=\"4\">$ResultadoRevo</td>\n";
                                                echo "  </tr>\n";
                                                echo "</tr>\n";
                                            }

                                            if ($ResultadoAnul != "") {
                                                echo "<tr>\n";
                                                echo "  <td class=\"textonegrito\" bgcolor=\"#F7F7F7\" colspan=\"4\" align=\"center\" >ANULAÇÃO </td>\n";
                                                echo "  <tr>\n";
                                                echo "  	<td class=\"textonormal\" colspan=\"4\">$ResultadoAnul</td>\n";
                                                echo "  </tr>\n";
                                                echo "</tr>\n";
                                            }
                                            ?>
                                        <tr>
                                            </form>

                        					<form method="post" action="ConsAcompResultadoGeral.php">
                            	    	      	<td class="textonormal" colspan="4" align="right">
                                                    <input type="submit" name="Voltar" value="Voltar" class="botao">
                            		          	</td>
                        		          	</form>
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

<script language="javascript" type="text/javascript">
    function AbreJanelaItem(url,largura,altura){
	   window.open(url,'paginaitem','status=no,scrollbars=yes,left=90,top=150,width='+largura+',height='+altura);
    }
</script>
