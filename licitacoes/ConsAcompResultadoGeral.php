<?php
    #-------------------------------------------------------------------------
    # Portal da DGCO
    # Programa: ConsAcompResultadoGeral.php
    # Autor:    Pitang
    # Data:     13/06/14
    # Objetivo: Programa de Pesquisa de Acompanhamento Licitação
    #-------------------------------------------------------------------------
    # Alterado:	Pitang
    # Data:		19/08/2014 - Limpar formulário ao clicar no botão nova consulta.
    # Altera exibição dos valores do checkbox para "desmarcado/marcado"
    # no resultado da pesquisa.
        # Alteração [CR123143]: REDMINE 19 (P6): Daniel Semblano <daniel.semblano@pitang.com>
    #-------------------------------------------------------------------------
    # Alterado:	José Almir <jose.almir@pitang.com>
    # Data:		19/08/2014
    # Objetivo: #3 [CR123143]: REDMINE 19 (P6) - Remover fase "Interna" do array de fases "Em andamento"
    #-------------------------------------------------------------------------

    # Acesso ao arquivo de funções #
    include "../funcoes.php";
    include "funcoesLicitacoes.php";

    # Executa o controle de segurança #
    session_start();
    Seguranca();

    # Adiciona páginas no MenuAcesso #
    AddMenuAcesso('/licitacoes/ConsAcompPesquisaGeral.php');
    AddMenuAcesso('/licitacoes/ConsAcompDetalhes.php');

    # Variáveis com o global off #
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        $carregaProcesso  = $_POST['carregaProcesso'];
        $Botao            = $_POST['Botao'];
        $Critica          = $_POST['Critica'];
    }

    # Identifica o Programa para Erro de Banco de Dados #
    $ErroPrograma = "ConsAcompResultadoGeral.php";
    $arraySituacoesConcluidas = array('11', '12', '13', '15', '17', '18', '19'); // Array com os ids das situações concluídas
    $arraySituacoesEmAndamento = array('2', '3', '4', '5', '6', '7', '8', '9', '10', '14', '16'); // Array com os ids das situações em andamento

    define("COL_SPAN", 6);

    if ($_SESSION['Pesquisar'] == 1) {
        $Selecao              = $_SESSION['Selecao'];
        $Objeto               = strtoupper2($_SESSION['Objeto']);
        $OrgaoLicitanteCodigo = $_SESSION['OrgaoLicitanteCodigo'];
        $ComissaoCodigo       = $_SESSION['ComissaoCodigo'];
        $ModalidadeCodigo     = $_SESSION['ModalidadeCodigo'];
        $LicitacaoAno         = $_SESSION['LicitacaoAno'];
        $TipoItemLicitacao      =    $_SESSION['TipoItemLicitacao'];
        $Item                  =    $_SESSION['Item'];
        $adminDireta          = $_SESSION['adminDireta'];
        $tipoEmpresa          = $_SESSION['tipoEmpresa'];
        $licitacaoSituacao    = $_SESSION['licitacaoSituacao'];

        // Prepara exibição dos filtros
        $fragmentoSelect      = " SELECT LP.CLICPOPROC ";
        $fragmentoFrom        = " FROM SFPC.TBLICITACAOPORTAL LP ";
        $fragmentoWhere       = " WHERE 1 = 1 ";
        $queryExiste          = false;

        if (empty($OrgaoLicitanteCodigo) === false) {
            $fragmentoSelect  .= " , OL.EORGLIDESC ";
            $fragmentoFrom    .= " , SFPC.TBORGAOLICITANTE OL ";
            $fragmentoWhere   .= " AND OL.CORGLICODI = $OrgaoLicitanteCodigo ";
            $queryExiste       = true;
        }

        if (empty($ComissaoCodigo) === false) {
            $fragmentoSelect  .= " , CL.ECOMLIDESC ";
            $fragmentoFrom    .= " , SFPC.TBCOMISSAOLICITACAO CL ";
            $fragmentoWhere   .= " AND CL.CCOMLICODI = $ComissaoCodigo ";
            $queryExiste       = true;
        }

        if (empty($ModalidadeCodigo) === false) {
            $fragmentoSelect  .= " , ML.EMODLIDESC ";
            $fragmentoFrom    .= " , SFPC.TBMODALIDADELICITACAO ML ";
            $fragmentoWhere   .= " AND ML.CMODLICODI = $ModalidadeCodigo ";
            $queryExiste       = true;
        }

        $descricaoOrgaoLicitante    = 'Todos';
        $descricaoComissao          = 'Todas';
        $descricaoModalidade        = 'Todas';
        $descricaoFase              = ucfirst($licitacaoSituacao);

        // Verifica se é necessário executar consulta ao banco
        if ($queryExiste) {
            $sql  = $fragmentoSelect.$fragmentoFrom.$fragmentoWhere." LIMIT 1";

            $db         = Conexao();
            $result     = $db->query($sql);

            if (PEAR::isError($result)) {
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
            }

            while ($linha = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
                $descricaoOrgaoLicitante = (isset($linha['eorglidesc'])) ? $linha['eorglidesc'] : 'Todos';
                $descricaoComissao = (isset($linha['ecomlidesc'])) ? $linha['ecomlidesc'] : 'Todas';
                $descricaoModalidade = (isset($linha['emodlidesc'])) ? $linha['emodlidesc'] : 'Todas';
            }

            $db->disconnect();
        }

        // Recupera o tipo de item
        $descricaoTipoItem = 'Todos';
        if (empty($TipoItemLicitacao) === false) {
            $descricaoTipoItem = ($TipoItemLicitacao == "1") ? 'Material' : 'Serviço';
        }

        $filtroPesquisa = array(
            'Objeto' => ($Objeto == '') ? 'Todos' : $Objeto ,
            'Órgão Licitante' => $descricaoOrgaoLicitante,
            'Administração direta' => ($adminDireta === true) ? 'Marcado' : 'Desmarcado',
            'Comissão' => $descricaoComissao,
            'Modalidade' => $descricaoModalidade,
            'Situação' => $descricaoFase,
            'Ano' => ($LicitacaoAno != "" && $licitacaoSituacao == 'concluídas') ? $LicitacaoAno : 'Todos',
            'Microempresa, EPP ou MEI' => ($tipoEmpresa === true) ? 'Marcado' : 'Desmarcado',
            'Item' => $descricaoTipoItem,
            'Descrição item' => ($Item == '') ? 'Todas' : $Item,
        );
    }

    # Redireciona dados para ConsAcompPesquisaGeral.php se Houve Erro #
    if (($Item == "") and ($TipoItemLicitacao) != "") {
        $_SESSION['Mensagem']             = "Falta digitar o texto do Item";
        $_SESSION['Mens']                 = 1;
        $_SESSION['Tipo']                 = 1;
        $_SESSION['Objeto']               = $Objeto;
        $_SESSION['OrgaoLicitanteCodigo'] = $OrgaoLicitanteCodigo;
        $_SESSION['ComissaoCodigo']       = $ComissaoCodigo;
        $_SESSION['ModalidadeCodigo']     = $ModalidadeCodigo;
        $_SESSION['Selecao']              = $Selecao;
        $_SESSION['RetornoPesquisa']      = 1;
        $_SESSION['TipoItemLicitacao']      = $TipoItemLicitacao;
        $_SESSION['Item']                  = $Item;
        $_SESSION['adminDireta']          = $adminDireta;
        $_SESSION['tipoEmpresa']          = $tipoEmpresa;
        $_SESSION['licitacaoSituacao']    = $licitacaoSituacao;

        header("location: ConsAcompPesquisaGeral.php");
        exit();
    }

    # Redireciona dados para ConsAcompPesquisaGeral.php #
    if ($Botao == "Pesquisa") {
        $_SESSION['RetornoPesquisa'] = null;
        header("location: ConsAcompPesquisaGeral.php");
        exit();
    }

    if ($Botao == "carregaProcesso") {
        list($_SESSION['GrupoCodigoDet'], $_SESSION['ProcessoDet'], $_SESSION['ProcessoAnoDet'], $_SESSION['ComissaoCodigoDet'], $_SESSION['OrgaoLicitanteCodigoDet']) = explode("-", $carregaProcesso);
        header("location: ConsAcompDetalhes.php");
        exit();
    }

    $Mens = 0;

    if ($Mens == 0) {
        $db       = Conexao();
        $Data     = date("Y-m-d");

        $novaSql = '';

        // SELECT
        $novaSql .= " SELECT ";
        $novaSql .= " distinct A.CLICPOPROC, B.EORGLIDESC, CD.ECOMLIDESC, e.EFASESDESC, GE.EGREMPDESC, ML.EMODLIDESC, ";
        $novaSql .= " A.ALICPOANOP, A.CLICPOCODL, A.ALICPOANOL, A.XLICPOOBJE, ";
        $novaSql .= " A.TLICPODHAB, A.CGREMPCODI, A.CCOMLICODI, A.CORGLICODI, e.CFASESCODI ";

        // FROM
        $novaSql .= ' FROM ';

        if ($TipoItemLicitacao == 1) {
            $novaSql .= " SFPC.tbitemlicitacaoportal F, SFPC.tbmaterialportal G, ";
        }

        if ($TipoItemLicitacao == 2) {
            $novaSql .= " SFPC.tbitemlicitacaoportal F, SFPC.tbservicoportal G, ";
        }

        $novaSql .= ' SFPC.TBFASES e,
                        (
                            SELECT
                                l.CLICPOPROC AS Proc ,
                                l.ALICPOANOP AS Ano ,
                                l.CGREMPCODI AS Grupo ,
                                l.CCOMLICODI AS Comis ,
                                l.CORGLICODI AS Orgao ,
                                MAX(o.AFASESORDE) AS Maior
                            FROM
                                SFPC.TBFASELICITACAO l ,
                                SFPC.TBFASES o
                            WHERE
                                l.CFASESCODI = o.CFASESCODI
                            GROUP BY
                                l.CLICPOPROC ,
                                l.ALICPOANOP ,
                                l.CGREMPCODI ,
                                l.CCOMLICODI ,
                                l.CORGLICODI
                        ) AS maiorordem, ';
        $novaSql .= ' SFPC.TBFASELICITACAO D, ';
        $novaSql .= ' SFPC.TBLICITACAOPORTAL A ';

        // Microempresa, EPP ou MEI
        if ($tipoEmpresa === true) {
            $novaSql .= " LEFT OUTER JOIN SFPC.TBITEMLICITACAOPORTAL ILP ";
            $novaSql .= " ON ILP.CLICPOPROC = A.CLICPOPROC ";
            $novaSql .= " AND ILP.ALICPOANOP = A.ALICPOANOP ";
            $novaSql .= " AND ILP.CGREMPCODI = A.CGREMPCODI ";
            $novaSql .= " AND ILP.CCOMLICODI = A.CCOMLICODI ";
            $novaSql .= " AND ILP.CORGLICODI = A.CORGLICODI INNER JOIN SFPC.TBFORNECEDORCREDENCIADO FC ";
            $novaSql .= " ON ILP.AFORCRSEQU = FC.AFORCRSEQU ";
            $novaSql .= " AND FC.FFORCRMEPP IS NOT NULL ";
        }

        // JOIN Comissão licitação
        $novaSql .= ' INNER JOIN SFPC.TBCOMISSAOLICITACAO CD ';
        $novaSql .= ' ON CD.CCOMLICODI = A.CCOMLICODI ';

        // JOIN Grupo
        $novaSql .= ' INNER JOIN SFPC.TBGRUPOORGAO GO ';
        $novaSql .= ' ON GO.CGREMPCODI = A.CGREMPCODI AND GO.CORGLICODI = A.CORGLICODI ';
        $novaSql .= ' INNER JOIN SFPC.TBGRUPOEMPRESA GE ';
        $novaSql .= ' ON GE.CGREMPCODI = GO.CGREMPCODI ';

        // JOIN Modalidade
        $novaSql .= ' INNER JOIN SFPC.TBMODALIDADELICITACAO ML ';
        $novaSql .= ' ON ML.CMODLICODI = A.CMODLICODI ';

        // JOIN Órgão licitante
        $novaSql .= ' INNER JOIN SFPC.TBORGAOLICITANTE B ';
        $novaSql .= ' ON B.CORGLICODI = A.CORGLICODI ';

        if ($OrgaoLicitanteCodigo != "") {
            $novaSql .= " AND A.CORGLICODI = '$OrgaoLicitanteCodigo' ";
        }

        // WHERE
        $novaSql .= ' WHERE 1 = 1 ';
        $novaSql .= ' AND a.CLICPOPROC = maiorordem.Proc
                      AND a.ALICPOANOP = maiorordem.Ano
                      AND a.CGREMPCODI = maiorordem.Grupo
                      AND a.CCOMLICODI = maiorordem.Comis
                      AND a.CORGLICODI = maiorordem.Orgao
                      AND e.AFASESORDE = maiorordem.Maior
                      AND a.clicpoproc = d.clicpoproc
                      AND a.alicpoanop = d.alicpoanop
                      AND a.cgrempcodi = d.cgrempcodi
                      AND a.ccomlicodi = d.ccomlicodi
                      AND a.corglicodi = d.corglicodi
                      AND d.CFASESCODI = e.CFASESCODI
                      AND a.CCOMLICODI NOT IN(41) ';

        // Objeto
        if ($Objeto != "") {
            $novaSql .= " AND upper(A.XLICPOOBJE) LIKE '%$Objeto%'";
        }

        // Administração direta
        if ($adminDireta === true) {
            $novaSql .= " AND a.CGREMPCODI = 1 "; // Grupo administração direta
            $novaSql .= " AND a.CCOMLICODI IN (1, 2, 3, 4, 8, 40, 34, 39, 42, 44) ";
        }

        // Comissão
        if ($ComissaoCodigo != "") {
            $novaSql .= " AND A.CCOMLICODI = $ComissaoCodigo ";
        }

        // Modalidade
        if ($ModalidadeCodigo != "") {
            $novaSql .= " AND A.CMODLICODI = $ModalidadeCodigo ";
        }

        // Situação
        if ($licitacaoSituacao != "") {
            if ($licitacaoSituacao == 'concluídas') {
                $strIdConcluidas = implode(', ', $arraySituacoesConcluidas);
                $novaSql   .= " AND D.CFASESCODI IN ($strIdConcluidas) ";
            } elseif ($licitacaoSituacao == 'andamento') {
                $strIdAndamento = implode(', ', $arraySituacoesEmAndamento);
                $novaSql   .= " AND D.CFASESCODI IN ($strIdAndamento) ";
            }

            if ($LicitacaoAno != "" && $licitacaoSituacao == 'concluídas') {
                $novaSql .= " AND EXTRACT(YEAR FROM D.TFASELDATA) = '$LicitacaoAno' ";
            }
        }

        // Item
        if (($TipoItemLicitacao == 1) or ($TipoItemLicitacao == 2)) {
            $novaSql .= " AND A.CLICPOPROC = F.CLICPOPROC ";
            $novaSql .= " AND A.ALICPOANOP = F.ALICPOANOP ";
            $novaSql .= " AND A.CGREMPCODI = F.CGREMPCODI ";
            $novaSql .= " AND A.CCOMLICODI = F.CCOMLICODI ";
            $novaSql .= " AND A.CORGLICODI = F.CORGLICODI ";
        }

        // Descrição do item material
        if ($TipoItemLicitacao == 1) {
            $novaSql .= " AND F.CMATEPSEQU = G.CMATEPSEQU ";
            $novaSql .= " AND (G.EMATEPDESC ILIKE '%$Item%') ";
        }

        // Descrição do item serviço
        if ($TipoItemLicitacao == 2) {
            $novaSql .= " AND F.CSERVPSEQU = G.CSERVPSEQU ";
            $novaSql .= " AND (G.ESERVPDESC ILIKE '%$Item%') ";
        }

        // ORDER BY
        $novaSql .= " ORDER BY GE.EGREMPDESC, ML.EMODLIDESC, CD.ECOMLIDESC, A.ALICPOANOP DESC, A.CLICPOPROC DESC";

        $result = $db->query($novaSql);

        if (PEAR::isError($result)) {
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $novaSql");
        }

        $cont = 0;
        while ($cols = $result->fetchRow()) {
            // Query para recuperar todas as fases da licitação
            $sqlFases = "SELECT F.CFASESCODI FROM SFPC.TBFASELICITACAO F WHERE F.CLICPOPROC = $cols[0] AND F.ALICPOANOP = $cols[6]
						 AND F.CGREMPCODI = $cols[11] AND F.CCOMLICODI = $cols[12] AND F.CORGLICODI = $cols[13]";

            $fasesLicitacao = $db->getCol($sqlFases);
            if (PEAR::isError($fasesLicitacao)) {
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlFases");
            }

            $licitacaoPublicada = in_array(2, $fasesLicitacao);
            $dataAberturaMenorQueAtual = strtotime($cols[10]) < strtotime(date('Y-m-d H:i:s'));

            // Verifica se a licitação está publicada ou se a data de abertura é menor que a data atual
            if ($licitacaoPublicada || $dataAberturaMenorQueAtual) {
                $cont++;
                $dados[$cont-1] = "$cols[4]_$cols[5]_$cols[2]_$cols[0]_$cols[6]_$cols[7]_$cols[8]_$cols[9]_$cols[10]_$cols[1]_$cols[11]_$cols[12]_$cols[13]_$cols[14]_$cols[3]";
            }
        }

        $GrupoDescricao = "";

        if ($cont != 0) {
            echo "<html>\n";

            # Carrega o layout padrão #
            layout();
            echo "<script language=\"javascript\">\n";
            echo "<!--\n";
            echo "function enviar(valor){\n";
            echo "	document.Acomp.Botao.value=valor;\n";
            echo "	document.Acomp.submit();\n";
            echo "}\n";
            echo "function carregaProcesso(valor){\n";
            echo "	document.Acomp.Botao.value='carregaProcesso';\n";
            echo "	document.Acomp.carregaProcesso.value=valor;\n";
            echo "	document.Acomp.submit();\n";
            echo "}\n";
            MenuAcesso();
            echo "//-->\n";
            echo "</script>\n";
            echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"../estilo.css\">\n";

            ?>

			<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
			<script language="JavaScript" src="../menu.js"></script>
			<script language="JavaScript">Init();</script>

			<?php
            echo "<form action=\"ConsAcompResultadoGeral.php\" method=\"post\" name=\"Acomp\">\n";
            echo "<br><br><br><br>\n";
            echo "<table cellpadding=\"3\" border=\"0\" summary=\"\">\n";
            echo "  <!-- Caminho -->\n";
            echo "  <tr>\n";
            echo "    <td width=\"150\"><img border=\"0\" src=\"../midia/linha.gif\" alt=\"\"></td>\n";
            echo "    <td align=\"left\" class=\"textonormal\" colspan=\"2\"><br>\n";
            echo "      <font class=\"titulo2\">|</font>\n";
            echo "      <a href=\"../index.php\"><font color=\"#000000\">Página Principal</font></a> > Licitações > Acompanhamento\n";
            echo "    </td>\n";
            echo "  </tr>\n";
            echo "  <!-- Fim do Caminho-->\n";
            echo "	<!-- Erro -->\n";

            if ($Mens == 1) {
                echo "	<tr>\n";
                echo "	  <td width=\"100\"></td>\n";
                echo "	  <td align=\"left\" colspan=\"2\">\n";

                if ($Mens == 1) {
                    ExibeMens($Mensagem, $Tipo, 1);
                }

                echo "    </td>\n";
                echo "	</tr>\n";
            }

            echo "	<!-- Fim do Erro -->\n";
            echo "	<!-- Corpo -->\n";
            echo "	<tr>\n";
            echo "		<td width=\"100\"></td>\n";
            echo "		<td class=\"textonormal\">\n";
            echo "      <table  border=\"0\" cellspacing=\"0\" cellpadding=\"3\" bgcolor=\"#ffffff\" summary=\"\">\n";
            echo "        <tr>\n";
            echo "	      	<td class=\"textonormal\">\n";
            echo "	        	<table border=\"1\" cellpadding=\"3\" cellspacing=\"0\" bordercolor=\"#75ADE6\" summary=\"\" class=\"textonormal\">\n";
            echo "	          	<tr>\n";
            echo "	            	<td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" colspan=\"".COL_SPAN."\" class=\"titulo3\">\n";
            echo "		    					ACOMPANHAMENTO DE LICITAÇÕES\n";
            echo "		    					- RESULTADO\n";
            echo "		          	</td>\n";
            echo "		        	</tr>\n";
            echo "<tr>";
            echo "</td>";
            echo "</tr>";
            echo "";
            echo "	          	<tr>\n";
            echo "	            	<td colspan=\"".COL_SPAN."\" class=\"textonormal\">\n";
            echo "	        	    		Para visualizar mais informações sobre a Licitação, clique no número do Processo desejado. Para realizar uma nova pesquisa, selecione o botão \"Nova Pesquisa\"\n";
            echo "		          	</td>\n";
            echo "		        	</tr>\n";
            echo "	          	<tr>\n";
            echo "	    	      	<td class=\"textonormal\" colspan=\"".COL_SPAN."\" align=\"right\">\n";
            echo "			            <input type=\"hidden\" name=\"Selecao\" value=\"$Selecao\">\n";
            echo "	          			<input type=\"button\" name=\"Pesquisa\" value=\"Nova Pesquisa\" class=\"botao\" onclick=\"javascript:enviar('Pesquisa');\">\n";
            echo "			            <input type=\"hidden\" name=\"Botao\" value=\"\">\n";
            echo "			            <input type=\"hidden\" name=\"carregaProcesso\" value=\"\">\n";
            echo "          			</td>\n";
            echo "		        	</tr>\n";

            // [Filtros da pesquisa]
            echo "<tr><td align=\"center\" class=\"titulo3\" colspan=\"".COL_SPAN."\" bgcolor=\"#DCEDF7\">CRITÉRIOS DE PESQUISA</td></tr>\n";
            foreach ($filtroPesquisa as $nomeFiltro => $valor) {
                echo "<tr>";
                echo "<td colspan=\"2\" valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">$nomeFiltro</td>";
                echo "<td colspan=\"4\" valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$valor</td>";
                echo "</tr>";
            }
            // [/Filtros da pesquisa]

            for ($Row = 0; $Row < $cont; $Row++) {
                $Linha = explode("_", $dados[$Row]);

                if ($GrupoDescricao != $Linha[0]) {
                    $GrupoDescricao = $Linha[0];
                    echo "<tr><td align=\"center\" class=\"titulo3\" colspan=\"".COL_SPAN."\" bgcolor=\"#DCEDF7\">$GrupoDescricao</td></tr>\n";
                    $ModalidadeDescricao = "";
                }

                if ($ModalidadeDescricao != $Linha[1]) {
                    $ModalidadeDescricao = $Linha[1];
                    echo "<tr><td align=\"center\" class=\"titulo3\" colspan=\"".COL_SPAN."\">$ModalidadeDescricao</td></tr>\n";
                    $ComissaoDescricao = "";
                }

                if ($ComissaoDescricao != $Linha[2]) {
                    $ComissaoDescricao = $Linha[2];
                    echo "<tr><td class=\"titulo2\" colspan=\"".COL_SPAN."\" color=\"#000000\">$ComissaoDescricao</td></tr>\n";
                    echo "<tr><td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">PROCESSO</td>\n";
                    echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">LICITAÇÃO</td>\n";
                    echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">OBJETO</td>\n";
                    echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">DATA/HORA ABERTURA</td>\n";
                    echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">ÓRGÃO LICITANTE</td>\n";
                    echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"titulo3\">SITUAÇÃO</td></tr>\n";
                }

                $NProcesso  = substr($Linha[3] + 10000, 1);
                $NLicitacao = substr($Linha[4] + 10000, 1);
                $Linha[5]   = substr($Linha[5] + 10000, 1);
                $Linha[6]   = substr($Linha[6] + 10000, 1);
                $LicitacaoDtAbertura = substr($Linha[8], 8, 2)."/".substr($Linha[8], 5, 2)."/".substr($Linha[8], 0, 4);
                $LicitacaoHoraAbertura = substr($Linha[8], 11, 5);

                echo "<tr>\n";
                $Url = $Linha[10]."-".$Linha[3]."-".$Linha[4]."-".$Linha[11]."-".$Linha[12];
                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\"><a href=\"javascript:carregaProcesso('$Url');\"><font color=\"#000000\"><u>$NProcesso/$Linha[4]</u></font></a></td>\n";
                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[5]/$Linha[6]</td>\n";
                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[7]</td>\n";
                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$LicitacaoDtAbertura<br>$LicitacaoHoraAbertura h</td>\n";
                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$Linha[9]</td>\n";

                $idUltimaFaseLicitacao = ultimaFase($Linha[3], $Linha[4], $Linha[10], $Linha[11], $Linha[12], $db);
                $situacaoAtualLicitacao = 'EM ANDAMENTO';

                if (in_array($idUltimaFaseLicitacao, $arraySituacoesConcluidas)) {
                    $situacaoAtualLicitacao = 'CONCLUÍDA';
                }

                echo "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$situacaoAtualLicitacao</td>\n";
                echo "</tr>\n";
            }
        } else {
            # Envia mensagem para página selecionar #
            $_SESSION['Mensagem']             = "Nenhuma ocorrência foi encontrada";
            $_SESSION['Mens']                 = 1;
            $_SESSION['Tipo']                 = 1;
            $_SESSION['Objeto']               = $Objeto;
            $_SESSION['OrgaoLicitanteCodigo'] = $OrgaoLicitanteCodigo;
            $_SESSION['ComissaoCodigo']       = $ComissaoCodigo;
            $_SESSION['ModalidadeCodigo']     = $ModalidadeCodigo;
            $_SESSION['Selecao']              = $Selecao;
            $_SESSION['RetornoPesquisa']      = 1;
            $_SESSION['TipoItemLicitacao']      = $TipoItemLicitacao;
            $_SESSION['Item']                  = $Item;
            header("location: ConsAcompPesquisaGeral.php");
            exit();
        }

        echo "    	  	  </table>\n";
        echo "					</td>\n";
        echo "				</tr>\n";
        echo "      </table>\n";
        echo "		</td>\n";
        echo "	</tr>\n";
        echo "	<!-- Fim do Corpo -->\n";
        echo "</table>\n";
        echo "</form>\n";
        echo "</body>\n";
        echo "</html>\n";
        $db->disconnect();
    }
?>
