<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: ConsDetalheAgrupamento.php
# Autor:    José Almir <jose.almir@pitang.com>
# Data:     02/07/2014 - [CR125346]: REDMINE 73
# Objetivo: Consultar detalhe do agrupamento
# ------------------------------------------------------------------------
# Alterado:	José Almir <jose.almir@pitang.com>
# Data:		26/09/2104
# Objetivo: [CR125346]: REDMINE 73
# ------------------------------------------------------------------------

$programa = "ConsDetalheAgrupamento.php";

# Acesso ao arquivo de funções #
require_once 'funcoesCompras.php';

# Executa o controle de segurança #
session_start();

Seguranca();

AddMenuAcesso('/compras/ConsAcompSolicitacaoCompra.php');

# Abrindo Conexao #
$db = Conexao();

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

$Orgao = '';
$boolPesquisar = true;

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao          = filter_input(INPUT_POST, 'Botao');
    $idSolicitacao  = filter_input(INPUT_POST, 'idSolicitacao');
    
    if ($Botao == 'Imprimir') {
        $_SESSION['Botao']          = $Botao;
        $_SESSION['idSolicitacao']  = $idSolicitacao;
        
        header("location: RelDetalheAgrupamentoPdf.php");
        exit;
    }
    
    if ($Botao == 'Voltar') {
        $_SESSION['Botao'] = 'Pesquisar';
        $_SESSION["carregarSelecionarDoSession"] = true;
        
        $dataInicial    = $_SESSION['DataIni'];
        $dataFinal      = $_SESSION['DataFim'];
        $orgao          = $_SESSION['Orgao'];
        $situacao       = $_SESSION['Situacao'];
        
        $uri = 'ConsAgrupamento.php?';
        $uri .= 'dataInicial=' . $dataInicial;
        $uri .= '&dataFinal=' . $dataFinal;
        $uri .= '&orgao=' . $orgao;
        //$uri .= '&situacao=' . $situacao;
        
        header("location: $uri");
        exit;
    }
}

?>

<html>
    <?php
        # Carrega o layout padrão #
        layout();
    ?>
    
    <script language="javascript" src="../janela.js" type="text/javascript"></script>
    <script language="javascript" type="">
        <!--
        function enviar(valor)
        {
        document.formulario.Botao.value = valor;
        document.formulario.submit();
        }
        function AbreJanela(url,largura,altura)
        {
        window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=15,top=15,width='+largura+',height='+altura);
        }
        <?php MenuAcesso(); ?>
        //-->
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css">
    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
        <script language="JavaScript" src="../menu.js"></script>
        <script language="JavaScript">Init();</script>
        <script language="JavaScript">
            $(document).ready(function() {
                //No click do botão detalhar
                $(".detalhar").live("click", function() {
                    //Pega o atributu ID que é a sequencia da solicitacao
                    var seq = $(this).attr("id");
                    //Ver a string dele (+ ou -)
                    var valAtual = $(this).html();
                    //Se for + mostra todas as tr que tem as classe 'opdetalhe' e com a 'seq' clicada
                    if (valAtual == "+") {
                        //Volto para -
                        $(this).html("-");
                        $(".opdetalhe." + seq).show();
                        //Se for - esconde todas as tr que tem as classe 'opdetalhe' e com a 'seq' clicada
                    } else {
                        //Volto para +
                        $(this).html("+");
                        $(".opdetalhe." + seq).hide();
                    }
                });
            });
        </script>
        
        <?php
            if (isset($_SESSION['idSolicitacao'])) {
                $idSolicitacao = $_SESSION['idSolicitacao'];
            
                $sql = "SELECT
                            SOL.CSOLCOSEQU ,
                            SOL.TSOLCODATA ,
                            SOL.CORGLICODI ,
                            ORG.EORGLIDESC ,
                            SOL.CSITSOCODI ,
                            SSO.ESITSONOME ,
                            GRU.CAGSOLSEQU ,
                            GRU.FAGSOLFLAG ,
                            GRU.TAGSOLULAT ,
                            CEN.ECENPODESC ,
                            CEN.ECENPODETA
                        FROM
                            SFPC.TBSOLICITACAOCOMPRA AS SOL JOIN SFPC.TBORGAOLICITANTE AS ORG
                                ON SOL.CORGLICODI = ORG.CORGLICODI JOIN SFPC.TBSITUACAOSOLICITACAO AS SSO
                                ON SOL.CSITSOCODI = SSO.CSITSOCODI JOIN SFPC.TBAGRUPASOLICITACAO AS GRU
                                ON SOL.CSOLCOSEQU = GRU.CSOLCOSEQU JOIN SFPC.TBCENTROCUSTOPORTAL AS CEN
                                ON SOL.CCENPOSEQU = CEN.CCENPOSEQU
                        WHERE
                            SOL.CTPCOMCODI = 2
                            AND SOL.FSOLCORGPR = 'S'
                            AND GRU.CAGSOLSEQU = $idSolicitacao";
                
                $res = $db->query($sql);
                
                if (PEAR::isError($res)) {
                    $CodErroEmail  = $res->getCode();
                    $DescErroEmail = $res->getMessage();
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
                } else {
                    $CodAgrupamentoAntes = "";
                    $ContagemGrupo = 0;
                    $DescOrgaoAntes = "";
                    $DescCentroCustoAntes = "";
                    
                    $html = '';
                    $html .= '<br><br><br><br><br>';
                    $html .= '<table width="100%" cellpadding="3" border="0" summary="">';
                    $html .= '<tr>';
                    $html .= '<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>';
                    $html .= '<td align="left" class="textonormal" colspan="2">';
                    $html .= '<font class="titulo2">|</font>';
                    $html .= '<a href="../index.php"><font color="#000000">Página Principal</font></a> > Compras > Agrupamento > Detalhes consulta';
                    $html .= '</td>';
                    $html .= '</tr>';
                    $html .= '<tr>';
                    $html .= '<td width="150"></td>';
                    $html .= '<td class="textonormal">';
                    $html .= '<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">';
                    
                    $html .= '{ITENS}';
                    
                    $html .= '<tr><td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="5">SOLICITAÇÕES AGRUPADAS</td></tr>';
                    
                    $arrayItens = new ArrayObject();
                    $nomeOrgaoGestor = "&nbsp;";
                    
                    while ($Linha = $res->fetchRow()) {
                        $CodSolicitacao  = $Linha[0]; 			 // SOL.CSOLCOSEQU, /* CÓDIGO SEQUENCIAL DA SOLICITAÇÃO DE COMPRA */
                        $DataSolicitacao = DataBarra($Linha[1]); // SOL.TSOLCODATA, /* DATA E HORA DA SOLICITAÇÃO DE COMPRA */
                        $CodOrgao        = $Linha[2]; 			 // SOL.CORGLICODI, /* CÓDIGO DO ÓRGÃO */
                        $DescOrgao  	 = $Linha[3]; 			 // ORG.EORGLIDESC, /* DESCRIÇÃO DO ÓRGÃO LICITANTE */
                        $CodSituacao	 = $Linha[4];			 // SOL.CSITSOCODI, /* SITUAÇÃO ATUAL DA SOLICITAÇÃO */
                        $DescSolicitacao = $Linha[5];			 // SSO.ESITSONOME, /* DESCRIÇÃO DA SOLICITAÇÃO DA LICITAÇÃO */
                        $CodAgrupamento  = $Linha[6];			 // GRU.CAGSOLSEQU, /* CÓDIGO SEQUENCIAL DO AGRUPAMENTO DAS LICITAÇÕES */
                        $FlagGrupo		 = $Linha[7]; 			 // GRU.FAGSOLFLAG, /* FLAG QUE INDICA A SCC COM O ÓRGÃO GESTOR RESPONSÁVEL PELO AGRUPAMENTO - S/N */
                        $DataAgrupamento = DataBarra($Linha[8]); // GRU.TAGSOLULAT  /* DATA E HORA DA ÚLTIMA ATUALIZAÇÃO */
                        $DescCentroCusto = $Linha[9];			 // CEN.ECENPODESC, /* DESCRIÇÃO DO CENTRO DE CUSTO SFPC */
                        $DetaCentroCusto = $Linha[10];			 // CEN.ECENPODETA, /* DESCRIÇÃO DO DETALHAMENTO DO CENTRO DE CUSTO SFPC */
                        
                        if ($DescOrgaoAntes != $DescOrgao) {
                            $html .= "<tr class='linhaorgao'>\n";
                            $html .= "	<td align=\"center\" bgcolor=\"#BFDAF2\" colspan=\"5\" class=\"titulo3\">$DescOrgao</td>\n";
                            $html .= "</tr>\n";
                        }
                        	
                        $DescOrgaoAntes = $DescOrgao;

                        if ($FlagGrupo == "S") {
                        	$nomeOrgaoGestor = $DescOrgao;
                        }
                        
                        if ($DescCentroCustoAntes != $DescCentroCusto) {
                            $html .= "<tr class='linhacentro'>\n";
                            $html .= "	<td align=\"center\" bgcolor=\"#DDECF9\" colspan=\"5\" class=\"titulo3\">$DescCentroCusto</td>\n";
                            $html .= "</tr>\n";
                            $html .= "<tr class='linhainfo'>\n";
                            $html .= "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">SOLICITAÇÃO</td>\n";
                            $html .= "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">DETALHAMENTO</td>\n";
                            $html .= "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">DATA</td>\n";
                            $html .= "	<td class=\"titulo3\" bgcolor=\"#F7F7F7\">SITUAÇÃO</td>\n";
            				$html .= "</tr>\n";
                        }
                        	
                        $DescCentroCustoAntes = $DescCentroCusto;
                        
                        $strSolicitacaoCodigo = getNumeroSolicitacaoCompra($db, $CodSolicitacao);
                        $html .= "<tr>\n";
                        $html .= "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$strSolicitacaoCodigo</td>\n";
                        $html .= "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$DetaCentroCusto</td>\n";
                        $html .= "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$DataSolicitacao</td>\n";
                        $html .= "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\">$DescSolicitacao</td>\n";
                        $html .= "</tr>\n";
                        
                        // Itens
                        $sqlItens = "   SELECT
                                            I.CITESCSEQU ,
                                            I.CMATEPSEQU ,
                                            I.CSERVPSEQU ,
                                            I.AITESCORDE ,
                                            I.AITESCQTSO ,
                                            I.VITESCUNIT ,
                                            I.VITESCVEXE ,
                                            M.EMATEPDESC ,
                                            S.ESERVPDESC ,
                                            I.EITESCDESCMAT ,
                                            I.EITESCDESCSE                                            
                                        FROM
                                            SFPC.TBITEMSOLICITACAOCOMPRA I LEFT JOIN SFPC.TBMATERIALPORTAL M
                                                ON(
                                                M.CMATEPSEQU = I.CMATEPSEQU
                                            ) LEFT JOIN SFPC.TBSERVICOPORTAL S
                                                ON(
                                                S.CSERVPSEQU = I.CSERVPSEQU
                                            )
                                        WHERE
                                            I.CSOLCOSEQU = $CodSolicitacao
                                        ORDER BY
                                            I.CITESCSEQU ASC";
                        
                        $resItens = $db->query($sqlItens);
                        
                        if (PEAR::isError($resItens)) {
                            $CodErroEmail  = $resIntens->getCode();
                            $DescErroEmail = $resIntens->getMessage();
                            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlItens\n\n$DescErroEmail ($CodErroEmail)");
                        } else {
                    		while ($LinhaItens = $resItens->fetchRow()) {
                    			$intSeqIntem 		= $LinhaItens[0];
                    			$srtSeqMaterial 	= $LinhaItens[1];
                    			$srtSeqServico 		= $LinhaItens[2];
                    			$ordemItem	 		= $LinhaItens[3];
                    			$strQuantidade		= $LinhaItens[4];
                    			$strValorEstimado	= $LinhaItens[5];
                    			
                    			$item = new ArrayObject();
                    			$item->offsetSet('total', $strQuantidade);
                    			$item->offsetSet('licitantes', new ArrayObject());
                    
                    			$strTipo = '';
                    			$strDescricao = '';
                    			$strDescricaoDetalhada = '';
                    			$codRed = '';
                    			
                    			if ($srtSeqMaterial != "") {
                    				$codRed 		= $srtSeqMaterial;
                    				$strDescricao 	= $LinhaItens[7];
                    				$strDescricaoDetalhada = $LinhaItens[9];
                    				$strTipo 		= "CADUM";
                    				$item->offsetSet('id', $srtSeqMaterial);
                    			} else {
                    				$codRed 		= $srtSeqServico;
                    				$strDescricao 	= $LinhaItens[8];
                    				$strDescricaoDetalhada = $LinhaItens[10];
                    				$strTipo 		= "CADUS";
                    				$item->offsetSet('id', $srtSeqServico);
                    			}

                    			$item->offsetSet('ordem', $ordemItem);
                    			$item->offsetSet('quantidade', $strQuantidade);
                    			$item->offsetSet('valorEstimado', $strValorEstimado);
                    			$item->offsetSet('descricao', $strDescricao);
                    			$item->offsetSet('descricaoDetalhada', $strDescricaoDetalhada);
                    			$item->offsetSet('tipo', $strTipo);
                    			$item->offsetSet('codigoReduzido', $codRed);
                    			$itemExiste = false;
                    			
                    			// Varre o arrayItens para verifica se o item existe.
                    			// Caso exista incrementa a quantidade.
                    			foreach ($arrayItens as $itemSolicitacao) {
                                    if ($itemSolicitacao->offsetGet('id') == $item->offsetGet('id') && 
                                        $itemSolicitacao->offsetGet('tipo') == $item->offsetGet('tipo')) {

                                        // Incrementa a quantidade
                                        $somaQtdItem = $itemSolicitacao->offsetGet('total') + $item->offsetGet('quantidade');
                                        $itemSolicitacao->offsetSet('total', $somaQtdItem);
                                        $itemExiste = true;

                                        $licitantes = $itemSolicitacao->offsetGet('licitantes');
                                        $idOrgaoAddItem = null;
                                        
                                        foreach ($licitantes as $licitante) {
                                        	// Se o órgão já existir, adiciona o item ao órgão
                                        	if ($licitante->offsetGet('id') == $CodOrgao) {
                                        		$idOrgaoAddItem = $licitante->offsetGet('id');

                                        		// Item do licitante
                                        		$itemOrgao = new ArrayObject();
                                        		$itemOrgao->offsetSet('idItemOrgao', $item->offsetGet('id'));
                                        		$itemOrgao->offsetSet('descricaoItemOrgao', $item->offsetGet('descricao'));
                                        		$itemOrgao->offsetSet('descricaoDetalhadaItemOrgao', $item->offsetGet('descricaoDetalhada'));
                                        		$itemOrgao->offsetSet('ordemItemOrgao', $item->offsetGet('ordem'));
                                        		$itemOrgao->offsetSet('quantidadeItemOrgao', $item->offsetGet('quantidade'));
                                        		$itemOrgao->offsetSet('valorEstimadoItemOrgao', $item->offsetGet('valorEstimado'));
                                        		 
                                        		// Adiciona ao array o item do licitante
                                        		$itensOrgao = $licitante->offsetGet('itemOrgao');
                                        		$itensOrgao->append($itemOrgao);
                                        	}
                                        }
                                        
                                        // Caso o órgão não exista, 
                                        // adiciona um novo órgão e adiciona o item a esse
                                        if (is_null($idOrgaoAddItem)) {
                                        	$orgao = new ArrayObject();
                                        	$orgao->offsetSet('id', $CodOrgao);
                                        	$orgao->offsetSet('descricao', $DescOrgao);
                                        	$orgao->offsetSet('itemOrgao', new ArrayObject());
                                        	
                                        	// Item do licitante
                                        	$itemOrgao = new ArrayObject();
                                        	$itemOrgao->offsetSet('idItemOrgao', $item->offsetGet('id'));
                                        	$itemOrgao->offsetSet('descricaoItemOrgao', $item->offsetGet('descricao'));
                                        	$itemOrgao->offsetSet('descricaoDetalhadaItemOrgao', $item->offsetGet('descricaoDetalhada'));
                                        	$itemOrgao->offsetSet('ordemItemOrgao', $item->offsetGet('ordem'));
                                        	$itemOrgao->offsetSet('quantidadeItemOrgao', $item->offsetGet('quantidade'));
                                        	$itemOrgao->offsetSet('valorEstimadoItemOrgao', $item->offsetGet('valorEstimado'));
                                        	
                                        	// Adiciona ao array o item do licitante
                                        	$itensOrgao = $orgao->offsetGet('itemOrgao');
                                        	$itensOrgao->append($itemOrgao);
                                        	
                                        	// Adiciona o licitante ao array de licitantes
                                        	$licitantes = $itemSolicitacao->offsetGet('licitantes');
                                        	$licitantes->append($orgao);
                                        	
                                        	// Redefine o array de licitantes
                                        	$item->offsetSet('licitantes', $licitantes);
                                        }

                                        break;
                                    }
                                }

                                // Caso não exista, adiciona o item no array
                                if ($itemExiste === false) {
                                    // Licitante
                                    $orgao = new ArrayObject();
                                    $orgao->offsetSet('id', $CodOrgao);
                                    $orgao->offsetSet('descricao', $DescOrgao);
                                    $orgao->offsetSet('itemOrgao', new ArrayObject());
                                    
                                    // Item do licitante
                                    $itemOrgao = new ArrayObject();
                                    $itemOrgao->offsetSet('idItemOrgao', $item->offsetGet('id'));
                                    $itemOrgao->offsetSet('descricaoItemOrgao', $item->offsetGet('descricao'));
                                    $itemOrgao->offsetSet('descricaoDetalhadaItemOrgao', $item->offsetGet('descricaoDetalhada'));
                                    $itemOrgao->offsetSet('ordemItemOrgao', $item->offsetGet('ordem'));
                                    $itemOrgao->offsetSet('quantidadeItemOrgao', $item->offsetGet('quantidade'));
                                    $itemOrgao->offsetSet('valorEstimadoItemOrgao', $item->offsetGet('valorEstimado'));
                                    
                                    // Adiciona ao array o item do licitante
                                    $itensOrgao = $orgao->offsetGet('itemOrgao');
                                    $itensOrgao->append($itemOrgao);

                                    // Adiciona o licitante ao array de licitantes
                                    $licitantes = $item->offsetGet('licitantes');
                                    $licitantes->append($orgao);
                                    
                                    // Redefine o array de licitantes
                                    $item->offsetSet('licitantes', $licitantes);
                                   
                                    // Adiciona o item ao array
                                    $arrayItens->append($item);
                                }
                            } // fim while itens
                        } // fim else                        
                    } // fim while

                    $html .= "<tr class='linhacentro'>\n";
                    $html .= "	<td bgcolor=\"#DDECF9\" class=\"titulo3\">ÓRGÃO GESTOR</td>";
                    $html .= "	<td bgcolor=\"#DDECF9\" colspan=\"5\" class=\"titulo3\">$nomeOrgaoGestor</td>\n";
                    $html .= "</tr>\n";
                    
                    $html .= '</table>';
                    $html .= '</td>';
                    $html .= '</tr>';
                    
                    $html .= '<tr>';
                    $html .= '<td class="textonormal" align="right" colspan="4">';
                    $html .= '<form action="" method="post" name="formulario">';
                    $html .= '<input type="button" name="ImprimirDetalhamento" value="Imprimir" class="botao" onClick="javascript:enviar(\'Imprimir\')">';
                    $html .= '<input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar(\'Voltar\')">';
					$html .= '<input type="hidden" name="Botao" value="">';
					$html .= '<input type="hidden" name="idSolicitacao" value="' . $idSolicitacao . '">';
					$html .= '</form>';
                    $html .= '</td>';
					$html .= '</tr>';
					
                    $html .= '</table>';
                    
                    $htmlItens = '';                    
                    $htmlItens .= '<tr>';
                    $htmlItens .= "<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" colspan=\"6\">";
                    $htmlItens .= '<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">';
                    $htmlItens .= '<tr><td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="6">CONSULTAR AGRUPAMENTO - SOLICITAÇÃO DE COMPRA  E CONTRATAÇÃO (SCC)</td></tr>';
                    $htmlItens .= '<tr><td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="6">ITENS SELECIONADOS</td></tr>';
                    $htmlItens .= "<tr>";
                    $htmlItens .= "<th>ORDEM</th>";
                    $htmlItens .= "<th>DESCRIÇÃO</th>";
                    $htmlItens .= "<th>DESCRIÇÃO DETALHADA</th>";
                    $htmlItens .= "<th>TIPO</th>";
                    $htmlItens .= "<th>CÓD. RED</th>";
                    $htmlItens .= "<th>QUANTIDADE</th>";
                    $htmlItens .= "</tr>";

                    foreach ($arrayItens as $item) {
                        $descricaoDetalhadaItem = $item->offsetGet('descricaoDetalhada');
                        if ($item->offsetGet('descricaoDetalhada') == "") {
                            $descricaoDetalhadaItem = '<center> - </center>';
                        }                        

                        $htmlItens .= "<tr>";
                        $htmlItens .= "<td>" . $item->offsetGet('ordem') . "<span style='cursor:pointer;margin-left:5px;margin-right:10px;' id='" . $item->offsetGet('id') . "' class='detalhar' onclick=''>+</span></td>";
                        $htmlItens .= "<td>" . $item->offsetGet('descricao') . "</td>";
                        $htmlItens .= "<td>" . $descricaoDetalhadaItem . "</td>";
                        $htmlItens .= "<td>" . $item->offsetGet('tipo') . " </td>";
                        $htmlItens .= "<td>" . $item->offsetGet('codigoReduzido') . "</td>";
                        $htmlItens .= "<td>" . converte_valor_estoques($item->offsetGet('total')) . "</td>";
                        $htmlItens .= "</tr>";
                        
                        $htmlItens .= "<tr style=\"display:none;\" class=\"opdetalhe " . $item->offsetGet('id') . "\">";
                        $htmlItens .= "<td style=\"background-color:#F1F1F1;\" colspan=\"6\">";
                        $htmlItens .= "<table bordercolor=\"#75ADE6\" border=\"1\" bgcolor=\"bfdaf2\" width=\"100%\" class=\"textonormal\">";

                        $licitantes = $item->offsetGet('licitantes');
                        foreach ($licitantes as $licitante) {
							$htmlItens .= "<tr>";
							$htmlItens .= "<td align=\"center\" bgcolor=\"#75ADE6\" valign=\"middle\" class=\"titulo3\" colspan=\"6\">";
							$htmlItens .= $licitante->offsetGet('descricao');
							$htmlItens .= "</td>";
							$htmlItens .= "</tr>";
							
							$htmlItens .= "<tr>";
							$htmlItens .= "<th>DESCRIÇÃO DETALHADA</th>";
							$htmlItens .= "<th>QUANTIDADE</th>";
							$htmlItens .= "<th>VALOR ESTIMADO</th>";
							$htmlItens .= "</tr>";
							
                            foreach ($licitante->offsetGet('itemOrgao') as $itens) {
                                $descricaoDetalhadaItemOrgao = $itens->offsetGet('descricaoDetalhadaItemOrgao');
                                if ($itens->offsetGet('descricaoDetalhadaItemOrgao') == "") {
                                    $descricaoDetalhadaItemOrgao = '<center> - </center>';
                                }

                                $htmlItens .= "<tr>";
                                $htmlItens .= "<td>" . $descricaoDetalhadaItemOrgao . "</td>";
                                $htmlItens .= "<td>" . converte_valor_estoques($itens->offsetGet('quantidadeItemOrgao')) . "</td>";
                                $htmlItens .= "<td>" . converte_valor_estoques($itens->offsetGet('valorEstimadoItemOrgao')) . "</td>";
                                $htmlItens .= "</tr>";
                            }
                        }
                        
                        $htmlItens .= "</table>";
                        $htmlItens .= "</td>";
                        $htmlItens .= "</tr>";
                    }
                    
                    $htmlItens .= "</table>";
                    $htmlItens .= "</td>";
                    $htmlItens .= "</tr>";
                    
                    $html = str_replace('{ITENS}', $htmlItens, $html);                    
                    
                    echo $html;
                } // fim else
            }
        ?>
        
    </body>
</html>

<?php $db->disconnect(); ?>