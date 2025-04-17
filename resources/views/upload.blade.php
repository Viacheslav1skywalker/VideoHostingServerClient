<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Upload</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        body {
            background-color: #1e1e1e;
            color: #fff;
        }
        .upload-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            background-color: #2c2c2c;
            box-shadow: 0 0 15px rgba(255, 64, 129, 0.3),
                        0 0 30px rgba(255, 64, 129, 0.5),
                        0 0 50px rgba(255, 64, 129, 0.7);
            border: 1px solid #ff4081;
        }
        .upload-container h2 {
            color: #ff4081;
            text-shadow: 0 0 10px #ff4081;
        }
        .form-label {
            color: #eee;
        }
        .form-control {
            background-color: #333;
            color: #fff;
            border: 1px solid #555;
        }
        .form-control:focus {
            background-color: #444;
            border-color: #ff4081;
            box-shadow: 0 0 5px rgba(255, 64, 129, 0.5);
        }
        .progress {
            height: 25px;
            margin-top: 20px;
            background-color: #333;
            border-radius: 5px;
            border: 1px solid #555;
            overflow: hidden;
        }
        .progress-bar {
            background-color: #ff4081;
            color: #000;
            text-align: center;
            line-height: 25px;
            box-shadow: 0 0 5px #ff4081;
            width: 0%;
        }
        .btn-primary {
            background-color: #ff4081;
            border-color: #ff4081;
            box-shadow: 0 0 10px rgba(255, 64, 129, 0.5);
        }
        .btn-primary:hover {
            background-color: #e63977;
            border-color: #e63977;
        }
        .alert {
            color: #fff;
            border: 1px solid;
            border-radius: 5px;
            padding: 10px;
            margin-top: 20px;
            display: none;
        }
        .alert-success {
            background-color: rgba(0, 200, 0, 0.3);
            border-color: rgba(0, 200, 0, 0.5);
        }
        .alert-danger {
            background-color: rgba(200, 0, 0, 0.3);
            border-color: rgba(200, 0, 0, 0.5);
        }
    </style>
</head>
<body>

    <div class="container upload-container">
        <h2 class="text-center mb-4">Upload Video</h2>
        
        <div class="mb-3">
            <label for="videoFile" class="form-label">Select video file (MP4, MOV, WMV, AVI, AVCHD, FLV, SWF, F4V, MKV, WEBM, max 2GB)</label>
            <input class="form-control" type="file" id="videoFile" accept="video/*">
        </div>

        <div class="progress">
            <div id="progress" class="progress-bar progress-bar-striped progress-bar-animated" 
                 role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
        </div>

        <button id="uploadBtn" class="btn btn-primary w-100 mt-3">Upload</button>

        <div id="successAlert" class="alert alert-success">
            Video uploaded successfully!
        </div>

        <div id="errorAlert" class="alert alert-danger">
            Error occurred during upload.
        </div>
        <p id="progress-text" style="margin-top: 5px;"></p>
        <p id="video-link-message" style="color: green; display: none;"></p>
    </div>

<script>
    const fileInput = document.getElementById('videoFile');
    const progressBar = document.getElementById('progress');
    const progressText = document.getElementById('progress-text');
    const videoLinkMessage = document.getElementById('video-link-message');

    fileInput.addEventListener('change', handleFileUpload);

    function generateRandomString(length = 20) {
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let result = '';

        for (let i = 0; i < length; i++) {
            const randomIndex = Math.floor(Math.random() * characters.length);
            result += characters[randomIndex];
        }

        return result;
    }

    function handleFileUpload(event) {
        const file = event.target.files[0];
        const fileExt = file.name.split('.').pop();
        const chunkSize = 1024 * 1024; 
        let start = 0;
        let chankCounts = 1;
        const totalChunks = Math.ceil(file.size / chunkSize);
        const fileId = generateRandomString();

        while (start < file.size) {
            uploadChunk(file.slice(start, start + chunkSize), fileId, chankCounts, totalChunks, fileExt);
            start += chunkSize;
            chankCounts += 1;
        }

        getProgress(fileId);
    }

    function uploadChunk(chunk, fileId, number_chunk, totalChunks, fileExt) {
        const formData = new FormData();
        formData.append('file', chunk);
        formData.append('fileId', fileId);
        formData.append('number_chunk', number_chunk);
        formData.append('totalChunks', totalChunks);
        formData.append('fileExtension', fileExt);

        const csrfToken = '{{ csrf_token() }}';
        fetch('/upload', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken 
            }
        });
    }

    function getProgress(fileId) {
        const checkProgress = () => {
            fetch(`/check-progress/${fileId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text(); 
            })
            .then(data => {
                if (data.includes('master.m3u8')) {
                    clearInterval(intervalId);
                    videoLinkMessage.style.display = 'block';
                    updateProgressBar(100);
                    videoLinkMessage.innerText = `Вы можете вставить эту ссылку в плеер и посмотреть видео: ${data}`;
                    console.log('Загрузка завершена!');
                } else {
                    const progressPercentage = parseInt(data, 10);
                    updateProgressBar(progressPercentage);
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
            });
        };

        const intervalId = setInterval(checkProgress, 2000);
    }

    function updateProgressBar(percentage) {
        progressBar.style.width = percentage + '%';
        progressText.innerText = `Загрузка: ${percentage}%`;
    }
</script>
</body>
</html>