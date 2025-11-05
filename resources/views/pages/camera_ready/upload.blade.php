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
    <li class="breadcrumb-item active"><a href="{{ route('index.camera-ready', request()->route('event')) }}">Camera-ready
            Paper</a>
    </li>
    <li class="breadcrumb-item active"><span>Upload Camera-ready Paper & Copyright Transfer</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Camera-ready Paper & Copyright Transfer</h4>
            </div>
            <div class="card-body px-4">
            <form action="{{ route('upload.camera-ready', [request()->route('event'), $papers->paper_sub_id]) }}"
                method="POST" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="first_paper_sub_id" value="{{ $papers->first_paper_sub_id }}">
                <input type="hidden" name="event_id" value="{{ $event->event_id }}">
                <input type="hidden" name="user_id" value="{{ auth()->user()->user_id }}">

                <div>
                    <h5 class="my-1">Camera-ready Paper</h5>
                    @if (optional($camera_ready)->cr_paper_file)
                        <p>Your Submitted File: <a
                                href="{{ asset('storage/paper/' . $camera_ready->event_id . '/' . $camera_ready->first_paper_sub_id . '/' . $camera_ready->cr_paper_file) }}"
                                download="{{ $camera_ready->cr_paper_file }}">{{ $camera_ready->cr_paper_file }}</a> | |
                            Last Update: {{ $camera_ready->updated_at }}</p>
                    @else
                        <p>Please submit your Camera-ready Paper!</p>
                    @endif
                    <div class="button-wrapper d-flex align-items-end justify-content-start gap-2">
                        <label for="uploadCamera"
                            class="btn btn-primary d-flex align-items-center justify-content-center gap-2">Upload File <i
                                class="cil-cloud-upload nav-icon"></i></label>
                        <input type="file" id="uploadCamera" name="cr_paper_file" style="display: none;"
                            onchange="handleFileCameraReadyUpload(event)">

                        <!-- Teks dan Link Preview -->
                        <p id="fileChosenCamera" class="align-self-center m-0" style="display: none;">
                            File Chosen:
                            <a id="fileLinkCamera" href="#" download style="display: none;"></a>
                        </p>
                    </div>
                    <p class="text-muted small mb-0">
                        <i class="fas fa-info-circle"></i>
                            Only <strong>.doc</strong> or <strong>.docx</strong> files are allowed, with a maximum size of <strong>5MB</strong>.
                        </p>
                </div>

                @error('cr_paper_file')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror

                <div class="mt-4">
                    <h5 class="my-1">Copyright Transfer</h5>
                    @if (optional($camera_ready)->copyright_trf_file)
                        <p>Your Submitted File: <a
                                href="{{ asset('storage/copyright/' . $camera_ready->event_id . '/' . $camera_ready->first_paper_sub_id . '/' . $camera_ready->copyright_trf_file) }}"
                                download="{{ $camera_ready->copyright_trf_file }}">{{ $camera_ready->copyright_trf_file }}</a>
                            | | Last Update: {{ $camera_ready->updated_at }}</p>
                    @else
                        <!-- <p>Please download, check, and submit your Copyright Transfer!</p> -->
                         <p><strong>Not available at this time. You will be notified once it is ready</strong></p>
                    @endif
                    <div class="button-wrapper d-flex align-items-end justify-content-start gap-2">
                        <button class="btn btn-success d-flex align-items-center justify-content-center gap-2"
                            id="downloadBtnTemplate">Download Template
                            <i class="cil-cloud-download nav-icon"></i></button>
                        <label for="uploadCopyright"
                            class="btn btn-primary d-flex align-items-center justify-content-center gap-2">Upload File <i
                                class="cil-cloud-upload nav-icon"></i></label>
                        <input type="file" id="uploadCopyright" name="copyright_tf_file" style="display: none;"
                            onchange="handleFileCopyrightUpload(event)">
                        <p id="fileChosenText" class="align-self-center m-0" style="display: none;">
                            File Chosen : <a id="fileLinkCopyright" href="" download style="display: none;"></a>
                        </p>
                    </div>
                                        <!-- <p class="text-muted small mb-0">* Only <strong>.pdf</strong> files are allowed, with a maximum size of <strong>5MB</strong>.</p> -->
                </div>

                @error('copyright_tf_file')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror

                <div class="btn-wrapper mt-4 pt-4 border-top">
                    <button type="submit" class="btn btn-primary ">Save</button>
                </div>

            </form>
            </div>
        </div>
    </div>
    <script>
        $('#reviewer').DataTable({});

        document.getElementById('downloadBtnTemplate').addEventListener('click', function(event) {
            event.preventDefault();
            const a = document.createElement('a');
            a.href = '/assets/template/publication_right_form.pdf';
            a.download = 'publication_right_form.pdf';
            document.body.appendChild(a);
            a.click();
            a.remove();
        });

        function handleFileCopyrightUpload(event) {
            const file = event.target.files[0];
            const link = document.getElementById("fileLinkCopyright");
            const fileText = document.getElementById("fileChosenText");

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


        function handleFileCameraReadyUpload(event) {
            const file = event.target.files[0];
            const link = document.getElementById("fileLinkCamera");
            const fileText = document.getElementById("fileChosenCamera");

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
