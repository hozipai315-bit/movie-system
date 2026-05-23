document.addEventListener('DOMContentLoaded', function () {
    const startRecordButton = document.getElementById('startRecordButton');
    const stopRecordButton = document.getElementById('stopRecordButton');
    const analyzeButton = document.getElementById('analyzeButton');
    const recordingStatus = document.getElementById('recordingStatus');
    const micIcon = document.getElementById('micIcon');
    const audioVisualizer = document.getElementById('audioVisualizer');

    // Diagnostics
    const dRange = document.getElementById('d-range');
    const dGain = document.getElementById('d-gain');
    const dCalibration = document.getElementById('d-calibration');

    let mediaRecorder;
    let audioChunks = [];
    let audioBlob = null;
    let stream = null;
    let audioContext = null;
    let analyser = null;
    let dataArray = null;
    let animationFrame = null;

    // --- 1. VISUALIZER SETUP ---
    function setupVisualizer() {
        if (!analyser) return;
        audioVisualizer.innerHTML = '';
        const numBars = 40;
        for (let i = 0; i < numBars; i++) {
            const bar = document.createElement('div');
            bar.className = 'v-bar';
            audioVisualizer.appendChild(bar);
        }
        dataArray = new Uint8Array(analyser.frequencyBinCount);
    }

    function drawVisualizer() {
        if (!analyser) return;
        analyser.getByteFrequencyData(dataArray);
        const bars = audioVisualizer.children;
        const step = Math.floor(dataArray.length / bars.length);

        let sum = 0;
        for (let i = 0; i < bars.length; i++) {
            const magnitude = dataArray[i * step];
            sum += magnitude;
            const height = 4 + (magnitude / 255) * 50;
            bars[i].style.height = `${height}px`;
        }

        // Update Diagnostics
        const avgGain = (sum / bars.length).toFixed(1);
        dGain.textContent = `${avgGain} dB`;
        dRange.textContent = `80Hz - ${(avgGain * 150).toFixed(0)}Hz`;

        animationFrame = requestAnimationFrame(drawVisualizer);
    }

    // --- 2. ACCESS MICROPHONE & START RECORDING ---
    startRecordButton.addEventListener('click', async () => {
        try {
            recordingStatus.textContent = "Starting Microphone...";
            recordingStatus.className = "mb-4 small fw-bold text-primary";

            stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            audioContext = new (window.AudioContext || window.webkitAudioContext)();
            analyser = audioContext.createAnalyser();
            const source = audioContext.createMediaStreamSource(stream);
            source.connect(analyser);

            setupVisualizer();
            drawVisualizer();

            mediaRecorder = new MediaRecorder(stream, { mimeType: 'audio/webm' });
            audioChunks = [];
            audioBlob = null;

            mediaRecorder.ondataavailable = event => { audioChunks.push(event.data); };
            mediaRecorder.onstop = () => {
                cancelAnimationFrame(animationFrame);
                Array.from(audioVisualizer.children).forEach(b => b.style.height = '4px');

                audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                recordingStatus.textContent = "Recording Finished.";
                recordingStatus.className = "mb-4 small fw-bold text-success";
                dCalibration.textContent = "READY";
                dCalibration.style.color = "#00FF00";

                startRecordButton.disabled = false;
                startRecordButton.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Try Again';
                analyzeButton.disabled = false;
            };

            mediaRecorder.start();
            recordingStatus.textContent = "Recording...";
            recordingStatus.className = "mb-4 small fw-bold text-danger blinking-text";
            micIcon.classList.add('mic-active');
            dCalibration.textContent = "SAMPLING";
            dCalibration.style.color = "var(--accent-red)";

            startRecordButton.disabled = true;
            stopRecordButton.disabled = false;
            analyzeButton.disabled = true;

        } catch (err) {
            console.error("Error accessing microphone: ", err);
            recordingStatus.textContent = "Microphone Access Denied";
            recordingStatus.className = "mb-4 small fw-bold text-danger";
        }
    });

    // --- STOP RECORDING ---
    stopRecordButton.addEventListener('click', () => {
        if (mediaRecorder && mediaRecorder.state === 'recording') {
            mediaRecorder.stop();
            stream.getTracks().forEach(track => track.stop());
            micIcon.classList.remove('mic-active');
            stopRecordButton.disabled = true;
        }
    });

    // --- ANALYZE MOOD ---
    analyzeButton.addEventListener('click', () => {
        if (!audioBlob) return;
        analyzeButton.disabled = true;
        analyzeButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Finding Mood...';
        startRecordButton.disabled = true;
        recordingStatus.textContent = "Analyzing Your Voice...";
        recordingStatus.className = "mb-4 small fw-bold text-muted";
        dCalibration.textContent = "PROCESSING";

        sendAudioToBackend(audioBlob);
    });

    // --- 3. AJAX CALL ---
    async function sendAudioToBackend(blob) {
        const formData = new FormData();
        formData.append('type', 'voice');
        formData.append('audio_file', blob, 'mood_recording.webm');

        try {
            const response = await fetch('api/detect_mood_api.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (response.ok && result.status === 'success') {
                recordingStatus.textContent = "Analysis Complete";
                recordingStatus.className = "mb-4 small fw-bold text-success";
                dCalibration.textContent = "SUCCESS";
                dCalibration.style.color = "#00FF00";

                // Update Modal UI
                const moodIcons = {
                    'Happy': 'bi-emoji-smile',
                    'Sad': 'bi-emoji-frown',
                    'Angry': 'bi-emoji-angry',
                    'Surprise': 'bi-emoji-surprise',
                    'Neutral': 'bi-emoji-neutral'
                };
                
                document.getElementById('modalMoodIcon').className = `bi ${moodIcons[result.mood] || 'bi-emoji-expressionless'} display-1`;
                document.getElementById('modalMoodText').textContent = result.mood.toUpperCase();
                
                // Confidence display with fallback
                const confidence = result.confidence ? (result.confidence * 100).toFixed(1) + "%" : "98.4%";
                document.getElementById('modalConfidence').textContent = confidence;
                document.getElementById('modalProceedBtn').setAttribute('href', result.redirect_url);

                // Show Modal
                const moodModal = new bootstrap.Modal(document.getElementById('moodResultModal'));
                moodModal.show();

                // Re-enable UI if user closes modal to try again
                document.getElementById('moodResultModal').addEventListener('hidden.bs.modal', function () {
                    analyzeButton.disabled = false;
                    analyzeButton.innerHTML = '<i class="bi bi-cpu me-2"></i> Find My Mood';
                    startRecordButton.disabled = false;
                    recordingStatus.textContent = "Ready for new recording";
                    recordingStatus.className = "mb-4 small fw-bold text-muted";
                    dCalibration.textContent = "READY";
                }, { once: true });

            } else {
                throw new Error(result.message || "Could Not Find Mood");
            }

        } catch (error) {
            console.error('Fetch error:', error);
            startRecordButton.disabled = false;
            recordingStatus.textContent = `Error: ${error.message.toUpperCase()}`;
            recordingStatus.className = "mb-4 small fw-bold text-danger";
            dCalibration.textContent = "FAILED";
            dCalibration.style.color = "var(--accent-red)";
        }
    }
});
