@extends('layouts.event')

@section('title', 'Submit Paper - ' . $event->event_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active"><a href="{{ route(name: 'events') }}">Events</a>
    </li>
    <li class="breadcrumb-item active"><a
            href="{{ route('dashboard.event', request()->route('event')) }}">{{ $event->event_name }}</a>
    </li>
    <li class="breadcrumb-item active"><a href="{{ route('index.paper', request()->route('event')) }}">Papers</a>
    </li>
    <li class="breadcrumb-item active"><span>Edit Paper</span>
    </li>
@endsection
<style>
    .btn-help:hover {
        color: #0d6efd !important;
    }

    .gif-author {
        width: 150%;
        height: auto;
    }

    @media (max-width: 768px) {
        .gif-author {
            width: 100%;
        }
    }
</style>
@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Edit Paper</h4>
            </div>
            <div class="">
                <div class="card-body px-4">
                    <form id="myForm" class="needs-validation" novalidate
                        action="{{ route('update.paper', [request()->route('event'), $paper->paper_sub_id]) }}" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <fieldset>
                                <legend>Paper</legend>
                                <input type="text" name="event" value=" {{ request()->route('event') }} " hidden>
                                <div class="row align-items-start">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title<span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                                            id="title" maxlength="255" name="title" required
                                            value="{{ old('title', $paper->title) }}" placeholder="Title">
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="subtitle" class="form-label">Subtitle</label>
                                        <input type="text" class="form-control @error('subtitle') is-invalid @enderror"
                                            id="subtitle" maxlength="255" name="subtitle"
                                            value="{{ old('subtitle', $paper->subtitle) }}"
                                            placeholder="Subtitle (Optional)">
                                        @error('subtitle')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="abstract" class="form-label">Abstract<span
                                                class="text-danger">*</span></label>
                                        <textarea class="form-control @error('abstract') is-invalid @enderror" id="abstract" name="abstract" required
                                            placeholder="Abstract max 250 words" style="height: 255px;">{{ old('abstract', $paper->abstract) }}</textarea>
                                        @error('abstract')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small id="wordCount" class="form-text">Word Count: 0 / 250</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="topics" class="form-label">Topics<span
                                                class="text-danger">*</span></label>
                                       <select class="form-select @error('topics') is-invalid @enderror"
                                            id="topics" name="topics" required>
                                            <option value="" selected>Select Topic</option>
                                            @foreach ($topics as $topic)
                                                <option value="{{ $topic->topic_id }}"
                                                    {{ old('topics', $paper->topics->topic_id) == $topic->topic_id ? 'selected' : '' }}> {{ $topic->topic_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('topics')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="keywords" class="form-label">Keywords<span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('keywords') is-invalid @enderror"
                                            id="keywords" name="keywords" required
                                            value="{{ old('keywords', $paper->keywords) }}" placeholder="Keywords">
                                        @error('keywords')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="paper_file" class="form-label">Paper File</label>
                                        <input type="file" class="form-control @error('paper_file') is-invalid @enderror"
                                            id="paper_file" name="paper_file" required onchange="previewLogo(event)"
                                            placeholder="Paper File">
                                        @error('paper_file')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text">Allowed file type: DOC/DOCX. Max. file
                                            size: 5 MB. Leave blank if you don't want to update the file</small>
                                        @if ($paper->attach_file)
                                            <div class="mt-2">
                                                <a href="{{ asset('storage/' . $paper->attach_file) }}"
                                                    target="_blank">Current
                                                    File: {{ basename($paper->attach_file) }}</a>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mb-3">
                                        <label for="authors" class="form-label">Authors<span
                                                class="text-danger">*</span>
                                            <i class="ms-2 fa-solid fa-question btn-help" style="cursor: pointer;"data-coreui-toggle="modal" data-coreui-target="#modalGif"></i>
                                        </label>
                                        <input type="text"
                                            class="form-control place @error('authors') is-invalid @enderror"
                                            id="authors" name="authors" required
                                            value="{{ old('authors', $paper->authors) }}"
                                            placeholder="Add authors by typing: Authors Name - Authors Email">
                                        @error('authors')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="corresponding" class="form-label">Corresponding Author<span
                                                class="text-danger">*</span></label>
                                        <select class="form-select @error('corresponding') is-invalid @enderror"
                                            id="corresponding" name="corresponding" required>
                                            <option value="">No Author Chosen</option>
                                        </select>
                                        @error('corresponding')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="note_for_editor" class="form-label">Note for Editor</label>
                                        <textarea class="form-control @error('note_for_editor') is-invalid @enderror" id="note_for_editor"
                                            name="note_for_editor" required placeholder="Note for Editor max 3000 characters" style="height: 255px;"
                                            maxlength="3000">{{ old('note_for_editor', $paper->note_for_editor) }}</textarea>
                                        @error('note_for_editor')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small id="charCount" class="form-text">Character Count: 0 / 3000</small>
                                    </div>
                                </div>
                            </fieldset>

                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('index.paper', request()->route('event')) }}"
                                class="btn btn-danger">Cancel</a>
                            <button type="submit" class="btn btn-primary confirm-submit">Submit</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalGif" tabindex="-1" aria-labelledby="modalGif" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered d-flex align-items-center justify-content-center m-gif">
            <img src="/assets/img/gif/author_gif6c.gif" alt="Authors" class="gif-author">
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("myForm").addEventListener("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                }
            });
        });

        var input = document.querySelector('input[name=keywords]');
        new Tagify(input)

        var authors = document.querySelector('input[name=authors]');
        new Tagify(authors, {
            whitelist: [
                @foreach ($user as $u)
                    "{{ $u->given_name }} {{ $u->family_name }} - {{ $u->email }}",
                @endforeach
            ],
            dropdown: {
                enabled: 0,
                closeOnSelect: false,
                maxItems: 0,
                show: false
            }
        })
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".confirm-submit").forEach(button => {
                button.addEventListener("click", function(e) {
                    e.preventDefault();
                    let form = this.closest("form");

                    Swal.fire({
                        title: "Are you sure want to Submit?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, Submit!",
                        cancelButtonText: "No, cancel!",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            const authorsInput = document.querySelector('input[name="authors"]');
            const correspondingSelect = document.getElementById("corresponding");
            let savedCorrespondingValue = null;

            // Save the initial corresponding value
            @if ($paper->corresponding)
                savedCorrespondingValue = `{{ $paper->corresponding }}`;
            @elseif (old('corresponding'))
                savedCorrespondingValue = `{{ old('corresponding') }}`;
            @endif

            function updateCorrespondingOptions() {
                let authors = [];
                let currentSelectedValue = correspondingSelect.value;

                try {
                    authors = JSON.parse(authorsInput.value || "[]");
                } catch (e) {
                    authors = [];
                }

                correspondingSelect.innerHTML = '<option value="">No Author Chosen</option>';

                authors.forEach((author, index) => {
                    const option = document.createElement("option");
                    option.value = `${index + 1}`;
                    option.textContent = `Author ${index + 1} - ${author.value}`;
                    correspondingSelect.appendChild(option);
                });

                // Set the corresponding value
                if (savedCorrespondingValue && savedCorrespondingValue <= authors.length) {
                    correspondingSelect.value = savedCorrespondingValue;
                    savedCorrespondingValue = null; // Clear after first use
                } else if (currentSelectedValue && currentSelectedValue <= authors.length) {
                    correspondingSelect.value = currentSelectedValue;
                } else {
                    correspondingSelect.value = "";
                }
            }

            // Initialize after a small delay to ensure Tagify is ready
            setTimeout(() => {
                updateCorrespondingOptions();
                authorsInput.addEventListener("change", updateCorrespondingOptions);
            }, 100);

            // Additional check after a longer delay for Tagify initialization
            setTimeout(updateCorrespondingOptions, 3000);
        });

        document.addEventListener("DOMContentLoaded", function() {
            const abstractInput = document.getElementById("abstract");
            const wordCountDisplay = document.getElementById("wordCount");
            const wordLimit = 250;

            function countWords(text) {
                return text.trim().split(/\s+/).filter(word => word.length > 0).length;
            }

            abstractInput.addEventListener("input", function() {
                const wordCount = countWords(this.value);

                wordCountDisplay.textContent = `Word Count: ${wordCount} / ${wordLimit}`;

                if (wordCount > wordLimit) {
                    wordCountDisplay.style.color = "red";
                } else {
                    wordCountDisplay.style.color = "grey";
                }
            });

            const initialWordCount = countWords(abstractInput.value);
            wordCountDisplay.textContent = `Word Count: ${initialWordCount} / ${wordLimit}`;
            wordCountDisplay.style.color = initialWordCount > wordLimit ? "red" : "grey";

            const noteInput = document.getElementById("note_for_editor");
            const charCountDisplay = document.getElementById("charCount");
            const charLimit = 3000;

            function updateCharCount() {
                const charCount = noteInput.value.length;
                charCountDisplay.textContent = `Character Count: ${charCount} / ${charLimit}`;
                charCountDisplay.style.color = charCount > charLimit ? "red" : "grey";
            }

            noteInput.addEventListener("input", updateCharCount);
            updateCharCount();
        });
    </script>
    
@endsection
