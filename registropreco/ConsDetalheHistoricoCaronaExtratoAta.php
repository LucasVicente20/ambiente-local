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
 * @category  Pitang Registro Preço
 * @package   registropreco
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 * @version   Git: $Id:$
 */

 // 220038--
 
if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 *
 * @author jfsi
 *        
 */
class RegistroPreco_Dados_ConsDetalheHistoricoCaronaExtratoAta extends Dados_Abstrata
{

    public function sqlConsultarAta($tipoAta, $ata, $material, $servico)
    {
        $sql = "SELECT
    ata.*,
    iarpn.aitarporde,
    iarpn.cmatepsequ,
    iarpn.cservpsequ,
    iarpn.eitarpdescmat,
    iarpn.eitarpdescse,
    iarpn.aitarpqtor,
    iarpn.aitarpqtat,
    (CASE
       when iarpn.cmatepsequ IS NOT NULL
           then (select mp.ematepdesc from sfpc.tbmaterialportal mp
           where mp.cmatepsequ = iarpn.cmatepsequ)
       when iarpn.cservpsequ IS NOT NULL
           then (select sp.eservpdesc from sfpc.tbservicoportal sp
           where sp.cservpsequ = iarpn.cservpsequ)
    END) AS descricaoItem
FROM
    sfpc.tbataregistroprecointerna ata INNER JOIN sfpc.tbitemataregistropreconova iarpn
        ON iarpn.carpnosequ = ata.carpnosequ INNER JOIN sfpc.tbcaronaorgaoexterno coe
        ON coe.carpnosequ = ata.carpnosequ
    INNER JOIN SFPC.tbcaronaorgaoexternoitem coei
    on coei.ccaroesequ = coe.ccaroesequ
    AND coei.citarpsequ = iarpn.citarpsequ
WHERE";
        $sql .= " ata.carpnosequ = " . $ata;
        if ($material != null) {
            $sql .= " and iarpn.cmatepsequ=" . $material;
        } else {
            $sql .= " and iarpn.cservpsequ=" . $servico;
        }
        
        return $sql;
    }

    public function sqlConsultarSCCDoProcesso($ata, $codigoItem, $tipoItem, $comparacao, $orgao = null)
    {
        
        $sql = "    SELECT DISTINCT ATAI.carpnosequ, SOL.csolcosequ, ITEMS.aitescqtso, SOL.tsolcodata, ol.eorglidesc, SOL.corglicodi as orgao_agrupamento, SOL.corglicod1 as orgao_gestor
                FROM sfpc.tbataregistroprecointerna ATAI, sfpc.tbitemataregistropreconova ITEMA,
                    sfpc.tbsolicitacaocompra SOL, sfpc.tbitemsolicitacaocompra ITEMS, sfpc.tborgaolicitante ol ";

                    

        $sql .= "  WHERE  1=1
                AND ATAI.carpnosequ  = $ata
                AND ATAI.carpnosequ  = SOL.carpnosequ
                AND SOL.csolcosequ   = ITEMS.csolcosequ
                AND SOL.ctpcomcodi   = 5
                AND SOL.fsolcorpcp   = 'P'
                AND ol.corglicodi = SOL.corglicodi ";

        if ($tipoItem == 'M') {
            $sql .= " and ITEMS.cmatepsequ = " . $codigoItem;
        } else {
            $sql .= " and ITEMS.cservpsequ = " . $codigoItem;
        }

        
        $sql .= "  AND ITEMS.carpnosequ = ATAI.carpnosequ ";

        echo $sql;

        die;

        return $sql;
    }

    public function sqlConsultarQuantidadeParticipanteItem($numeroAta, $item, $tipoItem)
    {
        $sql = "select iarpn.aitarpqtat, coe.ecaroeorgg, iarpn.aitarpqtor from sfpc.tbcaronaorgaoexterno coe";
        $sql .= " inner join SFPC.tbcaronaorgaoexternoitem coei";
        $sql .= " on coei.ccaroesequ = coe.ccaroesequ";
        $sql .= " inner join sfpc.tbitemataregistropreconova iarpn";
        $sql .= " on coe.carpnosequ = iarpn.carpnosequ";
        $sql .= " and coei.citarpsequ = iarpn.citarpsequ";
        $sql .= " where coe.carpnosequ = $numeroAta";
        if ($tipoItem == 'M') {
            $sql .= " and iarpn.cmatepsequ =$item";
        } else {
            $sql .= " and iarpn.cservpsequ =$item";
        }
        return $sql;
    }
}

/**
 * A camada de Interface Gráfica com o Usuário é a camada que conterá o código que irá implementar a interação
 * do sistema com os usuários (telas, relatórios e troca de dados).
 *
 * Utiliza serviços da camada de Adaptação e Transformação.
 *
 * @author jfsi
 *        
 */
class RegistroPreco_UI_ConsDetalheHistoricoCaronaExtratoAta extends UI_Abstrata
{

    /**
     * [__construct description]
     */
    public function __construct()
    {   
        if($_GET['window']==1 || $_POST['window']==1){
            $template = new TemplateNovaJanela("templates/ConsDetalheHistoricoParticipanteExtratoAta.html", "Registro de Preço > Extrato Atas");
            $this->getTemplate()->WINDOW  = "1";
            $this->getTemplate()->BOTAO_VOLTAR =  "javascript:enviar('Voltar')";
        }else{
            $template = new TemplatePaginaPadrao("templates/ConsDetalheHistoricoParticipanteExtratoAta.html", "Registro de Preço > Extrato Atas");
            $this->getTemplate()->WINDOW  = "0";
            $this->getTemplate()->BOTAO_VOLTAR =  "javascript:enviar('Voltar')";
        }
        $this->setTemplate($template);
        $this->setAdaptacao(new RegistroPreco_Adaptacao_ConsDetalheHistoricoCaronaExtratoAta());
        $this->getTemplate()->NOME_PROGRAMA = "ConsDetalheHistoricoParticipanteExtratoAta";
    }

    public function proccessPrincipal()
    {
        $this->getAdaptacao()->configurarValoresAta($this);
        $this->getAdaptacao()->configurarValoresScc($this);
        $qtdUtilizada = $this->getAdaptacao()->quantidadeUtilizadaTotalParticipante + $this->getAdaptacao()->quantidadeUtilizadaTotalGestor;
        $this->getTemplate()->QTDGESTORPARTICIPANTE = intval($qtdUtilizada);
        $this->getTemplate()->SALDOGESTORPARTICIPANTE = $this->getAdaptacao()->quantidadeAta - $qtdUtilizada;
        
        $ata = $_REQUEST['ata'];
        $tipo = $_REQUEST['tipo'];
        $orgao = $_REQUEST['orgao'];
        $item = $_REQUEST['item'];
        $tipoItem = $_REQUEST['tipoItem'];
        
        $this->getTemplate()->ATA = $ata;
        $this->getTemplate()->TIPO = $tipo;
        $this->getTemplate()->ORGAO = $orgao;
        $this->getTemplate()->ITEM = $item;
        $this->getTemplate()->TIPOITEM = $tipoItem;
    }

    public function processVoltar()
    {
        $uri = "ConsAtaRegistroPrecoExtratoAtaDetalhe.php?window=".$_REQUEST['window']."&processo=6&ano=2015&ata=8&tipo=I&orgao=37";
        header('Location: ' . $uri);
    }

    public function imprimir()
    {
        $ata = $_REQUEST['ata'];
        $tipo = $_REQUEST['tipo'];
        $orgao = $_REQUEST['orgao'];
        $item = $_REQUEST['item'];
        $tipoItem = $_REQUEST['tipoItem'];
        
        $uri = "PdfVisualizarExtratoAtaParticipante.php?ata=$ata&tipo=$tipo&orgao=$orgao&item=$item&tipoItem=$tipoItem";
        header('Location: ' . $uri);
    }
}

/**
 * A camada de Adaptação e Transformação conterá o código que tratará a lógica de apresentação dos resultados
 * das requisições dos usuários e a troca de dados com sistemas externos.
 *
 * Utiliza serviços da camada de Negócio.
 *
 * @author jfsi
 *        
 */
class Mockup
{

    public function __construct()
    {}

    public static function consultarExtratoAta()
    {
        $atasDTO = new stdClass();
        $atasDto->aitarporde = 1;
        $atasDto->eitarpdescse = 'Descrição detalhada do serviço teste';
        $atasDto->eservpdesc = 'Descrição do serviço';
        $atasDto->aitarpqtat = 1524;
        $atasDto->apiarpqtat = 1500;
        $atasDto->carpnosequ = 1;
        $atasDto->alicpoanop = '2015';
        $atasDto->clicpoproc = 545;
        
        return $atasDTO;
    }

    public static function consultarExtratoSCC()
    {
        $resultados = array();
        $sccGestorAta = new stdClass();
        $sccGestorAta->tsolcodata = '31/02/1856';
        $sccGestorAta->aitescqtex = 25;
        $sccGestorAta->csolcosequ = 23;
        $sccGestorAta->corglicodi = 37;
        $sccGestorAta->asolcoanos = 29;
        $sccGestorAta->eorglidesc = 'CEN - CENTRO ESPCIALIZADO EM NADA';
        $resultados[] = $sccGestorAta;
        return $resultados;
    }

    public static function consultarExtratoSCCParticipante()
    {
        $resultados = array();
        $sccParticipanteAta = new stdClass();
        $sccParticipanteAta->tsolcodata = '31/02/1898';
        $sccParticipanteAta->aitescqtex = 25;
        $sccParticipanteAta->csolcosequ = 89;
        $sccParticipanteAta->corglicodi = 37;
        $sccParticipanteAta->asolcoanos = 29;
        $sccParticipanteAta->eorglidesc = 'CEN - CENTRO ESPCIALIZADO EM NADA';
        
        $resultados[] = $sccParticipanteAta;
        return $resultados;
    }
}

class RegistroPreco_Adaptacao_ConsDetalheHistoricoCaronaExtratoAta extends Adaptacao_Abstrata
{

    public $quantidadeUtilizadaTotalParticipante = 0;

    public $quantidadeUtilizadaTotalGestor = 0;

    public $quantidadeAta = 0;

    public function __construct()
    {
        $this->setNegocio(new RegistroPreco_Negocio_ConsDetalheHistoricoCaronaExtratoAta());
    }

    public function configurarValoresAta(UI_Interface $gui)
    {
        $item = $_REQUEST['item'];
        $tipoItem = $_REQUEST['tipoItem'];
        if ($tipoItem == "M") {
            $material = $item;
        } else {
            $servico = $item;
        }
        $extratosAta = $this->getNegocio()->consultarExtratoAta('I', $_REQUEST['ata'], $material, $servico);
        
        if (! empty($extratosAta)) {
            foreach ($extratosAta as $extrato) {
                $this->plotarBlocoResultadoAta($gui, $extrato);
            }
        }
    }

    public function configurarValoresScc(UI_Interface $gui)
    {
        $ata = $_REQUEST['ata'];
        $orgao = $_REQUEST['orgao'];
        $item = $_REQUEST['item'];
        $tipoItem = $_REQUEST['tipoItem'];
        
        $quantidadeOrgao = $this->getNegocio()->consultarQuantidadeParticipanteItem($ata, $item, $tipoItem);
        
        $sccGestor = $this->getNegocio()->consultarSccDoProcesso($gui, $ata, $item, $tipoItem, '=', $orgao);
        $this->plotarBlocoSCCGestor($sccGestor, $quantidadeOrgao);
        $sccParticipante = $this->getNegocio()->consultarSccDoProcesso($gui, $ata, $item, $tipoItem, '!=', $orgao);
        $this->plotarBlocoSCCParticipante($sccParticipante, $quantidadeOrgao);
    }

    public function plotarBlocoSCCGestor(UI_Interface $gui, $sccGestor, $qtdsSolicitada)
    {
        $plotouOrgao = false;
        foreach ($sccGestor as $scc) {
            if (! $plotouOrgao) {
                $gui->getTemplate()->ORGAOGESTOR = $scc->eorglidesc;
                $gui->getTemplate()->TIPO_ORGAO = 'GESTOR';
                $plotouOrgao = true;
            }
            $gui->getTemplate()->NUMEROSCC = getNumeroSolicitacaoCompra(ClaDatabasePostgresql::getConexao(), $scc->csolcosequ);
            $gui->getTemplate()->DATASCC = date("d/m/Y", strtotime($scc->tsolcodata));
            $gui->getTemplate()->QTDUTILIZADA = intval($scc->aitescqtso);
            $qtd_utilizada = $scc->aitescqtso + $qtd_utilizada;
            $gui->getTemplate()->block('bloco_resultado_scc');
        }
        
        $qtdSolicitadaAta = 0;
        foreach ($qtdsSolicitada as $quantidade) {
            $qtdSolicitadaAta = $quantidade->aitarpqtor;
        }
        $gui->getTemplate()->QTD_UTILIZADA = intval($qtd_utilizada);
        $gui->getTemplate()->QTD_SOLICITADA = intval($qtdSolicitadaAta);
        
        $this->quantidadeUtilizadaTotalGestor += intval($qtd_utilizada);
        
        
        $gui->getTemplate()->block('bloco_orgao_scc');
    }

    public function plotarBlocoSCCParticipante($sccParticipante, $quantidades)
    {
        $plotouOrgao = false;
        $ultimoOrgaoPlotado = null;
        
        foreach ($sccParticipante as $scc) {
            if (! $plotouOrgao) {
                $this->getTemplate()->ORGAOGESTOR = $scc->eorglidesc;
                $this->getTemplate()->TIPO_ORGAO = 'PARTICIPANTE';
                $plotouOrgao = true;
            }
            if ($ultimoOrgaoPlotado == $scc->corglicodi) {
                $this->getTemplate()->NUMEROSCC = getNumeroSolicitacaoCompra(ClaDatabasePostgresql::getConexao(), $scc->csolcosequ);
                $this->getTemplate()->DATASCC = date("d/m/Y", strtotime($scc->tsolcodata));
                $this->getTemplate()->QTDUTILIZADA = $scc->aitescqtex;
                $qtd_utilizada = $scc->aitarpqtat + $qtd_utilizada;
                $this->getTemplate()->block('bloco_resultado_scc');
            } else {
                $this->getTemplate()->QTD_UTILIZADA = $qtd_utilizada;
                $this->$quantidadeUtilizadaTotalParticipante += $qtd_utilizada;
                $qtdSolicitada = 0;
                foreach ($quantidades as $quantidade) {
                    if ($quantidade->corglicodi == $ultimoOrgaoPlotado) {
                        $qtdSolicitada = $quantidade->aitarpqtat;
                        break;
                    }
                }
                $this->getTemplate()->QTD_SOLICITADA = $qtdSolicitada;
                $this->getTemplate()->block('bloco_orgao_scc');
                
                /* Depois de fechar o bloco do orgão anterior, é a hora de abrir o bloco inicial */
                $this->getTemplate()->ORGAOGESTOR = $scc->eorglidesc;
                $this->getTemplate()->TIPO_ORGAO = 'PARTICIPANTE';
                $plotouOrgao = true;
                
                $this->getTemplate()->NUMEROSCC = getNumeroSolicitacaoCompra(ClaDatabasePostgresql::getConexao(), $scc->csolcosequ);
                $this->getTemplate()->DATASCC = $scc->tsolcodata;
                $this->getTemplate()->QTDUTILIZADA = $scc->aitarpqtat;
                $qtd_utilizada = $scc->aitarpqtat + $qtd_utilizada;
                $ultimoOrgaoPlotado = $scc->corglicodi;
                $this->getTemplate()->block('bloco_resultado_scc');
            }
        }
    }

    public function plotarBlocoResultadoAta(UI_Interface $gui, $ata)
    {
        $quantidade = $ata->aitarpqtat != 0 ? $ata->apiarpqtat : $ata->aitarpqtor;
        
        $item = $ata->cmatepsequ != null ? $ata->cmatepsequ : $ata->cservpsequ;
        $descricaoCompleta = $ata->cmatepsequ != null ? $ata->eitarpdescmat : $ata->eitarpdescse;
        $tipoServico = $ata->eservpdesc != null ? 'CADUS' : 'CADUM';
        $gui->getTemplate()->NUMEROATA = '154/245.2015';
        $gui->getTemplate()->ORDEM = $ata->aitarporde;
        $gui->getTemplate()->TIPOMATERIAL = $tipoServico;
        $gui->getTemplate()->TIPOMATERIALVALUE = $item;
        $gui->getTemplate()->DESCRICAO = $ata->descricaoitem;
        $gui->getTemplate()->DESCRICAOCOMPLETA = $ata->eitarpdescse;
        $gui->getTemplate()->QUANTIDADE = intval($quantidade);
        $gui->getTemplate()->QTDGESTORPARTICIPANTE = intval($quantidade);
        $gui->getTemplate()->SALDOGESTORPARTICIPANTE = $ata->aitarpqtat - $ata->apiarpqtat;
        $this->quantidadeAta = intval($quantidade);
    }
}

/**
 * A camada de Negócio conterá o código que irá implementar todas as regras de negócio do sistema.
 *
 * Utiliza serviços da camada de Dados.
 *
 * @author jfsi
 *        
 */
class RegistroPreco_Negocio_ConsDetalheHistoricoCaronaExtratoAta extends Negocio_Abstrata
{

    public function __construct()
    {
        $this->setDados(new RegistroPreco_Dados_ConsDetalheHistoricoCaronaExtratoAta());
    }

    public function consultarOrgaosParticipantesAtas()
    {
        $sql = $this->getDados()->sqlOrgaoParticipante();
        $res = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($res);
        
        return $res;
    }

    public function consultarQuantidadeParticipanteItem($ata, $itemAta, $tipoAta)
    {
        $sql = $this->getDados()->sqlConsultarQuantidadeParticipanteItem($ata, $itemAta, $tipoAta);
        $res = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($res);
        
        return $res;
    }

    public function consultarExtratoAta($tipoAta, $ata, $material, $servico)
    {
        $sql = $this->getDados()->sqlConsultarAta($tipoAta, $ata, $material, $servico);
        
        $res = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($res);
        
        return $res;
    }

    public function consultarSccDoProcesso($ata, $item, $tipoItem, $comparacao, $orgao)
    {
        $sql = $this->getDados()->sqlConsultarSCCDoProcesso($ata, $item, $tipoItem, $comparacao, $orgao);
        $res = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($res);
        
        return $res;
    }
}
/**
 * [$app description]
 *
 * @var Negocio
 */
$app = new RegistroPreco_UI_ConsDetalheHistoricoCaronaExtratoAta();

$acao = filter_input(INPUT_POST, 'Botao', FILTER_SANITIZE_STRING);

switch ($acao) {
    case 'Voltar':
        $app->processVoltar();
        break;
    case 'Pesquisar':
        $app->consultarExtratoAta();
        $app->proccessPrincipal();
        break;
    case 'Imprimir':
        $app->imprimir();
    default:
        $app->proccessPrincipal();
        break;
}
$app->getTemplate()->LABEL_SALDO = 'Quantidade Utilizada Carona';
$app->getTemplate()->LABEL_QTD_UTLIZADA = 'Quantidade  Máxima Carona';
$app->getTemplate()->TITULO_SUPERIOR = 'EXTRATO ATAS - HISTÓRICO - CARONA';
echo $app->getTemplate()->show();
