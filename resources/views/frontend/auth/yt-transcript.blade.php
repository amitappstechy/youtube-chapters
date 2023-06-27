<script>
    let hit_count = 10;
    let hit_index = 0;
    let process_completed = false;
    //ajax setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(document).on('submit', '.hit-trans', function(e) {
        e.preventDefault();
        var self = $(this);
        var video_id = getYouTubeVideoId($('.yt-url').val());
        var option = $('.choice-option').val();
        if(process_completed == true)
        {
            $('.response-box').text('');
            if(option=="medium"){
                hit_count = 15;
            }
            else if(option=="large"){
                hit_count = 20;
            }
            else{
                hit_count = 10;
            }
            hit_index = 0;
        }
        else{
            if(option=="medium"){
                hit_count = 15;
            }
            else if(option=="large"){
                hit_count = 20;
            }
            else{
                hit_count = 10;
            }
        }
        $.ajax({
            url: "{{ route('front.youtube-video.transcript') }}",
            type: 'POST', // change status
            data: {
                video_id: video_id,option:option
            },
            dataType: "json",
            beforeSend: function() {
                // setting a timeout
                $('.response-box').attr('disabled',true);
                self.find('input[type="submit"]').attr('disabled',true);
                self.find('input[type="submit"]').val('transcripting.. wait');
            },
            success: function(result) {
                if (result.status == "success") {
                    self.find('input[type="submit"]').val('generating response.. wait');
                    get_open_api_response(result.message,hit_index)
                } else {
                    swal('',result.message,result.status);
                    self.find('input[type="submit"]').attr('disabled',false);
                    self.find('input[type="submit"]').val('Submit');
                }
            }
        });
    })

    //get open ai resposne
    function get_open_api_response(transcript,hit_index)
    {
        if(hit_count!=0 && transcript[hit_index]!=undefined)
        {
            let custom_transcript = transcript;
            var option = $('.choice-option').val();
            var response_sentence ='';
            $.ajax({
                url: "{{ route('front.youtube-video.open-ai-response') }}",
                type: 'POST', // change status
                data: {
                    transcript: transcript[hit_index],option:option,hit_index: hit_index
                },
                dataType: "json",
                success: function(result) {
                    if ((result.status == "success")&&(hit_count!=0)) {
                        hit_index = hit_index+1;
                        hit_count = hit_count-1;
                        response_sentence= $('.response-box').text()+result.response.replace(/\n/g, '').replace(/['"]/g, '')+'\n';
                        $('.response-box').text(response_sentence);
                        $('.response-box').scrollTop($('.response-box')[0].scrollHeight);  
                        get_open_api_response(custom_transcript,hit_index)
                    } 
                    else if(hit_count==0)
                    {
                        $('.response-box').attr('disabled',false);
                        process_completed = true;
                        $('.hit-trans').find('input[type="submit"]').attr('disabled',false);
                        $('.hit-trans').find('input[type="submit"]').val('Submit');
                        swal(' ','Chapters generated successfully','success')
                    }else {
                        swal('',result.message,result.status);
                    }
                }
            });
        }
        else{
            process_completed = true;
            $('.response-box').attr('disabled',false);
            $('.hit-trans').find('input[type="submit"]').attr('disabled',false);
            $('.hit-trans').find('input[type="submit"]').val('Submit');
            swal(' ','Chapters generated successfully','success')
        }
    }

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