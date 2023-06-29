<?php
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadSubstituicaoMaterial.php
# Autor:    Carlos Abreu
# Data:     06/06/2006
# Objetivo: Programa de transferencia de subclasse de um material
#-----------------------------
# Alterado: Álvaro Faria
# Data:     29/08/2006 - Opção de pesquisa de material com descrição "iniciada por"
# Alterado: Wagner Barros
# Data:     02/10/2006 - Exibir o código reduzido do material ao lado da descrição"
# Alterado: Rossana Lira
# Data:     04/05/2007 - Incluir na transferência a tabela TBINVENTARIOREGISTRO
#                                       - Exibir lista de almoxarifado para restringir alteração se necessário
# Alterado: Rodrigo Melo
# Data:     23/11/2007 - Correção do material na situação de substituição por outro material para atualizar também a tabela TBPREMATERIAL.
# Alterado: Rodrigo Melo
# Data:     01/02/2008 - Alteração para que o material seja substituído também na tabela tbhistoricomaterial quando o material for substituído. E permitir
#                                 que o usuário apenas substitua os materiais que pertençam ao mesmo grupo.
# Alterado: Rodrigo Melo
# Data:     18/02/2008 - Alteração para que o material de origem (material a ser substituído) não seja excluído, mas inativo.
#                                 Para preservar integridade com o SOFIN e CADUM. Alterando a substituição do histórico do material (tbhistoricomaterial), pois,
#                                 já que o material não será mais excluído o seu histórico permanecerá.
# Alterado: Rodrigo Melo
# Data: 	03/03/2008 - Não permitir que o usuário substitua um material por outro material inativo ou por ele mesmo e remoção da integração com a tabela de histórico.
# Alterado: Ariston Cordeiro
# Data: 	13/05/2009 - Adicionar TBITEMSOLICICAOCOMPRA às tabelas em que o material é substituído.
# Alterado: Rodrigo Melo
# Data:     22/09/2009 - Alterando o nome das tabelas TBPREMATERIAL e TBPREMATERIALTIPOSITUACAO para TBPREMATERIALSERVICO e BPREMATERIALSERVICOTIPOSITUACAO, respectivamente (CR 2749).
#-------------------
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------
# Alterado: Pitang Agile IT - Caio Coutinho
# Data:     18/12/2018
# Objetivo: 207930
#----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/CadCorrecaoMaterialSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao    												= $_POST['Botao'];
		$TipoMaterial 										= $_POST['TipoMaterial'];
		$Grupo   													= $_POST['Grupo'];
		$DescGrupo   											= $_POST['DescGrupo'];
    $CodigoGrupo                      = $_POST['CodigoGrupo'];
		$Classe    												= $_POST['Classe'];
		$DescClasse    										= $_POST['DescClasse'];
		$Subclasse    										= $_POST['Subclasse'];
		$DescSubclasse  									= $_POST['DescSubclasse'];
		$Unidade  												= $_POST['Unidade'];
		$Material  			  								= $_POST['Material'];
		$DescMaterial   									= $_POST['DescMaterial'];
		$DescMaterialComp									= $_POST['DescMaterialComp'];
		$DescUnidade   										= $_POST['DescUnidade'];

		# Pesquisa direta #
		$OpcaoPesquisaMaterialDestino			= $_POST['OpcaoPesquisaMaterialDestino'];
		$OpcaoPesquisaSubClasseDestino		= $_POST['OpcaoPesquisaSubClasseDestino'];
		$SubclasseDescricaoDiretaDestino  = strtoupper(trim($_POST['SubclasseDescricaoDiretaDestino']));
		$MaterialDescricaoDiretaDestino   = strtoupper(trim($_POST['MaterialDescricaoDiretaDestino']));

		$TipoMaterialDestino							= $_POST['TipoMaterialDestino'];
		$GrupoDestino											= $_POST['GrupoDestino'];
		$ClasseDestino										= $_POST['ClasseDestino'];
		$SubclasseDestino									= $_POST['SubclasseDestino'];
		$MaterialDestino									= $_POST['MaterialDestino'];
		$Confirmar												= $_POST['Confirmar'];
		$CodigoReduzido 				 				  = $_POST['CodigoReduzido'];
		$AlmoxarifadoSel 				 				  = $_POST['AlmoxarifadoSel'];

}else{
		$Grupo     				= $_GET['Grupo'];
		$Classe    				= $_GET['Classe'];
		$Subclasse 				= $_GET['Subclasse'];
		$Material  				= $_GET['Material'];
}

/* Pega um novo sequencial de SFPC.TBCORRECAOMATERIAL */
function CorrecaoMax( $db ){
		/* Pega último sequencial com o incremento */
		$sql  = "SELECT MAX( ACORMASEQU ) ";
		$sql .= "  FROM SFPC.TBCORRECAOMATERIAL ";
		$sql .= " WHERE ACORMAANOC = ".date("Y")."";
		$res  = $db->query($sql);
		if( PEAR::isError($res) ){
			  $db->query("ROLLBACK");
			  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql" );
				$Erro = 1;
		}else{
				$CorrecaoSequ = $res->fetchRow();
				$CorrecaoSequ = $CorrecaoSequ[0];
		}
		return $CorrecaoSequ + 1;
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Monta o sql para montagem dinâmica da grade a partir da pesquisa #
$sql    = "SELECT DISTINCT(GRU.CGRUMSCODI),GRU.EGRUMSDESC,CLA.CCLAMSCODI,CLA.ECLAMSDESC,";
$sql   .= "       SUB.CSUBCLSEQU,SUB.ESUBCLDESC,MAT.CMATEPSEQU,MAT.EMATEPDESC,UND.EUNIDMSIGL,GRU.FGRUMSTIPM, MAT.CMATEPSEQU ";
$from   = "  FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, ";
$from  .= "       SFPC.TBSUBCLASSEMATERIAL SUB,SFPC.TBUNIDADEDEMEDIDA UND ";
$where  = " WHERE MAT.CSUBCLSEQU = SUB.CSUBCLSEQU AND SUB.CGRUMSCODI = CLA.CGRUMSCODI ";
$where .= "   AND SUB.CCLAMSCODI = CLA.CCLAMSCODI AND CLA.CGRUMSCODI = GRU.CGRUMSCODI ";
$where .= "   AND MAT.CUNIDMCODI = UND.CUNIDMCODI AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU ";
$where .= "   AND GRU.FGRUMSSITU = 'A' AND CLA.FCLAMSSITU = 'A' AND SUB.FSUBCLSITU = 'A' AND MAT.CMATEPSITU = 'A' "; //Não permite que o material seja substituido por materiais inativos (MAT.CMATEPSITU = 'A').
$where .= "   AND MAT.CMATEPSEQU <> $Material "; //Não permite que o material seja substituído por ele mesmo.
$order  = " ORDER BY GRU.FGRUMSTIPM, GRU.EGRUMSDESC, CLA.ECLAMSDESC, SUB.ESUBCLDESC, MAT.EMATEPDESC ";

# Verifica se o Tipo de Material foi escolhido #
if( $TipoMaterialDestino != "" and $MaterialDescricaoDiretaDestino == "" and $SubclasseDescricaoDiretaDestino == "" ){
		$where .= " AND GRU.FGRUMSTIPM = '$TipoMaterialDestino' ";
}

# Verifica se o Grupo foi escolhido #
if( $GrupoDestino != "" and $MaterialDescricaoDiretaDestino == "" and $SubclasseDescricaoDiretaDestino == "" ){
		$where .= " AND GRU.CGRUMSCODI = $GrupoDestino ";
}

# Verifica se a Classe foi escolhida #
if( $ClasseDestino != "" and $MaterialDescricaoDiretaDestino == "" and $SubclasseDescricaoDiretaDestino == "" ){
		$where .= " AND CLA.CGRUMSCODI = $GrupoDestino AND CLA.CCLAMSCODI = $ClasseDestino ";
}

# Verifica se a SubClasse foi escolhida #
if( $SubclasseDestino != "" and $MaterialDescricaoDiretaDestino == "" and $SubclasseDescricaoDiretaDestino == "" ){
		$where .= " AND SUB.CSUBCLSEQU = $Subclasse ";
}

# Se foi digitado algo na caixa de texto da subclasse em pesquisa familia #
if( $SubclasseDescricaoFamiliaDestino != "" and $MaterialDescricaoDiretaDestino == "" and $SubclasseDescricaoDiretaDestino == "" ){
		$where .= " AND ( ";
  	$where .= "      TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper(RetiraAcentos($SubclasseDescricaoFamiliaDestino))."%' OR ";
  	$where .= "      TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '% ".strtoupper(RetiraAcentos($SubclasseDescricaoFamiliaDestino))."%' ";
  	$where .= "     )";
}

# Se foi digitado algo na caixa de texto da subclasse em pesquisa direta #
if( $SubclasseDescricaoDiretaDestino != "" and $MaterialDescricaoDiretaDestino == "" ){
		if( $OpcaoPesquisaSubClasseDestino == 0 ){
			  if( SoNumeros($SubclasseDescricaoDiretaDestino) ){
	    	  	$where .= " AND SUB.CSUBCLSEQU = $SubclasseDescricaoDiretaDestino AND SUB.CGRUMSCODI = $GrupoDestino";
	    	}
	  }elseif($OpcaoPesquisaSubClasseDestino == 1){
	    	$where .= " AND ( ";
		  	$where .= "      TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper(RetiraAcentos($SubclasseDescricaoDiretaDestino))."%' OR ";
		  	$where .= "      TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '% ".strtoupper(RetiraAcentos($SubclasseDescricaoDiretaDestino))."%' ";
		  	$where .= "     )";
	  }else{
				$where .= " AND TRANSLATE(SUB.ESUBCLDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper(RetiraAcentos($SubclasseDescricaoDiretaDestino))."%' ";
		}
}

# Se foi digitado algo na caixa de texto do material em pesquisa direta #
if( $MaterialDescricaoDiretaDestino != "" and $SubclasseDescricaoDiretaDestino == "" ){
		if( $OpcaoPesquisaMaterialDestino == 0 ){
			  if (SoNumeros($MaterialDescricaoDiretaDestino)) {
	    	  $where .= " AND MAT.CMATEPSEQU = $MaterialDescricaoDiretaDestino AND GRU.CGRUMSCODI = $GrupoDestino";
	    	}
	  }elseif($OpcaoPesquisaMaterialDestino == 1){
	    	$where .= " AND ( ";
		  	$where .= "      TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper(RetiraAcentos($MaterialDescricaoDiretaDestino))."%' OR ";
		  	$where .= "      TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '% ".strtoupper(RetiraAcentos($MaterialDescricaoDiretaDestino))."%' ";
		  	$where .= "     )";
	  }else{
				$where .= " AND TRANSLATE(MAT.EMATEPDESC,'ÃÕÁÉÍÓÚÀÂÊÔÜÇ','AOAEIOUAAEOUC') ILIKE '".strtoupper(RetiraAcentos($MaterialDescricaoDiretaDestino))."%' ";
		}
}

# Gera o SQL com a concatenação das variaveis $sql,$from,$where,$order #
$sqlgeral = $sql.$from.$where.$order;

if( $Botao == "Voltar" ){
		header("location: CadCorrecaoMaterialSelecionar.php");
		exit;
}elseif($Botao == "Validar"){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $SubclasseDescricaoDiretaDestino != "" and $OpcaoPesquisaSubClasseDestino == 0 and ! SoNumeros($SubclasseDescricaoDiretaDestino) ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadSubstituicaoMaterial.SubclasseDescricaoDiretaDestino.focus();\" class=\"titulo2\">Código reduzido da Subclasse</a>";
		}elseif($SubclasseDescricaoDiretaDestino != "" and ($OpcaoPesquisaSubClasseDestino == 1 or $OpcaoPesquisaSubClasseDestino == 2) and strlen($SubclasseDescricaoDiretaDestino)< 2){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadSubstituicaoMaterial.SubclasseDescricaoDiretaDestino.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
		}
		if($MaterialDescricaoDiretaDestino != "" and $OpcaoPesquisaMaterialDestino == 0 and ! SoNumeros($MaterialDescricaoDiretaDestino) ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadSubstituicaoMaterial.MaterialDescricaoDiretaDestino.focus();\" class=\"titulo2\">Código reduzido do Material</a>";
		}elseif($MaterialDescricaoDiretaDestino != "" and ($OpcaoPesquisaMaterialDestino == 1 or $OpcaoPesquisaMaterialDestino == 2) and strlen($MaterialDescricaoDiretaDestino)< 2){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadSubstituicaoMaterial.MaterialDescricaoDiretaDestino.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
		}
}elseif( $Botao == "Substituir" ){
		$db   = Conexao();
		/* Armazena Data e Hora para SFPC.TBCORRECAOMATERIAL */
		$datahora = date("Y-m-d H:i:s");
		/********************************************************************
		  Verifica se Material Origem e Destino estão na mesma Localização
		********************************************************************/
		$sql  = "SELECT COUNT( A.CMATEPSEQU ) ";
		$sql .= "  FROM SFPC.TBARMAZENAMENTOMATERIAL A, SFPC.TBARMAZENAMENTOMATERIAL B ";
		$sql .= " WHERE A.CMATEPSEQU = $Material ";
		$sql .= "   AND B.CMATEPSEQU = $MaterialDestino";
		$sql .= "   AND A.CLOCMACODI = B.CLOCMACODI ";
		if ($AlmoxarifadoSel <> "") {
				$sql .= "   AND A.CLOCMACODI = ( ";
				$sql .= "       SELECT CLOCMACODI ";
				$sql .= "         FROM SFPC.TBLOCALIZACAOMATERIAL ";
				$sql .= "        WHERE CALMPOCODI = $AlmoxarifadoSel ";
				$sql .= "       )";
		}
	  $res  = $db->query($sql);
		if( PEAR::isError($res) ){
		    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql" );
				$db->query("ROLLBACK");
		}else{
				$Linha = $res->fetchRow();
				if ( $Linha[0] > 0 ){
						$Mens = 1;
						$Tipo = 2;
						$Mensagem = "Material Origem e Destino presentes no(s) mesmo(s) almoxarifado(s)";
				} else {
						/* Inicia Transação */
						$db->query("BEGIN TRANSACTION");
					  /******************************************************************
						  Verifica se material origem esta presente em algum almoxarifado
						******************************************************************/
						$sql  = "SELECT COUNT( CMATEPSEQU ) FROM SFPC.TBARMAZENAMENTOMATERIAL WHERE CMATEPSEQU = $Material ";
					  $res  = $db->query($sql);
						if( PEAR::isError($res) ){
						    $Erro = 1;
						}else{
								$Linha = $res->fetchRow();
								if ( $Linha[0] == 0 ){
										$Mens = 1;
										$Tipo = 2;
										$Mensagem = "O Material Origem não está presente em nenhum almoxarifado";
								} else {
										/*************************************************
										  Substitui Material Origem por Material Destino
										*************************************************/
										$sql  = "SELECT A.CALMPOCODI ";
										$sql .= "  FROM SFPC.TBLOCALIZACAOMATERIAL A, SFPC.TBARMAZENAMENTOMATERIAL B ";
										$sql .= " WHERE A.CLOCMACODI = B.CLOCMACODI AND B.CMATEPSEQU = $Material ";
										if ($AlmoxarifadoSel <> "") {
												$sql .= "   AND A.CLOCMACODI = ( ";
												$sql .= "       SELECT CLOCMACODI ";
												$sql .= "         FROM SFPC.TBLOCALIZACAOMATERIAL ";
												$sql .= "        WHERE CALMPOCODI = $AlmoxarifadoSel ";
												$sql .= "       )";
										}
										$resAlmoxarifado  = $db->query($sql);
									  if( PEAR::isError($resAlmoxarifado) ){
											  $Erro = 1;
										}else{
												/* Altera dados nos almoxarifados */
												while( $Linha = $resAlmoxarifado->fetchRow() ){
														$Almoxarifado = $Linha[0];
														/* Lê dados anteriores do material no almoxarifado */
														$sql  = "SELECT AARMATQTDE, VARMATUMED ";
														$sql .= "  FROM SFPC.TBARMAZENAMENTOMATERIAL";
														$sql .= " WHERE CMATEPSEQU = $Material ";
														$sql .= "   AND CLOCMACODI = ( ";
														$sql .= "       SELECT CLOCMACODI ";
														$sql .= "         FROM SFPC.TBLOCALIZACAOMATERIAL ";
														$sql .= "        WHERE CALMPOCODI = $Almoxarifado ";
														$sql .= "       )";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
															  $Erro = 1; break;
														}else{
																$MaterialHistorico = $res->fetchRow();
														}
														/* Substitui Materiais em SFPC.TBARMAZENAMENTOMATERIAL */
														$sql  = "UPDATE SFPC.TBARMAZENAMENTOMATERIAL ";
														$sql .= "   SET CMATEPSEQU = $MaterialDestino,  ";
														$sql .= "       TARMATULAT = '$datahora'  ";
														$sql .= " WHERE CMATEPSEQU = $Material ";
														$sql .= "   AND CLOCMACODI = ( ";
														$sql .= "       SELECT CLOCMACODI ";
														$sql .= "         FROM SFPC.TBLOCALIZACAOMATERIAL ";
														$sql .= "        WHERE CALMPOCODI = $Almoxarifado ";
														$sql .= "       )";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
															  $Erro = 1; break;
														}
														/* Registra alteração em SFPC.TBCORRECAOMATERIAL */
														$sql  = "INSERT INTO SFPC.TBCORRECAOMATERIAL ";
														$sql .= "     VALUES (".date("Y").", ".CorrecaoMax( $db ).", $Almoxarifado, ";
														$sql .= "             'TBARMAZENAMENTOMATERIAL', $MaterialHistorico[0], ";
														$sql .= "             $MaterialHistorico[1], $Material, '', $MaterialDestino, ";
														$sql .= "             ".$_SESSION['_cgrempcodi_'].",".$_SESSION['_cusupocodi_'].", ";
														$sql .= "             '$datahora'";
														$sql .= "            )";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
															  $Erro = 1; break;
														}
														/* Substitui Materiais em SFPC.TBINVENTARIOMATERIAL */
														$sql  = "UPDATE SFPC.TBINVENTARIOMATERIAL ";
														$sql .= "   SET CMATEPSEQU = $MaterialDestino, ";
														$sql .= "       TINVMAULAT = '$datahora'  ";
														$sql .= " WHERE CMATEPSEQU = $Material ";
														$sql .= "   AND CLOCMACODI = ( ";
														$sql .= "       SELECT CLOCMACODI ";
														$sql .= "         FROM SFPC.TBLOCALIZACAOMATERIAL ";
														$sql .= "        WHERE CALMPOCODI = $Almoxarifado ";
														$sql .= "       )";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
															  $Erro = 1; break;
														}
														/* Substitui Materiais em SFPC.TBINVENTARIOREGISTRO */
														$sql  = "UPDATE SFPC.TBINVENTARIOREGISTRO ";
														$sql .= "   SET CMATEPSEQU = $MaterialDestino, ";
														$sql .= "       TINVREULAT = '$datahora'  ";
														$sql .= " WHERE CMATEPSEQU = $Material ";
														$sql .= "   AND CLOCMACODI = ( ";
														$sql .= "       SELECT CLOCMACODI ";
														$sql .= "         FROM SFPC.TBLOCALIZACAOMATERIAL ";
														$sql .= "        WHERE CALMPOCODI = $Almoxarifado ";
														$sql .= "       )";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
															  $Erro = 1; break;
														}
														/* Registra alteração em SFPC.TBCORRECAOMATERIAL */
														$sql  = "INSERT INTO SFPC.TBCORRECAOMATERIAL ";
														$sql .= "     VALUES (".date("Y").", ".CorrecaoMax( $db ).", $Almoxarifado, ";
														$sql .= "             'TBINVENTARIOMATERIAL', $MaterialHistorico[0], ";
														$sql .= "             $MaterialHistorico[1], $Material, '', $MaterialDestino, ";
														$sql .= "             ".$_SESSION['_cgrempcodi_'].",".$_SESSION['_cusupocodi_'].", ";
														$sql .= "             '$datahora'";
														$sql .= "            )";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
															  $Erro = 1; break;
														}
														/* Substitui Materiais em SFPC.TBITEMREQUISICAO */
														$sql  = "UPDATE SFPC.TBITEMREQUISICAO ";
														$sql .= "   SET CMATEPSEQU = $MaterialDestino, ";
														$sql .= "       TITEMRULAT = '$datahora'  ";
														$sql .= " WHERE CMATEPSEQU = $Material ";
														$sql .= "   AND CREQMASEQU IN ( ";
														$sql .= "       SELECT CREQMASEQU ";
														$sql .= "         FROM SFPC.TBREQUISICAOMATERIAL ";
														$sql .= "        WHERE CALMPOCODI = $Almoxarifado ";
														$sql .= "       )";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
															  $Erro = 1; break;
														}
														/* Registra alteração em SFPC.TBCORRECAOMATERIAL */
														$sql  = "INSERT INTO SFPC.TBCORRECAOMATERIAL ";
														$sql .= "     VALUES (".date("Y").", ".CorrecaoMax( $db ).", $Almoxarifado, ";
														$sql .= "             'TBITEMREQUISICAO', $MaterialHistorico[0], ";
														$sql .= "             $MaterialHistorico[1], $Material, '', $MaterialDestino, ";
														$sql .= "             ".$_SESSION['_cgrempcodi_'].",".$_SESSION['_cusupocodi_'].", ";
														$sql .= "             '$datahora'";
														$sql .= "            )";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
															  $Erro = 1; break;
														}
														/* Substitui Materiais em SFPC.TBITEMNOTAFISCAL */
														$sql  = "UPDATE SFPC.TBITEMNOTAFISCAL ";
														$sql .= "   SET CMATEPSEQU = $MaterialDestino, ";
														$sql .= "       TITENFULAT = '$datahora'  ";
														$sql .= " WHERE CMATEPSEQU = $Material ";
														$sql .= "   AND CALMPOCODI = $Almoxarifado ";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
															  $Erro = 1; break;
														}
														/* Registra alteração em SFPC.TBCORRECAOMATERIAL */
														$sql  = "INSERT INTO SFPC.TBCORRECAOMATERIAL ";
														$sql .= "     VALUES (".date("Y").", ".CorrecaoMax( $db ).", $Almoxarifado, ";
														$sql .= "             'TBITEMNOTAFISCAL', $MaterialHistorico[0], ";
														$sql .= "             $MaterialHistorico[1], $Material, '', $MaterialDestino, ";
														$sql .= "             ".$_SESSION['_cgrempcodi_'].",".$_SESSION['_cusupocodi_'].", ";
														$sql .= "             '$datahora'";
														$sql .= "            )";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
															  $Erro = 1; break;
														}
														/* Substitui Materiais em SFPC.TBMOVIMENTACAOMATERIAL */
														$sql  = "UPDATE SFPC.TBMOVIMENTACAOMATERIAL ";
														$sql .= "   SET CMATEPSEQU = $MaterialDestino, ";
														$sql .= "       TMOVMAULAT = '$datahora'  ";
														$sql .= " WHERE CMATEPSEQU = $Material ";
														$sql .= "   AND CALMPOCODI = $Almoxarifado ";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
															  $Erro = 1; break;
														}
														/* Registra alteração em SFPC.TBCORRECAOMATERIAL */
														$sql  = "INSERT INTO SFPC.TBCORRECAOMATERIAL ";
														$sql .= "     VALUES (".date("Y").", ".CorrecaoMax( $db ).", $Almoxarifado, ";
														$sql .= "             'TBMOVIMENTACAOMATERIAL', $MaterialHistorico[0], ";
														$sql .= "             $MaterialHistorico[1], $Material, '', $MaterialDestino, ";
														$sql .= "             ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", ";
														$sql .= "             '$datahora'";
														$sql .= "            )";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
															  $Erro = 1; break;
														}
														/* Substitui Materiais em SFPC.TBITEMSOLICITACAOCOMPRA */
														$sql  = "
															UPDATE SFPC.TBITEMSOLICITACAOCOMPRA
															SET
																CMATEPSEQU = $MaterialDestino,
																TITESCULAT = '$datahora'
															where
																cmatepsequ = $Material
																and csolcosequ in (
																	select csolcosequ
																	from SFPC.tbsolicitacaocompra
																	where corglicodi in (
																		select ao.corglicodi
																		from sfpc.tbalmoxarifadoorgao ao
																		where calmpocodi = $Almoxarifado
																	)
																)
														";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
															  $Erro = 1; break;
														}
														/* Registra alteração em SFPC.TBCORRECAOMATERIAL */
														$sql  = "INSERT INTO SFPC.TBCORRECAOMATERIAL ";
														$sql .= "     VALUES (".date("Y").", ".CorrecaoMax( $db ).", $Almoxarifado, ";
														$sql .= "             'TBITEMSOLICITACAOCOMPRA', $MaterialHistorico[0], ";
														$sql .= "             $MaterialHistorico[1], $Material, '', $MaterialDestino, ";
														$sql .= "             ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].", ";
														$sql .= "             '$datahora'";
														$sql .= "            )";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
															  $Erro = 1; break;
														}
														if ( !$Erro ){
																$Mensagem  = "Substituição de Materiais Realizada com Sucesso";
														}
												}
										}
								}
								/* Exclui o material e registra alteração em SFPC.TBCORRECAOMATERIAL */
								if ( ! $Erro == 1 ){
										/* Verifica se existe algum registro com aquele código de material */
										$sql  = "SELECT 1 FROM SFPC.TBARMAZENAMENTOMATERIAL ARM WHERE ARM.CMATEPSEQU = $Material UNION ";
										$sql .= "SELECT 1 FROM SFPC.TBINVENTARIOMATERIAL INV WHERE INV.CMATEPSEQU = $Material UNION ";
										$sql .= "SELECT 1 FROM SFPC.TBITEMREQUISICAO ITEM WHERE ITEM.CMATEPSEQU = $Material UNION ";
										$sql .= "SELECT 1 FROM SFPC.TBITEMNOTAFISCAL ITEMN WHERE ITEMN.CMATEPSEQU = $Material UNION ";
										$sql .= "SELECT 1 FROM SFPC.TBMOVIMENTACAOMATERIAL MOVM WHERE MOVM.CMATEPSEQU = $Material UNION ";
										$sql .= "SELECT 1 FROM SFPC.TBITEMSOLICITACAOCOMPRA ITESL WHERE ITESL.CMATEPSEQU = $Material";
									  $res  = $db->query($sql);
										if( PEAR::isError($res) ){
										    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql" );
												$db->query("ROLLBACK");
										}else{
												$Linha = $res->fetchRow();
												if ( $Linha[0] <> 1 ){
														$sql  = "SELECT EMATEPDESC ";
														$sql .= "  FROM SFPC.TBMATERIALPORTAL";
														$sql .= " WHERE CMATEPSEQU = $Material ";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
															  $Erro = 1;
														}else{
																$MaterialDescricao = $res->fetchRow();
																$MaterialDescricao = $MaterialDescricao[0];
														}
														/* Remove Material Origem */
                            # O material não pode ser mais deletado, apenas inativado, para a integridade com o SOFIN e CADUM.
														$sql = "UPDATE SFPC.TBMATERIALPORTAL SET CMATEPSITU = 'I', CUSUPOCODI = ".$_SESSION['_cusupocodi_']." WHERE CMATEPSEQU = $Material";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
                                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql" );
                                $Erro = 1;
														}
														$sql  = "INSERT INTO SFPC.TBCORRECAOMATERIAL ";
														$sql .= "     VALUES (".date("Y").", ".CorrecaoMax( $db ).", NULL, ";
														$sql .= "             'TBMATERIALPORTAL', NULL, ";
														$sql .= "             NULL, $Material, '$MaterialDescricao', ";
														$sql .= "             $MaterialDestino, ".$_SESSION['_cgrempcodi_'].", ";
														$sql .= "             ".$_SESSION['_cusupocodi_'].", '$datahora'";
														$sql .= "            )";
														$res  = $db->query($sql);
														if( PEAR::isError($res) ){
															  $Erro = 1;
														}
														$Mensagem .= ". Material Origem Inativado com Sucesso";
												}
										}
								}
						}
						/* Finaliza operação */
						if ( $Erro == 1 ){
								ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql" );
								$db->query("ROLLBACK");
						} else {
								$db->query("COMMIT TRANSACTION");
								$db->query("END TRANSACTION");
								$db->disconnect();
								/* Redireciona para a tela de Seleção de Correção */
								$Url = "CadCorrecaoMaterialSelecionar.php?Mens=1&Tipo=1&Mensagem=".urlencode( $Mensagem );
								if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
								header("location: ".$Url);
								exit;
						}
				}
		}
		$db->disconnect();
}
if( $Botao == "" ){
		# Pega os dados do Pré-Material de acordo com o código #
		$db   = Conexao();
    $sql  = "SELECT GRU.FGRUMSTIPM, GRU.EGRUMSDESC, CLA.ECLAMSDESC, SUB.ESUBCLDESC, ";
    $sql .= "       MAT.EMATEPDESC, MAT.EMATEPCOMP, UND.CUNIDMCODI, UND.EUNIDMDESC, ";
    #$sql .= "       MAT.EMATEPOBSE, MAT.CSUBCLSEQU, ";
    $sql .= "       MAT.EMATEPOBSE, MAT.CSUBCLSEQU, GRU.CGRUMSCODI ";
		$sql .= "  FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, ";
		$sql .= "       SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBUNIDADEDEMEDIDA UND ";
		$sql .= " WHERE SUB.CGRUMSCODI = CLA.CGRUMSCODI AND SUB.CCLAMSCODI = CLA.CCLAMSCODI ";
		$sql .= "   AND CLA.CGRUMSCODI = GRU.CGRUMSCODI AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU  ";
		$sql .= "   AND MAT.CUNIDMCODI = UND.CUNIDMCODI AND MAT.CMATEPSEQU = $Material ";
		$res  = $db->query($sql);
	  if( PEAR::isError($res) ){
			  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha         		= $res->fetchRow();
      	$TipoMaterial  		= $Linha[0];
      	$DescGrupo     		= $Linha[1];
				$DescClasse    		= $Linha[2];
				$DescSubclasse 		= $Linha[3];
				$DescMaterial  		= $Linha[4];
				$NCaracteresM     = strlen($DescMaterial);
				$DescMaterialComp = $Linha[5];
				$NCaracteresC     = strlen($DescMaterialComp);
				$Unidade       		= $Linha[6];
				$DescUnidade   		= $Linha[7];
				$Observacao       = $Linha[8];
				$NCaracteresO     = strlen($Observacao);
				$Subclasse        = $Linha[9];
        $CodigoGrupo      = $Linha[10];
		}
		$db->disconnect();
}
if ( $MaterialDestino ){
		$db   = Conexao();
    $sql  = "SELECT GRU.FGRUMSTIPM, GRU.EGRUMSDESC, CLA.ECLAMSDESC, SUB.ESUBCLDESC, ";
    $sql .= "       MAT.EMATEPDESC, MAT.EMATEPCOMP, UND.CUNIDMCODI, UND.EUNIDMDESC, ";
    $sql .= "       MAT.EMATEPOBSE,MAT.CSUBCLSEQU ";
		$sql .= "  FROM SFPC.TBMATERIALPORTAL MAT, SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, ";
		$sql .= "       SFPC.TBSUBCLASSEMATERIAL SUB, SFPC.TBUNIDADEDEMEDIDA UND ";
		$sql .= " WHERE SUB.CGRUMSCODI = CLA.CGRUMSCODI AND SUB.CCLAMSCODI = CLA.CCLAMSCODI ";
		$sql .= "   AND CLA.CGRUMSCODI = GRU.CGRUMSCODI AND MAT.CSUBCLSEQU = SUB.CSUBCLSEQU  ";
		$sql .= "   AND MAT.CUNIDMCODI = UND.CUNIDMCODI AND MAT.CMATEPSEQU = $MaterialDestino ";
		$res  = $db->query($sql);
	  if( PEAR::isError($res) ){
			  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha         		= $res->fetchRow();
      	$TipoMaterialDestino  	 = $Linha[0];
      	$DescGrupoDestino     	 = $Linha[1];
				$DescClasseDestino    	 = $Linha[2];
				$DescSubclasseDestino 	 = $Linha[3];
				$DescMaterialDestino  	 = $Linha[4];
				$NCaracteresMDestino     = strlen($DescMaterialDestino);
				$DescMaterialCompDestino = $Linha[5];
				$NCaracteresCDestino     = strlen($DescMaterialCompDestino);
				$UnidadeDestino      		 = $Linha[6];
				$DescUnidadeDestino   	 = $Linha[7];
				$ObservacaoDestino       = $Linha[8];
				$NCaracteresODestino     = strlen($Observacao);
				$SubclasseDestino        = $Linha[9];
		}
		$db->disconnect();
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function remeter(){
	document.CadSubstituicaoMaterial.GrupoDestino.value  = '';
	document.CadSubstituicaoMaterial.ClasseDestino.value = '';
	document.CadSubstituicaoMaterial.submit();
}
function enviar(valor){
	document.CadSubstituicaoMaterial.Botao.value = valor;
	document.CadSubstituicaoMaterial.submit();
}
function validapesquisa(){
	if( document.CadSubstituicaoMaterial.SubclasseDestino ){
		document.CadSubstituicaoMaterial.SubclasseDestino.value = "";
	}
	document.CadSubstituicaoMaterial.Botao.value = 'Validar';
	document.CadSubstituicaoMaterial.submit();
}

<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadSubstituicaoMaterial.php" method="post" name="CadSubstituicaoMaterial">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Correção > Substituição de Materiais
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left">
	  	<?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,1); } ?>
	  </td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table border="0" cellspacing="0" cellpadding="3" bgcolor="ffffff" summary="">
				<tr>
					<td class="textonormal">
						<table border="0" cellspacing="0" cellpadding="0" summary="">
							<tr>
				      	<td class="textonormal">
				        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
				          	<tr>
				            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
					    					SUBSTITUIÇÃO DE MATERIAIS
					          	</td>
					        	</tr>
					        	<?phpif ( $Confirmar == 1 ){ ?>
					        	<tr>
				    	      	<td class="textonormal" colspan="4">
												<p align="justify">
							             Clique no botão Confirmar Transferência para concluir a correção ou no botão Voltar para iniciar o processo.
				          	   	</p>
				          		</td>
					        	</tr>
					        	<?php} else { ?>
				  	      	<tr>
				    	      	<td class="textonormal" colspan="4">
												<p align="justify">
							             Para pesquisar um item já cadastrado, preencha o argumento da pesquisa. Depois, clique no item desejado.
				          	   	</p>
				          		</td>
					        	</tr>
					        	<?php} ?>
					        	<tr>
				            	<td align="center" bgcolor="#BFDAF2" valign="middle" class="titulo3" colspan="4">
					    					MATERIAL ORIGEM
					          	</td>
					        	</tr>
					        	<tr>
											<td colspan="4">
												<table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
													<tr>
														<td colspan="2">
									      	    <table class="textonormal" border="0" width="100%" summary="">
									      	    	<tr>
										              <td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Tipo de Material</td>
										              <td class="textonormal">
										              	<?php if( $TipoMaterial == "C" ){ echo "CONSUMO"; }else{ echo "PERMANENTE"; } ?>
									              	</td>
									            	</tr>
									            	<tr>
									              	<td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Grupo</td>
									              	<td class="textonormal"><?php echo $DescGrupo; ?></td>
									            	</tr>
											        	<tr>
											            <td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Classe</td>
										  	        	<td class="textonormal"><?php echo $DescClasse; ?></td>
											        	</tr>
										        		<tr>
											            <td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Subclasse</td>
										  	        	<td class="textonormal"><?php echo $DescSubclasse; ?></td>
											        	</tr>
									      	    	<tr>
										            	<td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Código Material</td>
									  	        		<td class="textonormal"><?php echo $Material;?></td>
									  	        	</tr>
										        		<tr>
										            	<td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Descrição</td>
									  	        		<td class="textonormal"><?phpecho $DescMaterial; ?></td>
										        		</tr>
										        		<tr>
										            	<td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Descrição Completa</td>
									  	        		<td class="textonormal"><?phpecho $DescMaterialComp; ?></td>
										        		</tr>
										        		<tr>
										            	<td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Unidade</td>
									  	        		<td class="textonormal"><?phpecho $DescUnidade; ?></td>
										        		</tr>
									          	</table>
														</td>
													</tr>
												</table>
											</td>
										</tr>
					        	<tr>
				            	<td align="center" bgcolor="#BFDAF2" valign="middle" class="titulo3" colspan="4">
					    					MATERIAL DESTINO
					          	</td>
					        	</tr>
					        	<?phpif ( $Confirmar != 1 ) { ?>
					        	<tr>
		        				  <td align="center" bgcolor="#DCEDF7" class="titulo3" colspan="4">PESQUISA DIRETA</td>
		        				</tr>
		        				<tr>
		          				<td colspan="4">
		            				<table border="0" width="100%" summary="">
			            				<tr>
			              				<td class="textonormal" bgcolor="#DCEDF7" width="31%">Subclasse</td>
			              				<td class="textonormal" colspan="2">
			              					<select name="OpcaoPesquisaSubClasseDestino" class="textonormal">
			              						<option value="0">Código Reduzido</option>
			              						<option value="1">Descrição contendo</option>
																<option value="2">Descrição iniciada por</option>
			              					</select>
		         	        				<input type="text" name="SubclasseDescricaoDiretaDestino" size="10" maxlength="10" class="textonormal">
						           	      <a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
			              				</td>
			              			</tr>
			              			<tr>
	              				<td class="textonormal" bgcolor="#DCEDF7">Material</td>
	              				<td class="textonormal" colspan="2">
	              					<select name="OpcaoPesquisaMaterialDestino" class="textonormal">
	              						<option value="0">Código Reduzido</option>
	              						<option value="1">Descrição contendo</option>
														<option value="2">Descrição iniciada por</option>
	              					</select>
         	        				<input type="text" name="MaterialDescricaoDiretaDestino" size="10" maxlength="10" class="textonormal">
				           	      <a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
	              				</td>
	            				</tr>
		            				</table>
		          				</td>
		        				</tr>
						        <tr>
		        				  <td align="center" bgcolor="#DCEDF7" class="titulo3" colspan="4">PESQUISA POR FAMILIA</td>
		        				</tr>
					        	<tr>
											<td colspan="4">
												<table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
													<tr>
														<td colspan="4">
									      	    <table class="textonormal" border="0" width="100%" summary="">
								                <tr>
										              <td class="textonormal" bgcolor="#DCEDF7" height="20">Tipo de Material</td>
										              <td class="textonormal">

                                    <?php
                                      //Tornando fixo o tipo do material para apenas realizar substituições de materiais no mesmo tipo e grupo do material.
                                      $TipoMaterialDestino = $TipoMaterial;
                                    ?>

											              <input type="radio" name="TipoMaterialDestino" value="C" onClick="javascript:<?
											              if ($GrupoDestino!=""){echo "GrupoDestino.selectedIndex=0;";}
											              if ($ClasseDestino!=""){echo "ClasseDestino.selectedIndex=0;";}
											              if ($SubclasseDestino!=""){echo "SubclasseDestino.selectedIndex=0;";}
											              echo "submit();\" ";
											              if( $TipoMaterialDestino == "C" ){ echo "checked"; } else { echo "disabled"; }
											              ?> > Consumo
											              <input type="radio" name="TipoMaterialDestino" value="P" onClick="javascript:<?
											              if ($GrupoDestino!=""){echo "GrupoDestino.selectedIndex=0;";}
											              if ($ClasseDestino!=""){echo "ClasseDestino.selectedIndex=0;";}
											              if ($SubclasseDestino!=""){echo "SubclasseDestino.selectedIndex=0;";}
											              echo "submit();\" ";
											              if( $TipoMaterialDestino == "P" ){ echo "checked"; } else { echo "disabled"; }
											              ?> > Permanente
											              	</td>
											            	</tr>
											            	<?phpif( $TipoMaterialDestino != "" ){ ?>
											            	<tr>
											              	<td class="textonormal" bgcolor="#DCEDF7" height="20">Grupo</td>
											              	<td class="textonormal">
                                        <?php
                                          //Tornando fixo o grupo do material para apenas realizar substituições de materiais no mesmo tipo e grupo.
                                          $GrupoDestino = $CodigoGrupo;
                                        ?>

											              	  <select name="GrupoDestino" style="background-color:#FFFFFF" onChange="<?
									              	  if ($ClasseDestino){echo "ClasseDestino.selectedIndex=0;";}
									              	  if ($SubclasseDestino){echo "SubclasseDestino.selectedIndex=0;";}
									              	  ?>submit();" class="textonormal">
		              	              		<option value="">Selecione um Grupo...</option>
									              	    <?php
									              			$db = Conexao();
																			if( $TipoMaterialDestino == "C" or $TipoMaterialDestino == "P" ){
																					$sql = "SELECT CGRUMSCODI,EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO ";
																					$sql .= "WHERE FGRUMSTIPM = '$TipoMaterialDestino' AND CGRUMSCODI = $GrupoDestino AND FGRUMSSITU = 'A' ORDER BY EGRUMSDESC";
												                	$result = $db->query($sql);
				                									if (PEAR::isError($result)) {
																				     ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																					}else{
																					   //while($Linha = $result->fetchRow()){
                                             if($Linha = $result->fetchRow()) {
					          	      							      $Descricao   = substr($Linha[1],0,75);
										          	      			    //if( $Linha[0] == $GrupoDestino ){
										    	      							      echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
												      	      		      //}else{
										    	      							  //    echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
												      	      		      //}
								      	      				       //}
                                             }
											                    }
							              	        }
									              	    ?>
									              	  </select>
									              	</td>
									            	</tr>
									            	<?php
									            	}
		                       		  if( $GrupoDestino != ""){
		                       		  ?>
									              <tr>
										              <td class="textonormal" bgcolor="#DCEDF7">Classe </td>
		              								<td class="textonormal">
										              	<select name="ClasseDestino" class="textonormal" onChange="<?
							              	  if ($SubclasseDestino){echo "SubclasseDestino.selectedIndex=0;";}
							              	  ?>submit();">
		              										<option value="">Selecione uma Classe...</option>
											              		<?php
		            												if( $GrupoDestino != "" ){
													              		$db   = Conexao();
                                            $sql  = "SELECT CLA.CCLAMSCODI, CLA.ECLAMSDESC ";
																						$sql .= "  FROM SFPC.TBCLASSEMATERIALSERVICO CLA, SFPC.TBGRUPOMATERIALSERVICO GRU ";
																						$sql .= " WHERE GRU.CGRUMSCODI = CLA.CGRUMSCODI AND CLA.CGRUMSCODI = $GrupoDestino AND CLA.FCLAMSSITU = 'A' AND GRU.FGRUMSSITU = 'A' ";
																						$sql .= " ORDER BY ECLAMSDESC";
																						$res  = $db->query($sql);
																					  if( PEAR::isError($res) ){
																							  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																						}else{
																								while( $Linha = $res->fetchRow() ){
															          	      			$Descricao = substr($Linha[1],0,75);
				          	  											    			if( $Linha[0] == $ClasseDestino){
									    	      														echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
																		      	      		}else{
																													echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
																		      	      		}
						            								    		}
																						}
												  	              	$db->disconnect();
		  	            										}
		              											?>
		              									</select>
		              								</td>
		            								</tr>
		                            <?php
		                            }
		                            if( $GrupoDestino != "" and $ClasseDestino != "" ){
		                            ?>
										        		<tr>
											            <td class="textonormal" bgcolor="#DCEDF7" height="20">Subclasse</td>
										  	        	<td class="textonormal">
									              	  <select name="SubclasseDestino" onChange="submit();" class="textonormal">
		              	              		<option value="">Selecione uma Subclasse...</option>
									              	    <?
									              			$db = Conexao();
																			//$sql = "SELECT CSUBCLSEQU,ESUBCLDESC FROM SFPC.TBSUBCLASSEMATERIAL ";
																			//$sql .= "WHERE CGRUMSCODI = '$GrupoDestino' AND CCLAMSCODI = '$ClasseDestino' ORDER BY ESUBCLDESC";

                                      $sql   = "  SELECT SUB.CSUBCLSEQU, SUB.ESUBCLDESC ";
                                      $sql  .= "  FROM SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, ";
                                      $sql  .= "       SFPC.TBSUBCLASSEMATERIAL SUB ";
                                      $sql  .= " WHERE SUB.CGRUMSCODI = CLA.CGRUMSCODI ";
                                      $sql  .= "   AND SUB.CCLAMSCODI = CLA.CCLAMSCODI AND CLA.CGRUMSCODI = GRU.CGRUMSCODI ";
                                      $sql  .= "   AND GRU.FGRUMSSITU = 'A' AND CLA.FCLAMSSITU = 'A' AND SUB.FSUBCLSITU = 'A' ";
                                      $sql  .= "   AND SUB.CGRUMSCODI = '$GrupoDestino' AND SUB.CCLAMSCODI = '$ClasseDestino' ";
                                      $sql  .= "   ORDER BY ESUBCLDESC ";

												              $result = $db->query($sql);
				                							if (PEAR::isError($result)) {
																			   ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																			}else{
																			   while($Linha = $result->fetchRow()){
					          	      					      $Descricao   = substr($Linha[1],0,75);
										          	      	    if( $Linha[0] == $SubclasseDestino ){
										    	      				       echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
												      	            }else{
										    	      						   echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
												      	      		  }
								      	      				   }
											                }
									              	    ?>
									              	  </select>
									              	  <input type="text" name="SubclasseDescricaoFamiliaDestino" size="10" maxlength="10" class="textonormal">
						           	      			<a href="javascript:validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
										  	        	</td>
											        	</tr>
											        	<?php} ?>
									          	</table>
														</td>
													</tr>
												</table>
											</td>
										</tr>

			            	<?php
			            	if( $MaterialDescricaoDiretaDestino != "" ) {
												if( $OpcaoPesquisaMaterialDestino == 0 ){
														if( !SoNumeros($MaterialDescricaoDiretaDestino) ){ $sqlgeral = ""; }
											 	}
										}
										if( $SubclasseDescricaoDiretaDestino != "" ){
												if( $OpcaoPesquisaSubClasseDestino == 0 ){
														if( !SoNumeros($SubclasseDescricaoDiretaDestino) ){ $sqlgeral = ""; }
												}
										}
										if( $sqlgeral != "" and $Mens == 0 ) {
												if( ( $MaterialDescricaoDiretaDestino != "" or $SubclasseDescricaoDiretaDestino != "" ) or
										 				( $SubclasseDestino != "" or $SubclasseDescricaoFamiliaDestino != "" or $ChkSubclasse != "" ) ){
														$db     = Conexao();
                            $res    = $db->query($sqlgeral);
														if( PEAR::isError($res) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlgeral");
														}else{
																$qtdres = $res->numRows();
																echo "<tr>\n";
																echo "  <td align=\"center\" bgcolor=\"#BFDAF2\" colspan=\"4\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
																echo "</tr>\n";
																if( $qtdres > 0 ) {
								  									$TipoMaterialAntes  = "";
								  									$GrupoAntes         = "";
								  									$ClasseAntes        = "";
								  									$SubClasseAntes     = "";
								  									$SubClasseSequAntes = "";
																		$irow = 1;
								  									while( $row	= $res->fetchRow() ){
								    										$GrupoCodigo        = $row[0];
								    										$GrupoDescricao     = $row[1];
								    										$ClasseCodigo       = $row[2];
								    										$ClasseDescricao    = $row[3];
								    										$SubClasseSequ      = $row[4];
								    										$SubClasseDescricao = $row[5];
								    										$MaterialSequencia  = $row[6];
								    										$MaterialDescricao  = $row[7];
								    										$UndMedidaSigla     = $row[8];
								    										$TipoMaterialCodigo = $row[9];
								    										$CodigoReduzido		  = $row[10];
																				if( $TipoMaterialAntes != $TipoMaterialCodigo ) {
									    											echo "<tr>\n";
									    											echo "  <td class=\"textoabason\" bgcolor=\"#BFDAF2\" colspan=\"4\" align=\"center\">";
								         										if($TipoMaterialCodigo == "C"){ echo "CONSUMO"; }else{ echo "PERMANENTE";}
								  		    									echo "  </td>\n";
								  				    							echo "</tr>\n";
								    										}
																	      if( $ClasseAntes != $ClasseDescricao ) {
						    						            		echo "<tr>\n";
						      											    echo "  <td class=\"textoabason\" bgcolor=\"#DDECF9\" colspan=\"4\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
						      											    echo "</tr>\n";
						      												  echo "<tr>\n";
								      											echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"30%\">SUBCLASSE</td>\n";
								      											echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"50%\">DESCRIÇÃO DO MATERIAL</td>\n";
								      											echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">CÓD.RED.</td>\n";
								      											echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\" align=\"center\">UNIDADE</td>\n";
								      											echo "</tr>\n";
								    										}
								    										echo "<tr>\n";
						      											if( $SubClasseAntes != $SubClasseDescricao or $SubClasseSequAntes != $SubClasseSequ ) {
								    											  $flg = "S";
								      											echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"30%\">\n";
								      											echo "    $SubClasseDescricao";
								      											echo "  </td>\n";
								    										} else {
								      											echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"30%\">\n";
								      											echo "&nbsp;";
								      											echo "  </td>\n";
								      											$flg = "";
								      									}
						    												echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"60%\">\n";
				    														echo "	  <a href=\"javascript:CadSubstituicaoMaterial.Confirmar.value=1;CadSubstituicaoMaterial.MaterialDestino.value=$MaterialSequencia;CadSubstituicaoMaterial.submit();\"><font color=\"#000000\">$MaterialDescricao</font></a>";
						    												echo "	</td>\n";
						    												echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\" width=\"30%\">\n";
																				echo "    $CodigoReduzido";
																				echo "  </td>\n";
						    												echo "	<td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" align=\"center\"  width=\"10%\">\n";
						    												echo "	  $UndMedidaSigla";
						    												echo "	</td>\n";
								    										echo "</tr>\n";
								    										$TipoMaterialAntes  = $TipoMaterialCodigo;
								    										$GrupoAntes         = $GrupoDescricao;
								    										$ClasseAntes        = $ClasseDescricao;
								    										$SubClasseAntes     = $SubClasseDescricao;
								    										$SubClasseSequAntes = $SubClasseSequ;
								  									}
																		$db->disconnect();
												        }else{
								  									echo "<tr>\n";
								  									echo "	<td valign=\"top\" colspan=\"4\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
								  									echo "		Pesquisa sem Ocorrências.\n";
								  									echo "	</td>\n";
								  									echo "</tr>\n";
																}
													  }
										    }
										}
								} else {
									?>
									<tr>
				    	      	<td class="textonormal" colspan="4">
												<p align="justify">
							            <table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
													<tr>
														<td colspan="4">
									      	    <table class="textonormal" border="0" width="100%" summary="">
								            		<tr>
										              <td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Tipo de Material</td>
										              <td class="textonormal">
										              	<?php if( $TipoMaterialDestino == "C" ){ echo "CONSUMO"; }else{ echo "PERMANENTE"; } ?>
									              	</td>
									            	</tr>
									            	<tr>
									              	<td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Grupo</td>
									              	<td class="textonormal"><?php echo $DescGrupoDestino; ?></td>
									            	</tr>
											        	<tr>
											            <td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Classe</td>
										  	        	<td class="textonormal"><?php echo $DescClasseDestino; ?></td>
											        	</tr>
										        		<tr>
											            <td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Subclasse</td>
										  	        	<td class="textonormal"><?php echo $DescSubclasseDestino; ?></td>
											        	</tr>
											        	<tr>
											            <td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Código Material</td>
										  	        	<td class="textonormal"><?php echo $MaterialDestino; ?></td>
											        	</tr>
											        	<tr>
											            <td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Descrição Material</td>
										  	        	<td class="textonormal"><?php echo $DescMaterialDestino; ?></td>
											        	</tr>
											        	<tr>
											            <td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Descrição Completa</td>
										  	        	<td class="textonormal"><?php echo $DescMaterialCompDestino; ?></td>
											        	</tr>
										        		<tr>
										            	<td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Unidade</td>
									  	        		<td class="textonormal"><?phpecho $DescUnidadeDestino; ?></td>
										        		</tr>
									          	</table>
														</td>
													</tr>
												</table>
				          	   	</p>
				          		</td>
					        	</tr>
					        	<?

					        	# Mostra o(s) Almoxarifado(s) para ser feita uma correção específica só para o perfil de administrador ou visão corporativa #
										if( ($Confirmar == 1 ) and ($_SESSION['_cgrempcodi_'] == 0) or ($_SESSION['_fperficorp_'] == 'S')){	?>
											<tr>
												<td class="textonormal" bgcolor="#DCEDF7"	height="20" width="20%">Almoxarifado</td>
												<td class="textonormal">
													<?php
													$db  = Conexao();
													$sql = "SELECT DISTINCT A.CALMPOCODI, A.EALMPODESC FROM SFPC.TBALMOXARIFADOPORTAL A, SFPC.TBMOVIMENTACAOMATERIAL B ";
													$sql .= " WHERE A.FALMPOSITU	= 'A' AND A.CALMPOCODI = B.CALMPOCODI AND B.CMATEPSEQU = $Material ";
													$sql .= " ORDER BY A.EALMPODESC	";
													$res  = $db->query($sql);
													if( PEAR::isError($res) ){
															ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
													}else{
															$Rows = $res->numRows();
															if($Rows > 0){
																	echo "<select name=\"AlmoxarifadoSel\" class=\"textonormal\">\n";
																	echo "	<option value=\"\">Todos</option>\n";
																	for($i=0; $i< $Rows; $i++){
																			$Linha = $res->fetchRow();
																			$DescAlmoxarifado = $Linha[1];
																			if($Linha[0] == $AlmoxarifadoSel){
																					echo"<option value=\"$Linha[0]\" selected>$Linha[0]_$DescAlmoxarifado</option>\n";
																			}else{
																					echo"<option value=\"$Linha[0]\">$DescAlmoxarifado</option>\n";
																			}
																	}
																	echo "</select>\n";
															}else{
																	echo "ALMOXARIFADO NÃO CADASTRADO OU INATIVO";
															}
													}
													$db->disconnect();
													?>
												</td>
											</tr>
	       		 				<?php} ?>



	       		 		<?php} ?>
                		<tr>
				            	<td colspan="4" align="right">
			              		<input type="hidden" name="TipoMaterial" value="<?php echo $TipoMaterial; ?>">
			              		<input type="hidden" name="CodigoGrupo" value="<?php echo $CodigoGrupo; ?>">
                        <input type="hidden" name="Grupo" value="<?php echo $Grupo; ?>">
			              		<input type="hidden" name="DescGrupo" value="<?php echo $DescGrupo; ?>">
			              		<input type="hidden" name="Classe" value="<?php echo $Classe; ?>">
			              		<input type="hidden" name="DescClasse" value="<?php echo $DescClasse; ?>">
			              		<input type="hidden" name="Subclasse" value="<?php echo $Subclasse; ?>">
			              		<input type="hidden" name="DescSubclasse" value="<?php echo $DescSubclasse; ?>">
			              		<input type="hidden" name="Material" value="<?php echo $Material; ?>">
			              		<input type="hidden" name="DescMaterial" value="<?php echo $DescMaterial; ?>">
			              		<input type="hidden" name="DescMaterialComp" value="<?php echo $DescMaterialComp; ?>">
			              		<input type="hidden" name="DescUnidade" value="<?php echo $DescUnidade; ?>">
			              		<?phpif ( $Confirmar == 1 ){ ?>
			              		<input type="hidden" name="GrupoDestino" value="<?php echo $GrupoDestino; ?>">
			              		<input type="hidden" name="ClasseDestino" value="<?php echo $ClasseDestino; ?>">
			              		<input type="hidden" name="SubclasseDestino" value="<?php echo $SubclasseDestino; ?>">
							       		<input type="button" value="Confirmar Substituição" class="botao" onclick="javascript:enviar('Substituir');">
							       		<?php} ?>
							       		<input type="hidden" name="MaterialDestino" value="<?php echo $MaterialDestino; ?>">
							       		<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
							       		<input type="hidden" name="Confirmar" value="<?=$Confirmar;?>">
												<input type="hidden" name="Botao" value="">
											</td>
			            	</tr>
		     					</table>
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