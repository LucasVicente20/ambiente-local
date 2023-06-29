<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: CadAnaliseCertidaoFornecedor.php
# Autor:    João Batista Brito
# Data:     15/03/13
# Objetivo: Programa de Renovação das Certidões Deferidas e Indeferida
#-------------------------------------------------------------------------
# Alterado: Daniel Augusto
# Data:		16/05/2023
# Objetivo: Tarefa Redmine 282903
# -----------------------------------------------------------------------------------------------------------------------------------------------


# Acesso ao arquivo de funções #
include '../funcoes.php';

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/ConsAcompFornecedorSelecionar.php' );
AddMenuAcesso( '/fornecedores/ConsAcompFornecedor.php' );
AddMenuAcesso( '/fornecedores/CadRenovacaoCadastroIncluir.php' );
AddMenuAcesso( '/fornecedores/CadAnaliseCertidaoFornecedor.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] != ""){
		$Botao        	= @$_REQUEST['Botao'];
		$ItemPesquisa 	= @$_REQUEST['ItemPesquisa'];
		$Argumento		  = strtoupper2(trim(@$_REQUEST['Argumento']));
		$Palavra		    = @$_REQUEST['Palavra'];
		$Desvio    	 	  = @$_REQUEST['Desvio'];
		$Sequencial     = @$_REQUEST['Sequencial'];
		$Situacao       = @$_REQUEST['Situacao'];
		$DescSituacao   = @$_REQUEST['DescSituacao'];
		$SituacaoCmb    = @$_REQUEST['SituacaoCmb'];
		$Codigo         = @$_REQUEST['Codigo'];
		$Mens      		  = @$_REQUEST['Mens'];
		$Mensagem  		  = @$_REQUEST['Mensagem'];
		$Tipo	  		    = @$_REQUEST['Tipo'];
		$Chave	  		  = @$_REQUEST['Chave'];
		$Confirmar	  	= @$_REQUEST['Confirmar'];

}


// Sessao de tratamento de desvios
if( $Botao == "Limpar" ){
	  header("location: CadAnaliseCertidaoFornecedor.php?Desvio=CadAnaliseCertidaoFornecedor&Sequencial=$Sequencial");
	  exit;
}

if( $Botao == "Voltar" ){
	  header("location: ConsAcompFornecedorSelecionar.php?Desvio=CadAnaliseCertidaoFornecedor");
	  exit;
}





/*
  echo var_dump($SituacaoCmb);
  echo "<br>";
  echo var_dump($Chave);
  exit;
*/

$db	  = Conexao();
$sql  = "SELECT A.DFORCRGERA, A.AFORCRSEQU, A.AFORCRCCGC, A.AFORCRCCPF, A.NFORCRRAZS ";
$sql .= "  FROM SFPC.TBFORNECEDORCREDENCIADO A WHERE A.AFORCRSEQU = $Sequencial ";
$result = $db->query($sql);
if( PEAR::isError($result) ){
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
	$Linha = $result->fetchRow();
  $Sequencial	   = $Linha[1];
	$CNPJ    			 = $Linha[2];
	$CPF					 = $Linha[3];
	$Razao         = $Linha[4];

}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadAnaliseCertidaoFornecedor.php?Desvio=CadAnaliseCertidaoFornecedor";

if( $Botao == "Limpar" ){
	  header("location: CadAnaliseCertidaoFornecedor.php?Desvio=CadAnaliseCertidaoFornecedor");
	  exit;
}

if ( trim($Confirmar) ==  "Confirmar" ) {

	// UPDATE DA TABELA
	//-- begin Transaction
	$result = executarTransacao($db, "BEGIN TRANSACTION");

	$i=-1;
	foreach ($SituacaoCmb as $valor) {
		 $i++;
		$situacaoAux = $SituacaoCmb[$i];
		$situacaoAux = trim($situacaoAux);

	  $chaveAux = $Chave[$i];
    $sql  = " update sfpc.tbrenovacaocertidoesforn ";
    if (!empty($situacaoAux)) {
 		    $sql .= " set frecefanal = '$situacaoAux', ";
    }
    else {
         $sql .= " set frecefanal = null, ";
    }
    $sql .= " crecefulat = now()  ";
    $sql .= ", cusupocod1 =".$_SESSION['_cusupocodi_'];
    $sql .= ", cusupocodi =".$_SESSION['_cusupocodi_'];
    $sql .= " where ";
    $sql .= " crecefcodi = $chaveAux ";
	  $result = executarTransacao($db, $sql);

		//echo "<p>$sql</p>";


  }
  executarTransacao($db, "COMMIT");
  executarTransacao($db, "END TRANSACTION");

  // Vou verificar se ainda falta deferir masi alguma certidão
  $sql = "  select count(*) as qtdnulas ";
  $sql .= " from sfpc.tbrenovacaocertidoesforn ";
  $sql .= " where ";
  $sql .= " aforcrsequ = $Sequencial ";
  $sql .= " and frecefanal is null ";
  $result = executarTransacao($db, $sql);
  $row = $result->fetchRow(DB_FETCHMODE_OBJECT);
  $qtdnulas = $row->qtdnulas;


}


/*
foreach ($Situacao as $i => $valor) {
		 if ($Situacao[$i] != "" && $DescSituacao[$i] == "") {
		 	$Mens = 1;$Tipo = 2;$Virgula=1;
		 	$Mensagem .= "<a href=\"javascript:document.getElementById('DescSituacao".$i."').focus();\" class=\"titulo2\">uma descrição para a certidão</a>";
	 	}
}
*/




?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" type="">
<!--
function enviar(valor){
	document.CadAnaliseCertidao.Botao.value=valor;
	document.CadAnaliseCertidao.submit();
}
<?php MenuAcesso(); ?>
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
<script language="JavaScript" src="../menu.js"></script>
<script language="JavaScript">Init();</script>
<form action="CadAnaliseCertidaoFornecedor.php?Desvio=CadAnaliseCertidaoFornecedor" method="post" name="CadAnaliseCertidao">
<br><br><br><br><br>
<table cellpadding="3" border="0" summary="">
  <!-- Caminho -->
  <tr>
    <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
    <td align="left" class="textonormal">
      <font class="titulo2">|</font>
      <a href="../index.php"><font color="#000000">Página Principal</font></a> > Fornecedores > Analisar Certidões
    </td>
  </tr>
  <!-- Fim do Caminho-->

	<!-- Erro -->
	<?php if ( $Mens == 1 ) {?>
	<tr>
	  <td width="100"></td>
	  <td align="left">
	  	<?php if ( $Mens == 1 ) { ExibeMens($Mensagem,$Tipo,$Virgula); } ?>
	  </td>
	</tr>
	<?php } ?>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td width="150"></td>
		<td class="textonormal">
		<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal" bgcolor="#FFFFFF">
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
	        	ANÁLISE DO PEDIDO DE RENOVAÇÃO DE CERTIDÕES
    </td>
        </tr>
        <tr>
          <td class="textonormal" colspan="4">
             <p align="justify">
             Selecione a Situação que deseja analisar e clique no botão "Pesquisar".<br><br>
             Os dados abaixo referem-se ao histórico dos Registros de Intenção de Renovação de Certidões do Fornecedor.<br>
             Para proceder a análise informe: o resultado da análise para cada pedido e clique em confirmar.<br>
             Para Voltar a tela de pesquisa clique no botão "Voltar".<br><br>
             </p>
          </td>
        </tr>
        <tr>
          <td colspan="4">
            <table>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">CNPJ </td>
               	  <td class="textonormal"><?php echo FormataCNPJ($CNPJ) ; ?>
               	  <input type="hidden" name="CNPJ" value="<?php echo $CNPJ; ?>">
             	  </td>
							</tr>
              <tr>
                <td class="textonormal" bgcolor="#DCEDF7">Razão Social/Nome </td>
               	<td class="textonormal"><?php echo $Razao; ?>
               	<input type="hidden" name="Razao" value="<?php echo $Razao; ?>">
              </td>
              </tr>
              <tr>
              	<td class="textonormal" bgcolor="#DCEDF7" >Situação </td>
	              <td class="textonormal">
	                <select name="Situacao" value="<?echo $DescSituacao;?>" class="textonormal" readonly >
	        	       <!--  <option value="0" <?php if ( $Situacao == "0" ) { echo "selected"; }?>> </option> -->
	        	        <option value="9" <?php if ( $Situacao == "9" ) { echo "selected"; }?>>Todas</option>
                    <option value="D" <?php if ( $Situacao == "D" ) { echo "selected"; }?>>Deferido</option>
                    <option value="I" <?php if ( $Situacao == "I" ) { echo "selected"; }?>>Indeferido</option>
                  </select>
                </td>
	            </tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right" colspan="4">
	          <input type="button" value="Pesquisar" class="botao" onclick="javascript:enviar('Pesquisar');">
						<input name="limpar" type="button" value="Limpar" class="botao" onclick="javascript:enviar('Limpar')">
	          <input type="hidden" name="Botao" value="">
	          <input type="hidden" name="Sequencial" value="<?php echo $Sequencial; ?>">
	          <input type="hidden" name="Desvio" value="<?php echo $Desvio; ?>">

          </td>
        </tr>
<?
echo "	<tr>\n";
echo "		<td align=\"center\" bgcolor=\"#DCEDF7\" colspan=\"4\" class=\"titulo3\">RESULTADO DA PESQUISA</td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td align=\"center\" class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"40%\">CERTIDÕES</td>\n";
echo "		<td align=\"center\" class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"18%\">DATA REGISTRO</td>\n";
echo "		<td align=\"center\" class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"32%\">MOTIVO</td>\n";
echo "		<td align=\"center\" class=\"titulo3\" bgcolor=\"#F7F7F7\" width=\"10%\">SITUAÇÃO</td>\n";
echo "	</tr>\n";

	 // Inicio da grade
		$sql  = " select a.crecefcodi as codigo, b.etipcedesc as descricao, to_char(a.drecefdreg,'DD/MM/YYYY' ) as data, a.arecefmotc as motivo, a.frecefanal as situacao, to_char(a.drecefdreg,'YYYY/MM/DD' ) as datainvertida  ";
		$sql .= " from sfpc.tbrenovacaocertidoesforn a, sfpc.tbtipocertidao b ";
		$sql .= " where ";
		$sql .= " a.aforcrsequ= $Sequencial ";

if ($Situacao=='0') {
	$sql .= " and  a.frecefanal is null ";
}

if ($Situacao=='9') {
	// não precisa de filtro
}

if ($Situacao=='D') {
	$sql .= " and  a.frecefanal = 'D' ";
}

if ($Situacao=='I') {
	$sql .= " and  a.frecefanal = 'I' ";
}

$sql .= " and a.ctipcecodi=b.ctipcecodi ";
$sql .= " order by datainvertida desc ";
$result = executarTransacao($db, $sql);

$i=-1;
while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)  ) {
  $i++;
	$codigo    = $row->codigo;
  $descricao = $row->descricao;
  $data      = $row->data;
  $motivo    = strtoupper2($row->motivo);
  $Situacao  = $row->situacao;
	echo "<tr>";
	echo "		<td>$descricao</td>\n";
	echo "		<td>$data</td>\n";
	echo "		<td>$motivo</td>\n";
  ?>

  <td>
  	 <select name="SituacaoCmb[<?=$i;?>]"   class="textonormal"   >
     <option value="0"  <?php if ( $Situacao == "" )  { echo "selected"; }?> > </option>
     <option value="D"  <?php if ( $Situacao == "D" ) { echo "selected"; }?> >Deferido</option>
     <option value="I"  <?php if ( $Situacao == "I" ) { echo "selected"; }?> >Indeferido</option>
     </select>
     <input type="hidden" name="Chave[<?=$i;?>]"  value="<?=$codigo; ?>" >
   </td>

  <?

 }
   $db->disconnect();


    if ( $qtdnulas > 0   ) {
   	 ?>
   	    <script language="javascript" type="">
   	      alert('Existe <?=$qtdnulas?> certidão(ões) não analizadas!');
   	    </script>

   	 <?

  }



	 ?>

	     </table>
	        </td>
		        </tr>
	  	         <tr>
       	      	<td class="textonormal" align="right" colspan=4>
       	      		<input type="submit" name="Confirmar" value=" Confirmar " class="botao">
       	     <!--   		<input type="submit" name="Voltar" value=" Voltar " class="botao">  -->
       	      		<input type="button" value=" Voltar " class="botao" onclick="javascript:enviar('Voltar');">

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
<script language="javascript" type="">

document.CadAnaliseCertidao.focus();

</script>
