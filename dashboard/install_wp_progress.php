<!DOCTYPE html>
<html>
<head>
    <title>Installing WordPress</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script>
        function updateProgress() {
            let progress = 0;
            const interval = setInterval(() => {
                progress += 10;
                document.getElementById('progress-bar').style.width = progress + '%';
                document.getElementById('progress-bar').innerText = progress + '%';
                if (progress >= 100) {
                    clearInterval(interval);
                    window.location.href = 'install_wp_success.php';
                }
            }, 1000);
        }
    </script>
</head>
<body onload="updateProgress()" class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h3>Installing WordPress</h3>
                    </div>
                    <div class="card-body">
                        <div class="progress">
                            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;">0%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>