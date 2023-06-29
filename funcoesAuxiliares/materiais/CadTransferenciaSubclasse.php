<?php
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadTransferenciaSubclasse.php
# Autor:    Carlos Abreu
# Data:     02/06/2006
# Objetivo: Programa de transferencia de subclasse de um material
#------------------
# Alterado: Álvaro Faria
# Data:     29/08/2006 - Opção de pesquisa de material com descrição "iniciada por"
# Alterado: Rodrigo Melo
# Data:     23/11/2007 - Correção do material na situação de transferência de uma classe para outra para atualizar também a tabela TBPREMATERIAL.
# Alterado: Rodrigo Melo
# Data:     16/01/2008 - Alteração para realizar transferência de todos os materiais de um subclasse para outra.
# Alterado: Rodrigo Melo
# Data:     01/02/2008 - Alteração para que o material ao ser tranferido de uma subclasse para outra seja incluido na tabela tbhistoricomaterial a alteração de subclasse do
#                                 referido material e permita a exclusão da subclasse caso não exista nenhuma movimentação com o material.
# Alterado: Rodrigo Melo
#	Data: 03/03/2008 - Não permitir tranferência de subclasses para subclasses inativas ou para ela mesma, remoção da integração com a tabela de histórico e permitir
#                            que o usuário apenas realize tranferência entre subclasses pertencentes ao mesmo grupo.
#	Data: 11/03/2008 - Correção para alterar a atualização dos pre-materiais quando realizar a transferia de subclasse de todos os materiais, foi colocado o Filtro: PREMAT.CGRUMSCODI = $CodigoGrupo AND PREMAT.CCLAMSCODI = $Subclasse.
# Alterado: Ariston Cordeiro
#	Data: 03/08/2008	- Correção de bug em tranferência de subclasses (materiais sem pré-materiais relacionados voltava com item nulo do id do pre-material, corrompendo a variável $PreMateriais que por sua vez fazia o update falhar.)
# Alterado: Ariston Cordeiro
#	Data: 14/08/2008	- Correção de bug em que, quando é feita pesquisa direta do material destino, o Grupo do Destino é passado como nulo.
# Alterado: Rodrigo Melo
# Data:     22/09/2009 - Alterando o nome das tabelas TBPREMATERIAL e TBPREMATERIALTIPOSITUACAO para TBPREMATERIALSERVICO e BPREMATERIALSERVICOTIPOSITUACAO, respectivamente (CR 2749).
#----------------------
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
AddMenuAcesso("/materiais/CadCorrecaoMaterialSelecionar.php");

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
		$OpcaoPesquisaSubClasseDestino		= $_POST['OpcaoPesquisaSubClasseDestino'];
		$SubclasseDescricaoDiretaDestino  = strtoupper(trim($_POST['SubclasseDescricaoDiretaDestino']));
		$TipoMaterialDestino							= $_POST['TipoMaterialDestino'];
		$GrupoDestino											= $_POST['GrupoDestino'];
		$ClasseDestino										= $_POST['ClasseDestino'];
		$SubclasseDestino									= $_POST['SubclasseDestino'];
		$Confirmar												= $_POST['Confirmar'];
		$RemoverSubclasse									= $_POST['RemoverSubclasse'];
    $TranferirTodos  									= $_POST['TranferirTodos'];
    $CodPreMaterial									  = $_POST['CodPreMaterial'];
}else{
		$Grupo     = $_GET['Grupo'];
		$Classe    = $_GET['Classe'];
		$Subclasse = $_GET['Subclasse'];
		$Material  = $_GET['Material'];
}

if( $Botao == "" ){
		# Pega os dados do Pré-Material de acordo com o código #
		$db   = Conexao();
    $sql  = "SELECT GRU.FGRUMSTIPM, GRU.EGRUMSDESC, CLA.ECLAMSDESC, SUB.ESUBCLDESC, ";
    $sql .= "       MAT.EMATEPDESC, MAT.EMATEPCOMP, MAT.CUNIDMCODI, UND.EUNIDMDESC, ";
    #$sql .= "       UND.CUNIDMCODI,MAT.EMATEPOBSE,MAT.CSUBCLSEQU, MAT.CPREMACODI ";
    $sql .= "       UND.CUNIDMCODI,MAT.EMATEPOBSE,MAT.CSUBCLSEQU, MAT.CPREMACODI, GRU.CGRUMSCODI ";
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
				$Unidade       		= $Linha[8];
				$Observacao       = $Linha[9];
				$NCaracteresO     = strlen($Observacao);
				$Subclasse        = $Linha[10];
        $CodPreMaterial   = $Linha[11];
        $CodigoGrupo      = $Linha[12];
		}
		$db->disconnect();
}

# caso deja pesquisa direta de grupo destino, grupo destino vem nupo pois está acossiado ao list box da pesquisa indireta.
# Em todo caso, Grupo do Destino deve ser igual a Grupo do Fonte, pois a transferencia nao deve ocorrer entre grupos.
if(($SubclasseDescricaoDiretaDestino!="")&&($GrupoDestino == "")){
	$GrupoDestino = $CodigoGrupo;
}

echo "[".$GrupoDestino."]";
echo "[".$CodigoGrupo."]";

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Monta o sql para montagem dinâmica da grade a partir da pesquisa #
$sql    = "SELECT DISTINCT(GRU.CGRUMSCODI),GRU.EGRUMSDESC,CLA.CCLAMSCODI,CLA.ECLAMSDESC, ";
$sql   .= "       SUB.CSUBCLSEQU,SUB.ESUBCLDESC,GRU.FGRUMSTIPM ";
$from   = "  FROM SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, ";
$from  .= "       SFPC.TBSUBCLASSEMATERIAL SUB ";
$where  = " WHERE SUB.CGRUMSCODI = CLA.CGRUMSCODI ";
$where .= "   AND SUB.CCLAMSCODI = CLA.CCLAMSCODI AND CLA.CGRUMSCODI = GRU.CGRUMSCODI ";
$where .= "   AND GRU.FGRUMSSITU = 'A' AND CLA.FCLAMSSITU = 'A' AND SUB.FSUBCLSITU = 'A' ";

$where .= "   AND SUB.CSUBCLSEQU <> $Subclasse "; //Não permite que a seja feita tranferência de subclasse de materiais para ela mesma.

$order  = "ORDER BY GRU.FGRUMSTIPM, GRU.EGRUMSDESC, CLA.ECLAMSDESC, SUB.ESUBCLDESC";
if($SubclasseDescricaoDiretaDestino == ""){
		# Verifica se o Tipo de Material foi escolhido #
		if( $TipoMaterialDestino != ""){
				$where .= " AND GRU.FGRUMSTIPM = '$TipoMaterialDestino' ";
		}

		# Verifica se o Grupo foi escolhido #
		if( $GrupoDestino != ""){
				$where .= " AND GRU.CGRUMSCODI = $GrupoDestino ";
		}

		# Verifica se a Classe foi escolhida #
		if( $ClasseDestino != "" ){
				$where .= " AND CLA.CGRUMSCODI = $GrupoDestino AND CLA.CCLAMSCODI = $ClasseDestino ";
		}

		# Verifica se a SubClasse foi escolhida #
		if( $SubclasseDestino != "" ){
				$where .= " AND SUB.CSUBCLSEQU = $SubclasseDestino ";
		}
}else{ # Se foi digitado algo na caixa de texto da subclasse em pesquisa direta #
		if( $OpcaoPesquisaSubClasseDestino == 0 ){
			  if( SoNumeros($SubclasseDescricaoDiretaDestino) ){
//	    	  	$where .= " AND SUB.CSUBCLSEQU = $SubclasseDescricaoDiretaDestino ";
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

# Gera o SQL com a concatenação das variaveis $sql,$from,$where,$order #
$sqlgeral = $sql.$from.$where.$order;

if( $SubclasseDescricaoDiretaDestino != "" and $OpcaoPesquisaSubClasseDestino == 0 and ! SoNumeros($SubclasseDescricaoDiretaDestino) ){
	  if( $Mens == 1 ){ $Mensagem .= ", "; }
  	$Mens = 1;
  	$Tipo = 2;
  	$Mensagem .= "<a href=\"javascript:document.CadTransferenciaSubclasse.SubclasseDescricaoDiretaDestino.focus();\" class=\"titulo2\">Código reduzido da Subclasse</a>";
}

if( $Botao == "Voltar" ){
		header("location: CadCorrecaoMaterialSelecionar.php");
		exit;
}elseif( $Botao == "Transferir" ){
	$db   = Conexao();

	# Altera dados na Tabela de Materiais #
  $db->query("BEGIN TRANSACTION");

  //Obtem o Código dos Pré-Materiais para atualiza-los, caso ocorra uma tranferência de todos os materais.
  if($TranferirTodos == 'S'){
    $sqlPreMaterial   = "SELECT MAT.CPREMACODI FROM SFPC.TBMATERIALPORTAL MAT ";
    $sqlPreMaterial  .= "  WHERE MAT.CSUBCLSEQU = $Subclasse ";
    $resPreMaterial   = $db->query($sqlPreMaterial);


    if( PEAR::isError($resPreMaterial) ){
      $db->query("ROLLBACK");
      ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlPreMaterial");
      exit;
    }else{
      $ArrayPreMateriais = array();
      for($i = 0; $i < $resPreMaterial->numRows(); $i++){
        $Linha = $resPreMaterial->fetchRow();
				if(($Linha[0]!="")&&($Linha[0]!=null)){
					$ArrayPreMateriais[$i] = $Linha[0];
				}
      }

      if(count($ArrayPreMateriais) > 0){
        $PreMateriais = implode(",", $ArrayPreMateriais);
      } else {
        $PreMateriais = null;
      }
    }
  }
  $sql  = "UPDATE SFPC.TBMATERIALPORTAL SET CSUBCLSEQU = $SubclasseDestino, CUSUPOCODI = ".$_SESSION['_cusupocodi_'].", TMATEPULAT = '".date("Y-m-d H:i:s")."' ";

  if($TranferirTodos == 'S'){ //Transfere todos os materias de uma subclasse
    $sql .= "WHERE CSUBCLSEQU = $Subclasse";
  } else { //Transfere apenas um material de uma subclasse de origem para uma subclasse de destino.
    $sql .= "WHERE CMATEPSEQU = $Material";
  }
  $res  = $db->query($sql);
	if( PEAR::isError($res) ){
			$db->query("ROLLBACK");
	    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	}else{
      # Para os materiais cadastrados sem o código do pré-material, não deve ser modificado a classe e sub-classe do pré-material, uma vez que este não exite, ou seja, é nulo
      if ($CodPreMaterial != null || $PreMateriais != null) {
        $sql   = "UPDATE SFPC.TBPREMATERIALSERVICO PREMAT SET CGRUMSCODI = SUBCL.CGRUMSCODI, CCLAMSCODI = SUBCL.CCLAMSCODI, TPREMAULAT = '".date("Y-m-d H:i:s")."' ";
        $sql  .= "FROM TBSUBCLASSEMATERIAL SUBCL ";

        if($TranferirTodos == 'S'){ //Transfere todos os materias de uma subclasse
           $sql  .= "WHERE SUBCL.CSUBCLSEQU = $SubclasseDestino AND PREMAT.CPREMACODI IN ($PreMateriais) ";
        } else { //Transfere apenas um material de uma subclasse de origem para uma subclasse de destino.
           $sql  .= "WHERE SUBCL.CSUBCLSEQU = (SELECT CSUBCLSEQU FROM TBMATERIALPORTAL WHERE CPREMACODI = $CodPreMaterial) ";
           $sql  .= "AND PREMAT.CPREMACODI = $CodPreMaterial ";
        }

        $res  = $db->query($sql);
        if(PEAR::isError($res)) {
          $db->query("ROLLBACK");
          ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
        }
      }

      if ( $RemoverSubclasse == "R" ){
            $sql  = "SELECT COUNT(*) FROM SFPC.TBMATERIALPORTAL WHERE CSUBCLSEQU = $Subclasse";
            $res  = $db->query($sql);

            if( PEAR::isError($res) ){
                $db->query("ROLLBACK");
                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
            } else {
                $Linha = $res->fetchRow();
                if ( $Linha[0] == 0 ){
                      //Removendo a subclasse.
                      $sql  = "DELETE FROM SFPC.TBSUBCLASSEMATERIAL WHERE CSUBCLSEQU = $Subclasse";
                      $res  = $db->query($sql);
                      if( PEAR::isError($res) ){
                          $db->query("ROLLBACK");
                          ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                          $erro = 1;
                      }
                      $Mensagem = " e Subclasse Origem Excluída com Sucesso";
                } else {
                    if ( $Linha[0] == 1 ){
                        $Mensagem = " e Subclasse Origem não pode ser excluída por possuir 1 item relacionado";
                    } else {
                        $Mensagem = " e Subclasse Origem não pode ser excluída por possuir $Linha[0] itens relacionados";
                    }
                }
            }
      }


			if ( $erro != 1 ){
					$db->query("COMMIT");
					$db->query("END TRANSACTION");
					$db->disconnect();

					# Redireciona para a tela de Seleção de Correção #
					$Mensagem = urlencode("Subclasse do Material Alterada com Sucesso".$Mensagem);
					$Url = "CadCorrecaoMaterialSelecionar.php?Mens=1&Tipo=1&Mensagem=$Mensagem";
					if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
					header("location: ".$Url);
					exit;
			}
	}
	$db->disconnect();
}elseif($Botao == "Validar"){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( $SubclasseDescricaoDiretaDestino != "" and $OpcaoPesquisaSubClasseDestino == 0 and ! SoNumeros($SubclasseDescricaoDiretaDestino) ){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadTransferenciaSubclasse.OpcaoPesquisaSubClasseDestino.focus();\" class=\"titulo2\">Código reduzido da Subclasse</a>";
		}elseif($SubclasseDescricaoDiretaDestino != "" and ($OpcaoPesquisaSubClasseDestino == 1 or $OpcaoPesquisaSubClasseDestino == 2) and strlen($SubclasseDescricaoDiretaDestino)< 2){
				if($Mens == 1){ $Mensagem .= ", "; }
				$Mens = 1;
				$Tipo = 2;
				$Mensagem .= "<a href=\"javascript:document.CadTransferenciaSubclasse.OpcaoPesquisaSubClasseDestino.focus();\" class=\"titulo2\">Descrição com no mínimo 2 caracteres</a>";
		}
}

/*echo "[".$GrupoDestino."]";
echo "[".$CodigoGrupo."]";*/



if (($SubclasseDestino)&&($Mens!=1)){
		$db   = Conexao();
		$sql  = "SELECT GRU.FGRUMSTIPM, GRU.EGRUMSDESC, CLA.ECLAMSDESC, SUB.ESUBCLDESC ";
		$sql .= "  FROM SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, ";
		$sql .= "       SFPC.TBSUBCLASSEMATERIAL SUB ";
		$sql .= " WHERE SUB.CGRUMSCODI = CLA.CGRUMSCODI AND SUB.CCLAMSCODI = CLA.CCLAMSCODI ";
		$sql .= "   AND CLA.CGRUMSCODI = GRU.CGRUMSCODI AND SUB.CSUBCLSEQU = $SubclasseDestino AND SUB.CGRUMSCODI = $GrupoDestino";
		$res  = $db->query($sql);
		if( PEAR::isError($res) ){
				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		}else{
				$Linha         		= $res->fetchRow();
				$TipoMaterialDestino  		= $Linha[0];
				$DescGrupoDestino     		= $Linha[1];
				$DescClasseDestino    		= $Linha[2];
				$DescSubclasseDestino 		= $Linha[3];
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
	document.CadTransferenciaSubclasse.GrupoDestino.value  = '';
	document.CadTransferenciaSubclasse.ClasseDestino.value = '';
	document.CadTransferenciaSubclasse.submit();
}
function enviar(valor){
	document.CadTransferenciaSubclasse.Botao.value = valor;
	document.CadTransferenciaSubclasse.submit();
}
function validapesquisa(){
	if( document.CadTransferenciaSubclasse.SubclasseDestino ){
    document.CadTransferenciaSubclasse.SubclasseDestino.value = "";
  }
  document.CadTransferenciaSubclasse.Botao.value = 'Validar';
  document.CadTransferenciaSubclasse.submit();
}

<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadTransferenciaSubclasse.php" method="post" name="CadTransferenciaSubclasse">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Correção > Transferência de Subclasse
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if($Mens == 1){?>
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
      <table border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff" summary="">
				<tr>
					<td class="textonormal">
						<table border="0" cellspacing="0" cellpadding="0" summary="">
							<tr>
				      	<td class="textonormal">
				        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
				          	<tr>
				            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
					    					TRANSFERÊNCIA DE SUBCLASSE DE MATERIAIS
					          	</td>
					        	</tr>
					        	<?php if ( $Confirmar == 1 ){ ?>
					        	<tr>
				    	      	<td class="textonormal">
												<p align="justify">
							             Clique no botão Confirmar Transferência para concluir a correção ou no botão Voltar para iniciar o processo.
				          	   	</p>
				          		</td>
					        	</tr>
					        	<?php } ?>
					        	<tr>
				            	<td align="center" bgcolor="#BFDAF2" valign="middle" class="titulo3">
					    					MATERIAL
					          	</td>
					        	</tr>
					        	<tr>
											<td>
												<table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
													<tr>
														<td>
									      	    <table class="textonormal" border="0" width="100%" summary="">
									      	    	<tr>
										            	<td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Material</td>
									  	        		<td class="textonormal"><?php echo $Material;?></td>
									  	        	</tr>
										        		<tr>
										            	<td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Descrição</td>
									  	        		<td class="textonormal"><?php echo $DescMaterial; ?></td>
										        		</tr>
									          	</table>
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
				            	<td align="center" bgcolor="#BFDAF2" valign="middle" class="titulo3">
					    					SUBCLASSE ORIGEM
					          	</td>
					        	</tr>
										<tr>
				    	      	<td class="textonormal">
												<p align="justify">
							            <table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
													<tr>
														<td>
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
											            <td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Transferir todos os materiais da Subclasse</td>
										  	        	<td class="textonormal"><input name="TranferirTodos" type="checkbox" value="S" <?php if ($TranferirTodos){echo " checked";}?>></td>
											        	</tr>
											        	<tr>
											            <td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Remover Subclasse</td>
										  	        	<td class="textonormal"><input name="RemoverSubclasse" type="checkbox" value="R"<?php if ($RemoverSubclasse){echo " checked";}?>></td>
											        	</tr>
									          	</table>
														</td>
													</tr>
												</table>
				          	   	</p>
				          		</td>
					        	</tr>
					        	<tr>
				            	<td align="center" bgcolor="#BFDAF2" valign="middle" class="titulo3">
					    					SUBCLASSE DESTINO
					          	</td>
					        	</tr>
					        	<?php if ( $Confirmar != 1 ) { ?>
					        	<tr>
				    	      	<td class="textonormal">
												<p align="justify">
							             Para pesquisar uma subclasse já cadastrada, preencha o argumento da pesquisa.
										       Depois, clique na subclasse desejada.
				          	   	</p>
				          		</td>
					        	</tr>
					        	<tr>
		        				  <td align="center" bgcolor="#DCEDF7" class="titulo3">PESQUISA DIRETA</td>
		        				</tr>
		        				<tr>
		          				<td>
		            				<table border="0" width="100%" summary="">
			            				<tr>
														<td class="textonormal" bgcolor="#DCEDF7" width="150">Subclasse</td>
														<td class="textonormal">
															<select name="OpcaoPesquisaSubClasseDestino" class="textonormal">
																<option value="0">Código Reduzido</option>
																<option value="1">Descrição contendo</option>
																<option value="2">Descrição iniciada por</option>
															</select>
		         	        				<input type="text" name="SubclasseDescricaoDiretaDestino" size="10" maxlength="10" class="textonormal">
						           	      <a href="javascript:<?php
						           	      	if ($GrupoDestino!=""){echo "CadTransferenciaSubclasse.GrupoDestino.selectedIndex=0;";}
																if ($ClasseDestino!=""){echo "CadTransferenciaSubclasse.ClasseDestino.selectedIndex=0;";}
																if ($SubclasseDestino!=""){echo "CadTransferenciaSubclasse.SubclasseDestino.selectedIndex=0;";}
						           	      	?>validapesquisa();"><img src="../midia/lupa.gif" border="0"></a>
														</td>
													</tr>
		            				</table>
		          				</td>
		        				</tr>
						        <tr>
		        				  <td align="center" bgcolor="#DCEDF7" class="titulo3">PESQUISA POR FAMILIA</td>
		        				</tr>
					        	<tr>
											<td>
												<table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
													<tr>
														<td>
									      	    <table class="textonormal" border="0" width="100%" summary="">
															  <tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Tipo de Material</td>
																	<td class="textonormal">

                                  <?php
                                      //Tornando fixo o tipo do material para apenas realizar substituições de materiais no mesmo tipo e grupo do material.
                                      $TipoMaterialDestino = $TipoMaterial;
                                    ?>

																	<input type="radio" name="TipoMaterialDestino" value="C" onClick="javascript:<?php
																if ($GrupoDestino!=""){echo "GrupoDestino.selectedIndex=0;";}
																if ($ClasseDestino!=""){echo "ClasseDestino.selectedIndex=0;";}
																if ($SubclasseDestino!=""){echo "SubclasseDestino.selectedIndex=0;";}
																echo "submit();\" ";
                                if( $TipoMaterialDestino == "C" ){ echo "checked"; } else { echo "disabled"; }
																?>> Consumo
																<input type="radio" name="TipoMaterialDestino" value="P" onClick="javascript:<?php
																if ($GrupoDestino!=""){echo "GrupoDestino.selectedIndex=0;";}
																if ($ClasseDestino!=""){echo "ClasseDestino.selectedIndex=0;";}
																if ($SubclasseDestino!=""){echo "SubclasseDestino.selectedIndex=0;";}
																echo "submit();\" ";
                                if( $TipoMaterialDestino == "P" ){ echo "checked"; } else { echo "disabled"; }
																?>> Permanente
																	</td>
									            	</tr>
									            	<?php if( $TipoMaterialDestino != "" ){ ?>
									            	<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Grupo</td>
																	<td class="textonormal">
                                    <?php
                                      //Tornando fixo o grupo do material para apenas realizar substituições de materiais no mesmo tipo e grupo.
                                      $GrupoDestino = $CodigoGrupo;
                                    ?>
																	  <select name="GrupoDestino" style="background-color:#FFFFFF" onChange="<?php
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
                                             if($Linha = $result->fetchRow()){
					          	      							      $Descricao   = substr($Linha[1],0,75);
										          	      			    //if( $Linha[0] == $GrupoDestino ){
										    	      							      echo"<option value=\"$Linha[0]\" selected>$Descricao</option>\n";
												      	      		      //}else{
										    	      							  //    echo"<option value=\"$Linha[0]\">$Descricao</option>\n";
												      	      		      //}
								      	      				       }
																		      }
															        }
																	    ?>
																	  </select>
																	</td>
									            	</tr>
									            	<?php
										            } else {
										            		echo "<input type=\"hidden\" name=\"GrupoDestino\" value=\"$GrupoDestino\">";
										            }
									         		  if( $GrupoDestino != ""){
									         		  ?>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Classe </td>
																	<td class="textonormal">
																		<select name="ClasseDestino" class="textonormal" onChange="<?php
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
									            	} else {
									            			echo "<input type=\"hidden\" name=\"ClasseDestino\" value=\"$ClasseDestino\">";
									            	}
																if( $GrupoDestino != "" and $ClasseDestino != "" ){
																?>
										        		<tr>
											            <td class="textonormal" bgcolor="#DCEDF7" height="20" width="150">Subclasse</td>
										  	        	<td class="textonormal">
																	  <select name="SubclasseDestino" onChange="submit();" class="textonormal">
																			<option value="">Selecione uma Subclasse...</option>
																	    <?php
																			$db = Conexao();
																			//$sql = "SELECT CSUBCLSEQU,ESUBCLDESC FROM SFPC.TBSUBCLASSEMATERIAL ";
																			//$sql .= "WHERE CGRUMSCODI = '$GrupoDestino' AND CCLAMSCODI = '$ClasseDestino' AND ORDER BY ESUBCLDESC";

                                      $sql   = "  SELECT SUB.CSUBCLSEQU, SUB.ESUBCLDESC ";
                                      $sql  .= "  FROM SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, ";
                                      $sql  .= "       SFPC.TBSUBCLASSEMATERIAL SUB ";
                                      $sql  .= " WHERE SUB.CGRUMSCODI = CLA.CGRUMSCODI ";
                                      $sql  .= "   AND SUB.CCLAMSCODI = CLA.CCLAMSCODI AND CLA.CGRUMSCODI = GRU.CGRUMSCODI ";
                                      $sql  .= "   AND GRU.FGRUMSSITU = 'A' AND CLA.FCLAMSSITU = 'A' AND SUB.FSUBCLSITU = 'A' ";
                                      $sql  .= "   AND SUB.CGRUMSCODI = '$GrupoDestino' AND SUB.CCLAMSCODI = '$ClasseDestino' ";
                                      $sql  .= "    AND SUB.CSUBCLSEQU <> $Subclasse "; //Não permite que a seja feita tranferência de subclasse de materiais para ela mesma.
                                      $sql  .= "    ORDER BY ESUBCLDESC ";


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
										  	        	</td>
											        	</tr>
											        	<?php
											        	} else {
											        			echo "<input type=\"hidden\" name=\"SubclasseDestino\" value=\"$SubclasseDestino\">";
											        	}
											        	?>
									          	</table>
														</td>
													</tr>
												</table>
											</td>
										</tr>
					          <?php
										if( $SubclasseDescricaoDiretaDestino != "" && $OpcaoPesquisaSubClasseDestino == 0 ){
												if( !SoNumeros($SubclasseDescricaoDiretaDestino) ){ $sqlgeral = ""; }
										}
										if( $sqlgeral != "" and $Mens == 0) {
												if( ( $SubclasseDescricaoDiretaDestino != "" ) or	( $SubclasseDestino != "" ) ){
														$db     = Conexao();
														$res    = $db->query($sqlgeral);
														if( PEAR::isError($res) ){
																ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlgeral");
														}else{
																$qtdres = $res->numRows();
																echo "<tr>\n";
																echo "  <td align=\"center\" bgcolor=\"#BFDAF2\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
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
																				if( $TipoMaterialAntes != $TipoMaterialCodigo ) {
									    											echo "<tr>\n";
									    											echo "  <td class=\"textoabason\" bgcolor=\"#BFDAF2\" align=\"center\">";
								         										if($TipoMaterialCodigo == "C"){ echo "CONSUMO"; }else{ echo "PERMANENTE";}
								  		    									echo "  </td>\n";
								  				    							echo "</tr>\n";
								    										}
																				if( $GrupoAntes != $GrupoDescricao ) {
								    						            if( $ClasseAntes != $ClasseDescricao ) {
								    						            		echo "<tr>\n";
								      											    echo "  <td class=\"textoabason\" bgcolor=\"#DDECF9\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
								      											    echo "</tr>\n";
								      											}
								   											}else{
								    						            if( $ClasseAntes != $ClasseDescricao ) {
								    						            		echo "<tr>\n";
								      											    echo "  <td class=\"textoabason\" bgcolor=\"#DDECF9\" align=\"center\">$GrupoDescricao / $ClasseDescricao</td>\n";
								      											    echo "</tr>\n";
								      											}
								   										  }
								    										if( $ClasseAntes != $ClasseDescricao ) {
								    											  echo "<tr>\n";
								      											echo "  <td class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"30%\">SUBCLASSE</td>\n";
								      											echo "</tr>\n";
								    										}
								    										echo "<tr>\n";
						      											echo "  <td valign=\"top\" bgcolor=\"#F7F7F7\" class=\"textonormal\" width=\"30%\">\n";
						      											echo "    <a href=\"javascript:CadTransferenciaSubclasse.Confirmar.value=1;CadTransferenciaSubclasse.SubclasseDestino.value=$SubClasseSequ;CadTransferenciaSubclasse.submit();\"><font color=\"#000000\">$SubClasseDescricao</font></a>";
						      											echo "  </td>\n";
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
																		echo "	<td valign=\"top\" class=\"textonormal\" bgcolor=\"FFFFFF\">\n";
																		echo "		Pesquisa sem Ocorrências.\n";
																		echo "	</td>\n";
																		echo "</tr>\n";
																}
														}
												}
										}
								}else{
									?>
									<tr>
										<td class="textonormal">
											<p align="justify">
												<table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
													<tr>
														<td>
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
										  	        	<input type="hidden" name="SubclasseDestino" value="<?php echo $SubclasseDestino; ?>">
											        	</tr>
									          	</table>
														</td>
													</tr>
												</table>
											</p>
										</td>
									</tr>
									<?php
								}
								?>
										<tr>
											<td align="right">
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
                        <input type="hidden" name="CodPreMaterial" value="<?php echo $CodPreMaterial; ?>">
												<?php
												if($Confirmar == 1){ echo "<input type=\"button\" value=\"Confirmar Transferência\" class=\"botao\" onclick=\"javascript:enviar('Transferir');\">"; }
												?>
												<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
												<input type="hidden" name="Confirmar" value="<?php $Confirmar;?>">
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