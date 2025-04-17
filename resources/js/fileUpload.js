document.getElementById('uploadBtn').addEventListener('click', async () => {
  const file = document.getElementById('videoFile').files[0];
  if (!file) return;

  const chunkSize = 10 * 1024 * 1024; // 10 МБ
  const totalChunks = Math.ceil(file.size / chunkSize);
  const fileId = Date.now() + '-' + Math.random().toString(36).substr(2);

  for (let i = 0; i < totalChunks; i++) {
    const chunk = file.slice(i * chunkSize, (i + 1) * chunkSize);
    const formData = new FormData();
    formData.append('chank', chunk);
    formData.append('fileId', fileId);
    formData.append('chunkNumber', i + 1);
    formData.append('totalChunks', totalChunks);

    try {
      await fetch('/upload', {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': '...' } // Для Laravel
      });
      document.getElementById('progress').value = (i + 1) / totalChunks * 100;
    } catch (error) {
      console.error('Ошибка загрузки чанка:', error);
      break;
    }
  }
});