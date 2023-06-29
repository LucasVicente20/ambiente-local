<?php
// 220038--
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
 * @category  Pitang
 * @package   Vendor
 * @author    Pitang Agile TI <contato@pitang.com>
 * @copyright 2014 EMPRESA MUNICIPAL DE INFORMÁTICA - EMPREL
 * @license   http://www.php.net/license/3_01.txt PHP License 3.01
 *
 * @version   GIT: EMPREL-SAD-PORTAL-COMPRAS-REGISTRO-PRECO-BL-FUNC-20160610-1013
 */
class Arquivo
{

    /**
     * [$tamanhoMaximo description]
     *
     * @var [type]
     */
    private $tamanhoMaximo;

    /**
     * [$extensoes description]
     *
     * @var [type]
     */
    private $extensoes;


    /**
     * Configura os arquivos que serão enviados para o servidor
     *
     * @return void
     */
    public function configurarArquivo()
    {
        if (isset($_FILES['fileArquivo'])) {

            $_SESSION['Mens'] = '';
            $arquivo = $_FILES['fileArquivo'];

            $arquivo['name'] = tratarNomeArquivo($arquivo['name']);
            $extensoes = explode(',', strtolower2($this->getExtensoes()));

            // Faz a verificação da extensão do arquivo
            $extensao = strtolower(end(explode('.', $_FILES['fileArquivo']['name'])));
            if (array_search($extensao, $extensoes) === false) {
                $_SESSION['mensagemFeedback'] .= 'Selecione somente documento com a(s) extensão(ões) ' . $this->getExtensoes();
                $_SESSION['Mens'] = 1;
            }

            if (strlen($arquivo['name']) > 100) {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['mensagemFeedback'] .= ', ';
                }

                $_SESSION['mensagemFeedback'] .= 'Nome do Arquivo com até ' . 100 . ' Caracateres ( atualmente com ' . strlen($arquivo['name']) . ' )';
            }

            if (($arquivo['size'] > $this->getTamanhoMaximo()) || ($arquivo['size'] == 0)) {
                if ($_SESSION['Mens'] == 1) {
                    $_SESSION['mensagemFeedback'] .= ', ';
                }
                $Kbytes = (int) $arquivo['size'] * 1024;
                $Kbytes = (int) $Kbytes;
                $_SESSION['mensagemFeedback'] .= "Este arquivo ou é muito grande ou está vazio. Tamanho Máximo: $Kbytes Kb";
            }
           
            if (isset($_SESSION['Mens']) && $_SESSION['Mens'] == '') {
                
                $_SESSION['Arquivos_Upload']['conteudo'][] = file_get_contents($arquivo['tmp_name']);
                $_SESSION['Arquivos_Upload']['nome'][] = $arquivo['name'];
            }
                        
        }

    }//end configurarArquivo()


    /**
     * [getTamanhoMaximo description]
     *
     * @return [type] [description]
     */
    public function getTamanhoMaximo()
    {
        return $this->tamanhoMaximo;
    }

    /**
     * [setTamanhoMaximo description]
     *
     * @param [type] $tamanho
     *            [description]
     */
    public function setTamanhoMaximo($tamanho)
    {
        $this->tamanhoMaximo = $tamanho;
    }

    /**
     * [getExtensoes description]
     *
     * @return [type] [description]
     */
    public function getExtensoes()
    {
        return $this->extensoes;
    }

    /**
     * [setExtensoes description]
     *
     * @param [type] $extensoes
     *            [description]
     */
    public function setExtensoes($extensoes)
    {
        $this->extensoes = $extensoes;
    }
}
