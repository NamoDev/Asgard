@extends("templates.master")

@section("content")
<div class="row">
    <div class="col-md-4">
        <div class="panel">
            <div class="panel-body text-center">
                <br />
                    <p style="margin-bottom:-5px;">Project Asgard</p>
                    <h4>Network Diagnostics Tool</h4>
                <hr />
                    <h6>Your IP address: <b>{{ Request::ip() }}</b></h6>

                    <div id="additionalConnectionDataLoadingDiv">
                        <small><i class="fa fa-spinner fa-spin"></i> Loading additional information<br />about your internet connection...</small>
                        <br /><br />
                    </div>
                    <div id="additionalConnectionDataLoadErrorDiv">
                        <small><i class="fa fa-info-circle"></i> Cannot load additional information<br />about your connection at this time.</small>
                        <br /><br />
                    </div>
                    <div id="additionalConnectionData">
                        <h6 class="text-muted">Network: <span id="dp_netname"></span> (<span id="dp_asn"></span>)</h6>
                        <h6 class="text-muted"><span id="dp_asdescr"></span></h6>
                    </div>

                    <a class="btn btn-info btn-block btn-sm" style="margin-top:15px;" id="btnCopySelfIP">Use my IP as the destination address</a>
                <hr />
                    <div class="form-group">
                        <label for="exampleInputName" style="margin-bottom:15px;"><i class="fa fa-sitemap"></i> Destination Address</label>
                        <input class="form-control" id="destinationAddr" placeholder="Hostname or IPv4 address" type="text" autocomplete="off">
                        <h6 id="invalidHIPalerter" class="text-danger"><i class="fa fa-exclamation-triangle"></i> Invalid hostname or IPv4 address</h6>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputName" style="margin-bottom:15px;"><i class="fa fa-wrench"></i> Test Type</label>
                        <select class="form-control" id="testType" autocomplete="off">
                            <option value="traceroute" selected>IPv4 Traceroute</option>
                            <option value="ping">IPv4 Ping</option>
                            <option value="mtr">IPv4 MTR</option>
                        </select>
                    </div>
                    <br />
                    <a href="#" id="btnRunTest" class="btn btn-w-md btn-success btn-block">Run Test</a>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="panel panel-filled">
            <br />
            <div class="panel-body">
                <div id="div_netTestResults">
                    Please enter the destination address you wish to run the test for, select the test type to run,<br />and click on <code>Run Test</code> to start testing.
                    <br /><br />
                    <small><i class="fa fa-info-circle"></i> Due to the low-priority nature of these requests on our network infrastructure,<br />these tests should only be used to verify routing information.</small>
                </div>
            </div>
            <br />
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xs-12 text-center" style="margin-top:50px;">
        <small class="text-muted">Made with <i class="fa fa-heart"></i> by <a class="link-no-highlight" href="https://github.com/NamoDev" target="_blank">@NamoDev</a></small>
    </div>
</div>
@endsection

@section("additional_scripts")
<script>
    var csrf_token = "{{ csrf_token() }}";
    var testRunning = false;
    var testID = "";
    var webClientIP = "{{ Request::ip() }}";
    var checkTick;

    $(function(){
        $("#invalidHIPalerter").hide();
        $("#additionalConnectionData").hide();
        $("#additionalConnectionDataLoadErrorDiv").hide();
        checkIPInfo();
    });

    $("#destinationAddr").on("keyup", function (e) {
        if ($("#destinationAddr").val().length > 0 && e.keyCode == 13) {
            $("#btnRunTest").click();
        }
    });

    $("#btnCopySelfIP").click(function(e){
        e.preventDefault();
        $("#destinationAddr").val(webClientIP);
    });

    $("#btnRunTest").click(function(e){

        if(testRunning === false){
            $("#invalidHIPalerter").fadeOut(200);
            e.preventDefault();

            // Valid hostname or IP?
            var validIP = /^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)){3})$/.test($("#destinationAddr").val());
            var validHostname = /^((([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9]))$/.test($("#destinationAddr").val());

            if(validIP === true || validHostname === true){
                testRunning = true;
                requestTest($("#destinationAddr").val());
                $("#btnRunTest").addClass("disabled").removeClass("btn-success").addClass("btn-default");
                $("#btnRunTest").html("Test running...");
                $("#div_netTestResults").fadeOut(200, function(){
                    $("#div_netTestResults").html("<i class=\"fa fa-spinner fa-spin\"></i> Running tests. Please do not refresh this page while your request is being processed...");
                    $("#div_netTestResults").fadeIn(200);
                });
            }else{
                $("#invalidHIPalerter").fadeIn(200);
            }
        }

    });

    function requestTest(destination){
        $.ajax({
            url: "/api/start_traceroute",
            data: {
                _token: csrf_token,
                destination: destination,
                type: $("#testType").val()
            },
            error: function(request, status, error) {
                $("#div_netTestResults").fadeOut(200, function(){
                    $("#div_netTestResults").html("<i class=\"fa fa-exclamation-triangle\"></i> An error occured while trying to initiate the test. Please try again later (E1-03)");
                    $("#div_netTestResults").fadeIn(200);
                    resetLGUserInterface();
                });
            },
            dataType: "json",
            success: function(data) {
                testID = data.traceID;
                checkTestResult();
            },
            type: "POST"
        });
    }

    function resetLGUserInterface(){
        testRunning = false;
        $("#btnRunTest").removeClass("disabled").removeClass("btn-default").addClass("btn-success");
        $("#btnRunTest").html("Run Test");
    }

    function checkTestResult(){

        $.ajax({
            url: "/api/check_result/" + testID.toString(),
            data: {
                _token: csrf_token
            },
            error: function(request, status, error) {
                $("#div_netTestResults").fadeOut(200, function(){
                    $("#div_netTestResults").html("<i class=\"fa fa-exclamation-triangle\"></i> Cannot access the test results. Please try again later (E1-04)");
                    $("#div_netTestResults").fadeIn(200);
                    clearTimeout(checkTick);
                    resetLGUserInterface();
                });
            },
            dataType: "json",
            success: function(data) {
                if(data.done == 1){
                    clearTimeout(checkTick);
                    $("#div_netTestResults").fadeOut(200, function(){
                        $("#div_netTestResults").html("<h4>Test Result:</h4><pre>" + data.result + "</pre>");
                        $("#div_netTestResults").fadeIn(200);
                        resetLGUserInterface();
                    });
                }
            },
            type: "POST"
        });

        checkTick = setTimeout("checkTestResult()", 1000);
    }

    function checkIPInfo(){
        $.ajax({
            url: "/api/ipinfo",
            data: {
                _token: csrf_token,
                ip: webClientIP
            },
            error: function(request, status, error) {
                $("#additionalConnectionDataLoadingDiv").fadeOut(200, function(){
                    $("#additionalConnectionDataLoadErrorDiv").fadeIn(200);
                });
            },
            dataType: "json",
            success: function(data) {

                $("#dp_netname").html(data.netname);
                $("#dp_asn").html(data.asn);
                $("#dp_asdescr").html(data.asdescr);

                $("#additionalConnectionDataLoadingDiv").fadeOut(200, function(){
                    $("#additionalConnectionData").fadeIn(200);
                });

                console.log(data);
            },
            type: "POST"
        });
    }

</script>
@endsection
