<?php
#--------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: TabGrupoSubelementoIntegrar.php
# Autor:    Rodrigo Melo
# Data:     14/11/2007
# Objetivo: Programa que integra a tabela de Grupo com a tabela de Sub-elemento de despesa
#-------------------------------------
# Alterado: Rodrigo Melo
# Data:     10/03/2008 - Alteração para permitir que um grupo seja associado a mais de um subelemento de despesa e um subelemento de despesa
#                                 esteja associado a um único grupo. Não permitir a exclusão das associações, mas apenas a sua inativação.
# Alterado: Rodrigo Melo
# Data:     13/03/2008 - Correção para retirar os grupos duplicdos.
# Alterado: Rodrigo Melo
# Data:     19/03/2008 - Correção para atualizar a data da última atualização (tgruseulat), a informação da geração de laçamento custo/contabil quando a situação de um subelemento de despesa for alterado.
# Alterado: Rodrigo Melo
# Data:     13/05/2008 - Alteração para incluir a natureza da integração do material. Necessário para realizar o lançamento de custo no SOFIN.
# Alterado: Ariston Cordeiro
# Data:     23/02/2011 - Correção da tabela no IE
# Alterado: Luiz Alves	
# Data: 		08/11/2011
# Alterado: João Batista Brito	
# Data: 		28/03/2012 - Correção #5260
# Alterado: Ariston Cordeiro
# Data:    	03/09/2012 - Permitindo associação de mais de um subelemento de despesa por grupo
#---------------------------------------
# OBS.:     Tabulação 2 espaços
#--------------------------------------------------------------------------------------
# Acesso ao arquivo de funções #
include "../funcoes.php";
# Executa o controle de segurança #
session_start();
Seguranca();
# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Botao                 = $_POST['Botao'];
		$CentroCusto           = $_POST['CentroCusto']; //RETIRAR APOS FAZER A PARTE DE EXCLUSAO.
		$CheckUnidade          = $_POST['CheckUnidade'];
    	$NaturezaGrupo         = $_POST['NaturezaGrupo'];
		$ObrigatoriedadeGrupo  = $_POST['ObrigatoriedadeGrupo'];
		$CheckObrigatoriedade  = $_POST['CheckObrigatoriedade'];
		
    
		$TipoGrupo             = $_POST['TipoGrupo'];
    $GrupoCodigo           = $_POST['GrupoCodigo'];
    $TipoMaterial          = $_POST['TipoMaterial'];
    $SubElementoDespesa    = $_POST['SubElementoDespesa'];
    $Programa			         = $_POST['Programa'];
    $AnoExercicio          = $_POST['AnoExercicio'];
    $DescricaoSubElemento  = $_POST['DescricaoSubElemento'];
}else{
		$Mensagem    = urldecode($_GET['Mensagem']);
		$Mens        = $_GET['Mens'];
		$Tipo        = $_GET['Tipo'];
    $Programa		 = $_GET['Programa'];
}
			




# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;
/*
    $AnoExercicioAtual, é utilizado para setar o ano ($AnoExercicio) para inserir
    no atributo AGRUSEANOI da tabela SFPC.TBGRUPOSUBELEMENTODESPESA
    referente ao Ano de Integração do grupo ao sub-elemento de despesa, este ano
    pode ser o ano atual ou um ano posterior.
*/
$AnoExercicioAtual = date("Y");
if( $Botao == "Limpar" ){
	  if( $Programa == "A" ){
	  		$Url = "TabGrupoSubelementoIntegrar.php?Programa=A";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
			  header("location: ".$Url);
			  exit();
		}else{
	  		header("location: TabGrupoSubelementoIntegrar.php");
	  		exit();
	  }
	  exit;
}elseif( $Botao == "Integrar" ){
	  $Mens     = 0;
    $Mensagem = "Informe: ";

    #Critica dos Campos#
    if( $TipoGrupo == "" ){
        if ($Mens == 1){$Mensagem.=", ";}
    		$Mens      = 1;
    		$Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.getElementById('TipoGrupoMaterial').focus();\" class=\"titulo2\">Tipo de Grupo</a>";
    }

    if( $GrupoCodigo == "" ){
        if ($Mens == 1){$Mensagem.=", ";}
    		$Mens      = 1;
    		$Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.TabGrupoSubelementoIntegrar.GrupoCodigo.focus();\" class=\"titulo2\">Grupo</a>";
    }

    if( $TipoGrupo == "M" && $TipoMaterial == "" ){
        if ($Mens == 1){$Mensagem.=", ";}
    		$Mens      = 1;
    		$Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.getElementById('TipoMaterialConsumo').focus();\" class=\"titulo2\">Tipo de Material</a>";
    }

    if( $SubElementoDespesa == "" ){
        if ($Mens == 1){$Mensagem.=", ";}
    		$Mens      = 1;
    		$Tipo      = 2;
		    $Mensagem .= "<a href=\"javascript:document.TabGrupoSubelementoIntegrar.SubElementoDespesa.focus();\" class=\"titulo2\">Sub-elemento de Despesa</a>";
    }

    # Obtendo dados para a pesquisa e inserção dos dados.
    $Dados   = explode("{}",$SubElementoDespesa);
    $CodigoElementoDespesa   = $Dados[0];
    $DescricaoSubElemento    = strtoupper2(trim($Dados[1]));
    $Codigos    = explode(".",$CodigoElementoDespesa);
    $CGRUSEELE1 = $Codigos[0];
    $CGRUSEELE2 = $Codigos[1];
    $CGRUSEELE3 = $Codigos[2];
    $CGRUSEELE4 = $Codigos[3];
    $CGRUSESUBE = $Codigos[4];

		if( $Mens == 0 ){
      # Insere uma integração entre o grupo e o subgrupo na tabela SFPC.TBGRUPOSUBELEMENTODESPESA - Tabela de Sub-elemento de  despesa #
      $OcorreuErro = false;
      $db = Conexao();
      $db->query("BEGIN TRANSACTION");

      $sql    = "SELECT COUNT(*) FROM SFPC.TBGRUPOSUBELEMENTODESPESA SUB ";
      $sql   .= " WHERE CGRUMSCODI = $GrupoCodigo ";
      $sql   .= " AND AGRUSEANOI = $AnoExercicio ";
      $sql   .= " AND FGRUSESITU = 'A' "; //Situação: A - Ativa.
      $sql   .= " AND FGRUSENATU = 'S' "; //Natureza: S - Sim.
      $result = $db->query($sql);

      if( PEAR::isError($result) ){
        $db->query("ROLLBACK");
        EmailErroDB("Erro de banco","Erro de banco",$result);
      } else {
        $Linha = $result->fetchRow();
        $QtdeNaturezaGrupo = $Linha[0];

        if($QtdeNaturezaGrupo == 0){ //Não possuia Nenhuma natureza.
          $Natureza = "S";
        } else {
          $Natureza = "N";
        }

        $sql  = "SELECT (SUB.CGRUSEELE1 ||'.'|| ";
        $sql  .= "       SUB.CGRUSEELE2 ||'.'|| ";
        $sql  .= "       SUB.CGRUSEELE3 ||'.'|| ";
        $sql  .= "       SUB.CGRUSEELE4 ||'.'|| ";
        $sql  .= "       SUB.CGRUSESUBE) AS ELEMENTO_DESPESA ";
        $sql  .= " FROM SFPC.TBGRUPOSUBELEMENTODESPESA SUB ";
        $sql  .= " WHERE CGRUMSCODI = $GrupoCodigo ";
        $sql  .= " AND CGRUSEELE1 = $CGRUSEELE1 ";
        $sql  .= " AND CGRUSEELE2 = $CGRUSEELE2 ";
        $sql  .= " AND CGRUSEELE3 = $CGRUSEELE3 ";
        $sql  .= " AND CGRUSEELE4 = $CGRUSEELE4 ";
        $sql  .= " AND CGRUSESUBE = $CGRUSESUBE ";
        $sql  .= " AND AGRUSEANOI = $AnoExercicio ";
        $sql  .= " ORDER BY ELEMENTO_DESPESA ";

        $result = $db->query($sql);
        if( PEAR::isError($result) ){
            $db->query("ROLLBACK");
            EmailErroDB("Erro de banco","Erro de banco",$result);
        }else{
          if($result->numRows() > 0){ //Já existe está integração, mas está inativa
            //Ativando a integração existente
            $sql    = "UPDATE SFPC.TBGRUPOSUBELEMENTODESPESA SUB ";
            $sql   .= " SET FGRUSESITU = 'A', TGRUSEULAT = '".date("Y-m-d H:i:s")."',  ";
            $sql   .= " NGRUSENOMS = '$DescricaoSubElemento', FGRUSENATU = '$Natureza' ";
            $sql   .= " WHERE CGRUMSCODI = $GrupoCodigo ";
            $sql   .= " AND CGRUSEELE1 = $CGRUSEELE1 ";
            $sql   .= " AND CGRUSEELE2 = $CGRUSEELE2 ";
            $sql   .= " AND CGRUSEELE3 = $CGRUSEELE3 ";
            $sql   .= " AND CGRUSEELE4 = $CGRUSEELE4 ";
            $sql   .= " AND CGRUSESUBE = $CGRUSESUBE ";
            $sql   .= " AND AGRUSEANOI = $AnoExercicio ";
            $result = $db->query($sql);
            if( PEAR::isError($result) ){
              $db->query("ROLLBACK");
              EmailErroDB("Erro de banco","Erro de banco",$result);
              $OcorreuErro = true;
            }
          } else { //Esta integração não existe integração,
            //Inserindo integração
            $usuario = $_SESSION['_cusupocodi_'];
            $sql    = "INSERT INTO SFPC.TBGRUPOSUBELEMENTODESPESA ( ";
            $sql   .= " AGRUSEANOI, CGRUMSCODI, CGRUSEELE1, CGRUSEELE2, CGRUSEELE3, CGRUSEELE4, CGRUSESUBE,";
            $sql   .= " NGRUSENOMS, TGRUSEULAT, FGRUSESITU, FGRUSENATU, CUSUPOCODI ";
            $sql   .= " )  VALUES (";
            $sql   .= " $AnoExercicio, $GrupoCodigo, $CGRUSEELE1, $CGRUSEELE2, $CGRUSEELE3, $CGRUSEELE4, $CGRUSESUBE,";
            $sql   .= " '$DescricaoSubElemento', '".date("Y-m-d H:i:s")."', 'A', '$Natureza', '$usuario' ";
            $sql   .= " )";
            $result = $db->query($sql);
            if( PEAR::isError($result) ){
                $db->query("ROLLBACK");
                EmailErroDB("Erro de banco","Erro de banco",$result);
                $OcorreuErro = true;
            }
          }
        }

      }

      if(!$OcorreuErro){
          $db->query("COMMIT");
          $OrgaoLicitante = "";
          $Centrocusto    = "";
          $Mens           = 1;
          $Tipo           = 1;
          $Mensagem       = "Integração Realizada com Sucesso";
      }
      $db->query("END TRANSACTION");
      $db->disconnect();

      #Resetando os Valores para a próxima integração.
      $SubElementoDespesa = null;
      $TipoGrupo = null;
		}

    $Botao = "";

}elseif( $Botao == "Retirar" ){
		if( count($CheckUnidade) != 0 ){
				$db = Conexao();
        $OcorreuErro = false;
				for( $i=0; $i< count($CheckUnidade); $i++ ){
          if( $CheckUnidade[$i] != "" ){
            $Dados   = explode("_",$CheckUnidade[$i]);
            $AnoExercicio  = $Dados[0];
            $CodigoGrupo   = $Dados[1];
            $CodigoSubElemento = $Dados[2];

            $Codigos    = explode(".",$CodigoSubElemento);
            $CGRUSEELE1 = $Codigos[0];
            $CGRUSEELE2 = $Codigos[1];
            $CGRUSEELE3 = $Codigos[2];
            $CGRUSEELE4 = $Codigos[3];
            $CGRUSESUBE = $Codigos[4];

            #Remove a integração entre o grupo e o sub-elemento no ano Integrado.
            $db->query("BEGIN TRANSACTION");

            $sql    = " SELECT COUNT(*) FROM SFPC.TBGRUPOSUBELEMENTODESPESA SUB ";
            $sql   .= " WHERE CGRUMSCODI = $CodigoGrupo ";
            $sql   .= " AND AGRUSEANOI = $AnoExercicio ";
            $sql   .= " AND FGRUSESITU = 'A' "; //Situação: A - Ativa.
            $result = $db->query($sql);

            if( PEAR::isError($result) ){
              $db->query("ROLLBACK");
              EmailErroDB("Erro de banco","Erro de banco",$result);
              $OcorreuErro = true;
            } else {
              $Linha = $result->fetchRow();
              $QtdeGrupos = $Linha[0];
              
              
              //Veriificar se o grupo cuja integração será desfeita não possui a natureza como N - Não.
              $sqlNatureza    = " SELECT SUB.FGRUSENATU FROM SFPC.TBGRUPOSUBELEMENTODESPESA SUB ";
              $sqlNatureza   .= " WHERE SUB.CGRUMSCODI = $CodigoGrupo ";
              $sqlNatureza   .= " AND SUB.CGRUSEELE1 = $CGRUSEELE1 ";
              $sqlNatureza   .= " AND SUB.CGRUSEELE2 = $CGRUSEELE2 ";
              $sqlNatureza   .= " AND SUB.CGRUSEELE3 = $CGRUSEELE3 ";
              $sqlNatureza   .= " AND SUB.CGRUSEELE4 = $CGRUSEELE4 ";
              $sqlNatureza   .= " AND SUB.CGRUSESUBE = $CGRUSESUBE ";
              $sqlNatureza   .= " AND SUB.AGRUSEANOI = $AnoExercicio ";
              $resultNatureza = $db->query($sqlNatureza);

              if( PEAR::isError($resultNatureza) ){
                $db->query("ROLLBACK");
                EmailErroDB("Erro de banco","Erro de banco",$result);
                $OcorreuErro = true;
              } else {
                $LinhaNatureza = $resultNatureza->fetchRow();
                $Natureza = $LinhaNatureza[0];

               // (heraldo);
               // 
                
                if( ($Natureza == 'S' && $QtdeGrupos == 1) || ($Natureza == 'N') ) {
                  $sql    = "UPDATE SFPC.TBGRUPOSUBELEMENTODESPESA ";
                  $sql   .= " SET FGRUSESITU = 'I', TGRUSEULAT = '".date("Y-m-d H:i:s")."', FGRUSENATU = 'N' ";
                  $sql   .= " WHERE CGRUMSCODI = $CodigoGrupo ";
                  $sql   .= " AND CGRUSEELE1 = $CGRUSEELE1 ";
                  $sql   .= " AND CGRUSEELE2 = $CGRUSEELE2 ";
                  $sql   .= " AND CGRUSEELE3 = $CGRUSEELE3 ";
                  $sql   .= " AND CGRUSEELE4 = $CGRUSEELE4 ";
                  $sql   .= " AND CGRUSESUBE = $CGRUSESUBE ";
                  $sql   .= " AND AGRUSEANOI = $AnoExercicio ";
                  $result = $db->query($sql);

                  if( PEAR::isError($result) ){
                    $db->query("ROLLBACK");
                    EmailErroDB("Erro de banco","Erro de banco",$result);
                    $OcorreuErro = true;
                  }
                } else {
                  //Uma integração não pode ser desfeito caso a sua natureza seja S - Sim.
                  $Mens      = 1;
                  $Tipo      = 2;
                  $Mensagem = "Favor alterar a Natureza do grupo";
                  $OcorreuErro = true;
                }
              }
            }
          }
				}

				if(!$OcorreuErro){
          $db->query("COMMIT");
  				$Mens     = 1;
          $Tipo     = 1;
          $Mensagem = "Retirada da Integração Efetuada com Sucesso";
        }

        $db->query("END TRANSACTION");
				$db->disconnect();

		} else {
      $Mens     = 1;
      $Tipo     = 2;
      $Mensagem = "Favor marcar uma ou mais integrações para efetuar a retirada";
    }

		$Botao = "";

} elseif( $Botao == "AlterarNatureza" ){

  $Dados   = explode("_",$NaturezaGrupo);
  $AnoExercicio  = $Dados[0];
  $CodigoGrupo   = $Dados[1];
  $CodigoSubElemento = $Dados[2];

  $Codigos    = explode(".",$CodigoSubElemento);
  $CGRUSEELE1 = $Codigos[0];
  $CGRUSEELE2 = $Codigos[1];
  $CGRUSEELE3 = $Codigos[2];
  $CGRUSEELE4 = $Codigos[3];
  $CGRUSESUBE = $Codigos[4];
  #Remove a integração entre o grupo e o sub-elemento no ano Integrado.
  $db = Conexao();
  $db->query("BEGIN TRANSACTION");
  $sql    = "UPDATE SFPC.TBGRUPOSUBELEMENTODESPESA SUB ";
  $sql   .= " SET FGRUSENATU = 'N', TGRUSEULAT = '".date("Y-m-d H:i:s")."'";
  $sql   .= " WHERE CGRUMSCODI = $CodigoGrupo ";
  $sql   .= " AND AGRUSEANOI = $AnoExercicio ";
  $result = $db->query($sql);

  if( PEAR::isError($result) ){
    $db->query("ROLLBACK");
    $db->query("END TRANSACTION");
    $db->disconnect();
    EmailErroDB("Erro de banco","Erro de banco",$result);
  } else {
    $sql    = "UPDATE SFPC.TBGRUPOSUBELEMENTODESPESA SUB ";
    $sql   .= " SET FGRUSENATU = 'S', TGRUSEULAT = '".date("Y-m-d H:i:s")."'";
    $sql   .= " WHERE CGRUMSCODI = $CodigoGrupo ";
    $sql   .= " AND CGRUSEELE1 = $CGRUSEELE1 ";
    $sql   .= " AND CGRUSEELE2 = $CGRUSEELE2 ";
    $sql   .= " AND CGRUSEELE3 = $CGRUSEELE3 ";
    $sql   .= " AND CGRUSEELE4 = $CGRUSEELE4 ";
    $sql   .= " AND CGRUSESUBE = $CGRUSESUBE ";
    $sql   .= " AND AGRUSEANOI = $AnoExercicio ";
    $result = $db->query($sql);

    if( PEAR::isError($result) ){
      $db->query("ROLLBACK");
      $db->query("END TRANSACTION");
      $db->disconnect();
      EmailErroDB("Erro de banco","Erro de banco",$result);
    }  else {
      $sql    = "SELECT GRU.EGRUMSDESC ";
      $sql   .= " FROM SFPC.TBGRUPOMATERIALSERVICO GRU ";
      $sql   .= " WHERE GRU.CGRUMSCODI = $CodigoGrupo AND GRU.FGRUMSSITU = 'A' ";
      $result = $db->query($sql);
      
      
      if( PEAR::isError($result) ){
          EmailErroDB("Erro de banco","Erro de banco",$result);
      }else{
        $Linha = $result->fetchRow();
        $NomeGrupo = $Linha[0];

        $Mens     = 1;
        $Tipo     = 1;
        $Mensagem = "Alteração da natureza do grupo $NomeGrupo efetuada com sucesso";

        $db->query("COMMIT");
      }
    }
  }
  $db->query("END TRANSACTION");
  $db->disconnect();
  #Resetando os Valores para a próxima integração.
  $SubElementoDespesa = null;
  $TipoGrupo = null;
  $Botao = "";
}

#Alteração da Obrigatoriedade 
elseif( $Botao == "AlterarObrigatoriedade" ){
	

  $Dados   = explode("_",$ObrigatoriedadeGrupo);
  $AnoExercicio  		= $Dados[0];
  $CodigoGrupo   		= $Dados[1];
  $CodigoSubElemento 	= $Dados[2];
  $PosicaoLinha			= $Dados[3];
    
  if (is_null($CheckObrigatoriedade [$PosicaoLinha])){
 	$Obrigatoriedade = 'N';
  }else{
 	$Obrigatoriedade = 'S';
  } 
 	  
  $Codigos    = explode(".",$CodigoSubElemento);
  $CGRUSEELE1 = $Codigos[0];
  $CGRUSEELE2 = $Codigos[1];
  $CGRUSEELE3 = $Codigos[2];
  $CGRUSEELE4 = $Codigos[3];
  $CGRUSESUBE = $Codigos[4];
  
  
  #Remove a integração entre o grupo e o sub-elemento no ano Integrado.

  $db = Conexao();
  $db->query("BEGIN TRANSACTION");
  $sql    = "UPDATE SFPC.TBGRUPOSUBELEMENTODESPESA SUB 
  			 SET FGRUSECONT = '$Obrigatoriedade', TGRUSEULAT = '".date("Y-m-d H:i:s")."'
  			 WHERE CGRUMSCODI = $CodigoGrupo 
  			 AND CGRUSEELE1 = $CGRUSEELE1 
  			 AND CGRUSEELE2 = $CGRUSEELE2
  	         AND CGRUSEELE3 = $CGRUSEELE3 
 			 AND CGRUSEELE4 = $CGRUSEELE4 
 			 AND CGRUSESUBE = $CGRUSESUBE  
  			 AND AGRUSEANOI = $AnoExercicio ";
  
  $result = $db->query($sql);
  if( PEAR::isError($result) ){
    $db->query("ROLLBACK");
    $db->query("END TRANSACTION");
    $db->disconnect();
    EmailErroDB("Erro de banco","Erro de banco",$result);
  }   else {
      $sql    = "SELECT GRU.EGRUMSDESC 
      			 FROM SFPC.TBGRUPOMATERIALSERVICO GRU 
      			 WHERE GRU.CGRUMSCODI = $CodigoGrupo AND GRU.FGRUMSSITU = 'A' ";
      $result = $db->query($sql);
     
      if( PEAR::isError($result) ){
          EmailErroDB("Erro de banco","Erro de banco",$result);
      }else{
        $Linha = $result->fetchRow();
        $NomeGrupo = $Linha[0];

        $Mens     = 1;
        $Tipo     = 1;
        $Mensagem = "Alteração da obrigatoriedade do grupo $NomeGrupo efetuada com sucesso";

        $db->query("COMMIT");
      }
    
  }
  $db->query("END TRANSACTION");
  $db->disconnect();
  #Resetando os Valores para a próxima integração.
  $SubElementoDespesa = null;
  $TipoGrupo = null;
  $Botao = "";

}

  #Fim da alteração da Obrigatoriedade

if( $Botao == "" ){

    if($AnoExercicio == null) {
      $AnoExercicio = $AnoExercicioAtual + 1;
    }

    # Verifica se existe alguma integração no respectivo ano de exercicio #
		$db     = Conexao();
    $sql    = "SELECT COUNT(*) ";
    $sql   .= " FROM SFPC.TBGRUPOSUBELEMENTODESPESA SUB ";
    $sql   .= " INNER JOIN SFPC.TBGRUPOMATERIALSERVICO GRU ";
    $sql   .= " ON SUB.CGRUMSCODI = GRU.CGRUMSCODI ";
    $sql   .= " WHERE AGRUSEANOI = $AnoExercicio ";
		$result = $db->query($sql);
		
		
		if (PEAR::isError($result)) {
		    EmailErroDB("Erro de banco","Erro de banco",$result);
		}else{
				$Linha  = $result->fetchRow();
				$ExisteIntegracao = $Linha[0]; # Utilizado para verificar se existe alguma integração no ano de exercício.
		}
		// # Verifica se o ano corrente está Cadastrado #
		// $sql    = "SELECT COUNT(*) FROM SFPC.TBCENTROCUSTOPORTAL";
		// $sql   .= "  WHERE ACENPOANOE = $AnoExercicio";
		// $result = $db->query($sql);
		// if (PEAR::isError($result)) {
		    //  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		// }else{
				// $Linha     = $result->fetchRow();
				// $ExisteAno = $Linha[0];
		// }

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
function enviar(valor){
	document.TabGrupoSubelementoIntegrar.Botao.value=valor;
	document.TabGrupoSubelementoIntegrar.submit();
}
function setarDescricaoCorrente(){
  var hiddenDescricao = document.TabGrupoSubelementoIntegrar.DescricaoSubElemento;
  var subElementos = document.TabGrupoSubelementoIntegrar.SubElementoDespesa;

  //hiddenDescricao.value = subElementos.options[subElementos.selectedIndex].text;
}
function AlterarNatureza(valor){
 document.TabGrupoSubelementoIntegrar.NaturezaGrupo.value = valor;
 enviar('AlterarNatureza');
}
function AlterarObrigatoriedade(valor){  
 document.TabGrupoSubelementoIntegrar.ObrigatoriedadeGrupo.value = valor;
 enviar('AlterarObrigatoriedade');
}

	

<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="TabGrupoSubelementoIntegrar.php" method="post" name="TabGrupoSubelementoIntegrar">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal" colspan="2">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Materiais > Grupo > Integrar
    </td>
  </tr>
  <!-- Fim do Caminho-->
	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
  <tr>
  	<td width="150"></td>
		<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->
	<!-- Corpo -->
	<tr>
		<td width="100"></td>
		<td class="textonormal">
      <table  border="0" cellspacing="0" cellpadding="3" bgcolor="#ffffff">
        <tr>
	      	<td class="textonormal">
	        	<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" class="textonormal" summary="">
	          	<tr>
	            	<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
		    					INTEGRAÇÃO DA TABELA DE GRUPO COM SUB-ELEMENTO DE DESPESA
		          	</td>
		        	</tr> 
		        	<tr>
	    	      	<td class="textonormal">
	      	    		<p align="justify">
	        	    		Para fazer a integração da tabela de Grupo com a tabela de Sub-elemento de despesa, escolha os campos abaixo e clique no botão "Integrar".<br>
	        	    		Quando houver alguma integração feita, será exibida uma lista, para retirar um ou mais itens dessa lista marque o(s) item(s) desejados e clique no botão "Retirar".<br>
	        	    		"ATENÇÃO: Apenas grupos ativos podem ser integrados e serão listados aqui. Caso seja ativado algum grupo posteriormente, lembrar de integrar este a um sub-elemento de despesa."
	          	   	</p>
	          		</td>
		        	</tr>
		        	<tr>
	  	        	<td>
	    	      		<table class="textonormal" border="0" align="left" width="100%">
										<tr>
										  <td class="textonormal" bgcolor="#DCEDF7" height="20">Ano da Associação*</td>
										  <td class="textonormal">
                        <select name="AnoExercicio" class="textonormal" onchange="javascript:document.TabGrupoSubelementoIntegrar.submit();">
                          <?php
                            if( $AnoExercicio == $AnoExercicioAtual ){
                              echo"<option value=\"$AnoExercicioAtual\" selected>$AnoExercicioAtual</option>\n";
                              echo"<option value=\"".($AnoExercicioAtual + 1)."\">".($AnoExercicioAtual + 1)."</option>\n";
                            } else {
                              echo"<option value=\"$AnoExercicioAtual\">$AnoExercicioAtual</option>\n";
                              echo"<option value=\"".($AnoExercicioAtual + 1)."\" selected>".($AnoExercicioAtual + 1)."</option>\n";
                            }
                          ?>
                        </select>
                      </td>
										</tr>
										<tr>
										  <td class="textonormal" bgcolor="#DCEDF7" height="20" width="40%">Tipo de Grupo*</td>
	                      <td class="textonormal">
                          <input name="TipoGrupo" id="TipoGrupoMaterial" value="M" onclick="javascript:document.TabGrupoSubelementoIntegrar.submit();" <?php if( $TipoGrupo == "M" ){ echo "checked"; }?> type="radio"> Material
	              	        <input name="TipoGrupo" id="TipoGrupoServico" value="S" onclick="javascript:document.TabGrupoSubelementoIntegrar.submit();" <?php if( $TipoGrupo == "S" ){ echo "checked"; }?> type="radio"> Serviço
	                      </td>
										</tr>

                    <?php if ($TipoGrupo == "M") { ?>
		                  <tr>
      		              <td class="textonormal" bgcolor="#DCEDF7" width="40%">Tipo de Material*</td>
      		              <td class="textonormal">
      		              	<input type="radio" id="TipoMaterialConsumo" name="TipoMaterial" value="C" onClick="javascript:document.TabGrupoSubelementoIntegrar.submit();" <?php if( $TipoMaterial == "C" ){ echo "checked"; } ?> > Consumo
      		              	<input type="radio" id="TipoMaterialPermanente" name="TipoMaterial" value="P" onClick="javascript:document.TabGrupoSubelementoIntegrar.submit();" <?php if( $TipoMaterial == "P" ){ echo "checked"; }?> > Permanente
                        </td>
                      </tr>
                    <?php } else {
                      $TipoMaterial = "";
                    } ?>

                    <!-- AQUI -->
                    <tr>
                      <td class="textonormal" bgcolor="#DCEDF7">Grupo*</td>
                      <td class="textonormal">
                        <select name="GrupoCodigo" class="textonormal">
                        	<option value="">Selecione um Grupo...</option>
                        	<?php
                            $QtdGruposAtivos = 0;
      						if( $TipoGrupo == "M" or $TipoGrupo == "S") {
      			                	$db     = Conexao();
      									if( $TipoMaterial == "C" or $TipoMaterial == "P") {
      									$sql 	= "SELECT GRU.CGRUMSCODI, GRU.EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO GRU ";
      									$sql   .= "WHERE  GRU.FGRUMSTIPO = 'M' AND GRU.FGRUMSSITU = 'A' ";
      			                		$sql   .= "AND GRU.FGRUMSTIPM = '$TipoMaterial' ";
      			                		$sql   .= "ORDER  BY GRU.EGRUMSDESC";
      			                		$result = $db->query($sql);
      			                		  
      			                		if (PEAR::isError($result)) {
      													    EmailErroDB("Erro de banco","Erro de banco",$result);
      													}else{
                                    $QtdGruposAtivos = $result->numRows();
      															while( $Linha = $result->fetchRow() ){
      				          	      			$DescricaoGrupo   = substr($Linha[1],0,75);
      				          	      			if( $Linha[0] == $GrupoCodigo ){
      									    	      			echo"<option value=\"$Linha[0]\" selected>$DescricaoGrupo</option>\n";
      							      	      		}else{
      									    	      			echo"<option value=\"$Linha[0]\">$DescricaoGrupo</option>\n";
      							      	      		}
      							      	      	}
      						              }
      			                	}	else {
      								if( $TipoGrupo == "S" ){
      				                	  # Mostra os grupos cadastrados #
      				                		$db     = Conexao();
      										$sql 	= " SELECT distinct GRU.CGRUMSCODI, GRU.EGRUMSDESC FROM SFPC.TBGRUPOMATERIALSERVICO GRU ";
      										$sql   .= "LEFT JOIN SFPC.TBGRUPOSUBELEMENTODESPESA SUB ";
      										$sql   .= "ON SUB.CGRUMSCODI = GRU.CGRUMSCODI  ";
      										$sql   .= "WHERE FGRUMSTIPO = 'S' ";
      										$sql   .= "AND fgrumssitu = 'A'  ";  
                          //$sql   .= "AND GRU.CGRUMSCODI NOT IN ( ";
      			           					//$sql   .= "SELECT CGRUMSCODI FROM SFPC.TBGRUPOSUBELEMENTODESPESA WHERE AGRUSEANOI = $AnoExercicio) ";
      										$sql   .= "ORDER BY EGRUMSDESC";

      										
      										
      				                		$result = $db->query($sql);
      				                		if (PEAR::isError($result)) {
      														    EmailErroDB("Erro de banco","Erro de banco",$result);
      														}else{
                                      $QtdGruposAtivos = $result->numRows();
      																while( $Linha = $result->fetchRow() ){
      					          	      			$DescricaoGrupo   = substr($Linha[1],0,75);
      					          	      			if( $Linha[0] == $GrupoCodigo ){
      										    	      			echo"<option value=\"$Linha[0]\" selected>$DescricaoGrupo</option>\n";
      								      	      		}else{
      										    	      			echo"<option value=\"$Linha[0]\">$DescricaoGrupo</option>\n";
      								      	      		}
      							                	}
      							              }
      				  	              }
      				  	            }
      				  	            $db->disconnect();
      		  	              }
            	            ?>
                        </select>
                      </td>
    
                    </tr>
                    <!-- AQUI -->
                    <tr>
                      <td class="textonormal" bgcolor="#DCEDF7">Sub-elemento de Despesa*</td>
                      <td class="textonormal">
                        <select name="SubElementoDespesa" class="textonormal" onchange="javascript:setarDescricaoCorrente();">
			                  	<option value="">Selecione um Sub-elemento de Despesa...</option>
                          <?php
                            if( ($TipoGrupo == "M" && $TipoMaterial != null) or $TipoGrupo == "S") {
                              $dbOracle     = ConexaoOracle();
                              $sql 		= "SELECT CELED1ELE1, CELED2ELE2, CELED3ELE3, CELED4ELE4,";
                              $sql 	 .= " CSUBEDELEM, NSUBEDNOME ";
                              $sql 	 .= " FROM SPOD.TBSUBELEMENTODESPESA";
                              $sql   .= " WHERE DEXERCANOR = $AnoExercicio";
                              $sql   .= " ORDER BY CELED1ELE1, CELED2ELE2, CELED3ELE3, CELED4ELE4,";
                              $sql 	 .= " CSUBEDELEM, NSUBEDNOME ";
                              
      						
                              $result = $dbOracle->query($sql);
                              
                            
                              
                              if (PEAR::isError($result)) {
                                  EmailErroDB("Erro de banco","Erro de banco",$result);
                              }else{

                                # Obtém os sub-elementos de despesa já integrados (Na tabela SFPC.TBGRUPOSUBELEMENTODESPESA - PostgreSQL)
                                # para comparar com todos os sub-elementos de despesa (da tabela SPOD.TBSUBELEMENTODESPESA - Oracle) removendo
                                # os já integrados do combobox.

                                $subElementosIntegrados = null;
                                $db     = Conexao();
                                $sql  = "SELECT (SUB.CGRUSEELE1 ||'.'|| ";
                                $sql  .= "       SUB.CGRUSEELE2 ||'.'|| ";
                                $sql  .= "       SUB.CGRUSEELE3 ||'.'|| ";
                                $sql  .= "       SUB.CGRUSEELE4 ||'.'|| ";
                                $sql  .= "       SUB.CGRUSESUBE) AS ELEMENTO_DESPESA ";
                                $sql  .= " FROM SFPC.TBGRUPOSUBELEMENTODESPESA SUB ";
                                //$sql  .= " WHERE AGRUSEANOI = $AnoExercicio AND FGRUSESITU in ('A','I') ";
                                $sql  .= " WHERE AGRUSEANOI = $AnoExercicio AND FGRUSESITU  = 'A' ";
                                $sql  .= " ORDER BY ELEMENTO_DESPESA ";
                                
                           
                                $resultPostgreSQL = $db->query($sql);

                                if (PEAR::isError($resultPostgreSQL)) {
                                  EmailErroDB("Erro de banco","Erro de banco",$resultPostgreSQL);
                                }else{
                                 //for ( $i = 0; $LinhaSubElemento = $result->fetchRow(); $i++ ) {
                                  while( $LinhaSubElemento = $resultPostgreSQL->fetchRow() ){
                                   //$subElementosIntegrados[$i] = $LinhaSubElemento[0];
                                   $subElementosIntegrados[] = $LinhaSubElemento[0];
                                  }
                                }
                                $db->disconnect();

                                while( $Linha = $result->fetchRow() ){
                                  $DescricaoAbreviadaSubElemento  = $Linha[5];
                                  $CodigoElementoDespesa = "$Linha[0].$Linha[1].$Linha[2].$Linha[3].$Linha[4]";

                                  // echo "<br>";
                                  // echo ("function strstr of SubElementoDespesa = ".substr(0,strrchr($SubElementoDespesa,'_')));
                                  // echo "<br>";
                                  // echo "CodigoElementoDespesa = ".$CodigoElementoDespesa;
                                  // echo "<br>";
                                  // exit;

                                  //substr(strrchr($PATH, ":"), 1);

                                  //while( $LinhaPostgreSQL = $resultPostgreSQL->fetchRow() ){

                                  if(!in_array ($CodigoElementoDespesa,$subElementosIntegrados) ){
                                    if( $CodigoElementoDespesa == substr($SubElementoDespesa,0,strpos($SubElementoDespesa, '{}')) ){
                                        echo"<option value=\"".$CodigoElementoDespesa."{}".$DescricaoAbreviadaSubElemento."\" selected>$CodigoElementoDespesa - ".substr($DescricaoAbreviadaSubElemento,0,63)."</option>\n";
                                    }else{
                                        echo"<option value=\"".$CodigoElementoDespesa."{}".$DescricaoAbreviadaSubElemento."\">$CodigoElementoDespesa - ".substr($DescricaoAbreviadaSubElemento,0,63)."</option>\n";
                                    }
                                  }
                                }
                              }
                              //echo $sql;
                              
                              $dbOracle->disconnect();
                              //exit;
                            }
            	            ?>
                        </select>
                      </td>
                    </tr>
									</table>
								</td>
		        	</tr>
	  	      	<tr>
   	  	  			<td class="textonormal" align="right">
                  <input type="hidden" name="NaturezaGrupo" value="<?php echo $NaturezaGrupo; ?>">
                  <input type="hidden" name="ObrigatoriedadeGrupo" value="<?php echo $ObrigatoriedadeGrupo; ?>">
                  <input type="hidden" name="Existe" value="<?php echo $ExisteIntegracao; ?>">
                  <input type="hidden" name="Programa" value="<?php echo $Programa; ?>">
                  <input type="hidden" name="DescricaoSubElemento" value="<?php echo $DescricaoSubElemento; ?>">
                  <input type="hidden" name="Botao" value="">
			            <input type="button" value="Integrar" class="botao" onclick="javascript:enviar('Integrar');">
                  <input type="button" name="Limpar" value="Limpar" class="botao" onClick="javascript:enviar('Limpar')">
		          	</td>
		        	</tr>
		        	<?php if( $ExisteIntegracao != 0 ){ ?>
		        	<tr>
	  	        	<td>
	    	      		<table border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" width="100%">
										<tr>
											<td class="titulo3" bgcolor="#BFDAF2" height="20" colspan="5" align="center">GRUPOS INTEGRADOS</td>
										</tr>
										<tr>
											<td class="titulo3" bgcolor="#DCEDF7" height="20" width="5%">&nbsp;</td>
											<td class="titulo3" bgcolor="#DCEDF7" height="20" width="45%">GRUPO</td>
											<td class="titulo3" bgcolor="#DCEDF7" height="20">SUB-ELEMENTO DE DESPESA</td>
                   						    <td class="titulo3" bgcolor="#DCEDF7" height="20">NATUREZA</td>
                 					        <td class="titulo3" bgcolor="#DCEDF7" height="20">OBRIGATORIEDADE DE CONTRATO </td>
										</tr>
							    	<?php
                  	# Mostra os grupos e sub-elementos integrados#
                		$db     = Conexao();
                		$sql    = "SELECT SUB.AGRUSEANOI, SUB.CGRUMSCODI, GRU.EGRUMSDESC, ";
                		$sql   .= "(SUB.CGRUSEELE1 ||'.'|| ";
                		$sql   .= " SUB.CGRUSEELE2 ||'.'|| ";
                		$sql   .= " SUB.CGRUSEELE3 ||'.'|| ";
                		$sql   .= " SUB.CGRUSEELE4 ||'.'|| ";
                		$sql   .= " SUB.CGRUSESUBE) AS ELEMENTO_DESPESA, ";
                		$sql   .= " SUB.NGRUSENOMS, SUB.FGRUSENATU, SUB.FGRUSECONT";
                		$sql   .= " FROM SFPC.TBGRUPOSUBELEMENTODESPESA SUB ";
                		$sql   .= " INNER JOIN SFPC.TBGRUPOMATERIALSERVICO GRU ";
                		$sql   .= " ON SUB.CGRUMSCODI = GRU.CGRUMSCODI AND FGRUMSSITU in ( 'A','I')  ";
                		$sql   .= " WHERE AGRUSEANOI = $AnoExercicio AND FGRUSESITU in ('A') ";
						$sql   .= " ORDER BY  GRU.EGRUMSDESC ";
                        
						 
			 
                               
						
						 
                        $result = $db->query($sql);
                		if( PEAR::isError($result) ){
										    EmailErroDB("Erro de banco","Erro de banco",$result);
										}else{
												$Rows = $result->numRows();
												
												for( $i=0; $i< $Rows;$i++ ){
														$Linha = $result->fetchRow();
    	          	  		echo "<tr>\n";
    	          	  		 
						    echo "	<td class=\"textonormal\" bgcolor=\"#F7F7F7\" height=\"20\" width=\"5%\" align=\"center\" valign=\"top\">\n";
							echo "		<input type=\"checkbox\" name=\"CheckUnidade[]\" value=\"".$Linha[0]."_".$Linha[1]."_".$Linha[3]."\">\n";
							echo "	</td>\n";
		          	    	echo "	<td class=\"textonormal\" bgcolor=\"#F7F7F7\" height=\"20\" width=\"45%\" valign=\"top\">\n";
		          	    	echo "		$Linha[2]\n";
							echo "	</td>\n";
		          	    	echo "	<td class=\"textonormal\" bgcolor=\"#F7F7F7\" height=\"20\" valign=\"top\">$Linha[3] - $Linha[4]</td>\n";
                            echo "	<td class=\"textonormal\" bgcolor=\"#F7F7F7\" height=\"20\" width=\"5%\" align=\"center\" valign=\"top\">\n";

                            if($Linha[5] == 'S'){ //$Linha[5] => NATUREZA - FGRUSENATU
                              echo "		<input type=\"checkbox\" name=\"CheckNatureza\" value=\"\" checked />\n";
                            } else {
                              echo "		<input type=\"checkbox\" name=\"CheckNatureza\" value=\"\" onclick=\"javascript:if(this.checked) {AlterarNatureza('$Linha[0]_$Linha[1]_$Linha[3]');}\" />\n";
                            }
												
                            echo "	<td class=\"textonormal\" bgcolor=\"#F7F7F7\" height=\"20\" width=\"5%\" align=\"center\" valign=\"top\">\n";

								
                            
                            
                            // OBRIGATORIEDADE - FGRUSECONT
                               echo "  <input type='checkbox' name='CheckObrigatoriedade[".$i."]'  onclick=\"javascript:AlterarObrigatoriedade('$Linha[0]_$Linha[1]_$Linha[3]_$i');\"  ";
                              if($Linha[6] == 'S'){
                              	echo " checked ";
                              }
                              echo " /> ";


							
														echo "	</td>\n";


		          	      			echo "</tr>\n";
			                	}
			              }
  	              	$db->disconnect();
      	            ?>
									</table>
								</td>
		        	</tr>
							<tr>
	  	        	<td>
	    	      		<table border="0" cellpadding="3" cellspacing="0" class="textonormal" width="100%" summary="" >
							<!-- 			<tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="31%">Total de Grupos Ativos</td>
											<td class="textonormal"> <?php echo $QtdGruposAtivos; ?> </td>
				     	    	</tr>
				     	     -->	
				     	    	
                    <tr>
											<td class="textonormal" bgcolor="#DCEDF7" height="20" width="31%">Total de Grupos Integrados no Ano</td>
											<td class="textonormal"><?php echo $i; ?></td>
				     	    	</tr>
									</table>
								</td>
		        	</tr>
			      	<tr>
				  			<td class="textonormal" align="right">
			        	  <input type="button" value="Retirar" class="botao" onclick="javascript:enviar('Retirar');">
			        	</td>
			      	</tr>
		        	<?php } ?>
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
