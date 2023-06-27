@extends('layouts.frontend')
@push('style')
    <style>
        .form-box {
            max-width: 500px;
            margin: auto;
            padding: 50px;
            background: #ffffff;
            border: 10px solid #f2f2f2;
        }

        h1,
        p {
            text-align: center;
        }

        input,
        textarea {
            width: 100%;
        }
    </style>
@endpush
@section('content')
    <div class="container">
        <div class="form-box">
            <h1>YouTube Chapter Generator</h1>
            <p>Using Open AI generate chapters</p>
            <form class="hit-trans" method="post">
                <div class="mb-3">
                    <label for="exampleFormControlInput1" class="form-label">Youtube URL</label>
                    <input type="text" class="form-control yt-url" id="exampleFormControlInput1"
                        placeholder="write url here" autocomplete="off">
                </div>
                <div class="mb-3">
                    <label for="exampleFormControlSelect1" class="form-label">Numbers of chapters</label>
                    <select class="form-control choice-option" id="exampleFormControlSelect1">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="large">Large</option>
                    </select>
                </div>
                <input class="btn btn-primary" type="submit" value="Submit" />
            </form>
        </div>
        <div class="mb-3">
            <label for="exampleFormControlTextarea1" class="form-label">Example textarea</label>
            <textarea class="form-control response-box" id="exampleFormControlTextarea1" rows="10" disabled></textarea>
        </div>
    </div>
@endsection
@push('script')
    @include('frontend.auth.yt-transcript')
@endpush
