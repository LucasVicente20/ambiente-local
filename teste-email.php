<?php
ini_set('display_errors', 0);
error_reporting(E_ALL ^ E_NOTICE);
 print_r(get_loaded_extensions());die;
include "funcoes.php";

$numargs = 4;
global $Mail;

// Ignorar o parametro do email para evitar que mande e-mail para os usuários não relacionados ao suporte, caso não esteja em produção
$_PARA_ = "eliakim.dev@gmail.com,";

$_ASSUNTO_ = "teste de envio Eliakim";
$_MENSAGEM_ = "funcionou?";
$_COMPLEMENTO_ = "portalcompras@recife.pe.gov.br";

require_once ($GLOBALS["CAMINHO_EMAIL"] . 'class.phpmailer.php');
require_once ($GLOBALS["CAMINHO_EMAIL"] . 'class.smtp.php');

/* Cria Objeto do E-mail */
$objmail = new PHPMailer();

/* Destinatários */
$_PARA_ = explode(",", $_PARA_);

foreach ($_PARA_ as $Address) {
    $objmail->addAddress(trim($Address));
}

/* Remetente */
$_COMPLEMENTO_ = trim(str_replace("from:", "", strtolower2($_COMPLEMENTO_)));

$objmail->From = $_COMPLEMENTO_;
$objmail->Sender = $_COMPLEMENTO_;
$objmail->FromName = $_COMPLEMENTO_;

/* Mensagem */
$objmail->Body = iconv('utf-8', 'iso-8859-1', $_MENSAGEM_);

/* Assunto */
$titulo = $GLOBALS["LOCAL_SISTEMA_TITULO"] . " " . $_ASSUNTO_;
$objmail->Subject = iconv('utf-8', 'iso-8859-1', $titulo);


$objmail->IsSMTP();

/* Dados do Servidor de Envio */
$objmail->Mailer = 'smtp';
$objmail->Host = 'smtp.recife.pe.gov.br';
$objmail->Port = '25';

$teste = $objmail->send();
var_dump($teste);