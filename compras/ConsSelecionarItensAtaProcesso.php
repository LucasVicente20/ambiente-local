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
 * @version   Git: v1.8.0-97-g28abed4
 */

 # Acesso ao arquivo de funções #
include "../funcoes.php";

if (! @require_once dirname(__FILE__)."/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 *
 * @author jfsi
 *
 */
class Dados
{
    /**
     * [sqlConsultaItem description]
     * @param  integer $codigoMaterial [description]
     * @return string                 [description]
     */
    private function sqlConsultaItem($codigoMaterial)
    {
        $codigoMaterial = filter_var($codigoMaterial, FILTER_SANITIZE_NUMBER_INT);

        $sql = "
        SELECT
            DISTINCT M.EMATEPDESC ,
            U.EUNIDMSIGL ,
            I.EITESCDESCMAT
        FROM
            SFPC.TBMATERIALPORTAL M ,
            SFPC.TBUNIDADEDEMEDIDA U ,
            SFPC.TBITEMSOLICITACAOCOMPRA I
        WHERE
            M.CMATEPSEQU = %d
            AND U.CUNIDMCODI = M.CUNIDMCODI
            AND M.CMATEPSEQU = I.CMATEPSEQU
        ";       

        return sprintf($sql, $codigoMaterial);
    }

    /**
     * [sqlOrgaoAtaGerada description]
     *
     * @return [type] [description]
     */
    private function sqlItensDaAta($nAta)
    {
        /*if ($_SESSION['TipoSarp'] == 'P') {
            $tabelaJoin = 'sfpc.tbparticipanteatarp pa';
        } else {
            $tabelaJoin = 'sfpc.tbcaronaorgaoexterno pa';
        }

        if ($_SESSION ['TipoAta'] == 'I') {
            $tabela = "sfpc.tbataregistroprecointerna ata";
        } else {
            $tabela = "sfpc.tbataregistroprecoexterna ata";
        }*/

        $sql = "SELECT  ITEMA.CARPNOSEQU, ITEMA.CITARPSEQU, ITEMA.AITARPQTOR, ITEMA.AITARPQTAT, ";
        $sql .= "       ITEMA.AITARPORDE, ITEMA.CMATEPSEQU, ITEMA.CSERVPSEQU, ITEMA.EITARPDESCMAT, ";
        $sql .= "       ITEMA.EITARPDESCSE, MA.EMATEPDESC, SE.ESERVPDESC, ITEMA.VITARPVATU, ";
        $sql .= "       ITEMA.VITARPVORI, ITEMA.EITARPMARC, ITEMA.EITARPMODE, UM.EUNIDMSIGL ";
        $sql .= "   FROM SFPC.TBITEMATAREGISTROPRECONOVA ITEMA ";
        $sql .= "   LEFT JOIN SFPC.TBMATERIALPORTAL MA "; // TBMATERIALPORTAL
        $sql .= "       ON ITEMA.CMATEPSEQU = MA.CMATEPSEQU ";
        $sql .= "   LEFT JOIN SFPC.TBSERVICOPORTAL SE "; // TBSERVICOPORTAL
        $sql .= "       ON ITEMA.CSERVPSEQU = SE.CSERVPSEQU ";
        $sql .= "   LEFT JOIN SFPC.TBUNIDADEDEMEDIDA UM "; // TBUNIDADEDEMEDIDA
        $sql .= "       ON MA.CUNIDMCODI = UM.CUNIDMCODI ";
        $sql .= "   WHERE 1=1 AND ITEMA.CARPNOSEQU = " . $nAta;
        $sql .= "   AND ITEMA.FITARPSITU = 'A' ";
        $sql .= "   GROUP BY ITEMA.CARPNOSEQU, ITEMA.CITARPSEQU, MA.EMATEPDESC, SE.ESERVPDESC, UM.EUNIDMSIGL, ITEMA.AITARPQTOR, ITEMA.AITARPQTAT, 
        ITEMA.AITARPORDE, ITEMA.CMATEPSEQU, ITEMA.CSERVPSEQU, ITEMA.EITARPDESCMAT, ITEMA.EITARPDESCSE, ITEMA.VITARPVATU, ITEMA.EITARPMARC, ITEMA.EITARPMODE, ITEMA.VITARPVATU, ITEMA.VITARPVORI"; // TODO Verificar esse group by pq estava repetindo os registros
        $sql .= "   ORDER BY ITEMA.aitarporde ASC ";
        
        return $sql;
    }
    /**
     * [sqlItemServico description]
     * @param  [type] $itemCodigo [description]
     * @return [type]             [description]
     */
    private function sqlItemServico($itemCodigo)
    {
        $sql = "
        SELECT
            m.eservpdesc
        FROM
            sfpc.tbservicoportal m
        WHERE
            m.cservpsequ = %d
        ";

        return sprintf($sql, $itemCodigo);
    }

    
    /**
     * [consultarValoresMaterial description]
     * @param  [type] $produtoCodigo [description]
     * @return [type]                [description]
     */
    public function consultarValoresMaterial($produtoCodigo)
    {
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $this->sqlConsultaItem($produtoCodigo));

        $resultado->fetchInto($produto, DB_FETCHMODE_OBJECT);

        return $produto;
    }
    /**
     * [consultarValoresServico description]
     * @param  [type] $produtoCodigo [description]
     * @return [type]                [description]
     */
    public function consultarValoresServico($produtoCodigo)
    {
        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $this->sqlItemServico($produtoCodigo));

        $resultado->fetchInto($produto, DB_FETCHMODE_OBJECT);

        return $produto;
    }
    /**
     * [consultarItensDaAta description]
     * @param  [type] $ata [description]
     * @return [type]      [description]
     */
    public function consultarItensDaAta($ata)
    {
        $resultados = array();
        $processos = null;

        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $this->sqlItensDaAta($ata));
        
        while ($resultado->fetchInto($itemAta, DB_FETCHMODE_OBJECT)) {
            $resultados [] = $itemAta;
        }
        
        return $resultados;
    }

    function sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {
        $sql = "
            SELECT
                ccp.ccenpocorg, ccp.ccenpounid, ccp.corglicodi
            FROM sfpc.tbcentrocustoportal ccp
            WHERE 1=1
        ";

        if ($corglicodi != null || $corglicodi != "") {
            $sql .= " AND ccp.corglicodi = %d";
        }

        return sprintf($sql, $corglicodi);
    }

    function consultarDCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi)
    {   
        $resultados = array();
        $centros = null;

        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $this->sqlConsultarCentroDeCustoUsuario($cgrempcodi, $cusupocodi, $corglicodi));

        while ($resultado->fetchInto($centros, DB_FETCHMODE_OBJECT)) {
            $resultados [] = $centros;
        }
        
        return $resultados;               
    }

    function sqlConsultarFornecedorAta($ata, $tipo = 'E')
    {
        $sql  = " SELECT tbata.aforcrsequ, tbfc.aforcrccgc, tbfc.aforcrccpf, tbfc.nforcrrazs  ";
        if($tipo == 'I') {
            $sql .= " FROM sfpc.tbataregistroprecointerna as tbata ";
        } else {
            $sql .= " ,tbata.carpexcodn, tbata.aarpexanon FROM sfpc.tbataregistroprecoexterna as tbata ";
        }
        
        $sql .= "    left join sfpc.tbfornecedorcredenciado as tbfc on tbata.aforcrsequ = tbfc.aforcrsequ
             WHERE carpnosequ = %d             
        ";

        return sprintf($sql, $ata);
    }

    function sqlConsultarProcurarAta($carpnosequ)
    {
        $sql = "
            SELECT * FROM sfpc.tbataregistroprecointerna WHERE carpnosequ = %d             
        ";

        return sprintf($sql, $carpnosequ);
    }

    function procurarAtaInterna($carpnosequ)
    {   
        $resultados = array();
        $ata = null;

        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $this->sqlConsultarProcurarAta($carpnosequ));

        while ($resultado->fetchInto($atas, DB_FETCHMODE_OBJECT)) {
            $resultados [] = $atas;
        }
        
        return $resultados;         
    }

    function procurarFornecedorAta($ata, $tipoAta)
    {   
        $resultados = '';
        $res = null;

        $resultado = executarSQL(ClaDatabasePostgresql::getConexao(), $this->sqlConsultarFornecedorAta($ata, $tipoAta));

        while ($resultado->fetchInto($res, DB_FETCHMODE_OBJECT)) {
            $resultados = $res;
        }
        
        return $resultados;         
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
class GUI extends BaseIntefaceGraficaUsuario
{
    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setTemplate(new TemplatePortal("templates/DetalharItensProcessoAtas.html"));
        $this->setAdaptacao(new Adaptacao());
        $this->getAdaptacao()->setTemplate($this->getTemplate());
    }
    /**
     * [proccessPrincipal description]
     * 
     * TODO exibir o nº correto da ata
     * 
     * @return [type] [description]
     */
    public function proccessPrincipal()
    {       
        $radio      = isset($_POST ['radioItem']) ? $_POST ['radioItem'] : $_GET['ata'];
        $tipoAta    = isset($_GET['TipoAta']) ? $_GET['TipoAta'] : 'I';
        $close      = isset($_GET['close']) ? $_GET['close'] : 0;

        if(isset($_POST ['radioItem'])) {
            $_SESSION['numeroAtaCasoSARP'] = $_POST['numeroAta'][$radio];
        }

        $processo               = $_GET ['processo'];
        $ano                    = $_GET ['ano'];
        $orgao                  = $_GET ['orgao'];
        $grupo                  = $_GET ['grupo'];
        $tipoSarp               = (isset($_GET ['tipoSarp'])) ? $_GET['tipoSarp'] : $_SESSION['tipoSarp'];
        $_SESSION['ataSarp']    = array('ata'=>$radio,'processo'=>$processo,'ano'=>$ano,'orgao'=>$orgao,'grupo'=>$grupo);
        $_SESSION ['ataCasoSARP'] = $radio;
        
        // Remover os itens caso trocar a ata
        if(isset($_SESSION['ataCasoSARP']) && $_SESSION['ataCasoSARP'] != $radio) {
            unset($_SESSION['item']);
        }

        // salvar o carpnosequ na sessão
        // caseo seja externa o campo foi salvo na tela anterior
        $_SESSION['ataCasoSARP'] = $radio;      

        $itensAtasDoProcesso = $this->getAdaptacao()->getNegocio()->getDados()->consultarItensDaAta($radio); // step 1       
        Adaptacao::plotarBlocoAtas($itensAtasDoProcesso, $tipoAta, $tipoSarp);

        // Verificar fornecedor
        $fornecedor = $this->getAdaptacao()->getNegocio()->getDados()->procurarFornecedorAta($radio, $tipoAta);
        $fornecedor_text = '<input type="hidden" name="fornecedor" value="';
        $fornecedor_text .= (!empty($fornecedor->aforcrccgc)) ? $fornecedor->aforcrccgc : $fornecedor->aforcrccpf;
        $fornecedor_text .= '">';

        // Nº da ata
        if($tipoAta == 'I') {
            $ata_ = $this->getAdaptacao()->getNegocio()->getDados()->procurarAtaInterna($radio);
            $ata = current($ata_);
            $centro = $this->getAdaptacao()->getNegocio()->getDados()->consultarDCentroDeCustoUsuario($ata->cgrempcodi, $ata->cusupocodi, $ata->corglicodi);
            $objeto = current($centro);
                        
            $numeroAtaFormatado = $objeto->ccenpocorg . str_pad($objeto->ccenpounid, 2, '0', STR_PAD_LEFT);        
            $numeroAtaFormatado .= "." . str_pad($ata->carpincodn, 4, "0", STR_PAD_LEFT) . "/" . $ata->aarpinanon;
        } else {
            $numeroAtaFormatado = $fornecedor->carpexcodn.'/'.$fornecedor->aarpexanon;
        }

        //$this->getTemplate()->ID_ATA = $radio;
        //$this->getTemplate()->ID_ITEM_ATA = $radio;

        $this->getTemplate()->ACESSO_TITULO_ATA = "ITENS ATA(S) Nº " . $numeroAtaFormatado . " DE REGISTRO DE PREÇO";
        $this->getTemplate()->COLSPAN = 9;
        $this->getTemplate()->FORNECEDOR = $fornecedor_text;
        $this->getTemplate()->NAME_BOTAO = "incluir";
        $this->getTemplate()->VALOR_BOTAO = "Incluir";
        $this->getTemplate()->CLICK_BOTAO = "$('form').submit()";
        $this->getTemplate()->NAME_BOTAO_VOLTAR = "voltar";
        $this->getTemplate()->VALOR_BOTAO_VOLTAR = "Voltar";
        
        // Botão Voltar
        if($close) {
            $this->getTemplate()->CLICK_BOTAO_VOLTAR = "window.close();";
        } else {
            if($tipoAta == 'E') {
                $this->getTemplate()->CLICK_BOTAO_VOLTAR = "location.href='ConsProcessoPesquisar.php??Programa=CadSolicitacaoCompraIncluir&CampoProcessoSARP=NumProcessoSARP&CampoAnoSARP=AnoProcessoSARP&CampoComissaoCodigoSARP=ComissaoCodigoSARP&CampoOrgaoLicitanteCodigoSARP=OrgaoLicitanteCodigoSARP&CampoGrupoEmpresaCodigoSARP=GrupoEmpresaCodigoSARP&CampoCarregaProcessoSARP=CarregaProcessoSARP&TipoAta=E&TipoSarp=C';"; //tipoSarp
            } else {
                $this->getTemplate()->CLICK_BOTAO_VOLTAR = "location.href='ConsSelecionarAtaProcesso.php?processo=".$processo."&ano=".$ano."&orgao=".$orgao."&grupo=".$grupo."&tipoSarp=".$tipoSarp."';";
            }
        }
        
        $this->getTemplate()->NOME_PROGRAMA = "ConsSelecionarItensAtaProcesso";
    }
    /**
     * [processSelecionar description]
     *
     * @return [type] [description]
     */
    public function processIncluir($itens)
    {
        if(!empty($itens['item'])) {            
            foreach ($itens['item'] as $key => $value) {
                $_SESSION['item'][count($_SESSION['item'])] = $itens['descricao'][$key].$GLOBALS['SimboloConcatenacaoArray'].$itens['codigo'][$key].$GLOBALS['SimboloConcatenacaoArray'].$itens['unidade'][$key].$GLOBALS['SimboloConcatenacaoArray'].$itens['tipo'][$key].$GLOBALS['SimboloConcatenacaoArray'].$itens['unitario'][$key].$GLOBALS['SimboloConcatenacaoArray'].$itens['marca'][$key].$GLOBALS['SimboloConcatenacaoArray'].$itens['modelo'][$key].$GLOBALS['SimboloConcatenacaoArray'].$itens['quantidade'][$key].$GLOBALS['SimboloConcatenacaoArray'].$itens['descricaoDetalhada'][$key].$GLOBALS['SimboloConcatenacaoArray'].$itens['fornecedor'];
            }
        }
        echo "<script>opener.document.forms[0].submit();</script>";
        echo "<script>self.close()</script>";
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
class Adaptacao extends AbstractAdaptacao
{
    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setNegocio(new Negocio());
    }
    
    /**
     * [plotarBlocoProcesso description]
     *
     * @param  GUI    $gui
     *                           [description]
     * @param  [type] $processos
     *                           [description]
     * @return [type] [description]
     */
    public function plotarBlocoAtas($itens, $tipoAta = 'I', $tipoSarp = 'C')
    {
        if ($itens == null) {
            return;
        }        

        foreach ($itens as $key => $item) {            
            // Quantidade de itens da ata
            $qtdItensAta = ($item->aitarpqtat != 0) ? $item->aitarpqtat : $item->aitarpqtor;
                        
            if($tipoAta == 'I') {
                if($tipoSarp != 'P') {
                    // Quantidade do total dos caronas internos
                    $qtdOrgaoCaronaInterna = getQtdTotalOrgaoCaronaInterna(ClaDatabasePostgresql::getConexao(), null, $item->carpnosequ, $item->citarpsequ);            
                    // Quantidade do total dos caronas externos
                    $qtdOrgaoCaronaExterna = getQtdTotalOrgaoCaronaExterna(ClaDatabasePostgresql::getConexao(), $item->carpnosequ, $item->citarpsequ);
                    // Quantidade total catona interna por inclusão direta
                    $qtdOrgaoCaronaInternaIncDir = getQtdTotalOrgaoCaronaInternaInclusaoDireta(ClaDatabasePostgresql::getConexao(), $item->carpnosequ, $item->citarpsequ);            

                    // Parâmetro para multiplicação da qtd de carona
                    $fatorMaxCarona = getFatorQtdMaxCarona(ClaDatabasePostgresql::getConexao());
                    $saldoGeralCaronaAta = ($qtdItensAta * $fatorMaxCarona) - ($qtdOrgaoCaronaInterna + $qtdOrgaoCaronaExterna + $qtdOrgaoCaronaInternaIncDir);     

                    // Quantidade total para orgão que vai ser carona
                    $qtdOrgaoSelecionadoCaronaInterna = getQtdTotalOrgaoCaronaInterna(ClaDatabasePostgresql::getConexao(), $_SESSION['centroCustoAnterior'], $item->carpnosequ, $item->citarpsequ);            
                    $qtdLimiteOrgaoCarona = $qtdItensAta - $qtdOrgaoSelecionadoCaronaInterna;
                    if($qtdLimiteOrgaoCarona < $saldoGeralCaronaAta) {
                        $saldoGeralCaronaAta = $qtdLimiteOrgaoCarona;
                    }
                } else {
                    $qtdOrgaoParticipanteInterna = getQtdTotalOrgaoParticipanteInterna(ClaDatabasePostgresql::getConexao(), $_SESSION['centroCustoAnterior'], $item->carpnosequ, $item->citarpsequ);
                    $saldoGeralCaronaAta = $qtdItensAta - $qtdOrgaoParticipanteInterna;        
                }
            } else if($tipoAta == 'E') {
                $qtdOrgaoCaronaInternaAtaExterna = getTotalQtdCaronaInternaAtaExterna(ClaDatabasePostgresql::getConexao(), $_SESSION['centroCustoAnterior'], $item->carpnosequ, $item->citarpsequ);            
                $saldoGeralCaronaAta = $qtdItensAta - $qtdOrgaoCaronaInternaAtaExterna;        
            }
            
            // Verificar quantidade carona para habilitar a seleção do item
            $checkboxItem = $item->aitarporde;
            $checkboxQtd = '---';
            if($saldoGeralCaronaAta > 0) {
                $checkboxItem = '<input type="checkbox" name="item['.$key.']" value="'.$item->citarpsequ.'">' .$item->aitarporde;
                $checkboxQtd = '<input type="text" data-pos="'.$key.'" class="dinheiro4casas verificarQuantidade" name="quantidade['.$key.']" value="0">';
            } else {
                $saldoGeralCaronaAta = 0;
            }
            
            // Tipo
            $itn = (!empty($item->cmatepsequ)) ? "M" : "S";
            $input_tipo = '<input name="tipo['.$key.']" value="'.$itn.'" type="hidden"/>';
            // Descrição
            $des = (!empty($item->cmatepsequ)) ? $item->ematepdesc : $item->eservpdesc;
            $input_desc = '<input name="descricao['.$key.']" value="'.$des.'" type="hidden"/>';
            // Descrição detalhada
            $desc_det = (!empty($item->cmatepsequ)) ? $item->eitarpdescmat : $item->eitarpdescse;
            $input_desc_det = '<textarea style="display:none" name="descricaoDetalhada['.$key.']">' .$desc_det. '</textarea>';
            // Unidade
            $input_und = '<input name="unidade['.$key.']" value="'.$item->eunidmsigl.'" type="hidden"/>';
            // Código
            $cod = (!empty($item->cmatepsequ)) ? $item->cmatepsequ : $item->cservpsequ;
            $cod_2 = $cod . '#' . $item->citarpsequ;
            $input_cod = '<input name="codigo['.$key.']" value="'.$cod_2.'" type="hidden"/>';
            // Valor unitário
            $valor_unitario = ($item->vitarpvatu > 0) ? $item->vitarpvatu : $item->vitarpvori;
            $input_uni = '<input name="unitario['.$key.']" value="'.$valor_unitario.'" type="hidden"/>';
            // Marca
            $marc = (empty($item->eitarpmarc) || $item->eitarpmarc == 'null') ? '' : $item->eitarpmarc;
            $input_marc = '<input name="marca['.$key.']" value="'.$marc.'" type="hidden"/>';
            // Modelo
            $mod = (empty($item->eitarpmode) || $item->eitarpmode == 'null') ? '' : $item->eitarpmode;
            $input_mod = '<input name="modelo['.$key.']" value="'.$mod.'" type="hidden"/>';
            // Carona
            $input_carona = '<input name="carona['.$key.']" value="'.converte_valor_estoques($saldoGeralCaronaAta).'" type="hidden"/>';

            $this->getTemplate()->CHECKBOX_ITEM = $checkboxItem;
            $this->getTemplate()->TIPO_ATA          = (!empty($item->cmatepsequ)) ? "CADUM" : "CADUS";
            $this->getTemplate()->CODIGO_ATA        = (!empty($item->cmatepsequ)) ? $item->cmatepsequ : $item->cservpsequ;
            $this->getTemplate()->DESCRICAO_ATA     = (!empty($item->cmatepsequ)) ? $item->ematepdesc : $item->eservpdesc;
            $this->getTemplate()->DESCRICAO_DET_ATA = (!empty($item->cmatepsequ)) ? $item->eitarpdescmat : $item->eitarpdescse;
            $this->getTemplate()->UNIDADE_ATA       = $item->eunidmsigl; 
            $this->getTemplate()->QUANTIDADE_ITEM_ATA  = converte_valor_estoques($qtdItensAta); 
            $this->getTemplate()->SALDO_CARONA_ATA  = ($saldoGeralCaronaAta <= 0) ? ' --- ' : converte_valor_estoques($saldoGeralCaronaAta);        
            $this->getTemplate()->CHECKBOX_QTD      = $checkboxQtd;
            $this->getTemplate()->INPUT_TIPO        = $input_tipo;
            $this->getTemplate()->INPUT_DESC        = $input_desc;
            $this->getTemplate()->INPUT_DESC_DET    = $input_desc_det;
            $this->getTemplate()->INPUT_UND         = $input_und;
            $this->getTemplate()->INPUT_COD         = $input_cod;
            $this->getTemplate()->INPUT_UNI         = $input_uni;
            $this->getTemplate()->INPUT_MARC        = $input_marc;
            $this->getTemplate()->INPUT_MOD         = $input_mod;
            $this->getTemplate()->INPUT_CARONA      = $input_carona;

            $this->getTemplate()->block("bloco_lista_ata");
        }
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
class Negocio extends BaseNegocio
{
    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->setDados(new Dados());
    }        
}

/**
 * [$app description]
 *
 * @var Negocio
 */
$app = new GUI();

$acao = filter_input(INPUT_POST, 'incluir', FILTER_SANITIZE_STRING);
switch ($acao) {
    case 'Incluir':
        $itens = array(
            'item'      => $_POST['item'],
            'tipo'      => $_POST['tipo'],
            'descricao' => $_POST['descricao'],
            'descricaoDetalhada' => $_POST['descricaoDetalhada'],
            'unidade'   => $_POST['unidade'],
            'value'     => $_POST['value'],
            'codigo'    => $_POST['codigo'],
            'marca'     => $_POST['marca'],
            'modelo'    => $_POST['modelo'],
            'quantidade' => $_POST['quantidade'],
            'unitario'  => $_POST['unitario'],
            'id_ata'    => $_POST['id_ata'],
            'fornecedor'    => $_POST['fornecedor'],
        );
        $app->processIncluir($itens);
        break;
    case 'Principal':
    default:
        $app->proccessPrincipal();
        break;
}

echo $app->getTemplate()->show();