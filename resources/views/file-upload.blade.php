// HTML input element
<input type="file" id="file-upload">
<div id="progress-container" style="width: 100%; background-color: #f3f3f3; border-radius: 5px; margin-top: 10px;">
    <div id="progress-bar" style="width: 0%; height: 30px; background-color: #4caf50; border-radius: 5px;"></div>
</div>
<p id="progress-text" style="margin-top: 5px;"></p>
<p id="video-link-message" style="color: green; display: none;"></p>

<script>
    // вынести в будущем в отдельные модели
    // отрефакторить код до чистого
    const fileInput = document.getElementById('file-upload');
    const progressBar = document.getElementById('progress-bar');
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