<?php
#----------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadMaterialIncluir.php
# Autor:    Roberta Costa/Altamiro
# Data:     04/08/2005
#----------------------------------------------------------------------------
# Alterado: Álvaro Faria
# Data:     26/12/2006 - Retirada de quebra de linha da descrição do material
# Objetivo: Programa de Inclusão de Material das Classes de Fornecimento
#----------------------------------------------------------------------------
# Alterado: Rossana Lira
# Data:     06/05/2007 - Exibição do número sequencial do material na mensagem e
#                        retirada da exibição do código do material na família
#----------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     18/08/2008 - Alteração do campo "Descrição Completa" ser obrigatório.
#----------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     31/01/2008 - Alteração para que seja inserido na tabela SFPC.TBhistoricomaterial quando o material ter sido incluído.
#----------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     03/03/2008 - Remoção da integração com a tabela de histórico.
#----------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     12/03/2008 - Alteração para aumentar o campo Observação de 100 Caracteres para 150.
#----------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     26/08/2009 - Alteração para inserir o cadastro de serviços
#----------------------------------------------------------------------------
# Alterado: Ariston Cordeiro
# Data:     04/02/2011 - #1546 Red Mine- campo de descrição de serviços vai de 300 para 500 caracteres
#----------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     17/05/2011 - Tarefa do Redmine: 2694 - Campo de descrição de serviços vai de 500 para 700 caracteres
#----------------------------------------------------------------------------
# Alterado: Heraldo Botelho
# Data:     01/11/2012 - Tarefa do Redmine: 17354 - Criar o campo que indica que não deve gravar na TRP
#----------------------------------------------------------------------------
# Alterado: Pitang
# Data:     13/06/2014 - [CR123139]: REDMINE 20 (P2)
#----------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     09/07/2018
# Objetivo: Tarefa Redmine 165579
#----------------------------------------------------------------------------
# OBS.:     Tabulação 2 espaços
#----------------------------------------------------------------------------
# Alterado: Pitang Agile IT - Caio Coutinho
# Data:     18/12/2018
# Objetivo: 207930
#----------------------------------------------------------------------------

# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança   #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso('/materiais/CadMaterialIncluirSelecionar.php');

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao = $_POST['Botao'];
    $TipoMaterial = $_POST['TipoMaterial'];
    $TipoGrupo = $_POST['TipoGrupo'];
    $Grupo = $_POST['Grupo'];
    $GrupoDescricao = strtoupper2(trim($_POST['GrupoDescricao']));
    $Classe = $_POST['Classe'];
    $Subclasse = $_POST['Subclasse'];
    $Unidade = $_POST['Unidade'];
    $Material = $_POST['Material'];
    $NCaracteresM = $_POST['NCaracteresM'];
    $NCaracteresC = $_POST['NCaracteresC'];
    $NCaracteresO = $_POST['NCaracteresO'];
    $indGravarTRP = $_POST['indGravarTRP'];
    ?>
    <!--
         <script>
           alert('<?php echo "indGravarTRP=$indGravarTRP"; ?>');
         </script>
    -->

    <?php
    $DescMaterial = stripslashes(strtoupper2(str_replace("\r\n", "", str_replace("'", "", trim($_POST['DescMaterial'])))));
    $DescMaterialComp = stripslashes(strtoupper2(str_replace("\r\n", "", str_replace("'", "", trim($_POST['DescMaterialComp'])))));
    $Observacao = stripslashes(strtoupper2(str_replace("\r\n", "", str_replace("'", "", trim($_POST['Observacao'])))));

//      $DescMaterial       = mb_strtoupper($_POST['DescMaterial']);
//      $DescMaterialComp   = mb_strtoupper($_POST['DescMaterialComp']);
//      $Observacao         = mb_strtoupper($_POST['Observacao']);

    $Pesquisa = $_POST['Pesquisa'];
    $Palavra = $_POST['Palavra'];
    
    $CampoGenerico     = filter_input(INPUT_POST, 'CampoGenerico');
    $ItemSustentavel   = $_POST['ItemSustentavel'];
} else {
    $Grupo = $_GET['Grupo'];
    $Classe = $_GET['Classe'];
    $Subclasse = $_GET['Subclasse'];
    $TipoGrupo = $_GET['TipoGrupo'];

}

$noCasacteresCampoDescricaoServico = 700;
$noCasacteresCampoDescricaoMaterial = 300;
$noCasacteresCampoDescricaoCompletaMaterial = 3000;
$noCasacteresCampoObservacao = 150;

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

//Variáveis dinâmicas para colocar as informações para material ou serviço.
if ($TipoGrupo == 'M') {
    $Descricao = "Material";
} else {
    $Descricao = "Serviço";
}

if ($Botao == "Voltar") {
    header("location: CadMaterialIncluirSelecionar.php");
    exit;
} elseif ($Botao == "Incluir") {
    $Mens = 0;
    $Mensagem = "Informe: ";
    if ($Unidade == "" && $TipoGrupo == 'M') {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.CadMaterialIncluir.Unidade.focus();\" class=\"titulo2\">Unidade de Medida</a>";
    }
    if ($DescMaterial == "") {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.CadMaterialIncluir.DescMaterial.focus();\" class=\"titulo2\">$Descricao</a>";
    } else {
        if ($TipoGrupo == 'M' and strlen($DescMaterial) > $noCasacteresCampoDescricaoMaterial) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.CadMaterialIncluir.DescMaterial.focus();\" class=\"titulo2\">$Descricao no Máximo com " . $noCasacteresCampoDescricaoMaterial . " Caracteres</a>";
        } elseif ($TipoGrupo == 'S' and strlen($DescMaterial) > $noCasacteresCampoDescricaoServico) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.CadMaterialIncluir.DescMaterial.focus();\" class=\"titulo2\">$Descricao no Máximo com " . $noCasacteresCampoDescricaoServico . " Caracteres</a>";
        }
    }

    if ($DescMaterialComp == "" && $TipoGrupo == 'M') {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.CadMaterialIncluir.DescMaterialComp.focus();\" class=\"titulo2\">Descrição Completa do Material</a>";
    } else {
        if (strlen($DescMaterialComp) > $noCasacteresCampoDescricaoCompletaMaterial) {
            if ($Mens == 1) {
                $Mensagem .= ", ";
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= "<a href=\"javascript:document.CadMaterialIncluir.DescMaterialComp.focus();\" class=\"titulo2\">Descrição Completa do Material no Máximo com " . $noCasacteresCampoDescricaoCompletaMaterial . " Caracteres</a>";
        }
    }
    if (strlen($Observacao) > $noCasacteresCampoObservacao) {
        if ($Mens == 1) {
            $Mensagem .= ", ";
        }
        $Mens = 1;
        $Tipo = 2;
        $Mensagem .= "<a href=\"javascript:document.CadMaterialIncluir.Observacao.focus();\" class=\"titulo2\">Observação no Máximo com " . $noCasacteresCampoObservacao . " Caracteres</a>";
    }
    if ($Mens == 0) {

        $db = Conexao();

        if ($TipoGrupo == 'M') { //PARA MATERIAL (M)
            $sql = "SELECT COUNT(*) FROM SFPC.TBMATERIALPORTAL ";
            $sql .= " WHERE EMATEPDESC = '$DescMaterial' AND CSUBCLSEQU = $Subclasse ";
        } else { //PARA SERVIÇO (S)
            $sql = "SELECT COUNT(*) FROM SFPC.TBSERVICOPORTAL ";
            $sql .= " WHERE ESERVPDESC = '$DescMaterial' AND CCLAMSCODI = $Classe AND CGRUMSCODI = $Grupo ";
        }

        $res = $db->query($sql);
        if (PEAR::isError($res)) {
            ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
        } else {
            $Qtd = $res->fetchRow();
            if ($Qtd[0] > 0) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = "<a href=\"javascript:document.CadMaterialIncluir.DescMaterial.focus();\" class=\"titulo2\">$Descricao já Cadastrado</a>";
            } else {

                # Atribuindo NULL aos Campos não obrigatórios #
                if ($Observacao == "") {
                    $Obs = "NULL";
                } else {
                    $Obs = "'" . $Observacao . "'";
                }

                if ($TipoGrupo == 'M') { //PARA MATERIAL (M)
                    if ($DescMaterialComp == "") {
                        $DescCompleta = "NULL";
                    } else {
                        $DescCompleta = "'" . $DescMaterialComp . "'";
                    }

                    # pega o número máximo do material na família
                    $sql = "SELECT MAX(CMATEPCODI) FROM SFPC.TBMATERIALPORTAL ";
                    $res = $db->query($sql);
                    if (PEAR::isError($res)) {
                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                    } else {
                        $Linha = $res->fetchRow();
                        $Material = $Linha[0] + 1;
                    }
                }

                # Inclui na Tabela de Materiais/Serviços #
                $db->query("BEGIN TRANSACTION");

                if ($TipoGrupo == 'M') { //PARA MATERIAL (M)
                    
                    if ($indGravarTRP)
                        $naoGravarTRP = "S";
                    else
                        $naoGravarTRP = "N";
                    
                    if ($CampoGenerico) {
                        $cadumgenerico="S";
                    } else {
                        $cadumgenerico="N";
                    }

                    if ($ItemSustentavel) {
                        $itemsustentavel = "S";
                    } else {
                        $itemsustentavel = "N";
                    }

                    # Inclui na Tabela de Material Portal #
                    $sql = "INSERT INTO SFPC.TBMATERIALPORTAL( ";
                    $sql .= "CMATEPSEQU, CSUBCLSEQU, CMATEPCODI, CUNIDMCODI, ";
                    $sql .= "EMATEPDESC, EMATEPOBSE, CMATEPSITU, TMATEPULAT, ";
                    $sql .= "EMATEPCOMP, FMATEPNTRP, FMATEPGENE, FMATEPSUST, CUSUPOCODI ";
                    $sql .= ") VALUES ( ";
                    $sql .= "nextval('sfpc.tbmaterialportal_cmatepsequ_seq'), $Subclasse, $Material, $Unidade, ";
                    $sql .= "'$DescMaterial', $Obs, 'A', '" . date("Y-m-d H:i:s") . "', ";
                    $sql .= "$DescCompleta, '$naoGravarTRP', '$cadumgenerico' , '$itemsustentavel' , ".$_SESSION['_cusupocodi_'].")";
                } else {
                    # Inclui na Tabela de Serviço Portal #
                    $sql = "INSERT INTO SFPC.TBSERVICOPORTAL( ";
                    $sql .= "CSERVPSEQU, ";
                    $sql .= "CGRUMSCODI, CCLAMSCODI, ESERVPDESC, CSERVPSITU, ";
                    $sql .= "CGREMPCODI, CUSUPOCODI, TSERVPULAT, ESERVPOBSE ";
                    $sql .= ") VALUES ( ";
                    $sql .= "nextval('sfpc.tbservicoportal_cservpsequ_seq'), ";
                    $sql .= "$Grupo, $Classe, '$DescMaterial', 'A', ";
                    $sql .= " " . $_SESSION['_cgrempcodi_'] . ", " . $_SESSION['_cusupocodi_'] . ",'" . date("Y-m-d H:i:s") . "', $Obs)";
                }

                $res = $db->query($sql);
                //echo var_dump($res);
                //exit;

                if (PEAR::isError($res)) {
                    $db->query("ROLLBACK");
                    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                } else {
                    $db->query("COMMIT");
                    $db->query("END TRANSACTION");

                    if ($TipoGrupo == 'M') { //PARA MATERIAL (M)
                        # Pega o número sequencial geral do material para exibir
                        $sql = "SELECT CMATEPSEQU FROM SFPC.TBMATERIALPORTAL WHERE CMATEPCODI = $Material";
                    } else { //PARA SERVIÇO (S)
                        # Pega o número sequencial geral do serviço para exibir
                        $sql = "SELECT CSERVPSEQU, ESERVPDESC FROM SFPC.TBSERVICOPORTAL ";
                        $sql .= "WHERE CSERVPSEQU = (SELECT MAX(CSERVPSEQU) FROM SFPC.TBSERVICOPORTAL WHERE CGRUMSCODI = $Grupo AND CCLAMSCODI = $Classe) ";
                    }

                    $res = $db->query($sql);
                    if (PEAR::isError($res)) {
                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                    } else {
                        $Linha = $res->fetchRow();
                        $MaterialSeq = $Linha[0];
                    }
                    $Mens = 1;
                    $Tipo = 1;
//                    $Mensagem = "$Descricao Incluído com Sucesso. O Código Sequencial do $Descricao é $MaterialSeq";
                    $_SESSION['InclusaoMensagem'] = "$Descricao Incluído com Sucesso. O Código Sequencial do $Descricao é $MaterialSeq";
                    $DescMaterial = "";
                    $DescMaterialComp = "";
                    $Observacao = "";
                    $naoGravarTRP = false;

                    $NCaracteresM = 0;
                    $NCaracteresC = 0;
                    $NCaracteresO = 0;
                    header("location: CadMaterialIncluirSelecionar.php");
                    exit;
                }
            }
        }
        $db->disconnect();
    }

		if( strlen($Observacao) > $noCasacteresCampoObservacao ){
				if( $Mens == 1 ){ $Mensagem .= ", "; }
				$Mens      = 1;
				$Tipo      = 2;
				$Mensagem .= "<a href=\"javascript:document.CadMaterialIncluir.Observacao.focus();\" class=\"titulo2\">Observação no Máximo com ".$noCasacteresCampoObservacao." Caracteres</a>";
		}
		if( $Mens == 0 ){

				$db   = Conexao();

				if($TipoGrupo == 'M'){ //PARA MATERIAL (M)
					$sql  = "SELECT COUNT(*) FROM SFPC.TBMATERIALPORTAL ";
					$sql .= " WHERE EMATEPDESC = '$DescMaterial' AND CSUBCLSEQU = $Subclasse ";
				} else { //PARA SERVIÇO (S)
					$sql  = "SELECT COUNT(*) FROM SFPC.TBSERVICOPORTAL ";
					$sql .= " WHERE ESERVPDESC = '$DescMaterial' AND CCLAMSCODI = $Classe AND CGRUMSCODI = $Grupo ";
				}


				$res  = $db->query($sql);
				if( PEAR::isError($res) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
				}else{
						$Qtd = $res->fetchRow();
						if( $Qtd[0] > 0 ){
								$Mens     = 1;
								$Tipo     = 2;
								$Mensagem = "<a href=\"javascript:document.CadMaterialIncluir.DescMaterial.focus();\" class=\"titulo2\">$Descricao já Cadastrado</a>";
						}else{

								# Atribuindo NULL aos Campos não obrigatórios #
								if( $Observacao       == "" ){ $Obs          = "NULL"; }else{ $Obs          = "'".$Observacao."'"; }

								if($TipoGrupo == 'M'){ //PARA MATERIAL (M)
									if( $DescMaterialComp == "" ){ $DescCompleta = "NULL"; }else{ $DescCompleta = "'".$DescMaterialComp."'"; }

									# pega o número máximo do material na família
									$sql = "SELECT MAX(CMATEPCODI) FROM SFPC.TBMATERIALPORTAL ";
									$res = $db->query($sql);
									if( PEAR::isError($res) ){
											ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{
											$Linha    = $res->fetchRow();
											$Material = $Linha[0] + 1;
									}
								}



								# Inclui na Tabela de Materiais/Serviços #
								$db->query("BEGIN TRANSACTION");

								if($TipoGrupo == 'M'){ //PARA MATERIAL (M)

								  if ( $indGravarTRP )  $naoGravarTRP="S"; else $naoGravarTRP="N";
									# Inclui na Tabela de Material Portal #
									$sql  = "INSERT INTO SFPC.TBMATERIALPORTAL( ";
									$sql .= "CMATEPSEQU, CSUBCLSEQU, CMATEPCODI, CUNIDMCODI, ";
									$sql .= "EMATEPDESC, EMATEPOBSE, CMATEPSITU, TMATEPULAT, ";
									$sql .= "EMATEPCOMP, FMATEPNTRP, CUSUPOCODI ";
									$sql .= ") VALUES ( ";
									$sql .= "nextval('sfpc.tbmaterialportal_cmatepsequ_seq'), $Subclasse, $Material, $Unidade, ";
									$sql .= "'$DescMaterial', $Obs, 'A', '".date("Y-m-d H:i:s")."', ";
									$sql .= "$DescCompleta, '$naoGravarTRP' , ".$_SESSION['_cusupocodi_'].")";
								} else {
									# Inclui na Tabela de Serviço Portal #
						            $sql  = "INSERT INTO SFPC.TBSERVICOPORTAL( ";
 						            $sql .= "CSERVPSEQU, ";
						            $sql .= "CGRUMSCODI, CCLAMSCODI, ESERVPDESC, CSERVPSITU, ";
						            $sql .= "CGREMPCODI, CUSUPOCODI, TSERVPULAT, ESERVPOBSE ";
						            $sql .= ") VALUES ( ";
						            $sql .= "nextval('sfpc.tbservicoportal_cservpsequ_seq'), ";
 						            $sql .= "$Grupo, $Classe, '$DescMaterial', 'A', ";
						            $sql .= " ".$_SESSION['_cgrempcodi_'].", ".$_SESSION['_cusupocodi_'].",'".date("Y-m-d H:i:s")."', $Obs)";
								}


								$res  = $db->query($sql );
								//echo var_dump($res);
								//exit;


								if( PEAR::isError($res) ){
										$db->query("ROLLBACK");
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
								}else{
										$db->query("COMMIT");
										$db->query("END TRANSACTION");

										if($TipoGrupo == 'M'){ //PARA MATERIAL (M)
											# Pega o número sequencial geral do material para exibir
  											$sql = "SELECT CMATEPSEQU FROM SFPC.TBMATERIALPORTAL WHERE CMATEPCODI = $Material";
										} else { //PARA SERVIÇO (S)
											# Pega o número sequencial geral do serviço para exibir
											$sql  = "SELECT CSERVPSEQU, ESERVPDESC FROM SFPC.TBSERVICOPORTAL ";
                  							$sql .= "WHERE CSERVPSEQU = (SELECT MAX(CSERVPSEQU) FROM SFPC.TBSERVICOPORTAL WHERE CGRUMSCODI = $Grupo AND CCLAMSCODI = $Classe) ";
										}

										$res = $db->query($sql);
										if( PEAR::isError($res) ){
												ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
										}else{
												$Linha       = $res->fetchRow();
												$MaterialSeq = $Linha[0];
										}
										$Mens             = 1;
										$Tipo             = 1;
										$Mensagem         = "$Descricao Incluído com Sucesso. O Código Sequencial do $Descricao é $MaterialSeq";
										$DescMaterial     = "";
										$DescMaterialComp = "";
										$Observacao       = "";
										$naoGravarTRP     = false;

										$NCaracteresM     = 0;
										$NCaracteresC     = 0;
										$NCaracteresO     = 0;
								}
						}
				}
				$db->disconnect();
		}
}
if ($Botao == "") {
    $NCaracteresM = strlen($DescMaterial);
    $NCaracteresC = strlen($DescMaterialComp);
    $NCaracteresO = strlen($Observacao);
}
?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
    <script language="javascript" type="">
        <!--
        function remeter()
        {
        document.CadMaterialIncluir.Grupo.value  = '';
        document.CadMaterialIncluir.Classe.value = '';
        document.CadMaterialIncluir.submit();
        }
        function enviar(valor)
        {
        document.CadMaterialIncluir.Botao.value=valor;
        document.CadMaterialIncluir.submit();
        }
        function ncaracteresM(valor)
        {
        document.CadMaterialIncluir.NCaracteresM.value = '' +  document.CadMaterialIncluir.DescMaterial.value.length;
        if (navigator.appName == 'Netscape' && valor) {  //Netscape Only
        document.CadMaterialIncluir.NCaracteresM.focus();
        }
        }
        function ncaracteresC(valor)
        {
        document.CadMaterialIncluir.NCaracteresC.value = '' +  document.CadMaterialIncluir.DescMaterialComp.value.length;
        if (navigator.appName == 'Netscape' && valor) {  //Netscape Only
        document.CadMaterialIncluir.NCaracteresC.focus();
        }
        }
        function ncaracteresO(valor)
        {
        document.CadMaterialIncluir.NCaracteresO.value = '' +  document.CadMaterialIncluir.Observacao.value.length;
        if (navigator.appName == 'Netscape' && valor) {  //Netscape Only
        document.CadMaterialIncluir.NCaracteresO.focus();
        }
        }
        function noCaracteresTextArea(textAreaField, limit)
        {
        var ta = document.getElementById(textAreaField);

        if (ta.value.length >= limit) {
        ta.value = ta.value.substring(0, limit-1);
        }
        }

                <?php MenuAcesso(); ?>
        //-->
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css">
    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
        <script language="JavaScript" src="../menu.js"></script>
        <script language="JavaScript">Init();</script>
        <form action="CadMaterialIncluir.php" method="post" name="CadMaterialIncluir">
            <br><br><br><br><br>
            <table cellpadding="3" border="0" summary="">
                <!-- Caminho -->
                <tr>
                <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                <td align="left" class="textonormal" colspan="2">
                    <font class="titulo2">|</font>
                    <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais/Serv > Cadastro > Incluir
                </td>
                </tr>
                <!-- Fim do Caminho-->

                <!-- Erro -->
<?php if ($Mens == 1) { ?>
                    <tr>
                    <td width="100"></td>
                    <td align="left" colspan="2">
    <?php if ($Mens == 1) {
        ExibeMens($Mensagem, $Tipo, 1);
    } ?>
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
                                            INCLUIR - CADASTRO DE MATERIAIS/SERVIÇOS
                                        </td>
                                        </tr>
                                        <tr>
                                        <td class="textonormal">
                                            <p align="justify">
                                                Para incluir um novo <?php echo strtolower($Descricao); ?> informe os dados abaixo e clique no botão "Incluir". Os itens obrigatórios estão com *.
                                            </p>
                                        </td>
                                        </tr>
                                        <tr>
                                        <td>
                                            <table border="0" cellpadding="0" cellspacing="0" bordercolor="#75ADE6" width="100%" summary="">
                                                <tr>
                                                <td colspan="2">
                                                    <table class="textonormal" border="0" width="100%" summary="">

<?php
$db = Conexao();
$sql = "SELECT FGRUMSTIPM, EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO ";
$sql .= " WHERE FGRUMSTIPO = '$TipoGrupo' AND CGRUMSCODI = $Grupo";
$res = $db->query($sql);
if (PEAR::isError($res)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    $Linha = $res->fetchRow();
    $TipoMaterial = $Linha[0];
    $GrupoDescricao = $Linha[1];
}
if ($TipoGrupo == 'M') { //APENAS PARA MATERIAL  
    ?>
                                                            <tr>
                                                            <td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Tipo de Material</td>
                                                            <td class="textonormal">
                                                                <?php
                                                                if ($TipoMaterial == "C") {
                                                                    echo "CONSUMO";
                                                                } else {
                                                                    echo "PERMANENTE";
                                                                }
                                                                ?>
                                                            </td>
                                                            </tr>
                                                            <?php } // FIM  if($TipoGrupo == 'M') ?>

                                                        <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7" height="20">Grupo</td>
                                                        <td class="textonormal"><?php echo $GrupoDescricao; ?></td>
                                                        </tr>
                                                        <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7" height="20">Classe</td>
                                                        <td class="textonormal">
                                                            <?php
                                                            $sql = "SELECT ECLAMSDESC FROM SFPC.TBCLASSEMATERIALSERVICO ";
                                                            $sql .= " WHERE CGRUMSCODI = $Grupo AND CCLAMSCODI = $Classe";
                                                            $result = $db->query($sql);
                                                            if (PEAR::isError($result)) {
                                                                ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                                            } else {
                                                                $Linha = $result->fetchRow();
                                                                echo $Linha[0];
                                                            }
                                                            ?>
                                                        </td>
                                                        </tr>

                                                                <?php if ($TipoGrupo == 'M') { //APENAS PARA MATERIAL ?>
                                                            <tr>
                                                            <td class="textonormal" bgcolor="#DCEDF7" height="20">Subclasse</td>
                                                            <td class="textonormal">
                                                                    <?php
                                                                    $sql = "SELECT ESUBCLDESC FROM SFPC.TBSUBCLASSEMATERIAL ";
                                                                    $sql .= " WHERE CSUBCLSEQU = $Subclasse ";
                                                                    $res = $db->query($sql);
                                                                    if (PEAR::isError($res)) {
                                                                        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
                                                                    } else {
                                                                        $Linha = $res->fetchRow();
                                                                        echo $Linha[0];
                                                                    }
                                                                    $db->disconnect();
                                                                    ?>
                                                            </td>
                                                            </tr>

                                                            <tr>
                                                            <td class="textonormal" bgcolor="#DCEDF7">Unidade de Medida*</td>
                                                            <td class="textonormal">
                                                                <select name="Unidade" class="textonormal">
                                                                    <option value="">Selecione uma Unidade de Medida...</option>
    <?php
    $db = Conexao();
    $sql = "SELECT CUNIDMCODI, EUNIDMDESC ";
    $sql .= "  FROM SFPC.TBUNIDADEDEMEDIDA ";
    $sql .= " ORDER BY EUNIDMDESC";
    $result = $db->query($sql);
    if (PEAR::isError($result)) {
        ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
    } else {
        while ($Linha = $result->fetchRow()) {
            $DescUnidade = substr($Linha[1], 0, 60);
            if ($Linha[0] == $Unidade) {
                echo "<option value=\"$Linha[0]\" selected>$DescUnidade</option>\n";
            } else {
                echo "<option value=\"$Linha[0]\">$DescUnidade</option>\n";
            }
        }
    }
    $db->disconnect();
    ?>
                                                                </select>
                                                            </td>
                                                            </tr>
<?php } // FIM  if($TipoGrupo == 'M')  ?>

                                                        <?php if ($TipoGrupo == 'M') { //APENAS PARA MATERIAL ?>
                                                            <tr>
                                                            <td class="textonormal" bgcolor="#DCEDF7"><?php echo $Descricao ?>*</td>
                                                            <td class="textonormal">
                                                                <font class="textonormal">máximo de <?php echo $noCasacteresCampoDescricaoMaterial ?> caracteres</font>
                                                                <input type="text" name="NCaracteresM" size="3" value="<?php echo $NCaracteresM ?>" OnFocus="javascript:document.CadMaterialIncluir.DescMaterial.focus();" class="textonormal"><br>
                                                                <textarea id="DescMaterial" name="DescMaterial" cols="60" rows="5" OnKeyUp="javascript:ncaracteresM(1);
                                                                        noCaracteresTextArea('DescMaterial', <?php echo $noCasacteresCampoDescricaoMaterial ?>);" onKeyDown="ncaracteresM(1);
                                                                                noCaracteresTextArea('DescMaterial', <?php echo $noCasacteresCampoDescricaoMaterial ?>);" OnBlur="javascript:ncaracteresM(0)" OnSelect="javascript:ncaracteresM(1)" class="textonormal"><?php echo "$DescMaterial"; ?></textarea>
                                                            </td>
                                                            </tr>
                                                            <tr>
                                                            <td class="textonormal" bgcolor="#DCEDF7">Descrição Completa*</td>
                                                            <td class="textonormal">
                                                                <font class="textonormal">máximo de <?php echo $noCasacteresCampoDescricaoCompletaMaterial ?> caracteres</font>
                                                                <input type="text" name="NCaracteresC" size="3" value="<?php echo $NCaracteresC ?>" OnFocus="javascript:document.CadMaterialIncluir.DescMaterialComp.focus();" class="textonormal"><br>
                                                                <textarea id="DescMaterialComp" name="DescMaterialComp" cols="60" rows="8" OnKeyUp="javascript:ncaracteresC(1);
                                                                        noCaracteresTextArea('DescMaterialComp', <?php echo $noCasacteresCampoDescricaoCompletaMaterial ?>);" onKeyDown="ncaracteresC(1);
                                                                                noCaracteresTextArea('DescMaterialComp', <?php echo $noCasacteresCampoDescricaoCompletaMaterial ?>);" OnBlur="javascript:ncaracteresC(0)" OnSelect="javascript:ncaracteresC(1)" class="textonormal"><?php echo "$DescMaterialComp"; ?></textarea>
                                                            </td>
                                                            </tr>

<?php } else { ?>
                                                            <tr>
                                                            <td class="textonormal" bgcolor="#DCEDF7"><?php echo $Descricao ?>*</td>
                                                            <td class="textonormal">
                                                                <font class="textonormal">máximo de <?php echo $noCasacteresCampoDescricaoServico ?> caracteres</font>
                                                                <input type="text" name="NCaracteresM" size="3" value="<?php echo $NCaracteresM ?>" OnFocus="javascript:document.CadMaterialIncluir.DescMaterial.focus();" class="textonormal"><br>
                                                                <textarea id="DescMaterial" name="DescMaterial" cols="60" rows="5" OnKeyUp="javascript:ncaracteresM(1);
                                                                        noCaracteresTextArea('DescMaterial', <?php echo $noCasacteresCampoDescricaoServico ?>);" onKeyDown="ncaracteresM(1);
                                                                                noCaracteresTextArea('DescMaterial', <?php echo $noCasacteresCampoDescricaoServico ?>);" OnBlur="javascript:ncaracteresM(0)" OnSelect="javascript:ncaracteresM(1)" class="textonormal"><?php echo "$DescMaterial"; ?></textarea>
                                                            </td>
                                                            </tr>
<?php } // FIM  if($TipoGrupo == 'M')  ?>

                                                        <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7">Observação</td>
                                                        <td class="textonormal">
                                                            <font class="textonormal">máximo de <?php echo $noCasacteresCampoObservacao ?> caracteres</font>
                                                            <input type="text" name="NCaracteresO" size="3" value="<?php echo $NCaracteresO ?>" OnFocus="javascript:document.CadMaterialIncluir.Observacao.focus();" class="textonormal"><br>
                                                            <textarea id="Observacao" name="Observacao" cols="39" rows="3" OnKeyUp="javascript:ncaracteresO(1);
                                                                    noCaracteresTextArea('Observacao', <?php echo $noCasacteresCampoObservacao ?>);" onKeyDown="ncaracteresO(1);
                                                                            noCaracteresTextArea('Observacao', <?php echo $noCasacteresCampoObservacao ?>);" OnBlur="javascript:ncaracteresO(0)" OnSelect="javascript:ncaracteresO(1)" class="textonormal"><?php echo "$Observacao"; ?></textarea>
                                                        </td>
                                                        </tr>

                                                        <tr>
                                                        <td class="textonormal" bgcolor="#DCEDF7">Não Gravar na TRP</td>
                                                        <td class="textonormal">
<?php if ($indGravarTRP) { ?>
                                                                <input type="checkbox" name="indGravarTRP"  checked >
<?php } else { ?>
                                                                <input type=checkbox  name=indGravarTRP>
<?php } ?>
                                                        </td>
                                                        </tr>

                                <!-- Campo Generico -->
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Genérico</td>
                                    <td class="textonormal">

                                        <?php  if ( $cadumgenerico=="S"  ) { ?>
                                            <input type="checkbox" name="CampoGenerico"  checked >
                                        <?php  } else {  ?>
                                            <input type=checkbox  name="CampoGenerico" >
                                        <?php  } ?>
                                    </td>
                                </tr>
                                <!-- Campo Generico -->
                                <!-- Campo Item Sustentável -->
                                <tr>
                                    <td class="textonormal" bgcolor="#DCEDF7">Item sustentável</td>
                                    <td class="textonormal">

                                        <?php  if ( $itemsustentavel =="S"  ) { ?>
                                            <input type="checkbox" name="ItemSustentavel"  checked >
                                        <?php  } else {  ?>
                                            <input type=checkbox  name="ItemSustentavel" >
                                        <?php  } ?>
                                    </td>
                                </tr>
                                <!-- Campo Item Sustentável -->

                                                    </table>
                                                </td>
                                                </tr>
                                            </table>
                                        </td>
                                        </tr>
                                        <tr>
                                        <td colspan="2" align="right">
                                            <input type="hidden" name="Grupo" value="<?php echo $Grupo; ?>">
                                            <input type="hidden" name="Classe" value="<?php echo $Classe; ?>">
                                            <input type="hidden" name="Subclasse" value="<?php echo $Subclasse; ?>">
                                            <input type="hidden" name="TipoGrupo" value="<?php echo $TipoGrupo; ?>">
                                            <input type="button" value="Incluir" class="botao" onclick="javascript:enviar('Incluir');">
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
