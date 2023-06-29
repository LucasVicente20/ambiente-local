<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <style>
            a.disabled {
                pointer-events: none;
            }
    </style>
    <title>Report</title>
</head>
<body>
    <div class="container-fluid">
        <div class="container">
            <div class="row">
                <div class="d-grid gap-2 col-6 mx-auto">
                    <button class="btn btn-primary" id="request">Report material</button>
                    <a  href="ControllerReport.php?action=excelm" class="btn btn-success disabled" id="btnMaterial" target="_blank" rel="noopener noreferrer">Exportar Excel Material</a>
                </div>
            </div>
            <div class="row">
                <div class="d-grid gap-2 col-6 mx-auto">
                    <button class="btn btn-primary" id="requestServico">Report Serviço</button>
                    <a  href="ControllerReport.php?action=excelv" class="btn btn-success disabled" id="btnServico" target="_blank" rel="noopener noreferrer">Exportar Excel Serviço</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        var request = new XMLHttpRequest();
        var btnM = document.getElementById('request');
        var excelM = document.getElementById('btnMaterial');
        var btnV = document.getElementById('requestServico');
        var excelV = document.getElementById('btnServico');
        var isVOrM = '';
         // Form fields, see IDs above
         var data = new FormData();
        //data.set('action','carregaDados');
        // keep track of the request
        request.onreadystatechange = function() {
            // check if the response data send back to us 
            if(request.readyState === 4) {
                if(request.status === 200) {
                    console.log("Foi");
                    console.log(isVOrM);
                    if(isVOrM == "M"){
                        excelM.setAttribute("class", "btn btn-success");
                    } else if( isVOrM == "V"){
                        excelV.setAttribute("class", "btn btn-success");
                    }
                    console.log(isVOrM);
                } else {
                    // otherwise display an error message
                    console.log('An error occurred during your request: ' +  request.status + ' ' + request.statusText);
                }
            }
        }

        // register an event
        btnM.addEventListener('click', function() {
            // send the request
            isVOrM = "M";
            data.set('action','carregaDados');
            request.open('POST', 'ControllerReport.php', false);
            request.send(data);
        });

        btnV.addEventListener('click', function() {
            // send the request
            isVOrM = "V";
            data.set('action','carregaDadosServico');
            request.open('POST', 'ControllerReport.php', false);
            request.send(data);
        });

    </script>
</body>
</html>