
<?php
#-------------------------------------------------------------------------
# Portal da DGCO
# Programa: AbaContrato.php
# Autor:    Eliakim Ramos | João Madson
# Data:     10/12/2019
# Objetivo: Programa de incluir contrato
#-------------------------------------------------------------------------

require_once "./ClassContratos.php";

session_start();


function fornecedor(){
    
}
function fiscal(){
    
}
function anexarArquivos(){
 if ($retirar){

 }else{

 }   
}
function buscaPost($idReg){
    $db = conexao();
    $sql = "select ectrpcobje, corglicodi, cctrpciden, ectrpcraza, ectrpclogr, ectrpccomp, ectrpcbair, cctrpcesta, nctrpccida, actrpcnuen
            from sfpc.tbcontratosfpc
            where cdocpcsequ = $idReg";

    $resultado = executarSQL($db, $sql);
            while($resultado->fetchInto($retorno, DB_FETCHMODE_OBJECT)){
                $dadosPesquisa = $retorno;
                
            }
            return $dadosPesquisa;
}
if($button == "Salvar"){

    //Codigos para tratamento dos valores inseridos e envio para o banco
    //criar db
    //aqui
    #Primeira parte do sql para identificar as tabelas onde os dados serão inseridos
    $sqlIns = "INSERT INTO tbcontratosfpc
    'actrpcnumc' = $numContratoNF, 'ectrpcnumf' =  $numContratoF, 'actrpcanoc' = $anoContrato, 'csolcosequ' = (sequencial scc), 'aforcrsequ' = (fornecedor original),
    'aforcrseq1' = (caso tenha), 'corglicodi' = $orgLicitante, 'ectrpcobje' = $objetoDesc, 'ectrpcobse' = obs encerramento, 'ectrpcraza' = $razSocial, 
    'cctrpcccep' = (adicionar a busca), 'ectrpclogr' = $Rua, 'actrpcnuen' = $numEnd, 'ectrpccomp' = $complEnd, 'ectrpcbair' = $Bairro, 'nctrpccida' = $Cidade, 
    'cctrpcesta' = $UF, 'ectrpctlct' = $repTelefone, 'fctrpcserc' = $continuo, 'fctrpccons' = $consorcio, 'fctrpcobra' = $obra, 'fctrpcremf' = $flagModoReg, 
    'ectrpcremf' = $ectrpcremf, 'actrpcpzec' = $opcaoExecucaoContrato,  'dctrpcinvg' = ,   'dctrpcfivg' = ,  'dctrpcpbdm' = , 'dctrpcdttr' = ,
    'dctrpcinex' = , 'dctrpcfiex' = , 'cctrpctpfr' = , 'actrpcnucv' = , 'actrpcnuoc' = , 'nctrpcnmrl' = , 'nctrpccgrl' = , 
    'ectrpccpfr' = , 'nctrpcmlrl' = , 'ectrpctlrl' = , 'ectrpcidrl' = , 'nctrpcoerl' = , 'nctrpcufrl' = , 'nctrpccdrl' = , 
    'nctrpcedrl' = , 'nctrpcnarl' = , 'cctrpcecrl' = , 'nctrpcprrl' = , 'nctrpcnmpr' = , 'ectrpccpfp' = , 'nctrpcmlpr' = , 
    'ectrpctlpr' = , 'nctrpcnmgt' = , 'nctrpccpfg' = , 'nctrpcmtgt' = , 'nctrpcmlgt' = , 'ectrpctlgt' = , 'dctrpccada' = , 
    'vctrpcglaa' = , 'vctrpcvlor' = , 'cctrpciden' = , 'nctrpcnmgr' = , 'ictrpcgrnt' = , 'cusupocodi' = , 'tctrpculat' = , 
    'actrpcivie' = , 'actrpcfvfe' = , 'fctrpcosem' = , 'cdocpcseq1' = , 'cdocpcseq2' = , 'cdocpcsequ' = , 'cctrpcopex' = , 
    'fctrpcanti' = , 'ctpcomcodi' = , 'ctipencodi' = , 'vctrpceant' = , 'vctrpcsean' = , 'actrpcnuad' = , 'actrpcnuap' = , 
    'nctrpcnmos' = , 'ictrpcanos' = )";
}
# Exibe Aba Contrato Inclusão - Formulário A #
function ExibeAbaContratoIncluir(){ 
    
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        // var_dump($_POST['origselec-'.$_POST['idregistro']]);
        $Origem            	= $_POST['Origem'];
        $Destino           	= $_POST['Destino'];
                
        //Usa função para buscar dados da scc
        $idReg = $_POST['idregistro'];
        if($idReg){
            $dadosPesquisa = buscaPost($idReg);
            
            $dadosPesquisa->corglicodi;//criar func pra pegar o orgão
            // var_dump($dadosPesquisa);
        }
        $_SESSION['Botao'] = $_POST['Botao'];    
        
        $charRetira = array(".", "/"); //Usada para parametrizar a desformatação dos campos recebidos
        //Pegar os valores dos campos
        $numContratoF /**numero contrato formatado */ = $_POST['numcontrato'];
        $numContratoNf = str_replace($charRetira, "", $numContratoF); /**Retirada da formatação */
        $anoContrato = str_replace($charRetira, "",(strstr($numContratoF, "/"))); /**Retira o ano do numero de contrato*/
        //Inicio da coleta dos dados selecionados em pesquisa via post  MADSON
        $numScc = $_POST['sccselec-'.$_POST['idregistro']];
        $origemScc = $_POST['origselec-'.$_POST['idregistro']];      
        $CpfCnpj = $_POST['cpfselec-'.$_POST['idregistro']];
        //Fim
        $flagCpfCnpj  = $dadosPesquisa->cctrpciden; //Usar para mostrar na tela qual deles é e para a Masc
        $orgLicitante = $dadosPesquisa->corglicodi; //Usar para mostrar na tela qual deles é e para a Masc
        $objetoDesc   = $dadosPesquisa->ectrpcobje;    
        $razSocial    = $dadosPesquisa->ectrpcraza;
        $Rua          = $dadosPesquisa->ectrpclogr;
        $numEnd       = $dadosPesquisa->actrpcnuen;
        $complEnd     = $dadosPesquisa->ectrpccomp;
        $Bairro       = $dadosPesquisa->ectrpcbair;
        $UF           = $dadosPesquisa->cctrpcesta;
        $Cidade       = $dadosPesquisa->nctrpccida;
        
        
        $consorcio = $_POST['fieldConsorcio'];
        $continuo = $_POST['fieldContinuo'];
        $obra = $_POST['obra'];

        if(empty($_POST['regimeExecSel'])){
            $ectrpcremf = $_POST['modoFornecimento'];
            $flagModoReg = 'M';
        }else{
            $ectrpcremf = $_POST['regimeExecSel'];  
            $flagModoReg = 'R'; 
        }
        $opcaoExecucaoContrato = $_POST['opcaoExecucaoContrato'];
        $prazo = $_POST['prazo'];
        $dataPublicacaoDom = $_POST['dataPublicacaoDom'];
        $dataTranscricao = $_POST['dataTranscricao'];

        $vigenciaNdiasIniExec = $_POST['vigenciaNdiasIniExec'];
        $vigenciaNdiasFimExec = $_POST['vigenciaNdiasFimExec'];
        $vigenciaDataInicio = $_POST['vigenciaDataInicio'];
        $vigenciaDataTermino = $_POST['vigenciaDataTermino'];
        $execucaoDataInicio = $_POST['execucaoDataInicio'];
        $execucaoDataTermino = $_POST['execucaoDataTermino'];

        $tipoEspFonteRecurso = $_POST['tipoEspFonteRecurso'];
        $comboGarantia = $_POST['comboGarantia'];
        $repNome = $_POST['repNome'];
        $repCPF = $_POST['repCPF'];
        $repCargo = $_POST['repCargo'];
        $repRG = $_POST['repRG'];
        $repRgOrgao = $_POST['repRgOrgao'];
        $repRgUF = $_POST['repRgUF'];
        $repCidade = $_POST['repCidade'];
        $repEstado = $_POST['repEstado'];
        $repNacionalidade = $_POST['repNacionalidade'];
        $repEstCiv = $_POST['repEstCiv'];
        $repProfissao = $_POST['repProfissao'];
        $repEmail = $_POST['repEmail'];
        $repTelefone = $_POST['repTelefone'];
        $prepNome = $_POST['prepNome'];
        $prepCPF = $_POST['prepCPF'];
        $prepEmail = $_POST['prepEmail'];
        $prepTelefone = $_POST['prepTelefone'];
        $gestorNome = $_POST['gestorNome'];
        $gestorMatricula = $_POST['gestorMatricula'];
        $gestorCPF = $_POST['gestorCPF'];
        $gestorEmail = $_POST['gestorEmail'];
        $gestorTelefone = $_POST['gestorTelefone'];
    }
   $teste = $_SESSION['dadosTabela'];
    ?>
    <html>
    <?php
    $ObjContrato = new Contrato();
    $dadosTipoCompra = $ObjContrato->ListTipoCompra();
        # Carrega o layout padrão #
        layout();
    ?>
    <script language="javascript" src="../janela.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>​
    <script language="javascript" type="">
        
        function AbreJanela(url,largura,altura) {
            window.open(url,'detalhe','status=no,scrollbars=yes,left=70,top=130,width='+largura+',height='+altura);
        }
        function Submete(Destino){
            document.CadContratoIncluir.Destino.value = Destino;
            document.CadContratoIncluir.submit();
        }
        function enviar(valor){
            document.CadPregaoPresencialSessaoPublica.Botao.value = valor;
            document.CadPregaoPresencialSessaoPublica.submit();
        }
        function enviarDestino(valor, Destino){
            document.CadPregaoPresencialSessaoPublica.Destino.value = Destino;
            document.CadPregaoPresencialSessaoPublica.Botao.value = valor;
            document.CadPregaoPresencialSessaoPublica.submit();
        }
        function datasCalc(dataVig){ 
            if (dataVig != null && dataVig != "") {
                var tipoExec = $("#opcaoExecucaoContrato").val();
                var prazoExec = isNaN(parseInt($("#prazo").val())) ? 0 : parseInt($("#prazo").val());
                var prazIni = isNaN(parseInt($("#iniVigenciaNdiasIniExec").val())) ? 0 : parseInt($("#iniVigenciaNdiasIniExec").val());
                var prazFim = isNaN(parseInt($("#fimVigenciaNdiasFimExec").val())) ? 0 : parseInt($("#fimVigenciaNdiasFimExec").val());
                console.log(prazoExec);
            //   Tratando a data
               var data = new Date();
                data.setMonth(dataVig[1] -1); // Janeiro
                data.setFullYear(dataVig[2]);
                data.setDate(dataVig[0]);
            // Somando Número de dias entre Início de Vigência e Início de Execução
                data.setDate(data.getDate() + prazIni);
                 if(data.getMonth() <= 9){
                    var mesCorrigido = '0' + (data.getMonth()+1);
                 }else{
                        var mesCorrigido = (data.getMonth());    
                    }
                var dataExecucao = data.getDate() + '/' + mesCorrigido + '/' + data.getFullYear();
                $("#execucaoDataInicio").val(dataExecucao);
            // gerando data final da execução
                 if(tipoExec === "Dias"){
                    data.setDate(data.getDate() + prazoExec);
                    if(data.getMonth() <= 9){
                        var mesCorrigido = '0' + (data.getMonth()+1);
                    }else{
                        var mesCorrigido = (data.getMonth());    
                    }
                    var dataExecucao = data.getDate() + '/' + mesCorrigido + '/' + data.getFullYear();
                    $("#execucaoDataTermino").val(dataExecucao);
                 }else if(tipoExec === "Meses"){
                    data.setMonth(data.getMonth() + prazoExec);
                    if(data.getMonth() <= 9){
                         var mesCorrigido = '0' + (data.getMonth()+1);
                    }else{
                        var mesCorrigido = (data.getMonth());    
                    }
                    var dataExecucao = parseInt(data.getDate()) + '/' + mesCorrigido + '/' + parseInt(data.getFullYear());
                    $("#execucaoDataTermino").val(dataExecucao);
                 }else{
                     data.setDate(data.getDate() + prazIni);
                    $("#execucaoDataTermino").val(dataExecucao);
                 }
                
                 
            // somando Número de dias entre Final de Execução e Final de Vigência
                data.setDate(data.getDate() + prazFim); 
                if(data.getMonth() <= 9){
                    var mesCorrigido = '0' + (data.getMonth()+1);
                }
                var dataFimVig =  data.getDate() + '/' + mesCorrigido + '/' + data.getFullYear();
                $("#vigenciaDataTermino").val(dataFimVig);
                 
            }else{
                return;
            }
        }
        function retiraFornecedor(dado){
            $.post("postDados.php",{op:"ExcluirForneModal",info:dado}, function(data){
                    ObjJson = JSON.parse(data);
                    $(".dadosFornec").html(CriatableModal(ObjJson));
                    $("#shownewfornecedores").html(CriatableView(ObjJson));
			 });
        }
        function CriatableModal(objJson){
                    tabelaHtml = '<table border="1" bordercolor="#75ADE6">';
                    tabelaHtml += '<thead>';
                    tabelaHtml += '<tr>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'Identificador do Contratado';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'Razão Social';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += '';
                    tabelaHtml += '</td>';
                    tabelaHtml += '</tr>';
                    tabelaHtml += '</thead>';
                    tabelaHtml += '<tbody>';
                    for(i in objJson){
                        if(objJson[i].aforcrsequ){
                            tabelaHtml += '<tr>';
                            tabelaHtml += '<td>';
                            tabelaHtml += '<input type="hidden" name="codFornecedorModalPesquisa[]" value="'+objJson[i].aforcrsequ+'">';
                            tabelaHtml +=  (objJson[i].aforcrccpf == '')?objJson[i].aforcrccpf:objJson[i].aforcrccgc;
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml += objJson[i].nforcrrazs;
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml += '<button type="button" onclick="retiraFornecedor(\''+objJson[i].aforcrsequ+'\')"><img src="../midia/excluirfornec.png" alt="Excluir" /> </button>';
                            tabelaHtml += '</td>';
                            tabelaHtml += '</tr>';
                        }
                    }
                    tabelaHtml += '</tbody>';
                    tabelaHtml += '</table>';
                return tabelaHtml;
        }
        function  CriatableView(objJson){
                    tabelaHtml = '<table border="1" bordercolor="#75ADE6">';
                    tabelaHtml += '<thead>';
                    tabelaHtml += '<tr>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'Identificador do Contratado';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'Razão Social';
                    tabelaHtml += '</td>';
                    tabelaHtml += '</tr>';
                    tabelaHtml += '</thead>';
                    tabelaHtml += '<tbody>';
                    for(i in objJson){
                        if(objJson[i].aforcrsequ){
                            tabelaHtml += '<tr>';
                            tabelaHtml += '<td>';
                            tabelaHtml += '<input type="hidden" name="codFornecedorModalPesquisa[]" value="'+objJson[i].aforcrsequ+'">';
                            tabelaHtml +=  (objJson[i].aforcrccpf == '')?objJson[i].aforcrccpf:objJson[i].aforcrccgc;
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml += objJson[i].nforcrrazs;
                            tabelaHtml += '</td>';
                            tabelaHtml += '</tr>';
                        }
                    }
                    tabelaHtml += '</tbody>';
                    tabelaHtml += '</table>';
                return tabelaHtml;
        }
        function CriaTabelaFiscal(objJson){
            tabelaHtml = '<table border="1" bordercolor="#75ADE6">';
                    tabelaHtml += '<thead>';
                    tabelaHtml += '<tr>';
                    tabelaHtml += '<td>';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'Tipo Fiscal';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'Nome';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'Matrícula';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'CPF';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'Ent. Compet.';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'Registro ou Insc.';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'E-mail';
                    tabelaHtml += '</td>';
                    tabelaHtml += '<td>';
                    tabelaHtml += 'Tel.';
                    tabelaHtml += '</td>';
                    tabelaHtml += '</tr>';
                    tabelaHtml += '</thead>';
                    tabelaHtml += '<tbody>';
                    for(i in objJson){
                        if(objJson[i].cfiscdcpff){
                            tabelaHtml += '<tr>';
                            tabelaHtml += '<td>';
                            tabelaHtml += '<input type="radio" name="cpfFiscal" value="'+objJson[i].cfiscdcpff+'">';
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  (objJson[i].nfiscdtipo != '')?objJson[i].nfiscdtipo:'';
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  (objJson[i].nfiscdnmfs != '')?objJson[i].nfiscdnmfs:'';
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  (objJson[i].efiscdmtfs != '')?objJson[i].efiscdmtfs:'';
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  (objJson[i].cfiscdcpff != '')?objJson[i].cfiscdcpff:'';
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  (objJson[i].nfiscdencp != '')?objJson[i].nfiscdencp:'';
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  (objJson[i].efiscdrgic != '')?objJson[i].efiscdrgic:'';
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  (objJson[i].nfiscdmlfs != '')?objJson[i].nfiscdmlfs:'';
                            tabelaHtml += '</td>';
                            tabelaHtml += '<td>';
                            tabelaHtml +=  (objJson[i].efiscdtlfs != '')?objJson[i].efiscdtlfs:'';
                            tabelaHtml += '</td>';
                            tabelaHtml += '</tr>';
                        }
                    }
                    tabelaHtml += '</tbody>';
                    tabelaHtml += '<tfoot>';
                    tabelaHtml += '<tr>';
                    tabelaHtml += '<td colspan="9">';
                    tabelaHtml += '<input  type="button" name="excluir" id="btnExcluirModal" value="Excluir" style="float:right" title="Excluir" class="botao_excluir botao">';
                    tabelaHtml += '<input  type="button" name="alterar" id="btnAlterarModal" value="Alterar" style="float:right" title="Alterar" class="botao_Alterar botao">';
                    tabelaHtml += '<input  type="button" name="newselect" id="btnNewSelectModal" value="Selecionar" style="float:right;" onclick="SelecionarFiscalModal()" title="Selecionar" class="botao_New_Selecionar botao">';
                    tabelaHtml += '</td>';
                    tabelaHtml += '</tr>';
                    tabelaHtml += '</tfoot>';
                    tabelaHtml += '</table>';
                return tabelaHtml;
        }
        function SelecionarFiscalModal(){
            const Doc = "<?php echo $dadosContratos->seqdocumento;?>";
            const cpf = $("input[name='cpfFiscal']:checked").val();
                console.log(Doc);
                $.post("postDados.php",{op:"SelecFiscal",cpf:cpf,doc:Doc}, function(data){
                    ObjJson = JSON.parse(data);
                     $("#mostrartbfiscais").html(CriaTabelaFiscalView(ObjJson));
                     $("#cpffiscal").removeAttr('disabled');
                     $("#cpffiscal").val('');
                     $("input[name='tipofiscalr']").removeAttr('disabled');
                     $("#btnNewPesquisaModalFiscal").hide();
                     $("#btnPesquisaModalFiscal").show();
                     $(".dadosFiscal").html('');
                     $("#modalFiscal").hide();
			    });
        }
        $(document).ready(function() { 
                $('#modalNScc').mask('9999.9999/9999');
                $('#numcontrato').mask('9999.9999/9999');
                $('.data').mask('99/99/9999');
                $('.cnpj').mask('99.999.999/9999-99');
                $('.CPF').mask('999.999.999-99');
                $('.telefone').mask('(99)99999-9999');
                

                $("#obra0").on('click', function(){
                $(".regimeExecucao").show();
                $(".modoFornecedor").hide();
                $(".modoFornecedorDisabl").hide();
                });
                $("#obra1").on('click', function(){
                $(".regimeExecucao").hide();
                $(".modoFornecedorDisabl").hide();
                $(".modoFornecedor").show();
                });
            
            $("#btnvoltar").on('click', function(){
                window.history.back();
            });
            $(".btn-pesquisa-scc").on('click', function(){
                $.post("postDados.php",{op:"modalSccPesquisa"}, function(data){
                    $(".modal-content").html(data);
                    $("#modal").show();
			    });
            });
            $("#manterfiscal").on('click', function(){
                $.post("postDados.php",{op:"modalFiscal"}, function(data){
                    $(".modal-content").html(data);
                    $("#modal").show();
			    });
            });
            $("#cnpj").live('focus', function(){
                $('#cnpj').mask('99.999.999/9999-99');
            });
            $("#cpffiscal").live('focus', function(){
                $('#cpffiscal').mask('999.999.999-99');
            });
            $("#btnPesquisaModal").live("click",function(){
                const tipo = $("input[name='tipofiscalr']:checked").val();
                $.post("postDados.php",{op:"Fiscal",cpf:$("#cpffiscal").val(),tipo:tipo}, function(data){
                    ObjJson = JSON.parse(data);
                     $(".dadosFiscal").html(CriaTabelaFiscal(ObjJson));
                     $("#cpffiscal").attr('disabled','disabled');
                     $("input[name='tipofiscalr']").attr('disabled','disabled');
                     $("#btnNewPesquisaModal").show();
                     $("#btnPesquisaModal").hide();
                     

			    });
            });
            $("#btnNewPesquisaModal").live('click', function(){
                     $("#cpffiscal").removeAttr('disabled');
                     $("#cpffiscal").val('');
                     $("input[name='tipofiscalr']").removeAttr('disabled');
                     $("#btnNewPesquisaModal").hide();
                     $("#btnPesquisaModal").show();
                     $(".dadosFiscal").html('');
            });
            
            $("#btnPesquisaModalSCC").live('click', function(){
                $.post("./postDados.php",
                        {
                            op          : "PesquisaModalScc",
                            Nscc        : $("#modalNScc").val(),
                            CodOrgao    : $("#modal-origem").val(),
                            NumContrato : $("#numcontrato").val(),
            
                        },
                    function(data){
                        $("#selectDivModal").html(data);
                    });
            });
            
            $("#adicionarFornecedorButton").on('click', function(){
                $.post("postDados.php",{op:"ModalFornecedorCred"}, function(data){
                    $(".modal-content").html(data);
                    $("#modal").show();
			    });
            });

            //fornecedor
            $('#radio-cpf').live('click',function(){
                $(".mostracnpj").hide();
                $(".mostracpf").show();
                $('#cpf').mask('999.999.999-99');
            });
            $('#radio-cnpj').live('click',function(){
                $(".mostracnpj").show();
                $(".mostracpf").hide();
            });
            $("#btnAdicionarModal").live('click',function(){
                $.post("postDados.php",{op:"Fornecerdor2",cpf:$("#cpf").val(),cnpj:$("#cnpj").val()}, function(data){
                    ObjJson = JSON.parse(data);
                    $(".dadosFornec").html(CriatableModal(ObjJson));
                    $("#shownewfornecedores").html(CriatableView(ObjJson));
			    });
            });
            $("#btn-fecha-modal").live('click', function(){
                $("#modal").hide();
            });
            $(".botao_fechar").live('click',function(){
                $("#modal").hide();
            });
            $(".botao_fechar_fiscal").live('click',function(){
                $("#modal").hide();
            });
            $("#btn-fecha-modal-fiscal").live('click',function(){
                $("#modal").hide();
            });           
            $("#fieldConsorcio0").on('click', function(){
               $(".fornecedorcampo").show();
            });
            $("#fieldConsorcio1").on('click', function(){
                $(".fornecedorcampo").hide();
            });
            $("#opcaoExecucaoContrato").on('change', function(){
                var valor = $("#opcaoExecucaoContrato").val();
                console.log(valor);
                if(valor != 0){
                    $("#prazo").prop("disabled", false);
                }else{
                    $("#prazo").prop("disabled", true);
                    $("#prazo").val("");
                }
                
                var dataVig = $("#vigenciaDataInicio").val();
                    var validado = datasCalc(dataVig.split("/"));
            });

            //Madson| Chamadas de função para alterar as datas proveniente da vigencia e demais campos -----------------------------
            $("#vigenciaDataInicio").on('change', function(){
                var dataVig = $("#vigenciaDataInicio").val();
                var validado = datasCalc(dataVig.split("/"));
            });
            $("#calendarioVig").on('blur', function(){
                var dataVig = $("#vigenciaDataInicio").val();
                var validado = datasCalc(dataVig.split("/"));
            });
            // $("#prazo").on('change', function(){
            //     var dataVig = $("#vigenciaDataInicio").val();
            //         var validado = datasCalc(dataVig.split("/"));
            // });
            $("#prazo").on('blur', function(){
                var dataVig = $("#vigenciaDataInicio").val();
                    var validado = datasCalc(dataVig.split("/"));
            });

            $("#iniVigenciaNdiasIniExec").on('blur', function(){
                var dataVig = $("#vigenciaDataInicio").val();
                    var validado = datasCalc(dataVig.split("/"));
            });
            $("#fimVigenciaNdiasFimExec").on('blur', function(){
                var dataVig = $("#vigenciaDataInicio").val();
                    var validado = datasCalc(dataVig.split("/"));
            });
            //FIM -----------------------------------------------------------------------------------------------------------------
            $("#file").on('change', function(){
                var file = $("#file").val();
                $.post("postDados.php",{op:"InsereArquivo", arquivo:file}, function(data){
                });
            });

        });
        <?php MenuAcesso(); ?>
       
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css">
    <body background="../midia/bg.gif" marginwidth="0" marginheight="0">
    <script language="JavaScript" src="../menu.js"></script>
    <script language="JavaScript">Init();</script>
    <form action="CadContratoIncluir.php" method="post" name="CadContratoIncluir">
        <br><br><br><br><br>
        <table cellpadding="3" border="0" summary="">
            <!-- Caminho -->
            <tr>
                <td width="100"><img border="0" src="../midia/linha.gif" alt=""></td>
                <td align="left" class="textonormal" colspan="2">
                    <font class="titulo2">|</font>
                    <a href="../index.php"><font color="#000000">Página Principal</font></a> > Contratos > Gestão
                </td>
            </tr>
            <!-- Fim do Caminho-->
            
            <!-- Erro -->
            <tr>
                <td width="100"></td>
                <td align="left" colspan="2">
                    <?php if( $_SESSION['Mens'] != 0 ){ ExibeMens($_SESSION['Mensagem'],$_SESSION['Tipo'],$_SESSION['Virgula']); }

                    $_SESSION['Mens'] = null;
                    $_SESSION['Tipo'] = null;
                    $_SESSION['Mensagem'] = null

                    ?>
                </td>
            </tr>
            <!-- Fim do Erro -->

            <!-- Corpo -->
            <tr>
                <td width="100"></td>
                <td class="textonormal">
                    <table  border="0" cellspacing="0" cellpadding="3" summary="" width="1024px" bgcolor="#FFFFFF">
                        <tr>
                            <td class="textonormal">

                                <table border="1" cellpadding="3" cellspacing="0" bordercolor="white" summary="" class="textonormal" bgcolor="#FFFFFF">
                                    <tr>
                                        <td align="left">
                                            <?php echo NavegacaoAbas(on,off,off,off,off); ?>
                                            <table cellpadding="0" cellspacing="0" border="0" bordercolor="#75ADE6" width="1024px" summary="">
                                                <tr bgcolor="#bfdaf2">
                                                    <!-- <td colspan="4"> -->
                                                        <table id="scc_material" summary=""  border="0" bordercolor="#75ADE6" width="100%">
                                                            <thead><td colspan="17" align="center" bgcolor="#75ADE6" valign="middle"> <b>Incluir Contrato</b></td></br></thead>
                                                            <tbody>
                                                               
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7">Número do Contrato/Ano :
                                                                        <td bgcolor="White" >
                                                                            <input id="numcontrato" type="text" name="numcontrato" class="numeroContrato" maxlength="20" size="20">
                                                                        </td>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7">
                                                                        Solici. de Compra/Contratação-SCC * :
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" id="numeroscc" name="numeroscc" value="<?php if(!is_null($numScc)){  echo "$numScc"; } ?>" readonly disabled="disabled">
                                                                        <a href="#" class="btn-pesquisa-scc">
                                                                            <img src="../midia/lupa.gif" border="0">
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7">Origem :
                                                                     <td class="inputs">
                                                                     <span id="origem"><?php if(!is_null($origemScc)){  echo "$origemScc"; } ?></span>
                                                                     </td>
                                                                </tr>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7">Órgão Contratante Responsável :</td>
                                                                    <td class="inputs">
                                                                        
                                                                        <span id="orgao"></span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7">Objeto * :
                                                                    <td class="inputs">
                                                                        <textarea id="objeto" name="objeto" cols="40" onkeyup="return limitChars(this,1000)" rows="8"><?php if(!is_null($objetoDesc)){  echo "$objetoDesc"; } ?>
                                                                        </textarea>
                                                                    </td>
                                                                    </td>
                                                                </tr>                                                                
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7">Contratado :
                                                                    <td class="inputs">
                                                                        <table id="_gridContratadoNovo">
                                                                            <tbody><tr><td bgcolor="#DCEDF7" class="labels">
                                                                                <span id="_panelLblCpfCnpj">
                                                                                <label for="" style=";" class="" >CNPJ do Contratado :</label></span></td><td class="inputs" colspan="3"><div id="_panelInputCpfCnpj"><?php if(!is_null($CpfCnpj)){  echo "$CpfCnpj"; } ?><label>
                                                                                    </label></div></td></tr>
                                                                                    <tr><td bgcolor="#DCEDF7" class="labels"><label for="" style=";" class="">Razão Social :</label></td><td class="inputs" colspan="3"><div id="_panelGroupRazao"><span id="_razaoSocialfornecedor"><?php if(!is_null($razSocial)){  echo "$razSocial"; } ?></span></div></td></tr>
                                                                                    <tr><td bgcolor="#DCEDF7" class="labels"><label for="" style=";" class="">Logradouro :</label></td><td class="inputs" colspan="3"><span id="_logradourofornecedor"><?php if(!is_null($Rua)){  echo "$Rua"; } ?></span></td></tr>
                                                                                    <tr><td bgcolor="#DCEDF7" class="labels"><label for="" style=";" class="">Número :</label></td><td class="inputs" colspan="3"><span id="_numerofornecedor"><?php if(!is_null($numEnd)){  echo "$numEnd"; } ?></span></td></tr>
                                                                                    <tr><td bgcolor="#DCEDF7" class="labels"><label for="" style=";" class="">Complemento :</label></td><td class="inputs"><span id="_complementoLogradourofornecedor"><?php if(!is_null($complEnd)){  echo "$complEnd"; } ?></span></td><td bgcolor="#DCEDF7" class="labels"><label for="" style=";" class="">Bairro:</label></td><td class="inputs"><span id="_bairrofornecedor"><?php if(!is_null($Bairro)){  echo "$Bairro"; } ?></span></td></tr>
                                                                                    <tr><td bgcolor="#DCEDF7" class="labels"><label for="" style=";" class="">Cidade :</label></td><td class="inputs"><span id="_cidadefornecedor"><?php if(!is_null($Cidade)){  echo "$Cidade"; } ?></span></td><td bgcolor="#DCEDF7" class="labels"><label for="" style=";" class="">UF:</label></td><td class="inputs"><span id="_estadofornecedor"><?php if(!is_null($UF)){  echo "$UF"; } ?></span></td></tr>
                                                                                    </tbody></table>
                                                                    </td>
                                                                </td>
                                                                </tr>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7">Consórcio / Matriz-Filial / Publicidade ?* :</td>
                                                                    <td class="inputs">
                                                                        <table id="fieldConsorcio">
                                                                        <tbody><tr>
                                                                            <td>
                                                                                <input type="radio" name="fieldConsorcio" id="fieldConsorcio0" value="SIM" title="Consórcio / Matriz-Filial / Publicidade ?"><label for="fieldConsorcio0"> Sim</label></td>
                                                                            <td>
                                                                                <input type="radio" name="fieldConsorcio" id="fieldConsorcio1" value="NAO" title="Consórcio / Matriz-Filial / Publicidade ?"><label for="fieldConsorcio1"> Não</label></td>
                                                                            </tr>
                                                                    </tbody></table></td></td>
                                                                </tr>
                                                                <tr class="fornecedorcampo" style="display:none">
                                                                    <td bgcolor="#DCEDF7">Fornecedor :</td>
                                                                    <td class="inputs">
                                                                        <input id="adicionarFornecedorButton" type="button" name="adicionarFornecedorButton" value="Adicionar Fornecedor" style="float:right" title="Adicionar Fornecedor" class="botao_final">
                                                                        <div id="shownewfornecedores"></div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7">Contínuo * :</td>
                                                                     <td class="inputs">
                                                                     <table id="fieldContinuo">
                                                                        <tbody><tr>
                                                                            <td>
                                                                            <input type="radio" name="fieldContinuo" id="fieldContinuo0" value="SIM" title="Contínuo"><label for="fieldContinuo0"> Sim</label></td>
                                                                            <td>
                                                                            <input type="radio" name="fieldContinuo" id="fieldContinuo1" value="NAO" title="Contínuo"><label for="fieldContinuo1"> Não</label></td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7">Obra * :</td>
                                                                    <td class="inputs">
                                                                        <table id="obra">
                                                                            <tbody><tr>
                                                                                <td>
                                                                                    <input type="radio" name="obra" id="obra0" value="SIM"><label for="obra:0"> Sim</label></td>
                                                                                <td>
                                                                                    <input type="radio" name="obra" id="obra1" value="NAO"><label for="obra:1"> Não</label></td>
                                                                            </tr>
                                                                        </tbody>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                                <tr class="modoFornecedorDisabl" style="display:">
                                                                    <td bgcolor="#DCEDF7">Modo de Fornecimento * :</td>
                                                                    <td class="inputs">
                                                                        <select class="modoFornecimentoDisablSel" name="modoFornecimento" size="1" disabled="disabled">	
                                                                            <option selected="selected"></option>
                                                                            <option value="INTEGRAL">INTEGRAL</option>
                                                                            <option value="PARCELADO">PARCELADO</option>
                                                                        </select>
                                                                    </td>
                                                                </tr><!--Madson-->
                                                                <tr class="modoFornecedor" style="display:none">
                                                                    <td bgcolor="#DCEDF7">Modo de Fornecimento * :</td>
                                                                    <td class="inputs">
                                                                        <select class="modoFornecimentoSel" name="modoFornecimento" size="1">	
                                                                            <option selected="selected"></option>
                                                                            <option value="INTEGRAL">INTEGRAL</option>
                                                                            <option value="PARCELADO">PARCELADO</option>
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                <tr class="regimeExecucao" style="display:none">
                                                                    <td bgcolor="#DCEDF7">Regime de Execução * :</td>
                                                                    <td class="inputs">
                                                                    <select class="regimeExecSel" name="regimeExecSel" size="1">
                                                                        <option selected="selected"></option>
                                                                        <option value="PRECO GLOBAL">EMPREITADA POR PREÇO GLOBAL</option>
                                                                        <option value="EMPREITADA POR PRECO UNITARIO">EMPREITADA POR PREÇO UNITÁRIO</option>
                                                                        <option value="TAREFA">TAREFA</option>
                                                                        <option value="EMPREITADA INTEGRAL">EMPREITADA INTEGRAL</option>
                                                                    </select>
                                                                    </td>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7"bgcolor="#DCEDF7">Opção de Execução do Contrato * :</td>
                                                                    <td class="inputs">
                                                                        <select id="opcaoExecucaoContrato" name="opcaoExecucaoContrato" size="1" title="Opção de Execução do Contrato ">	
                                                                        <option value="" selected="selected"></option>
                                                                        <option value="Dias">Dias</option>
                                                                        <option value="Meses">Meses</option>
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7">Prazo de Execução do Contrato * :</td>
                                                                    <td class="inputs"><input id="prazo" type="text" name="prazo" class="inteiroPositivo" maxlength="2" size="2" disabled="disabled">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7">Data de Publicação no DOM :</td>
                                                                    <td class="inputs">
                                                                        <input id="dataPublicacaoDom" type="text" name="dataPublicacaoDom" class="data" maxlength="10" size="12" title="">
                                                                        <a style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=CadContratoIncluir&amp;Campo=dataPublicacaoDom','Calendario',220,170,1,0)"> 
			                                                                    <img src="../midia/calendario.gif" border="0" alt="">
		                                                                    </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7">Data de Transcrição :</td>
                                                                     <td class="inputs">
                                                                        <input id="dataTranscricao" type="text" name="dataTranscricao" class="data" maxlength="10" size="12" title="">
                                                                        <a style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=CadContratoIncluir&amp;Campo=dataTranscricao','Calendario',220,170,1,0)"> 
                                                                            <img src="../midia/calendario.gif" border="0" alt="">
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7">Número de dias entre Início de Vigência e Início de Execução :</td>
                                                                     <td class="inputs"><input id="iniVigenciaNdiasIniExec" type="text" name="vigenciaNdiasIniExec" value="30" class="inteiroPositivo" maxlength="8" size="3" title="Número de dias entre Início de Vigência e Início de Execução"></td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7">Número de dias entre Final de Vigência e Final de Execução :</td>
                                                                     <td class="inputs"><input id="fimVigenciaNdiasFimExec" type="text" name="vigenciaNdiasFimExec" value="150" class="inteiroPositivo" maxlength="8" onblur="A4J.AJAX.Submit('form',event,{'control':this,'similarityGroupingId':'ajaxNumeroDiasFinalExecucaoFinalVigencia','parameters':{'ajaxNumeroDiasFinalExecucaoFinalVigencia':'ajaxNumeroDiasFinalExecucaoFinalVigencia','ajaxSingle':'txtEditarNumeroDiasFinalExecucaoFinalVigencia'} } )" size="3" title="Número de dias entre  Final de Execução e Final Vigência"></td>
                                                                </tr>
                                                                <tr id="linhaTabelaOS">
				                                                    <td id="colunaVaziaOS" width="35%" bgcolor="#bfdaf2"></td>
				                                                    <td id="colunaDataInicioOS" bgcolor="#bfdaf2"><table id="panelDataInicioOrdemServico" class="colorBlue">
                                                                    <thead>
                                                                    <tr ><th colspan="1" scope="colgroup"><span id="labelDataInicioOrdemServico">DATA DE INÍCIO</span></th></tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    </tbody>
                                                                    </table>
                                                                    </td>
                                                                     <td id="colunaDataTerminoOS" bgcolor="#bfdaf2"><table id="panelDataTerminoOrdemServico" class="colorBlue">
                                                                    <thead>
                                                                    <tr><th colspan="1" scope="colgroup"><span id="labelDataTerminoOrdemServico">DATA DE TÉRMINO</span></th></tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    </tbody>
                                                                    </table>
                                                                    </td>
			                                                        </tr>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7">Vigência * :</td>
                                                                     <td class="inputs">
                                                                     <span id="vigenciaGroup">
                                                                     <input id="vigenciaDataInicio" type="text" name="vigenciaDataInicio" class="data" maxlength="10" size="12" title="">
                                                                     <a id="calendarioVig" style="text-decoration: none" href="javascript:janela('../calendario.php?Formulario=CadContratoIncluir&amp;Campo=vigenciaDataInicio','Calendario',220,170,1,0)"> 
                                                                        <img src="../midia/calendario.gif" border="0" alt="">
		                                                                </a>
                                                                    <td>                
                                                                    <input id="vigenciaDataTermino" type="text" name="vigenciaDataTermino" value="" class="data"  maxlength="10" size="12" readonly style="border:none">
                                                                    </td>
                                                                    </span>   
                                                                        </td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7">Execução :</td>
                                                                     <td class="inputs">
                                                                        <span id="execucaoGroup">              
                                                                            <input id="execucaoDataInicio" type="text" name="execucaoDataInicio" value="" class="data"  maxlength="10" size="12" readonly style="border:none">
                                                                            <td>                
                                                                                <input id="execucaoDataTermino" type="text" name="execucaoDataTermino" value="" class="data"  maxlength="10" size="12" readonly style="border:none">
                                                                            </td>
                                                                            </span>
                                                                        </span>
                                                                    </td>   
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7">Tipo Especial de Fonte de Recurso :</td>
                                                                     <td class="inputs">
                                                                        <select id="tipo" name="tipoEspFonteRecurso" size="1" title="Tipo Especial de Fonte de Recurso">	
                                                                            <option value="" selected="selected"></option>
                                                                            <option value="1">Nenhum</option>
                                                                            <option value="2">Convênio</option>
                                                                            <option value="3">Operação de Crédito</option>
                                                                        </select></td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7">Garantia :</td>
                                                                     <td class="inputs">
                                                                        <select id="comboGarantia" name="comboGarantia" size="1" title="Garantia">	
                                                                            <option value="" selected="selected"></option>
                                                                            <option value="1">CAUÇÃO EM DINHEIRO</option>
                                                                            <option value="2">CAUÇÃO EM TÍTULOS DA DÍVIDA PÚBLICA</option>
                                                                            <option value="3">SEGURO-GARANTIA</option>
                                                                            <option value="4">FIANÇA BANCÁRIA</option>
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                <!-- <tr id="linhaTabelaOS" bgcolor="#bfdaf2"> -->
				                                                    <!-- <td id="colunaDataInicioOS"><table id="panelDataInicioOrdemServico" class="colorBlue"> -->
                                                                
                                                                    <thead textalign="center" bgcolor="#bfdaf2">
                                                                    <tr><th colspan="1" ><span id="labelDataInicioOrdemServico">Representante Legal</span></th></tr>
                                                                    </thead>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7">Nome * :<td bgcolor="White">
                                                                                <input type="text" name="repNome">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7">CPF * : <td bgcolor="White">
                                                                                <input  class="CPF" type="text" name="repCPF">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7">Cargo : <td bgcolor="White">
                                                                                <input type="text" name="repCargo">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7">Identidade :	<td bgcolor="White">
                                                                                <input type="text" name="repRG">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7">Órgão Emissor : <td bgcolor="White">
                                                                                <input type="text" name="repRgOrgao">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7">UF da Identidade : <td bgcolor="White">
                                                                                <input type="text" name="repRgUF">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7">Cidade de Domicílio : <td bgcolor="White">
                                                                                <input type="text" name="repCidade">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7">Estado de Domicílio : <td bgcolor="White">
                                                                                <input type="text" name="repEstado">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7">Nacionalidade :	<td bgcolor="White">
                                                                                <input type="text" name="repNacionalidade">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7">Estado Civil :	 <td bgcolor="White">
                                                                                <input type="text" name="repEstCiv">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7">Profissão :
                                                                    <td class="inputs">
                                                                        <input id="Profissao" type="text" name="repProfissao" maxlength="50" size="50" style="text-transform: uppercase">
                                                                    </td></td>
                                                                </tr> 
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7">E-mail : <td bgcolor="White">
                                                                                <input type="text" name="repEmail">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                     <td bgcolor="#DCEDF7">Telefone(s) : <td bgcolor="White">
                                                                                <input class="telefone" type="text" name="repTelefone">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                <thead bgcolor="#bfdaf2">
                                                                    <tr><th colspan="1" scope="colgroup">Preposto</th></tr>
                                                                </thead>
                                                                </tr>
                                                            
                                                                <tr>
                                                                        <td bgcolor="#DCEDF7">Nome : <td bgcolor="White">
                                                                                    <input type="text" name="prepNome">
                                                                        </td></td>
                                                                </tr>
                                                                
                                                                <tr>
                                                                        <td bgcolor="#DCEDF7">CPF : <td bgcolor="White">
                                                                                    <input class="CPF" type="text" name="prepCPF">
                                                                        </td></td>
                                                                </tr>
                                                                
                                                                <tr>
                                                                        <td bgcolor="#DCEDF7">E-mail : <td bgcolor="White">
                                                                                    <input type="text" name="prepEmail">
                                                                        </td></td>
                                                                </tr>
                                                                
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7"> Telefone(s) : <td bgcolor="White">
                                                                        <input class="telefone" type="text" name="prepTelefone">
                                                                    </td></td>
                                                                </tr>
                                                                <tr>
                                                                    <thead bgcolor="#bfdaf2">
                                                                        <tr><th colspan="1" scope="colgroup">Gestor</th></tr>
                                                                    </thead>
                                                                </tr>
                                                                
                                                                <tr>
                                                                        <td bgcolor="#DCEDF7">Nome : <td bgcolor="White">
                                                                                    <input type="text" name="gestorNome">
                                                                        </td></td>
                                                                </tr>
                                                                <tr>
                                                                        <td bgcolor="#DCEDF7">Matrícula : <td bgcolor="White">
                                                                                    <input type="text" name="gestorMatricula">
                                                                        </td></td>
                                                                </tr>
                                                                
                                                                <tr>
                                                                        <td bgcolor="#DCEDF7">CPF : <td bgcolor="White">
                                                                                    <input class="CPF" type="text" name="gestorCPF">
                                                                        </td></td>
                                                                </tr>
                                                                
                                                                <tr>
                                                                        <td bgcolor="#DCEDF7">E-mail : 
                                                                            <td bgcolor="White">
                                                                                <input type="text" name="gestorEmail">
                                                                            </td>
                                                                        </td>
                                                                </tr>
                                                                
                                                                <tr>
                                                                    <td bgcolor="#DCEDF7">Telefone(s) : 
                                                                        <td bgcolor="White">
                                                                            <input class="telefone" type="text" name="gestorTelefone">
                                                                        </td>
                                                                    </td>
                                                                </tr>
                                                            <thead >
                                                                        <th colspan="17" scope="colgroup" bgcolor="#bfdaf2">FISCAL(IS)*</th>
                                                            </thead>
                                                            <tr>
                                                                <!-- Eliakim Ramos -->
                                                                <td colspan="3" >
                                                                  <table style="width:100%; border:1px solid #bfdaf2;"  id="tabelaficais">
                                                                      <thead bgcolor="#bfdaf2" style="">
                                                                          <tr>
                                                                              <td colspan="1">Tipo Fiscal</td>
                                                                              <td colspan="1">Nome</td>
                                                                              <td colspan="1">Matrícula</td>
                                                                              <td colspan="1">CPF</td>
                                                                              <td colspan="1">Ent. Compet.</td>
                                                                              <td colspan="1"> Registro ou INSC.</td>
                                                                              <td colspan="1">E-MAIL</td>
                                                                              <td colspan="1">Telefone.</td>
                                                                          </tr>
                                                                      </thead>
                                                                    <tbody id="mostrartbfiscais">
                                                                      
                                                                        <!-- <tr>
                                                                            <td> 
                                                                                <input id="radiofisc" type="radio" name="fiscais" value="">
                                                                            </td>
                                                                            <td id="tipofiscal"></td>
                                                                            <td id="nomefiscal"></td>
                                                                            <td id="matricfisc"></td>
                                                                            <td id="cpffiscal"></td>
                                                                            <td id="entcompfis"></td>
                                                                            <td id="reginscfis"></td>
                                                                            <td id="emailfisc"></td>
                                                                            <td id="teleffisc"></td>
                                                                        </tr> -->
                                                                    </tbody>
                                                                    <tfoot>
                                                                        <tr>
                                                                            <td  colspan="9" style="itens-align:right;">
                                                                                <button class="botao" type="button" id="removefiscal" style="float: right;">Remover Fiscal</button>
                                                                                <button class="botao" type="button" id="manterfiscal" style="float: right;">Manter Fiscal</button>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td bgcolor="#bfdaf2" colspan="4">Situação :</td>
                                                                            <td ><?php echo $situacao;?></td>
                                                                        </tr>
                                                                    </tfoot>
                                                                  </table>
                                                                </td>
                                                            </tr>
                                                            
				                                            <thead >
                                                                        <th colspan="17" scope="colgroup" bgcolor="#bfdaf2">CHECKLIST</th>
                                                            </thead>
                                                            
                                                            <tr>
                                                                <td colspan="2"><table id="gridDocumentos" style="border: 1px solid #000;" bgcolor="#bfdaf2">
                                                                        <tr>
                                                                            <td class="labels">
                                                                                <label for="form:" style=";" class="">Anexar Documento(s) :</label></td>
                                                                            <td class="inputs">
                                                                            <!-- #2 - 147139 <h:panelGroup rendered="true"> --></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="labels">
                                                                                <div id="arquivosNomes" style="display:none;">
                                                                                </div>
                                                                                <div>
                                                                                    <input id="file" type="file" name="file[]" multiple >
                                                                                </div>
                                                                                    <input id="botaoIncluirDocumento" type="submit" name="botaoIncluirDocumento" value="Incluir" style="float:right" class="botao_voltar">
                                                                            </td>
                                                                        </tr>                                                                                                           
                                                                </td>
                                                            </tr>
                                                            
                                                            </table>
    </form>
    <div class="modal" id="modal"> 
        <div class="modal-content" style="min-height: 50%;width: 60%;">
         
        </div>
    </div> 
    <!-- Fim Modal -->
    </body>
    </html>
    <?php
    exit;
}

