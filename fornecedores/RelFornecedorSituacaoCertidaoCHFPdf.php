<?php
#------------------------------------------------------------------------------------
# Portal da DGCO
# Programa: RelFornecedorSituacaoCertidaoCHF.php
# Autor:    Rodrigo Melo
# Data:     27/04/2011
# Objetivo: Programa de Impressão dos Fornecedores Inscritos com as seguintes informações:
#   - Razão Social/Nome; CPF/CNPJ; Endereço; Telefone(s); email(s); Validade CHF; Situação;
#
# E os seguintes filtros:
#    - Data de Balanço Expirada; CHF Válidos; Certidões em dia; Período de emissão de CHF (data inicial e data final);
#
# Tarefa do Redmine: 2244
#
# OBS.:     Tabulação 2 espaços
#------------------------------------------------------------------------------------
# Alterado: Lucas André e Daniel Augusto
# Data:		16/05/2023
# Objetivo: Tarefa Redmine 282898
# -----------------------------------------------------------------------------------------------------------------------------------------------

# Acesso ao arquivo de funções #
require_once( "funcoesFornecedores.php");

# Executa o controle de segurança #
session_cache_limiter('private_no_expire');
session_start();
Seguranca();

# Adiciona páginas no MenuAcesso #
AddMenuAcesso( '/fornecedores/RelFornecedorSituacaoCertidaoCHF.php' );

# Variáveis com o global off #
if( $_SERVER['REQUEST_METHOD'] == "GET"){

		$CHFValido      = $_GET['CHFValido'];
		$CertidoesValidas = $_GET['CertidoesValidas'];
		$DataIni        = $_GET['DataIni'];
		$DataFim        = $_GET['DataFim'];
		$DataBalanco    = $_GET['DataBalanco'];
}

# Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

# Fução exibe o Cabeçalho e o Rodapé #
CabecalhoRodape();

# Informa o Título do Relatório #
$TituloRelatorio = "Relatório de Fornecedores Por Situação do Balanço, Certidões e CHF";

# Cria o objeto PDF, o Default é formato Retrato, A4  e a medida em milímetros #
$pdf = new PDF("P","mm","A4");

# Define um apelido para o número total de páginas #
$pdf->AliasNbPages();

# Define as cores do preenchimentos que serão usados #
$pdf->SetFillColor(220,220,220);

# Adiciona uma página no documento #
$pdf->AddPage();

# Seta as fontes que serão usadas na impressão de strings #
$pdf->SetFont("Arial","",9);

# Pega os dados da Inscrição do Fornecedor #
$db   = Conexao();

#SIT_FORN.CFORTSCODI,
$sql  = "
	SELECT DISTINCT
		FORN.NFORCRRAZS,
		SIT_FORN.DFORSISITU,
		FORN.AFORCRSEQU,
		FORN.AFORCRCCGC,
		FORN.AFORCRCCPF,
		FORN.CCEPPOCODI,
		--PERGUNTAR AO USUÁRIO, SE SERÁ NECESSÁRIO COLOCAR CEP DO FORNECEDOR (SFPC.TBFORNECEDORCREDENCIADO.CCEPPOCODI)?
    	FORN.EFORCRLOGR,
    	FORN.AFORCRNUME,
    	FORN.EFORCRCOMP,
    	FORN.EFORCRBAIR,
		FORN.NFORCRCIDA,
		FORN.CFORCRESTA,
		FORN.AFORCRCDDD,
		FORN.AFORCRTELS,
    	FORN.AFORCRNFAX,
    	FORN.NFORCRMAIL,
    	CHF_FORN.DFORCHVALI,
    	FORN.DFORCRULTB,
    	FORN.DFORCRCNFC,
    	FORN.FFORCRTIPO,
    	FORN.FFORCRMEPP
	FROM
		SFPC.TBFORNECEDORCREDENCIADO FORN
			LEFT OUTER JOIN SFPC.TBFORNSITUACAO SIT_FORN
				ON  SIT_FORN.AFORCRSEQU = FORN.AFORCRSEQU
				AND SIT_FORN.DFORSISITU = ( -- última situação em vigor
					SELECT MAX(SIT_FORN_2.DFORSISITU)
					FROM SFPC.TBFORNSITUACAO SIT_FORN_2
					WHERE SIT_FORN_2.AFORCRSEQU = FORN.AFORCRSEQU
				)

			INNER JOIN SFPC.TBFORNECEDORCHF CHF_FORN
				ON CHF_FORN.AFORCRSEQU = SIT_FORN.AFORCRSEQU
";

#Filtro CHF Válido
if($CHFValido == 'S'){
	$sql  .= "		AND CHF_FORN.DFORCHVALI >= NOW() ";
}

#Filtro Período de emissão de CHF
$sql  .= "		AND CHF_FORN.DFORCHGERA BETWEEN '".$DataIni."' AND '".$DataFim."' ";

#Filtro Certidões Obrigatórias Válidas
if($CertidoesValidas == 'S'){
	$sql  .= "
				INNER JOIN SFPC.TBFORNECEDORCERTIDAO CERT_FORN
					ON CERT_FORN.AFORCRSEQU = CHF_FORN.AFORCRSEQU
					AND CERT_FORN.DFORCEVALI >= NOW()

				INNER JOIN SFPC.TBTIPOCERTIDAO TIPO_CERT
					ON TIPO_CERT.CTIPCECODI = CERT_FORN.CTIPCECODI
					AND TIPO_CERT.FTIPCEOBRI = 'S' --APENAS CERTIDÕES OBRIGATÓRIAS
	";
}

#Filtro Data da certidão de validade do balanço Válida
if($DataBalanco == 'S'){
	$sql  .= "
		WHERE
	    	FORN.DFORCRULTB >= NOW()  
	 	    OR ( FORN.DFORCRULTB IS NULL AND FORN.FFORCRMEPP in ( '1','2','3' ) )   
	--		AND FORN.DFORCRCNFC >= NOW() -- VERIFICAR COM O USUÁRIO DEVE COLOCAR A DATA DA CERTIDÃO NEGATIVA DE FALÊNCIA OU CONCORDATA COMO UM FILTRO.
	";
}

$sql  .= " ORDER BY FORN.NFORCRRAZS ";


$res  = $db->query($sql);
if( PEAR::isError($res) ){
		ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}else{
		$rows = $res->numRows();
		if( $rows == 0 ){
				$Mensagem = "Nenhuma Ocorrência Encontrada";
				$Url = "RelFornecedorSituacaoCertidaoCHF.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
				if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
				header("location: ".$Url);
				exit;
		}else{
        $pdf->Cell(192,5,"Dados da Pesquisa",1,1,"C",1);
        $pdf->Cell(42,5,"Validade de Balanço Válida",1,0,"L",1);
        $pdf->Cell(150,5,($DataBalanco == 'S')? "SIM" : "NÃO",1,1,"L",0);
        $pdf->Cell(42,5,"CHF Válido",1,0,"L",1);
        $pdf->Cell(150,5,($CHFValido == 'S')? "SIM" : "NÃO",1,1,"L",0);
        $pdf->Cell(42,5,"Certidões Obrig. Válidas",1,0,"L",1);
        $pdf->Cell(150,5,($CertidoesValidas == 'S')? "SIM" : "NÃO",1,1,"L",0);
        $pdf->Cell(42,5,"Período de emissão de CHF",1,0,'L',1);
        $pdf->Cell(20,5,$DataIni." a ","LTB",0,'L',0);
        $pdf->Cell(130,5,$DataFim,"RTB",1,'L',0);

        $pdf->ln(5);

        $pdf->Cell(192,5,"Resultado da Pesquisa",1,1,"C",1);
        $pdf->Cell(42,5,"Total de Fornecedores",1,0,"L",1);
        $pdf->Cell(150,5,$rows,1,1,"L",0);

        $pdf->ln(5);


				for( $i=0; $i< $rows; $i++ ){
						$Linha        = $res->fetchRow();
						$DataSituacao = DataBarra($Linha[1]);
						$Sequencial   = $Linha[2];
						if( $Linha[3] != "" ){
								$CPF_CNPJ = $Linha[3];
						}else{
								$CPF_CNPJ = $Linha[4];
						}
						$RazaoSocial     = $Linha[0];
						$Cep             = $Linha[5];
						$Logradouro      = $Linha[6];
						$Numero          = $Linha[7];
						$Complemento     = $Linha[8];
						$Bairro          = $Linha[9];
						$Cidade          = $Linha[10];
						$Estado          = $Linha[11];
						$DDD             = $Linha[12];
						$TelefoneForn    = $Linha[13];
						$FaxForn         = $Linha[14];
						$EmailForn       = $Linha[15];
						$DtValidadeCHF   = DataBarra($Linha[16]);
         				$DataUltBalanco  = $Linha[17];
						$DataCertidaoNeg = $Linha[18];
            			$TipoHabilitacao = $Linha[19];
            			$MicroEmpresa  	 = $Linha[20];
            			 
                       

						# Colocando o Endereço Agrupado #
						if( $Logradouro != "" ){
								if( $Numero == ""){ $Numero = "S/N"; }
								if( $Complemento != "" ){
										$Endereco = $Logradouro.", ".$Numero." ".$Compemento." - ".$Bairro." ".$Cidade."/".$Estado;
								}else{
										$Endereco = $Logradouro.", ".$Numero." - ".$Bairro." ".$Cidade."/".$Estado;
								}
						}else{
								$Endereco = "";
						}

						# Formata o CNPJ ou o CPF #
						if( strlen($CPF_CNPJ) == 14 ){
								$Tipo     = 1;
								$CPF_CNPJ = FormataCNPJ($CPF_CNPJ);
						}elseif( strlen($CPF_CNPJ) == 11 ){
								$Tipo     = 2;
								$CPF_CNPJ = FormataCPF($CPF_CNPJ);
						}
						# Pegando os Dados da Situação #
						$sqlsit  = "
							SELECT
								A.CFORTSCODI, A.EFORSIMOTI, A.DFORSIEXPI, B.EFORTSDESC
							FROM SFPC.TBFORNSITUACAO A, SFPC.TBFORNECEDORTIPOSITUACAO B
							WHERE
								A.CFORTSCODI = B.CFORTSCODI
								AND A.DFORSISITU = '".DataInvertida($DataSituacao)."'
								AND A.AFORCRSEQU = $Sequencial
							ORDER BY A.TFORSIULAT DESC --Garantir que a última modificação da data de situação mais recente esteja na 1a linha
						";

						$ressit  = $db->query($sqlsit);
						if( PEAR::isError($ressit) ){
								ExibeErroBD("$ErroPrograma\nLinha: 115\nSql: $sqlsit");
						}else{
								$Linha          = $ressit->fetchRow();
								$SituacaoCodigo = $Linha[0];
								$Motivo         = $Linha[1];
								$DataExpiracao  = DataBarra($Linha[2]);
								$DescSituacao   = $Linha[3];
								$cont++;

								$pdf->Cell(42,5,"Razão Social/Nome",1,0,"L",1);
								$pdf->Cell(150,5,$RazaoSocial,1,1,"L",0);
								$pdf->Cell(42,5,"CNPJ/CPF",1,0,"L",1);
										$pdf->Cell(150,5,$CPF_CNPJ,1,1,"L",0);

                    if ($DDD <> ""){
                      $DDD = "(".$DDD.") ";
                    }
                    $pdf->Cell(42,5,"Telefone(s)",1,0,'L',1);
                    $pdf->Cell(6,5,$DDD,"LTB",0,'L',0);
                    $pdf->Cell(144,5,$TelefoneForn,"RTB",1,'L',0);

                    $pdf->Cell(42,5,"Fax",1,0,'L',1);
                    $pdf->Cell(6,5,$DDD,"LTB",0,'L',0);
                    $pdf->Cell(144,5,$TelefoneForn,"RTB",1,'L',0);

                    $pdf->Cell(42,5,"E-mail",1,0,'L',1);
                    $pdf->Cell(150,5,$EmailForn,1,1,'L',0);


                    //Campo Endereço
                    $TamEndereco = strlen($Endereco);

                    $Linha = 5;
                    $QtdeLinha = floor( $TamEndereco / 41); // O tamanho da linha é 81, então ao dividir pela metade de uma string com 2 linhas (tamanho = 82 em diante), peguei o multiplo para calcular a quantidade de linhas.

                    if($QtdeLinha > 1) {
                      $Linha = $Linha * $QtdeLinha;
                    }



                    $pdf->Cell(42,$Linha,"Endereço",1,0,"L",1);
                    $pdf->MultiCell(150,5,$Endereco,1,"L",0);
                    //Fim do campo Endereço

					          //Tratamento para a descrição CADASTRADO HABILITADO / INABILITADO - Apenas para a situação Cadastrado (SFPC.TBFORNECEDORTIPOSITUACAO.CFORTSCODI = 1)
                    $Cadastrado = "HABILITADO";

                    if($TipoHabilitacao == "L"){
                      # Verifica a Validação das Certidões do Fornecedor #
                      $sql  = "SELECT A.CTIPCECODI, A.ETIPCEDESC, B.DFORCEVALI ";
                      $sql .= "  FROM SFPC.TBTIPOCERTIDAO A, SFPC.TBFORNECEDORCERTIDAO B ";
                      $sql .= " WHERE A.CTIPCECODI = B.CTIPCECODI AND A.FTIPCEOBRI = 'S' ";
                      $sql .= "   AND B.AFORCRSEQU = $Sequencial AND B.DFORCEVALI < NOW()";
                      $sql .= " ORDER BY B.DFORCEVALI";
                      $result = $db->query($sql);
                      if( PEAR::isError($result) ){
                          ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                      }else{
                        $Rows = $result->numRows();

                        //Solicitação do usuário: A mensagem deve ser "COM CERTIDÃO OBRIGATÓRIA EXPIRADA" tanto para as certidões obrigatórias quanto a certidão negativa de falência e concordata.
                        if( ($Rows > 0) or ($DataCertidaoNeg < prazoCertidaoNegDeFalencia()->format('Y-m-d')) ){
                          $Cadastrado = "COM CERTIDÃO OBRIGATÓRIA EXPIRADA";
                        }
                      }

                      # Verifica também se a data de balanço anual está no prazo #
                      if( $DataUltBalanco < prazoUltimoBalanço()->format('Y-m-d') ){
                        if($Cadastrado != 'HABILITADO') {
                          $Cadastrado .= ", ";
                        } else {
                          $Cadastrado = "";
                        }
                        $Cadastrado .= "COM PRAZO DE VALIDADE DO BALANÇO EXPIRADO";
                      }
                      

                      if ($DtValidadeCHF < date("Y-m-d")) {
                        if($Cadastrado != 'HABILITADO') {
                          $Cadastrado .= ", ";
                        } else {
                          $Cadastrado = "";
                        }
                        $Cadastrado .= "COM VALIDADE DE CHF EXPIRADA";
                      }
                      
                      //Tratamento da String para colocar as vírgulas e a substituir a última vírgula pela preposição 'e'.
                      if ( strrpos($Cadastrado, ",") != 0 ) {
                        $Cadastrado = substr_replace($Cadastrado, " e ", strrpos($Cadastrado, ",")) . substr($Cadastrado,(strrpos($Cadastrado, ",")+1));
                      }

                    }else{
                      $Cadastrado = "HABILITADO";
                    }
                    
                    if ( !empty($MicroEmpresa) and empty($DataUltBalanco) ) {
                    	if($Cadastrado != 'HABILITADO') {
                    		$Cadastrado .= ", ";
                    	} else {
                    		$Cadastrado = "";
                    	}
                    	$Cadastrado .= "CHF SIMPLIFICADO SEM DEMONSTRAÇÕES CONTÁBEIS";
                    }
                    
                    if( $SituacaoCodigo == 1 ){ //Apenas para a situação Cadastrado
                    	$DescSituacao = $DescSituacao." ".$Cadastrado;
                    }

                    
                    //FIM Tratamento para a descrição CADASTRADO HABILITADO / INABILITADO
					$pdf->Cell(42,5,"Data de Validade do CHF",1,0,"L",1);
					$pdf->Cell(150,5,$DtValidadeCHF,1,1,"L",0);

                    //Campo Situação
                    $TamDescSituacao = strlen($DescSituacao);

                    $Linha = 5;
                    $QtdeLinha = floor( $TamDescSituacao / 42); // O tamanho da linha é 83, então ao dividir pela metade de uma string com 2 linhas (tamanho = 84 em diante), peguei o multiplo para calcular a quantidade de linhas.

                    if($QtdeLinha > 1) {
                      $Linha = $Linha * $QtdeLinha;
                    }

                    $pdf->Cell(42,$Linha,"Situação do Fornecedor",1,0,"L",1);
                    $pdf->MultiCell(150,5,$DescSituacao,1,"L",0);

                    //Fim do campo Situação

                    if( $Motivo != "" ){
							$pdf->Cell(42,5,"Motivo",1,0,"L",1);
							$pdf->Cell(150,5,$Motivo,1,1,"L",0);
					}

					if( $DataExpiracao != "" and ( $SituacaoCodigo == 3 or $SituacaoCodigo == 6 ) ){
							$pdf->Cell(42,5,"Data Expiração",1,0,"L",1);
							$pdf->Cell(150,5,$DataExpiracao,1,1,"L",0);
					}
					$pdf->ln(5);

						}
				}
				if( $cont == 0 ){
						$Mensagem = "Nenhuma Ocorrência Encontrada";
						$Url = "RelFornecedorSituacaoCertidaoCHF.php?Mensagem=".urlencode($Mensagem)."&Mens=1&Tipo=1";
						if (!in_array($Url,$_SESSION['GetUrl'])){ $_SESSION['GetUrl'][] = $Url; }
						header("location: ".$Url);
						exit;
				}
		}
}
$db->disconnect();
$pdf->Output();
?>
