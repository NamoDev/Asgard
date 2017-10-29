<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        <title>Project Asgard</title>
        <link rel="stylesheet" href="/assets/css/load.css" />
    </head>
    <body>
        <div class="wrapper">
            <nav class="navbar navbar-fixed-top">
                <div class="container">
                    <i class="navbar-brand">
                        <b>Asgard</b> Looking Glass
                    </i>
                </div>
            </nav>
            <div class="container" style="margin-top:85px;">
                @yield("content")
            </div>
        </div>
        <script src="/assets/js/jquery.min.js"></script>
        <script src="/assets/js/bootstrap.min.js"></script>
        @yield("additional_scripts")
    </body>
</html>
