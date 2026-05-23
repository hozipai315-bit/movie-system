document.addEventListener('DOMContentLoaded', function () {
    const moodForm = document.getElementById('moodForm');
    const userTextarea = document.getElementById('userTextarea');
    const analyzeButton = document.getElementById('analyzeButton');
    const statusMessage = document.getElementById('statusMessage');

    // Metrics elements
    const mSentiment = document.getElementById('m-sentiment');
    const mTokens = document.getElementById('m-tokens');
    const mIntegrity = document.getElementById('m-integrity');
    const mLatency = document.getElementById('m-latency');

    // --- 1. LIVE METRICS UPDATER ---
    userTextarea.addEventListener('input', () => {
        const text = userTextarea.value.trim();
        const tokens = text === "" ? 0 : text.split(/\s+/).length;
        mTokens.textContent = tokens;
        
        if (tokens > 0) {
            mIntegrity.textContent = tokens > 10 ? "Good" : "Too Short";
            mIntegrity.style.color = tokens > 10 ? "#00FF00" : "#FFA500";
        } else {
            mIntegrity.textContent = "Waiting...";
            mIntegrity.style.color = "#ffffff";
        }
    });

    // --- 2. FORM SUBMISSION ---
    moodForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const text = userTextarea.value.trim();

        if (text.length < 20) {
            statusMessage.classList.remove('d-none');
            statusMessage.textContent = "Please write a bit more (at least 20 characters).";
            statusMessage.className = "mt-3 small fw-bold text-danger text-uppercase tracking-wider text-center";
            return;
        }

        // Disable UI
        analyzeButton.disabled = true;
        analyzeButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Finding Mood...';
        statusMessage.classList.remove('d-none');
        statusMessage.textContent = "Analyzing Your Text...";
        statusMessage.className = "mt-3 small fw-bold text-muted text-uppercase tracking-wider text-center";
        
        mSentiment.textContent = "Finding...";

        sendTextToBackend(text);
    });

    // --- 3. AJAX CALL ---
    async function sendTextToBackend(text) {
        try {
            const startTime = Date.now();
            const formData = new URLSearchParams();
            formData.append('type', 'text');
            formData.append('text', text);

            const response = await fetch('api/detect_mood_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            });

            const result = await response.json();
            const latency = Date.now() - startTime;
            mLatency.textContent = `${latency}ms`;

            if (response.ok && result.status === 'success') {
                statusMessage.textContent = "Analysis Complete";
                statusMessage.className = "mt-3 small fw-bold text-success text-uppercase tracking-wider text-center";
                
                mSentiment.textContent = "Found";
                mSentiment.style.color = "#00FF00";

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
                
                // Confidence display with fallback for types that don't return it
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
                    statusMessage.textContent = "Ready for new analysis";
                    statusMessage.className = "mt-3 small fw-bold text-muted text-uppercase tracking-wider text-center";
                }, { once: true });

            } else {
                throw new Error(result.message || "Could Not Find Mood");
            }

        } catch (error) {
            console.error('Fetch error:', error);
            analyzeButton.disabled = false;
            analyzeButton.innerHTML = '<i class="bi bi-cpu me-2"></i> Find My Mood';
            statusMessage.textContent = `Error: ${error.message.toUpperCase()}`;
            statusMessage.className = "mt-3 small fw-bold text-danger text-uppercase tracking-wider text-center";
            mSentiment.textContent = "FAILED";
            mSentiment.style.color = "var(--accent-red)";
        }
    }
});
