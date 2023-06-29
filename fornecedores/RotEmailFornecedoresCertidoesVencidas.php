<?php

#---------------------------------------------------------------------------
# Portal da DGCO
# Programa: RotEmailFornecedoresCertidoesVencidas.php
# Objetivo: Rotina para enviar email para todos fornecedores com certidões obrigatórias fora do prazo de validade
# Autor:    Ariston Cordeiro
# Data:     13/09/2010
#---------------------------------------------------------------------------
# Alterado: Rodrigo Melo
# Data:     24/05/2011	- Tarefa Redmine: 2209 - Mandar envio de emails para os 2 emails do fornecedor
#---------------------------------------------------------------------------
# Alterado: Lucas Baracho
# Data:     25/07/2018
# Objetivo: Tarefa Redmine 191814
#---------------------------------------------------------------------------
# OBS.:     Tabulação 2 espaços
#---------------------------------------------------------------------------
#Alterado : Osmar Celestino
# Data: 29/03/2021
# Objetivo: CR #244966  Rotina de Envio de E-mail - Correção sql e melhoria do texto
#---------------------------------------------------------------------------
# Alterado : Osmar Celestino
# Data: 29/07/2021
# Objetivo: CR #250280
#---------------------------------------------------------------------------





include "../funcoes.php";

function mascara_cnpjcpf($valor){
    $cnpjFormatado = strripos($valor, "-");
    if($cnpjFormatado == true){
        return $valor;
    }
    if(strlen($valor) == 14){
        $mascara = "##.###.###/####-##";
        for($i =0; $i <= strlen($mascara); $i++){
            if($mascara[$i] == "#"){
                if(isset($valor[$k])){
                $maskared .= $valor[$k++];
                }
            }else{
                $maskared .= $mascara[$i];
            }
        }
        return $maskared;
	}
}	
if(strlen($valor) == 11){
	$mascara = "###.###.###-##";
	for($i =0; $i <= strlen($mascara); $i++){
		if($mascara[$i] == "#"){
			if(isset($valor[$k])){
			$maskared .= $valor[$k++];
			}
		}else{
			$maskared .= $mascara[$i];
		}
	}
	// var_dump($maskared);
	return $maskared;
}


$acao = null;
if( $_SERVER['REQUEST_METHOD'] == "GET"){
	$acao = $_GET['acao']; //ação tem que ser igual a "enviar" para rotina ser executada
}
$arquivoErro="RotEmailFornecedoresCertidoesValidas.php";
session_start();
if($acao=="enviar"){
	$db  = Conexao();
	# sql para pegar todos fornecedores (de situação cadastrados tipo licitação) com alguma certidão obrigatória vencida
	
	$sql = "	SELECT
        DISTINCT F.AFORCRSEQU, F.NFORCRRAZS, F.NFORCRMAIL, F.NFORCRMAI2, F.DFORCRCONT
FROM
        SFPC.TBFORNECEDORCREDENCIADO F,
        SFPC.TBFORNECEDORCERTIDAO FC,
        SFPC.TBTIPOCERTIDAO TC,
        SFPC.TBFORNSITUACAO FS
WHERE
        -- LIGANDO AS TABELAS
    TC.CTIPCECODI = FC.CTIPCECODI
    AND FC.AFORCRSEQU = F.AFORCRSEQU
    AND FC.CTIPCECODI = TC.CTIPCECODI
    AND F.AFORCRSEQU = FS.AFORCRSEQU
    -- CONDICOES
            -- situação atual do fornecedor
    AND FS.DFORSISITU = (
            SELECT MAX(FS2.DFORSISITU)
            FROM SFPC.TBFORNSITUACAO FS2
            WHERE FS2.AFORCRSEQU = F.AFORCRSEQU
    )
    AND F.FFORCRTIPO = 'L' --fornecedor de licitção
    AND CFORTSCODI = 1 -- fornecedor na situação cadastrado
    AND TC.FTIPCEOBRI = 'S' --certidão é obrigatória
    AND FC.DFORCEVALI < current_date -- certidão está vencida
    AND ((F.nforcrmail is not null) or (F.nforcrmail is not null))
    AND EXTRACT(YEAR FROM F.DFORCRCONT) >= (EXTRACT(YEAR FROM CURRENT_DATE) - 5)
ORDER BY
    F.AFORCRSEQU "
	;

	$res   = executarSQL($db, $sql);
	if( PEAR::isError($res) ){
			$db->disconnect;
			echo "ERRO: mensagem enviada ao analista";
			EmailErroSQL("Erro de SQL em ".$arquivoErro, __FILE__, __LINE__, "Erro em SQL para pegar fornecedores com certidões vencidas", $sql, $res);
			exit(0);
	}

	$noFornecedores = $res->numRows();
	//echo "noFornecedores = ".$noFornecedores."<br/>";

	$textoEmail="";
	$teste = true;

	for( $i=0;$i<$noFornecedores;$i++ ){
		//echo "i = ".$i."<br/>";
		if($i>0){
			$teste = false;
		}

		$Linha 	= $res->fetchRow();
		$codFornecedor = $Linha[0];
		$nomeFornecedor = $Linha[1];
		$emailFornecedor = $Linha[2];
		$emailFornecedor2 = $Linha[3];
		//$cpfFornecedor = $Linha[4];
		//$cnpjFornecedor = $linha [5];


		//echo "	emailFornecedor = ".$emailFornecedor."<br/>";

		if(!is_null($emailFornecedor)){ // não mandar email para fornecedores sem email
			//echo "		tem email!<br/>";

			$textoEmail= "Para: ".$nomeFornecedor."\n\nVerificamos em nosso sistema que a(s) sua(s) certidão(ões) abaixo descrita(s) está(ão) com prazo(s) de validade vencido(s):\n\n";

				$sql = "SELECT TC.ETIPCEDESC, FC.DFORCEVALI, F.NFORCRRAZS, AFORCRCCPF, AFORCRCCGC
					FROM
					SFPC.TBFORNECEDORCERTIDAO FC, SFPC.TBTIPOCERTIDAO TC, SFPC.TBFORNECEDORCREDENCIADO F
					WHERE
						FC.AFORCRSEQU = ".$codFornecedor."
						AND TC.CTIPCECODI = FC.CTIPCECODI
						AND TC.FTIPCEOBRI = 'S' --certidão é obrigatória
						AND FC.DFORCEVALI < current_date -- certidão está vencida
						AND FC.AFORCRSEQU = F.AFORCRSEQU -- ligação entre as tabelas
						";
			$resCertidoes = $db->query($sql);

			if( PEAR::isError($resCertidoes) ){
					$db->disconnect;
					echo "ERRO: mensagem enviada ao analista";
					EmailErroSQL("Erro de SQL em ".$arquivoErro, __FILE__, __LINE__, "Erro em SQL para pegar certidões vencidas de um fornecedor", $sql, $resCertidoes);
					exit(0);
			}


			$noCertidoesVencidas = $resCertidoes->numRows();
			$exibir = '';
			$exibir2 = '';
			$nome = '';
			$cpf = '';
			$cnpj = '';
			$textoEmail='';
			for( $i2=0;$i2<$noCertidoesVencidas;$i2++ ){
				$LinhaCertidoes = $resCertidoes->fetchRow();
				$nomeCertidao  = $LinhaCertidoes[0]; 
				$validadeCertidao = DataBarra($LinhaCertidoes[1]);
				$nome = $LinhaCertidoes[2];
				$cpf = mascara_cnpjcpf($LinhaCertidoes[3]);
				$cnpj = mascara_cnpjcpf($LinhaCertidoes[4]);
				$exibir .= "CERTIDÃO: ".$nomeCertidao.  ", VALIDADE: ".$validadeCertidao.";"."<br>";
			//	$exibir2 .= "\n" .$validadeCertidao."<br>";
				if (empty($cpf)){
							//$nomeC = .$nomeCertidao."\n";
					        //$validadeC = .$validadeCertidao."\n";
						//começa o dodigo da mensagem
						$textoEmail = " 
						<!DOCTYPE html>
							<html lang=\"en\">
							<head>
								<meta charset=\"UTF-8\">
								<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
								<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
								<title>Document</title>
								<style>
									div{
										width: 800px;
										align-items: center;
									}
									table{
										align-items: center;
										text-align:justify;
										color: #0B62C3;
										width: 960px;
										padding-right: 5em;
									}
									img{
										width: 920px;
									}
									td{
										
									}
							
									#content{
										width: 800px;
									}
									#bordatd{
										border: 1px solid grey;
									}
									#head{
										width: 900px;
										height: 65px;
										align-items: center;
										background-image: linear-gradient(to right, #0B62C3 , #c8d9f0e3);
										color: #ffffff;
									}
									#port{
										align-items: center;
										color: #ffffff;
										height: 5px;
										font-size: 2.3em;
									}
									body{
										font-family: \"Times New Roman\", Times, serif;
										}
										.largo{
										
											font-size: 13px;
	
										}
										.titulotabela{
											font-size: 15px;
											font-weight: bolder;
										}
								</style>
							</head>
							<body>
								
								<div>
									<div id=\"head\">
										<ul id=\"port\">PORTAL DE COMPRAS</ul>
										<ul>Prefeitura do Recife</ul>
									</div>
									<container id=\"content\">
										<table>
											<thead>
												<tr>
													<td>
														<br>
														Assunto: Aviso do vencimento de certidão de fornecedores
														<br><br>
														Informamos que as certidões abaixo estão vencidas: 
														<br><br>
													</td>
												</tr>
											</thead>
										<tr >
												<td id=\"bordatd\" id=\"titulotabela\">
													<strong>Nome </strong>
												</td>
												<td id=\"bordatd\">
													$nome 
												</td>
										</tr>
										<tr >
												<td id=\"bordatd\" id=\"titulotabela\">
													<strong>CPF/CNPJ</strong>
												</td>
												<td id=\"bordatd\">
													$cnpj
												</td>
										</tr>
										<tr >
											<tr >
												<td colspan=\"2\" id=\"bordatd\" id=\"titulotabela\">
													<strong>Certidões </strong>
												</td>
											</tr>
												<td colspan=\"2\" id=\"bordatd\"class=\"largo\" >
												$exibir
												</td>
											
											<tr>
											<td colspan=\"2\">
												<br><br>
												Favor providenciar a atualização das referidas certidões.
												<br><br><br>
												<hr size=\"1\" style=\"border:1px dashed #0B62C3;\">
											</td>
										</tr>
							
										<tr>
											<td colspan=\"2\">
												Em caso de dúvida entre em contato com a equipe do 	SICREF através do
											e-mail sicref@recife.pe.gov.br. ou do telefone 3355-8285.
												<hr size=\"1\" style=\"border:1px dashed #0B62C3;\">
											</td>
										</tr>
										</table>
									</container>
								</div>
							</body>
							
							</html>";
					

				}
				else{
					$textoEmail = " 
					<!DOCTYPE html>
						<html lang=\"en\">
						<head>
							<meta charset=\"UTF-8\">
							<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
							<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
							<title>Document</title>
							<style>
								div{
									width: 800px;
									align-items: center;
								}
								table{
									align-items: center;
									text-align:justify;
									color: #0B62C3;
									width: 1200px;
									padding-right: 5em;
								}
								img{
									width: 900px;
								}
								td{
									width: 60%;
								}
						
								#content{
									width: 800px;
								}
								#bordatd{
									border: 1px solid grey;
								}
								#head{
									width: 900px;
									height: 65px;
									align-items: center;
									background-image: linear-gradient(to right, #0B62C3 , #c8d9f0e3);
									color: #ffffff;
								}
								#port{
									align-items: center;
									color: #ffffff;
									height: 5px;
									font-size: 2.3em;
								}
								body{
									font-family: \"Times New Roman\", Times, serif;
									}
									.largo{
									
										font-size: 15px;

									}
									.titulotabela{
										font-size: 15px;
										font-weight: bolder;
									}
							</style>
						</head>
						<body>
							
							<div>
								<div id=\"head\">
									<ul id=\"port\">PORTAL DE COMPRAS</ul>
									<ul>Prefeitura do Recife</ul>
								</div>
								<container id=\"content\">
									<table>
										<thead>
											<tr>
												<td>
													<br>
													Assunto: Aviso do vencimento de certidão de fornecedores
													<br><br>
													Informamos que as certidões abaixo estão vencidas: 
													<br><br>
												</td>
											</tr>
										</thead>
									<tr >
											<td id=\"bordatd\" id=\"titulotabela\">
												<strong>Nome </strong>
											</td>
											<td id=\"bordatd\">
												$nome 
											</td>
									</tr>
									<tr >
											<td id=\"bordatd\" id=\"titulotabela\">
												<strong>CPF/CNPJ</strong>
											</td>
											<td id=\"bordatd\">
												$cpf
											</td>
									</tr>
									<tr >
										<tr >
											<td colspan=\"2\" id=\"bordatd\" id=\"titulotabela\">
												<strong>Certidões </strong>
											</td>
										</tr>
											<td colspan=\"2\" id=\"bordatd\"class=\"largo\" >
											$exibir
											</td>
										<tr>
										<td colspan=\"2\">
											<br><br>
											Favor providenciar a atualização das referidas certidões.
											<br><br><br>
											<hr size=\"1\" style=\"border:1px dashed #0B62C3;\">
										</td>
									</tr>
						
									<tr>
										<td colspan=\"2\">
											Em caso de dúvida entre em contato com a equipe do 	SICREF através do
										e-mail sicref@recife.pe.gov.br. ou do telefone 3355-8285.
											<hr size=\"1\" style=\"border:1px dashed #0B62C3;\">
										</td>
									</tr>
									</table>
								</container>
							</div>
						</body>
						
						</html>";


				}

				
			}
						
			
		
			# e-mail destino, assunto (subject do email), mensagem, e-mail remetente, arquivo atachado (opcional), nome do arquivo atachado (opcional)
			echo $emailFornecedor;
			echo "<br><br>";
			echo "Assunto: Não Responda  - Aviso Vencimento de Certidões";
			echo "<br><br>";
			echo $textoEmail;
			echo "<br><br>";
			echo "-----------------------------------------------------";
			$sicref = 'Sicref@recife.pe.gov.br';
				//if($teste==true){
					EnviaEmailHTML($emailFornecedor, " NÃO RESPONDA - Aviso de Vencimento de Certidões",$textoEmail, $sicref);
				if($emailFornecedor2 != "" and !is_null($emailFornecedor2) ){
				EnviaEmailHTML($emailFornecedor2, " NÃO RESPONDA  - Aviso de Vencimento de Certidões",$textoEmail, $sicref);
				}
			//}
		}

	}
			EnviaEmailSistema("Rotina RotEmailFornecedoresCertidoesVencidas.php executada", "Rotina de envio de email a fornecedores com certidões vencidas foi executada com sucesso.");
			echo "Executado com sucesso.";
			$db->disconnect();
}else{
	# mensagem para avisar que comando 'acao' não foi recebido
	echo "ERRO: comando requerido inválido";
}
?>
