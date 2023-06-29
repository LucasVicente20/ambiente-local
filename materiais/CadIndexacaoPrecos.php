<?php

/**
 * Portal de Compras
 * Programa: CadAnalisarDFD.php
 * Autor: Osmar Celestino
 * Data: 09/12/2022
 * Objetivo: Programa para Analisar de DFD
 * Tarefa Redmine: #275876
 * ---------------------------------------------------------------------------
 * Alterado: Diógenes Dantas
 * Data:     13/12/2022
 * Objetivo: CR 275574
 * ---------------------------------------------------------------------------
 */
require '../funcoes.php';
session_start();
Seguranca();
if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}



/**
 * plotarBlocoAno
 *
 * Monta o select de ano
 *
 * @param array $anos Lista com anos
 *
 * @return void
 */

function indexarPreco(){
    return true;
}
function consultaindexPreco(){
    $db = Conexao();
    $sql = "select cinrprcodi, tinrprulat, vinrprrepr ";
    $sql .= "from sfpc.tbindicereajusteprecos ";
    $sql .= "order by cinrprcodi desc ";
    $resultado = executarSQL($db, $sql);
    $dados = array();
    while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)) {
        $dados[] = $retorno;
    }
    return $dados;
}

function excluirValor($valores){
    $db = Conexao();
    $sql = "DELETE FROM sfpc.tbindicereajusteprecos
            WHERE cinrprcodi in ($valores)";
    $resultado = executarSQL($db, $sql);
    
    if ($resultado != False) {
        return True;
    } else {
        return False;
    }

}
function proccessPrincipal(TemplatePaginaPadrao $tpl)
{
    // Variáveis com o global off #
    if($_POST['Botao'] == 'excluir'){
    
        $valores = $_POST['checkboxID'];
        $valorExcluir = '';
        foreach($valores as $valor){
            $valorExcluir .= $valor.','; 
        }
        $valorExcluir = substr_replace($valorExcluir,'',strripos($valorExcluir,',')); 
        $retorno = excluirValor($valorExcluir);
        if($retorno == True){
            header('Location: CadIndexacaoPrecos.php');
        }
        // $tpl->MENSAGEM = 'Selecione pelo menos um sequencial.';
        // $tpl->BLOCK('BLOCO_SUCESSO');
    }

    if($_POST['Botao'] == 'erro'){
        $tpl->MENSAGEM = 'Selecione pelo menos um sequencial.';
        $tpl->BLOCK('BLOCO_SUCESSO');
    }

    
    if($_POST['Botao'] == 'Exportar'){
        indexarPreco($tpl);
    }
    $indexPreco = consultaindexPreco();
    foreach ($indexPreco as $index){
        $data = DataBarra($index->tinrprulat);
        $tpl->CODIGO_INDICE = $index->cinrprcodi;
        $tpl->VALOR_INDICE = converte_valor_licitacao($index->vinrprrepr);
        $tpl->DATA_INDICE = $data;
        $tpl->BLOCK('DADOS_INDEX');
    }



}


function alteraSituacao($sequencial,$situacao)
{
    $db = Conexao();
    $sql = "update sfpc.tbplanejamentodfd set cplsitcodi= '" .$situacao."' where cpldfdsequ = $sequencial";
    $resultado = executarSQL($db, $sql);
    if ($resultado != False) {
        return True;
    } else {
        return False;
    }
}

function processAnalisar()
{
    if ($_POST['sequencial']) {
        $status = alteraSituacao($_POST['sequencial'],6);
        header("Refresh: 0");
    }

}
function processExportar($tpl)
{
    $objPlanejamento = new Planejamento();

    $dados = consultaDFD();
    $htmlResultado = $objPlanejamento->montaHTMLAnalisarPDF($dados);
    $_SESSION['HTMLPDF'] = $htmlResultado;
    $_SESSION['HTMLPDFDownload'] = false;
    $_SESSION['HTMLPDFMudaOrientacao'] = true;
    echo "<script>window.open('../dompdf/GeraPdf.php', '_blank')</script>";
}


/**
 * [frontController description]
 */
function frontController()
{
    $tpl = new TemplatePaginaPadrao("templates/CadIndexacaoPrecos.html", "Materiais > Indexação de Preços");
    proccessPrincipal($tpl);
    $tpl->show();
}
frontController();
