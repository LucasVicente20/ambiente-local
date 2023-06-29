<?php
/**
 * Portal da DGCO
 *
 * PHP version 5.2.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  Pitang Novo Layout
 * @package   App
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @version   Git: $Id:$
 */

if (!@require_once dirname(__FILE__)."/TemplateAppPadrao.php") {
    throw new Exception("Error Processing Request - TemplateAppPadrao.php", 1);
}

$tpl = new TemplateAppPadrao(CAMINHO_SISTEMA."app/templates/ConsComissao.html", "ConsComissao");

$db     = Conexao();

$sql    = "
    SELECT ECOMLIDESC, NCOMLIPRES, ECOMLIMAIL, ECOMLILOCA, ACOMLIFONE, ACOMLINFAX, EGREMPDESC, CCOMLICODI
    FROM SFPC.TBCOMISSAOLICITACAO A
    INNER JOIN SFPC.TBGRUPOEMPRESA B ON A.CGREMPCODI = B.CGREMPCODI
    WHERE A.FCOMLISTAT = 'A' AND A.CCOMLICODI NOT IN (41)
    ORDER BY EGREMPDESC, ECOMLIDESC
";

$result = $db->query($sql);

if (PEAR::isError($result)) {
    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
}

$GrupoDescricao = "";

while ($row =& $result->fetchRow(DB_FETCHMODE_OBJECT)) {
    if ($GrupoDescricao != $row->egrempdesc) {
        $GrupoDescricao = $row->egrempdesc;
        $tpl->GRUPO     = $row->egrempdesc;

        $tpl->block("TABELA_COMISSAO");
        $tpl->block("TABELA_CABECALHO");
    }
    $tpl->COMISSAO    = $row->ecomlidesc;
    $tpl->PRESIDENTE  = $row->ncomlipres;

    $tpl->LOCALIZACAO = $row->ecomliloca;
    $tpl->TELEFONE    = $row->acomlifone;
    $tpl->FAX         = $row->acomlinfax;

    if (!empty($row->ecomlimail)) {
        $url       = 'RotEmailPadrao.php?Comissao='.$row->ccomlicodi;
        $tpl->MAIL = '<td><a href="'.$url.'">'.$row->ecomlimail.'</a></td>';
    } else {
        $tpl->MAIL = '<td>'.$row->ecomlimail.'</td>';
    }

    $tpl->block("TABELA_VALORES");
    $tpl->block("TABELA_CORPO");
}

$tpl->show();

?>
