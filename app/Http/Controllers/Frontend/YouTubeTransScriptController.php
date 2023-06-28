<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use GuzzleHttp\Client;
use Validator;
use Carbon\Carbon;

class YouTubeTransScriptController extends Controller
{
    /**
     * @param VIDEO_ID
     * 
     * @return Transcript
     */
    public function get_transcript(Request $request)
    {
        $video_id = $request->video_id;
        if($request->ajax() && $request->filled('video_id') && $request->video_id!="null")
        {
            $validator = Validator::make($request->all(), [
                'video_id' => 'required',
                'option' => 'required|in:low,medium,larage'
            ]);
            if($validator->passes())
            {
                $client = new Client();

                try {
                    $response = $client->request('GET', 'https://backend-api-samyabrata-maji.vercel.app/api/ts/'.$video_id);
                    
                    $statusCode = $response->getStatusCode();
                    
                    if ($statusCode === 200) {
                        $responseBody = $response->getBody()->getContents();
                        $responseJson = json_decode($responseBody, true);
                        
                        if (isset($responseJson['transcript']) && count($responseJson['transcript'])) {
                            // $transcript = preg_replace('/\d+\.\d+-\d+\.\d+=/', '', $responseJson['transcript'][0]);
                            // $transcript = trim($transcript);
                            return response()->json([
                                'status'=>'success',
                                'message'=>$responseJson['transcript']
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    return response()->json([
                        'status'=>'error',
                        'message'=>'No transscript is persent'
                    ]);
                }
            }
            else{
                return response()->json([
                    'status'=>'warning',
                    'message'=>'Please fill youtube url'
                ]);
            }
        }
        else{
            return response()->json([
                'status'=>'warning',
                'message'=>'Please fill youtube url'
            ]);
        }
    }


    /**
     * @param transcript_array
     * 
     * @return open_ai_data
     */
    public function get_open_ai_response(Request $request)
    {
        if($request->ajax())
        {
            $prompt_result_data =[];
            $validator = Validator::make($request->all(), [
                'transcript' => 'required',
                'option' => 'required|in:low,medium,larage'
            ]);
            $hit_index = $request->hit_index;
            $transcript_data = $request->transcript;

            if(isset($transcript_data))
            {
                $transcript =  $request->transcript;

                // Extract first time using regular expression
                preg_match('/(\d{2}:\d{2})/', $transcript, $matches);
                $firstSecond = self::getTime($matches[0]);
                
                // Extract last time using regular expression
                preg_match_all('/(\d{2}:\d{2})/', $transcript, $matches);
                $lastSecond = self::getTime(end($matches[0]));

                if ($hit_index==0)
                {
                    $firstSecond = '00:00:00';
                }
  
                $transcript_prompt = preg_replace('/\d+\.\d+-\d+\.\d+=/', '', $transcript);

                $open_ai_response = self::hit_chat_gpt($transcript_prompt);

                if($open_ai_response['status'])
                {
                    $prompt_result_data['initial_time'] = $firstSecond;
                    $prompt_result_data['end_time'] = $lastSecond;
                    $prompt_result_data['title'] = $open_ai_response['response'];
                    $prompt_result_data['result_text'] = $firstSecond.' '.$prompt_result_data['title'];
                }
            }
        
            if(count($prompt_result_data))
            {
                return response()->json([
                    'status'=>'success',
                    'response'=>  trim($prompt_result_data['result_text'])
                ]);
            }
            else{
                return response()->json([
                    'status'=>'error'
                ]);
            }    
        }
    }

    /**
     * Get time
     */
    function getTime($seconds)
    {
        if(!strtotime($seconds))
        {
            $seconds = intval($seconds); // Convert to integer
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $seconds = round($seconds % 60);

            $time = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
        }
        elseif(strtotime($seconds)){
            $y = $seconds;
            // Check if the input format includes the hour value
            if (preg_match('/^\d+:\d+$/', $y)) {
                $format = 'i:s';
            } else {
                $format = 'H:i:s';
            }
            $time = Carbon::createFromFormat($format, $y)->format('H:i:s');
        }
        return $time;
    }

    /**
     * hit chat gpt
     */
    function hit_chat_gpt($prompt_text)
    {
        $response_data_result=[
            'status' => false
        ];

        $OPENAI_API_KEY = env('open_ai_key');
        $dTemperature = 0.6;
        $iMaxTokens = 2400;
        $top_p = 1;
        $frequency_penalty = 0.5;
        $presence_penalty = 0.5;
        $sModel = "text-davinci-003";
        
        $client = new Client();
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $OPENAI_API_KEY
        ];
        
        $data = [
            'model' => $sModel,
            'prompt' => "What will be attractive topic title for this : ".$prompt_text." . Don't return description.",
            'temperature' => $dTemperature,
            'max_tokens' => $iMaxTokens,
            'top_p' => $top_p,
            'frequency_penalty' => $frequency_penalty,
            'presence_penalty' => $presence_penalty,
            'stop' => '[" Human:", " AI:"]',
        ];
        
        try {
            $response = $client->post('https://api.openai.com/v1/completions', [
                'headers' => $headers,
                'json' => $data,
            ]);
            
            $result = json_decode($response->getBody(), true);
            
            if (isset($result['choices'])) {
                $response_data_result=[
                    'status' => true,
                    'response' => $result['choices'][0]['text'],
                ];
            } elseif (isset($result['error'])) {
                $response_data_result=[
                    'status' => false,
                    'response' => $result['error']['message'],
                ];
            }
        } catch (\Exception $e) {
            $response_data_result=[
                'status' => false,
                'response' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response_data_result;
    }
}
