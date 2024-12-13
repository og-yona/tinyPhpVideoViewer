<?php
// Define the folder where the videos are stored
$videoFolder = './videos'; // Adjust this path if needed

// Get all .mp4 and .bin files in the folder
$mp4Files = glob("$videoFolder/*.mp4");
// ..some http-servers/browsers apparently have difficulties reading 
//   MIME of mp4-files, but if they are renamed as ".bin" they do work normally..
$binFiles = glob("$videoFolder/*.bin"); 
$videos = array_merge($binFiles, $mp4Files);
    
// Sort files by newest first
usort($videos, function ($a, $b) {
    return filemtime($b) - filemtime($a);
});

// Function to get video duration using ffprobe
function getVideoDuration($filePath) {
    $command = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($filePath);
    $duration = shell_exec($command);
    return $duration ? gmdate("H:i:s", $duration) : "Unknown";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>tinyPhpVideoViewer</title>
    <link rel="stylesheet" href="ogy.css">
</head>
<body class="theme-Orange">
    <div class="container">
        <?php //======================
        // Video player panel
        //===========================?>
        <div class="video-panel">
            <video id="video-player" controls>
                <source id="video-source" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <div id="loading-indicator" class="loading-indicator">Loading...</div>
            <h2 id="current-video-title" class="video-title"></h2>
        </div>
        <?php //======================
        // Resizer
        //===========================?>
        <div class="resizer" id="resizer"></div>
        <?php //======================
        // File browser/options panel
        //===========================?>
        <div class="list-panel">
            <div class="theme-selector">
                <div class="theme-button D-Orange" data-theme="Orange">D</div>
                <div class="theme-button D-Matrix" data-theme="Matrix">D</div>
                <div class="theme-button L-Light1" data-theme="Light1">L</div>
                <div class="theme-button L-Light2" data-theme="Light2">L</div>
            </div>
            <h2>Available Videos</h2>
            <div class="controls">
                <input type="text" id="filter-input" placeholder="Search videos by name...">
                <div class="order-row">
                    <a href="#" id="clear-selection" class="clear-selection">Clear Selection</a>
                    <button id="reverse-button">Reverse Order</button>
                    <span id="order-indicator">(Newest to Oldest)</span>
                </div>
            </div>
            <ul id="video-list">
                <?php foreach ($videos as $video): ?>
                    <?php $fakeName = pathinfo($video, PATHINFO_FILENAME) . '.mp4'; ?>
                    <li data-video-src="<?= htmlspecialchars($video) ?>">
                        <a href="#" class="video-link">
                            <?= htmlspecialchars($fakeName) ?>
                        </a>
                        <div class="details">
                            <?= round(filesize($video) / 1024 / 1024, 2) ?> MB | <?= date("Y-m-d H:i", filemtime($video)) ?> | Duration: <?= getVideoDuration($video) ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php //======================
    // Scripts
    //===========================?>
    <script>
    
        const resizer = document.getElementById('resizer');
        const videoPanel = document.querySelector('.video-panel');
        const listPanel = document.querySelector('.list-panel');
        const container = document.querySelector('.container');
        const themeButtons = document.querySelectorAll('.theme-button');
        const videoPlayer = document.getElementById('video-player');
        const videoSource = document.getElementById('video-source');
        const videoTitle = document.getElementById('current-video-title');
        const loadingIndicator = document.getElementById('loading-indicator');
        const videoList = document.getElementById('video-list');
        const filterInput = document.getElementById('filter-input');
        const reverseButton = document.getElementById('reverse-button');
        const orderIndicator = document.getElementById('order-indicator');
        const clearSelection = document.getElementById('clear-selection');
        
        //============================        
        // Resizer functionality
        
        let isResizing = false;

        // add mouse click listener for the resizer/panel splitter
        resizer.addEventListener('mousedown', (e) => {
            isResizing = true;
            document.body.style.cursor = 'col-resize';

            // Attach resizing events
            document.addEventListener('mousemove', resizePanels);
            document.addEventListener('mouseup', stopResizing);
        });

        // resize the panels to match mouse position
        function resizePanels(e) {
            if (!isResizing) return;

            const containerWidth = container.offsetWidth;
            const newVideoWidth = e.clientX;

            // Prevent extreme panel sizes
            if (newVideoWidth > 200 && newVideoWidth < containerWidth - 200) {
                const videoWidthPercentage = (newVideoWidth / containerWidth) * 100;
                const listWidthPercentage = 100 - videoWidthPercentage;

                // Update panel sizes
                videoPanel.style.flex = `0 0 ${videoWidthPercentage}%`;
                listPanel.style.flex = `0 0 ${listWidthPercentage}%`;

                // Align the resizer
                resizer.style.flex = `0 0 0.3%`;
            }
        }

        // release the resizing per mouse
        function stopResizing() {
            if (!isResizing) return;

            isResizing = false;
            document.body.style.cursor = 'default';            
        }

        //============================
        // Theme selector functionality
        themeButtons.forEach(button => {
            button.addEventListener('click', () => {
                document.body.className = `theme-${button.dataset.theme}`;
            });
        });

        //============================
        // Filter functionality
        filterInput.addEventListener('input', () => {
            const query = filterInput.value.toLowerCase();
            document.querySelectorAll('#video-list li').forEach(li => {
                const text = li.textContent.toLowerCase();
                li.style.display = text.includes(query) ? '' : 'none';
            });
        });

        //============================
        // Reverse order functionality
        reverseButton.addEventListener('click', () => {
            const items = Array.from(videoList.children);
            videoList.innerHTML = '';
            items.reverse().forEach(item => videoList.appendChild(item));

            isReversed = !isReversed;
            orderIndicator.textContent = isReversed ? "(Oldest to Newest)" : "(Newest to Oldest)";
        });

        //============================
        // Clear selection functionality
        clearSelection.addEventListener('click', () => {
            videoSource.src = '';
            videoTitle.textContent = '';
            videoPlayer.load();

            // Remove active state and clear title
            document.querySelectorAll('#video-list li').forEach(item => item.classList.remove('active'));
        });

        //============================
        // Video selection functionality
        videoList.addEventListener('click', (e) => {
            const li = e.target.closest('li');
            if (!li) return;

            const videoSrc = li.getAttribute('data-video-src');
            const videoName = li.querySelector('a').textContent;

            videoSource.src = videoSrc;
            videoPlayer.load();

            videoTitle.textContent = videoName;
            loadingIndicator.style.display = 'block';

            videoPlayer.addEventListener('canplay', () => {
                loadingIndicator.style.display = 'none';
            }, { once: true });

            document.querySelectorAll('#video-list li').forEach(item => item.classList.remove('active'));
            li.classList.add('active');
        });
        
        
        //============================
        // Query URL / direct video sharing
        
        // get the query parameter from current url
        function getQueryParameter(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }

        // set the query parameter to the url
        function setQueryParameter(name, value) {
            const url = new URL(window.location);
            url.searchParams.set(name, value);
            window.history.replaceState({}, '', url); // Update URL without reloading
        }

        // start playing a video/file from current url parameter
        function playVideoFromQuery() {
            const videoParam = getQueryParameter('play');
            if (videoParam) {
                const videoListItems = Array.from(videoList.querySelectorAll('li'));
                const matchingItem = videoListItems.find(item =>
                    item.querySelector('a').textContent.trim() === videoParam
                );

                if (matchingItem) {
                    matchingItem.click(); // Simulate click to play the video
                }
            }
        }

        // add "Copy URL" -buttons as prefix for each of the file-li-element
        function addShareLinkButton() {
            videoList.querySelectorAll('li').forEach(item => {
                const shareButton = document.createElement('button');
                shareButton.textContent = 'Copy URL';
                shareButton.style.marginLeft = '1px';
                shareButton.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const videoName = item.querySelector('a').textContent.trim();
                    const url = new URL(window.location);
                    url.searchParams.set('play', videoName);
                    navigator.clipboard.writeText(url.toString()).then(() => {
                        // alert('Link copied to clipboard!');
                    });
                });
                item.prepend(shareButton);       // before filename
                //item.appendChild(shareButton); // after details
            });
        }

        // Automatically select video from query parameter on page load
        document.addEventListener('DOMContentLoaded', () => {
            playVideoFromQuery();
            addShareLinkButton();
        });

        // Update URL when a video is selected
        videoList.addEventListener('click', (e) => {
            const li = e.target.closest('li');
            if (li) {
                const videoTitle = li.querySelector('a').textContent.trim();
                setQueryParameter('play', videoTitle);
            }
        });
        
    </script>
</body>
</html>
