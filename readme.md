# Youtube Upload/Commenter Bot for Meme Compilation Bot

This bot is specifically designed to upload the meme compilations created using https://github.com/EthanSK/Meme-Compilation-Bot to youtube at specified intervals. It comments on the video it updloads and adds a description, and sets a bunch of other metadata like keywords

Also, in order to increase subscriber traffic to my channel, it goes and posts a comment (randomly selected copypasta meme from a list in the index.php) on a bunch of youtube channels' videos, and then says come subscribe to me. It has been running like this for many months and has not been banned by youtube.

I have deleted a bunch of mp4 and image files, so if the script needs them, just add your own to the root dir.

Designed to run on Mac with localhost XAMPP w/ php 5.6.35 apache server. Give the entire root dir recursive read write permissions for all users.

You must get your own credentials for youtube data api. Get them with oAuth playground for refresh token. 
To set the upload frequency, change numberOfVideosToUploadADay at the top of index.php

Any questions please create issues so I can publicly answer them. 