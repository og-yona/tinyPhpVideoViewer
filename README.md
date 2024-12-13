# tinyPhpVideoViewer

## About
Small and simple .php-page for mp4-video/clip sharing - from a http-/ftp-/php-server you have access to.

Allows browsing of videofiles in a server from a webpage and streaming/playing the videos through browser's video playing capabilities, and sharing a direct URL's to certain videos.

Nice and simple way to share your videos/clips with your friends ("semi-")privately.

![tinyphpvideoviewer](https://yona.kapsi.fi/tinyphpviewer.jpg)

## Usage
- Simply copy/put the index.php and ogy.css -files on a place accessible by a http-browser
- Add a /videos/ subdirectory, and put your .mp4 videos in there
- Open the location through web-browser and start playing and sharing your clips

## Features
- Responsive page/layout - No page reloads
- 2 Dark themes and 2 light themes to select from
- Fetches a list of video-files from a specified *('./videos/' by default )* subfolder
-- Url-parameter file-selections
-- Looks the folder for both *.mp4* and *.bin* files - as for some reason some servers/browsers need renaming the .mp4's as .bins to make playing work - dont ask me why.
-- Show basic details about the files : filesize, modified date and duration (the php server needs to have ffprobe to be able to get the durations)
- Sort files by modified date on server 
-- Order reversible
- Filter videos by their filenames - filtering is case-insensitive and treated as substring
- Copy direct URL's to specific videos to share a video link to your friends
- Auto-select the correct video from URL-parameter when loading a page (from a shared link)
- Allow resizing the video/browser -panels by dragging the separator/resizer

## License
WTFPL
