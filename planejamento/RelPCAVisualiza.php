<?php 
/**
* Portal de Compras
* Programa: CadGerarPCA.php
* Autor: João Madson
* Data: 03/02/2023
* Objetivo: Programa para GerarPCA
* -------------------------------------------------------------------
*/

# Acesso ao arquivo de funções #
require_once "../funcoes.php";
# Executa o controle de segurança	#
session_start();
Seguranca();

if ($_SERVER['REQUEST_METHOD'] == "GET") {
}
if ($_SERVER['REQUEST_METHOD'] == "POST") {    
}

?>

<html>
    <script language="javascript" src="../import/jquery/jquery-1.7.2.min.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskmoney.js" type="text/javascript"></script>
    <script language="javascript" src="../import/jquery/jquery.maskedinput.js" type="text/javascript"></script>
    <script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
    <script language="javascript" >
    // function geraGraficoBarras(dados, idElemento){
    //     function drawChart() {
    //         var data = google.visualization.arrayToDataTable([
    //         ]);

    //         var options = {
    //             title: 'teste',
    //             legend: { position: 'none' },
    //             colors: ['ciano'],
    //         };

    //         var chart = new google.visualization.Histogram(document.getElementById('chart_div'));
    //         chart.draw(data, options);
    //     }
    // }
    // function geraGraficoPizza(dados, idElemento){        
    //     function drawChart() {
    //         // console.log(dados);
    //         var dadostratados = [['ÁREA REQUISITANTE', 'DFDS CADASTRADOS'],];
    //         for(var i=0;i<dados.length;i++){
    //             dadostratados.push([dados[i].eorglidesc, dados[i].quantDFDs],);
    //         }
    //         for(var i=0;i<dadostratados.length;i++){
    //             console.log(dadostratados[i]);
    //             var data = google.visualization.arrayToDataTable([
    //                 [dadostratados[i]]
    //             ]);
    //         }
            
    //         var options = {
    //         title: 'teste'
    //         };

    //         var chart = new google.visualization.PieChart(document.getElementById(idElemento));
    //         chart.draw(data, options);
    //     }
    // }

    // function AbreJanelaItem(url,largura,altura){
    //     window.open(url,'paginaitem','status=no,scrollbars=yes,left=90,top=150,width='+largura+',height='+altura);
    // }

    $(document).ready(function() {
        // $("#tdload").show();
        // $("#op").val("gerarPCA");
        $.post("PostDadosGerarPCA.php", {op:"gerarPCA"}, function(data) {
            const response = JSON.parse(data);
            if (response.status == true) {
                $("#getHtmlPDF").html(response.html);
            }
        })
        // $(".modal-content").html("<img id='baixarPDF' src='../midia/download.jpg' style='width:40px;'>");
        // $(".modal-content").attr("style","max-width: 40px;float:right;");
        // $("#modal").show();
        $("#baixarPDF").live("click", function(){
            $("#baixarPDF").hide();
            window.print()
            $("#baixarPDF").show();
        });
    })
    </script>
    <link rel="stylesheet" type="text/css" href="../estilo.css?v=<?php echo time();?>">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <style>
        -webkit-column-break-inside: avoid;
        page-break-inside: avoid;
        break-inside: avoid;
        @media print {
            .tdResultTituloOrg{
                font-family: Verdana;
                /* font-size: 13pt; */
                background-color: #D3D3D3 !important;
                print-color-adjust: exact; 
            }
        }
        body {
            font-size: 8pt;
            font-family: Verdana;
        }
        .pagenum:before {content: counter(page);}
                    footer .pagenum:before {content: counter(page);}
        table{
            width: 100%;
                    }
        #tdHeader{
            text-align:center;
            width: 100%;
                    }
        p{
            text-align: justify;
            font-size: 10.6667px; 
                    }
        #containerSessB{
            border-color: black;
            border-style: solid;
            border-width: thin;
        }
        .tdResultTitulo{
            font-family: Verdana;
            font-size: 8pt;
        }
        .tdResultTituloOrg{
            font-family: Verdana;
            font-size: 13pt;
            background-color: #D3D3D3;
        }
        .label, .result, .tdresult{
            font-family: Verdana;
            font-size: 8pt;
        }
    </style>
    <header>
        <img id='baixarPDF' src='../midia/download.jpg' style='width:40px;float:right;'>
    </header>
    <body>
        <div id="getHtmlPDF"></div>
    </body>
    <!-- <div class="modal" id="modal">
        <div class="modal-content" >
        
        </div>
    </div> -->
</html>