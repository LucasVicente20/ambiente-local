<?php

/**
 * Portal da DGCO.
 *
 * PHP version 5.2.5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt. If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @author     Pitang Agile TI <contato@pitang.com>
 * @copyright  2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license    http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT: v1.30.4
 */

 // 220038--
 
if (! require_once dirname(__FILE__) . '/../bootstrap.php') {
    throw new Exception('Error Processing Request - Bootstrap', 1);
}

Seguranca();

class RegistroPreco_Dados_CadIncluirIntencaoRegistroPrecoCopiarItens
{
    /**
     *
     * @param integer $sequencialAta
     * @throws InvalidArgumentException
     */
    public function sqlItemAtaNova($sequencialAta)
    {
        if (! filter_var($sequencialAta, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('Informe o $sequencialAta do tipo inteiro');
        }
        
        $sequencialAta = filter_var($sequencialAta, FILTER_SANITIZE_NUMBER_INT);
        
        $sql = "
            SELECT
                i.aitarporde,
                m.ematepdesc,
                s.eservpdesc,
                i.cmatepsequ,
                i.cmatepsequ,
                i.citarpnuml,
                u.eunidmsigl,
                i.aitarpqtor,
                i.vitarpvatu,
                i.vitarpvori,
                i.eitarpmarc,
                i.eitarpmode
            FROM
                sfpc.tbitemataregistropreconova i LEFT OUTER JOIN sfpc.tbmaterialPortal m
                    ON m.cmatepsequ = i.cmatepsequ
                    LEFT OUTER JOIN sfpc.tbservicoportal s ON s.cservpsequ = i.cservpsequ
                    LEFT OUTER JOIN sfpc.tbunidadedeMedida u
                    ON m.cunidmcodi = u.cunidmcodi
                AND m.cmatepsequ = i.cmatepsequ
            WHERE
                i.carpnosequ = %d
        ";
        
        return sprintf($sql, $sequencialAta);
    }
}

class RegistroPreco_Adaptacao_CadIncluirIntencaoRegistroPrecoCopiarItens extends Adaptacao_Abstrata
{
    /**
     *
     * @param integer $numeroAta
     * @throws InvalidArgumentException
     */
    public function consultarItemAta($numeroAta)
    {
        $dados = new RegistroPreco_Dados_CadIncluirIntencaoRegistroPrecoCopiarItens();
        $sql = $dados->sqlItemAtaNova($numeroAta);
        return ClaDatabasePostgresql::executarSQL($sql);
    }
}

class RegistroPreco_UI_CadIncluirIntencaoRegistroPrecoCopiarItens extends UI_Abstrata
{
    /**
     *
     * @param unknown $itens
     */
    public function plotarBlocoItemAta($itens)
    {
        if (! $itens == null) {
            foreach ($itens as $item) {
                $this->getTemplate()->VALOR_ORDEM = $item->aitarporde;
                $this->getTemplate()->VALOR_DESCRICAO = $item->cmatepsequ == null ? $item->eservpdesc : $item->ematepdesc;
                $this->getTemplate()->VALOR_TIPO = $item->cmatepsequ == null ? 'CADUS' : 'CADUM';
                $this->getTemplate()->VALOR_CODIGO = $item->cmatepsequ == null ? $item->cservpsequ : $item->cmatepsequ;
                $this->getTemplate()->VALOR_LOTE = $item->citarpnuml;

                $this->getTemplate()->VALOR_MARCA = ($item->eitarpmarc == 'null') ? '' : $item->eitarpmarc;
                $this->getTemplate()->VALOR_MODELO = ($item->eitarpmode == 'null') ? '' : $item->eitarpmode;

                $this->getTemplate()->VALOR_UNIDADE = $item->eunidmsigl;
                $this->getTemplate()->VALOR_QUANTIDADE = converte_valor_estoques($item->aitarpqtor);
                $this->getTemplate()->VALOR_HOMOLOGADO = converte_valor_licitacao($item->vitarpvori);
                $this->getTemplate()->VALOR_TOTAL_ATUAL = converte_valor_licitacao($item->aitarpqtor * $item->vitarpvori);
                
                $this->getTemplate()->block("BLOCO_RESULTADO_ATAS");
            }
        }
        
        $this->getTemplate()->block("BLOCO_FORMULARIO_MANTER");
    }
}

$app = new RegistroPreco_UI_CadIncluirIntencaoRegistroPrecoCopiarItens();
$template = new TemplatePortal("templates/CadIncluirIntencaoRegistroPrecoCopiarItens.html");
$app->setTemplate($template);
$app->setAdaptacao(new RegistroPreco_Adaptacao_CadIncluirIntencaoRegistroPrecoCopiarItens());
$colecao = $app->getAdaptacao()->consultarItemAta($_GET['ata']);
$app->plotarBlocoItemAta($colecao);
echo $app->getTemplate()->show();
