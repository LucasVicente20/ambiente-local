<?php 

require_once "../funcoes.php";
/*require_once(CAMINHO_SISTEMA."/import/Template.class.php");
$db = Conexao();
var_dump($db);

$tpl = new Template("templates/padrao.template.html");
$tpl->show();
*/
require_once "TemplateAppPadrao.php";

$x = new TemplateAppPadrao("templates/ConsInformacaoFornecedores.html");
//$x->MENU = "teste maldito";
$x->show();
