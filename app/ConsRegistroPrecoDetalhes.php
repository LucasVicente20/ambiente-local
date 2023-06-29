<?php
/**
 * Portal da DGCO
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category Application
 * @package Pitang
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * #-------------------------------------------------------------------------
 * # Portal da DGCO
 * # Programa: EmissaoCHF.php
 * # Autor:    Roberta Costa
 * # Data:     21/09/04
 * # Objetivo: Programa que Exibe os dados do CHF do Fornecedor Cadastrado
 * #---------------------------------
 * # Alterado: Rossana Lira
 * # Data:     16/05/07 - Troca do nome fornecedor para firma
 * # Data:     09/07/07 - Permitir emitir o CHF, mesmo estando com certidões fora do
 * #                      prazo de validade
 * #                    - Passar mensagem fornecedor c/certidões fora do prazo p/impressão
 * # Alterado: Everton Lino
 * # Data:     06/08/2010 - Verificação de data de balanço anual se está no prazo
 * # Alterado: Everton Lino
 * # Data:     14/10/2010- Correção
 * # Alterado: Ariston Cordeiro
 * # Data:     05/11/2010 - Alterando prazos de balanço anual e certidão negativa
 * # Alterado: Rodrigo Melo
 * # Data:     25/04/2011 - Retirando da mensagem de atenção a palavra "Inabilitado",
 * #                        devido a solicitação do usuário. Tarefa Redmine: 2205.
 * # Data:     28/11/2014 - Novo Layout
 * # Alterado: Pitang Agile TI
 */
# Alterado: Lucas Baracho
# Data:     29/10/2018
# Objetivo: Tarefa Redmine 199575
#-------------------------------------------------------------------------
# Alterado: Caio Coutinho
# Data:     26/11/2018
# Objetivo: Tarefa Redmine 206846
#-------------------------------------------------------------------------
require_once "TemplateAppPadrao.php";
$tpl = new TemplateAppPadrao("templates/ConsRegistroPrecoDetalhes.html");
$db = Conexao();

// # Acesso ao arquivo de funções #
// include "../funcoes.php";

// # Executa o controle de segurança #
// session_start();
// Seguranca();

// Variáveis com o global off #
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $Botao = $_POST['Botao'];
    $Critica = $_POST['Critica'];
} else {
    $GrupoCodigo          = $_GET['GrupoCodigo'];
    $Processo             = $_GET['Processo'];
    $ProcessoAno          = $_GET['ProcessoAno'];
    $ComissaoCodigo       = $_GET['ComissaoCodigo'];
    $OrgaoLicitanteCodigo = $_GET['OrgaoLicitanteCodigo'];
    $ObjetoPesquisa       = $_GET['ItemObjeto'];
    $ComissaoPesquisa     = $_GET['ItemComissao'];
    $OrgaoPesquisa        = $_GET['ItemOrgao'];
    $ata                  = $_GET['ata'];



}

$titulo = ''; $dados = ''; $tituloDocumentos = ''; $documentos = '';

// Identifica o Programa para Erro de Banco de Dados #
$ErroPrograma = __FILE__;

function sqlAtaPorchave($processo, $orgao, $ano, $chaveAta) {
    $sql  = "select a.carpincodn, a.earpinobje, a.aarpinanon, a.aarpinpzvg, a.tarpindini, a.cgrempcodi, a.cusupocodi, f.nforcrrazs, d.edoclinome,";
    $sql .= " a.corglicodi, a.carpnosequ, a.alicpoanop, s.csolcosequ, a.aarpinanon, carpnoseq1, ";

    $sql .= " f.nforcrrazs, f.aforcrccgc, f.aforcrccpf, f.eforcrlogr, ";
    $sql .= " f.aforcrnume, f.eforcrbair, f.nforcrcida, f.cforcresta, ";

    $sql .= " fa.nforcrrazs as razaoFornecedorAtual, fa.aforcrccgc as cgcFornecedorAtual, fa.aforcrccpf as cpfFornecedorAtual, fa.eforcrlogr as logradouroFornecedorAtual, ";
    $sql .= " fa.aforcrnume as numeroEnderecoFornecedorAtual, fa.eforcrbair as bairroFornecedorAtual, fa.nforcrcida as cidadeFornecedorAtual, fa.cforcresta as estadoFornecedorAtual ";

    $sql .= " from sfpc.tbataregistroprecointerna a";

    $sql .= " left outer join sfpc.tbsolicitacaolicitacaoportal s";
    $sql .= " on (s.clicpoproc = a.clicpoproc";
    $sql .= " and s.alicpoanop = a.alicpoanop";
    $sql .= " and s.ccomlicodi = a.ccomlicodi";
    $sql .= " and s.corglicodi = a.corglicodi)";

    $sql .= " left outer join sfpc.tbfornecedorcredenciado f";
    $sql .= " on f.aforcrsequ = a.aforcrsequ";

    $sql .= " left outer join sfpc.tbfornecedorcredenciado fa";
    $sql .= " on fa.aforcrsequ = (select afa.aforcrsequ from sfpc.tbataregistroprecointerna afa where afa.carpnosequ = a.carpnoseq1)";

    $sql .= " left outer join sfpc.tbdocumentolicitacao d";
    $sql .= " on d.clicpoproc = a.clicpoproc";
    $sql .= " and d.clicpoproc = " . $processo;
    $sql .= " and d.corglicodi = " . $orgao;
    $sql .= " and d.alicpoanop = " . $ano;

    $sql .= " where a.carpnosequ = " . $chaveAta;

    return $sql;
}

$sql = "SELECT ge.egrempdesc, arpi.carpnosequ, cl.ecomlidesc, arpi.earpinobje, ol.eorglidesc, arpi.tarpindini, ";
$sql .= " arpi.aarpinpzvg, arpi.aforcrsequ, arpi.clicpoproc, arpi.alicpoanop, arpi.cgrempcodi, arpi.ccomlicodi,  ";
$sql .= " arpi.corglicodi, arpi.cusupocodi, arpi.aarpinanon, arpi.carpnoseq1 "; 
$sql .= " FROM sfpc.tbataregistropreconova arpn";

// Ata interna
$sql .= ' LEFT JOIN sfpc.tbataregistroprecointerna arpi ';
$sql .= ' ON arpn.carpnosequ = arpi.carpnosequ ';

// Comissão
$sql .= ' LEFT JOIN sfpc.tbcomissaolicitacao cl ';
$sql .= ' ON arpi.ccomlicodi = cl.ccomlicodi ';

// Grupo
$sql .= ' LEFT JOIN sfpc.tbgrupoempresa ge';
$sql .= ' on arpi.cgrempcodi = ge.cgrempcodi';

// Órgão licitante
$sql .= ' JOIN sfpc.tborgaolicitante ol ';
$sql .= ' ON ol.corglicodi = arpi.corglicodi ';

$sql .= '  WHERE arpn.carpnosequ = ' . $ata;

$result = $db->query($sql);
if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    $Rows = $result->numRows();
    while ($Linha = $result->fetchRow()) {
        $GrupoDesc              = $Linha[0];
        $ata                    = $Linha[1];
        $ComissaoDesc           = $Linha[2];
        $objeto                 = $Linha[3];
        $OrgaoLicitacao         = $Linha[4];
        $dataInicial            = $Linha[5];
        $vigencia               = $Linha[6] . ' Meses';
        $fornecedor             = $Linha[7];
        $clicpoproc             = $Linha[8];
        $alicpoanop             = $Linha[9];
        $cgrempcodi             = $Linha[10];
        $ccomlicodi             = $Linha[11];
        $corglicodi             = $Linha[12];
        $cusupocodi             = $Linha[13];
        $aarpinanon             = $Linha[14];
        $carpnoseq1             = $Linha[15];        
    }
}

// Fornecedor
$sql = " SELECT nforcrrazs, aforcrccgc, aforcrccpf FROM sfpc.tbfornecedorcredenciado WHERE aforcrsequ = " . $fornecedor;
$result = $db->query($sql);
if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    $Rows = $result->numRows();
    while ($Linha = $result->fetchRow()) {
        $nomeFornecedor = $Linha[0];
        $cnpjCpffornecedor = !empty($Linha[1]) ? FormataCNPJ($Linha[1]) : FormataCPF($Linha[2]);
    }
}

// Documentos
$documentos = array();
$sql = " SELECT encode(idocatarqu, 'base64') as arquivo, carpnosequ, cdocatsequ, edocatnome, cusupocodi ";
$sql .= " FROM sfpc.tbdocumentoatarp darp ";
$sql .= " WHERE darp.carpnosequ = " . $ata;
$result = $db->query($sql);
if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    $Rows = $result->numRows();
    while ($Linha = $result->fetchRow()) {
        $documentos[] = $Linha;
    }
}

// Licitação
$licitacao = array();
$sql = " SELECT cmodlicodi FROM sfpc.tblicitacaoportal WHERE 1 = 1 AND clicpoproc = %d AND alicpoanop = %d AND cgrempcodi = %d AND ccomlicodi = %d AND corglicodi = %d ";
$sql = sprintf($sql, $clicpoproc, $alicpoanop, $cgrempcodi, $ccomlicodi, $corglicodi);
$result = $db->query($sql);
if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    $Rows = $result->numRows();
    while ($Linha = $result->fetchRow()) {
        $cmodlicodi = $Linha[0];
    }
}

// Modalidade
$sql = " SELECT mod.emodlidesc FROM sfpc.tbmodalidadelicitacao mod WHERE mod.cmodlicodi = " .$cmodlicodi;
$result = $db->query($sql);
if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    $Rows = $result->numRows();
    while ($Linha = $result->fetchRow()) {
        $ModalidadeDesc = $Linha[0];
    }
}

// Número da ata
$dto = array();
$sqlConsultaAta = sqlAtaPorchave($clicpoproc, $corglicodi, $alicpoanop, $ata);
$resultado = executarSQL($db, $sqlConsultaAta);
$resultado->fetchInto($consultaAta, DB_FETCHMODE_OBJECT);

$sql = " SELECT ccp.ccenpocorg, ccp.ccenpounid, ccp.corglicodi FROM sfpc.tbcentrocustoportal ccp WHERE 1=1 ";  
if ($consultaAta->corglicodi != null || $consultaAta->corglicodi != "") {
    $sql .= " AND ccp.corglicodi = " . $consultaAta->corglicodi;
}
$res = executarSQL($db, $sql);
$itens = array();
$item = null;
while ($res->fetchInto($item, DB_FETCHMODE_OBJECT)) {
    $itens[] = $item;
}
    
$obj         = current($itens);
$numeroAta      = $obj->ccenpocorg . str_pad($obj->ccenpounid, 2, '0', STR_PAD_LEFT);
$numeroAta      .= "." . str_pad($consultaAta->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $aarpinanon;

// Fornecedor atual
$fornecedorAtual = '';
if(!empty($carpnoseq1)) {
    $sql = ' SELECT fc.nforcrrazs, fc.aforcrccgc, fc.aforcrccpf FROM sfpc.tbataregistroprecointerna arpi ';
    $sql .= ' LEFT JOIN sfpc.tbfornecedorcredenciado fc ON arpi.aforcrsequ = fc.aforcrsequ ';
    $sql .= ' WHERE arpi.carpnosequ = ' . $carpnoseq1;
    $resultado = executarSQL($db, $sql);
    $resultado->fetchInto($fornecedorAtual, DB_FETCHMODE_OBJECT);
}

// Itens
$itens = array();
$sql = "  SELECT iarpn.citarpnuml, iarpn.aitarporde, iarpn.cmatepsequ, iarpn.cservpsequ, ";
$sql .= "   iarpn.aitarpqtor, iarpn.aitarpqtat, iarpn.vitarpvatu, iarpn.vitarpvori, ";
$sql .= "   iarpn.eitarpmarc, iarpn.eitarpmode, m.ematepdesc, s.eservpdesc, iarpn.eitarpdescmat, iarpn.eitarpdescse ";
$sql .= " FROM sfpc.tbitemataregistropreconova iarpn ";
$sql .= " LEFT OUTER JOIN sfpc.tbmaterialportal m ON iarpn.cmatepsequ = m.cmatepsequ ";    				
$sql .= " LEFT OUTER JOIN sfpc.tbservicoportal s ON iarpn.cservpsequ = s.cservpsequ ";
$sql .= " where iarpn.carpnosequ = $ata";
$result = $db->query($sql);
if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: " . __LINE__ . "\nSql: $sql");
} else {
    $Rows = $result->numRows();
    while ($Linha = $result->fetchRow()) {
        $itens[] = $Linha;
    }
}

$titulo .= "          <td colspan=\"4\"><strong>\n";
$titulo .= "$GrupoDesc<br><br>$OrgaoLicitacao<br>";
$titulo .= "          </strong></td>\n";
$tpl->TITULO = $titulo;
$Processo = substr($Processo + 10000, 1);

$dados .= "          <tr>\n";
$dados .= "              <td valign=\"top\" colspan=\"2\"><strong>NÚMERO DA ATA</strong></td>\n";
$dados .= "              <td valign=\"top\" class=\"textonormal\" colspan=\"2\">$numeroAta</td>\n";
$dados .= "          </tr>\n";

$dados .= "          <tr>\n";
$dados .= "              <td valign=\"top\" colspan=\"2\"><strong>PROCESSO LICITATÓRIO</strong></td>\n";
$dados .= "              <td valign=\"top\" class=\"textonormal\" colspan=\"2\">$Processo</td>\n";
$dados .= "          </tr>\n";

$dados .= "          <tr>\n";
$dados .= "              <td valign=\"top\" colspan=\"2\"><strong>ANO DO PROCESSO</strong></td>\n";
$dados .= "              <td valign=\"top\" class=\"textonormal\" colspan=\"2\">$ProcessoAno</td>\n";
$dados .= "          </tr>\n";

$dados .= "          <tr>\n";
$dados .= "              <td valign=\"top\" colspan=\"2\"><strong>COMISSÃO DE LICITAÇÃO</strong></td>\n";
$dados .= "              <td valign=\"top\" class=\"textonormal\" colspan=\"2\">$ComissaoDesc</td>\n";
$dados .= "          </tr>\n";

$dados .= "          <tr>\n";
$dados .= "              <td valign=\"top\" colspan=\"2\"><strong>MODALIDADE</strong></td>\n";
$dados .= "              <td valign=\"top\" class=\"textonormal\" colspan=\"2\">$ModalidadeDesc</td>\n";
$dados .= "          </tr>\n";

$dados .= "          <tr>\n";
$dados .= "              <td valign=\"top\" colspan=\"2\"><strong>OBJETO</strong></td>\n";
$dados .= "              <td valign=\"top\" class=\"textonormal\" colspan=\"2\">$objeto</td>\n";
$dados .= "          </tr>\n";

$dados .= "          <tr>\n";
$dados .= "              <td valign=\"top\" colspan=\"2\"><strong>DATA INICIAL DA ATA</strong></td>\n";
$dados .= "              <td valign=\"top\" class=\"textonormal\" colspan=\"2\">" . ClaHelper::converterDataBancoParaBr($dataInicial) . "</td>\n";
$dados .= "          </tr>\n";

$dados .= "          <tr>\n";
$dados .= "              <td valign=\"top\" colspan=\"2\"><strong>VIGÊNCIA DA ATA</strong></td>\n";
$dados .= "              <td valign=\"top\" class=\"textonormal\" colspan=\"2\">$vigencia</td>\n";
$dados .= "          </tr>\n";

$dados .= "          <tr>\n";
$dados .= "              <td valign=\"top\" colspan=\"2\"><strong>FORNECEDOR ORIGINAL</strong></td>\n";
$dados .= "              <td valign=\"top\" class=\"textonormal\" colspan=\"2\">".$cnpjCpffornecedor." - ".$nomeFornecedor."</td>\n";
$dados .= "          </tr>\n";

if(!empty($fornecedorAtual)) {
    $nomeFornecedorAtual = $fornecedorAtual->nforcrrazs;
    $cnpjCpffornecedorAtual = !empty($fornecedorAtual->aforcrccgc) ? FormataCNPJ($fornecedorAtual->aforcrccgc) : FormataCPF($fornecedorAtual->aforcrccpf);
    $dados .= "          <tr>\n";
    $dados .= "              <td valign=\"top\" colspan=\"2\"><strong>FORNECEDOR ATUAL</strong></td>\n";
    $dados .= "              <td valign=\"top\" class=\"textonormal\" colspan=\"2\">".$cnpjCpffornecedorAtual." - ".$nomeFornecedorAtual."</td>\n";
    $dados .= "          </tr>\n";
}

$tpl->DADOS = $dados;

// Tabela de documentos
if (count($documentos) > 0) {        
    foreach ($documentos as $key => $documento) {
        $documento_key = 'documento'.$ata.'arquivo'.$key;        
        if (!empty($documento[0])) {
            $documentoDecodificado = base64_decode($documento[0]);
            $doc .= '<input type="hidden" value="'.$documentoDecodificado.'" id="'.$documento_key.'">';
            $doc .= " <a href=\"#\" class=\"$documento_key\">$documento[3]</a>";
            $doc .= "<br>";
        } else {
            $doc .= "<img src=\"../midia/disqueteInexistente.gif\" border=\"0\"> $Linha[1] - <b>Arquivo não armazenado</b>";
        }
        
        $doc .= "<br>\n";
    }
} else {
    $doc = "Nenhum documento encontrado";
}

$tpl->DOCUMENTOS = $doc;

// Tabela de itens
if (!empty($itens)) {
    $valor_item = "<tr style='font-weight: bold'> <td>Lote</td> <td>Ordem</td> <td>Tipo</td> <td>Código<br> Reduzido</td> <td>Descrição</td> <td>Descrição<br> Detalhada</td> <td>Marca</td> <td>Modelo</td> <td>Quantidade</td> <td>Valor Unitário</td> <td>Valor Total</td> </tr>";
    foreach ($itens as $key => $item) {
            $valor_item .= "<tr>";
            $tipoItem   = (!is_null($item[2])) ? 'Material' : 'Serviço';
            $codRed     = (!is_null($item[2])) ? $item[2] : $item[3];
            $desc       = (!is_null($item[2])) ? $item[10] : $item[11];
            $descDet    = (!is_null($item[2])) ? $item[12] : $item[13];
            $quantidade = (!!empty($item[5])) ? $item[5] : $item[4];
            $unitario   = (!!empty($item[6])) ? $item[5] : $item[7];

            $valor_item .= "<td style='text-align:center'>".$item[0]."</td>";
            $valor_item .= "<td style='text-align:center'>".$item[1]."</td>";
            $valor_item .= "<td>".$tipoItem."</td>";
            $valor_item .= "<td>".$codRed."</td>";
            $valor_item .= "<td>".$desc."</td>";
            $valor_item .= "<td>".$descDet."</td>";
            $valor_item .= "<td>".$item[8]."</td>";
            $valor_item .= "<td>".$item[9]."</td>";
            $valor_item .= "<td>".converte_valor_licitacao($quantidade)."</td>";
            $valor_item .= "<td>".converte_valor_licitacao($unitario)."</td>";
            $valor_item .= "<td>".converte_valor_licitacao($quantidade * $unitario)."</td>";
            $valor_item .= "</tr>";
    }
} else {
    $valor_item = "<td colspan='11'>Nenhum item cadastrado</td>";
}

$tpl->ITENS = $valor_item;

$tpl->COMISSAO_CODIGO = $ComissaoCodigo;
$tpl->ORGAO_LICITANTE_CODIGO = $OrgaoLicitanteCodigo;
$tpl->MODALIDADE_CODIGO = $ModalidadeCodigo;
$tpl->GRUPO_CODIGO = $GrupoCodigo;
$tpl->OBJETO = $Objeto;

$tpl->COMISSAO_PESQUISA = $ComissaoPesquisa;
$tpl->ORGAO_PESQUISA = $OrgaoPesquisa;
$tpl->OBJETO_PESQUISA = $ObjetoPesquisa;

echo $tpl->show();
