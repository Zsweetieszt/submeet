@extends('layouts.event')

@section('title', 'Papers - ' . $event->event_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active"><a href="{{ route(name: 'events') }}">Events</a>
    </li>
    <li class="breadcrumb-item active"><a
            href="{{ route('dashboard.event', request()->route('event')) }}">{{ $event->event_name }}</a>
    </li>
    <li class="breadcrumb-item active"><a
            href="{{ route('index.supporting-materials', request()->route('event')) }}">Supporting Materials
            Paper</a>
    </li>
    <li class="breadcrumb-item active"><span>Upload Supporting Materials</span>
    </li>
@endsection


@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Supporting Materials</h4>
            </div>
            <div class="card-body px-4">
                <form action="{{ route('upload.supporting-materials', [request()->route('event'), $papers->paper_sub_id]) }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                
                <input type="hidden" name="first_paper_sub_id" value="{{ $papers->paper_sub_id }}">
                <input type="hidden" name="event_id" value="{{ $event->event_id }}">
                <input type="hidden" name="user_id" value="{{ auth()->user()->user_id }}">

                <div>
                    <h5 class="my-1">Presentation Slide</h5>
                    @if (optional($supporting)->slide_file)
                        <p>Your Submitted File: <a
                                href="{{ asset('storage/slide/' . $supporting->event_id . '/' . $supporting->first_paper_sub_id . '/' . $supporting->slide_file) }}"
                                download="{{ $supporting->slide_file }}">{{ $supporting->slide_file }}</a> | |
                            Last Update: {{ $supporting->updated_at }}</p>
                            @else
                        <p>Upload your presentation slides in .ppt, .pptx, or .pdf format, prepared for oral presentation. (Maximum size: 10MB)</p>
                        @endif
                        <div class="button-wrapper d-flex align-items-end justify-content-start gap-2">
                            <label for="uploadPresentation"
                            class="btn btn-primary d-flex align-items-center justify-content-center gap-2">Upload File <i
                                class="cil-cloud-upload nav-icon"></i></label>
                                <input type="file" id="uploadPresentation" name="presentation_tf_file" style="display: none;"
                                onchange="handleFilePresentationUpload(event)">
                                <p id="fileChosenPresentation" class="align-self-center m-0" style="display: none;">
                            File Chosen : <a id="fileLinkPresentation" href="" download
                                style="display: none;">filename.docx</a>
                        </p>
                    </div>
                </div>

                @error('presentation_tf_file')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
                
                <div class="mt-4">
                    <h5 class="my-1">Presentation Video</h5>
                    @if (optional($supporting)->poster_file)
                        <p>Provide a YouTube or Google Drive link to your pre-recorded presentation video with clear audio
                            and visuals | |
                            Last Update: {{ $supporting->updated_at }}</p>
                            @else
                            <p>Provide a YouTube or Google Drive link to your pre-recorded presentation video with clear audio
                                and visuals.</p>
                    @endif

                    <div class="button-wrapper">
                        <input type="url" id="videoLinkInput" name="video_link" class="form-control mb-2"
                            placeholder="Paste your video link here" oninput="handleVideoLinkInput(event)"
                            value="{{ $supporting ? $supporting->video_url : null }}">
                            
                            <p id="fileChosenVideo" class="m-0" style="display: none;">
                                Link Chosen :
                                <a id="fileLinkVideo" href="#" target="_blank" style="display: none;">Video Link</a>
                            </p>
                    </div>

                    @error('video_link')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="btn-wrapper mt-4 pt-4 border-top">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
                
                </form>
            </div>
        </div>
    </div>
    <script>
        $('#reviewer').DataTable({});

        document.getElementById('downloadBtnTemplate').addEventListener('click', function() {
            const a = document.createElement('a');
            a.href = '/assets/template/publication_right_form.pdf';
            a.download = 'publication_right_form.pdf';
            document.body.appendChild(a);
            a.click();
            a.remove();
        });

        function handleFilePresentationUpload(event) {
            const file = event.target.files[0];
            const link = document.getElementById("fileLinkPresentation");
            const fileText = document.getElementById("fileChosenPresentation");

            if (file) {
                const url = URL.createObjectURL(file);
                link.href = url;
                link.download = file.name;
                link.textContent = file.name;
                link.style.display = "inline";
                fileText.style.display = "block";
            } else {
                link.href = "";
                link.textContent = "";
                link.style.display = "none";
                fileText.style.display = "none";
            }
        }


        function handleVideoLinkInput(event) {
            const input = event.target.value;
            const link = document.getElementById("fileLinkVideo");
            const linkText = document.getElementById("fileChosenVideo");

            if (input && (input.startsWith("http://") || input.startsWith("https://"))) {
                link.href = input;
                link.textContent = input;
                link.style.display = "inline";
                linkText.style.display = "block";
            } else {
                link.href = "";
                link.textContent = "";
                link.style.display = "none";
                linkText.style.display = "none";
            }
        }


        function handleFilePosterReadyUpload(event) {
            const file = event.target.files[0];
            const link = document.getElementById("fileLinkPoster");
            const fileText = document.getElementById("fileChosenPoster");

            if (file) {
                const url = URL.createObjectURL(file);
                link.href = url;
                link.download = file.name;
                link.textContent = file.name;
                link.style.display = "inline";
                fileText.style.display = "block";
            } else {
                link.href = "";
                link.textContent = "";
                link.style.display = "none";
                fileText.style.display = "none";
            }
        }
    </script>
    
@endsection
