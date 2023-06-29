<?php

#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: funcoesOld.php
# Objetivo: funções obsoletas que não devem ser usadas, mas estão mantidas no sistema para compatibilidade. 
# Autor:    Ariston Cordeiro
#---------------------------
# OBS.:     Tabulação 2 espaços
#-------------------------------------------------------------------------


# Funções obsoletas 
# As funções abaixo estão obsoletas por haver melhores opções.
# Não foram deletados ou sobrescritos para compatibilidade.

/** OBSOLETO. Ano Exercicio é sempre o ano corrente, usar date("Y"). */
function AnoExercicio(){
	return date("Y");
}

/** 
 * OBSOLETO. Use strtolower2($Arg);
 * Troca Caracteres para Minúsculo 
 * */
function lower_acento($Arg) {
	//$Minusculo = array ("á","à","ã","â","ä","é","è","ê","ë","í","ì","î","ï","ó","ò","õ","ô","ö","ú","ù","û","ü","ç");
	//$Maiusculo = array ("Á","À","Ã","Â","Ä","É","È","Ê","Ë","Í","Ì","Î","Ï","Ó","Ò","Õ","Ô","Ö","Ú","Ù","Û","Ü","Ç");
	//$Arg = str_replace ($Maiusculo,$Minusculo,$Arg);
	//return $Arg;
	return strtolower2($Arg);
}

/** 
 * OBSOLETO. Use strtoupper2($Arg);
 * Troca caracteres acentuados para Maiúsculo 
 * */
function upper_acento($Arg) {
	//$Minusculo = array ("á","à","ã","â","ä","é","è","ê","ë","í","ì","î","ï","ó","ò","õ","ô","ö","ú","ù","û","ü","ç");
	//$Maiusculo = array ("Á","À","Ã","Â","Ä","É","È","Ê","Ë","Í","Ì","Î","Ï","Ó","Ò","Õ","Ô","Ö","Ú","Ù","Û","Ü","Ç");
	//$Arg = str_replace($Minusculo,$Maiusculo,$Arg);
	return strtoupper2($Arg);
	//return $Arg;
}

/** 
 * OBSOLETO. Usar in_array($item, $array);
 * Verifica se Existe no Array 
 * */
function FindArray($_Valor_,$_Array_) {
	for( $C = 0; $C < count($_Array_); $C++ ){
		if($_Array_[$C] == $_Valor_ ){ return 1; }
	}
	return 0;
}

/** 
 * OBSOLETO. Forma incorreta de limpar sessão. Para apagar a sessão, usar RedirecionaPraFora(). 
 * Para apagar variáveis específicas, deve-se apagar no próprio programa e não nesta função global
 * */
function LimparSessao(){
	if( isset($_SESSION['Virgula']) ){
			unset($_SESSION['Virgula']);
	}
	if( isset($_SESSION['TipoHabilitacao']) ){
			unset($_SESSION['TipoHabilitacao']);
	}
	if( isset($_SESSION['ErroPrograma']) ){
			unset($_SESSION['ErroPrograma']);
	}
	if( isset($_SESSION['CarregaPagina']) ){
			unset($_SESSION['CarregaPagina']);
	}
	if( isset($_SESSION['Elementos']) ){
			unset($_SESSION['Elementos']);
	}
	if( isset($_SESSION['ComissaoResp']) ){
			unset($_SESSION['ComissaoResp']);
	}
	if( isset($_SESSION['DataAnaliseDoc']) ){
			unset($_SESSION['DataAnaliseDoc']);
	}
	# Limpa Variáveis de Sessão - Formulário A #
	if( isset($_SESSION['TipoHabilitacao']) ){
			unset($_SESSION['TipoHabilitacao']);
	}
	if( isset($_SESSION['Irregularidade']) ){
			unset($_SESSION['Irregularidade']);
	}
	if( isset($_SESSION['CPF_CNPJ']) ){
			unset($_SESSION['CPF_CNPJ']);
	}
	if( isset($_SESSION['CPF']) ){
			unset($_SESSION['CPF']);
	}
	if( isset($_SESSION['CNPJ']) ){
			unset($_SESSION['CNPJ']);
	}
	if( isset($_SESSION['TipoCnpjCpf']) ){
			unset($_SESSION['TipoCnpjCpf']);
	}
	if( isset($_SESSION['Identidade']) ){
			unset($_SESSION['Identidade']);
	}
	if( isset($_SESSION['MicroEmpresa']) ){
			unset($_SESSION['MicroEmpresa']);
	}
	if( isset($_SESSION['OrgaoUF']) ){
			unset($_SESSION['OrgaoUF']);
	}

	if( isset($_SESSION['RazaoSocial']) ){
			unset($_SESSION['RazaoSocial']);
	}
	if( isset($_SESSION['NomeFantasia']) ){
			unset($_SESSION['NomeFantasia']);
	}
	if( isset($_SESSION['CEP']) ){
			unset($_SESSION['CEP']);
			unset($_SESSION['CEPInformado']);
	}
	if( isset($_SESSION['CEPAntes']) ){
			unset($_SESSION['CEPAntes']);
	}
	if( isset($_SESSION['Localidade']) ){
			unset($_SESSION['Localidade']);
	}
	if( isset($_SESSION['Logradouro']) ){
			unset($_SESSION['Logradouro']);
	}
	if( isset($_SESSION['Numero']) ){
			unset($_SESSION['Numero']);
	}
	if( isset($_SESSION['Complemento']) ){
			unset($_SESSION['Complemento']);
	}
	if( isset($_SESSION['Bairro']) ){
			unset($_SESSION['Bairro']);
	}
	if( isset($_SESSION['Cidade']) ){
			unset($_SESSION['Cidade']);
	}
	if( isset($_SESSION['UF']) ){
			unset($_SESSION['UF']);
	}
	if( isset($_SESSION['DDD']) ){
			unset($_SESSION['DDD']);
	}
	if( isset($_SESSION['Telefone']) ){
			unset($_SESSION['Telefone']);
	}
	if( isset($_SESSION['Email']) ){
			unset($_SESSION['Email']);
	}
	if( isset($_SESSION['Email2']) ){
			unset($_SESSION['Email2']);
	}
	if( isset($_SESSION['EmailVazio']) ){
			unset($_SESSION['EmailVazio']);
	}
	if( isset($_SESSION['Fax']) ){
			unset($_SESSION['Fax']);
	}
	if( isset($_SESSION['RegistroJunta']) ){
			unset($_SESSION['RegistroJunta']);
	}
	if( isset($_SESSION['DataRegistro']) ){
			unset($_SESSION['DataRegistro']);
	}
	if( isset($_SESSION['NomeContato']) ){
			unset($_SESSION['NomeContato']);
	}
	if( isset($_SESSION['CPFContato']) ){
			unset($_SESSION['CPFContato']);
	}
	if( isset($_SESSION['CargoContato']) ){
			unset($_SESSION['CargoContato']);
	}
	if( isset($_SESSION['DDDContato']) ){
			unset($_SESSION['DDDContato']);
	}
	if( isset($_SESSION['TelefoneContato']) ){
			unset($_SESSION['TelefoneContato']);
	}

	# Limpa Variáveis de Sessão - Formulário B #
	if( isset($_SESSION['InscEstadual']) ){
			unset($_SESSION['InscEstadual']);
	}
	if( isset($_SESSION['InscMercantil']) ){
			unset($_SESSION['InscMercantil']);
	}
	if( isset($_SESSION['InscricaoValida']) ){
			unset($_SESSION['InscricaoValida']);
	}
	if( isset($_SESSION['InscOMunic']) ){
			unset($_SESSION['InscOMunic']);
	}
	if( isset($_SESSION['Certidao']) ){
			unset($_SESSION['Certidao']);
	}
	if( isset($_SESSION['CertidaoObrigatoria']) ){
			unset($_SESSION['CertidaoObrigatoria']);
	}
	if( isset($_SESSION['CarregaCertOb']) ){ //Alterar Inscrito e Cad. e Gestão
			unset($_SESSION['CarregaCertOb']);
	}
	if( isset($_SESSION['DataCertidaoOb']) ){
			unset($_SESSION['DataCertidaoOb']);
	}
	if( isset($_SESSION['CheckComplementar']) ){
			unset($_SESSION['CheckComplementar']);
	}
	if( isset($_SESSION['CarregaCertComp']) ){ //Alterar Inscrito e Cad. e Gestão
			unset($_SESSION['CarregaCertComp']);
	}
	if( isset($_SESSION['DataCertidaoComp']) ){
			unset($_SESSION['DataCertidaoComp']);
	}
	if( isset($_SESSION['CertidaoComplementar']) ){
			unset($_SESSION['CertidaoComplementar']);
	}

	# Limpa Variáveis de Sessão - Formulário C #
	if( isset($_SESSION['CapSocial']) ){
			unset($_SESSION['CapSocial']);
	}
	if( isset($_SESSION['CapIntegralizado']) ){
			unset($_SESSION['CapIntegralizado']);
	}
	if( isset($_SESSION['Patrimonio']) ){
			unset($_SESSION['Patrimonio']);
	}
	if( isset($_SESSION['IndLiqCorrente']) ){
			unset($_SESSION['IndLiqCorrente']);
	}
	if( isset($_SESSION['IndLiqGeral']) ){
			unset($_SESSION['IndLiqGeral']);
	}
	if( isset($_SESSION['IndEndividamento']) ){
			unset($_SESSION['IndEndividamento']);
	}
	if( isset($_SESSION['Banco1']) ){
			unset($_SESSION['Banco1']);
	}
	if( isset($_SESSION['Agencia1']) ){
			unset($_SESSION['Agencia1']);
	}
	if( isset($_SESSION['ContaCorrente1']) ){
			unset($_SESSION['ContaCorrente1']);
	}
	if( isset($_SESSION['Banco2']) ){
			unset($_SESSION['Banco2']);
	}
	if( isset($_SESSION['Agencia2']) ){
			unset($_SESSION['Agencia2']);
	}
	if( isset($_SESSION['ContaCorrente2']) ){
			unset($_SESSION['ContaCorrente2']);
	}
	if( isset($_SESSION['DataBalanco']) ){
			unset($_SESSION['DataBalanco']);
	}
	if( isset($_SESSION['DataNegativa']) ){
			unset($_SESSION['DataNegativa']);
	}

	# Limpa Variáveis de Sessão - Formulário D #
	if( isset($_SESSION['RegistroEntidade']) ){
			unset($_SESSION['RegistroEntidade']);
	}
	if( isset($_SESSION['NomeEntidade']) ){
			unset($_SESSION['NomeEntidade']);
	}
	if( isset($_SESSION['DataVigencia']) ){
			unset($_SESSION['DataVigencia']);
	}
	if( isset($_SESSION['RegistroTecnico']) ){
			unset($_SESSION['RegistroTecnico']);
	}
	if( isset($_SESSION['TipoGrupo']) ){
			unset($_SESSION['TipoGrupo']);
	}
	if( isset($_SESSION['Grupo']) ){
			unset($_SESSION['Grupo']);
	}
	if( isset($_SESSION['Classe']) ){
			unset($_SESSION['Classe']);
	}
	if( isset($_SESSION['CarregaGrupos']) ){ // Alterar Inscrito, Cadatro e Gestão
			unset($_SESSION['CarregaGrupos']);
	}
	if( isset($_SESSION['CarregaAutorizacao']) ){ // Alterar Inscrito, Cadatro e Gestão
			unset($_SESSION['CarregaAutorizacao']);
	}
	if( isset($_SESSION['CheckAutorizacao']) ){
			unset($_SESSION['CheckAutorizacao']);
	}
	if( isset($_SESSION['AutorizacaoNome']) ){
			unset($_SESSION['AutorizacaoNome']);
	}
	if( isset($_SESSION['AutorizaNome']) ){
			unset($_SESSION['AutorizaNome']);
	}
	if( isset($_SESSION['AutorizacaoRegistro']) ){
			unset($_SESSION['AutorizacaoRegistro']);
	}
	if( isset($_SESSION['AutorizaRegistro']) ){
			unset($_SESSION['AutorizaRegistro']);
	}
	if( isset($_SESSION['AutorizacaoData']) ){
			unset($_SESSION['AutorizacaoData']);
	}
	if( isset($_SESSION['AutorizaData']) ){
			unset($_SESSION['AutorizaData']);
	}
	if( isset($_SESSION['AutoEspecifica']) ){
			unset($_SESSION['AutoEspecifica']);
	}
	if( isset($_SESSION['Cumprimento']) ){
			unset($_SESSION['Cumprimento']);
	}
	if( isset($_SESSION['CheckMateriais']) ){
			unset($_SESSION['CheckMateriais']);
	}
	if( isset($_SESSION['Materiais']) ){
			unset($_SESSION['Materiais']);
	}
	if( isset($_SESSION['CheckServicos']) ){
			unset($_SESSION['CheckServicos']);
	}
	if( isset($_SESSION['Servicos']) ){
			unset($_SESSION['Servicos']);
	}
	if( isset($_SESSION['EmailPopup']) ){
			unset($_SESSION['EmailPopup']);
	}

	# Limpa Variáveis de Cadastro e Gestão #
	if( isset($_SESSION['Cadastrado']) ){
			unset($_SESSION['Cadastrado']);
	}
	if( isset($_SESSION['ValCertidao']) ){
			unset($_SESSION['ValCertidao']);
	}
	if( isset($_SESSION['DataSituacao']) ){
			unset($_SESSION['DataSituacao']);
	}
	if( isset($_SESSION['Motivo']) ){
			unset($_SESSION['Motivo']);
	}
	if( isset($_SESSION['Situacao']) ){
			unset($_SESSION['Situacao']);
	}
	if( isset($_SESSION['SituacaoAntes']) ){
			unset($_SESSION['SituacaoAntes']);
	}
	if( isset($_SESSION['DataGeracaoCHF']) ){
			unset($_SESSION['DataGeracaoCHF']);
	}
	if( isset($_SESSION['DataGeracaoCHFAntes']) ){
			unset($_SESSION['DataGeracaoCHFAntes']);
	}
	if( isset($_SESSION['DataValidadeCHF']) ){
			unset($_SESSION['DataValidadeCHF']);
	}
	if( isset($_SESSION['DataInscricao']) ){
			unset($_SESSION['DataInscricao']);
	}
	if( isset($_SESSION['Ocorrencias']) ){
			unset($_SESSION['Ocorrencias']);
	}
	//ano de geração da unidade orçamentária (usado na ferramenta de geração de Unidade Orçamentária)
	if( isset($_SESSION['AnoGeracaoUnidadeOrcamentaria']) ){
			unset($_SESSION['AnoGeracaoUnidadeOrcamentaria']);
	}
}

/** 
 * OBSOLETO. Usar file_get_contents( $Arquivo );
 * Ler Um Arquivo Qualquer # Abreu 
 * */
function LerArquivo($Arquivo){
	echo file_get_contents( $Arquivo );
}

/** 
 * OBSOLETO. Usar funções que montam SQL fragmentam o SQL e dificultam o entendimento. Favor não usar.
 * Monta Query com Expressões Regulares - Retorna Parte da Query 
 * */
function SQL_ExpReg($Campo,$Argumento) {
	$SQLER = " ".$Campo." ILIKE '%".$Argumento."%' ";
	/*$SQLER = $Campo ." ~* '[^a-z]". $Argumento ."[^a-z]' OR ";
	$SQLER .= $Campo ." ~* '^". $Argumento ."[^a-z]' OR ";
	$SQLER .= $Campo ." ~* '[^a-z]". $Argumento ."$' OR ";
	$SQLER .= $Campo ." ~* '^". $Argumento ."$' ";*/
   return $SQLER;
}

/** OBSOLETO */
function ControlaDocumento($Numero){
	// $Numero é a concatenação do Sequencial + CNPJ/CPF + Data de Geração do Cadastro AAAAMMDD
	$Len  = strlen($Numero);
	$Soma = "";
	for( $i=0;$i<$Len-1;$i++ ){
			$Digito[$i] = substr($Numero,$i,1);
	}
	$Fator = 2;
	for( $Ind=$Len-1;$Ind>=0;$Ind-- ){
			$Produto = $Digito[$Ind] * $Fator;
			$Soma   += substr($Produto,0,1) + substr($Produto,1,1);
			if( $Fator == 2 ){
					$Fator = 1;
			}else{
	        $Fator = 2;
			}
	}
	$Resto = $Soma % 10;
	$DV10  = 10 - $Resto;
	if( $DV10 == 10 ){
			$DV10 = 0;
	}
	return $DV10;
}

/** 
 * OBSOLETO. O portal usa 4 dígitos agora, para dinheiro e quantidade. Usar converte_valor_estoques($valor) e moeda2float($valor).
 * Função que Formata Quantidade - 2 dígitos após vírgula 
 * */
function converte_quant($valor){
		$valor = str_replace(",",".",$valor);
		return number_format((float)$valor,2,",",".");
}

/** 
 * OBSOLETO. O portal usa 4 dígitos agora, para dinheiro e quantidade. Usar converte_valor_estoques($valor) e moeda2float($valor).
 * Função que Formata Valor - 2 dígitos após vírgula 
 * */
function converte_valor($valor){
		$valor = str_replace(",",".",$valor);
		return number_format((float)$valor,2,",",".");
}

/** 
 * OBSOLETO. Função muito específica. Deve-se fazer uma função mais genérica caso seja necessário esta função em outro local
 * Função que corrige os Centavos 
 * */
function funcao_centavo($valor){
    $centavos = substr("$valor", -5);
    $posicao = strpos($centavos, ".");
    if( $posicao == 0 || $posicao == 1 ){
        $centavo_valido = substr($valor, 0,  -2);
        return $centavo_valido;
    }
    return $valor;
}

/** OBSOLETO. Utilizar sprintf()
 * Função de correcao de zeros 
 * */
function exivalor($valor){
    $pos = strpos($valor, ".");
    if( $pos === false ){
        $valor_con = $valor . "00";
        return $valor_con;
    }else{
        $final = substr($valor, -2);
        if( $final == ".1" || $final == ".2" || $final == ".3" || $final == ".4" || $final == ".5" || $final == ".6" || $final == ".7" || $final == ".8" || $final == ".9"  || $final == ".0" )
            return $valor. "0";
        else
            return $valor;
    }
}

/** 
 * OBSOLETO. Esta validação de segurança deve ser evitada, pois palavras como EXECUTE, ASC são comuns em campos e strings.
 * Função que verifica se o texto tem palavras reservadas 
 * */
function ValidaTexto($Texto){
		$Texto = strtoupper2($Texto);
		$Achou = "";

		# Palavras Reservadas #
		$Especiais = array("ABORT","ALTER","ANALYZE","ASC","BEGIN","CASE","CHECKPOINT","CLOSE","CLUSTER","COMMENT","COMMIT","COPY","CREATE","DEALLOCATE","DECLARE","DELETE","DESC","DROP","END","EXECUTE","EXPLAIN","FETCH","FROM","GRANT","GROUP","IN","INSERT","JOIN","LEFT","LISTEN","LOAD","LOCK","MAX","MIN","MOVE","NOT","NOTIFY","ON","OUTER","OWNER","PREPARE","REINDEX","RESET","REVOKE","ROLLBACK","SELECT","SET","SHOW","START","SUBSTR","SUM","TABLE","THEN","TRANSACTION","TRUNCATE","UNION","UNLISTEN","UPDATE","VACUUM","WHERE",";","'");

		# Verifica se o Texto contém as palavras do array para na primeira ocorrência #
		for( $i = 0; $i < count($Especiais); $i++ ){
				$pos = strpos($Texto,$Especiais[$i]);
				if( ( !($pos === false) ) and $Achou == "" ) {
				    $Achou = "S";
				}
		}
		return $Achou;
}


/** 
 * OBSOLETO. função deve ser reescrita para usar strpos e recurção
 * Quebra a linha sem separar a palavra - Roberta 
 * */
function SeparaFrase($frase,$tamrec){
	# tamrec é a quantidade de letras que cabe na célula em cada linha #
	$tam = strlen($frase);
	if( $tam > $tamrec){
			$divisao      = ceil($tam/$tamrec);
			$parte1       = substr($frase,0,$tamrec);
			$espacoantes  = strrpos($parte1," ");
			$parte1       = trim(substr($parte1,0,$espacoantes));
			$parte2       = trim(substr($frase,$espacoantes));
			$pedacofrase  = trim($parte2);
			$qtdqespaços1 = $tamrec - $espacoantes;
			$qtdqespaços1 = str_repeat(" ", $qtdqespaços1);
			if( $divisao == 2){
					$novafrase = $parte1.$qtdqespaços1.$parte2;
			}
			if( $divisao == 3){
					$parte2       = substr($pedacofrase,0,$tamrec);
					$espacoantes  = strrpos($parte2," ");
					$parte2       = trim(substr($pedacofrase,0,$espacoantes));
					$parte3       = trim(substr($pedacofrase,$espacoantes));
					if( strlen($parte3) > $tamrec ){
							$divisao = 4;
					}else{
							$qtdqespaços2 = $tamrec - strlen($parte2);
							$qtdqespaços2 = str_repeat(" ", $qtdqespaços2);
							$novafrase    = $parte1.$qtdqespaços1.$parte2.$qtdqespaços2.$parte3;
					}
			}
			if( $divisao == 4 ){
					$pedacofraseARM = $pedacofrase;
					$parte2         = substr($pedacofrase,0,$tamrec);
					$espacoantes    = strrpos($parte2," ");
					$parte2         = trim(substr($pedacofrase,0,$espacoantes));
					$parte3         = trim(substr($pedacofrase,$espacoantes));
					$qtdqespaços2   = $tamrec - $espacoantes;
					$qtdqespaços2   = str_repeat(" ", $qtdqespaços2);
					$pedacofrase    = trim($parte3);
					$parte3         = substr($pedacofrase,0,$tamrec);
					$espacoantes    = strrpos($parte3," ");
					$parte3         = trim(substr($pedacofrase,0,$espacoantes));
					$parte4         = trim(substr($pedacofrase,$espacoantes));
          if( strlen($parte4) > $tamrec ){
          		$divisao = 5;
          		$pedacofrase = $pedacofraseARM;
          }else{
          		$qtdqespaços3 = $tamrec - strlen($parte3);
          		$qtdqespaços3 = str_repeat(" ", $qtdqespaços3);
							$novafrase    = $parte1.$qtdqespaços1.$parte2.$qtdqespaços2.$parte3.$qtdqespaços3.$parte4;
					}
			}
			if( $divisao == 5 ){
					$pedacofraseARM = $pedacofrase;
					$parte2         = substr($pedacofrase,0,$tamrec);
					$espacoantes    = strrpos($parte2," ");
					$parte2         = trim(substr($pedacofrase,0,$espacoantes));
					$parte3         = trim(substr($pedacofrase,$espacoantes));
					$qtdqespaços2   = $tamrec - $espacoantes;
					$qtdqespaços2   = str_repeat(" ", $qtdqespaços2);
					$pedacofrase    = trim($parte3);
					$parte3         = substr($pedacofrase,0,$tamrec);
					$espacoantes    = strrpos($parte3," ");
					$parte3         = trim(substr($pedacofrase,0,$espacoantes));
					$parte4         = trim(substr($pedacofrase,$espacoantes));
					$qtdqespaços3   = $tamrec - $espacoantes;
					$qtdqespaços3   = str_repeat(" ", $qtdqespaços3);
					$pedacofrase    = trim($parte4);
					$parte4         = substr($pedacofrase,0,$tamrec);
					$espacoantes    = strrpos($parte4," ");
					$parte4         = trim(substr($pedacofrase,0,$espacoantes));
					$parte5         = trim(substr($pedacofrase,$espacoantes));
					if( strlen($parte5) > $tamrec ){
							$divisao = 6;
							$pedacofrase  = $pedacofraseARM;
					}else{
							$qtdqespaços4 = $tamrec - strlen($parte4);
          		$qtdqespaços4 = str_repeat(" ", $qtdqespaços4);
							$novafrase    = $parte1.$qtdqespaços1.$parte2.$qtdqespaços2.$parte3.$qtdqespaços3.$parte4.$qtdqespaços4.$parte5;
					}
			}
			if( $divisao == 6 ){
					$pedacofraseARM = $pedacofrase;
					$parte2       = substr($pedacofrase,0,$tamrec);
					$espacoantes  = strrpos($parte2," ");
					$parte2       = trim(substr($pedacofrase,0,$espacoantes));
					$parte3       = trim(substr($pedacofrase,$espacoantes));
					$qtdqespaços2 = $tamrec - $espacoantes;
					$qtdqespaços2 = str_repeat(" ", $qtdqespaços2);
					$pedacofrase  = trim($parte3);
					$parte3       = substr($pedacofrase,0,$tamrec);
					$espacoantes  = strrpos($parte3," ");
					$parte3       = trim(substr($pedacofrase,0,$espacoantes));
					$parte4       = trim(substr($pedacofrase,$espacoantes));
					$qtdqespaços3 = $tamrec - $espacoantes;
					$qtdqespaços3 = str_repeat(" ", $qtdqespaços3);
					$pedacofrase  = trim($parte4);
					$parte4       = substr($pedacofrase,0,$tamrec);
					$espacoantes  = strrpos($parte4," ");
					$parte4       = trim(substr($pedacofrase,0,$espacoantes));
					$parte5       = trim(substr($pedacofrase,$espacoantes));
					$qtdqespaços4 = $tamrec - $espacoantes;
					$qtdqespaços4 = str_repeat(" ", $qtdqespaços4);
					$pedacofrase  = trim($parte5);
					$parte5       = substr($pedacofrase,0,$tamrec);
					$espacoantes  = strrpos($parte5," ");
					$parte5       = trim(substr($pedacofrase,0,$espacoantes));
					$parte6       = trim(substr($pedacofrase,$espacoantes));
					if( strlen($parte6) > $tamrec ){
							$divisao = 7;
							$pedacofrase = $pedacofraseARM;
					}else{
							$qtdqespaços5 = $tamrec - strlen($parte5);
          		$qtdqespaços5 = str_repeat(" ", $qtdqespaços5);
							$novafrase    = $parte1.$qtdqespaços1.$parte2.$qtdqespaços2.$parte3.$qtdqespaços3.$parte4.$qtdqespaços4.$parte5.$qtdqespaços5.$parte6;
					}
			}
			if( $divisao == 7 ){
					$pedacofraseARM = $pedacofrase;
					$parte2         = substr($pedacofrase,0,$tamrec);
					$espacoantes    = strrpos($parte2," ");
					$parte2         = trim(substr($pedacofrase,0,$espacoantes));
					$parte3         = trim(substr($pedacofrase,$espacoantes));
					$qtdqespaços2   = $tamrec - $espacoantes;
					$qtdqespaços2   = str_repeat(" ", $qtdqespaços2);
					$pedacofrase    = trim($parte3);
					$parte3         = substr($pedacofrase,0,$tamrec);
					$espacoantes    = strrpos($parte3," ");
					$parte3         = trim(substr($pedacofrase,0,$espacoantes));
					$parte4         = trim(substr($pedacofrase,$espacoantes));
					$qtdqespaços3   = $tamrec - $espacoantes;
					$qtdqespaços3   = str_repeat(" ", $qtdqespaços3);
					$pedacofrase    = trim($parte4);
					$parte4         = substr($pedacofrase,0,$tamrec);
					$espacoantes    = strrpos($parte4," ");
					$parte4         = trim(substr($pedacofrase,0,$espacoantes));
					$parte5         = trim(substr($pedacofrase,$espacoantes));
					$qtdqespaços4   = $tamrec - $espacoantes;
					$qtdqespaços4   = str_repeat(" ", $qtdqespaços4);
					$pedacofrase    = trim($parte5);
					$parte5         = substr($pedacofrase,0,$tamrec);
					$espacoantes    = strrpos($parte5," ");
					$parte5         = trim(substr($pedacofrase,0,$espacoantes));
					$parte6         = trim(substr($pedacofrase,$espacoantes));
					$qtdqespaços5   = $tamrec - $espacoantes;
					$qtdqespaços5   = str_repeat(" ", $qtdqespaços5);
					$pedacofrase    = trim($parte6);
					$parte6         = substr($pedacofrase,0,$tamrec);
					$espacoantes    = strrpos($parte6," ");
					$parte6         = trim(substr($pedacofrase,0,$espacoantes));
					$parte7         = trim(substr($pedacofrase,$espacoantes));
					if( strlen($parte7) > $tamrec ){
							$divisao = 8;
							$pedacofrase = $pedacofraseARM;
					}else{
							$qtdqespaços6 = $tamrec - strlen($parte6);
          		$qtdqespaços6 = str_repeat(" ", $qtdqespaços6);
							$novafrase = $parte1.$qtdqespaços1.$parte2.$qtdqespaços2.$parte3.$qtdqespaços3.$parte4.$qtdqespaços4.$parte5.$qtdqespaços5.$parte6.$qtdqespaços6.$parte7;
					}
			}
			if( $divisao == 8 ){
					$parte2       = substr($pedacofrase,0,$tamrec);
					$espacoantes  = strrpos($parte2," ");
					$parte2       = trim(substr($pedacofrase,0,$espacoantes));
					$parte3       = trim(substr($pedacofrase,$espacoantes));
					$qtdqespaços2 = $tamrec - $espacoantes;
					$qtdqespaços2 = str_repeat(" ", $qtdqespaços2);
					$pedacofrase  = trim($parte3);
					$parte3       = substr($pedacofrase,0,$tamrec);
					$espacoantes  = strrpos($parte3," ");
					$parte3       = trim(substr($pedacofrase,0,$espacoantes));
					$parte4       = trim(substr($pedacofrase,$espacoantes));
					$qtdqespaços3 = $tamrec - $espacoantes;
					$qtdqespaços3 = str_repeat(" ", $qtdqespaços3);
					$pedacofrase  = trim($parte4);
					$parte4       = substr($pedacofrase,0,$tamrec);
					$espacoantes  = strrpos($parte4," ");
					$parte4       = trim(substr($pedacofrase,0,$espacoantes));
					$parte5       = trim(substr($pedacofrase,$espacoantes));
					$qtdqespaços4 = $tamrec - $espacoantes;
					$qtdqespaços4 = str_repeat(" ", $qtdqespaços4);
					$pedacofrase  = trim($parte5);
					$parte5       = substr($pedacofrase,0,$tamrec);
					$espacoantes  = strrpos($parte5," ");
					$parte5       = trim(substr($pedacofrase,0,$espacoantes));
					$parte6       = trim(substr($pedacofrase,$espacoantes));
					$qtdqespaços5 = $tamrec - $espacoantes;
					$qtdqespaços5 = str_repeat(" ", $qtdqespaços5);
					$pedacofrase  = trim($parte6);
					$parte6       = substr($pedacofrase,0,$tamrec);
					$espacoantes  = strrpos($parte6," ");
					$parte6       = trim(substr($pedacofrase,0,$espacoantes));
					$parte7       = trim(substr($pedacofrase,$espacoantes));
					$qtdqespaços6 = $tamrec - $espacoantes;
					$qtdqespaços6 = str_repeat(" ", $qtdqespaços6);
					$pedacofrase  = trim($parte7);
					$parte7       = substr($pedacofrase,0,$tamrec);
					$espacoantes  = strrpos($parte7," ");
					$parte7       = trim(substr($pedacofrase,0,$espacoantes));
					$parte8       = trim(substr($pedacofrase,$espacoantes));
					$qtdqespaços7 = $tamrec - strlen($parte7);
					$qtdqespaços7 = str_repeat(" ", $qtdqespaços6);
					$novafrase    = $parte1.$qtdqespaços1.$parte2.$qtdqespaços2.$parte3.$qtdqespaços3.$parte4.$qtdqespaços4.$parte5.$qtdqespaços5.$parte6.$qtdqespaços6.$parte7.$qtdqespaços7." ".$parte8;
			}
	}else{
			$novafrase = $frase;
	}
	return $novafrase;
}


/** 
 * OBSOLETO. usar DataHora->somar($dia, $mes=0, $ano=0, $hora=0, $minuto=0, $segundo=0)
 * Somar X Dias da Data  - Internet 
 * */
function SomaData($dias,$data){ //formato DD/MM/AAAA
	if( preg_match ("/[0-9]{1,2})/([0-9]{1,2})/([0-9]{4}/", $data,$sep) ){
		  $dia = $sep[1];
		  $mes = $sep[2];
		  $ano = $sep[3];
	}else{
			echo "<font class=textonormal>Formato Inválido de Data - $data</font><br>";
	}
	$i = $dias;
	for( $i = 0;$i<$dias;$i++ ){
	   if( $mes == "01" || $mes == "03" || $mes == "05" || $mes == "07" || $mes == "08" || $mes == "10" || $mes == "12" ){
		   	 if( $mes == 12 && $dia == 31 ){
			       $mes = 01;
			       $ano++;
			       $dia = 00;
		     }
		     if( $dia == 31 && $mes != 12 ){
			       $mes++;
			       $dia = 00;
			   }
	   }
	   if( $mes == "04" || $mes == "06" || $mes == "09" ||$mes == "11" ){
		   	 if( $dia == 30 ){
			       $dia =  00;
			       $mes++;
		     }
	   }
	   if( $mes == "02" ){
	   	 if( $ano % 4 == 0 && $ano % 100 != 0 ){//ano bissexto
		       if( $dia == 29 ){
			         $dia = 00;
			         $mes++;
		       }
	     }else{
		       if( $dia == 28 ){
			         $dia = 00;
			         $mes++;
		       }
	     }
	   }
	   $dia++;
	}

	# Confirma Saída de 2 dígitos #
	if(strlen($dia) == 1){$dia = "0".$dia;};
	if(strlen($mes) == 1){$mes = "0".$mes;};

	# Monta Saída #
	$nova_data = $dia."/".$mes."/".$ano;

	# Retorno da função #
	return $nova_data;
}


/** 
 * OBSOLETO. usar DataHora->subtrair($dia, $mes=0, $ano=0, $hora=0, $minuto=0, $segundo=0)
 * Subtrair X Dias da Data - Internet
 * */
function SubtraiData($dias,$datahoje){
  if( preg_match ("/^\d{1,2}\/\d{1,2}\/\d{4}$/", $datahoje, $sep) ){
		  $dia = $sep[1];
		  $mes = $sep[2];
		  $ano = $sep[3];
  }else{
    	echo "<b>Formato Inválido de Data - $datahoje</b><br>";
  }

  # Meses que o antecessor tem 31 dias #
  if( $mes == "01" || $mes == "02" || $mes == "04" || $mes == "06" || $mes == "08" || $mes == "09" || $mes == "11" ){
	    for( $cont = $dias ; $cont > 0 ; $cont-- ){
		    	$dia--;
		      # Volta o dia para dia 31 #
		      if( $dia == 00 ){
				      $dia = 31;
				      # Diminui um mês se o dia zerou #
				      $mes = $mes -1;
				      # Se for Janeiro e subtrair 1, vai para o ano anterior no mês de dezembro #
				      if( $mes == 00 ){
				        $mes = 12;
				        $ano = $ano - 1;
				      }
		      }
	    }
  }

 	# Meses que o antecessor tem 30 dias #
	if($mes == "05" || $mes == "07" || $mes == "10" || $mes == "12" ){
	    for( $cont = $dias ; $cont > 0 ; $cont-- ){
		    	$dia--;
		      # Volta o dia para dia 30 #
		      if( $dia == 00 ){
				      $dia = 30;
				      # Diminui um mês se o dia zerou #
				      $mes = $mes -1;
		      }
	    }
  }

  # Mês que o antecessor é fevereiro #
  if( $ano % 4 == 0 && $ano%100 != 0 ){ // se for bissexto
	    if( $mes == "03" ){
		      for( $cont = $dias ; $cont > 0 ; $cont-- ){
			    	  $dia--;
			        # Volta o dia para dia 30 #
			        if( $dia == 00 ){
					        $dia = 29;
					        # Diminui um mês se o dia zerou #
					        $mes = $mes -1;
			        }
		      }
	    }
  }else{ // se não for bissexto
	    if( $mes == "03" ){
		      for( $cont = $dias ; $cont > 0 ; $cont-- ){
			        $dia--;
			        # Volta o dia para dia 30 #
			        if( $dia == 00 ){
			          $dia = 28;
			          # Diminui um mês se o dia zerou #
			          $mes = $mes -1;
			        }
		      }
	    }
  }

  # Confirma Saída de 2 dígitos #
  if(strlen($dia) == 1){$dia = "0".$dia;}
  if(strlen($mes) == 1){$mes = "0".$mes;}

	# Monta Saída #
  $nova_data = $dia."/".$mes."/".$ano ;

  return $nova_data;
}

/** OBSOLETO. Usar:
 *  $data= new DataHora('aaaa-mm-dd');
 *  $data->formata('d/m/Y');
 * Monta a data com Barras(dd/mm/aaaa) quando a Data vem invertida(aaaa-mm-dd) - Roberta
 *  */
function DataBarra($Data){
	$DataBarra = "";
	if(!is_null($Data) and $Data!=""){
		$DataBarra = substr($Data,8,2)."/".substr($Data,5,2)."/".substr($Data,0,4);
	}
	return $DataBarra;
}

/** 
 * OBSOLETO. usar:
 *  $data= new DataHora('aaaa-mm-dd hh:ii:ss');
 *  $data->formata('H:i:s');
 * Monta a hora de Data que vem invertida(aaaa-mm-dd) 
 * */
function Hora($Data){
	$DataBarra = "";
	if(!is_null($Data) and $Data!=""){
		$DataBarra = substr($Data,11,8);
	}
	return $DataBarra;
}

/** 
 * OBSOLETO. Usar:
 *  $data= new DataHora('dd/mm/aaaa');
 *  $data->formata('Y-m-d');
 * Monta a data Invertida(aaaa-mm-dd) quando a Data vem dd/mm/aaaa - Roberta 
 * */
function DataInvertida($Data){
	$DataInvertida = substr($Data,6,4)."-".substr($Data,3,2)."-".substr($Data,0,2);
	return $DataInvertida;
}

/**
 * OBSOLETO. Usar:
 *  $data= new DataHora();
 *  $data->formata('Y-m-d H:i:s');
 *  Monta a data ATUAL Invertida(aaaa-mm-dd hh:mm:ss) - Roberta */
function DataAtual(){
	return date("Y-m-d H:i:s");
}

/** 
 * OBSOLETO. Usar:
 *  $data= new DataHora('aaaa-mm-dd');
 *  $data->formata('Y-M-d');
 * Formata a Data para o Padrão Oracle 
 * */
function DataOracle($Data){
	$Dia   = substr($Data,8,2);
	$Mes   = substr($Data,5,2);
	$Ano   = substr($Data,0,4);
	$Meses = Array("JAN","FEB","MAR","APR","MAY","JUN","JUL","AUG","SEP","OCT","NOV","DEC");
	$Data  = $Dia."-".$Meses[$Mes-1]."-".$Ano;
	return $Data;
}


/** 
 * OBSOLETO. Usar:
 *  $data= new DataHora('aaaa-MMM-dd');
 *  $data->formata('Y-m-d');
 * Formata a Data do Padrão Oracle para o Padrão Postgree 
 * */
function DataPost($Data){
	$Dia      = substr($Data,0,2);
	$Mes      = substr($Data,3,3);
	$Ano      = substr($Data,7,2);
	$MesesExt = Array("JAN","FEB","MAR","APR","MAY","JUN","JUL","AUG","SEP","OCT","NOV","DEC");
	$MesesNum = Array("01","02","03","04","05","06","07","08","09","10","11","12");
	$Mes = str_replace($MesesExt,$MesesNum,$Mes);
	$Data  = "20".$Ano."-".$Mes."-".$Dia;
	return $Data;
}

/** 
 * OBSOLETO. Usar:
 *  $data= new DataHora('d/m/a');
 *  $data->formata('d/m/Y');
 * Função para formatação de datas com barras DD/MM/AAAA # Abreu 
 * */
function FormataData($Data){
	if( SoNumerosBarra($Data) ){
			$Data     = explode("/",$Data);
			$NovaData = sprintf("%02d/%02d/%d",$Data[0],$Data[1],$Data[2]);
	}else{
			$NovaData = $Data;
	}
	return $NovaData;
}

/** 
 * OBSOLETO. Usar:
 *  "Recife, ".DataHora->formata('d').' de '.DataHora->formata('F').' de '.DataHora->formata('Y');  
 * Data por Extenso 
 * */
function DataExtensoRecife($Data){
	$Data = explode("/",$Data);
	$mes  = Array("Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro");
	$DataRecife = "Recife, ".$Data[0]." de ".$mes[$Data[1]-1]." de ".$Data[2];
	return $DataRecife;
}

/** 
 * OBSOLETO. Usar header() com caminho relativo
 * Redireciona para as Máquinas com Oracle - Roberta
 * */
function Redireciona($Caminho){
		header("location: ".$GLOBALS["DNS_SISTEMA"].$GLOBALS["PASTA_ORACLE"]."$Caminho");
	exit;
}

/** 
 * OBSOLETO. Usar header() com caminho relativo
 * Redireciona para as Máquinas sem Oracle - Roberta 
 * */
function RedirecionaPost($Caminho){
  	header("location: ".$GLOBALS["DNS_SISTEMA"]."$Caminho");
	exit;
}

/** 
 * OBSOLETO. Função específica demais. Deveria ser implementada localmente.
 * Números equivalentes do Alfabeto 
 * */
function NumAlfabeto($Letra) {
	$Alfabeto = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	return strpos($Alfabeto,$Letra)+1;
}

/** 
 * OBSOLETO. Função específica demais. Deveria ser implementada localmente.
 * Letras equivalentes ao Número # Abreu 
 * */
function AlfaNumero($Num) {
	$Alfabeto = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	return $Alfabeto[$Num-1];
}


/** 
 * OBSOLETO. Forma incorreta de limpar sessão. Para apagar a sessão, usar RedirecionaPraFora(). 
 * Para apagar variáveis específicas, deve-se apagar no próprio programa e não nesta função global
 * Função para apagar dados da sessão das páginas de Nota Fiscal - Álvaro 
 * */
function LimparSessaoNotaFiscal() {
	unset($_SESSION['TipoUsuario']);
	unset($_SESSION['CentroCusto']);
	unset($_SESSION['OrgaoUsuario']);
	unset($_SESSION['Localizacao']);
	unset($_SESSION['NumeroNota']);
	unset($_SESSION['SerieNota']);
	unset($_SESSION['DataEmissao']);
	unset($_SESSION['CNPJFornecedor']);
	unset($_SESSION['CPFFornecedor']);
	unset($_SESSION['RazaoSocial']);
	unset($_SESSION['DataEntrada']);
	unset($_SESSION['AnoEmpenho']);
	unset($_SESSION['OrgaoEmpenho']);
	unset($_SESSION['UnidadeEmpenho']);
	unset($_SESSION['SequencialEmpenho']);
	unset($_SESSION['ParcelaEmpenho']);
	unset($_SESSION['ValorNota']);

	# Dados do detalhe da nota fiscal #
	unset($_SESSION['CheckItem']);
	unset($_SESSION['Material']);
	unset($_SESSION['DescMaterial']);
	unset($_SESSION['Unidade']);
	unset($_SESSION['Quantidade']);
	unset($_SESSION['ValorUnitario']);
	unset($_SESSION['TipoItem']);
	unset($_SESSION['ValorTotal']);
}

/** 
 * OBSOLETO. Esta função é usada para validar datas. Usar ValidaData($Data)
 * So Numeros e Barra # Abreu 
 * */
function SoNumerosBarra($Numero) {
	return (preg_match( "/^\d+\/\d+\/\d+$/", trim($Numero) ));
}

/** 
 * OBSOLETO. Usado para validar moedas. Usar validaMonetario()
 * So Numeros e Vírgula # Abreu
 * */
function SoNumVirg($Numero) {
	return (preg_match( "/^((\d+)|(\d+,\d+))$/", trim($Numero) ));
}

/** 
 * OBSOLETO. Usado para validar moedas. Usar validaMonetario()
 * So Numeros e Vírgula # Abreu
 *  */
function SoNumVirgPonto($Numero) {
	return (preg_match( "/^((\d+)|(\d+,\d+)||(\d+.\d+))$/", trim($Numero) ));
}


/** 
 * OBSOLETO. Usado para validar moedas. Usar validaMonetario()
 * Verifica as casas decimais 
 * */
function Decimal($Numero){ //Formato do $Numero com Vírgula
	$Numero  = str_replace(",",".",$Numero);
	$PosVir  = strpos($Numero,".");
	if( $PosVir ){
			$Decimal = substr($Numero,$PosVir+1);
			$Inteira = substr($Numero,0,$PosVir);
			if( strlen($Decimal) > 4 or $Inteira == ""  ){
					$Numero = false;
			}else{
					$Formatado = sprintf("%01.2f", $Numero);
					$Numero    = str_replace(".",",",$Formatado);
			}
	}else{
			$Formatado = sprintf("%01.2f", $Numero);
			$Numero    = str_replace(".",",",$Formatado);
	}
	return $Numero;
}

/** 
 * OBSOLETO. Usado para validar moedas. Usar validaMonetario()
 * Verifica as casas decimais para valor
 *  */
function DecimalValor($Numero){ //Formato do $Numero com Vírgula
	$Numero  = str_replace(",",".",$Numero);
	$PosVir  = strpos($Numero,".");
	if( $PosVir ){
			$Decimal = substr($Numero,$PosVir+1);
			$Inteira = substr($Numero,0,$PosVir);
			if( strlen($Decimal) > 4 or $Inteira == ""  ){
					$Numero = false;
			}else{
					$Formatado = sprintf("%01.4f", $Numero);
					$Numero    = str_replace(".",",",$Formatado);
			}
	}else{
			$Formatado = sprintf("%01.4f", $Numero);
			$Numero    = str_replace(".",",",$Formatado);
	}
	return $Numero;
}


/** 
 * OBSOLETO. Função muito específica para ser global.
 * Pega todos os números da string e retorna todos esses números concatenados */
function trimNumeros($str){
	$result="";
	for($itr=0 ; $itr<strlen($str) ; $itr++){
		if(is_numeric($str[$itr])){
			$result.=$str[$itr];
		}
	}
	return $result;
}

?>