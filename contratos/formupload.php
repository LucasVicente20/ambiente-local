<?php ini_set('display_errors', 0)?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <div>
        <form action="" enctype="multipart/form-data" method="post" id="formDocumento" name="formDocumento">
            <input type="hidden" name="op" value="uploadArquivo">
            <input type="file" <?php echo $bloqueiacampo?'disabled="disabled"':'';?> multiple name="documento[]" id="documento" onchange="limpaMensagem()">
            <br>
            <!-- <span id="msgDoc"></span> -->
        </form>
    </div>
</body>
<script>
    function tableView(){

    }
    function limpaMensagem(){
        // document.querySelector("#msgDoc").innerHTML ="";
        parent.document.querySelector(".mensagem-texto").innerHTML="";
        parent.document.querySelector("#tdmensagem").style.display="none";
    }
    function MostraDoc(){
        var xhr2 = new XMLHttpRequest();
        var data = new FormData();
        data.append('op', 'GetDocAnex');
        xhr2.upload.onprogress = function(e){
            console.log("Subindo "+e.loaded+" de "+e.total);
        }
        xhr2.upload.onload = function(e){
            console.log("Upload Realizado");
        }
        xhr2.open("POST",'postDados.php', true);
        xhr2.send(data);
        xhr2.onreadystatechange = function () {
            if (xhr2.readyState < 4){}                             // está à espera de resposta
                // document.querySelector("#msgDoc").innerHTML ='';
            else if (xhr2.readyState === 4) {                    // 4 = A resposta do servidor está carregada
                if (xhr2.status == 200 && xhr2.status < 300)  // http status entre 200 e 299 quer dizer sucesso
                    retorno = xhr2.response;
                    parent.document.querySelector("#FootDOcFiscal").innerHTML = retorno;                    // ou usa o xhr.responseText de outra maneira
                    //parent.document.querySelector("#FootDOcFiscal").append = retorno;                    // ou usa o xhr.responseText de outra maneira
            }
        }
    }
    function subirArquivo(){
        var xhr = new XMLHttpRequest();
        xhr.upload.onprogress = function(e){
            // document.querySelector("#msgDoc").innerHTML = '<div class="load-content" > <img src="../midia/loading.gif" alt="Carregando"> <spam>Carregando...</spam> </div>';
        }
        xhr.upload.onload = function(e){
            // document.querySelector("#msgDoc").innerHTML ="";
        }
        xhr.open("POST",'postDados.php', true);
        xhr.send(new FormData(document.querySelector("#formDocumento")));
        xhr.onreadystatechange = function () {
            if (xhr.readyState < 4)  {        
                parent.document.querySelector("#loadArquivo").style.display = "block"                   // está à espera de resposta
                // document.querySelector("#msgDoc").innerHTML ='Carregando...';
           } else if (xhr.readyState === 4) {                    // 4 = A resposta do servidor está carregada
                if (xhr.status == 200 && xhr.status < 300)  // http status entre 200 e 299 quer dizer sucesso
                    retorno = JSON.parse(xhr.responseText);
                    parent.document.querySelector("#loadArquivo").style.display = "none";                // ou usa o xhr.responseText de outra maneira
                   if(retorno.sucess){
                        MostraDoc();
                   }else{
                         parent.document.querySelector("#tdmensagem").style.display="block";
                         let elmnt =  parent.document.querySelector("body");
                         elmnt.scrollTop = -1000;
                         elmnt.scrollLeft = -1000;
                         parent.document.querySelector(".mensagem-texto").innerHTML=retorno.msm;
                   }
            }
        }
    }

    function validaAnexo(){
        
        if(document.querySelector('#documento').files.length === 0){
                return false;
        }
        return true;
    }

</script>
</html>