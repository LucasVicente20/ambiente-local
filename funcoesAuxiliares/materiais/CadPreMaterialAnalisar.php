<?php
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadPreMaterialAnalisar.php
# Objetivo: Programa de Análise de Pré-inclusão de Material
# Autor:    Roberta Costa
# Data:     25/04/2005
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     19/12/2006 - Alteração de crítica e label de Observação para 100 caracteres
#                        Alteração de campos de descrição e descrição completa
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     26/12/2006 - Correção de transação / Retirada de quebra de linha da descrição do material
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     27/12/2006 - Correção de transação
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Carlos Abreu
# Data:     03/09/2007 - Ajuste para enviar por email tambem a descricao do material quando aprovado
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     21/11/2007 - Ajuste para gravar o código do pré-material na tabela de material (SFPC.TBMATERIALPORTAL)
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     27/11/2007 - Alteração para não atualizar a tabela de prematerial e não inserir novamente o mesmo material ao
#                                       clicar mais de uma vez no botão "Confirmar".
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     26/12/2007 - Alteração do campo "Classe" para exibir 150 caracteres.
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     01/08/2008 - Alteração do campo "Observação" para exibir 150 caracteres.
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     18/01/2008 - Alteração do campo "Descrição Completa" ser obrigatório, além de manter a impossibilidade de alterar a unidade de medida dos materiais.
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     18/01/2008 - Correção para evitar que o tipo do material seja alterado de consumo para permanente e seja exibido uma mensagem ao usuário quando o pré-material já tiver sido cadastrado.
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     21/01/2008 - Correção para que a crítica em que um pré-material já tenha sido cadastrado seja apenas quando o mesmo for para a situação aprovado.
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     31/01/2008 - Alteração para que seja inserido na tabela SFPC.TBhistoricomaterial quando o pre-material ter sido aprovado.
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     03/03/2008 - Remoção da integração com a tabela de histórico.
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     02/07/2008 - Correção da aprovação de material para inserir Caracteres ''. Exemplo: VÁLVULA DE RETENÇÃO 3/4''.
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     21/08/2008 - Alteração para realizar a análise de serviços pré-cadastrados
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     22/09/2009 - Alterando o nome das tabelas SFPC.TBPREMATERIAL e SFPC.TBPREMATERIALTIPOSITUACAO para SFPC.TBPREMATERIALSERVICO e BPREMATERIALSERVICOTIPOSITUACAO, respectivamente (CR 2749).
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# Autor:    Everton Lino
# Data:     11/08/2010- Remoção de menssagem de código de pré-cadastro
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     04/02/2011 - #1546 Red Mine- Campo de descrição de serviços vai de 300 para 500 caracteres
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     17/05/2011 - Tarefa do Redmine: 2694 - Campo de descrição de serviços vai de 500 para 700 caracteres
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Heraldo
# Data:     03/Out/2013 - Formatar colunas para tirar erro de query
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# OBS.:     Tabulação 2 espaços
# ----------------------------------------------------------------------------------------------------------------------------------------------------------------------
# Alterado: Pitang Agile IT - Caio Coutinho
# Data:     18/12/2018
# Objetivo: 207930
#----------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 13/09/2021
# Objetivo: CR #252482
#---------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 22/02/2022
# Objetivo: CR #259129
#---------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 07/04/2022
# Objetivo: CR #261839
#---------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança	#
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/materiais/CadPreMaterialAnalisarSelecionar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST" ){
		$Botao             = $_POST['Botao'];
		$TipoMaterial      = $_POST['TipoMaterial'];
		$Grupo             = $_POST['Grupo'];
		$DescGrupo         = $_POST['DescGrupo'];
		$Classe            = $_POST['Classe'];
		$DescClasse        = $_POST['DescClasse'];
		$Subclasse         = $_POST['Subclasse'];
		$Unidade           = $_POST['Unidade'];
		$DescUnidade       = $_POST['DescUnidade'];
		$NCaracteresM      = $_POST['NCaracteresM'];
		$NCaracteresC      = $_POST['NCaracteresC'];
		$NCaracteresO      = $_POST['NCaracteresO'];
		$PreMaterialServico = $_POST['PreMaterialServico'];
		$TipoGrupo         = $_POST['TipoGrupo'];
		$DescMaterial      = RetiraAcentosVirgula($_POST['DescMaterial']);
		$DescMaterialComp  = RetiraAcentosVirgula($_POST['DescMaterialComp']); 
		$Observacao        = RetiraAcentosVirgula($_POST['Observacao']); 
		
		$Situacao          = $_POST['Situacao'];
		$SituacaoAtualDesc = $_POST['SituacaoAtualDesc'];
		$SituacaoAtualCodi = $_POST['SituacaoAtualCodi'];
		$ResponsavelEmail  = $_POST['ResponsavelEmail'];
		$ResponsavelNome   = $_POST['ResponsavelNome'];
}else{
		$Grupo             = $_GET['Grupo'];
		$Classe            = $_GET['Classe'];
		$PreMaterialServico          = $_GET['PreMaterialServico'];
		$TipoGrupo         = $_GET['TipoGrupo'];
		$Mens              = $_GET['Mens'];
		$Tipo              = $_GET['Tipo'];
		$Mensagem          = $_GET['Mensagem'];

}

# Ambiente de desenvolvimento
$AMBIENTE_DESENVOLVIMENTO = true;



$ErroPrograma = __FILE__;


# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

//Variáveis dinâmicas para colocar as informações para material ou serviço.
if($TipoGrupo == 'M') {
   $QtdeCaracteres = 300;
   $DescricaoTipoGrupo = "Material";
} else {
	$QtdeCaracteres = 700;
	$DescricaoTipoGrupo = "Serviço";
}


if($Botao == "Voltar"){
		header("location: CadPreMaterialAnalisarSelecionar.php");
		exit;
}elseif($Botao == "Confirmar"){
		$Mens     = 0;
		$Mensagem = "Informe: ";
		if( ( $Subclasse == "" ) and ( $Situacao == 2) and $TipoGrupo == 'M'){
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadPreMaterialAnalisar.Subclasse.focus();\" class=\"titulo2\">Subclasse</a>";
		}
		# Campo obrigatório #
		if($DescMaterial == ""){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadPreMaterialAnalisar.DescMaterial.focus();\" class=\"titulo2\">$DescricaoTipoGrupo</a>";
		}else{
				if(strlen($DescMaterial) > 300){
						if( $Mens == 1 ){ $Mensagem .= ", "; }
						$Mens      = 1;
						$Tipo      = 2;
						$Mensagem .= "<a href=\"javascript:document.CadPreMaterialAnalisar.DescMaterial.focus();\" class=\"titulo2\">$DescricaoTipoGrupo no Máximo com ".$QtdeCaracteres." Caracteres</a>";
				}
		}
    if($DescMaterialComp == "" and $TipoGrupo == 'M'){
			if( $Mens == 1 ){ $Mensagem .= ", "; }
        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.CadPreMaterialAnalisar.DescMaterialComp.focus();\" class=\"titulo2\">Descrição Completa do Material</a>";
		}else{
			
  		if(strlen($DescMaterialComp) > 3000){
  				if( $Mens == 1 ){ $Mensagem .= ", "; }
  				$Mens      = 1;
  				$Tipo      = 2;
  				$Mensagem .= "<a href=\"javascript:document.CadPreMaterialAnalisar.DescMaterialComp.focus();\" class=\"titulo2\">Descrição Completa do Material no Máximo com 3000 Caracteres</a>";
  		}
    }
		if($Situacao == 1){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadPreMaterialAnalisar.Situacao.focus();\" class=\"titulo2\">Situação diferente da situação atual</a>";
		}
		if(strlen($Observacao) > 2000){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadPreMaterialAnalisar.Observacao.focus();\" class=\"titulo2\">Observação no Máximo com 2000 Caracteres</a>";
		}



		if($Mens == 0){
			# Atribuindo NULL aos Campos não obrigatórios #
			if( $Observacao       == "" ){ $Obs          = "NULL"; }else{ $Obs          = "'".$Observacao."'"; }

      //Validação para não inserir novamente o mesmo pré-material ao clicar mais de uma vez no botão "Confirmar"
			$db   = Conexao();

      //Caso a situação seja do tipo 2 -> "APROVADO" verificar se já existe cadastrado, senão, não deve-se levar em consideração
      //Pois, ou já está cadastrado ou então não foi foi aprovado.
      $Qtd = null;
      if($Situacao == 2){
  			$sql  = "SELECT COUNT(*) FROM SFPC.TBPREMATERIALSERVICO ";
  			$sql .= " WHERE EPREMADESC = '$DescMaterial' AND CGRUMSCODI = $Grupo ";
  			$sql .= " AND CCLAMSCODI = $Classe AND CPREMSCODI = $Situacao";

  			$res  = $db->query($sql);

  			if( PEAR::isError($res) ){
  				ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
  			} else {
          $Qtd = $res->fetchRow();
        }
      } else {
        $Qtd = array(0);
      }

	  $CodUsuario = $_SESSION['_cusupocodi_'];
      if($Qtd[0] == 0){
        $db->query("BEGIN TRANSACTION");
        $sql  = "UPDATE SFPC.TBPREMATERIALSERVICO ";
        $sql .= "SET CPREMSCODI = $Situacao, EPREMAOBSE = $Obs, TPREMAULAT = '".date("Y-m-d H:i:s")."', CUSUPOCOD1 = $CodUsuario";
        $sql .= " WHERE CGRUMSCODI = $Grupo AND CCLAMSCODI = $Classe ";
        $sql .= "AND CPREMACODI = $PreMaterialServico ";
        $res  = $db->query($sql);
        if( PEAR::isError($res) ){
            $db->query("ROLLBACK");
            ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
        }else{
          # Aprovado #
          if($Situacao == 2){
		  	  if($TipoGrupo == 'M'){ //INSERINDO UM MATERIAL (M)

	              # Pega o Máximo para criar o Código do Material #
	              $sql  = "SELECT MAX(CMATEPCODI) FROM SFPC.TBMATERIALPORTAL ";
	              $res  = $db->query($sql);
	              if( PEAR::isError($res) ){
	                  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	              }else{
	                  $Maximo = $res->fetchRow();
	                  if( $Maximo[0] == 0 ){ $CodMaterial = 1; }else{ $CodMaterial = $Maximo[0] + 1; }
	              }
	              # Inclui na Tabela de Material Portal #
	              $sql  = "INSERT INTO SFPC.TBMATERIALPORTAL( ";
	              $sql .= "CMATEPSEQU, CMATEPCODI, CPREMACODI, CSUBCLSEQU, EMATEPDESC, EMATEPCOMP, ";
	              $sql .= "CUNIDMCODI, CMATEPSITU, TMATEPULAT, EMATEPOBSE, CUSUPOCODI ";
	              $sql .= ") VALUES ( ";
	              $sql .= "nextval('sfpc.tbmaterialportal_cmatepsequ_seq'),$CodMaterial,$PreMaterialServico,$Subclasse, ";
	              $sql .= "'$DescMaterial','$DescMaterialComp',$Unidade,'A','".date("Y-m-d H:i:s")."', $Obs, ".$_SESSION['_cusupocodi_'].")";
	              $res  = $db->query($sql);
	              if( PEAR::isError($res) ){
	                  $Rollback = 1;
	                  $db->query("ROLLBACK");
	                  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	                  exit;
	              }

			  } else {//INSERINDO UM SERVIÇO (S)
	            # Inclui na Tabela de Serviço Portal #
	            $sql  = "INSERT INTO SFPC.TBSERVICOPORTAL( ";
	            $sql .= "CSERVPSEQU, CPREMACODI, ";
	            $sql .= "CGRUMSCODI, CCLAMSCODI, ESERVPDESC, CSERVPSITU, ";
	            $sql .= "CGREMPCODI, CUSUPOCODI, TSERVPULAT, ESERVPOBSE ";
	            $sql .= ") VALUES ( ";
	            $sql .= "nextval('sfpc.tbservicoportal_cservpsequ_seq'), $PreMaterialServico, ";
	            $sql .= "$Grupo, $Classe, '$DescMaterial', 'A', ";
	            $sql .= " ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].",'".date("Y-m-d H:i:s")."', $Obs)";
	            $res  = $db->query($sql);

	            if( PEAR::isError($res) ){
	                $Rollback = 1;
	                $db->query("ROLLBACK");
	                ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
	                exit;
	            }
		  	  }
            }
          if(!$Rollback){
              $db->query("COMMIT");
              $db->query("END TRANSACTION");
              $db->disconnect();
          }
          if ($SituacaoAtualCodi <> $Situacao){
			
              if($Situacao == 2){ #Aprovado
                  $db = Conexao();
                  # Envia uma mensagem pelo e-mail do usuário #
                  if($TipoGrupo == 'M'){ //SQL para buscar o Material (M)
                  	$sql  = "SELECT CMATEPSEQU, EMATEPDESC FROM SFPC.TBMATERIALPORTAL ";
                  	$sql .= "WHERE CMATEPCODI = $CodMaterial AND CSUBCLSEQU = $Subclasse ";
                  } else { //SQL para buscar o Serviço (S)
                  	$sql  = "SELECT CSERVPSEQU, ESERVPDESC FROM SFPC.TBSERVICOPORTAL ";
                  	$sql .= "WHERE CSERVPSEQU = (SELECT MAX(CSERVPSEQU) FROM SFPC.TBSERVICOPORTAL WHERE CGRUMSCODI = $Grupo AND CCLAMSCODI = $Classe) ";
                  }

                  $res  = $db->query($sql);
                  if( PEAR::isError($res) ){
                      $Rollback = 1;
                      $db->query("ROLLBACK");
                      ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                      exit;
                  }	else {
                    $Linha = $res->fetchRow();
                    $CodReduzido = $Linha[0];
                    $DescricaoMaterialServico = $Linha[1];
                  }
                  //$db->disconnect();
                  # Envia uma mensagem pelo e-mail do usuário #
                  if( $ResponsavelEmail != "" ){
					
                      EnviaEmail($ResponsavelEmail,"Pré-Cadastro de Material no Portal de Compras da Prefeitura do Recife","O cadastramento do $DescricaoTipoGrupo foi efetuado com sucesso, o código reduzido do " .strtolower2($DescricaoTipoGrupo). " solicitado é $CodReduzido ($DescricaoMaterialServico). \nPara um melhor acompanhamento acesse o Portal de Compras, no link Materiais/Serv -> Pré-Cadastro -> Acompanhamento.","from: portalcompras@recife.pe.gov.br");
                  	}
              	} else {
                  # Envia uma mensagem pelo e-mail do usuário #
                  if( $ResponsavelEmail != "" ){
					
                      EnviaEmail($ResponsavelEmail,"Pré-Cadastro de Material no Portal de Compras da Prefeitura do Recife","A sua solicitação de cadastro de $DescricaoTipoGrupo foi analisada pela equipe responsável.\nPara um melhor acompanhamento acesse o Portal de Compras, no link Materiais/Serv -> Pré-Cadastro -> Acompanhamento.","from: portalcompras@recife.pe.gov.br");
                  	}
              	}echo 'aquiiiiiiiiiii';
          }
          # Redireicona para a tela de Pesquisa #
          $Mensagem = urlencode("Análise do Pré-Cadastro de $DescricaoTipoGrupo Efetuada com Sucesso, um aviso foi enviado para o e-mail do usuário solicitante");
          $Url = "CadPreMaterialAnalisarSelecionar.php?Mens=1&Tipo=1&Mensagem=$Mensagem&Grupo=$Grupo&Classe=$Classe&Subclasse=$Subclasse&PreMaterialServico=$PreMaterialServico";

          if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
          header("location: ".$Url);
          exit;
        }
        $db->disconnect();
      } else {
        if( $Mens == 1 ){ $Mensagem .= ", "; }
        $Mens      = 1;
        $Tipo      = 2;
        $Mensagem .= "<a href=\"javascript:document.CadPreMaterialAnalisar.Situacao.focus();\" class=\"titulo2\">Pré-Material já Cadastrado nesta situação</a>";
      }
		}
}
if( $Botao == "" ){
		# Pega os dados do Pré-Material de acordo com o código #
		$db   = Conexao();

        if (empty($Grupo)) $Grupo = '999';
        if (empty($Classe)) $Classe = '999';
        if (empty($PreMaterialServico)) $PreMaterialServico = '999';


		$sql .= "SELECT GRU.FGRUMSTIPM, GRU.EGRUMSDESC, CLA.ECLAMSDESC, PRE.EPREMADESC, ";
		$sql .= "       PRE.CPREMACODI, PRE.CPREMSCODI, PRE.EPREMAOBSE, UND.EUNIDMDESC, ";
		$sql .= "       UND.CUNIDMCODI, PRESIT.CPREMSCODI, PRESIT.EPREMSDESC, USU.EUSUPORESP, USU.EUSUPOMAIL ";
		$sql .= "  FROM SFPC.TBGRUPOMATERIALSERVICO GRU, SFPC.TBCLASSEMATERIALSERVICO CLA, ";
		$sql .= "       SFPC.TBPREMATERIALSERVICOTIPOSITUACAO PRESIT, SFPC.TBUSUARIOPORTAL USU, SFPC.TBPREMATERIALSERVICO PRE ";

		$sql .= " LEFT OUTER JOIN SFPC.TBUNIDADEDEMEDIDA UND ON PRE.CUNIDMCODI = UND.CUNIDMCODI ";

		$sql .= " WHERE PRE.CGRUMSCODI = CLA.CGRUMSCODI AND PRE.CCLAMSCODI = CLA.CCLAMSCODI ";
		$sql .= "   AND CLA.CGRUMSCODI = GRU.CGRUMSCODI AND PRE.CGRUMSCODI = $Grupo ";
		$sql .= "   AND PRE.CCLAMSCODI = $Classe AND PRE.CPREMACODI = $PreMaterialServico	";
		$sql .= "   AND PRE.CPREMSCODI = PRESIT.CPREMSCODI ";
		$sql .= "   AND PRE.CUSUPOCODI = USU.CUSUPOCODI ";

		if($TipoGrupo != 'T'){
			$sql .= " AND GRU.FGRUMSTIPO = '$TipoGrupo' ";
		}



		$res  = $db->query($sql);
		if( PEAR::isError($res) ){

					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");

		}else{
				$Linha             = $res->fetchRow();
				$TipoMaterial      = $Linha[0];
				$DescGrupo         = substr($Linha[1],0,60);
				$DescClasse        = substr($Linha[2],0,150);
				$DescMaterial      = ($TipoMaterial == "" or $TipoMaterial == null) ? $Linha[3] : substr($Linha[3],0,300); // Caso o tipo do material seja Vazio ou Nulo, então é um serviço, logo, não deve truncar em 300 caracteres. Caso contrário é um Material e deve truncar para não dar erro ao inserir um novo material em SFPC.TBMATERIALPORTAL.
				$DescMaterialComp  = $Linha[3];
				$NCaracteresM      = strlen($DescMaterial);
				$NCaracteresC      = strlen($DescMaterialComp);
				$NCaracteresO      = strlen($Observacao);
				$CodigoMaterial    = $Linha[4];
				$Situacao          = $Linha[5];
				$Observacao        = $Linha[6];
				$DescUnidade       = $Linha[7];
				$Unidade           = $Linha[8];
				$SituacaoAtualCodi = $Linha[9];
				$SituacaoAtualDesc = $Linha[10];
				$ResponsavelNome   = $Linha[11];
				$ResponsavelEmail  = $Linha[12];
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
	document.CadPreMaterialAnalisar.Grupo.value  = '';
	document.CadPreMaterialAnalisar.Classe.value = '';
	document.CadPreMaterialAnalisar.submit();
}
function enviar(valor){
	document.CadPreMaterialAnalisar.Botao.value=valor;
	document.CadPreMaterialAnalisar.submit();
}
function ncaracteresM(valor){
	document.CadPreMaterialAnalisar.NCaracteresM.value = '' +  document.CadPreMaterialAnalisar.DescMaterial.value.length;
	if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
		document.CadPreMaterialAnalisar.NCaracteresM.focus();
	}
}
function ncaracteresC(valor){
	document.CadPreMaterialAnalisar.NCaracteresC.value = '' +  document.CadPreMaterialAnalisar.DescMaterialComp.value.length;
	if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
		document.CadPreMaterialAnalisar.NCaracteresC.focus();
	}
}
function ncaracteresO(valor){
	document.CadPreMaterialAnalisar.NCaracteresO.value = '' +  document.CadPreMaterialAnalisar.Observacao.value.length;
	if( navigator.appName == 'Netscape' && valor ) {  //Netscape Only
		document.CadPreMaterialAnalisar.NCaracteresO.focus();
	}
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadPreMaterialAnalisar.php" method="post" name="CadPreMaterialAnalisar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
	<!-- Caminho -->
	<tr>
		<td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
		<td align="left" class="textonormal" colspan="2">
			<font class="titulo2">|</font>
			<a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais/Serv > Pré-Cadastro > Análise
		</td>
	</tr>
	<!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
		<td width="100"></td>
		<td align="left" colspan="2">
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
												ANÁLISE - PRÉ-CADASTRO DE MATERIAIS
											</td>
										</tr>
										<tr>
											<td class="textonormal">
												<p align="justify">
													Informe uma nova situação para o pré-cadastro, uma nova descrição do material e clique no botão "Confirmar".
													Para voltar para a tela de Pesquisa, clique no botão "Voltar".
												</p>
											</td>
										</tr>
										<tr>
											<td>
												<table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
													<tr>
														<td colspan="2">
															<table class="textonormal" border="0" width="100%" summary="">
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Tipo de Grupo</td>
																	<td class="textonormal">
																		<?php
																			echo strtoupper2($DescricaoTipoGrupo);
																		?>
																	</td>
																</tr>

																<?php
																	//Variáveis dinâmicas para colocar as informações para material ou serviço.
																	if($TipoGrupo == 'M') {
																 ?>

																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Tipo de Material</td>
																	<td class="textonormal">
																		<?php if( $TipoMaterial == "C" ){ echo "CONSUMO"; }else{ echo "PERMANENTE"; } ?>
																	</td>
																</tr>
																<?php } //Fecha o if($TipoGrupo == 'M')  ?>


																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Grupo</td>
																	<td class="textonormal"><?php echo $DescGrupo; ?></td>
																</tr>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Classe</td>
																	<td class="textonormal"><?php echo $DescClasse; ?></td>
																</tr>

																<?php
																	//Variáveis dinâmicas para colocar as informações para material ou serviço.
																	if($TipoGrupo == 'M') {
																 ?>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">SubClasse*</td>
																	<td class="textonormal">
																		<select name="Subclasse" class="textonormal">
																			<option value="">Selecione uma Subclasse...</option>
																				<?php
																					if( $Grupo != "" and $Classe != "" ){
																							$db   = Conexao();
																							$sql  = "SELECT CSUBCLSEQU, ESUBCLDESC FROM SFPC.TBSUBCLASSEMATERIAL";
																							$sql .= " WHERE CGRUMSCODI = '$Grupo' and CCLAMSCODI = '$Classe' AND FSUBCLSITU = 'A' ";
																							$sql .= " ORDER BY ESUBCLDESC";
																							$res  = $db->query($sql);
																							if( PEAR::isError($res) ){
																									ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																							}else{
																									while( $Linha = $res->fetchRow() ){
																												$DescricaoSubclasse = substr($Linha[1],0,75);
																												if( $Linha[0] == $Subclasse){
																														echo"<option value=\"$Linha[0]\" selected>$DescricaoSubclasse</option>\n";
																												}else{
																														echo"<option value=\"$Linha[0]\">$DescricaoSubclasse</option>\n";
																												}
																									}
																							}
																							$db->disconnect();
																					}
																				?>
																		</select>
																	</td>
																</tr>
																<?php } //Fecha o if($TipoGrupo == 'M')  ?>
															<!--	<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Código do Pré-<?php // echo "$DescricaoTipoGrupo";?></td>
																	<td class="textonormal"><?php // echo $PreMaterialServico;?></td>
																</tr>
															-->

																<?php
																	//Variáveis dinâmicas para colocar as informações para material ou serviço.
																	if($TipoGrupo == 'M') {
																 ?>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Unidade</td>
																	<td class="textonormal"><?php echo $DescUnidade;?></td>
																</tr>
																<?php } //Fecha o if($TipoGrupo == 'M')  ?>

																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20"><?php echo "$DescricaoTipoGrupo"?>*</td>
																	<td class="textonormal">
																		<font class="textonormal">máximo de <?php echo $QtdeCaracteres?> caracteres</font>
																		<input type="text" name="NCaracteresM" disabled size="3" value="<?php echo $NCaracteresM ?>" class="textonormal"><br>
																		<textarea name="DescMaterial" maxlength="300" cols="60" rows="8" style = "text-transform: uppercase;" OnKeyUp="javascript:ncaracteresM(1)" OnBlur="javascript:ncaracteresM(0)" OnSelect="javascript:ncaracteresM(1)" style = "text-transform: uppercase;" class="textonormal"><?php echo stripslashes($DescMaterial); ?></textarea>
																	</td>
																</tr>

																<?php
																	//Variáveis dinâmicas para colocar as informações para material ou serviço.
																	if($TipoGrupo == 'M') {
																 ?>
																<tr>
										            	<td class="textonormal" bgcolor="#DCEDF7">Descrição Completa*</td>
									  	        		<td class="textonormal">
																		<font class="textonormal">máximo de 3000 caracteres</font>
																		<input type="text" name="NCaracteresC" disabled size="3" value="<?php echo $NCaracteresC ?>" class="textonormal"><br>
																		<textarea name="DescMaterialComp" cols="60" rows="10" size="3000" OnKeyUp="javascript:ncaracteresC(1)" OnBlur="javascript:ncaracteresC(0)" OnSelect="javascript:ncaracteresC(1)" class="textonormal" style = "text-transform: uppercase;" maxlength="3000"><?php echo stripslashes($DescMaterialComp);?></textarea>
																	</td>
										        		</tr>
										        				<?php } //Fecha o if($TipoGrupo == 'M')  ?>

																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7">Observação</td>
																	<td class="textonormal">
																		<font class="textonormal">máximo de 2000 caracteres</font>
																		<input type="text" name="NCaracteresO" disabled size="3" value="<?php echo $NCaracteresO ?>" class="textonormal"><br>
																		<textarea name="Observacao" cols="60" rows="5" maxlength="2000" OnKeyUp="javascript:ncaracteresO(1)" OnBlur="javascript:ncaracteresO(0)" style = "text-transform: uppercase;" OnSelect="javascript:ncaracteresO(1)" class="textonormal"><?php echo stripslashes($Observacao); ?></textarea>
																	</td>
																</tr>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Situação Atual</td>
																	<td class="textonormal"><?php echo $SituacaoAtualDesc; ?></td>
																</tr>
																<tr>
																	<td class="textonormal" bgcolor="#DCEDF7" height="20">Situação*</td>
																	<td class="textonormal">
																		<select name="Situacao" class="textonormal">
																			<?php
																			$db   = Conexao();
																			$sql  = "SELECT CPREMSCODI, EPREMSDESC ";
																			$sql .= "  FROM SFPC.TBPREMATERIALSERVICOTIPOSITUACAO ";
																			$sql .= " ORDER BY EPREMSDESC";
																			$res  = $db->query($sql);
																			if (PEAR::isError($res)) {
																					ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
																			}else{
																					while( $Linha = $res->fetchRow() ){
																							$DescSituacao = substr($Linha[1],0,60);
																							if( $Linha[0]== $Situacao ){
																									echo "<option value=\"$Linha[0]\" selected>$DescSituacao</option>\n";
																							}else{
																									echo "<option value=\"$Linha[0]\">$DescSituacao</option>\n";
																							}
																					}
																			}
																			$db->disconnect();
																			?>
																		</select>
																	</td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
											</td>
										</tr>
										<tr>
											<td colspan="2" align="right">
												<input type="hidden" name="Grupo" value="<?php echo $Grupo; ?>">
												<input type="hidden" name="DescGrupo" value="<?php echo $DescGrupo; ?>">
												<input type="hidden" name="Classe" value="<?php echo $Classe; ?>">
												<input type="hidden" name="TipoGrupo" value="<?php echo $TipoGrupo; ?>">
												<input type="hidden" name="DescClasse" value="<?php echo $DescClasse; ?>">
												<input type="hidden" name="PreMaterialServico" value="<?php echo $PreMaterialServico; ?>">
                        <input type="hidden" name="TipoMaterial" value="<?php echo $TipoMaterial; ?>">
												<input type="hidden" name="DescUnidade" value="<?php echo $DescUnidade; ?>">
                        <input type="hidden" name="Unidade" value="<?php echo $Unidade; ?>">
												<input type="hidden" name="SituacaoAtualDesc" value="<?php echo $SituacaoAtualDesc; ?>">
												<input type="hidden" name="SituacaoAtualCodi" value="<?php echo $SituacaoAtualCodi; ?>">
												<input type="hidden" name="ResponsavelNome" value="<?php echo $ResponsavelNome; ?>">
												<input type="hidden" name="ResponsavelEmail" value="<?php echo $ResponsavelEmail; ?>">
												<input type="button" value="Confirmar" class="botao" onclick="javascript:enviar('Confirmar');">
												<input type="button" value="Voltar" class="botao" onclick="javascript:enviar('Voltar');">
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
