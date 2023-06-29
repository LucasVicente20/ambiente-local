<?php
# -------------------------------------------------------------------------
# Portal de compras
# Programa: ConsEnvioDocumento.php
# Autor:    João Madson
# Data:     22/03/2021
# Objetivo: CR #245334
# -------------------------------------------------------------------------
# Autor:    João Madson
# Data:     06/04/2021
# Objetivo: CR #245672
# -------------------------------------------------------------------------
# Autor:    Lucas Vicente
# Data:     06/05/2022
# Objetivo: CR #255187
# -------------------------------------------------------------------------
# Autor:    Osmar Celestino
# Data:     22/09/2022
# Objetivo: CR #269079
# -------------------------------------------------------------------------

# Acesso ao arquivo de funções #
require_once("funcoesCompras.php");
$path = $GLOBALS["CAMINHO_UPLOADS"]."temp/";
# Executa o controle de segurança #
session_start();
Seguranca();
# Adiciona páginas no MenuAcesso #
AddMenuAcesso ('/estoques/CadItemDetalhe.php');
AddMenuAcesso ('/compras/'.$programaSelecao);
$db = Conexao();

#Função que busca os emails dos fornecedores de grupo igual ao item da Solicitação de compra
function buscaFornecedores($dadosItem, $csolcosequ){
	$db = Conexao();
	$material = false;
	$servico = false;
	for($i=0; $i<count($dadosItem); $i++){
		if(!is_null($dadosItem[$i]->cmatepsequ)){
			$material = true;
		}
		if(!is_null($dadosItem[$i]->cservpsequ)){
			$servico = true;
		}
	}
	if ($material == true) {
		$sqlmat = "SELECT DISTINCT FC.AFORCRSEQU, FC.NFORCRRAZS, FC.AFORCRCCPF, FC.AFORCRCCGC, FC.NFORCRMAIL, FC.NFORCRMAI2
			FROM SFPC.TBFORNECEDORCREDENCIADO FC
			LEFT JOIN SFPC.TBGRUPOFORNECEDOR GF ON FC.AFORCRSEQU = GF.AFORCRSEQU
			LEFT JOIN SFPC.TBSUBCLASSEMATERIAL SCM ON GF.CGRUMSCODI = SCM.CGRUMSCODI
			LEFT JOIN SFPC.TBMATERIALPORTAL MP ON SCM.CSUBCLSEQU = MP.CSUBCLSEQU
			LEFT JOIN SFPC.TBITEMSOLICITACAOCOMPRA ISC ON MP.CMATEPSEQU = ISC.CMATEPSEQU
			WHERE ISC.CSOLCOSEQU = $csolcosequ
			AND (NFORCRMAIL IS NOT NULL OR
			NFORCRMAI2 IS NOT NULL)
			and ((RTRIM(nforcrmail) <> 'SEM@EMAIL' and RTRIM(nforcrmail) <> 'sem@email' AND RTRIM(nforcrmail) <> 'SEM@EMAIL.COM' and RTRIM(nforcrmail) <> 'sem@email.com' and RTRIM(nforcrmail) <> 'SEM@EMAIL.COM.BR' and RTRIM(nforcrmail) <> 'sem@email.com.br')
    		or (RTRIM(nforcrmai2) <> 'SEM@EMAIL' and RTRIM(nforcrmai2) <> 'sem@email' AND RTRIM(nforcrmai2) <> 'SEM@EMAIL.COM' and RTRIM(nforcrmai2) <> 'sem@email.com' and RTRIM(nforcrmai2) <> 'SEM@EMAIL.COM.BR' and RTRIM(nforcrmai2) <> 'sem@email.com.br'))
			ORDER BY FC.AFORCRSEQU ASC";
			
		$resultadomat = executarSQL($db, $sqlmat);
		$dadosMatForn = array();
		$emailFmat = "";
		while($resultadomat->fetchInto($retornomat, DB_FETCHMODE_OBJECT)){
			$dadosMatForn[] = $retornomat;
		}
		//esta sessão vai para a tela de fornecedor
		$_SESSION['forn']['mat'] = $dadosMatForn;
		for($i=0; $i<count($dadosMatForn); $i++){
			if((!empty($dadosMatForn[$i]->nforcrmail) && !is_null($dadosMatForn[$i]->nforcrmail)) || (!empty($dadosMatForn[$i]->nforcrmai2) && !is_null($dadosMatForn[$i]->nforcrmai2))){
				if($dadosMatForn[$i]->nforcrmail == $dadosMatForn[$i]->nforcrmai2){
					$emailFmat .= $dadosMatForn[$i]->nforcrmail.","; 
				}elseif(!empty($dadosMatForn[$i]->nforcrmail) && !is_null($dadosMatForn[$i]->nforcrmail)){
					$emailFmat .= $dadosMatForn[$i]->nforcrmail.","; 
				}elseif(!empty($dadosMatForn[$i]->nforcrmai2) && !is_null($dadosMatForn[$i]->nforcrmai2)){
					$emailFmat .= $dadosMatForn[$i]->nforcrmai2.","; 
				}
			}
		}
	} 
	if ($servico == true) {
		$sqlserv = "SELECT DISTINCT FC.AFORCRSEQU, FC.NFORCRRAZS, FC.AFORCRCCPF, FC.AFORCRCCGC, FC.NFORCRMAIL, FC.NFORCRMAI2
				FROM SFPC.TBFORNECEDORCREDENCIADO FC
				LEFT JOIN SFPC.TBGRUPOFORNECEDOR GF ON FC.AFORCRSEQU = GF.AFORCRSEQU
				LEFT JOIN SFPC.TBSERVICOPORTAL SP ON GF.CGRUMSCODI = SP.CGRUMSCODI
				LEFT JOIN SFPC.TBITEMSOLICITACAOCOMPRA ISC ON SP.CSERVPSEQU = ISC.CSERVPSEQU
				WHERE ISC.CSOLCOSEQU = $csolcosequ
				AND (NFORCRMAIL IS NOT NULL OR
				NFORCRMAI2 IS NOT NULL)
				and ((RTRIM(nforcrmail) <> 'SEM@EMAIL' and RTRIM(nforcrmail) <> 'sem@email' AND RTRIM(nforcrmail) <> 'SEM@EMAIL.COM' and RTRIM(nforcrmail) <> 'sem@email.com' and RTRIM(nforcrmail) <> 'SEM@EMAIL.COM.BR' and RTRIM(nforcrmail) <> 'sem@email.com.br')
     			or (RTRIM(nforcrmai2) <> 'SEM@EMAIL' and RTRIM(nforcrmai2) <> 'sem@email' AND RTRIM(nforcrmai2) <> 'SEM@EMAIL.COM' and RTRIM(nforcrmai2) <> 'sem@email.com' and RTRIM(nforcrmai2) <> 'SEM@EMAIL.COM.BR' and RTRIM(nforcrmai2) <> 'sem@email.com.br'))
				ORDER BY FC.AFORCRSEQU ASC";
				
		$resultadoserv = executarSQL($db, $sqlserv);
		$dadosServForn = array();
		$emailFServ = array();
		while($resultadoserv->fetchInto($retornoserv, DB_FETCHMODE_OBJECT)){
			$dadosServForn[] = $retornoserv;
		}
		//esta sessão vai para a tela de fornecedor
		$_SESSION['forn']['serv'] = $dadosServForn;
		for($i=0; $i<count($dadosServForn); $i++){
			if((!empty($dadosServForn[$i]->nforcrmail) && !is_null($dadosServForn[$i]->nforcrmail)) || (!empty($dadosServForn[$i]->nforcrmai2) && !is_null($dadosServForn[$i]->nforcrmai2))){
				if($dadosServForn[$i]->nforcrmail == $dadosServForn[$i]->nforcrmai2){
					$emailFServ .= $dadosServForn[$i]->nforcrmail.","; 
				}elseif(!empty($dadosServForn[$i]->nforcrmail) && !is_null($dadosServForn[$i]->nforcrmail)){
					$emailFServ .= $dadosServForn[$i]->nforcrmail.","; 
				}elseif(!empty($dadosServForn[$i]->nforcrmai2) && !is_null($dadosServForn[$i]->nforcrmai2)){
					$emailFServ .= $dadosServForn[$i]->nforcrmai2.","; 
				}
			}
		}
	}
	
	if((!empty($emailFServ) && !is_null($emailFServ)) && (!empty($emailFmat) && !is_null($emailFmat))){
		$emailsFornecedores = $emailFServ . $emailFmat;
	}elseif(!empty($emailFServ) && !is_null($emailFServ)){
		$emailsFornecedores = $emailFServ;
	}elseif(!empty($emailFmat) && !is_null($emailFmat)){
		$emailsFornecedores = $emailFmat;
	}
	
	#Retira a ultima vírgula
	$emailsFornecedores = substr($emailsFornecedores, 0, -1);
	#Coloca espaços depois da virgula
	$emailsFornecedores = str_replace(",", ", ", $emailsFornecedores);
	
	return $emailsFornecedores;

}

function insertDados($csolcosequ, $dados, $AssuntoEmail, $textoEmail){
	$db = Conexao();

	$sqlSeq = "select max(csolensequ) as csolensequ from sfpc.tbsolicitacaoenviodoc";
	$resultBusca = executarSQL($db, $sqlSeq);

	$resultBusca->fetchInto($csolensequMax, DB_FETCHMODE_OBJECT);

	$csolensequ = 1 + $csolensequMax->csolensequ;
	//------------------------------------------------------

	$sqlSeq2 = "select max(csolefsequ) as csolefsequ from sfpc.tbsolicitacaoenviodocfornecedores";
	$resultBusca2 = executarSQL($db, $sqlSeq2);

	$resultBusca2->fetchInto($csolefsequMax, DB_FETCHMODE_OBJECT);

	//------------------------------------------------------
	$sqlSeq3 = "select max(csoleasequ) as csoleasequ from sfpc.tbsolicitacaoenviodocanexo";
	$resultBusca3 = executarSQL($db, $sqlSeq3);

	$resultBusca3->fetchInto($csoleasequMax, DB_FETCHMODE_OBJECT);

	$csoleasequ = 1 + $csoleasequMax->csoleasequ;

	//------------------------------------------------------

	
	$sqlInsert = "insert into sfpc.tbsolicitacaoenviodoc 
			(csolensequ, csolcosequ, esolentitu, esolencorp, tsolenenvi, cusupocodi, tsolenulat) 
			values ($csolensequ, $csolcosequ, '$AssuntoEmail', '$textoEmail', now(), ".$_SESSION['_cusupocodi_'].", now())";
	$result = executarSQL($db, $sqlInsert);

	$auxcsolefsequ = 1 + $csolefsequMax->csolefsequ;	

	if(!empty($_SESSION['forn']['serv'])){
		$fornserv = $_SESSION['forn']['serv'];
		for($i=0;$i<count($fornserv);$i++){
			$aforcrsequ = $fornserv[$i]->aforcrsequ;
			$nsolefmai1 = $fornserv[$i]->nsolefmai1;
			$nsolefmai2 = $fornserv[$i]->nsolefmai2;
			$sqlInsert1 = "insert into sfpc.tbsolicitacaoenviodocfornecedores 
							(csolensequ, csolefsequ, aforcrsequ, nsolefmai1, nsolefmai2, tsolefulat) 
							values ($csolensequ, $auxcsolefsequ, $aforcrsequ, '$nsolefmai1', '$nsolefmai2', now())";

			$result1 = executarSQL($db, $sqlInsert1);
			$auxcsolefsequ++;
		}
	}
	if(!empty($_SESSION['forn']['mat'])){
		$fornmat = $_SESSION['forn']['mat'];
		$aforcrsequ = $fornmat[$i]->aforcrsequ;
		$nsolefmai1 = $fornmat[$i]->nsolefmai1;
		$nsolefmai2 = $fornmat[$i]->nsolefmai2;
		for($i=0;$i<count($fornmat);$i++){
			$sqlInsert2 = "insert into sfpc.tbsolicitacaoenviodocfornecedores 
							(csolensequ, csolefsequ, aforcrsequ, nsolefmai1, nsolefmai2, tsolefulat) 
							values ($csolensequ, $auxcsolefsequ, $aforcrsequ, '$nsolefmai1', '$nsolefmai2', now())";

			$result2 = executarSQL($db, $sqlInsert2);
			$auxcsolefsequ++;
		}
	}
	
	for($i=0; $i<count($_SESSION['Arquivos_Upload']['nome']); $i++){
		$arquivo = bin2hex($_SESSION['Arquivos_Upload']['conteudo'][$i]);
		$arquivo_Nome = removeSimbolos($_SESSION['Arquivos_Upload']['nome'][$i]);
		if($_SESSION['Arquivos_Upload']['situacao'][$i] == 'novo' || $_SESSION['Arquivos_Upload']['situacao'][$i] == 'existente'){
			$sqlInsertFile = "insert into sfpc.tbsolicitacaoenviodocanexo 
				(csolensequ, csoleasequ, csoleanome, isoleaarqu, tsoleaulat) 
				values ($csolensequ, $csoleasequ, '".$arquivo_Nome."', '".$arquivo."', now())";
			
			$result = executarSQL($db, $sqlInsertFile);

			$csoleasequ++;
		}
	}
	


}

# Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "GET") {
	$files = glob($path.'*'); // pega os arquivos no repositórios
	if(!empty($files)){                   //Checa se tem arquivos
		foreach($files as $file){ // seta o foreach
			if(is_file($file)) {
				unlink($file); // deleta cada arquivo
			}
		} // Limpa o repositório de arquivos anexos
	}


	$Mensagem = urldecode($_GET['Mensagem']);
	$Mens     = $_GET['Mens'];
	$Tipo     = $_GET['Tipo'];
	$csolcosequ = $_GET['SeqSolicitacao'];
	$_SESSION['dados']['sequencial']   = $csolcosequ;
}elseif($_SERVER['REQUEST_METHOD'] == "POST"){
	$Botao 			= $_POST['Botao'];
	$csolcosequ		= $_POST['SeqSolicitacao'];
	$AssuntoEmail 	= $_POST['AssuntoEmail'];
	$textoEmail 	= nl2br($_POST['textoEmail']);
	$DDocumento     = $_POST['DDocumento'];
	if(empty($_POST['SeqSolicitacao']) && !empty($_SESSION['dados']['sequencial'])){
		$csolcosequ = $_SESSION['dados']['sequencial'];
	}elseif(!empty($_POST['SeqSolicitacao'])){
		$_SESSION['dados']['sequencial']   = $csolcosequ;
	}
			
}



#Busca os dados da scc selecionada
$sqlScc = "
			SELECT sol.*, org.eorglidesc
			from sfpc.tbsolicitacaocompra as sol
			inner join sfpc.tborgaolicitante as org on org.corglicodi = sol.corglicodi 
			WHERE csolcosequ = $csolcosequ
			";
$resultado = executarSQL($db, $sqlScc);
$resultado->fetchInto($dadosScc, DB_FETCHMODE_OBJECT);
#Busca os códigos de material e/ou serviço da solicitação
// Feito separado por considerar que pode vir mais de uma linha de informação diferente da $sqlScc
$sqlMat = "SELECT DISTINCT CMATEPSEQU, CSERVPSEQU FROM SFPC.TBITEMSOLICITACAOCOMPRA
			WHERE CSOLCOSEQU = $csolcosequ";

$resultado = executarSQL($db, $sqlMat);
$dadosItem = array();
while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
	$dadosItem[] = $retorno;
}

$sql = 'SELECT  QPARGETMAOBJETO, QPARGETMAJUSTIFICATIVA, QPARGEDESCSE, EPARGESUBELEMESPEC, QPARGEQMAC, QPARGEQMAC,
                EPARGETDOV
        FROM    SFPC.TBPARAMETROSGERAIS ';

$linha = resultLinhaUnica(executarSQL($db, $sql));

$tamanhoObjeto           = $linha[0];
$tamanhoJustificativa    = $linha[1];
$tamanhoDescricaoServico = strlen($linha[2]);
$subElementosEspeciais   = explode(',', $linha[3]);
$tamanhoArquivo          = $linha[4];
$tamanhoNomeArquivo      = $linha[5];
$extensoesArquivo        = $linha[6];

$objeto = $dadosScc->esolcoobje;
$Orgao = $dadosScc->eorglidesc;
$numeroSccAtual = getNumeroSolicitacaoCompra($db, $csolcosequ);

$dadosFornecedores = buscaFornecedores($dadosItem, $csolcosequ);


$ErroPrograma = __FILE__;

#Comandos dos botões buscam os emails separadamente porém pela mesma função para evitar perca de dados mantendo  centralização da função 
if ($Botao == "EnviarEmail" && $Mens == 0){
	$emailsFornecedores = buscaFornecedores($dadosItem, $csolcosequ);
	$textoEmailsalvar;
	$textoEmail;
	
	if(count($_SESSION['Arquivos_Upload']['path']) == 1){
		$arquivo 	 = $_SESSION['Arquivos_Upload']['path'][0];
		$arquivoNome = removeSimbolos(RetiraAcentos(removeCaracteresEspeciais($_SESSION['Arquivos_Upload']['nome'][0]))); //Retira acentos, caracteres e simbolos do nome do arquivo a ser enviado

	}else{
		$arquivo 	 = $_SESSION['Arquivos_Upload']['path'];
		$arquivoNome = removeSimbolos(RetiraAcentos(removeCaracteresEspeciais($_SESSION['Arquivos_Upload']['nome']))); //Retira acentos, caracteres e simbolos do nome do arquivo a ser enviado
	}
	
	if(!empty($AssuntoEmail) && !empty($textoEmail) && !empty($arquivo)){
		$mensagem = "
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
						width: 800px;
						padding-left: 5em;
						padding-right: 5em;
					}
					img{
						width: 800px;
					}
					td{
						width: 50%;
					}

					#content{
						width: 800px;
					}
					#bordatd{
						border: 1px solid grey;
						text-align: justify;
					}
					#textoEmail{
						text-align: justify;
						width: 500px;
					}
					#head{
						width: 800px;
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
                                        <td colspan=\"2\">
                                            <br>
                                            Assunto: $AssuntoEmail
											<br><br>
                                        </td>
                                    </tr>
                                </thead>
                                <tr>
                                    <td colspan=\"2\">
												$textoEmail
                                    </td>
                                </tr>
								<tr>
                                <td colspan=\"2\">
									<hr size=\"1\" style=\"border:1px dashed #0B62C3;\">
                                    Este e-mail foi enviado pelo sistema Portal de Compras do Recife, assim por favor não responda.<br><br> 
                                    Em caso de dúvida, entre em contato com a equipe de suporte do Portal de Compras através do 
                                    e-mail portalcompras@recife.pe.gov.br ou do telefone 3355-8790.
                                    <hr size=\"1\" style=\"border:1px dashed #0B62C3;\">
                                </td>
                            </tr>
                            </table>
                        </container>
                    </div>
                </body>
            
            </html>    
            ";

		if((!empty($arquivo) || !is_null($arquivo)) && (!empty($arquivoNome) || !is_null($arquivoNome))){

			$retorno = EnviaEmailHTML($emailsFornecedores,"NÃO RESPONDA".$NomeLocalTitulo." - ".$AssuntoEmail,$mensagem,$GLOBALS["EMAIL_FROM"],$arquivo,$arquivoNome);
			if($retorno == false){
				$Mens = 1;
				$Tipo = 2;
				$Mensagem = 'Problema no envio do e-mail!';
			}else{
				$returnInsert = insertDados($csolcosequ, $dadosFornecedores, $AssuntoEmail, $textoEmail);
			}


			unset($_POST['SeqSolicitacao']);
			unset($_POST['AssuntoEmail']);
			unset($_POST['textoEmail']);
			unset($_SESSION['Arquivos_Upload']);
			$files = glob($path.'*'); // pega os arquivos no repositórios
			if(!empty($files)){                   //Checa se tem arquivos
				foreach($files as $file){ // seta o foreach
					if(is_file($file)) {
						unlink($file); // deleta cada arquivo
					}
				} // Limpa o repositório de arquivos anexos
			} // Limpa o repositório de arquivos anexos

			$Mens = 1;
			$Tipo = 1;
			$Mensagem = 'E-mail enviado';
		}else{

			$retorno = EnviaEmailHTML($emailsFornecedores,"NÃO RESPONDA".$NomeLocalTitulo." - ".$AssuntoEmail,$mensagem,$GLOBALS["EMAIL_FROM"]);
			if($retorno == false){
				$Mens = 1;
				$Tipo = 2;
				$Mensagem = 'Problema no envio do e-mail!';
			}else{
				$returnInsert = insertDados($csolcosequ, $dadosFornecedores, $AssuntoEmail, $textoEmail);
			}

			unset($_POST['SeqSolicitacao']);
			unset($_POST['AssuntoEmail']);
			unset($_POST['textoEmail']);

			$Mens = 1;
			$Tipo = 1;
			$Mensagem = 'E-mail enviado';
		}
	}else{
		$informe = "";
		$informe .= empty($AssuntoEmail)  ? "Assunto do E-mail," : "";
		$informe .= empty($textoEmail)  ? "Texto do E-mail," : "";
		$informe .= empty($arquivo) ? "Arquivo Anexo," : "";
		#Retira a ultima vírgula
		$informe = substr($informe, 0, -1);
		#Coloca espaços depois da virgula
		$informe = str_replace(",", ", ", $informe);

		$Mens = 1;
		$Tipo = 2;
		$Mensagem = 'Informe: '.$informe;
	}
	
		
}elseif($Botao == 'Incluir_Documento') {
    if ($_FILES['arquivoAnexo']['tmp_name']) {
        $_FILES['arquivoAnexo']['name'] = RetiraAcentos($_FILES['arquivoAnexo']['name']);
		
		$extensoesArquivo .= ', .zip, .xlsm';
        $extensoes = explode(',', strtolower2($extensoesArquivo));
        array_push($extensoes, '.zip', '.xlsm');

        $noExtensoes = count($extensoes);
        $isExtensaoValida = false;
        
        for ($itr = 0; $itr < $noExtensoes; ++ $itr) {
            if (preg_match('/\\' . trim($extensoes[$itr]) . '$/', strtolower2($_FILES['arquivoAnexo']['name']))) {
                $isExtensaoValida = true;
            }
        }
        
        if (! $isExtensaoValida) {
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= 'Selecione somente documento com a(s) extensão(ões) ' . $extensoesArquivo;
        }
        if (strlen($_FILES['arquivoAnexo']['name']) > 100) {
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= 'O titulo do arquivo é muito grande. Máximo de 100 caracteres';
        }

        if (strlen($_FILES['arquivoAnexo']['name']) > $tamanhoNomeArquivo) {
            if ($Mens == 1) {
                $Mensagem .= ', ';
            }
            $Mens = 1;
            $Tipo = 2;
            $Mensagem .= 'Nome do Arquivo com até ' . $tamanhoNomeArquivo . ' Caracateres ( atualmente com ' . strlen($_FILES['arquivoAnexo']['name']) . ' )';
        }
        $Tamanho = $tamanhoArquivo * 1024;

        if (($_FILES['arquivoAnexo']['size'] > $Tamanho) || ($_FILES['arquivoAnexo']['size'] == 0)) {
            if ($Mens == 1) {
                $Mensagem .= ', ';
            }
            $Kbytes    = $tamanhoArquivo;
            $Kbytes    = (int) $Kbytes;
            $Mens      = 1;
            $Tipo      = 2;
            $Mensagem .= "Este arquivo ou é muito grande ou está vazio. Tamanho Máximo: $Kbytes Kb";
        }

        if ($Mens == '') {
            if (! ($_SESSION['Arquivos_Upload']['conteudo'][] = file_get_contents($_FILES['arquivoAnexo']['tmp_name']))) {
                $Mens = 1;
                $Tipo = 2;
                $Mensagem = 'Caminho da Documentação Inválido';
            } else {
                $_SESSION['Arquivos_Upload']['nome'][] = $_FILES['arquivoAnexo']['name'];
                $_SESSION['Arquivos_Upload']['situacao'][] = 'novo'; // situacao pode ser: novo, existente, cancelado e excluido
                $_SESSION['Arquivos_Upload']['codigo'][] = ''; // como é um arquivo novo, ainda nao possui código
                $_SESSION['Arquivos_Upload']['codigo'][] = ''; // local onde o arquivo vai ser salvo no ftp
				$_SESSION['Arquivos_Upload']['path'][] = $path.$_FILES['arquivoAnexo']['name'];
				move_uploaded_file($_FILES['arquivoAnexo']['tmp_name'], $path.$_FILES['arquivoAnexo']['name']);
            }
        }
    } else {
        $Mens = 1;
        $Tipo = 2;
        $Mensagem = 'Documentação Inválida';
    }
} elseif ($Botao == 'Retirar_Documento') {
    foreach ($DDocumento as $valor) {
		
        // $_SESSION['Arquivos_Upload']['conteudo'][$valor]="";
        // $_SESSION['Arquivos_Upload']['nome'][$valor]="";
        if ($_SESSION['Arquivos_Upload']['situacao'][$valor] == 'novo') {
            $_SESSION['Arquivos_Upload']['situacao'][$valor] = 'cancelado'; // cancelado- quando o usuário incluiu um arquivo novo mas desistiu
			unlink($_SESSION['Arquivos_Upload']['path'][$valor]); // O arquivo é retirado do FTP
		} elseif ($_SESSION['Arquivos_Upload']['situacao'][$valor] == 'existente') {
            $_SESSION['Arquivos_Upload']['situacao'][$valor] = 'excluido'; // excluído- quando o arquivo já existe e deve ser excluido no sistema
			unlink($_SESSION['Arquivos_Upload']['path'][$valor]); // O arquivo é retirado do FTP
		}
    }
}

?>
<html>
<?php
# Carrega o layout padrão #
layout();
?>
<script language="javascript" src="../janela.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
<script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>
<script language="javascript" type="">
	<!--
	function enviar(valor){
		if(valor == "voltar"){
			document.formulario.action = "ConsEnvioDocumentoSelecionar.php";
			document.formulario.submit();
		}else if(valor == "ExibeFornecedores"){
			document.formulario.action = "ConsEnvioDocumentoFornecedores.php";
			document.formulario.submit();
		}
		document.formulario.Botao.value = valor;
		document.formulario.submit();
	}
	
	function AbreJanela(url,largura,altura){
		window.open(url,'paginadetalhe','status=no,scrollbars=yes,left=15,top=15,width='+largura+',height='+altura);
	}

	function onClickDesativado(erro){
		alert(erro);
	}

    $(document).ready(function() {
        //$('#numeroAno').mask('9999/9999');
        $('#numeroScc').mask('9999.9999/9999');
    });

	<?php MenuAcesso(); ?>
	//-->
</script>
<link rel="stylesheet" type="text/css" href="../estilo.css">
<body background="../midia/bg.gif" marginwidth="0" marginheight="0">
	<script language="JavaScript" src="../menu.js"></script>
	<script language="JavaScript">Init();</script>
	<form action="<?=$programa?>" method="post" name="formulario"  enctype="multipart/form-data">
		<br><br><br><br><br>
		<table cellpadding="3" border="0" summary="">
			<!-- Caminho -->
			<tr>
				<td width="150"><img border="0" src="../midia/linha.gif" alt=""></td>
				<td align="left" class="textonormal" colspan="2">
					<font class="titulo2">|</font>
					<a href="../index.php">
						<font color="#000000">Página Principal</font>
					</a> > Compras > Enviar Documentos
				</td>
			</tr>
			<!-- Fim do Caminho-->
			<!-- Erro -->
			<?php if ($Mens == 1) { ?>
				<tr>
					<td width="150"></td>
					<td align="left" colspan="2"><?php ExibeMens($Mensagem,$Tipo,1); ?></td>
				</tr>
			<?php } ?>
			<!-- Fim do Erro -->
			<!-- Corpo -->
			<tr>
				<td width="150"></td>
				<td class="textonormal">
					<table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolor="#75ADE6" summary="" class="textonormal"  bgcolor="#FFFFFF">
						<tr>
							<td align="center" bgcolor="#75ADE6" valign="middle" class="titulo3" colspan="4">
								ENVIAR DOCUMENTOS - SOLICITAÇÃO DE COMPRA E CONTRATAÇÃO DE MATERIAL OU SERVIÇO (SCC)
							</td>
						</tr>
						<tr>
							<td class="textonormal" colspan="4">
								<p align="justify">
									Anexe o documento desejado e clique no botão "Enviar". Caso deseje copiar a lista de Fornecedores Associados ao(s) grupo(s) dos itens de materiais e/ou serviços da Solicitação(SCC) selecionada que receberão o e-mail, clique no botão "Exibir Fornecedores".
								</p>
							</td>
						</tr>
						<tr>
							<td colspan="4">
								<table border="0" width="100%" summary="">
                                    <tr>
                                        <td class="textonormal" bgcolor="#DCEDF7">Número da SCC </td>
                                        <td class="textonormal">
                                            <?php
												echo $numeroSccAtual;
											?>
                                        </td>
                                    </tr>
									<tr>
										<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Órgão</td>
										<td class="textonormal">
											<?php
												echo $Orgao;
											?>
										</td>
									</tr>
									<tr>
										<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Objeto</td>
										<td class="textonormal">
											<?php 
												echo $objeto; 
											?>
										</td>
									</tr>
									<tr>
										<td class="textonormal" bgcolor="#DCEDF7" width="30%" height="20">Assunto do e-mail*</td>
										<td>
											<input type="text" class="textonormal" name="AssuntoEmail" value="<?php echo $_POST['AssuntoEmail'];?>" maxlength="200" style="text-transform:none; width:500px;">
										</td>
									</tr>
									<tr>
										<td class="textonormal" bgcolor="#DCEDF7" width="200" height="20">Texto do e-mail*</td>
										<td>
											<textarea class="textonormal" name="textoEmail" style="width:500px; height:100px; text-align:adjust;" maxlength="1000" ><?php echo $_POST['textoEmail'];?></textarea>
										</td>
									</tr>
									<tr>
									<td class="textonormal" bgcolor="#DCEDF7" width="200" height="20">Arquivo Anexo*</td>
										<td>
											<input type="file" name="arquivoAnexo" required>
										</td>
									</tr>
									<?php
										$DTotal = count($_SESSION['Arquivos_Upload']['conteudo']);

										if ($DTotal == 0) { ?>
										<tr>
											<td class="textonormal" colspan='2' >
											Nenhum documento informado</td>
										</tr>
									<?php } ?>

									<?php 
										for ($Dcont = 0; $Dcont < $DTotal; ++ $Dcont) {
											if ($_SESSION['Arquivos_Upload']['situacao'][$Dcont] == 'novo' || $_SESSION['Arquivos_Upload']['situacao'][$Dcont] == 'existente') {
												echo '<tr>';
												if (! $ocultarCamposEdicao) {
													echo "<td align='right' ><input type='checkbox' name='DDocumento[$Dcont]' value='$Dcont' ></td>\n";
												}
												echo "<td class='textonormal' >";
												if (! $ocultarCamposEdicao) {
													echo $_SESSION['Arquivos_Upload']['nome'][$Dcont];
												} else {
													$arquivo = 'compras/' . $_SESSION['Arquivos_Upload']['nome'][$Dcont];
													addArquivoAcesso($arquivo);

													echo "<a href='../carregarArquivo.php?arq=" . urlencode($arquivo) . "'>" . $_SESSION['Arquivos_Upload']['nome'][$Dcont] . '</a>';
												}
												echo '</td></tr>';
											}
										}
									?>
									<?php if (! $ocultarCamposEdicao) { ?>
										<tr>
											<td class="textonormal" colspan="7" align="center" >
												<input type="button" name="IncluirDocumento" value="Incluir Documento" class="botao" onclick="javascript:enviar('Incluir_Documento');" >
												<input type="button" name="RetirarDocumento" value="Retirar Documento" class="botao" onClick="javascript:enviar('Retirar_Documento');" >
											</td>
										</tr>
									<?php } ?>
								</table>
							</td>
						</tr>
						<tr>
							<td class="textonormal" align="right" colspan="4">
								<input type="button" name="Exibir fornecedores" value="Exibir fornecedores" class="botao" onClick="javascript:enviar('ExibeFornecedores')">
								<input type="button" name="Enviar" value="Enviar" class="botao" onClick="javascript:enviar('EnviarEmail')">
								<input type="button" name="Voltar" value="Voltar" class="botao" onClick="javascript:enviar('voltar')">
								<input type="hidden" name="SeqSolicitacao" value="<?php echo $csolcosequ; ?>">
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
