<!DOCTYPE html>
<html>
<head>
    <title>Upload Audio and Generate Waveform</title>
    <link rel="stylesheet" href="https://unpkg.com/wavesurfer.js/dist/wavesurfer.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            background-color: #f0f0f0;
            height: 100vh;
            margin: 0;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }
        input[type=file] {
            margin-bottom: 10px;
            padding: 10px;
            width: 100%;
            box-sizing: border-box;
        }
        button[type=submit] {
            padding: 10px 20px;
            background-color: #000000;
            color: white;
            border: none;
            cursor: pointer;
            width: 100%;
        }
        button[type=submit]:hover {
            background-color: #000000;
        }
        #fileSizeError {
            color: red;
            display: none;
            margin-top: 10px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 600px;
            background-color: #fff;
            margin-top: 20px;
            display: none; /* Initially hide card */
        }
        #waveform {
            width: 100%;
            height: 200px; /* Initial height */
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            overflow: hidden;
        }
        #audioControls {
            display: none; /* Initially hide audio controls */
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-top: 10px;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
        }
        #audioControls button {
            margin-top: 10px;
            padding: 10px;
            background-color: transparent;
            color: black;
            border: none;
            cursor: pointer;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 1.5rem;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: background-color 0.3s ease;
        }
        #audioControls button:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }
        #audioControls button:focus {
            outline: none;
        }
        #audioControls button.play-icon:before {
            content: '\25B6'; /* Play icon */
        }
        #audioControls button.pause-icon:before {
            content: '\IIII'; /* Pause icon */
        }
        #audioControls span {
            margin: 10px 0;
            font-size: 0.9rem;
            color: #666;
        }
        .controls {
            margin-top: 20px;
            width: 100%;
            display: flex;
            justify-content: space-between;
        }
        .control-label {
            font-weight: bold;
        }
        .slider-container {
            width: 50%;
        }
    </style>
</head>
<body>
    <h1>Upload Audio (Max 30s) and Generate Waveform</h1>
    <form id="uploadForm">
        <input type="file" name="audio" id="audioInput" accept="audio/*" required>
        <button type="submit">Upload</button>
        <span id="fileSizeError">File exceeds 30 seconds</span>
    </form>
    <div class="card">
        <div id="audioControls">
            <button id="playButton" class="play-icon"></button>
            <button id="pauseButton" class="pause-icon" style="display: none;"></button>
            <span id="currentTime">0:00</span> / <span id="duration">0:00</span>
        </div>
        <div id="waveform"></div>
        <div class="controls">
            <div class="slider-container">
                <label class="control-label">Adjust Height:</label>
                <input type="range" id="heightSlider" min="100" max="500" value="200">
            </div>
            <div class="slider-container">
                <label class="control-label">Adjust Width:</label>
                <input type="range" id="widthSlider" min="50" max="600" value="500">
            </div>
        </div>
    </div>
    <audio id="audioPlayer" controls style="display: none;"></audio>
    
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script src="https://unpkg.com/wavesurfer.js"></script>
    <script>
        document.getElementById('uploadForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const file = document.getElementById('audioInput').files[0];

            if (file && file.duration > 30) {
                document.getElementById('fileSizeError').style.display = 'block';
                return;
            } else {
                document.getElementById('fileSizeError').style.display = 'none';
            }

            const formData = new FormData();
            formData.append('audio', file);

            axios.post('/upload', formData)
                .then(function (response) {
                    const audioUrl = response.data.url;
                    displayWaveformAndAudioControls(audioUrl);
                })
                .catch(function (error) {
                    console.error('Error uploading audio:', error);
                });
        });

        function displayWaveformAndAudioControls(url) {
            const wavesurfer = WaveSurfer.create({
                container: '#waveform',
                waveColor: 'black',
                progressColor: 'black',
                backend: 'MediaElement'
            });
            wavesurfer.load(url);

            const audioPlayer = document.getElementById('audioPlayer');
            audioPlayer.src = url;
            audioPlayer.style.display = 'none'; // Initially hide audio player

            wavesurfer.on('ready', function() {
                document.querySelector('.card').style.display = 'block'; // Display card
                document.getElementById('audioControls').style.display = 'flex'; // Display audio controls
                document.getElementById('duration').textContent = formatTime(wavesurfer.getDuration());
            });

            wavesurfer.on('audioprocess', function () {
                document.getElementById('currentTime').textContent = formatTime(wavesurfer.getCurrentTime());
            });

            wavesurfer.on('seek', function () {
                document.getElementById('currentTime').textContent = formatTime(wavesurfer.getCurrentTime());
            });

            const playButton = document.getElementById('playButton');
            const pauseButton = document.getElementById('pauseButton');

            playButton.addEventListener('click', function() {
                wavesurfer.play();
                playButton.style.display = 'none';
                pauseButton.style.display = 'inline-block';
            });

            pauseButton.addEventListener('click', function() {
                wavesurfer.pause();
                playButton.style.display = 'inline-block';
                pauseButton.style.display = 'none';
            });

            const heightSlider = document.getElementById('heightSlider');
            heightSlider.addEventListener('input', function() {
                adjustWaveformHeight(this.value);
            });

            const widthSlider = document.getElementById('widthSlider');
            widthSlider.addEventListener('input', function() {
                adjustWaveformWidth(this.value);
            });
        }

        function formatTime(time) {
            const minutes = Math.floor(time / 60);
            const seconds = Math.floor(time % 60);
            const formattedTime = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            return formattedTime;
        }

        function adjustWaveformHeight(height) {
            const waveformContainer = document.getElementById('waveform');

            const firstChild = waveformContainer.firstElementChild;

            if (firstChild && firstChild.tagName.toLowerCase() === 'div') {
                const shadowRoot = firstChild.shadowRoot;

                if (shadowRoot) {
                    const scrollDiv = shadowRoot.querySelector('.scroll');
                    const canvasesDiv = scrollDiv.querySelector('.canvases');

                    if (canvasesDiv) {
                        const canvases = canvasesDiv.querySelectorAll('canvas');
                        canvases.forEach(canvas => {
                            canvas.style.height = `${height}px`;
                        });

                        // Additionally, adjust the height of the progress bar if available
                        const progress = scrollDiv.querySelector('.progress');
                        if (progress) {
                            progress.style.height = `${height}px`;
                        }
                    }
                }
            }
        }


        function adjustWaveformWidth(width) {
            const waveformContainer = document.getElementById('waveform');
            waveformContainer.style.width = `${width}px`;
        }
    </script>
</body>
</html>
