<?php 
require_once("../funcoes.php");
require_once (CAMINHO_SISTEMA . "app/TemplateAppPadrao.php");

$tpl = new TemplateAppPadrao("templates/RotEmailPadrao.html","RotEmailPadrao");

if( $_SERVER['REQUEST_METHOD'] == "POST"){
		$Critica   = $_POST['Critica'];
		$Nome      = strtoupper2(trim($_POST['txtnome']));
		$Email     = trim($_POST['txtemail']);
		$MensEmail = strtoupper2(trim($_POST['txtmensagem']));
		$Comissao  = $_POST['Comissao'];
		$URL       = $_POST['URL'];

if ($Comissao){
		$db   = Conexao();
		$sql  = "SELECT NCOMLIPRES, ECOMLIMAIL ";
		$sql .= "  FROM SFPC.TBCOMISSAOLICITACAO ";
		$sql .= " WHERE FCOMLISTAT = 'A' AND CCOMLICODI = $Comissao ";
		$sql .= "				";
		$result = $db->query($sql);
		if( PEAR::isError($result) ){
		   $Mensagem =  ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
		} else {
				$Linha  = $result->fetchRow();
				//$Pessoa = $Linha[0];
				//$Para   = $Linha[1];
				$tpl->PESSOA = urldecode($Linha[0]);
		}
		$db->disconnect();
}

$Critica = 1;
# Critica dos Campos #
if( $Critica == 1 ){
        $Mens     = 0;
        $Mensagem = "<h4>Informe:</h4>";

              /* bloco ul alerta */
        $Mensagem .= "<ul>";
		if( $Nome == "" ) {
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem .= "<a href=\"javascript:document.RotFaleConosco.txtnome.focus();\" class=\"titulo2\">Nome</a>";
	  }
	  if( $Email == "" ){
				if( $Mens == 1 ){ $Mensagem.=", "; }
		    $Mens      = 1;
		    $Tipo      = 2;
  			$Mensagem = $Mensagem .= "<a href=\"javascript:document.RotFaleConosco.txtemail.focus();\" class=\"titulo2\">E-mail</a>";
	  }elseif(! strchr($Email, "@")){
		    if ($Mens == 1){$Mensagem.=", ";}
		    $Mens      = 1;
		    $Tipo      = 2;
                $Mensagem = $Mensagem .= "<a href=\"javascript:document.RotFaleConosco.txtmail.focus();\" class=\"titulo2\">E-mail Inválido</a>";
                $Email = "";
		}
	  if( $MensEmail == "" ){
		    if ($Mens == 1){$Mensagem.=", ";}
		    $Mens      = 1;
		    $Tipo      = 2;
    		$Mensagem  = $Mensagem .= "<a href=\"javascript:document.RotFaleConosco.txtmensagem.focus();\" class=\"titulo2\">Mensagem</a>";
		}
      if( $Mens == 0){
				$Para = "portalcompras@recife.pe.gov.br";
				$From = $Email;
				if( EnviaEmail($Para,"Mensagem Enviada do Portal de Compras da Prefeitura do Recife","Nome: ".$Nome."\nE-mail: ".$Email."\n\nMensagem:\n".$MensEmail,"from: $From") ){
						$Mensagem ="Mensagem Enviada com Sucesso";
                        $Tipo = 1;
            
				}else{
						$Mensagem ="Erro no envio. Tente novamente mais tarde";
				}
				$Mens      = 1;
				$Tipo      = 1;
				$Nome      = "";
	    		$Email     = "";
				$MensEmail = "";
		}
        $Mensagem .= "</ul>";
}

if ($Mens != 0 || !empty($Mensagem)) {
            $tpl->NOME = $Nome;
            $tpl->EMAIL = $Email;
            $tpl->TEXTO_MENSAGEM = $MensEmail;
            
            $tpl->exibirMensagemFeedback($Mensagem, $Tipo);
        }
        
        }else{
		$Comissao  = $_GET['Comissao'];
		$URL       = $_GET['URL'];
}

$tpl->show();
?>