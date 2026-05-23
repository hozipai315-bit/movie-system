document.addEventListener('DOMContentLoaded', function () {
    const startButton = document.getElementById('startButton');
    const captureButton = document.getElementById('captureButton');
    const webcamPreview = document.getElementById('webcamPreview');
    const webcamPlaceholder = document.getElementById('webcamPlaceholder');
    const canvasCapture = document.getElementById('canvasCapture');
    const statusMessage = document.getElementById('statusMessage');
    const scanLine = document.getElementById('scanLine');
    const neuralFeed = document.getElementById('neuralFeed');
    const statusIndicator = document.getElementById('statusIndicator');

    let stream = null;

    // --- 1. NEURAL FEED GENERATOR ---
    const feedPatterns = [
        "Analyzing face...",
        "Signal: Good",
        "Checking AI model...",
        "Secure connection established",
        "Calibrating camera...",
        "Reading expressions...",
        "Mood detected",
        "Syncing results...",
        "Speed: Fast",
        "Ready to use"
    ];

    function addToFeed() {
        const line = document.createElement('div');
        line.className = 'feed-line';
        const timestamp = new Date().toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const randomPattern = feedPatterns[Math.floor(Math.random() * feedPatterns.length)];
        line.textContent = `[${timestamp}] ${randomPattern}`;
        neuralFeed.prepend(line);
        
        if (neuralFeed.children.length > 15) {
            neuralFeed.lastElementChild.remove();
        }
    }

    setInterval(addToFeed, 2000);

    // --- 2. ACCESS WEBCAM ---
    startButton.addEventListener('click', async () => {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }

        try {
            statusMessage.textContent = "Starting Camera...";
            statusIndicator.textContent = "Connecting...";
            statusIndicator.style.color = "var(--accent-red)";

            stream = await navigator.mediaDevices.getUserMedia({ video: true });

            webcamPreview.srcObject = stream;
            webcamPreview.style.display = 'block';
            webcamPlaceholder.style.display = 'none';
            scanLine.style.display = 'block';

            webcamPreview.onloadedmetadata = () => {
                webcamPreview.play();
                statusMessage.textContent = "Camera On. Please look at the screen.";
                statusIndicator.textContent = "Connected";
                statusIndicator.style.color = "#00FF00";
                statusIndicator.classList.remove('animate-flicker');
                
                captureButton.disabled = false;
                startButton.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i> Start Over';
            };

        } catch (err) {
            console.error("Error accessing webcam: ", err);
            statusMessage.textContent = "Camera Access Denied";
            statusIndicator.textContent = "Error";
            captureButton.disabled = true;
        }
    });

    // --- 3. CAPTURE IMAGE FRAME ---
    captureButton.addEventListener('click', () => {
        if (!stream) return;

        const context = canvasCapture.getContext('2d');
        canvasCapture.width = webcamPreview.videoWidth;
        canvasCapture.height = webcamPreview.videoHeight;

        context.translate(canvasCapture.width, 0);
        context.scale(-1, 1);
        context.drawImage(webcamPreview, 0, 0, canvasCapture.width, canvasCapture.height);

        const imageDataURL = canvasCapture.toDataURL('image/jpeg', 0.9);

        captureButton.disabled = true;
        startButton.disabled = true;
        scanLine.style.animationDuration = '0.5s'; 
        
        statusMessage.textContent = "Finding Mood...";
        statusIndicator.textContent = "Finding...";
        statusIndicator.classList.add('animate-flicker');

        sendImageToBackend(imageDataURL);
    });

    // --- 4. AJAX CALL ---
    async function sendImageToBackend(imageData) {
        try {
            const formData = new URLSearchParams();
            formData.append('type', 'face');
            formData.append('image_data', imageData);

            const response = await fetch('api/detect_mood_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            });

            const result = await response.json();

            if (response.ok && result.status === 'success') {
                statusMessage.textContent = "Analysis Complete";
                statusIndicator.textContent = "Found";
                statusIndicator.style.color = "#00FF00";
                
                scanLine.style.display = 'none';

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
                document.getElementById('modalConfidence').textContent = (result.confidence * 100).toFixed(1) + "%";
                document.getElementById('modalProceedBtn').setAttribute('href', result.redirect_url);

                // Show Modal
                const moodModal = new bootstrap.Modal(document.getElementById('moodResultModal'));
                moodModal.show();

                // Re-enable UI if user closes modal to try again
                document.getElementById('moodResultModal').addEventListener('hidden.bs.modal', function () {
                    captureButton.disabled = false;
                    startButton.disabled = false;
                    scanLine.style.display = 'block';
                    scanLine.style.animationDuration = '3s';
                    statusMessage.textContent = "Ready for new scan";
                    statusIndicator.textContent = "Connected";
                }, { once: true });

            } else {
                throw new Error(result.message || "SYNTHESIS_ERROR");
            }

        } catch (error) {
            console.error('Fetch error:', error);
            startButton.disabled = false;
            statusMessage.textContent = `CRITICAL_ERROR: ${error.message.toUpperCase()}`;
            statusIndicator.textContent = "FAILED";
            statusIndicator.style.color = "var(--accent-red)";
            scanLine.style.animationDuration = '3s';
        }
    }
});
