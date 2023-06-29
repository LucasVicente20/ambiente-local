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
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 */

# ------------------------------------------------------------------------------
# Autor:   Caio Coutinho - Pitang Agile TI
# Data :    20/08/2018
# Objetivo: CR 201672 - Ata Externa Criar campo para controle especial da ata
# ------------------------------------------------------------------------------
# Autor:   Caio Coutinho - Pitang Agile TI
# Data :    17/04/2019
# Objetivo: CR 215117
# --------------------------------------------------------------------
# Alterado: Marcello Albuquerque
# Data:     08/05/2021
# Objetivo: CR #248031
# --------------------------------------------------------------------
# Alterado: Marcello Albuquerque
# Data:     13/09/2021
# Objetivo: CR #246174
# ----------------------------------------------------------------------------------------  
# Alterado : Osmar Celestino
# Data: 30/03/2022
# Objetivo: CR #260981 
#---------------------------------------------------------------------------

// 220038--

if (! @require_once dirname(__FILE__) . "/../bootstrap.php") {
    throw new Exception("Error Processing Request - Bootstrap", 1);
}

Seguranca();

/**
 * A camada de Dados conterá o código que irá realizar todo o acesso aos dados.
 */
class RegistroPreco_Dados_CadAtaRegistroPrecoExternaIncluir extends Dados_Abstrata
{

    /**
     * Consulta dados do fornecedor pelo código
     *
     * @param integer $codigo
     *            [description]
     * @return [type] [description]
     */
    public static function consultarFornecedorPorCdigo($codigo)
    {
        $sql = "
		SELECT f.aforcrsequ,f.nforcrrazs ,f.eforcrlogr,f.aforcrccgc
		FROM sfpc.tbfornecedorcredenciado f
		WHERE f.aforcrsequ = $codigo
		";

        $res = ClaDatabasePostgresql::executarSQL($sql);
        ClaDatabasePostgresql::hasError($res);
        return $res;
    }

    /**
     * Retorna dados para o html select Modalidade
     *
     * @return NULL
     */
    public function getTodasModalidade()
    {
        $repositorio = new Negocio_Repositorio_ModalidadeLicitacao();
        return $repositorio->listarTodosAtivos();
    }

    /**
     * Verifica se o CNPJ Existe no cadastro de Fornecedores Credenciados
     *
     * @param unknown $cnpjOrCpf
     * @param unknown $fornecedorOriginal
     * @return unknown
     */
    public static function verificarFornecedorCredenciado($cnpjOrCpf, $fornecedorOriginal)
    {
        $dao = ClaDatabasePostgresql::getConexao();
        $dao->setFetchMode(DB_FETCHMODE_OBJECT);
        $sql = "
            SELECT
                aforcrsequ,
                nforcrrazs,
                eforcrlogr,
                aforcrnume,o
                eforcrcomp,
                eforcrbair,
                nforcrcida,
                cforcresta
            FROM sfpc.tbfornecedorcredenciado
            WHERE ";

        $sql .= ($cnpjOrCpf == 1) ? " aforcrccgc = '%s' " : " aforcrccpf = '%s' ";

        $res = $dao->query(sprintf($sql, $fornecedorOriginal));
        ClaDatabasePostgresql::hasError($res);

        return $res;
    }

    /**
     *
     * @return [type]
     */
    public static function getEntidadeDocumentoAtaRP()
    {
        $entidade = new BaseEntidade();

        return $entidade->getEntidade('sfpc.tbdocumentoatarp');
    }

    /**
     * [getEntidadeItemAtaRegistroPrecoNova description]
     *
     * @return [type] [description]
     */
    public static function getEntidadeItemAtaRegistroPrecoNova()
    {
        $entidade = new BaseEntidade();

        return $entidade->getEntidade('sfpc.tbitemataregistropreconova');
    }

    /**
     * [getUltimoIdAtaRegistroPrecoNova description]
     *
     * @return [type] [description]
     */
    public function getUltimoIdAtaRegistroPrecoNova(&$database)
    {
        return $database->getCol('SELECT max(carpnosequ) FROM sfpc.tbataregistropreconova');
    }

    /**
     *
     * @param DB_psql $database
     * @param stdClass $entidadeAtaRegistro
     * @return unknown
     */
    public function incluirItemAtaExterna(&$database, $entidadeItemAtaRegistroPreco)
    {
        $res = $database->autoExecute('sfpc.tbitemataregistropreconova', (array) $entidadeItemAtaRegistroPreco, DB_AUTOQUERY_INSERT);

        ClaDatabasePostgresql::hasError($res);

        return $res;
    }

    /**
     *
     * @param DB_psql $database
     * @param stdClass $entidadeAtaRegistro
     * @return unknown
     */
    public function incluirAtaExterna(&$database, stdClass $entidadeAtaRegistro)
    {
        $repositorio = new Negocio_Repositorio_AtaRegistroPrecoExterna($database);

        $res = $repositorio->inserir($entidadeAtaRegistro);

        return $res;
    }

    /**
     *
     * @param DB_psql $database
     * @param stdClass $entidade
     * @return unknown
     */
    public function incluirAtaRPNova(&$database, $entidade)
    {
        $repositorio = new Negocio_Repositorio_AtaRegistroPrecoNova($database);
        $valorId = $this->getUltimoIdAtaRegistroPrecoNova($database);
        $entidade->carpnosequ = $valorId[0] + 1;
        $res = $repositorio->inserir($entidade);

        return $res;
    }
}

/**
 * A camada de Negócio conterá o código que irá implementar todas as regras de negócio do sistema.
 *
 * Utiliza serviços da camada de Dados.
 */
class RegistroPreco_Negocio_CadAtaRegistroPrecoExternaIncluir extends Negocio_Abstrata
{

    const COLLECTION_NAME = 'collectionItemAta';

    /**
     *
     * {@inheritdoc}
     *
     * @see Negocio_Abstrata::getDados()
     * @return [Dados_Abstrata] [Retorna qualquer um que extenda de Dados_Abstrata]
     */
    public function getDados()
    {
        $this->setDados(new RegistroPreco_Dados_CadAtaRegistroPrecoExternaIncluir());
        return parent::getDados();
    }

    /**
     * Coleta dados do CadItemIncluir que foram setado em session['item']
     * e move para session['intencaoItem'].
     */
    public function collectorSessionItem($sessionName)
    {
        if (isset($_SESSION['item'])) {
            $countItem = count($_SESSION['item']);
            for ($i = 0; $i < $countItem; ++ $i) {
                $newItem = $_SESSION['item'][$i];
                $_SESSION[$sessionName][] = $newItem;
            }
        }
        // cleaning for news itens
        unset($_SESSION['item']);
    }

    /**
     *
     * @return unknown
     */
    public function mapearAtaRegistroPrecoNova()
    {
        $entidade = ClaDatabasePostgresql::getEntidade('sfpc.tbataregistropreconova');
        $entidade->carpnosequ = null;
        $entidade->carpnotiat = 'E';
        $entidade->tarpnoincl = (string) date('Y-m-d H:i:s');
        $entidade->cusupocodi = (integer) $_SESSION['_cusupocodi_'];
        $entidade->tarpnoulat = (string) date('Y-m-d H:i:s');
        $entidade->farpnotsal = $_SESSION['TipoControle'];

        return $entidade;
    }

    /**
     * Especificacao dos campos do formulario para serem validados
     * pela a RN
     *
     * @return string[][]
     */
    private function especificacaoCampos()
    {
        return array(
            array(
                'campo' => 'NrAtaExterna',
                'text' => 'Número da Ata Externa',
                'href' => 'javascript:document.CadAtaRegistroPrecoExternaIncluir.NrAtaExterna.focus()',
                'finalMsg' => ' deve ser informado'
            ),
            array(
                'campo' => 'AnoAtaExterna',
                'text' => 'Ano da Ata Externa',
                'href' => 'javascript:document.CadAtaRegistroPrecoExternaIncluir.AnoAtaExterna.focus()',
                'finalMsg' => ' deve ser informado'
            ),
            array(
                'campo' => 'ProcessoLicitatorioExterno',
                'text' => 'Processo Licitatório Externo',
                'href' => 'javascript:document.CadAtaRegistroPrecoExternaIncluir.ProcessoLicitatorioExterno.focus()',
                'finalMsg' => ' deve ser informado'
            ),
            array(
                'campo' => 'Modalidade',
                'text' => 'Modalidade',
                'href' => 'javascript:document.CadAtaRegistroPrecoExternaIncluir.Modalidade.focus()',
                'finalMsg' => ' deve ser informada'
            ),
            array(
                'campo' => 'OrgaoGestorAtaExterna',
                'text' => 'Órgão Gestor da Ata Externa',
                'href' => 'javascript:document.CadAtaRegistroPrecoExternaIncluir.OrgaoGestorAtaExterna.focus()',
                'finalMsg' => ' deve ser informado'
            ),
            array(
                'campo' => 'Objeto',
                'text' => 'Objeto',
                'href' => 'javascript:document.CadAtaRegistroPrecoExternaIncluir.Objeto.focus()',
                'finalMsg' => ' deve ser informado'
            ),
            array(
                'campo' => 'DataInicial',
                'text' => 'Data Inicial',
                'href' => 'javascript:document.CadAtaRegistroPrecoExternaIncluir.DataInicial.focus()',
                'finalMsg' => ' deve ser informada'
            ),
            array(
                'campo' => 'Vigencia',
                'text' => 'Vigência',
                'href' => 'javascript:document.CadAtaRegistroPrecoExternaIncluir.Vigencia.focus()',
                'finalMsg' => ' deve ser informada'
            ),
            array(
                'campo' => 'CNPJCPFFornecedorOriginal',
                'text' => 'CNPJ/CPF do Fornecedor Original',
                'href' => 'javascript:document.CadAtaRegistroPrecoExternaIncluir.CNPJCPFFornecedorOriginal[0].focus()',
                'finalMsg' => ' deve ser informado'
            ),
            array(
                'campo' => 'FornecedorOriginal',
                'text' => 'Fornecedor Original',
                'href' => 'javascript:document.CadAtaRegistroPrecoExternaIncluir.FornecedorOriginal.focus()',
                'finalMsg' => ' deve ser informado'
            )
        );
    }

    /**
     */
    private function especificacaoCamposItem()
    {
        return array(
            array(
                'campo' => 'QtdOriginal',
                'text'  => 'Quantidade Original do Item',
                'href'  => 'javascript:document.CadAtaRegistroPrecoExternaIncluir.QtdOriginal.focus()'
            ),
            array(
                'campo' => 'ValorOriginalUnitario',
                'text'  => 'Valor Unitário do Item',
                'href'  => 'javascript:document.CadAtaRegistroPrecoExternaIncluir.ValorOriginalUnitario.focus()'
            ),
            array(
                'campo' => 'Lote',
                'text'  => 'Lote',
                'href'  => 'javascript:document.CadAtaRegistroPrecoExternaIncluir.Lote.focus()'
            ),
            array(
                'campo' => 'Marca',
                'text'  => 'Marca',
                'href'  => 'javascript:document.CadAtaRegistroPrecoExternaIncluir.Marca.focus()'
            ),
            array(
                'campo' => 'Modelo',
                'text'  => 'Modelo',
                'href'  => 'javascript:document.CadAtaRegistroPrecoExternaIncluir.Modelo.focus()'
            ),
            
        );
    }
    private function especificacaoCamposItemServico()
    {
        return array(
            array(
                'campo' => 'QtdOriginal',
                'text'  => 'Quantidade Original do Item',
                'href'  => 'javascript:document.CadAtaRegistroPrecoExternaIncluir.QtdOriginal.focus()'
            ),
            array(
                'campo' => 'ValorOriginalUnitario',
                'text'  => 'Valor Unitário do Item',
                'href'  => 'javascript:document.CadAtaRegistroPrecoExternaIncluir.ValorOriginalUnitario.focus()'
            ),
            array(
                'campo' => 'Lote',
                'text'  => 'Lote',
                'href'  => 'javascript:document.CadAtaRegistroPrecoExternaIncluir.Lote.focus()'
            ),
            
        );
    }
   


    /**
     *
     * @param unknown $tbAtaRPNova
     * @return unknown
     */
    private function mapearAtaRegistroPreco($tbAtaRPNova)
    {

        $fornecedor = filter_input(INPUT_POST, 'codigoFornecedorOriginal');
        $entidade = ClaDatabasePostgresql::getEntidade('sfpc.tbataregistroprecoexterna');
        $entidade->carpnosequ = $tbAtaRPNova->carpnosequ;
        $entidade->aarpexanon = filter_input(INPUT_POST, 'AnoAtaExterna', FILTER_VALIDATE_INT);
        $entidade->carpexcodn = filter_input(INPUT_POST, 'NrAtaExterna');
        $entidade->earpexproc = filter_input(INPUT_POST, 'ProcessoLicitatorioExterno');
        $entidade->cmodlicodi = filter_input(INPUT_POST, 'Modalidade', FILTER_VALIDATE_INT);
        $entidade->earpexorgg = strtoupper(filter_input(INPUT_POST, 'OrgaoGestorAtaExterna'));
        $entidade->earpexobje = strtoupper2(filter_input(INPUT_POST, 'Objeto'));
        $entidade->tarpexdini = filter_input(INPUT_POST, 'DataInicial');
        $entidade->aarpexpzvg = filter_input(INPUT_POST, 'Vigencia', FILTER_VALIDATE_INT);
        $entidade->aforcrsequ = intval($_SESSION['dadosFornecedorOriginal']->aforcrsequ);

        if (!empty($_SESSION['dadosFornecedorAtual'])) {
            $entidade->aforcrseq1 = intval($_SESSION['dadosFornecedorAtual']->aforcrsequ);
        }

        $entidade->farpexsitu = 'I';
        $entidade->cusupocodi = (integer) $_SESSION['_cusupocodi_'];
        $entidade->tarpexnulat = (string) date('Y-m-d H:i:s');

        return $entidade;
    }

    private function inserirDocumentoAta($conexao, $carpnosequ)
    {
        $conexao->query(sprintf("DELETE FROM sfpc.tbdocumentoatarp WHERE carpnosequ = %d", $carpnosequ));
        $documento = $conexao->getRow('SELECT MAX(cdocatsequ) FROM sfpc.tbdocumentoatarp WHERE carpnosequ = ?', array((int) $carpnosequ), DB_FETCHMODE_OBJECT);
        $valorMax = (int) $documento->max + 1;
        $tamanho = count($_SESSION['Arquivos_Upload']['nome']);

        $nomeTabela = 'sfpc.tbdocumentoatarp';
        $entidade = ClaDatabasePostgresql::getEntidade($nomeTabela);
        for ($i = 0; $i < $tamanho; $i ++) {
            $entidade->carpnosequ = (int) $carpnosequ;
            $entidade->cdocatsequ = (int) $valorMax;
            $entidade->edocatnome = $_SESSION['Arquivos_Upload']['nome'][$i];
            $entidade->idocatarqu = bin2hex($_SESSION['Arquivos_Upload']['conteudo'][$i]);
            $entidade->tdocatcada = 'NOW()';
            $entidade->cusupocodi = (int) $_SESSION['_cusupocodi_'];
            $entidade->tdocatulat = 'NOW()';
            $conexao->autoExecute($nomeTabela, (array) $entidade, DB_AUTOQUERY_INSERT);
            $valorMax ++;
        }
    }

    private function validarDocumento($arquivoUlpoad)
    {
        if (! isset($arquivoUlpoad['fileArquivo']['size']) || $arquivoUlpoad['fileArquivo']['size'] == 0) {
            return false;
        }
        return true;
    }

    /**
     *
     * @return boolean
     */
    public function validacao()
    {
        unset($_SESSION['mensagemFeedback']);
        $mensagem = array();

        $elem = new Element('a');
        $elem->set('class', 'titulo2');

        $listaEspecificacao = $this->especificacaoCampos();

        /* Valida os demais campos obrigatório */
        foreach ($listaEspecificacao as $especificacao) {
            $this->validacaoCampos($mensagem, $especificacao);
        }

        $cnpjOrCpf = filter_var($_POST['CNPJCPFFornecedorOriginal'], FILTER_VALIDATE_INT);
        $fornecedorOriginal = filter_var($_POST['FornecedorOriginal']);

        $numeroCnpjOrCpf = preg_replace("/[^0-9]/", "", $fornecedorOriginal);

        if (1 == $cnpjOrCpf && ! valida_CNPJ($numeroCnpjOrCpf)) {
            $elem->set('text', 'CNPJ Válido');
            $elem->set('href', 'javascript:document.CadAtaRegistroPrecoExternaIncluir.FornecedorOriginal.focus()');
            array_push($mensagem, $elem->build() . ' deve ser informado');
        } elseif (2 == $cnpjOrCpf && ! valida_CPF($numeroCnpjOrCpf)) {
            $elem->set('text', 'CPF Válido');
            $elem->set('href', 'javascript:document.CadAtaRegistroPrecoExternaIncluir.FornecedorOriginal.focus()');
            array_push($mensagem, $elem->build() . ' deve ser informado');
        }

        if(empty($_SESSION['dadosFornecedorOriginal'])) {
            array_push($mensagem, 'O fornecedor deve ser informado');
        }

        if (count($_SESSION['Arquivos_Upload']['nome']) == 0){
            array_push($mensagem, 'Documento deve ser informado');
        }
       
        $this->validaItemInformado($mensagem);

        $retorno = true;
        if (!empty($mensagem)) {
            $mensagens = array();
            $cont = 0;
            $retorno = false;
            foreach ($mensagem as $msg) {
                if ($cont == 0) {
                    $msg = "Informe: " . $msg;
                } else {
                    $msg = ", " . $msg;
                }
                array_push($mensagens, "$msg ");
                $cont ++;
            }
            $_SESSION['mensagemFeedback'] = $mensagens;
        }

        return $retorno;
    }

    /**
     * Validação de item do formulario
     *
     * @param unknown $mensagem
     * @param unknown $itensObrigatorios
     */
    public function validaItemInformado(&$mensagem)
    {
        
        $retorno = True;
        $totalItens = count($_POST['CodigoReduzido']);
        
       
        if ($totalItens == 0) {
            $mensagem[] = "É preciso informar item(ns) à ata Externa";
            $retorno = false;
        } else{
            for ($i = 0; $i < $totalItens; $i ++) {
                $tipoItem = $_POST['TipoItem'][$i];
                if($tipoItem == 'M'){
                    $itensObrigatorios = $this->especificacaoCamposItem(); 
                }else{
                    $itensObrigatorios = $this->especificacaoCamposItemServico(); 
                }  
                foreach ($itensObrigatorios as $especificacao) {
                  
                    $campo = $especificacao['campo'];
                    $text = $especificacao['text'];                   
                    $href = 'javascript:document.CadAtaRegistroPrecoExternaIncluir.' . $campo . '[' . ($i + 1) . '].focus()';

                    $msgParteFinal = '';
                    $validarMoeda = false;
                    $valida = true;
                    switch ($campo) {
                        case 'ValorOriginalUnitario':
                            $valida = true;
                        case 'Lote':
                            $msgParteFinal = ') deve ser informado';
                            $validarMoeda = true;
                            $valida = true;
                            break;
                        case 'QtdOriginal':
                            $validarMoeda = true;
                            $valida = true;
                        case 'Marca':
                            if($tipoItem == "M"){   
                                $msgParteFinal = ') deve ser informada';
                                $valida = true;
                                break;
                            }else{
                                $text = str_replace('Marca','',$text);
                                $valida = false;
                            }
                            
                        case 'Modelo': 
                            if($tipoItem =="M"){
                                $msgParteFinal = ') deve ser informado';
                                $valida = true;
                                break;
                            }else{
                                $text = str_replace('Modelo','',$text);
                                $valida = false;
                            }
                           
                    }
                    $lote = ($valida == true) ? '(' . ($i + 1) : '';
                  
                    if (empty($_POST[$campo][$i]) || (moeda2float($_POST[$campo][$i], 4) == 0.0000) && $validarMoeda) {
                        $elem = $this->getElementErro($text .$lote. $msgParteFinal, $href);
                        $mensagem[] .= $elem->build();
                        if($valida == false){
                            $mensagem = str_replace(', e ','',$mensagem);
                        }
                        $retorno = False;
                    }
                }
            }
        }

        return $retorno;
    }

    /**
     * Validacao do campos enviado do formulario
     *
     * @param array $mensagem
     * @param array $especificacaoCampo
     *
     * @return array $mensagem
     */
    public function validacaoCampos(&$mensagem, $especificacaoCampo)
    {
        $campo      = isset($especificacaoCampo['campo']) ? $especificacaoCampo['campo'] : null;
        $text       = isset($especificacaoCampo['text']) ? $especificacaoCampo['text'] : null;
        $href       = isset($especificacaoCampo['href']) ? $especificacaoCampo['href'] : null;
        $finalMsg   = isset($especificacaoCampo['finalMsg']) ? $especificacaoCampo['finalMsg'] : null;

        $camposObrigatorios = array(
            'NrAtaExterna',
            'AnoAtaExterna',
            'ProcessoLicitatorioExterno',
            'Modalidade',
            'OrgaoGestorAtaExterna',
            'Objeto',
            'DataInicial',
            'Vigencia',
            'CNPJCPFFornecedorOriginal',
            'FornecedorOriginal'
        );

        if (in_array($campo, $camposObrigatorios) && $_POST[$campo] == "") {
            $elem = $this->getElementErro($text . $finalMsg, $href);
            $mensagem[] .= $elem->build();
        } else {
            switch ($campo) {
                case 'NrAtaExterna':
                    $
                    $_POST[$campo] = filter_var($_POST[$campo], FILTER_SANITIZE_NUMBER_INT);
                    if (!filter_var(ltrim($_POST[$campo], '0'), FILTER_VALIDATE_INT, array(
                        "min_range" => 1,
                        "max_range" => 999999999
                    ))) {
                        $elem = $this->getElementErro($text . ' deve ser numérico (inteiro) de até 9 dígitos', $href);
                        $mensagem[] .= $elem->build();
                    }
                    break;
                case 'AnoAtaExterna':
                    $_POST[$campo] = filter_var($_POST[$campo], FILTER_SANITIZE_NUMBER_INT);
                    if (! filter_var($_POST[$campo], FILTER_VALIDATE_INT, array(
                        "min_range" => 1,
                        "max_range" => 999999999
                    ))) {
                        $elem = $this->getElementErro($text . ' deve ser numérico (inteiro) de 4 dígitos', $href);
                        $mensagem[] .= $elem->build();
                    }
                    break;
                case 'ProcessoLicitatorioExterno':
                    $_POST[$campo] = filter_var($_POST[$campo], FILTER_SANITIZE_STRING);
                    if ("" == $_POST[$campo]) {
                        $elem = $this->getElementErro($text . ' deve ser numérico (inteiro) de até 9 dígitos', $href);
                        $mensagem[] .= $elem->build();
                    }
                    break;
                case 'Modalidade':
                    $_POST[$campo] = filter_var($_POST[$campo], FILTER_SANITIZE_NUMBER_INT);
                    if (! filter_var($_POST[$campo], FILTER_VALIDATE_INT, array(
                        "min_range" => 1,
                        "max_range" => 999999999
                    ))) {
                        $elem = $this->getElementErro($text . ' deve ser numérico (inteiro) de até 9 dígitos', $href);
                        $mensagem[] .= $elem->build();
                    }
                    break;
                case 'OrgaoGestorAtaExterna':
                    $_POST[$campo] = filter_var($_POST[$campo], FILTER_SANITIZE_STRING);
                    if ("" == $_POST[$campo]) {
                        $elem = $this->getElementErro($text . ' deve ser alfanumérico', $href);
                        $mensagem[] .= $elem->build();
                    }
                    break;
                case 'Objeto':
                    $_POST[$campo] = filter_var($_POST[$campo], FILTER_SANITIZE_STRING);
                    if ("" == $_POST[$campo]) {
                        $elem = $this->getElementErro($text . ' deve ser alfanumérico', $href);
                        $mensagem[] .= $elem->build();
                    }
                    break;
                case 'DataInicial':
                    $_POST[$campo] = filter_var($_POST[$campo], FILTER_SANITIZE_STRING);

                    $_POST[$campo] = filter_var($_POST[$campo], FILTER_VALIDATE_REGEXP, array(
                        "options" => array(
                            "regexp" => '~^\d{2}/\d{2}/\d{4}$~'
                        )
                    ));

                    list ($day, $month, $year) = sscanf($_POST[$campo], '%02d/%02d/%04d');
                    if (empty($day) || empty($month) || empty($year)) {
                        $elem = $this->getElementErro($text . ' deve ser no formato dd/mm/aaaa', $href);
                        $mensagem[] .= $elem->build();
                    }
                    try {
                        $datetime = new DateTime("$year-$month-$day");
                        $_POST[$campo] = $datetime->format('d/m/Y');
                    } catch (Exception $e) {
                        $elem = $this->getElementErro($text . ' deve ser uma data válida', $href);
                        $mensagem[] .= $elem->build();
                    }

                    break;
                case 'Vigencia':
                    $_POST[$campo] = filter_var($_POST[$campo], FILTER_SANITIZE_NUMBER_INT);
                    if (! filter_var($_POST[$campo], FILTER_VALIDATE_INT, array(
                        "min_range" => 1,
                        "max_range" => 2
                    ))) {
                        $elem = $this->getElementErro($text . ' deve ser númerico (inteiro) de 2 dígitos', $href);
                        $mensagem[] .= $elem->build();
                    }
                    break;
                case 'CNPJCPFFornecedorOriginal':
                case 'FornecedorOrignal':
                    break;
            }
        }
        return $mensagem;
    }

    /**
     *
     * @param string $text
     * @param string $href
     * @return Element
     */
    private function getElementErro($text, $href)
    {
        $elem = new Element('a');
        $elem->set('class', 'titulo2');
        $elem->set('text', $text);
        $elem->set('href', $href);
        return $elem;
    }

    /**
     *
     * @param unknown $original
     * @param unknown $atual
     */
    public function verificarFornecedorOriginalDiferenteAtual($original, $atual)
    {
        if (($original == $atual) && (! empty($original) && ! empty($atual))) {
            $mensagem = array();
            $elem = new Element('a');
            $elem->set('class', 'titulo2');
            $elem->set('text', 'Fornecedor atual igual ao fornecedor original');
            $elem->set('href', 'javascript:document.CadAtaRegistroPrecoExternaIncluir.FornecedorAtual.focus()');
            $mensagem[] = $elem->build();

            $_SESSION['mensagemFeedback'] = $mensagem;
        }
    }

    /**
     *
     * @param DB_pgsql $database
     * @return boolean
     */
    public function persistenciaDados(DB_pgsql &$database)
    {
        $tbAtaRPNova = $this->mapearAtaRegistroPrecoNova();
        if (! $this->getDados()->incluirAtaRPNova($database, $tbAtaRPNova)) {
            $_SESSION['mensagemFeedback'][] = 'Não foi possivel adicionar a Ata de Registro de Preço Externa';
            return false;
        }

        $valor                      = $this->getDados()->getUltimoIdAtaRegistroPrecoNova($database);
        $tbAtaRPNova->carpnosequ    = (integer) $valor[0];
        $tbAtaRPExterna             = $this->mapearAtaRegistroPreco($tbAtaRPNova);
        $tbAtaRPExterna->tarpexdini = DataInvertida($tbAtaRPExterna->tarpexdini);

        $this->getDados()->incluirAtaExterna($database, $tbAtaRPExterna);
        
        $itens = $this->recuperarValoresItens($tbAtaRPNova->carpnosequ);
        foreach ($itens as $item) {           
            $this->getDados()->incluirItemAtaExterna($database, $item);
        }

        $this->inserirDocumentoAta($database, $tbAtaRPNova->carpnosequ);
        $commited = $database->commit();

        if ($commited instanceof DB_error) {
            $database->rollback();
            $_SESSION['mensagemFeedback'][] = 'Não foi possivel adicionar a Ata de Registro de Preço Externa';

            return false;
        }

        unset($_SESSION['codigoFornecedorOriginal']);
        unset($_SESSION['codigoFornecedorAtual']);

        return true;
    }

    /**
     *
     * @param unknown $ata
     */
    private function recuperarValoresItens($ata)
    {
        $itens = array();

        $valoresOriginal = $_POST['ValorOriginalUnitario'];
        for ($i = 0; $i < sizeof($valoresOriginal); $i ++) {
            $item = ClaDatabasePostgresql::getEntidade('sfpc.tbitemataregistropreconova');
            $item->carpnosequ = $ata;
            $item->citarpsequ = $i + 1;
            $item->aitarporde = $i + 1;                        
            
            if ($_POST['TipoItem'][$i] == 'S') {
                $item->cservpsequ = (int) $_POST['CodigoReduzido'][$i];
                $item->eitarpdescse = !empty($_POST['DescricaoDetalhada'][$i]) ? strtoupper2($_POST['DescricaoDetalhada'][$i]) : "";
            } else {
                $item->cmatepsequ = (int) $_POST['CodigoReduzido'][$i];
                $item->eitarpdescmat = !empty($_POST['DescricaoDetalhada'][$i]) ? strtoupper2($_POST['DescricaoDetalhada'][$i]) : "";
            }

            $item->aitarpqtor = moeda2float($_POST['QtdOriginal'][$i], 4);
            $item->aitarpqtat = (empty($_POST['QtdAtual'][$i])) ? moeda2float("0,0000", 4) : moeda2float($_POST['QtdAtual'][$i], 4);
            $item->vitarpvori = moeda2float($valoresOriginal[$i], 4);
            $item->vitarpvatu = (empty($_POST['ValorAtualUnitario'][$i])) ? moeda2float("0,0000", 4) : moeda2float($_POST['ValorAtualUnitario'][$i], 4);
            $item->citarpnuml = (int) $_POST['Lote'][$i];
            $item->fitarpsitu = "A";
            $item->titarpincl = "NOW()";
            $item->cusupocodi = (int) $_SESSION['_cusupocodi_'];
            $item->titarpulat = "NOW()";
            $item->fitarpincl = "S";
            $item->fitarpexcl = "N";
            $item->eitarpmarc = strtoupper2($_POST['Marca'][$i]);
            $item->eitarpmode = strtoupper2($_POST['Modelo'][$i]);

            array_push($itens, $item);
        }

        return $itens;
    }

    /**
     *
     * @throws Exception
     */
    public function removeDocumento()
    {
        $idDocumento = filter_input(INPUT_POST, 'documentoExcluir', FILTER_VALIDATE_INT);

        if (! is_int($idDocumento)) {
            throw new Exception("Error Processing Request", 1);
        }

        unset($_SESSION['Arquivos_Upload']['conteudo'][$idDocumento]);
        unset($_SESSION['Arquivos_Upload']['nome'][$idDocumento]);
        $_SESSION['Arquivos_Upload']['nome'] = array_values($_SESSION['Arquivos_Upload']['nome']);
        $_SESSION['Arquivos_Upload']['conteudo'] = array_values($_SESSION['Arquivos_Upload']['conteudo']);
    }
}

/**
 * A camada de Adaptação e Transformação conterá o código que tratará a lógica de apresentação dos resultados
 * das requisições dos usuários e a troca de dados com sistemas externos.
 *
 * Utiliza serviços da camada de Negócio.
 */
class RegistroPreco_Adaptacao_CadAtaRegistroPrecoExternaIncluir extends Adaptacao_Abstrata
{

    const COLLECTION_NAME = 'collectionItemAta';

    /**
     *
     * {@inheritdoc}
     *
     * @see Adaptacao_Abstrata::getNegocio()
     */
    public function getNegocio()
    {
        $this->setNegocio(new RegistroPreco_Negocio_CadAtaRegistroPrecoExternaIncluir());
        return parent::getNegocio();
    }

    /**
     *
     * @throws Exception
     */
    public function fluxoRetirarItem()
    {
        if (isset($_POST['CheckItem'])) {
            ClaItem::removeItemLista($_POST['CheckItem'], self::COLLECTION_NAME);
        } else {
            throw new Exception("Nenhum Item Informado", 1);
        }
    }

    /**
     * [consultarFornecedor description]
     *
     * @return [type] [description]
     */
    public function consultarFornecedorOriginal()
    {
        $cnpjOrCpf          = filter_var($_POST['CNPJCPFFornecedorOriginal'], FILTER_SANITIZE_NUMBER_INT);
        $numeroCnpjOrCpf    = filter_var($_POST['FornecedorOriginal']);


        if (empty($numeroCnpjOrCpf)) {
            return false;
        }

        $numeroCnpjOrCpf = preg_replace("/[^0-9]/", "", $numeroCnpjOrCpf);

        $resultado = FornecedorService::verificarFornecedorCredenciado($cnpjOrCpf, $numeroCnpjOrCpf);

        return current($resultado);
    }

    /**
     * [consultarFornecedor description]
     *
     * @return [type] [description]
     */
    public function consultarFornecedorAtual()
    {
        $cnpjOrCpf          = filter_var($_POST['CNPJCPFFornecedorAtual'], FILTER_SANITIZE_NUMBER_INT);
        $numeroCnpjOrCpf    = filter_var($_POST['FornecedorAtual']);
        $numeroCnpjOrCpfOri = filter_var($_POST['FornecedorOriginal']);

        if (empty($numeroCnpjOrCpf)) {
            return false;
        }

        $numeroCnpjOrCpf = preg_replace("/[^0-9]/", "", $numeroCnpjOrCpf);
        $numeroCnpjOrCpfOriginal = preg_replace("/[^0-9]/", "", $numeroCnpjOrCpfOri);
        $this->getNegocio()->verificarFornecedorOriginalDiferenteAtual($numeroCnpjOrCpfOriginal, $numeroCnpjOrCpf);

        $resultado = FornecedorService::verificarFornecedorCredenciado($cnpjOrCpf, $numeroCnpjOrCpf);
        return current($resultado);
    }

    /**
     *
     * @param stdClass $dados
     */
    public function gerarDetalhesFornecedorOriginal(stdClass $dados)
    {
        $elem = new Element('p');
        $stringHTML = $dados->nforcrrazs . ' <br />' . $dados->eforcrlogr . ', ' . $dados->aforcrnume . ' - ' . $dados->eforcrbair . ' - ' . $dados->nforcrcida . '/' . $dados->cforcresta;
        $elem->set('text', $stringHTML);
        $this->getTemplate()->DETALHES_FORNECEDOR_ORIGINAL = $elem->build();
        $this->getTemplate()->block('BLOCO_EXISTE_FORNECEDOR_ORIGINAL');
    }

    /**
     *
     * @param stdClass $dados
     */
    public function gerarDetalhesFornecedorAtual(stdClass $dados)
    {
        $elem = new Element('p');
        $stringHTML = $dados->nforcrrazs . ' <br />' . $dados->eforcrlogr . ', ' . $dados->aforcrnume . ' - ' . $dados->eforcrbair . ' - ' . $dados->nforcrcida . '/' . $dados->cforcresta;
        $elem->set('text', $stringHTML);
        $this->getTemplate()->DETALHES_FORNECEDOR_ATUAL = $elem->build();
        $this->getTemplate()->block('BLOCO_EXISTE_FORNECEDOR_ATUAL');
    }
}

/**
 * A camada de Interface Gráfica com o Usuário é a camada que conterá o código que irá implementar a interação
 * do sistema com os usuários (telas, relatórios e troca de dados).
 *
 * Utiliza serviços da camada de Adaptação e Transformação.
 */
class RegistroPreco_UI_CadAtaRegistroPrecoExternaIncluir extends UI_Abstrata
{

    const COLLECTION_NAME = 'collectionItemAta';

    /**
     *
     * {@inheritdoc}
     *
     * @see UI_Abstrata::getAdaptacao()
     */
    public function getAdaptacao()
    {
        $this->setAdaptacao(new RegistroPreco_Adaptacao_CadAtaRegistroPrecoExternaIncluir());
        return parent::getAdaptacao();
    }

    /**
     * Set Value Item Servico
     */
    private function setValueItemServico($pos)
    {
        $this->getTemplate()->VALOR_TIPO = 'CADUS';
    }

    /**
     *
     * @param unknown $fornecedor
     */
    public function montarDadosFornecedorOriginal(stdClass $fornecedor)
    {
        if (isset($fornecedor->aforcrsequ)) {
            $this->getTemplate()->CODIGO_FORNECEDOR_ORIGINAL = $fornecedor->aforcrsequ;
            $this->montaDetalhesFornecedor($fornecedor);

            $_SESSION['codigoFornecedorOriginal'] = $fornecedor->aforcrsequ;
        }
    }

    /**
     * Set Value Item Material
     */
    private function setValueItemMaterial($pos)
    {
        $this->getTemplate()->VALOR_TIPO = 'CADUM';
    }

    /**
     * [mapearDadosPostado description]
     *
     * @return [void] [description]
     */
    public function mapearDadosPostado()
    {
        $this->getTemplate()->VALOR_NR_ATA_EXTERNA = filter_var($_POST['NrAtaExterna']);
        $this->getTemplate()->VALOR_ANO_ATA_EXTERNA = filter_var($_POST['AnoAtaExterna']);
        $this->getTemplate()->VALOR_PROCESSO_LICITATORIO = filter_var($_POST['ProcessoLicitatorioExterno']);
        $this->getTemplate()->VALOR_ORGAO_ATA_EXTERNA = filter_var($_POST['OrgaoGestorAtaExterna']);
        $this->getTemplate()->VALOR_OBJETO = filter_var($_POST['Objeto']);
        $this->getTemplate()->VALOR_DATA_INICIAL = filter_var($_POST['DataInicial']);
        $this->getTemplate()->VALOR_VIGENCIA = filter_var($_POST['Vigencia']);

        $this->getTemplate()->VALOR_MARCA = filter_var($_POST['Marca']);
        $this->getTemplate()->VALOR_MODELO = filter_var($_POST['Modelo']);

        $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = $_POST['DescricaoDetalhada'];

        $this->getTemplate()->VALOR_FORNECEDOR_ORIGINAL = filter_var($_POST['FornecedorOriginal']);
        $this->getTemplate()->clear('CNPJCPFFornecedorOriginal');
        if (isset($_POST['CNPJCPFFornecedorOriginal']) && 1 == $_POST['CNPJCPFFornecedorOriginal']) {
            $this->getTemplate()->CNPJ_ORIGINAL_CHECKED = 'checked';
        } elseif (isset($_POST['CNPJCPFFornecedorOriginal']) && 2 == $_POST['CNPJCPFFornecedorOriginal']) {
            $this->getTemplate()->CPF_ORIGINAL_CHECKED = 'checked';
        }

        $this->getTemplate()->VALOR_FORNECEDOR_ATUAL = filter_var($_POST['FornecedorAtual']);
        $this->getTemplate()->clear('CNPJCPFFornecedorAtual');
        if (isset($_POST['CNPJCPFFornecedorAtual']) && 1 == $_POST['CNPJCPFFornecedorAtual']) {
            $this->getTemplate()->CNPJ_ATUAL_CHECKED = 'checked';
        } elseif (isset($_POST['CNPJCPFFornecedorAtual']) && 2 == $_POST['CNPJCPFFornecedorAtual']) {
            $this->getTemplate()->CPF_ATUAL_CHECKED = 'checked';
        }

        $this->getTemplate()->VALOR_CONTROLE = filter_var($_POST['TipoControle']);
        $_SESSION['TipoControle'] = filter_var($_POST['TipoControle']);

        if ($_REQUEST['Botao'] != 'FornecedorOriginal' && $_REQUEST['Botao'] != 'FornecedorAtual') {
            $fornecedorOriginal = $this->getAdaptacao()->consultarFornecedorOriginal();

            if ($fornecedorOriginal != false) {
                $this->montaDetalhesFornecedor($fornecedorOriginal);
            }

            $fornecedorAtual = $this->getAdaptacao()->consultarFornecedorAtual();

            if ($fornecedorAtual != false) {
                $this->montaDetalhesFornecedorAtual($fornecedorAtual);
            }
        }

        if (isset($_REQUEST['codigoFornecedorAtual']) && !empty($_REQUEST['codigoFornecedorAtual'])) {
            $this->getTemplate()->CODIGO_FORNECEDOR_ATUAL = $_REQUEST['codigoFornecedorAtual'];
        }

        if (isset($_REQUEST['codigoFornecedorOriginal']) && !empty($_REQUEST['codigoFornecedorOriginal'])) {
            $this->getTemplate()->CODIGO_FORNECEDOR_ORIGINAL = $_REQUEST['codigoFornecedorOriginal'];
        }
    }

    /**
     */
    public function coletarDocumentosAdicionados()
    {
        if (isset($_SESSION['Arquivos_Upload']['nome'])) {
            $qtdeDocumentos = sizeof($_SESSION['Arquivos_Upload']['nome']);
            for ($i = 0; $i < $qtdeDocumentos; $i ++) {
                $this->getTemplate()->ID_DOCUMENTO = $i;
                $this->getTemplate()->NOME_DOCUMENTO = $_SESSION['Arquivos_Upload']['nome'][$i];
                $this->getTemplate()->block('BLOCO_DOCUMENTO');
            }
        }
    }

    /**
     * [inicializaItem description]
     *
     * @return [type] [description]
     */
    public function inicializaItem()
    {
        $this->getTemplate()->VALOR_MATERIAL = '';
        $this->getTemplate()->VALOR_CODIGO_REDUZIDO = null;
        $this->getTemplate()->VALOR_DESCRICAO = '';
    }

    /**
     * [montaDetalhesFornecedor description]
     *
     * @param GUI $gui
     *            [description]
     * @param [type] $dadosFornecedor
     *            [description]
     * @return [type] [description]
     */
    public function montaDetalhesFornecedor($dadosFornecedor)
    {
        $_SESSION['dadosFornecedorOriginal'] = $dadosFornecedor;
        $elem = new Element('p');
        if (null == $dadosFornecedor || !$dadosFornecedor) {
            $elem->set('text', 'Fornecedor não existe no cadastro');
            $this->getTemplate()->MENSAGEM_NAO_EXISTE_FORNECEDOR_ORIGINAL = $elem->build();
            $this->getTemplate()->block("BLOCO_NAO_EXISTE_FORNECEDOR_ORIGINAL");
            return;
        }

        $stringHTML = $dadosFornecedor->nforcrrazs . ' <br />' . $dadosFornecedor->eforcrlogr . ', ' . $dadosFornecedor->aforcrnume . ' - ' . $dadosFornecedor->eforcrbair . ' - ' . $dadosFornecedor->nforcrcida . '/' . $dadosFornecedor->cforcresta;
        $elem->set('text', $stringHTML);
        $_SESSION['codigoFornecedorOriginal'] = $dadosFornecedor->aforcrsequ;

        $this->getTemplate()->VALORES_AUXILIARES_FORNECEDOR_ORIGINAL = $elem->build();
        $this->getTemplate()->block("BLOCO_CODIGO_FORNECEDOR_ORIGINAL");
    }

    /**
     *
     * @param GUI $gui
     */
    public function collectorListTableIntencaoItem($indice = null)
    {
        global $SimboloConcatenacaoArray, $SimboloConcatenacaoDesc;
        $indice = ! empty($indice) ? $indice : 'collectionItemAta';
        $countItem = count($_SESSION[$indice]);
        
        if ($countItem <= 0) {
            return;
        }

        $this->getTemplate()->block('BLOCO_RETIRAR_ITEM');
        $this->getTemplate()->VALOR_TOTAL_ATUAL = '0,0000';
        $total = 0;
        
        for ($i = 0; $i < $countItem; $i ++) {
            $this->inicializaItem();
            
            $dados = explode($SimboloConcatenacaoArray, $_SESSION[$indice][$i]);
            $this->getTemplate()->VALOR_ITEM = $i + 1;
            $this->getTemplate()->VALOR_MATERIAL = $_SESSION[$indice][$i];

            $descricao = explode($SimboloConcatenacaoDesc, $dados[0]);
            $this->getTemplate()->VALOR_DESCRICAO = $descricao[0];
            $this->getTemplate()->VALOR_CODIGO_REDUZIDO = $dados[1];
            $this->getTemplate()->VALOR_UNIDADE = $dados[2];

            $this->getTemplate()->VALOR_QTD_ORIGINAL = converte_valor_licitacao($_REQUEST['QtdOriginal'][$i]);
            $this->getTemplate()->VALOR_ORIGINAL_UNIT = converte_valor_licitacao($_REQUEST['ValorOriginalUnitario'][$i]);

            $this->getTemplate()->VALOR_MARCA       = $_REQUEST['Marca'][$i];
            $this->getTemplate()->VALOR_MODELO      = $_REQUEST['Modelo'][$i];
            $this->getTemplate()->VALOR_GENERICO    = $dados[4];

            $this->getTemplate()->VALOR_TOTAL_ORIGINAL = converte_valor_licitacao(converte_valor_licitacao($_REQUEST['QtdOriginal'][$i] * $_REQUEST['ValorOriginalUnitario'][$i]));
            $this->getTemplate()->VALOR_LOTE = $_REQUEST['Lote'][$i];
            $this->getTemplate()->VALOR_QTD_ATUAL = converte_valor_licitacao($_REQUEST['QtdAtual'][$i]);
            $this->getTemplate()->VALOR_ATUAL_UNIT = converte_valor_licitacao($_REQUEST['ValorAtualUnitario'][$i]);
            $this->getTemplate()->TIPO_ITEM = $dados[3]; // $dados[3];
            
            
            $totalAtual = converte_valor_licitacao($_REQUEST['QtdAtual'][$i] * $_POST['ValorAtualUnitario'][$i]);
            $this->getTemplate()->VALOR_TOTAL_ATUAL = $totalAtual;
            $total += $totalAtual;
            
            $textarea = "<textarea style='display:none' name='DescricaoDetalhada[]' rows='5'></textarea>";
            if ('S' == $dados[3]) {
                self::setValueItemServico($i);
                $this->getTemplate()->VALOR_TIPO_GRUPO = 'S';
                $textarea = "<textarea name='DescricaoDetalhada[]' rows='5'>". $_REQUEST['DescricaoDetalhada'][$i]."</textarea>";
                $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = $textarea;
            } else {
                self::setValueItemMaterial($i);
                $this->getTemplate()->VALOR_TIPO_GRUPO = 'M';
                if($dados[4] == 'S') {
                    $textarea = "<textarea name='DescricaoDetalhada[]' rows='5'>". $_REQUEST['DescricaoDetalhada'][$i]."</textarea>";
                    $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = $textarea;
                } else {
                    $this->getTemplate()->VALOR_DESCRICAO_DETALHADA = $textarea . ' - ';
                }
            }

            if (isset($_POST['ValorUnitarioEstimado'][$i])) {
                $this->getTemplate()->VALOR_ORIGINAL_UNIT = converte_valor_licitacao($_REQUEST['ValorUnitarioEstimado'][$i]);
            }
            $this->getTemplate()->TITULO_TABELA = "ITENS DA ATA";
            $this->getTemplate()->block('BLOCO_LISTAGEM_ITEM_EXTERNA');
        }
        

        $this->getTemplate()->TOTAL_ATA = converte_valor_licitacao($total);
    }

    /**
     * [plotarTelaPrincipal description]
     *
     * @return [type] [description]
     */
    public function plotarTelaPrincipal()
    {
        $atual = ($_SERVER['REQUEST_METHOD'] === 'POST') ? filter_var($_POST['Modalidade'], FILTER_VALIDATE_INT) : 0;
        $tipo  = ($_SERVER['REQUEST_METHOD'] === 'POST') ? filter_var($_POST['TipoControle'], FILTER_VALIDATE_INT) : 0;

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->garbageCollection();
            $this->carregarVariaveisIniciaisTemplate();
        }

        $this->montarSelect($atual, $tipo);
        $this->getTemplate()->SUPER_TITULO = "ATA EXTERNA - INCLUIR";
        $this->imprimeBlocoMensagem();
    }

    /**
     * [montaDetalhesFornecedor description]
     *
     * @param GUI $gui
     *            [description]
     * @param [type] $dadosFornecedor
     *            [description]
     * @return [type] [description]
     */
    public function montaDetalhesFornecedorAtual($dadosFornecedor)
    {
        $_SESSION['dadosFornecedorAtual'] = $dadosFornecedor;
        $elem = new Element('p');
        if (null == $dadosFornecedor || !$dadosFornecedor) {
            $elem->set('text', 'Fornecedor não existe no cadastro');
            $this->getTemplate()->MENSAGEM_NAO_EXISTE_FORNECEDOR_ATUAL = $elem->build();
            $this->getTemplate()->block("BLOCO_NAO_EXISTE_FORNECEDOR_ATUAL");
            $_SESSION['codigoFornecedorAtual'] = $dadosFornecedor->aforcrsequ;
            return;
        }

        $stringHTML = $dadosFornecedor->nforcrrazs . ' <br />' . $dadosFornecedor->eforcrlogr . ', ' . $dadosFornecedor->aforcrnume . ' - ' . $dadosFornecedor->eforcrbair . ' - ' . $dadosFornecedor->nforcrcida . '/' . $dadosFornecedor->cforcresta;
        $elem->set('text', $stringHTML);
        $_SESSION['codigoFornecedorAtual'] = $dadosFornecedor->aforcrsequ;

        $this->getTemplate()->DETALHES_FORNECEDOR_ATUAL = $elem->build();
        $this->getTemplate()->block("BLOCO_EXISTE_FORNECEDOR_ATUAL");
    }

    /**
     * [montarSelect description]
     *
     * @param GUI $gui
     *            [description]
     * @param integer $atual
     *            [description]
     * @return [type] [description]
     */
    public function montarSelect($atual = 0, $tipo = 0)
    {
        $res      = $this->getAdaptacao()->getNegocio()->getDados()->getTodasModalidade();
        $controle = selectTipoControle();

        foreach ($res as $modalidade) {
            $this->getTemplate()->VALOR_MODALIDADE = $modalidade->cmodlicodi;
            $this->getTemplate()->DESCICAO_MODALIDADE = $modalidade->emodlidesc;

            $this->getTemplate()->clear("VALOR_MODALIDADE_SELECIONADO");
            if ($atual == $modalidade->cmodlicodi) {
                $this->getTemplate()->VALOR_MODALIDADE_SELECIONADO = "selected";
            }
            $this->getTemplate()->block("BLOCO_MODALIDADE");
        }
        
        // Tipo controle
        foreach ($controle as $key => $value) {
            $this->getTemplate()->VALOR_CONTROLE = $key;
            $this->getTemplate()->DESCRICAO_CONTROLE = $value;
            
            $this->getTemplate()->clear("VALOR_CONTROLE_SELECIONADO");
            if ($tipo == $key) {
                $this->getTemplate()->VALOR_CONTROLE_SELECIONADO = "selected";
            }
            $this->getTemplate()->block("BLOCO_TIPOCONTROLE");
        }

    }

    /**
     * [__construct description]
     */
    public function __construct()
    {
        
        
        $template = new TemplatePaginaPadrao("templates/CadAtaRegistroPrecoExternaIncluir.html", "Registro de Preço > Ata Externa > Incluir");
        $this->setTemplate($template);

    }

    public function consultarFornecedorAtual()
    {
        $this->getAdaptacao()->consultarFornecedorAtual();
    }

    public function fluxoRetirarItem()
    {
        $this->getAdaptacao()
            ->getNegocio()
            ->fluxoRetirarItem();
    }

    public function removeDocumento()
    {
        $this->getAdaptacao()
            ->getNegocio()
            ->removeDocumento();
    }

    /**
     * [acaoIncluir description]
     *
     * @return [type] [description]
     */
    public function acaoIncluir()
    {
        if (!$this->getAdaptacao()->getNegocio()->validacao()) {
            $this->buildResultadoIncluir();
            return false;
        }

        $database = ClaDatabasePostgresql::getConexao();
        $database->autoCommit(false);

        if (!$this->getAdaptacao()->getNegocio()->persistenciaDados($database)) {
            $this->buildResultadoIncluir();
            return false;
        }

        ClaItem::clean(self::COLLECTION_NAME);
        $this->setMensagemFeedBack('Ata de Registro de Preço Externa adicionada com sucesso', 1, 0);

        /*
         * Libera espaço na session após os documentos serem adicionados
         */
        unset($_SESSION['Arquivos_Upload']);
        unset($_SESSION['dadosFornecedorOriginal']);
        unset($_SESSION['dadosFornecedorAtual']);

        $this->buildResultadoIncluir();
    }

    /**
     */
    public function buildResultadoIncluir()
    {
        $atual = 0;
        $tipo  = 0;

        $this->carregarVariaveisIniciaisTemplate();

        $this->montarSelect($atual, $tipo);
        $this->getTemplate()->SUPER_TITULO = "ATA EXTERNA - INCLUIR";
        $this->imprimeBlocoMensagem();
    }

    /**
     */
    private function carregarVariaveisIniciaisTemplate()
    {
        $this->getTemplate()->VALOR_NR_ATA_EXTERNA = '';
        $this->getTemplate()->VALOR_ANO_ATA_EXTERNA = '';
        $this->getTemplate()->VALOR_PROCESSO_LICITATORIO = '';
        $this->getTemplate()->VALOR_ORGAO_ATA_EXTERNA = '';
        $this->getTemplate()->VALOR_OBJETO = '';
        $this->getTemplate()->VALOR_DATA_INICIAL = '';
        $this->getTemplate()->VALOR_VIGENCIA = '';

        $this->getTemplate()->VALOR_FORNECEDOR_ORIGINAL = '';
        $this->getTemplate()->CNPJ_ORIGINAL_CHECKED = 'checked';
        $this->getTemplate()->VALOR_FORNECEDOR_ATUAL = '';
        $this->getTemplate()->CNPJ_ATUAL_CHECKED = 'checked';

        $this->getTemplate()->CODIGO_FORNECEDOR_ATUAL = '';

        $this->getTemplate()->CODIGO_FORNECEDOR_ORIGINAL = '';
    }

    public function acaoVoltar()
    {
        $orgao = $_SESSION["orgao"];
        $ano = $_SESSION["ano"];
        $processo = $_SESSION["processo"];

        $uri = 'CadAtaRegistroPrecoInternaCaronaOrgaoExternoManter.php?ano=' . $ano . "&processo=" . $processo . "orgao=" . $orgao;
    }
}


class CadAtaRegistroPrecoExternaIncluir extends ProgramaAbstrato
{

    private function acaoIncluir()
    {
        if (false === $this->getUI()->acaoIncluir()) {
            $this->exibePaginaInicial();
           
        }

        unset($_SESSION['TipoControle']);
    }
    

    /**
     * [insereDocumento description]Método que exibe a tela inicial
     */
    private function exibePaginaInicial()
    {
        $this->getUI()->plotarTelaPrincipal();
        $this->getUI()->coletarDocumentosAdicionados();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->getUI()->mapearDadosPostado();
            $this->getUI()->getAdaptacao()->getNegocio()->collectorSessionItem(RegistroPreco_Negocio_CadAtaRegistroPrecoExternaIncluir::COLLECTION_NAME);
            $this->getUI()->collectorListTableIntencaoItem(RegistroPreco_Negocio_CadAtaRegistroPrecoExternaIncluir::COLLECTION_NAME);
        } 
    }

    /**
     * [insereDocumento description]
     *
     * @return [type] [description]
     */
    private function insereDocumento()
    {
        $arquivoInformado = $_FILES['fileArquivo'];

        if ($arquivoInformado['size'] == 0) {
            $this->getUI()->setMensagemFeedBack("É preciso Informar um Arquivo", 1, 0);
            $this->getUI()->buildResultadoIncluir();
            return;
        }

        $arquivo = new Arquivo();
        $arquivo->setExtensoes('pdf');
        $arquivo->setTamanhoMaximo(2000000000000);

        $arquivo->configurarArquivo();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see ProgramaAbstrato::configuracao()
     */
    protected function configuracao()
    {
        $this->setUI(new RegistroPreco_UI_CadAtaRegistroPrecoExternaIncluir());

        $this->getUI()->getTemplate()->TITULO_TABELA = 'ITENS ATA';
        $this->getUI()->getTemplate()->VALOR_TIPO = 'CADUS';
        $this->getUI()
            ->getTemplate()
            ->block("BLOCO_LISTAGEM_EXTERNA");
        $this->getUI()
            ->getTemplate()
            ->block("BLOCO_INCLUIR_ITEM");
        $this->getUI()->getTemplate()->NOME_PROGRAMA = "CadAtaRegistroPrecoExternaIncluir";
        $this->getUI()
            ->getTemplate()
            ->block("BLOCO_FILE");
        $this->getUI()->getTemplate()->ACAO_SALVAR = 'Incluir';
    }

    public function consultarFornecedorOriginal($continua = true)
    {
        $numeroCnpjOrCpf    = filter_var($_POST['FornecedorOriginal']);
        if (!empty($numeroCnpjOrCpf)){
            $fornecedor = $this->getUI()->getAdaptacao()->consultarFornecedorOriginal();
            $this->getUI()->montaDetalhesFornecedor($fornecedor);
        }
        if ($continua) {
            $this->consultarFornecedorAtual(false);
        }
    }

    private function fluxoRetirarItem()
    {
        try {
            $this->getUI()
                ->getAdaptacao()
                ->fluxoRetirarItem();
        } catch (Exception $e) {
            $this->getUI()->blockErro($e->getMessage());
        }
    }

    /**
     *
     * @param string $continua
     */
    private function consultarFornecedorAtual($continua = true)
    {
        $numeroCnpjOrCpf    = filter_var($_POST['FornecedorAtual']);
        if (!empty($numeroCnpjOrCpf)){
            $fornecedor = $this->getUI()->getAdaptacao()->consultarFornecedorAtual();
                $this->getUI()->montaDetalhesFornecedorAtual($fornecedor);
        }

        if ($continua) {
            $this->consultarFornecedorOriginal(false);
        }
    }

    /**
     */
    private function removeDocumento()
    {
        $this->getUI()
            ->getAdaptacao()
            ->getNegocio()
            ->removeDocumento();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see ProgramaAbstrato::frontController()
     */
    protected function frontController()
    {

        $botao = isset($_POST['Botao']) ? filter_var($_POST['Botao'], FILTER_SANITIZE_STRING) : 'Principal';

        switch ($botao) {
            case 'RetirarItem':
                $this->fluxoRetirarItem();
                $this->exibePaginaInicial();
                break;
            case 'FornecedorOriginal':
                $this->consultarFornecedorOriginal();
                $this->exibePaginaInicial();
                break;
            case 'FornecedorAtual':
                $this->consultarFornecedorAtual();
                $this->exibePaginaInicial();
                break;
            case 'Incluir':
                $this->acaoIncluir();
                break;
            case 'InserirDocumento':
                $this->insereDocumento();
                $this->exibePaginaInicial();
                break;
            case 'Remover':
                $this->removeDocumento();
                $this->exibePaginaInicial();
                break;
            case 'Principal':
            default:
                $this->exibePaginaInicial();
                break;
        }
    }
}

ProgramaAbstrato::iniciar(new CadAtaRegistroPrecoExternaIncluir());
