<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

    <!--    Datatables  -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <title></title>

    <style>
        table.dataTable thead {
            background: linear-gradient(to right, #fcb045, #fd1d1d, #833ab4);
            color: white;
        }
    </style>
</head>

<body>
    <h2 class="text-center">Datatables</h2>
    <h3 class="text-center">Procesamiento ServerSide</h3>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <table id="tablaUsuarios" class="table table-striped table-bordered table-condensed" style="width:100%">
                    <thead class="text-center">
                        <tr>
                            <th>estudiante_id</th>
                            <th>nombre_completo</th>
                            <th>periodo_abconta</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

    <!-- Datatables-->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
    <script>
        $(document).ready(function() {
            $("#tablaUsuarios").DataTable({
                processing: true,
                serverSide: true,
                sAjaxSource: 'serversideUsuarios.php',
                columnDefs: [{
                    "data": null
                }]
            });
        });
    </script>

</body>

</html>