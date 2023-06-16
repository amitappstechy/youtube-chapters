<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html>
<body>

<h2>HTML Forms</h2>

<form action="/videos.php?part=chapters&id=F9UBPbsZ2Rs">
  <label for="youtube_url">You Tube Video URL:</label><br>
  <input type="text" id="youtube_url" name="youtube_url"><br>
  <br>
  <input type="submit" value="Submit and get chapters">
</form> 
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js?v=<?=time()?>"
    type="text/javascript"></script>
    <script>
        $(document).on('submit','form',function(e){
            e.preventDefault();
            var url = $('input[name="youtube_url"]').val();
            if(url!='')
            {
                var video_id =  getYouTubeVideoId(url);
                location.href ="/yt-chapters/videos.php?part=chapters&id="+video_id;
            }
           
        })

        // Function to get the value of 'v'
        function getYouTubeVideoId(url) {
            // Extract the query string from the URL
            var queryString = url.split('?')[1];
            
            // Split the query string into key-value pairs
            var params = new URLSearchParams(queryString);
            
            // Get the value of 'v'
            var videoId = params.get('v');
            
            return videoId;
        }

    </script>
</body>
</html>