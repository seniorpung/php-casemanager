        
                var scannerDevices = null;
                var _this = this;
        
                //JSPrintManager WebSocket settings
                JSPM.JSPrintManager.auto_reconnect = true;
                JSPM.JSPrintManager.start();
                JSPM.JSPrintManager.WS.onStatusChanged = function () {
                    if (jspmWSStatus()) {
                        //get scanners
                        JSPM.JSPrintManager.getScanners().then(function (scannersList) {
                            scannerDevices = scannersList;
                            var options = '';
                            for (var i = 0; i < scannerDevices.length; i++) {
                                options += '<option>' + scannerDevices[i] + '</option>';
                            }
                            $('#scannerName').html(options);
                        });
                    }
                };
        
                //Check JSPM WebSocket status
                function jspmWSStatus() {
                    if (JSPM.JSPrintManager.websocket_status == JSPM.WSStatus.Open)
                        return true;
                    else if (JSPM.JSPrintManager.websocket_status == JSPM.WSStatus.Closed) {
                        console.warn('JSPrintManager (JSPM) is not installed or not running! Download JSPM Client App from https://neodynamic.com/downloads/jspm');
                        return false;
                    }
                    else if (JSPM.JSPrintManager.websocket_status == JSPM.WSStatus.Blocked) {
                        alert('JSPM has blocked this website!');
                        return false;
                    }
                }
        
                //Do scanning...
                function doScanning() {
                    if (jspmWSStatus()) {
        
                        //create ClientScanJob
                        var csj = new JSPM.ClientScanJob();
                        //scanning settings
                        csj.scannerName = $('#scannerName').val();
                        csj.pixelMode = JSPM.PixelMode[$('#pixelMode').val()];
                        csj.resolution = parseInt($('#resolution').val());
                        csj.imageFormat = JSPM.ScannerImageFormatOutput[$('#imageFormat').val()];
        
                        let _this = this;
                        //get output image
                        csj.onUpdate = (data, last) => {
                            if (!(data instanceof Blob)) {
                                console.info(data);
                                return;
                            }
                            var imgBlob = new Blob([data]);
        
                            if (imgBlob.size == 0) return;
                            
                            var data_type = 'image/jpg';
                            if (csj.imageFormat == JSPM.ScannerImageFormatOutput.PNG) data_type = 'image/png';
                            //create html image obj from scan output
                            var img = URL.createObjectURL(imgBlob, { type: data_type });
                            //scale original image to be screen size friendly
                            var imgScale = { width: Math.round(96.0 / csj.resolution * 100.0) + "%", height: 'auto' };
                            $('#scanOutput').css(imgScale);
                            $('#scanOutput').attr("src", img);
                        }
        
                        csj.onError = function (data, is_critical) {
                            console.error(data);
                        };
        
                        //Send scan job to scanner!
                        csj.sendToClient().then(data => console.info(data));
        
                    }
                }
        
