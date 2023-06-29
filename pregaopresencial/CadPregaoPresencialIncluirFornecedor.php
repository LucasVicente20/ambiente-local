<?php
# -------------------------------------------------------------------------
# Portal da DGCO teste
# Programa: CadPregaoPresencialIncluirFornecedor.php
# Autor:    Hélio Miranda
# Data:     29/07/2016
# Objetivo: Programa de Manutenção de Usuário
# OBS.:     Tabulação 2 espaços
# -------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:		24/10/2018
# Objetivo: Tarefa Redmine 73662
# -------------------------------------------------------------------------

header("Content-Type: text/html; charset=UTF-8",true);


# Acesso ao arquivo de funções #
include "../funcoes.php";

# Executa o controle de segurança #
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/tabelasbasicas/TabUsuarioAlterar.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Encoding = 'UTF-8';
		$UsuarioCodigo = $_POST['UsuarioCodigo'];
		$Critica       = $_POST['Critica'];
		
		$_SESSION['Botao']					= $_POST['Botao'];
		$_SESSION['RazaoSocial']			= mb_strtoupper ( $_POST['RazaoSocial'], $Encoding );
		$_SESSION['CpfCnpj']				= $_POST['CpfCnpj'];
		$_SESSION['RepresentanteNome']		= mb_strtoupper ( $_POST['RepresentanteNome'], $Encoding );
		$_SESSION['RepresentanteRG']		= mb_strtoupper ( $_POST['RepresentanteRG'], $Encoding );		
		$_SESSION['RepresentanteOrgaoUF']	= mb_strtoupper ( $_POST['RepresentanteOrgaoUF'], $Encoding );
		$_SESSION['TipoEmpresa']			= $_POST['TipoEmpresa'];
		
}else{
		$Critica       = $_GET['Critica'];
		$Mensagem      = urldecode($_GET['Mensagem']);
		$Mens          = $_GET['Mens'];
		$Tipo          = $_GET['Tipo'];
}

function validarCPF($cpf = null) {
 
    // Verifica se um número foi informado
    if(empty($cpf)) {
        return false;
    }
 
    // Elimina possivel mascara
    $cpf = ereg_replace('[^0-9]', '', $cpf);
    $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
     
    // Verifica se o numero de digitos informados é igual a 11 
    if (strlen($cpf) != 11) {
        return false;
    }
    // Verifica se nenhuma das sequências invalidas abaixo 
    // foi digitada. Caso afirmativo, retorna falso
    else if ($cpf == '00000000000' || 
        $cpf == '11111111111' || 
        $cpf == '22222222222' || 
        $cpf == '33333333333' || 
        $cpf == '44444444444' || 
        $cpf == '55555555555' || 
        $cpf == '66666666666' || 
        $cpf == '77777777777' || 
        $cpf == '88888888888' || 
        $cpf == '99999999999') {
        return false;
     // Calcula os digitos verificadores para verificar se o
     // CPF é válido
     } else {   
         
        for ($t = 9; $t < 11; $t++) {
             
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf{$c} * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf{$c} != $d) {
                return false;
            }
        }
 
        return true;
    }
}

function validarCNPJ($cnpj)
{
	if(empty($cnpj)) 
	{
        return false;
    }
	
	$cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);
	// Valida tamanho
	if (strlen($cnpj) != 14)
		return false;
	// Valida primeiro dígito verificador
	for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++)
	{
		$soma += $cnpj{$i} * $j;
		$j = ($j == 2) ? 9 : $j - 1;
	}
	$resto = $soma % 11;
	if ($cnpj{12} != ($resto < 2 ? 0 : 11 - $resto))
		return false;
	
	if ($cnpj == '00000000000000' || 
			$cnpj == '11111111111111' || 
			$cnpj == '22222222222222' || 
			$cnpj == '33333333333333' || 
			$cnpj == '44444444444444' || 
			$cnpj == '55555555555555' || 
			$cnpj == '66666666666666' || 
			$cnpj == '77777777777777' || 
			$cnpj == '88888888888888' || 
			$cnpj == '99999999999999') 
	{
		return false;	
	}
	// Valida segundo dígito verificador
	for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++)
	{
		$soma += $cnpj{$i} * $j;
		$j = ($j == 2) ? 9 : $j - 1;
	}
	$resto = $soma % 11;
	return $cnpj{13} == ($resto < 2 ? 0 : 11 - $resto);
}


$_SESSION['TipoCnpjCpf']     = $_POST['TipoCnpjCpf'];

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = "CadPregaoPresencialIncluirFornecedor.php";

if($Critica == 1){
		# Critica dos Campos #
		$Mens     = 0;
		$Mensagem = "Informe: ";	
}

if($_SESSION['Botao'] == "Limpar")
{
	$_SESSION['Botao']					= null;
	$_SESSION['RazaoSocial']			= null;
	$_SESSION['CpfCnpj']				= null;
	$_SESSION['RepresentanteNome']		= null;
	$_SESSION['RepresentanteRG']		= null;		
	$_SESSION['RepresentanteOrgaoUF']	= null;
	$_SESSION['TipoEmpresa']				= null;
}	

if($_SESSION['Botao'] == "IncluirFornecedor")
{
	$_SESSION['Botao'] = null;
	
	$RazaoSocial 			= $_SESSION['RazaoSocial'];
	$CpfCnpj 				= $_SESSION['CpfCnpj'];
	$RepresentanteNome 		= $_SESSION['RepresentanteNome'];
	$RepresentanteRG 		= $_SESSION['RepresentanteRG'];	
	$RepresentanteOrgaoUF 	= $_SESSION['RepresentanteOrgaoUF'];
	$PregaoCod 				= $_SESSION['PregaoCod'];
	$TipoEmpresa 			= $_SESSION['TipoEmpresa'];
	$TipoCnpjCpf			= $_SESSION['TipoCnpjCpf'];
	$PreenchimentoCorreto 	= True;
	$SemRepresentante		= True;
	
	if($TipoCnpjCpf == "CNPJ")
	{
		if(strlen($CpfCnpj)  == "")
		{
			$PreenchimentoCorreto = False;
			
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "- Campo obrigatório [<a href=\"javascript:document.CadPregaoPresencialIncluirFornecedor.CpfCnpj.focus();\" class=\"titulo2\" style=\"color: red;\">CNPJ*</a>] não preenchido!<br />";			
		}
		
		if(strlen($CpfCnpj)  > 0 and (strlen($CpfCnpj)  < 14 or strlen($CpfCnpj)  > 14))
		{
			$PreenchimentoCorreto = False;
			
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "- CNPJ Inválido!<br />";			
		}	
	}
	else
	{
		if(strlen($CpfCnpj)  == "")
		{
			$PreenchimentoCorreto = False;
			
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "- Campo obrigatório [<a href=\"javascript:document.CadPregaoPresencialIncluirFornecedor.CpfCnpj.focus();\" class=\"titulo2\" style=\"color: red;\">CPF</a>] não preenchido!<br />";			
		}

		if(strlen($CpfCnpj)  > 0 and (strlen($CpfCnpj)  < 11 or strlen($CpfCnpj)  > 11))
		{
			$PreenchimentoCorreto = False;
			
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "- CPF Inválido!<br />";			
		}		
	}	
	
	if($TipoCnpjCpf == "CNPJ")
	{	
		if($RazaoSocial == '')
		{
			$PreenchimentoCorreto = False;
			
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "- Campo obrigatório [<a href=\"javascript:document.CadPregaoPresencialIncluirFornecedor.RazaoSocial.focus();\" class=\"titulo2\" style=\"color: red;\">Razão Social*</a>] não preenchido!<br />";			
		}
		
		if($TipoEmpresa == "")
		{
			$PreenchimentoCorreto = False;
			
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "- Campo obrigatório [<a href=\"javascript:document.CadPregaoPresencialIncluirFornecedor.TipoEmpresa.focus();\" class=\"titulo2\" style=\"color: red;\">Tipo de Empresa</a>] não preenchido!<br />";			
		}		
	}
	else
	{
		if($RazaoSocial == '')
		{
			$PreenchimentoCorreto = False;
			
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "- Campo obrigatório [<a href=\"javascript:document.CadPregaoPresencialIncluirFornecedor.RazaoSocial.focus();\" class=\"titulo2\" style=\"color: red;\">Nome*</a>] não preenchido!<br />";			
		}		
	}
	
	if($RepresentanteNome <> '')
	{
		$SemRepresentante = False;
		
		if($RepresentanteRG == '')
		{
			$PreenchimentoCorreto = False;
			
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "- Campo obrigatório [<a href=\"javascript:document.CadPregaoPresencialIncluirFornecedor.RepresentanteRG.focus();\" class=\"titulo2\" style=\"color: red;\">Representante R.G.*</a>] não preenchido!<br />";			
		}
		
		if($RepresentanteOrgaoUF == '')
		{
			$PreenchimentoCorreto = False;
			
			$_SESSION['Mens'] = 1;
			$_SESSION['Tipo'] = 2;
			$_SESSION['Mensagem'] .= "- Campo obrigatório [<a href=\"javascript:document.CadPregaoPresencialIncluirFornecedor.RepresentanteOrgaoUF.focus();\" class=\"titulo2\" style=\"color: red;\">Órgao Emissor/UF*</a>] não preenchido!<br />";			
		}
	}
	else
	{	
		$SemRepresentante = True;

		$_SESSION['Mens'] = 1;
		$_SESSION['Tipo'] = 2;
		$_SESSION['Mensagem'] .= "- Fornecedor SEM REPRESENTANTE. Não poderá dar lances!<br />";
	}	

	
	//VALIDAR CPF/CNPJ

	if($PreenchimentoCorreto == True)
	{
		$db     = Conexao();
		
		if(strlen($CpfCnpj)  == 14)
		{
			if(validarCNPJ($CpfCnpj))
			{
				$sqlSolicitacoes = "SELECT		cpregfsequ
									FROM 		sfpc.tbpregaopresencialfornecedor 
									WHERE 		apregfccgc	= '$CpfCnpj'
										AND		cpregasequ	= $PregaoCod";

							
				$result = $db->query($sqlSolicitacoes);
					
					if( PEAR::isError($resultSoli) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
					}
					
					$Linha = $result->fetchRow();
					
					$intQuantidade = 0;
					
					$intQuantidade = $result->numRows();		
					
					
					if($intQuantidade == 0){
						$sql = "SELECT MAX(cpregfsequ) FROM sfpc.tbpregaopresencialfornecedor";
						$res = $db->query($sql);
						
						if (PEAR::isError($res)) {
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Linha  = $res->fetchRow();
								$Codigo = $Linha[0] + 1;
						}			
					
					
						# Insere Membro de Comissão #
						$sql  = "INSERT INTO sfpc.tbpregaopresencialfornecedor( ";
						$sql .= "cpregfsequ, cpregasequ, fpregfmepp, apregfccgc, apregfccpf, npregfrazs, npregfnomr, apregfnurg, npregforgu, epregfsitu, ";
						$sql .= "dpregfcada, ";
						$sql .= "tpregfulat ";
						$sql .= " ) VALUES ( ";
						$sql .= "$Codigo, $PregaoCod, $TipoEmpresa, '$CpfCnpj', '', '$RazaoSocial', '$RepresentanteNome', '$RepresentanteRG', '$RepresentanteOrgaoUF','".($SemRepresentante ? 'S' : 'C')."',";
						$sql .= "'".date("Y-m-d")."', ";
						$sql .= "'".date("Y-m-d H:i:s")."' )";
						
						$res  = $db->query($sql);
						
						
						if( PEAR::isError($res) ){
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}  
						
						$_SESSION['Mens'] = 1;
						$_SESSION['Tipo'] = 1;
						$_SESSION['Mensagem'] .= "- Fornecedor incluído com sucesso";	
						
						//Início Classificação e Preço Inicial
						
							$sqlLote    = "SELECT 		 	pl.cpregtsequ ";
							$sqlLote   .= "  FROM 		   	sfpc.tbpregaopresenciallote pl";
							$sqlLote   .= "  WHERE 			pl.cpregasequ = ".$_SESSION['PregaoCod']." ";
							$sqlLote   .= "  ORDER BY 		pl.cpregtnuml";
							
							$result = $db->query($sqlLote);
							$Linha = $result->fetchRow();
							$intQuantidade = $result->numRows();
							
							if($intQuantidade > 0)
							{
								for ($itr = 0; $itr < $intQuantidade; ++ $itr) 
								{						
									# Insere Classificação #
									$sql  = "INSERT INTO sfpc.tbpregaopresencialclassificacao( ";
									$sql .= "cpregfsequ, cpregtsequ, cpresfsequ, cusupocodi, epregcmoti,";
									$sql .= "dpregccada, ";
									$sql .= "tpregculat ";
									$sql .= " ) VALUES ( ";
									$sql .= "$Codigo, $Linha[0], 1, ".$_SESSION['_cusupocodi_'].", '',";
									$sql .= "'".date("Y-m-d")."', ";
									$sql .= "'".date("Y-m-d H:i:s")."' )";
									
									$res  	= $db->query($sql);	

									#Recebe o último código de Preço Inicial#
									$sql = "SELECT MAX(cpregpsequ) FROM sfpc.tbpregaopresencialprecoinicial";
									$res = $db->query($sql);
									
									if (PEAR::isError($res)) {
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{
											$LinhaPrecoInicial  			= $res->fetchRow();
											$CodigoPrecoInicial 			= $LinhaPrecoInicial[0] + 1;
									}							
									
									#Insere Preço Inicial#
									$sql  = "INSERT INTO sfpc.tbpregaopresencialprecoinicial( ";
									$sql .= "cpregpsequ, cpregfsequ, cpregtsequ, vpregpvali, fpregpalan, cpregpoemp, ";
									$sql .= "dpregpcada, ";
									$sql .= "tpregpulat ";
									$sql .= " ) VALUES ( ";
									$sql .= "$CodigoPrecoInicial, $Codigo, $Linha[0], 0.00, 0, 0,";
									$sql .= "'".date("Y-m-d")."', ";
									$sql .= "'".date("Y-m-d H:i:s")."' )";
									
									$res  = $db->query($sql);							
									
									if (PEAR::isError($res)) {
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}								
									
									#Avança uma linha nas buscas#
									$Linha = $result->fetchRow();
									
									$sql = "UPDATE sfpc.tbpregaopresencialprecoinicial SET fpregpalan = 0, vpregpvali = 0.00 WHERE cpregfsequ IN (SELECT DISTINCT cpregfsequ FROM tbpregaopresencialfornecedor fr, tbpregaopresencial pp WHERE pp.cpregasequ = fr.cpregasequ AND pp.cpregasequ = $PregaoCod)";
									$res = $db->query($sql);
									if( PEAR::isError($res) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
									}								
								}
							}					
						
						//Fim Classificação e Preço Inicial					

						echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Destino.value = 'B';</script>";	
						echo "<script>opener.document.CadPregaoPresencialSessaoPublica.submit()</script>";						
						
						$_SESSION['Botao']					= null;
						$_SESSION['RazaoSocial']			= null;
						$_SESSION['CpfCnpj']				= null;
						$_SESSION['RepresentanteNome']		= null;
						$_SESSION['RepresentanteRG']		= null;	
						$_SESSION['RepresentanteOrgaoUF']	= null;
					
					}
					else
					{
						$_SESSION['Mens'] = 1;
						$_SESSION['Tipo'] = 2;
						$_SESSION['Mensagem'] .= "O Fornecedor [Pessoa Jurícica] já está vinculado ao Pregão Presencial selecionado";	
					}
					
					$db->disconnect();
			}
			else
			{
				$_SESSION['Mens'] = 1;
				$_SESSION['Tipo'] = 2;
				$_SESSION['Mensagem'] .= "CNPJ inválido!";					
			}	
		}
		else if(strlen($CpfCnpj)  == 11)
		{
			if(validarCPF($CpfCnpj))
			{			
				$sqlSolicitacoes = "SELECT		cpregfsequ
									FROM 		sfpc.tbpregaopresencialfornecedor
									WHERE 		apregfccpf	= '$CpfCnpj'
										AND		cpregasequ	= $PregaoCod";

				$result = $db->query($sqlSolicitacoes);
					
					if( PEAR::isError($resultSoli) ){
						ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
					}
					
					$Linha = $result->fetchRow();
					
					$intQuantidade = 0;
					
					$intQuantidade = $result->numRows();		
					
					
					if($intQuantidade == 0){
						$sql = "SELECT MAX(cpregfsequ) FROM sfpc.tbpregaopresencialfornecedor";
						$res = $db->query($sql);
						
						if (PEAR::isError($res)) {
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}else{
								$Linha  = $res->fetchRow();
								$Codigo = $Linha[0] + 1;
						}			
					
					
						# Insere Membro de Comissão #
						$sql  = "INSERT INTO sfpc.tbpregaopresencialfornecedor( ";
						$sql .= "cpregfsequ, cpregasequ, fpregfmepp, apregfccgc, apregfccpf, npregfrazs, npregfnomr, apregfnurg, npregforgu, epregfsitu, ";
						$sql .= "dpregfcada, ";
						$sql .= "tpregfulat ";
						$sql .= " ) VALUES ( ";
						$sql .= "$Codigo, $PregaoCod, 0, '', '$CpfCnpj', '$RazaoSocial', '$RepresentanteNome', '$RepresentanteRG', '$RepresentanteOrgaoUF','".($SemRepresentante ? 'S' : 'C')."',";
						$sql .= "'".date("Y-m-d")."', ";
						$sql .= "'".date("Y-m-d H:i:s")."' )";
						
						
						$res  = $db->query($sql);
						
						
						if( PEAR::isError($res) ){
							ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
						}  
						
						$_SESSION['Mens'] = 1;
						$_SESSION['Tipo'] = 1;
						$_SESSION['Mensagem'] .= "- Fornecedor incluído com sucesso";	

						//Início Classificação e Preço Inicial
						
							$sqlLote    = "SELECT 		 	pl.cpregtsequ ";
							$sqlLote   .= "  FROM 		   	sfpc.tbpregaopresenciallote pl";
							$sqlLote   .= "  WHERE 			pl.cpregasequ = ".$_SESSION['PregaoCod']." ";
							$sqlLote   .= "  ORDER BY 		pl.cpregtnuml";
							
							$result = $db->query($sqlLote);
							$Linha = $result->fetchRow();
							$intQuantidade = $result->numRows();
							
							if($intQuantidade > 0)
							{
								for ($itr = 0; $itr < $intQuantidade; ++ $itr) 
								{						
									# Insere Classificação #
									$sql  = "INSERT INTO sfpc.tbpregaopresencialclassificacao( ";
									$sql .= "cpregfsequ, cpregtsequ, cpresfsequ, cusupocodi, epregcmoti,";
									$sql .= "dpregccada, ";
									$sql .= "tpregculat ";
									$sql .= " ) VALUES ( ";
									$sql .= "$Codigo, $Linha[0], 1, ".$_SESSION['_cusupocodi_'].", '',";
									$sql .= "'".date("Y-m-d")."', ";
									$sql .= "'".date("Y-m-d H:i:s")."' )";
									
									$res  	= $db->query($sql);	

									#Recebe o último código de Preço Inicial#
									$sql = "SELECT MAX(cpregpsequ) FROM sfpc.tbpregaopresencialprecoinicial";
									$res = $db->query($sql);
									
									if (PEAR::isError($res)) {
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}else{
											$LinhaPrecoInicial  			= $res->fetchRow();
											$CodigoPrecoInicial 			= $LinhaPrecoInicial[0] + 1;
									}							
									
									#Insere Preço Inicial#
									$sql  = "INSERT INTO sfpc.tbpregaopresencialprecoinicial( ";
									$sql .= "cpregpsequ, cpregfsequ, cpregtsequ, vpregpvali, fpregpalan, cpregpoemp, ";
									$sql .= "dpregpcada, ";
									$sql .= "tpregpulat ";
									$sql .= " ) VALUES ( ";
									$sql .= "$CodigoPrecoInicial, $Codigo, $Linha[0], 0.00, 0, 0,";
									$sql .= "'".date("Y-m-d")."', ";
									$sql .= "'".date("Y-m-d H:i:s")."' )";
									
									$res  = $db->query($sql);							
									
									if (PEAR::isError($res)) {
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
									}								
									
									#Avança uma linha nas buscas#
									$Linha = $result->fetchRow();
									
									$sql = "UPDATE sfpc.tbpregaopresencialprecoinicial SET fpregpalan = 0, vpregpvali = 0.00 WHERE cpregfsequ IN (SELECT DISTINCT cpregfsequ FROM tbpregaopresencialfornecedor fr, tbpregaopresencial pp WHERE pp.cpregasequ = fr.cpregasequ AND pp.cpregasequ = $PregaoCod)";
									$res = $db->query($sql);
									if( PEAR::isError($res) ){
										ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlSolicitacoes");
									}								
								}
							}					
						
						//Fim Classificação e Preço Inicial						
						
						echo "<script>opener.document.CadPregaoPresencialSessaoPublica.Destino.value = 'B';</script>";	
						echo "<script>opener.document.CadPregaoPresencialSessaoPublica.submit()</script>";	
						
						$_SESSION['Botao']					= null;
						$_SESSION['RazaoSocial']			= null;
						$_SESSION['CpfCnpj']				= null;
						$_SESSION['RepresentanteNome']		= null;
						$_SESSION['RepresentanteRG']		= null;
						$_SESSION['RepresentanteOrgaoUF']	= null;
				
					}
					else
					{
						$_SESSION['Mens'] = 1;
						$_SESSION['Tipo'] = 2;
						$_SESSION['Mensagem'] .= "O Fornecedor [Pessoa Física] já está vinculado ao Pregão Presencial SELECIONADO";	
					}
			}
			else
			{
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= "CPF inválido!";					
			}
		}
		else
		{
					$_SESSION['Mens'] = 1;
					$_SESSION['Tipo'] = 2;
					$_SESSION['Mensagem'] .= "Erro Interno do Sistema!";	
		}
		
		$db->disconnect();			
	}
	
}

?>
<html>
<head>
<title>Portal de Compras - Incluir Fornecedor</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script language="javascript" type="">
<!--
function checktodos(){
	document.CadPregaoPresencialIncluirFornecedor.Subclasse.value = '';
	document.CadPregaoPresencialIncluirFornecedor.submit();
}
function enviar(valor){
	document.CadPregaoPresencialIncluirFornecedor.Botao.value = valor;
	document.CadPregaoPresencialIncluirFornecedor.submit();
}
function validapesquisa(){
	if( ( document.CadPregaoPresencialIncluirFornecedor.MaterialDescricaoDireta.value != '' ) || ( document.CadPregaoPresencialIncluirFornecedor.SubclasseDescricaoDireta.value != '') ) {
		if(document.CadPregaoPresencialIncluirFornecedor.Grupo){
			document.CadPregaoPresencialIncluirFornecedor.Grupo.value = '';
		}
		if(document.CadPregaoPresencialIncluirFornecedor.Classe){
			document.CadPregaoPresencialIncluirFornecedor.Classe.value = '';
		}
		document.CadPregaoPresencialIncluirFornecedor.Botao.value = 'Validar';
	}
	if(document.CadPregaoPresencialIncluirFornecedor.Subclasse){
		if(document.CadPregaoPresencialIncluirFornecedor.SubclasseDescricaoFamilia.value != "") {
			document.CadPregaoPresencialIncluirFornecedor.Subclasse.value = '';
		}
	}
	document.CadPregaoPresencialIncluirFornecedor.submit();
}
function AbreJanela(url,largura,altura) {
	window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=45,top=150,width='+largura+',height='+altura);
}
function voltar(){
	self.close();
}
function remeter(){
	document.CadPregaoPresencialIncluirFornecedor.submit();
}
//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">

</head>
<body background="../midia/bg.jpg" marginwidth="0" marginheight="0">
<form action="CadPregaoPresencialIncluirFornecedor.php" method="post" name="CadPregaoPresencialIncluirFornecedor">
<table cellpadding="3" border="0" summary="" width="100%">
	<!-- Erro -->
	<tr>
		<td>
			<?php 
				
				if( $_SESSION['Mens'] != 0 ){ ExibeMens($_SESSION['Mensagem'],$_SESSION['Tipo'],1);}
				
				$_SESSION['Mens'] = null;
				$_SESSION['Tipo'] = null;
				$_SESSION['Mensagem'] = null	
			?>
		</td>
	</tr>
	<!-- Fim do Erro -->

	<!-- Corpo -->
	<tr>
		<td class="textonormal">
			<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF" >
        <tr>
          <td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3">
	        	INCLUIR - FORNECEDOR
          </td>
        </tr>
        <tr>
          <td class="textonormal">
             <p align="justify">
             Para incluir um Fornecedor ao Pregão Presencial, preencha todas as informações e clique no botão "Incluir Fornecedor".
             </p>
          </td>
        </tr>
        <?php
				# Pega a descrição do Perfil do usuário logado #
				if( $_SESSION['_cperficodi_'] != 2 and $_SESSION['_cperficodi_'] != 0 ){
						$db  = Conexao();
						$sqlusuario = "SELECT CPERFICODI, EPERFIDESC FROM SFPC.TBPERFIL ";
						$sqlusuario .= "WHERE CPERFICODI = ".$_SESSION['_cperficodi_']." ";
						$resultUsuario = $db->query($sqlusuario);
						if( PEAR::isError($result) ){
						    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sqlusuario");
						}else{
            		$PerfilUsuario = $resultUsuario->fetchRow();
            		$PerfilUsuarioDesc = $PerfilUsuario[1];
						}
				}
				?>
        <tr>
          <td>
            <table border="0" summary="" width="100%">
              <tr>
                <td class="textonormal" bgcolor="#FFFFFF">
					<table border="0" width="100%" summary="">
					  <tr>
						<td width="40%" align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">
							<input type="radio" name="TipoCnpjCpf" value="CPF" <?php if( $_SESSION['TipoCnpjCpf'] == "" or $_SESSION['TipoCnpjCpf'] == "CPF" ){ echo "checked"; }?> onclick="document.CadPregaoPresencialIncluirFornecedor.Critica.value=1; javascript:submit();"> CPF /
							<input type="radio" name="TipoCnpjCpf" value="CNPJ" <?php if( $_SESSION['TipoCnpjCpf'] == "CNPJ" ){ echo "checked"; }?> onclick="document.CadPregaoPresencialIncluirFornecedor.Critica.value=1; javascript:submit();">CNPJ:*
						</td>
						<td align="left" class="textonormal" colspan="3" >
							<input type="text" name="CpfCnpj" size="15" maxlength="<?=(( $_SESSION['TipoCnpjCpf'] == "" or $_SESSION['TipoCnpjCpf'] == "CPF" ) ? ("11") : ("14"))?>" value="<?php echo $_SESSION['CpfCnpj'] ?>" class="textonormal">
							<input type="hidden" name="Critica" size="1" value="1">
						</td>
					  </tr>
					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">
							<?php echo $_SESSION['TipoCnpjCpf'] == "CNPJ" ? 'Razão Social' : 'Nome'?>:*
						</td>
						<td align="left" class="textonormal" colspan="3" >
							<input type="text" name="RazaoSocial" size="45" maxlength="120" value="<?php echo $_SESSION['RazaoSocial'] ?>" class="textonormal">
						</td>
					  </tr>
					  
					  <?
					  if($_SESSION['TipoCnpjCpf'] == "CNPJ")
					  {
					  ?>
					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Tipo de Empresa:*</td>		
						<td align="left" class="textonormal" colspan="3" >						
						  <select name="TipoEmpresa" class="textonormal">
							<option value="" <?php echo (($_SESSION['TipoEmpresa'] == 0 or $_SESSION['TipoEmpresa'] == '' or $_SESSION['TipoEmpresa'] == null)? 'selected' : '');?>>Selecione o Tipo de Empresa</option>
							<option value="0" <?php echo ($_SESSION['TipoEmpresa'] == 0 ? 'selected' : '');?>>[OE] OUTRAS EMPRESAS</option>
							<option value="1" <?php echo ($_SESSION['TipoEmpresa'] == 1 ? 'selected' : '');?>>[ME] MICRO EMPRESA</option>
							<option value="2" <?php echo ($_SESSION['TipoEmpresa'] == 2 ? 'selected' : '');?>>[EPP] EMPRESA DE PEQUENO PORTE</option>
							<option value="3" <?php echo ($_SESSION['TipoEmpresa'] == 3 ? 'selected' : '');?>>[MEI] MICRO EMPREENDEDOR INDIVIDUAL</option>
						  </select>	
						</td>						  
					  </tr>
					  <?
					  }
					  ?>					  
					  
					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Representante Nome:</td>
						<td align="left" class="textonormal" colspan="3" >
							<input type="text" name="RepresentanteNome" size="45" maxlength="80" value="<?php echo $_SESSION['RepresentanteNome'] ?>" class="textonormal">
						</td>
					  </tr>
					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Representante R.G.:</td>
						<td align="left" class="textonormal" colspan="3" >
							<input type="text" name="RepresentanteRG" size="15" maxlength="15" value="<?php echo $_SESSION['RepresentanteRG'] ?>" class="textonormal">
						</td>						
					  </tr>
					  <tr>
						<td align="left" bgcolor="#DCEDF7" class="textonormal" colspan="1" style="font-weight: bold;">Órgão Emissor/UF:</td>
						<td align="left" class="textonormal" colspan="3" >
							<input type="text" name="RepresentanteOrgaoUF" size="15" maxlength="20" value="<?php echo $_SESSION['RepresentanteOrgaoUF'] ?>" class="textonormal">
						</td>					  
					  </tr>
					</table>				
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
 	        <td class="textonormal" align="right">
          	<input type="submit" value="Incluir Fornecedor" class="botao" onclick="javascript:enviar('IncluirFornecedor');">
			<input type="button" value="Limpar Dados" class="botao" onclick="javascript:enviar('Limpar')();">
			<input type="button" value="Voltar" class="botao" onclick="javascript:voltar();">
			<input type="hidden" name="Botao" value="">			
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
<!--
document.Usuario.UsuarioCodigo.focus();
//-->
</script>
