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
 * @category  Pitang Novo Layout
 *
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT: v1.16.1-42-gf0455ca
 *
 * -----------------------------------------------------------------------------
 * HISTÓRICO DE ALTERAÇÕES NO PROGRAMA
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     06/07/2015
 * Objetivo: CR Redmine 81057 - Fornecedores - CHF - senha - internet
 * Link:     http://redmine.recife.pe.gov.br/issues/81057
 * Versão:   v1.22.0-8-g375a774
 * -----------------------------------------------------------------------------
 * Alterado: Pitang Agile TI
 * Data:     20/07/2015
 * Objetivo: CR Redmine 88231
 * Link:     http://redmine.recife.pe.gov.br/issues/88231
 */
if (!@require_once dirname(__FILE__).'/TemplateAppPadrao.php') {
    throw new Exception('Error Processing Request - TemplateAppPadrao.php', 1);
}

/**
 *
 */
class RotGerarNovaSenha
{
    /**
     * Template.
     *
     * @var TemplateAppPadrao
     */
    private $template;
    /**
     * [__construct description].
     *
     * @param TemplateAppPadrao $template Template do programa
     */
    public function __construct(TemplateAppPadrao $template)
    {
        $this->template = $template;
    }
    /**
     * Get Template do programa.
     *
     * @return TemplateAppPadrao
     */
    protected function getTemplate()
    {
        return $this->template;
    }
    /**
     * Enviar e-mail para fornecedor.
     *
     * @param StdClass $fornecedor [description]
     * @param string   $novaSenha  [description]
     *
     * @return bool [description]
     */
    private function enviarEmail($fornecedor, $novaSenha)
    {
        $razaoFornecedor = $fornecedor->nforcrrazs != null ? $fornecedor->nforcrrazs : $fornecedor->npreforazs;
        $email = $fornecedor->nforcrmail != null ? $fornecedor->nforcrmail : $fornecedor->nprefomail;
        $emailEnviado = EnviaEmail(
			"$email",
        	"Nova senha gerada para o Portal de Compras da Prefeitura do Recife",
        	"Nome/Razão Social: $razaoFornecedor\nNova senha: $novaSenha",
        	"from: portalcompras@recife.pe.gov.br"
        );

        return $emailEnviado;
    }
    /**
     * Consulta o fornecedor pela sua inscrição (CPF|CNPJ).
     *
     * @param string $TipoCnpjCpf     CPF | CNPJ são os valores válidos
     * @param int    $numeroInscricao [description]
     *
     * @return StdClass [description]
     */
    private function consultarFornecedor($TipoCnpjCpf, $numeroInscricao)
    {
        $dao = Conexao();
        $colunaDaConsulta = ($TipoCnpjCpf == 'CPF') ? 'A.AFORCRCCPF' : 'A.AFORCRCCGC';
        $sql = 'SELECT A.AFORCRSEQU, A.AFORCRCCGC, A.AFORCRCCPF, A.NFORCRRAZS, A.NFORCRMAIL ';
        $sql .= "	FROM SFPC.TBFORNECEDORCREDENCIADO A WHERE $colunaDaConsulta = '$numeroInscricao'";
        $fornecedor = $dao->getRow($sql, array(), DB_FETCHMODE_OBJECT);
        $dao->disconnect();

        if (PEAR::isError($fornecedor)) {
            ExibeErroBD(__FILE__."\nLinha: ".__LINE__."\nSql: $sql");
        }

        return $fornecedor;
    }

    /**
     * Consulta o pré-fornecedor pela sua inscrição (CPF|CNPJ).
     *
     * @param string $TipoCnpjCpf     CPF | CNPJ são os valores válidos
     * @param int    $numeroInscricao [description]
     *
     * @return StdClass [description]
     */
    private function consultarPreFornecedor($TipoCnpjCpf, $numeroInscricao)
    {
        $dao = Conexao();
        $colunaDaConsulta = ($TipoCnpjCpf == 'CPF') ? 'A.aprefoccpf' : 'A.aprefoccgc';
        $sql = 'SELECT A.aprefosequ, A.aprefoccgc, A.aprefoccpf, A.npreforazs, A.nprefomail ';
        $sql .= "	FROM SFPC.tbprefornecedor A WHERE $colunaDaConsulta = '$numeroInscricao'";
        $fornecedor = $dao->getRow($sql, array(), DB_FETCHMODE_OBJECT);
        $dao->disconnect();

        if (PEAR::isError($fornecedor)) {
            ExibeErroBD(__FILE__."\nLinha: ".__LINE__."\nSql: $sql");
        }

        return $fornecedor;
    }
    /**
     * Gerar nova senha para o fornecedor.
     *
     * @param string $TipoCnpjCpf     (CPF|CNPJ)
     * @param int    $numeroInscricao [description]
     */
    private function gerarNovaSenha($TipoCnpjCpf, $numeroInscricao)
    {
        $tipo = 1;
        $mensagem = '';
        $fornecedor = $this->consultarFornecedor($TipoCnpjCpf, removeSimbolos($numeroInscricao));

        if (is_null($fornecedor)) {
            //Caso não encontre na tabela de fornecedor, procura na tabela de préfornecedor
            $fornecedor = $this->consultarPreFornecedor($TipoCnpjCpf, removeSimbolos($numeroInscricao));
            if (is_null($fornecedor)) {
                $mensagem = 'Fornecedor não cadastrado';
                $tipo = 0;
                $this->getTemplate()->exibirMensagemFeedback($mensagem, $tipo);
            } else {
                $this->gerarNovaSenhaFornecedor($fornecedor);
            }
        } else {
            $this->gerarNovaSenhaFornecedor($fornecedor);
        }
    }

    private function gerarNovaSenhaFornecedor($fornecedor)
    {
        $isforn = $fornecedor->aforcrsequ != null;

        $aforcrsequ = $fornecedor->aforcrsequ != null ? $fornecedor->aforcrsequ : $fornecedor->aprefosequ;
        $senha = CriaSenha();
        $senhaCript = crypt($senha, 'P');
        $data = date('Y-m-d H:i:s');
        $ontem = new DateTime('now - 1 day');
        $dataOntem = $ontem->format('Y-m-d H:i:s');

             //Caso seja um fornecedor executa esses passos
            if ($isforn) {
                $dao = Conexao();
                $dao->query('BEGIN TRANSACTION');
                $sql = 'UPDATE SFPC.TBFORNECEDORCREDENCIADO ';
                $sql .= "	SET NFORCRSENH = '$senhaCript', tforcrulat = '$data',AFORCRNTEN=0, dforcrexps ='$dataOntem'";
                $sql .= "		WHERE AFORCRSEQU = $aforcrsequ";
                $resultado = $dao->query($sql);

                $ErroPrograma = __FILE__;
                if (PEAR::isError($resultado)) {
                    $dao->query('ROLLBACK');
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                }
            } else {
                //Mas se for um pré fornecedor
             $dao = Conexao();
                $dao->query('BEGIN TRANSACTION');
                $sql = 'UPDATE SFPC.tbprefornecedor ';
                $sql .= "	SET nprefosenh = '$senhaCript', tprefoulat = '$data',aprefonten=0, dprefoexps ='$dataOntem'";
                $sql .= "		WHERE aprefosequ = $aforcrsequ";
                $resultado = $dao->query($sql);

                $ErroPrograma = __FILE__;
                if (PEAR::isError($resultado)) {
                    $dao->query('ROLLBACK');
                    ExibeErroBD("$ErroPrograma\nLinha: ".__LINE__."\nSql: $sql");
                }
            }

        $mensagem = 'Ocorreu uma falha ao enviar o e-mail. Tente novamente mais tarde.';
        $tipo = 2;
        if ($this->enviarEmail($fornecedor, $senha)) {
            $razaoFornecedor = $fornecedor->nforcrrazs != null ? $fornecedor->nforcrrazs : $fornecedor->npreforazs;
            $mensagem = 'Uma nova senha foi enviada para o email do fornecedor '.$razaoFornecedor;
            $tipo = 1;
            $dao->query('COMMIT');
            $dao->query('END TRANSACTION');
            $this->getTemplate()->exibirMensagemFeedback($mensagem, $tipo);
        }

        $dao->disconnect();
    }

    /**
     * [dadosValidos description].
     *
     * @param [type] $dados [description]
     *
     * @return [type] [description]
     */
    private function dadosValidos($dados)
    {
        $dadosValidos = true;
        $numeroInscricao = removeSimbolos(trim($dados['numeroInscricao']));
        $TipoCnpjCpf = $dados['TipoCnpjCpf'];

        $funcao = ($TipoCnpjCpf == 'CPF') ? "valida_{$TipoCnpjCpf}Novo" : "valida_{$TipoCnpjCpf}";
        $numInscrValido = call_user_func($funcao, $numeroInscricao);

        if (!$numInscrValido) {
            $dadosValidos = false;
            $mensagem = 'Informe: <a href="javascript:document.RotGerarNovaSenha.numeroInscricao.focus();" class="titulo2">'.$TipoCnpjCpf.' Válido</a>';
            $this->getTemplate()->exibirMensagemFeedback($mensagem, 2);
        }
        if ($TipoCnpjCpf == 'CNPJ') {
            $this->getTemplate()->CHECKED_CNPJ = 'CHECKED';
        } else {
            $this->getTemplate()->CHECKED_CPF = 'CHECKED';
        }

        return $dadosValidos;
    }
    /**
     * [principal description].
     *
     * @return [type] [description]
     */
    private function principal()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $this->dadosValidos($_POST)) {
            $this->gerarNovaSenha($_POST['TipoCnpjCpf'], $_POST['numeroInscricao']);
        }
    }
    /**
     * [frontController description].
     *
     * @return [type] [description]
     */
    private function frontController()
    {
        $botao = $_POST['Botao'];

        switch ($botao) {
            case 'Principal':
            default:
                $this->principal();
        }
    }
    /**
     * [executar description].
     *
     * @return [type] [description]
     */
    public function executar()
    {
        $this->frontController();
        $this->getTemplate()->show();
    }
    /**
     * Iniciar o programa.
     */
    public static function iniciar()
    {
        $template = new TemplateAppPadrao('templates/RotGerarNovaSenha.html');
        $instancia = new self($template);
        $instancia->executar();
    }
}

RotGerarNovaSenha::iniciar();
