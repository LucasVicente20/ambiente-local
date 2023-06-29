<?php
#-------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadRequisicaoBaixa.php
# Objetivo: Programa de Atendimento de Requisição de Material para Localização Fixa
# Autor:    Altamiro Pedrosa
# Data:     17/08/2005
#---------------------------
# Alterado: Álvaro Faria
# Data:     26/05/2006
# Alterado: Álvaro Faria
# Data:     24/11/2006 - Suporte ao include da rotina de Custo/Contabilidade
# Alterado: Álvaro Faria
# Data:     12/12/2006 - Checagem do CC 800 além do 799
# Alterado: Álvaro Faria
# Data:     03/01/2007 - Suporte a materiais didáticos, fardamento e limpeza
# Alterado: Carlos Abreu
# Data:     17/04/2007 - Bloquear quando requisicao for atendida antes do inventario e tentar ser baixada posterior a inventario
# Alterado: Rodrigo Melo
# Data:     10/01/2008 - Ajuste na query para evitar que a movimentação seja realizada no período anterior ao último inventário
#                                do almoxarifado, ou seja, ajuste para buscar apenas o último sequencial e o último ano do inventário do almoxarifado,
#                                ou seja, correção da query utilizada para bloquear a baixa quando a requisição for atendida antes do inventário
#                                 e tentar ser baixada posterior ao inventário.
# Alterado: Rodrigo Melo
# Data:     30/04/2008 - Ajuste nas movimentações para chamar a rotina de lançamento contábil.
# Alterado: Ariston Cordeiro
# Data:     12/08/2008 - Redirecionamento para CadRequisicaoBaixaSelecionar.php quando variáveis requeridas estão em nulo (resultado de chamar esta página incorretamente)
# Alterado: Rodrigo Melo
# Data:     16/01/2009 - Alteração para permitir baixa de requisição após o inventário - CR: 663.
#--------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

#Acesso a rotina de lançamento custo/contábil
include "../oracle/estoques/RotLancamentoCustoContabil.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/estoques/CadRequisicaoConfirmarBaixa.php' );
AddMenuAcesso( '/estoques/CadRequisicaoBaixaSelecionar.php' );
AddMenuAcesso( '/estoques/CadItemDetalhe.php' );

$Virgula = 1;

# Variáveis com o global off #
if($_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao          = $_POST['Botao'];
		$Localizacao    = $_POST['Localizacao'];
		$CarregaLocalizacao = $_POST['CarregaLocalizacao'];
		$Almoxarifado   = $_POST['Almoxarifado'];
		$SeqRequisicao  = $_POST['SeqRequisicao'];
		$AnoRequisicao  = $_POST['AnoRequisicao'];
		$Requisicao     = $_POST['Requisicao'];
		$Situacao       = $_POST['Situacao'];
		$TipoUsuario    = $_POST['TipoUsuario'];
		$DataRequisicao = $_POST['DataRequisicao'];
		$GrupoEmp       = $_POST['GrupoEmp'];
		$Usuario        = $_POST['Usuario'];
		$TipoSituacao   = $_POST['TipoSituacao'];
		$DescMaterial   = $_POST['DescMaterial'];
		$UnidadeMedida  = $_POST['UnidadeMedida'];
		$DescUnidade    = $_POST['DescUnidade'];
		$Material       = $_POST['Material'];
		$QtdSolicitada  = $_POST['QtdSolicitada'];
		$QtdAtendida    = $_POST['QtdAtendida'];
		$Ordem          = $_POST['Ordem'];
		$RowsGeral      = $_POST['RowsGeral'];

		# Dados para o redirecionamento do oracle #
		$Orgao          = $_POST['Orgao'];
		$Unidade        = $_POST['Unidade'];
		$RPA            = $_POST['RPA'];
		$CentroCusto    = $_POST['CentroCusto'];
		$Detalhamento   = $_POST['Detalhamento'];
		$DiaBaixa       = $_POST['DiaBaixa'];
		$MesBaixa       = $_POST['MesBaixa'];
		$AnoBaixa       = $_POST['AnoBaixa'];
		$Matricula      = $_POST['Matricula'];
		$Responsavel    = strtoupper2(RetiraAcentos($_POST['Responsavel']));
    $AnoMovimentacao = $_POST['AnoMovimentacao'];
}else{
		$SeqRequisicao  = $_GET['SeqRequisicao'];
		$AnoRequisicao  = $_GET['AnoRequisicao'];
		$Almoxarifado   = $_GET['Almoxarifado'];
		$Mens           = $_GET['Mens'];
		$Tipo           = $_GET['Tipo'];
		$Mensagem       = $_GET['Mensagem'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# redirecionar para Selecionar, caso dados necessários para renderizar a página não forem especificados (expiração de sessão?)
if((is_null($SeqRequisicao))||(is_null($AnoRequisicao))){
		header("Location: CadRequisicaoBaixaSelecionar.php");
		exit;
}
if($Botao == "Voltar"){
		header("Location: CadRequisicaoBaixaSelecionar.php");
		exit;
}elseif( $Botao == "Baixou" ){
		$db     = Conexao();
		# Checa se o cadastro do 799 e 800 /77 do órgão corrente existe. Se não existir, emite erro #
		$sql  = "SELECT CCENPONRPA ";
		$sql .= "  FROM SFPC.TBCENTROCUSTOPORTAL ";
		$sql .= " WHERE CCENPOCORG = $Orgao and CCENPOUNID = $Unidade ";
		$sql .= "   AND (CCENPOCENT = 799 or CCENPOCENT = 800)";
		$sql .= "   AND CCENPODETA = 77 ";
		$sql .= "   AND FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
		$res  = $db->query($sql);
		if( db::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$RPAAlmox = $res->numRows();
				if($RPAAlmox < 2){
						$Mens      = 1;
						$Tipo      = 2;
						$Virgula   = 2;
						$Mensagem = "Falta Cadastrar o Centro de Custo 799 e/ou 800, detalhamento 77. Contatar o Responsável pelo Cadastramento de Centros de Custo";
				}else{
						# Resgata a soma dos valores da tabela de movimentacao do tipo saida por requisicao para aquela requisição #
						//$sql    = "SELECT DISTINCT A.CMATEPSEQU, A.AITEMRQTAT, E.FGRUMSTIPC "; //ORIGINAL
						$sql    = "SELECT DISTINCT A.CMATEPSEQU, A.AITEMRQTAT, E.FGRUMSTIPC, E.FGRUMSTIPM ";
						$sql   .= "  FROM SFPC.TBITEMREQUISICAO A, SFPC.TBMATERIALPORTAL B, SFPC.TBSUBCLASSEMATERIAL C, ";
						$sql   .= "       SFPC.TBCLASSEMATERIALSERVICO D, SFPC.TBGRUPOMATERIALSERVICO E";
						$sql   .= " WHERE A.CREQMASEQU = $SeqRequisicao AND A.CMATEPSEQU = B.CMATEPSEQU ";
						$sql   .= "   AND B.CSUBCLSEQU = C.CSUBCLSEQU AND C.CGRUMSCODI = D.CGRUMSCODI ";
						$sql   .= "   AND C.CCLAMSCODI = D.CCLAMSCODI AND D.CGRUMSCODI = E.CGRUMSCODI ";
						$sql   .= " ORDER BY E.FGRUMSTIPC ";
						$result = $db->query($sql);
						if( db::isError($result) ){
								$ErroSelect = 1;
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
              //Preparando os parâmetros para o lançamento custo/contábil
              $SubElementosDespesa = array(); //Array que contém os sub-elementos de despesa
              $ValoresSubelementos = array(); //Valores dos sub-elementos de despesa
              $EspecificacoesContabeis = array();  //Array que contém os valores: 'C' para Consumo ou 'P' para permanente
              $ValoresContabeis = array();  //valores contábeis conforme o tipo do material (Consumo ou Permanente)

              while( $Linha = $result->fetchRow() ){
                $Material      = $Linha[0];
                $QtdAtendida   = $Linha[1];
                $TipoMaterial  = $Linha[2];
                $TipoMaterialTESTE = $Linha[3];
                # Pega o Último Valor do Material de Acordo com o Atendimento da Requisição #
                $GrupoEmp = $_SESSION['_cgrempcodi_'];
                $sql   = "SELECT VMOVMAVALO FROM SFPC.TBMOVIMENTACAOMATERIAL ";
                $sql  .= " WHERE CTIPMVCODI IN (4,19,20) AND CMATEPSEQU = $Material AND";
                $sql  .= "       CREQMASEQU = $SeqRequisicao AND CGREMPCODI = $GrupoEmp ";
                $sql  .= "   AND (FMOVMASITU IS NULL OR FMOVMASITU = 'A' )"; // Apresentar só as movimentações ativas
                $sql  .= " ORDER BY TMOVMAULAT DESC";
                $res  = $db->query($sql);
                if( db::isError($res) ){
                    $ErroSelect = 1;
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                }else{
                  $LinhaVal   = $res->fetchRow();
                  $ValorMedio = $LinhaVal[0];


                  //Obtendo os Sub-elementos de despesa
                  $sql  = "SELECT DISTINCT GSE.CGRUSEELE1, GSE.CGRUSEELE2, GSE.CGRUSEELE3, ";
                  $sql .= "  GSE.CGRUSEELE4, GSE.CGRUSESUBE ";
                  $sql .= " FROM SFPC.TBMATERIALPORTAL MAT ";
                  $sql .= " LEFT OUTER JOIN SFPC.TBSUBCLASSEMATERIAL SUB ";
                  $sql .= "  ON MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
                  $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOMATERIALSERVICO GRU ";
                  $sql .= "  ON SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
                  $sql .= " LEFT OUTER JOIN SFPC.TBGRUPOSUBELEMENTODESPESA GSE ";
                  $sql .= "  ON GRU.CGRUMSCODI = GSE.CGRUMSCODI ";
                  $sql .= " WHERE (GSE.FGRUSENATU = 'S' OR GSE.FGRUSENATU IS NULL)AND ";
                  $sql .= "       (GSE.FGRUSESITU = 'A' OR GSE.FGRUSESITU IS NULL)AND ";
                  $sql .= "       (GSE.AGRUSEANOI = $AnoBaixa OR  GSE.AGRUSEANOI IS NULL) AND ";
                  $sql .= "       MAT.CMATEPSEQU = $Material ";

                  $res  = $db->query($sql);

                  if( db::isError($res) ){
                      $RollBack = 1;
                      $db->query("ROLLBACK");
                      $db->query("END TRANSACTION");
                      $db->disconnect();
                      ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                  } else {
                    $Linha = $res->fetchRow();
                    $CGRUSEELE1 = $Linha[0];
                    $CGRUSEELE2 = $Linha[1];
                    $CGRUSEELE3 = $Linha[2];
                    $CGRUSEELE4 = $Linha[3];
                    $CGRUSESUBE = $Linha[4];

                    if($CGRUSEELE1 != null && $CGRUSEELE2 != null && $CGRUSEELE3 != null && $CGRUSEELE4 != null && $CGRUSESUBE != null){
                      $Subelemento = "$CGRUSEELE1.$CGRUSEELE2.$CGRUSEELE3.$CGRUSEELE4.$CGRUSESUBE";
                      $ValorSubElemento = $QtdAtendida * $ValorMedio;

                      if(!in_array($Subelemento, $SubElementosDespesa)){
                        $indice = count($SubElementosDespesa);
                        $SubElementosDespesa[$indice] = $Subelemento;
                        $ValoresSubelementos[$indice] = $ValorSubElemento;
                      } else {
                        $indExist = array_search ($Subelemento, $SubElementosDespesa); //Equivale ao indExist: indice existente.
                        $ValoresSubelementos[$indExist] = $ValoresSubelementos[$indExist] + $ValorSubElemento;
                      }
                    } else {
                      # EXIBINDO MENSAGEM DE ERRO - Pois o grupo do material não está integrado a nenhum sub-elemento de despesa #
                      $Mensagem = urlencode("O grupo do Material (Cod. Red: $Material) não possui integração com Sub-elemento(s)");
                      $Url = "estoques/CadRequisicaoBaixaSelecionar.php?Mens=1&Tipo=2&Mensagem=$Mensagem";
                      if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
                      RedirecionaPost($Url);
                      exit;
                    }
                  }

                  $ValorContabilTESTE = ($QtdAtendida * $ValorMedio);

                  if(!in_array($TipoMaterialTESTE, $EspecificacoesContabeis)){
                    $indice = count($EspecificacoesContabeis);
                    $EspecificacoesContabeis[$indice] = $TipoMaterialTESTE;
                    $ValoresContabeis[$indice] = $ValorContabilTESTE;
                  } else {
                    $indExist = array_search ($TipoMaterialTESTE, $EspecificacoesContabeis); //Equivale ao indExist: indice existente.
                    $ValoresContabeis[$indExist] = $ValoresContabeis[$indExist] + $ValorContabilTESTE;
                  }
                }
              }
						}
				}
				if($ErroSelect != 1 and $Mens == 0){
						# Verifica se a requisição já foi baixada. Correção para o caso de duas pessoas chegarem
						# a tela de baixa simutaneamente, trabalhando em computadores diferentes, com a mesma
						# requisição (os dois clicando na tela de seleção antes da primeira baixa).
						# Caso um deles baixe, o segundo não poderá baixar novamente, exibindo erro
						$sqltestaatend   = "SELECT MAX(CTIPSRCODI) FROM SFPC.TBSITUACAOREQUISICAO ";
						$sqltestaatend  .= " WHERE CREQMASEQU = $SeqRequisicao";
						$restestaatend   = $db->query($sqltestaatend);
						if( db::isError($restestaatend) ){
								$db->disconnect();
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\n$Inicio\nSql: $sqltestaatend");
								exit;
						}else{
								$LinhaTestaAtend = $restestaatend->fetchRow();
								if($LinhaTestaAtend[0] == 5){ // 5 - Requisição Baixada
										$Mensagem = urlencode("Esta Requisição já foi Baixada. Verifique no Acompanhamento de Requisição");
										$Url = "estoques/CadRequisicaoBaixaSelecionar.php?Mens=1&Tipo=2&Mensagem=$Mensagem";
										if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
										RedirecionaPost($Url);
										exit;
								}else{
										# Carrega dados necessários para estoque e custo #
										$GrupoEmp             = $_SESSION['_cgrempcodi_'];
										$Usuario              = $_SESSION['_cusupocodi_'];
										$TimeStamp            = date("Y-m-d H:i:s");
										# Insere a Situação da Requisição #
										$db->query("BEGIN TRANSACTION");
										$sql  = "INSERT INTO SFPC.TBSITUACAOREQUISICAO ( ";
										$sql .= "CREQMASEQU, CTIPSRCODI, TSITRESITU, ";
										$sql .= "CGREMPCODI, CUSUPOCODI, TSITREULAT ";
										$sql .= ") VALUES ( ";
										$sql .= "$SeqRequisicao, 5 , '$TimeStamp', ";
										$sql .= "$GrupoEmp, $Usuario, '$TimeStamp' )";
										$result = $db->query($sql);
										if(db::isError($result)){
												$CodErroEmail  = $result->getCode();
												$DescErroEmail = $result->getMessage();
												$db->query("ROLLBACK");
												$db->query("END TRANSACTION");
												$db->disconnect();
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\n$Inicio\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
												exit;
										}else{
												$DataBaixa = $AnoBaixa."-".$MesBaixa."-".$DiaBaixa;
												$sql  = "UPDATE SFPC.TBREQUISICAOMATERIAL ";
												$sql .= "   SET AREQMAMATR = $Matricula, NREQMANOMR = '$Responsavel', ";
												$sql .= "       DREQMARECE = '$DataBaixa', CGREMPCODI = $GrupoEmp, ";
												$sql .= "       CUSUPOCODI = $Usuario, TREQMAULAT = '$TimeStamp' ";
												$sql .= " WHERE CREQMASEQU = $SeqRequisicao ";
												$result = $db->query($sql);
												if(db::isError($result)){
														$CodErroEmail  = $result->getCode();
														$DescErroEmail = $result->getMessage();
														$db->query("ROLLBACK");
														$db->query("END TRANSACTION");
														$db->disconnect();
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\n$Inicio\nSql: $sql\n\n$DescErroEmail ($CodErroEmail)");
														exit;
												}else{
														# Redireciona para o Oracle #
														$Movimentacao = 4; // Baixa de requisição
														$ProgramaDestino = "CadRequisicaoBaixa.php";

                            # Abre a Conexão com Oracle - para realizar os lançamentos custo e contábil#
                            $dbora = ConexaoOracle();

                            # Evita que Rollback não funcione #
                            $dbora->autoCommit(false);

                            # Inicia transação Oracle - Para inserir dados na tabela SFCP.TBMOVCUSTOALMOXARIFADO e SFCT.TBMOVCONTABILALMOXARIFADO #
                            $dbora->query("BEGIN TRANSACTION");

                            $ConfirmarInclusao = true;

                            //ORIGINAL
                            // GerarLancamentoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                                   // $Movimentacao, $TipoMaterialTESTE,
                                   // $AnoBaixa, $MesBaixa, $DiaBaixa, $ValorContabil,
                                   // $Matricula, $Responsavel,
                                   // $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                                   // $ProgramaDestino, $dbora, $db, $ConfirmarInclusao,
                                   // $SubElementosDespesa, $ValoresSubelementos,
                                   // $EspecificacoesContabeis, $ValoresContabeis);
                            //ORIGINAL

                          //TESTE 3
                          GerarLancamentoCustoContabil($Orgao, $RPA, $Unidade, $CentroCusto, $Detalhamento,
                                 $Movimentacao, $AnoBaixa, $MesBaixa, $DiaBaixa,
                                 $EspecificacoesContabeis, $ValoresContabeis,
                                 $SubElementosDespesa, $ValoresSubelementos,
                                 $Matricula, $Responsavel,
                                 $SeqRequisicao, $Almoxarifado, $MovNumero, $AnoMovimentacao,
                                 $ProgramaDestino, $dbora, $db, $ConfirmarInclusao);
                          //FIM TESTE 3
														exit;
												}
										}
								}
						}
				}
		}
}elseif($Botao == "Baixar"){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if($Almoxarifado == ""){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadRequisicaoBaixa.Almoxarifado.focus();\" class=\"titulo2\">Almoxarifado</a>";
		}
		if( ($Localizacao == "") && ($CarregaLocalizacao == 'N') ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "Localização";
		}elseif($Localizacao == ""){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadRequisicaoBaixa.Localizacao.focus();\" class=\"titulo2\">Localização</a>";
		}

		if($Mens == 0){
				$DifZero = 0;
				foreach($QtdAtendida as $QtdAtend){
						if($QtdAtend <> 0) $DifZero = 1;
				}
				if($DifZero == "0"){
						$Mensagem = "Atenção: ";
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "A requisição deve ter algum item com quantidade atendida para proceder a baixa";
				}else{
						$ProgramaOrigem = urlencode("CadRequisicaoBaixa");
						$Url = "CadRequisicaoConfirmarBaixa.php?ProgramaOrigem=$ProgramaOrigem&SeqRequisicao=$SeqRequisicao&Localizacao=$Localizacao";
						if(!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						echo "<script>window.open('$Url','pagina','status=no,scrollbars=yes,left=140,top=150,width=550,height=250');</script>";
				}
		}
}elseif($Botao == ""){
			# Pega os dados da Requisição de Material de acordo com o Sequencial #
			$db   = Conexao();
			$sql  = "SELECT DISTINCT(REQ.CREQMACODI), REQ.DREQMADATA, ITE.AITEMRQTSO, ITE.AITEMRQTAP, ";
			$sql .= "       ITE.AITEMRQTAT, ITE.AITEMRQTCA, ITE.AITEMRORDE, MAT.CMATEPSEQU, ";
			$sql .= "       MAT.EMATEPDESC, UND.CUNIDMCODI, UND.EUNIDMSIGL ";
			$sql .= "  FROM SFPC.TBREQUISICAOMATERIAL REQ ";
			$sql .= " INNER JOIN SFPC.TBSITUACAOREQUISICAO SITREQ ON (REQ.CREQMASEQU = SITREQ.CREQMASEQU) ";
			$sql .= " INNER JOIN SFPC.TBTIPOSITUACAOREQUISICAO TIPSIT ON (TIPSIT.CTIPSRCODI = SITREQ.CTIPSRCODI) ";
			$sql .= " INNER JOIN SFPC.TBITEMREQUISICAO ITE ON (REQ.CREQMASEQU = ITE.CREQMASEQU) ";
			$sql .= " INNER JOIN SFPC.TBMATERIALPORTAL MAT ON (ITE.CMATEPSEQU = MAT.CMATEPSEQU) ";
			$sql .= " INNER JOIN SFPC.TBUNIDADEDEMEDIDA UND ON (MAT.CUNIDMCODI = UND.CUNIDMCODI) ";
			$sql .= " WHERE REQ.AREQMAANOR = $AnoRequisicao AND REQ.CREQMASEQU = $SeqRequisicao ";
			$sql .= "   AND TIPSIT.CTIPSRCODI IN (3,4) ";
			$sql .= " ORDER BY ITE.AITEMRORDE ";
			$res  = $db->query($sql);
			if( db::isError($res) ){
					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
			}else{
					$RowsGeral = $res->numRows();
					for( $i=0;$i<$RowsGeral;$i++ ){
							$Linha              = $res->fetchRow();
							$Requisicao         = $Linha[0];
							$DataRequisicao     = DataBarra($Linha[1]);
							$QtdSolicitada[$i]  = $Linha[2];
							$QtdAprovada[$i]    = $Linha[3];
							$QtdAtendida[$i]    = $Linha[4];
							$QtdCancelada[$i]   = $Linha[5];
							$Ordem[$i]          = $Linha[6];
							$Material[$i]       = $Linha[7];
							$DescMaterial[$i]   = $Linha[8];
							$UnidadeMedida[$i]  = $Linha[9];
							$DescUnidade[$i]    = $Linha[10];
					}
			}
			$db->disconnect();
}
?>
<html>
<?
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" type="">
function enviar(valor){
	document.CadRequisicaoBaixa.Botao.value = valor;
	document.CadRequisicaoBaixa.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=60,top=150,width='+largura+',height='+altura);
}
<?php MenuAcesso(); ?>
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadRequisicaoBaixa.php" method="post" name="CadRequisicaoBaixa">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Estoques > Requisição > Baixa
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
		<td width="150"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,$Virgula); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="150"></td>
		<td class="textonormal">
			<table  border="0" cellspacing="0" cellpadding="3" width="100%" summary="">
				<tr>
					<td class="textonormal">
						<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" width="100%" bgcolor="#FFFFFF" summary="">
							<tr>
								<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
									BAIXA - REQUISIÇÃO DE MATERIAL
								</td>
							</tr>
							<tr>
								<td class="textonormal">
									<p align="justify">
										Para efetuar a baixa da Requisição de Material, clique no botão "Baixar". Para voltar para a janela anterior, clique no botão "Voltar".
									</p>
								</td>
							</tr>
							<tr>
								<td>
									<table class="textonormal" border="0" align="left" width="100%" summary="">
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Almoxarifado</td>
											<td class="textonormal">
												<?php
												$db   = Conexao();
												$sql  = "SELECT EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL WHERE CALMPOCODI = $Almoxarifado";
												$res  = $db->query($sql);
												if( db::isError($res) ){
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $res->fetchRow();
														echo "$Linha[0]<br>";
														echo "<input type=\"hidden\" name=\"Almoxarifado\" value=\"$Almoxarifado\">";
												}
												$db->disconnect();
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Centro de Custo</td>
											<td class="textonormal">
												<?php
												$db     = Conexao();
												$sql    = "SELECT A.ECENPODESC, B.EORGLIDESC, A.CCENPONRPA, A.ECENPODETA ";
												$sql   .= "FROM SFPC.TBCENTROCUSTOPORTAL A, SFPC.TBORGAOLICITANTE B, SFPC.TBREQUISICAOMATERIAL C ";
												$sql   .= "WHERE A.CORGLICODI = B.CORGLICODI AND A.CCENPOSEQU = C.CCENPOSEQU AND ";
												$sql   .= "C.CREQMASEQU = $SeqRequisicao ";
												$sql   .= "   AND A.FCENPOSITU <> 'I' "; // Inclusão da condição para mostrar centro de custos diferentes de inativos
												$sql   .= "ORDER BY B.EORGLIDESC, A.ECENPODESC ";
												$result = $db->query($sql);
												if( db::isError($result) ) {
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $result->fetchRow();
														echo $Linha[1]."<br>&nbsp;&nbsp;&nbsp;&nbsp;RPA ".$Linha[2]."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$Linha[0]."<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$Linha[3];
												}
												$db->disconnect();
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Requisição</td>
											<td class="textonormal"><?php echo substr($Requisicao+100000,1)."/".$AnoRequisicao; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Usuário Requisitante</td>
											<td class="textonormal">
											<?php
											# Carrega os dados do usuário que fez o requerimento. Nome do usuário em SFPC.TBUSUARIOPORTAL quando a situação for 1 em SFPC.TBSITUACAOREQUISICAO, ou seja, em análise #
											$db     = Conexao();
											$sql    = "SELECT USU.EUSUPORESP ";
											$sql   .= "  FROM SFPC.TBUSUARIOPORTAL USU, SFPC.TBSITUACAOREQUISICAO SIT ";
											$sql   .= " WHERE SIT.CREQMASEQU = $SeqRequisicao AND SIT.CTIPSRCODI = 1 ";
											$sql   .= "   AND USU.CGREMPCODI = SIT.CGREMPCODI AND USU.CUSUPOCODI = SIT.CUSUPOCODI ";
											$result = $db->query($sql);
											if( db::isError($result) ){
													ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
											}else{
													$Linha = $result->fetchRow();
													$Nome  = $Linha[0];
													echo $Nome;
											}
											?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Data da Requisição</td>
											<td class="textonormal"><?php echo $DataRequisicao; ?></td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Situação</td>
											<td class="textonormal">
												<?php
												# Mostra a situação da Requisição #
												$db     = Conexao();
												$sql    = "SELECT ETIPSRDESC FROM SFPC.TBREQUISICAOMATERIAL REQMAT ";
												$sql   .= " INNER JOIN SFPC.TBSITUACAOREQUISICAO SITREQ ON (REQMAT.CREQMASEQU = SITREQ.CREQMASEQU) ";
												$sql   .= " INNER JOIN SFPC.TBTIPOSITUACAOREQUISICAO TIPSIT ON (TIPSIT.CTIPSRCODI = SITREQ.CTIPSRCODI) ";
												$sql   .= " WHERE REQMAT.CREQMASEQU = $SeqRequisicao AND TIPSIT.CTIPSRCODI IN (3,4) ";
												$result = $db->query($sql);
												if( db::isError($result) ) {
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $result->fetchRow();
														echo $Linha[0];
												}
												$db->disconnect();
												?>
											</td>
										</tr>
										<?php if( $Almoxarifado != "" ){ ?>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="30%">Localização*</td>
											<td class="textonormal">
												<?php
												$db = Conexao();
												if($Localizacao != ""){
														# Mostra a Descrição de Acordo com o Almoxarifado #
														$sql    = "SELECT A.FLOCMAEQUI, A.ALOCMANEQU, A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
														$sql   .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B";
														$sql   .= " WHERE A.CLOCMACODI = $Localizacao AND A.FLOCMASITU = 'A'";
														$sql   .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
														$res  = $db->query($sql);
														if( db::isError($res) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Linha = $res->fetchRow();
																if( $Linha[0] == "E" ){
																		$Equipamento = "ESTANTE";
																}if( $Linha[0] == "A" ){
																		$Equipamento = "ARMÁRIO";
																}if( $Linha[0] == "P" ){
																		$Equipamento = "PALETE";
																}
																$DescArea = $Linha[4];
																echo "ÁREA: $DescArea - $Equipamento - $Linha[1]: ESCANINHO $Linha[2]$Linha[3]";
																echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
														}
												}else{
														# Mostra as Localizações de acordo com o Almoxarifado #
														$sql    = "SELECT A.CLOCMACODI, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU, B.EARLOCDESC ";
														$sql   .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBAREAALMOXARIFADO B ";
														$sql   .= " WHERE A.CALMPOCODI = $Almoxarifado AND A.FLOCMASITU = 'A'";
														$sql   .= "   AND A.CARLOCCODI = B.CARLOCCODI	";
														$sql   .= " ORDER BY B.EARLOCDESC DESC, A.FLOCMAEQUI, A.ALOCMANEQU, ";
														$sql   .= "       A.ALOCMAPRAT, A.ALOCMACOLU";
														$res  = $db->query($sql);
														if( db::isError($res) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
														}else{
																$Rows = $res->numRows();
																if( $Rows == 0 ){
																		echo "NENHUMA LOCALIZAÇÃO CADASTRADA PARA ESTE ALMOXARIFADO";
																		echo "<input type=\"hidden\" name=\"CarregaLocalizacao\" value=\"N\">";
																}else{
																		if( $Rows == 1 ){
																				$Linha = $res->fetchRow();
																				if( $Linha[1] == "E" ){
																						$Equipamento = "ESTANTE";
																				}if( $Linha[1] == "A" ){
																						$Equipamento = "ARMÁRIO";
																				}if( $Linha[1] == "P" ){
																						$Equipamento = "PALETE";
																				}
																				echo "ÁREA: $Linha[5] - $Equipamento - $Linha[2]: ESCANINHO $Linha[3]$Linha[4]";
																				$Localizacao = $Linha[0];
																				echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
																		}else{
																				if( $Rows == 1 ){
																						$Linha = $res->fetchRow();
																						if( $Linha[1] == "E" ){
																								$Equipamento = "ESTANTE";
																						}if( $Linha[1] == "A" ){
																								$Equipamento = "ARMÁRIO";
																						}if( $Linha[1] == "P" ){
																								$Equipamento = "PALETE";
																						}
																						echo "ÁREA: $Linha[5] - $Equipamento - $Linha[2]: ESCANINHO $Linha[3]$Linha[4]";
																						$Localizacao = $Linha[0];
																						echo "<input type=\"hidden\" name=\"Localizacao\" value=\"$Localizacao\">";
																				}else{
																						echo "<select name=\"Localizacao\" class=\"textonormal\" onChange=\"submit();\">\n";
																						echo "	<option value=\"\">Selecione uma Localização...</option>\n";
																						$EquipamentoAntes = "";
																						$DescAreaAntes    = "";
																						for( $i=0;$i< $Rows; $i++ ){
																								$Linha = $res->fetchRow();
																								$CodEquipamento = $Linha[2];
																								if( $Linha[1] == "E" ){
																										$Equipamento = "ESTANTE";
																								}if( $Linha[1] == "A" ){
																										$Equipamento = "ARMÁRIO";
																								}if( $Linha[1] == "P" ){
																										$Equipamento = "PALETE";
																								}
																								$NumeroEquip = $Linha[2];
																								$Prateleira  = $Linha[3];
																								$Coluna      = $Linha[4];
																								$DescArea    = $Linha[5];
																								if( $DescAreaAntes != $DescArea ){
																										echo"<option value=\"\">$DescArea</option>\n";
																										$Edentecao = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
																								}
																								if( $CodEquipamentoAntes != $CodEquipamento or $EquipamentoAntes != $Equipamento ){
																										echo"<option value=\"\">$Edentecao $Equipamento - $NumeroEquip</option>\n";
																								}
																								if( $Localizacao == $Linha[0] ){
																										echo"<option value=\"$Linha[0]\" selected>$Edentecao $Edentecao ESCANINHO $Prateleira$Coluna</option>\n";
																								}else{
																										echo"<option value=\"$Linha[0]\">$Edentecao $Edentecao ESCANINHO $Prateleira$Coluna</option>\n";
																								}
																								$DescAreaAntes       = $DescArea;
																								$CodEquipamentoAntes = $CodEquipamento;
																								$EquipamentoAntes    = $Equipamento;
																						}
																						echo "</select>\n";
																						$CarregaLocalizacao = "";
																				}
																		}
																}
														}
												}
												$db->disconnect();
												?>
											</td>
										</tr>
										<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20">Observação</td>
												<?php
												$db     = Conexao();
												$sql    = "SELECT A.EREQMAOBSE ";
												$sql   .= "  FROM SFPC.TBREQUISICAOMATERIAL A ";
												$sql   .= " WHERE A.CREQMASEQU = $SeqRequisicao";
												$result = $db->query($sql);
												if( db::isError($result) ) {
														ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
												}else{
														$Linha = $result->fetchRow();
														$Observacao = $Linha[0];
												}
												$db->disconnect();
												?>
											<td class="textonormal"><?php if( $Observacao != "" ){ echo $Observacao; }else{ echo "NÃO INFORMADA"; }?></td>
										</tr>
										<?php
										}
										if( $Almoxarifado != "" and $Localizacao != ""){
										?>
										<tr>
											<td class="textonormal" colspan="4">
												<table border="1" cellpadding="3" cellspacing="0" bgcolor="#bfdaf2" bordercolor="#75ADE6" width="100%" summary="">
													<tr>
														<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="7">
															ITENS DA REQUISIÇÃO
														</td>
													</tr>
													<?php
													for( $i=0;$i< count($Material);$i++ ){
															if( $i == 0 ){
																	echo "		<tr>\n";
																	echo "		  <td class=\"textoabason\" bgcolor=\"#DCEDF7\" rowspan=\"2\" align=\"center\">ORDEM</td>\n";
																	echo "		  <td class=\"textoabason\" bgcolor=\"#DCEDF7\" rowspan=\"2\">DESCRIÇÃO DO MATERIAL</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" rowspan=\"2\" align=\"center\" width=\"5%\">UNIDADE</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" colspan=\"4\" align=\"center\" width=\"10%\" colspan=\"4\">QUANTIDADE</td>\n";
																	echo "		</tr>\n";
																	echo "		<tr>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"5%\" align=\"center\">SOLICITADA</td>\n";
																	echo "			<td class=\"textoabason\" bgcolor=\"#DCEDF7\" width=\"5%\" align=\"center\">ATENDIDA</td>\n";
																	echo "		</tr>\n";
															}
													?>
													<tr>
														<td class="textonormal" align="center">
															<?php echo $Ordem[$i];?>
															<input type="hidden" name="Ordem[<?php echo $i; ?>]" value="<?php echo $Ordem[$i]; ?>">
														</td>
														<td class="textonormal">
															<?
															$Url = "CadItemDetalhe.php?Material=$Material[$i]";
															if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
															?>
															<a href="javascript:AbreJanela('<?=$Url;?>',700,350);"><font color="#000000"><?php echo $DescMaterial[$i];?></font></a>
															<input type="hidden" name="DescMaterial[<?php echo $i; ?>]" value="<?php echo $DescMaterial[$i]; ?>">
															<input type="hidden" name="Material[<?php echo $i; ?>]" value="<?php echo $Material[$i]; ?>">
														</td>
														<td class="textonormal" align="center">
															<?php echo $DescUnidade[$i];?>
															<input type="hidden" name="DescUnidade[<?php echo $i; ?>]" value="<?php echo $DescUnidade[$i]; ?>">
														</td>
														<td class="textonormal" align="right">
															<?php if( $QtdSolicitada[$i] == "" ){ echo 0; }else{ echo converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdSolicitada[$i]))); } ?>
															<input type="hidden" name="QtdSolicitada[<?php echo $i; ?>]" value="<?php echo $QtdSolicitada[$i]; ?>">
														</td>
														<td class="textonormal" align="right">
															<?php if( $QtdAtendida[$i] == "" ){ echo 0; }else{ echo converte_quant(sprintf("%01.2f",str_replace(",",".",$QtdAtendida[$i]))); } ?>
															<input type="hidden" name="QtdAtendida[<?php echo $i; ?>]" value="<?php echo $QtdAtendida[$i]; ?>">
														</td>
													</tr>
													<?php } ?>
												</table>
											</td>
										</tr>
										<?php } ?>
									</table>
								</td>
							</tr>
							<tr>
								<td class="textonormal" align="right">
									<input type="hidden" name="GrupoEmp" value="<?php echo $GrupoEmp; ?>">
									<input type="hidden" name="Usuario" value="<?php echo $Usuario; ?>">
									<input type="hidden" name="TipoSituacao" value="<?php echo $TipoSituacao; ?>">
									<input type="hidden" name="DataRequisicao" value="<?php echo $DataRequisicao; ?>">
									<input type="hidden" name="AnoRequisicao" value="<?php echo $AnoRequisicao; ?>">
									<input type="hidden" name="Requisicao" value="<?php echo $Requisicao; ?>">
									<input type="hidden" name="SeqRequisicao" value="<?php echo $SeqRequisicao; ?>">
									<input type="hidden" name="Almoxarifado" value="<?php echo $Almoxarifado; ?>">
									<input type="hidden" name="Orgao" value="<?php echo $Orgao; ?>">
									<input type="hidden" name="Unidade" value="<?php echo $Unidade; ?>">
									<input type="hidden" name="RPA" value="<?php echo $RPA; ?>">
									<input type="hidden" name="CentroCusto" value="<?php echo $CentroCusto; ?>">
									<input type="hidden" name="Detalhamento" value="<?php echo $Detalhamento; ?>">
									<input type="hidden" name="DiaBaixa" value="<?php echo $DiaBaixa; ?>">
									<input type="hidden" name="MesBaixa" value="<?php echo $MesBaixa; ?>">
									<input type="hidden" name="AnoBaixa" value="<?php echo $AnoBaixa; ?>">
									<input type="hidden" name="Matricula" value="<?php echo $Matricula; ?>">
									<input type="hidden" name="Responsavel" value="<?php echo $Responsavel; ?>">
                  <input type="hidden" name="AnoMovimentacao" value="<?php echo $AnoMovimentacao; ?>">
									<input type="button" name="Baixar" value="Baixar" class="botao" onClick="javascript:enviar('Baixar');">
									<input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar('Voltar');">
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
